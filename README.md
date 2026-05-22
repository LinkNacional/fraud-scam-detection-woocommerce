# Fraud and Scam Detection For WooCommerce AntiFraude

Plugin para [WooCommerce](https://www.linknacional.com.br/wordpress/woocommerce/) focado em prevenção de fraudes e golpes durante o checkout, adicionando validação [antifraude](https://www.linknacional.com.br/wordpress/woocommerce/antifraude/) com Google reCAPTCHA v3 ou Cloudflare Turnstile, além de recursos de bloqueio por IP e monitoramento administrativo.

## Visão geral

O plugin **Fraud and Scam Detection For WooCommerce** adiciona uma camada extra de proteção ao processo de compra da loja no [WordPress](https://www.linknacional.com.br/wordpress/) , ajudando a identificar comportamentos suspeitos e dificultar tentativas automatizadas ou abusivas no checkout.

Entre os recursos disponíveis, o plugin permite:

- proteger o checkout com validação antifraude;
- escolher entre **Google reCAPTCHA v3** e **Cloudflare Turnstile**;
- configurar pontuação mínima para validação via reCAPTCHA v3;
- bloquear IPs suspeitos manualmente com duração configurável (horas, dias, semanas, meses, anos ou permanente);
- impedir compras de IPs já banidos;
- definir com precisão como o sistema deve reagir ao detectar uma fraude (bloquear pedido, marcar como fraude e/ou adicionar nota);
- bloquear pedidos por e-mail, domínio de e-mail, telefone, país ou identidade de dispositivo;
- registrar ocorrências e respostas dos provedores em logs de depuração;
- marcar pedidos suspeitos com um status específico de fraude no WooCommerce.

## Principais recursos

### 1. Proteção antifraude no checkout
O plugin adiciona verificações de segurança no checkout do WooCommerce para dificultar compras automatizadas, abusivas ou potencialmente fraudulentas.

### 2. Integração com Google reCAPTCHA v3
Suporte ao Google reCAPTCHA v3 com configuração de:

- chave do site;
- chave secreta;
- score mínimo de aprovação.

Com isso, o plugin pode avaliar o risco da interação do usuário antes de permitir a finalização do pedido.

### 3. Integração com Cloudflare Turnstile
Também é possível usar o **Cloudflare Turnstile** como provedor de validação antifraude no checkout.

Configurações disponíveis:

- site key;
- secret key;
- tema do widget (`auto`, `light` ou `dark`).

### 4. Escolha do provedor de segurança
Na área administrativa do WooCommerce, o lojista pode selecionar qual serviço deseja usar:

- **Nenhum**;
- **Google reCAPTCHA v3**;
- **Cloudflare Turnstile**.

### 5. Comportamento antifraude configurável
Ao detectar uma tentativa de fraude, o lojista pode controlar exatamente o que acontece com o pedido. As ações são independentes e podem ser combinadas:

- **Bloquear pedido** — impede que o pedido seja finalizado, devolvendo uma mensagem de erro ao cliente;
- **Marcar como fraude** — altera o status do pedido para o status personalizado de fraude, permitindo revisão manual sem bloquear a transação;
- **Adicionar nota ao pedido** — registra uma nota interna com detalhes da detecção sem realizar nenhuma outra ação.

Essa flexibilidade permite que o lojista adote uma postura mais conservadora (apenas registrar) ou mais restritiva (bloquear imediatamente), de acordo com o perfil da loja.

### 6. Bloqueio e gerenciamento de IPs banidos com duração configurável
O sistema de banimento de IPs foi aprimorado para suportar banimentos temporários ou permanentes. Ao banir um IP, o lojista define:

- **Duração** — valor numérico que, combinado com a unidade, determina por quanto tempo o banimento permanece ativo;
- **Unidade** — horas, dias, semanas, meses, anos ou permanente.

Quando a unidade **Permanente (Forever)** é selecionada, o campo de duração é desativado automaticamente e o IP permanece banido indefinidamente. Banimentos temporários expiram automaticamente sem necessidade de intervenção manual.

Demais recursos da gestão de IPs:

- banir IP diretamente da página de detalhes do pedido;
- remover banimento de IP;
- listar todos os IPs banidos com suas datas de expiração;
- filtrar pedidos por IP para identificar padrões de uso;
- impedir checkout quando o IP estiver dentro do período de banimento.

### 7. Compatibilidade com diferentes fluxos de checkout
A validação antifraude é aplicada em diferentes contextos do WooCommerce, incluindo:

- checkout clássico;
- checkout baseado em blocos / Store API.

### 8. Status personalizado de pedido: Fraud
Quando uma tentativa é considerada inválida ou suspeita, o plugin pode alterar o pedido para um status personalizado de fraude.

### 9. Notas internas no pedido
No caso do Google reCAPTCHA v3, o plugin adiciona notas ao pedido com informações sobre o score retornado e uma interpretação do nível de risco detectado.

### 10. Logs de depuração
O plugin possui uma opção de **debug** para registrar informações técnicas no log do WooCommerce, auxiliando em testes, integração e diagnóstico de problemas.

### 11. Área de configuração no WooCommerce
O plugin adiciona uma aba própria de configurações nas opções do WooCommerce para centralizar os controles antifraude.

## Requisitos

- PHP 7.4+
- WordPress 6.0+
- WooCommerce instalado e ativo

## Instalação

1. Acesse o painel administrativo do WordPress.
2. Vá até **Plugins > Adicionar novo**.
3. Clique em **Enviar plugin**.
4. Faça upload do arquivo `.zip` do plugin.
5. Clique em **Instalar agora**.
6. Ative o plugin após a instalação.

## Configuração

1. Vá para **WooCommerce > Configurações**.
2. Acesse a aba **Antifraude**.
3. Ative a proteção antifraude.
4. Escolha o provedor de segurança:
   - Google reCAPTCHA v3; ou
   - Cloudflare Turnstile.
5. Informe as credenciais correspondentes ao provedor selecionado.
6. Caso utilize Google reCAPTCHA v3, defina a pontuação mínima desejada.
7. Se necessário, ative a verificação e o gerenciamento de IPs.
8. Opcionalmente, habilite o modo de depuração.
9. Clique em **Salvar**.

## Fluxo básico de funcionamento

1. O cliente acessa o checkout da loja.
2. O plugin carrega o provedor antifraude configurado.
3. Durante o processo de finalização do pedido, a validação é executada.
4. O plugin verifica se o IP do cliente está bloqueado e se o banimento ainda está vigente (para banimentos temporários).
5. Caso a validação falhe ou o IP/dado esteja banido, o sistema aplica o comportamento configurado: bloquear o pedido, marcar como fraude e/ou adicionar nota ao pedido.
6. Quando aplicável, informações adicionais são registradas nas notas do pedido e nos logs.

## Casos de uso

Este plugin pode ser útil para lojas que desejam:

- reduzir tentativas automatizadas de compra;
- adicionar uma barreira adicional contra bots;
- monitorar comportamentos suspeitos em pedidos;
- bloquear IPs reincidentes;
- melhorar o controle operacional sobre transações potencialmente fraudulentas.

## Observações importantes

- O plugin depende do **WooCommerce** para funcionar.
- Para que a identificação de IP funcione corretamente, a infraestrutura do servidor/proxy deve estar configurada adequadamente.
- O uso de serviços externos como **Google reCAPTCHA** e **Cloudflare Turnstile** pode exigir políticas de privacidade e termos apropriados na loja.

## Suporte e manutenção

Antes de utilizar em produção, recomenda-se:

- testar em ambiente de homologação;
- validar o comportamento do checkout clássico e do checkout por blocos;
- revisar as configurações de IP/proxy do servidor;
- acompanhar os logs de depuração durante a implantação inicial.

## Licença

GPL-2.0+
