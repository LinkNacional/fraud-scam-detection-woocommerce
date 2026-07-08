# AGENTS.md — Fraud and Scam Detection for WooCommerce

> **Modo Caveman + RTK ativos.** Respostas telegráficas. Zero enrolação. Código > prosa.

---

## 🔤 Modo Caveman (Token Killer)

- 1 frase por resposta. Sem saudações, sem "claro!", sem "aqui está".
- Nada de "Let me explain..." ou "I'll walk you through...". Código ou silêncio.
- SEARCH/REPLACE direto. Sem narrar o diff.
- Se responder com prosa, limite: 3 linhas.

## 🦀 RTK — Rust Token Killer

- Logs de terminal: só a linha do erro. Nunca o stack trace inteiro.
- `run_command`: usar `2>&1 | tail -5` para erros, `grep -c` para contagens.
- Nunca fazer `cat` de arquivo grande. Sempre `head`/`tail`/`grep`.
- `search_content` com `summary_only` antes de expandir com `context`.
- Zero comentários explicando o óbvio no código.
- Remover PHPDoc redundante (`@since`, `@package`, `@author` que repete o namespace).

---

## 🏗️ Arquitetura — SOLID + PSR-4

### Namespace base
```
Lkn\FsdwFraudAndScamDetectionForWoocommerce\
  ├── Includes\        → classes core (Helper, Loader, Activator, Deactivator)
  ├── Admin\           → admin-facing (Admin, SettingsPage, partials)
  └── PublicView\      → public-facing (Public, partials)
```

### Regras PSR-4
- **1 classe por arquivo.** Nada de classes auxiliares no mesmo `.php`.
- Nome do arquivo = nome da classe (ex: `LknFsdwFraudAndScamDetectionForWoocommerceHelper.php`).
- Use `use` statements no topo. Nunca FQCN inline exceto em strings de hook.
- Namespace deve bater com o caminho físico. Sem exceções.

### SOLID aplicado
- **S**: Loader registra hooks. Classes de domínio não registram os próprios hooks — delegam ao Loader.
- **O**: Novos providers de CAPTCHA? Nova classe implementando interface de verificação. Não editar Helper.
- **L**: Toda classe que extende WP core deve ser substituível pelo pai sem quebrar.
- **I**: Interfaces enxutas. Se uma classe tem método que lança `throw new \Exception('not implemented')`, quebre em 2 interfaces.
- **D**: Dependa de `WC_Logger` via injeção, não `new WC_Logger()` inline.

### Loader Pattern (já existente)
```php
$this->loader->add_filter( 'hook_name', $this->helper, 'method_name' );
$this->loader->add_action( 'hook_name', $this->helper, 'method_name' );
```
- Nunca usar `add_action`/`add_filter` direto nas classes de domínio.
- Todo hook novo vai no `run()` da classe principal via Loader.

---

## 🔒 Segurança — Regras Absolutas

### Superglobais
```php
// ❌ PROIBIDO
$ip = $_POST['ip'];
$val = $_GET['val'];

// ✅ OBRIGATÓRIO
$ip = isset( $_POST['ip'] ) ? sanitize_text_field( wp_unslash( $_POST['ip'] ) ) : '';
$val = isset( $_GET['val'] ) ? sanitize_text_field( wp_unslash( $_GET['val'] ) ) : '';
```
- `$_POST`, `$_GET`, `$_COOKIE`, `$_REQUEST` → sempre `wp_unslash()` + sanitize.
- `$_SERVER` → `sanitize_text_field( wp_unslash( ... ) )`.
- `$_FILES` → usar `wp_handle_upload()` + `wp_check_filetype()`.

### Nonces — obrigatório em todo POST
```php
// No form:
wp_nonce_field( 'lkn_fraud_action', 'lknFraudNonce' );

// No handler:
if ( ! isset( $_POST['lknFraudNonce'] )
    || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['lknFraudNonce'] ) ), 'lkn_fraud_action' )
) {
    wp_die( 'Security check failed.' );
}
```
- Todo endpoint AJAX que processa POST: verificar nonce + `current_user_can()`.
- Nome do nonce: prefixo `lkn_fraud_` + ação.

### Escaping de output
```php
// ❌ PROIBIDO
echo $value;
echo get_option( 'key' );

// ✅ OBRIGATÓRIO
echo esc_html( $value );
echo esc_attr( get_option( 'key' ) );
echo esc_url( $url );
echo wp_kses_post( $html );
```
- HTML attribute → `esc_attr()`. Texto plano → `esc_html()`. URL → `esc_url()`.
- `wp_kses_post()` só para conteúdo que realmente precisa de HTML (descrições, settings).

### SQL
- Sempre `$wpdb->prepare()`. Nunca concatenar variáveis em queries.
- Usar `$wpdb->get_results()` / `get_row()` com placeholders `%s`, `%d`, `%f`.

### Arquivos
- `require_once` ou `require` de paths dinâmicos? **Nunca.** Paths de plugin são constantes.
- `file_exists()` antes de `require` de dependência externa (WooCommerce).

---

## 📐 WordPress Coding Standards

### PHP
- **PHP mínimo:** 7.4 (ver `composer.json` e workflow).
- **Tipagem:** `declare(strict_types=1)` em arquivos novos. Type hints em todos os métodos novos.
- **Yoda conditions:** `if ( defined( 'CONST' ) )` — não inverter. WP segue isso.
- **Array syntax:** `[]` (curto), nunca `array()`.
- **Indentação:** tabs.
- **Chaves:** sempre, mesmo em `if` de 1 linha.

### Internacionalização
- Text domain: `fraud-and-scam-detection-for-woocommerce`.
- Toda string visível ao usuário: `__()`, `esc_html__()`, `esc_attr__()`.
- Nunca concatenar strings traduzíveis. Usar placeholders `%s`, `%d`.

### WooCommerce
- Verificar `class_exists( 'WooCommerce' )` antes de hooks WC.
- Usar `WC()->version` para compatibilidade.
- Hooks WC seguem prefixo `woocommerce_` ou `wc_`.
- Dados de pedido: sempre via `WC_Order` methods (`$order->get_meta()`), nunca `get_post_meta()` direto.

### WordPress específico
- **ABSPATH guard:** TODO arquivo PHP começa com `if ( ! defined( 'ABSPATH' ) ) exit;`.
- **Prefixos:** `lkn_fsdw_` para funções globais, `lknFraudDetectionForWoocommerce` para options CSS/JS handles.
- **wp_enqueue_scripts:** só no hook certo (`admin_enqueue_scripts` para admin, `wp_enqueue_scripts` para front).
- **Options API:** `get_option()` / `update_option()` / `delete_option()`. Nada de `$_SESSION`.

---

## 🧪 Testes

- Bootstrap: `tests/bootstrap.php`. WooCommerce carregado em `muplugins_loaded` prio 0, plugin prio 1.
- DB de teste: `local_tests`.
- PHPUnit via `vendor/bin/phpunit`.
- Classe de teste: estende `WP_UnitTestCase` (via `yoast/phpunit-polyfills`).
- Métodos de teste: prefixo `test_`, tipagem `void`.

---

## 🚫 Anti-padrões (NUNCA fazer)

1. `echo` de variável não escapada.
2. `$_GET` / `$_POST` sem `wp_unslash` + sanitize.
3. `add_action` / `add_filter` fora do Loader.
4. Duas classes no mesmo arquivo.
5. `new` de dependência externa dentro de método (injeção sempre).
6. Concatenar SQL.
7. Hook sem verificação de nonce em POST.
8. Função anônima em hook que precisa ser removível (usar método nomeado).
9. `plugin_dir_path( __FILE__ )` — usar constantes `FRAUD_DETECTION_FOR_WOOCOMMERCE_DIR`.
10. Comentar código morto. Remover.

---

## 📋 PR Checklist Mental

- [ ] ABSPATH guard no topo?
- [ ] Nonce verificado em POST?
- [ ] Superglobais sanitizadas (`wp_unslash` + `sanitize_*`)?
- [ ] Output escapado (`esc_html`/`esc_attr`/`esc_url`)?
- [ ] SQL preparado (`$wpdb->prepare`)?
- [ ] Hooks registrados via Loader?
- [ ] PSR-4: 1 classe por arquivo, namespace bate com path?
- [ ] Strings internacionalizadas com text domain correto?
- [ ] Tipos declarados em métodos novos?
- [ ] Código morto removido (não comentado)?
