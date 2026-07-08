#!/usr/bin/env bash
# wp-cli-wrapper.sh — WP-CLI seguro integrado ao ecossistema Local WP
#
# Uso:
#   .reasonix/skills/wp-cli-wrapper.sh <comando wp sem "wp">  → ex: plugin list
#   .reasonix/skills/wp-cli-wrapper.sh db query "SELECT ..."   → com trava
#
# Trava de segurança: comandos destrutivos de DB exigem --force.

set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# ── Detecta caminho do WP-CLI no Local WP ─────────────────
detect_wp_cli() {
    # Local WP: o binário `wp` está no PATH do site shell
    if command -v wp &>/dev/null; then
        echo "wp"
        return
    fi

    # Fallback: Local WP no macOS
    if [[ -f "/Applications/Local.app/Contents/Resources/extraResources/wp-cli/wp-cli.phar" ]]; then
        echo "php /Applications/Local.app/Contents/Resources/extraResources/wp-cli/wp-cli.phar"
        return
    fi

    # Fallback: Local WP no Linux (Lightning Services)
    if command -v lightning &>/dev/null; then
        echo "lightning wp"
        return
    fi

    # Fallback: phar local no projeto
    if [[ -f "$SCRIPT_DIR/wp-cli.phar" ]]; then
        echo "php $SCRIPT_DIR/wp-cli.phar"
        return
    fi

    echo ""
}

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WP="$(detect_wp_cli)"

if [[ -z "$WP" ]]; then
    echo -e "${RED}⛔ WP-CLI não encontrado.${NC}"
    echo "   Instale: https://wp-cli.org/#installing"
    echo "   Ou execute dentro do Site Shell do Local WP."
    exit 1
fi

# ── Comandos destrutivos de DB (exigem --force) ──────────
DESTRUCTIVE_DB_COMMANDS=(
    "db reset"
    "db drop"
    "db truncate"
    "db delete"
    "db import"
    "site empty"
)

is_destructive() {
    local full_cmd="$*"
    for dangerous in "${DESTRUCTIVE_DB_COMMANDS[@]}"; do
        if echo "$full_cmd" | grep -q "$dangerous"; then
            return 0
        fi
    done
    return 1
}

# ── Verifica se está no contexto correto ─────────────────
verify_context() {
    # Tenta detectar se wp-cli consegue falar com o WordPress
    if ! $WP core is-installed 2>/dev/null; then
        echo -e "${YELLOW}⚠️  WordPress não detectado no contexto atual.${NC}"
        echo "   Execute dentro do Site Shell do Local WP:"
        echo "   Botão direito no site → Open Site Shell"
        return 1
    fi
    return 0
}

# ── Main ──────────────────────────────────────────────────
if [[ $# -eq 0 ]]; then
    echo "WP-CLI Wrapper — Fraud Detection Plugin"
    echo ""
    echo "Uso: $(basename "$0") <comando wp sem 'wp'>"
    echo ""
    echo "Exemplos:"
    echo "  $(basename "$0") plugin list"
    echo "  $(basename "$0") option get lknFraudDetectionForWoocommerceEnableRecaptcha"
    echo "  $(basename "$0") db query \"SELECT option_value FROM wp_options WHERE option_name='active_plugins'\""
    echo "  $(basename "$0") db export dump.sql --force"
    echo ""
    echo "Trava: comandos destrutivos exigem --force."
    exit 0
fi

# Monta comando completo
FULL_CMD="$*"

# ── Trava de segurança ───────────────────────────────────
if is_destructive "$FULL_CMD"; then
    if ! echo "$FULL_CMD" | grep -q '\--force'; then
        echo -e "${RED}⛔ TRAVA DE SEGURANÇA: comando destrutivo de DB detectado.${NC}"
        echo "   Comando: $FULL_CMD"
        echo "   Adicione --force para executar mesmo assim."
        echo ""
        echo "   ⚠️  Isso vai ALTERAR/DESTRUIR dados no banco 'local_tests'."
        exit 1
    fi
    echo -e "${YELLOW}⚠️  ATENÇÃO: executando comando destrutivo com --force.${NC}"
    echo -n "   Continuar? [s/N] "
    read -r confirm
    if [[ ! "$confirm" =~ ^[Ss]$ ]]; then
        echo "Cancelado."
        exit 0
    fi
fi

# ── Executa ───────────────────────────────────────────────
echo -e "${CYAN}▶ ${WP} ${FULL_CMD}${NC}"
echo ""

if $WP $FULL_CMD; then
    echo ""
    echo -e "${GREEN}✅ OK${NC}"
else
    echo ""
    echo -e "${RED}⛔ Erro (exit=$?)${NC}"
    exit 1
fi
