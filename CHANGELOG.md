# 1.3.6 - 24/09/2026
* O captcha (Google reCAPTCHA ou Cloudflare Turnstile) agora é exibido na página de recibo, logo acima das notas do pedido.

# 1.3.5 - 22/09/2026
* Nova aba "Captcha": selecionar o provedor (Google reCAPTCHA ou Cloudflare Turnstile) ativa a verificação e revela as credenciais em cascata, sem sair da tela.
* Página de configurações reorganizada em 4 abas: Captcha, Data Blocking, Banned IPs e Blocked Data.
* Menu lateral atualizado com atalhos para Captcha e Data Blocking.
* Strings-fonte padronizadas em inglês para tradução no WordPress.org.

# 1.3.4 - 21/09/2026
* Segurança: o salvamento das configurações do antifraude é restrito às options do próprio plugin, impedindo que perfis de menor privilégio sobrescrevam options arbitrárias do WordPress.
* Segurança: ativar a verificação de segurança agora exige que as credenciais do provedor estejam preenchidas — um aviso na página de configurações leva direto à configuração do provedor — e o checkout passa a ser bloqueado (em vez de ignorar a verificação) quando um provedor mal configurado está sem credenciais.
* Novo menu lateral "AntiFraud" com atalhos para as abas de configurações, IPs banidos e bloqueio por dados.

# 1.3.3 - 21/09/2026
* Recursos de verificação de IP e de banimento por dados (e-mail/telefone) deixam de exigir a verificação de segurança ativa.
* Corrigido o salto de scroll ao alternar a opção de verificação de segurança na página de configurações.
* Removido script não utilizado das configurações do admin.

# 1.3.2 - 16/09/2026
* Antifraude com credenciais vazias não quebra mais o checkout: quando o CAPTCHA está ativo sem chaves configuradas, o plugin exibe um aviso neutro e pula a verificação em vez de bloquear o pedido.
* Melhorias de UX na página de configurações: avisos de credenciais ausentes nos campos e nas abas dos provedores (Google reCAPTCHA / Cloudflare Turnstile), com link para preenchimento.

# 1.3.1 - 08/07/2026
* Nova opção de banimento por número de telefone nas notas do pedido.

# 1.3.0 - 21/05/2026
* Novo sistema de banimento através dos dados.

# 1.2.1 - 20/05/2026
* Ajuste no estado padrão do checkbox na página de configurações.

# 1.2.0 - 18/05/2026
* Novo sistema de verificação de segurança com Cloudflare Turnstile.
* Novo sistema de banimento de IPs.

# 1.1.9/1.1.10 - 24/02/2026
* Novo banners de acordo com o país.

# 1.1.8 - 23/02/2026
* Novo layout das imagens do plugin.

# 1.1.7 - 02/01/2026
* Correção na URL do plugin.

# 1.1.6 - 19/09/2025
* Alteração em actions.

# 1.1.5 - 11/09/2025
* Correção em issues do wordpress.

# 1.1.4 - 04/09/2025
* Correção em issues do wordpress.

# 1.1.3 - 01/09/2025
* Removendo plugin updater.

# 1.1.2 - 07/07/2025
* Alteração do título do plugin.

# 1.1.1 - 19/03/2025
* Correção em actions do github.

# 1.1.0 - 14/03/2025
* Adição de compatibilidade com formulário por shortcode.

# 1.0.0 - 22/01/2025
* Lançamento de plugin;
* Adição de validação com reCAPTCHA.