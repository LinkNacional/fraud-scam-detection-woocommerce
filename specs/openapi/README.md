# /specs/openapi — Contratos REST & Webhooks

## Convenção

- Formato: OpenAPI 3.0 ou 3.1 (YAML)
- Nome do arquivo: `{dominio}-api.yaml` (ex: `fraud-detection-api.yaml`)
- `operationId`: camelCase descritivo (ex: `checkFraudScore`, `banIpAddress`)

## Template mínimo

```yaml
openapi: "3.0.3"
info:
  title: "{Nome da API}"
  version: "1.0.0"
paths:
  /wp-json/lkn-fraud/v1/{recurso}:
    post:
      operationId: "{operacao}"
      security:
        - nonce: []
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required: []
              properties: {}
      responses:
        "200":
          description: Sucesso
        "403":
          description: Nonce inválido
```

## Checklist de segurança (todo endpoint)

- [ ] Nonce WordPress no request
- [ ] `permission_callback` definido (`current_user_can`)
- [ ] Input validado + sanitizado
- [ ] Response não vaza dados internos
