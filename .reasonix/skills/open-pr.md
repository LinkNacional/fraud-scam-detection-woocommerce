---
name: open-pr
description: Abre PR da branch dev para main no padrão Link Nacional (título VERSION - repo - resumo; corpo com readme/metadados e resumo copiado do changelog)
---

# open-pr

Abre um Pull Request de `dev` → `main` via `gh pr create`, no padrão Link Nacional.

## Parâmetros (via `arguments`)

O usuário pode passar: `version=1.3.1 tested_up=7.0 summary=Banimento por número de telefone nas notas do pedido`. Qualquer valor ausente é extraído do código.

- **version** — versão da release (Stable tag / cabeçalho PHP)
- **tested_up** — WP testado até
- **summary** — resumo CURTO das mudanças (usado no TÍTULO). Se ausente, derive da entrada mais recente do changelog (NÃO do git log).

## Fluxo de execução

### 1. Extrair metadados (se não vierem nos arguments)

```bash
# Header PHP (fonte da verdade)
grep -m1 -E "^\s*\*\s*Version:" *.php
grep -m1 -E "^\s*\*\s*Requires PHP:" *.php

# README.txt (fallback para Tested up to)
grep -m1 -i "^Tested up to:" README.txt
grep -m1 -i "^Requires PHP:" README.txt
grep -m1 -i "^Stable tag:" README.txt

# Nome do repositório
REPO_NAME=$(basename "$PWD")
```

### 2. Ler o changelog da versão atual (fonte do resumo e dos bullets)

⚠️ **Regra anti-redundância.** NÃO use `git log` para gerar o resumo — o range de commits está dessincronizado (tags antigas/ausentes, branches de beta) e traz itens de versões já publicadas. Em vez disso, leia a entrada mais recente do changelog:

```bash
# Preferir CHANGELOG.md (português). Fallback: README.txt, seção == Changelog ==.
head -n 30 CHANGELOG.md
# ou
grep -A 20 "^== Changelog ==" README.txt
```

A entrada mais recente tem o formato `# VERSION - DD/MM/AA` seguido de bullets `* ...`.

- **TÍTULO**: resuma esses bullets em uma frase curta (≤ ~12 palavras).
- **CORPO**: copie os bullets tal como estão no changelog (sem hash, sem reescrever).

### 3. Montar TÍTULO

Formato exato (obrigatório):

```
VERSION - REPO_NAME (RESUMO_CURTO)
```

Exemplo:
```
1.3.1 - fraud-scam-detection-woocommerce (Banimento por número de telefone nas notas do pedido)
```

### 4. Montar CORPO

Use como gabarito o modelo abaixo. Extraia os campos fixos do `README.txt` (Contributors, Tags, License, License URI, etc.). Substitua APENAS os placeholders `{...}`:

```markdown
# {PLUGIN_NAME}

* Contribuidores: {CONTRIBUTORS}
* Link: {DONATE_LINK}
* Tags: {TAGS}
* Testado até: {TESTED_UP}
* Versão estável: {VERSION}
* Licença: GPLv2 ou posterior
* URI da Licença: {LICENSE_URI}
* Traduções: Português (Brasil) / Inglês

{PRIMEIRO PARÁGRAFO DA DESCRIÇÃO}

## Descrição

{DESCRIÇÃO DO README.txt — preserve o texto real, apenas remova recursos que o plugin não possui.}

## Instalação

1. Baixe o plugin.
2. No painel administrativo do WordPress, vá em Plugins > Adicionar Novo.
3. Clique em "Enviar Plugin" e selecione o arquivo ZIP do plugin.
4. Clique em "Instalar Agora" e depois em "Ativar Plugin".
5. Certifique-se de que o WooCommerce também está ativado.

## CHANGELOG:

{BULLETS copiados da entrada mais recente do CHANGELOG.md. NÃO invente a partir do git log.}
```

O mesmo conteúdo alimenta o corpo da release do `.zip` (`.github/release-body-template.md`), então mantenha os dois alinhados.

### 5. Abrir o PR

Sempre `dev` → `main`:

```bash
gh pr create \
  --base main \
  --head dev \
  --title "VERSION - REPO_NAME (RESUMO_CURTO)" \
  --body "$(cat <<'EOF'
...corpo...
EOF
)"
```

### 6. Confirmar

Mostre a URL retornada pelo `gh` e o comando usado. Se o PR já existir para `dev` → `main`, `gh` vai avisar — não force `--force` sem pedir.

## Regras

- **Nunca** edite arquivos do repo para abrir o PR (é só `gh pr create`).
- Título SEMPRE `dev → main` no formato `VERSION - REPO_NAME (resumo)`.
- Corpo SEMPRE com Testado até, Versão estável e o resumo da versão.
- Se `version` ou `tested_up` estiverem divergindo entre header PHP e README.txt, use o **header PHP** e avise.
- Nunca inclua hashes de commit no corpo.
- Resumo e bullets do corpo SEMPRE vindos do changelog da versão atual — nunca do `git log`.
