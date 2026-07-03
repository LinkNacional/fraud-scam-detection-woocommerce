#!/usr/bin/env bash
# check-security.sh — Varredura de segurança para código PHP do plugin
#
# Uso:
#   .reasonix/skills/check-security.sh          # varre tudo
#   .reasonix/skills/check-security.sh file.php # arquivo específico
#
# Saída: report com file:line + exit code (0 = limpo, 1 = violações)

set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

VIOLATIONS=0
TARGET="${1:-}"

# ── Helpers ───────────────────────────────────────────────
violation() {
    local rule="$1" file="$2" line="$3" detail="$4"
    echo -e "${RED}[${rule}]${NC} ${file}:${line} — ${detail}"
    VIOLATIONS=$((VIOLATIONS + 1))
}

# ── Arquivos a escanear ──────────────────────────────────
if [[ -n "$TARGET" ]]; then
    FILES=("$TARGET")
else
    mapfile -t FILES < <(
        find Includes Admin Public tests \
            -name '*.php' -type f 2>/dev/null \
            | sort
    )
fi

if [[ ${#FILES[@]} -eq 0 ]]; then
    echo "Nenhum arquivo PHP encontrado."
    exit 0
fi

echo -e "${YELLOW}🔍 Escaneando ${#FILES[@]} arquivo(s)...${NC}"
echo ""

for file in "${FILES[@]}"; do
    [[ -f "$file" ]] || continue

    line_num=0
    while IFS= read -r code_line; do
        line_num=$((line_num + 1))

        # Pula comentários e linhas vazias
        [[ "$code_line" =~ ^[[:space:]]*(\/\/|\#|\/\*) ]] && continue
        [[ -z "$(echo "$code_line" | tr -d '[:space:]')" ]] && continue

        # ── R1: $_POST sem wp_unslash ──────────────────
        if echo "$code_line" | grep -qP '\$(_(POST|GET|COOKIE|REQUEST)\b|HTTP_RAW_POST_DATA)'; then
            if ! echo "$code_line" | grep -qP 'wp_unslash'; then
                violation "R1-UNSLASH" "$file" "$line_num" "\$_POST/\$_GET/\$_COOKIE/\$_REQUEST sem wp_unslash()"
            fi
        fi

        # ── R2: Superglobal sem sanitize ────────────────
        if echo "$code_line" | grep -qP '\$_(POST|GET|COOKIE|REQUEST|SERVER)\b'; then
            if ! echo "$code_line" | grep -qP 'sanitize_'; then
                violation "R2-SANITIZE" "$file" "$line_num" "superglobal sem sanitize_text_field/sanitize_key/sanitize_email"
            fi
        fi

        # ── R3: echo de variável sem escape ─────────────
        if echo "$code_line" | grep -qP 'echo\s+\$[a-zA-Z_]'; then
            if ! echo "$code_line" | grep -qP 'esc_'; then
                violation "R3-ESCAPE" "$file" "$line_num" "echo de variável sem esc_html/esc_attr/esc_url"
            fi
        fi

        # ── R4: SQL sem prepare ─────────────────────────
        if echo "$code_line" | grep -qP '\$wpdb->(query|get_results|get_var|get_row|get_col)\s*\('; then
            if ! echo "$code_line" | grep -qP 'prepare\s*\('; then
                # Pula linhas que já têm prepare na mesma expressão
                violation "R4-SQL" "$file" "$line_num" "\$wpdb->query/get_results sem prepare()"
            fi
        fi

        # ── R5: ABSPATH guard ausente ───────────────────
        if [[ $line_num -eq 1 ]]; then
            if ! grep -qP "defined\s*\(\s*'ABSPATH'\s*\)" "$file"; then
                violation "R5-ABSPATH" "$file" "1" "arquivo sem ABSPATH guard"
            fi
        fi

    done < "$file"
done

echo ""
if [[ $VIOLATIONS -eq 0 ]]; then
    echo -e "${GREEN}✅ Nenhuma violação encontrada.${NC}"
    exit 0
else
    echo -e "${RED}⛔ ${VIOLATIONS} violação(ões) encontrada(s).${NC}"
    exit 1
fi
