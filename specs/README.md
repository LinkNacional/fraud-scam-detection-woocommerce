# /specs — Spec-Driven Development

Contratos antes do código. Toda feature nova começa aqui.

## Estrutura

```
specs/
├── openapi/       → Contratos REST API / Webhooks (OpenAPI 3.x)
└── features/      → Comportamento BDD / Gherkin (.feature)
```

## Fluxo SDD

1. **Especificar** — escrever o contrato (.yaml ou .feature)
2. **Validar** — revisar contra AGENTS.md (segurança, WP standards)
3. **Teste RED** — implementar teste baseado no spec
4. **GREEN** — codificar até passar
5. **REFACTOR** — limpar mantendo spec como referência

## Regras

- Spec NUNCA referencia implementação (nada de nomes de classe, paths de arquivo).
- OpenAPI: usar `operationId` como identificador estável para testes.
- Gherkin: cenários escritos em linguagem de negócio, não técnica.
- Todo endpoint REST ou webhook NOVO exige spec OpenAPI antes do código.
