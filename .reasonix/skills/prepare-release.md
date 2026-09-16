---
name: prepare-release
description: Prepara release: atualiza todos os locais de versão (cabeçalho PHP, constante, README.txt/Changelog/Upgrade Notice, CHANGELOG.md, workflows). Changelog baseado nos highlights do usuário (git log é só dica).
---

# prepare-release

Atualiza **todos** os arquivos que contêm o número de versão para uma nova release do plugin.

## Parâmetros (via `arguments`)

O usuário pode passar os valores diretamente: `"version=1.4.0 tested_up=7.1 php=8.2 highlights=Correção de bug X"`. Se algum valor faltar, pergunte.

- **version** — nova versão (Stable tag)
- **tested_up** — versão do WP testada (Tested up to)
- **php** — versão mínima do PHP (Requires PHP)
- **highlights** — resumo da versão (itens de mudança). É a **fonte primária** do changelog. Se vazio, **pergunte obrigatoriamente** ao usuário o que mudou — NÃO invente a partir do git log.

## Fluxo de execução

### 1. Coletar valores
Se não recebidos via arguments, pergunte ao usuário um por um. Detecte a versão atual via grep no `.php` raiz:
```
grep -E "Version:|Requires PHP:" fraud-scam-detection-woocommerce.php
```

### 2. Levantar o contexto das mudanças (changelog)

⚠️ **Regra anti-redundância.** O changelog NUNCA deve listar itens que já pertencem a versões anteriores. Antes de escrever qualquer entrada:

1. **Pergunte ao usuário** o que mudou nesta versão (se `highlights` não veio nos arguments). A resposta dele é a fonte da verdade.
2. O `git log` é **apenas uma dica** para o usuário lembrar — não gera os bullets sozinho. O range de commits costuma estar dessincronizado (tags antigas/ausentes, branches de beta) e pode trazer commits de releases já publicadas.
3. **Leia o topo do changelog atual** (`CHANGELOG.md`) e **descarte** qualquer item já descrito nas entradas anteriores.
4. Escreva os bullets **somente** com o que o usuário confirmou como novo.

```bash
# dica opcional — jamais usar como fonte única
LAST_TAG=$(git describe --tags --abbrev=0 2>/dev/null)
if [ -z "$LAST_TAG" ]; then
    git log -n 10 --oneline
else
    git log ${LAST_TAG}..HEAD --oneline
fi
```

### 3. Atualizar TODOS os arquivos com versão

A versão aparece em **8 locais** espalhados por **6 arquivos**. Atualize todos:

#### 3a. `fraud-scam-detection-woocommerce.php` (header do plugin)
- `* Version:            NOVA_VERSION`

#### 3b. `fraud-scam-detection-woocommerce-file.php` (constante)
- `define('FRAUD_DETECTION_FOR_WOOCOMMERCE_VERSION', 'NOVA_VERSION');`

#### 3c. `README.txt`
- `Stable tag: NOVA_VERSION`
- `Tested up to:` e `Requires PHP:` se alterados
- Adicionar entrada na seção `== Changelog ==` (topo), **em inglês**, no formato do arquivo (sem data, `= VERSION =`):
  ```
  == Changelog ==
  = NOVA_VERSION =
  * Item (escrito a partir do `highlights` do usuário, NÃO do git log)

  = VERSAO_ANTERIOR =
  ```
- Adicionar a mesma entrada na seção `== Upgrade Notice ==` (topo):
  ```
  == Upgrade Notice ==
  = NOVA_VERSION =
  * Item curto da versão

  = VERSAO_ANTERIOR =
  ```

#### 3d. `CHANGELOG.md` (português, com data)
- Adicionar entrada no topo, usando a **data de hoje** (`date +%d/%m/%Y`). Formato do arquivo: `# VERSION - DD/MM/AAAA`:
  ```
  # NOVA_VERSION - DD/MM/AAAA
  * Item (a partir do `highlights` do usuário, NÃO do git log)

  # VERSAO_ANTERIOR - DD/MM/AAAA
  ```

#### 3e. `.github/workflows/main.yml`
- `DEPLOY_TAG: "NOVA_VERSION"`

#### 3f. `.github/workflows/wordpressRelease.yml`
- `DEPLOY_TAG: "NOVA_VERSION"`
- `SLUG` permanece `fraud-and-scam-detection-for-woocommerce` (não é versão — não mexer)

#### 3g. `Includes/LknFsdwFraudAndScamDetectionForWoocommerce.php` — fallback
- Fallback estático em `$this->version = '1.0.0';` (branch quando a constante não está definida). Historicamente **não** é bumpado a cada release. Só altere se o usuário pedir.

> Não existe `README.md` versionado neste plugin, nem array `$old_versions`, nem asserção de versão nos testes.

### 4. Validação final
Rodar grep com a versão **antiga** para confirmar que não restou ocorrência fora do esperado:
```
grep -rn "VERSAO_ANTIGA" --include="*.php" --include="*.md" --include="*.txt" --include="*.yml" .
```
Esperado: `CHANGELOG.md` e `README.txt` ainda contêm a versão antiga **apenas** nas entradas antigas de Changelog/Upgrade Notice (isso é correto). Qualquer outro arquivo retornando a versão antiga é **erro**.

Depois, grep com a versão **nova**:
```
grep -rn "NOVA_VERSAO" --include="*.php" --include="*.md" --include="*.txt" --include="*.yml" .
```
Deve retornar **8+** matches nos 6 arquivos (múltiplas entradas no changelog do `README.txt` são normais).

### 5. Alinhamento com o corpo da release
O corpo das GitHub Releases é gerado por `.github/scripts/generate-release-body.sh` a partir de `README.txt` + `CHANGELOG.md`. Não é preciso editá-lo: ele lê `Stable tag`, `Tested up to` e a entrada correspondente do `CHANGELOG.md` automaticamente.
