# wp-cli-wrapper

Wrapper seguro do WP-CLI integrado ao ecossistema Local WP.

## Modos de detecção automática

| Ordem | Fonte | Quando |
|---|---|---|
| 1 | `wp` no PATH | Site Shell do Local WP ativo |
| 2 | Local.app (macOS) | Binário no `/Applications/Local.app/...` |
| 3 | `lightning wp` (Linux) | Lightning Services do Local WP |
| 4 | `wp-cli.phar` local | Phar no diretório do script |

## Trava de segurança

Comandos que **exigem `--force`** + confirmação interativa:

- `db reset` / `db drop` / `db truncate` / `db delete` / `db import`
- `site empty`

## Exemplos

```bash
# Leitura (não precisa de --force)
.reasonix/skills/wp-cli-wrapper.sh plugin list
.reasonix/skills/wp-cli-wrapper.sh option get active_plugins
.reasonix/skills/wp-cli-wrapper.sh db query "SELECT * FROM wp_options LIMIT 1"

# Escrita segura (não destrutiva)
.reasonix/skills/wp-cli-wrapper.sh option update lkn_setting_key "valor"

# Destrutivo (requer --force + confirmação)
.reasonix/skills/wp-cli-wrapper.sh db import dump.sql --force
```

## Integração

Usar no estágio 2 (RED) para preparar fixtures de banco.
Usar no estágio 4 (SECURITY) para verificar options salvas.
