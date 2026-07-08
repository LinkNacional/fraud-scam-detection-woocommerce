# check-security

Varredura automatizada de segurança para o plugin Fraud Detection.

## Regras verificadas

| Código | Descrição |
|---|---|
| R1-UNSLASH | `$_POST`/`$_GET`/`$_COOKIE`/`$_REQUEST` sem `wp_unslash()` |
| R2-SANITIZE | Superglobal sem `sanitize_text_field`/`sanitize_key`/`sanitize_email` |
| R3-ESCAPE | `echo $var` sem `esc_html`/`esc_attr`/`esc_url` |
| R4-SQL | `$wpdb->query/get_results` sem `prepare()` |
| R5-ABSPATH | Arquivo sem guard `if ( ! defined( 'ABSPATH' ) ) exit;` |

## Uso

```bash
# Varredura completa
.reasonix/skills/check-security.sh

# Arquivo específico
.reasonix/skills/check-security.sh Includes/MinhaClasse.php
```

## Integração com pipeline

Executar no estágio 4 (SECURITY) do `.reasonix.toml`. Bloquear merge se exit code ≠ 0.
