# Plugin WordPress para captura de leads — F10 Software + Brevo

O **F10 Lead Capture** é um plugin WordPress para criar formulários de captação de leads, registrar os contatos no banco de dados do site e encaminhá-los para o **F10 Software**. Opcionalmente, o plugin envia uma notificação de novo lead por e-mail usando a API transacional do Brevo.

Ele foi desenvolvido para sites, blogs e landing pages de escolas, cursos livres, escolas de idiomas, cursos técnicos, escolas profissionalizantes e redes de franquias educacionais que precisam integrar a geração de oportunidades ao processo comercial.

## Para que serve

O plugin resolve quatro pontos importantes da captura de leads no WordPress:

1. Exibe um formulário responsivo por shortcode.
2. Salva o lead localmente antes de chamar serviços externos.
3. Envia o contato para a API da F10 usando autenticação Bearer JWT.
4. Pode avisar a equipe comercial por e-mail através do Brevo.

Mesmo quando uma integração externa apresenta instabilidade, o contato permanece armazenado no WordPress e pode ser reenviado posteriormente.

## Sobre a F10 Software

A [F10 Software](https://f10.com.br/) oferece tecnologia para gestão escolar, captação de alunos, atendimento comercial, financeiro, pedagógico e comunicação entre escolas, alunos e responsáveis.

O [F10 CRM Escolar](https://f10.com.br/solucoes/crm-escolar) centraliza leads, conversas, tarefas, notificações, histórico comercial e próximas ações para ajudar escolas a responder mais rápido e aumentar matrículas.

- [Conheça a F10 Software](https://f10.com.br/)
- [Veja o CRM Escolar da F10](https://f10.com.br/solucoes/crm-escolar)
- [Solicite uma demonstração](https://f10.com.br/contato)
- [Conteúdos sobre gestão escolar](https://blog.f10.com.br/)

## Principais recursos

- Formulário com nome, WhatsApp, e-mail e escola ou empresa.
- Shortcode configurável para páginas, posts e landing pages.
- Armazenamento em tabela própria do WordPress antes do envio externo.
- Integração com a API da F10 usando o payload atual de leads.
- Notificação opcional por e-mail transacional via Brevo.
- Registro automático da página de captura, URL de referência e parâmetros UTM.
- Painel administrativo com histórico, filtros e detalhes técnicos.
- Exportação de leads em CSV com proteção contra fórmulas de planilha.
- Reenvio manual de integrações com falha.
- Novas tentativas automáticas via WP-Cron.
- Proteção com nonce, honeypot, rate limit e bloqueio de múltiplos envios.
- Armazenamento do IP somente como hash para controle de abuso.
- Consentimento de privacidade configurável.

## Requisitos

- WordPress 6.2 ou superior.
- PHP 7.4 ou superior.
- Credenciais válidas da integração F10 para envio ao sistema.
- Conta Brevo com remetente autorizado, somente quando a notificação por e-mail estiver ativada.

O requisito mínimo do WordPress foi definido como 6.2 porque o plugin utiliza o placeholder `%i` do `$wpdb->prepare()` para preparar identificadores de tabelas com segurança.

## Instalação

1. Baixe o repositório em ZIP ou copie os arquivos para `/wp-content/plugins/f10-lead-capture/`.
2. Ative **F10 Lead Capture** no painel do WordPress.
3. Acesse **Leads F10 → Configurações**.
4. Informe a URL da API F10, o token JWT, o ID da unidade, a fonte e a mídia.
5. Para receber notificações, marque **Enviar e-mail quando gerar um lead?** e configure o Brevo.
6. Insira o shortcode em um bloco **Shortcode** do WordPress.

## Uso do shortcode

### Formulário padrão

```text
[f10_lead_form]
```

### Formulário personalizado

```text
[f10_lead_form title="Receba uma demonstração" button="Quero uma demonstração" product="Sistema de gestão escolar" source="Blog F10" sub_source="Artigo"]
```

### Ocultar o campo de escola ou empresa

```text
[f10_lead_form show_institution="no"]
```

### Atributos disponíveis

| Atributo | Finalidade |
|---|---|
| `title` | Título exibido no formulário. |
| `description` | Texto complementar abaixo do título. |
| `button` | Texto do botão de envio. |
| `product` | Produto, curso ou interesse associado ao lead. |
| `form_id` | Identificador interno do formulário. |
| `source` | Origem descritiva do contato. |
| `sub_source` | Suborigem ou campanha. |
| `show_institution` | Exibe o campo de escola ou empresa: `yes` ou `no`. |
| `redirect_url` | URL interna ou permitida para redirecionamento após o sucesso. |

## Dados enviados para a F10

O plugin envia o lead com a estrutura esperada pela integração F10, incluindo:

- ID da unidade.
- Fonte e mídia.
- Nome.
- Telefone e celular.
- E-mail.
- Escola ou empresa.
- Produto ou interesse.
- Página de captura.
- Origem, suborigem, UTMs e contexto do formulário.

A requisição utiliza `Authorization: Bearer <JWT>`.

## Persistência e recuperação

Cada contato é gravado na tabela `{prefix}_f10_leads` antes das chamadas externas. O histórico mantém o resultado de cada integração, resposta HTTP, erro, quantidade de tentativas e data prevista para a próxima tentativa.

Integrações que já retornaram sucesso não são executadas novamente durante um reenvio.

## Segurança e privacidade

- As configurações ficam disponíveis somente para usuários com permissão administrativa.
- Tokens e chaves de API são exibidos como campos de senha e não são devolvidos ao navegador durante a edição.
- O formulário usa nonce e honeypot.
- O rate limit reduz envios automatizados e repetitivos.
- O endereço IP não é salvo em texto puro.
- A remoção dos dados durante a desinstalação é opcional e desativada por padrão.
- As consultas da tabela própria usam parâmetros preparados e identificadores seguros.

Nunca publique tokens JWT, chaves do Brevo ou credenciais reais no repositório.

## Estrutura do projeto

```text
assets/
  css/form.css
  js/form.js
includes/
  admin/
    trait-f10-lead-capture-admin-leads.php
    trait-f10-lead-capture-admin-settings.php
  class-f10-lead-capture-activator.php
  class-f10-lead-capture-admin.php
  class-f10-lead-capture-deactivator.php
  class-f10-lead-capture-form.php
  class-f10-lead-capture-integrations.php
  class-f10-lead-capture-plugin.php
  class-f10-lead-capture-repository.php
f10-lead-capture.php
readme.txt
uninstall.php
```

## Validação para WordPress.org

A versão `1.0.3` inclui ajustes específicos para o Plugin Check:

- consultas SQL preparadas;
- identificadores de tabela com `%i`;
- cache e invalidação para consulta individual de leads;
- exportação CSV sem operações diretas de arquivo;
- leitura de parâmetros administrativos sem avisos de nonce;
- `readme.txt` integralmente em inglês;
- pacote sem arquivos ocultos.

## Suporte comercial F10

Para conhecer o software de gestão escolar, CRM, WhatsApp integrado e demais soluções, acesse [f10.com.br](https://f10.com.br/) ou envie uma solicitação pela [página de contato](https://f10.com.br/contato).

## Licença

GPL-2.0-or-later.
