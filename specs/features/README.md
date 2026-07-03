# /specs/features — Comportamento BDD / Gherkin

## Convenção

- Formato: Gherkin (.feature)
- Linguagem: pt-BR (negócio) ou en (técnico) — consistente por arquivo
- Nome do arquivo: `{dominio}.feature` (ex: `antifraude-checkout.feature`)

## Template

```gherkin
# language: pt

Funcionalidade: {Nome da funcionalidade}
  Como {ator}
  Quero {ação}
  Para {objetivo de negócio}

  Regra de Negócio: {descrição da regra}

  Cenário: {nome do cenário}
    Dado que {pré-condição}
    Quando {ação}
    Então {resultado esperado}

  Cenário: {cenário de erro}
    Dado que {pré-condição}
    Quando {ação inválida}
    Então {mensagem de erro}
```

## Mapeamento Spec → Teste

| Gherkin | PHPUnit |
|---|---|
| Funcionalidade | Test class |
| Cenário | `test_` method |
| Dado que | `setUp()` / factory |
| Quando | método sendo testado |
| Então | `assert*()` |
