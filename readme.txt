=== F10 Lead Capture ===
Contributors: f10software
Tags: captura de leads, formulario wordpress, crm escolar, gestao escolar, brevo
Requires at least: 6.0
Requires PHP: 7.4
Tested up to: 7.0
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin WordPress para capturar leads, salvar no banco e integrar formulários ao CRM da F10 Software e ao Brevo.

== Descrição ==

O F10 Lead Capture transforma páginas, posts e landing pages WordPress em pontos seguros de geração de leads.

O formulário coleta nome, WhatsApp, e-mail e, opcionalmente, o nome da escola ou empresa. Antes de qualquer chamada externa, o contato é salvo em uma tabela própria do WordPress. Em seguida, o plugin pode enviar os dados para a API da F10 Software e notificar a equipe comercial por e-mail via Brevo.

A F10 Software oferece soluções para gestão escolar, CRM escolar, captação de alunos, atendimento comercial, financeiro, pedagógico e comunicação com alunos e responsáveis.

Conheça a F10 Software: https://f10.com.br/
Conheça o F10 CRM Escolar: https://f10.com.br/solucoes/crm-escolar
Solicite uma demonstração: https://f10.com.br/contato
Conteúdos sobre gestão escolar: https://blog.f10.com.br/

= Para que serve =

* Capturar leads em páginas e artigos do WordPress.
* Integrar formulários de escolas e negócios educacionais ao F10 Software.
* Manter uma cópia local para evitar perda de contatos.
* Identificar a página, campanha, origem e parâmetros UTM da conversão.
* Avisar o comercial sobre novos leads usando o Brevo.
* Reenviar contatos quando uma integração externa falhar.

= Recursos =

* Formulário responsivo por shortcode.
* Campos de nome, WhatsApp, e-mail e escola/empresa.
* Registro do lead no banco antes de qualquer chamada externa.
* Integração com o payload atual da F10 usando Bearer JWT.
* Notificação opcional por e-mail transacional via Brevo.
* Captura automática da página, referência e parâmetros UTM.
* Histórico administrativo, filtros, detalhes e exportação CSV.
* Reenvio manual e tentativas automáticas por WP-Cron.
* Honeypot, nonce, limite de tentativas e hash do IP.
* Consentimento de privacidade configurável.

== Instalação ==

1. Envie a pasta `f10-lead-capture` para `/wp-content/plugins/` ou instale o arquivo ZIP pelo painel.
2. Ative o plugin F10 Lead Capture.
3. Acesse `Leads F10 > Configurações`.
4. Informe URL da API, JWT, ID da unidade, fonte e mídia.
5. Para notificações, marque "Enviar e-mail quando gerar um lead?" e informe chave Brevo, destinatário e remetente verificado.
6. Insira `[f10_lead_form]` em um bloco Shortcode de uma página ou post. Não use o bloco HTML personalizado.

== Shortcode ==

Uso básico:

`[f10_lead_form]`

Uso personalizado:

`[f10_lead_form title="Receba uma demonstração" button="Quero uma demonstração" product="Sistema de gestão escolar" source="Blog F10" sub_source="Artigo"]`

Atributos:

* `title`: título do bloco.
* `description`: texto abaixo do título.
* `button`: texto do botão.
* `product`: valor enviado ao campo curso/produto.
* `form_id`: identificador interno do formulário.
* `source`: origem descritiva do lead.
* `sub_source`: suborigem descritiva.
* `show_institution`: `yes` ou `no`.
* `redirect_url`: URL permitida para redirecionar após sucesso.

== Integração com a F10 Software ==

O plugin envia a unidade, fonte, mídia e os dados da digitação para a API configurada. Nome, telefone, celular, e-mail, escola, produto, página e informações da campanha são organizados no formato esperado pela integração F10.

A autenticação utiliza o cabeçalho `Authorization: Bearer <JWT>`.

Uma conta e credenciais válidas da F10 são necessárias para usar essa integração.

== Persistência e reenvio ==

O lead é inserido na tabela `{prefixo}_f10_leads` antes das integrações. Falhas ficam registradas com resposta HTTP, erro, quantidade de tentativas e próxima tentativa. Integrações que já retornaram sucesso não são enviadas novamente.

== Brevo ==

O envio usa `POST https://api.brevo.com/v3/smtp/email` com autenticação pelo cabeçalho `api-key`. O e-mail remetente deve estar autorizado na conta Brevo.

== Serviços externos ==

Este plugin pode se conectar a dois serviços externos. Nenhum dado é enviado até que um administrador habilite e configure a integração correspondente.

= F10 Software API =

Quando a integração F10 está habilitada, o plugin envia à URL da API configurada pelo administrador os seguintes dados do lead: nome, WhatsApp, e-mail, escola ou empresa, produto ou interesse, página de captura, página de referência, origem, suborigem, parâmetros UTM e data de criação. Esses dados são enviados para registrar o contato no sistema F10 Software.

O envio ocorre após o visitante enviar o formulário e também pode ocorrer novamente durante uma tentativa manual ou automática de reenvio de uma integração que falhou. Uma conta F10 e credenciais válidas são necessárias.

* Site do serviço: https://f10.com.br/
* Termos de uso: https://f10.com.br/termos-de-uso
* Política de privacidade: https://f10.com.br/politica-de-privacidade

= Brevo Transactional Email API =

Quando a notificação por Brevo está habilitada, o plugin envia à Brevo o nome, WhatsApp, e-mail, escola ou empresa, produto ou interesse, página de captura, página de referência, origem, suborigem, parâmetros UTM e data de criação. Esses dados são usados para montar e enviar uma notificação por e-mail ao destinatário definido pelo administrador.

O envio ocorre após o visitante enviar o formulário e também pode ocorrer novamente durante uma tentativa manual ou automática de reenvio de uma integração que falhou. Uma conta Brevo, uma chave de API e um remetente autorizado são necessários.

* Site do serviço: https://www.brevo.com/
* Termos de serviço: https://www.brevo.com/legal/termsofuse/
* Política de privacidade: https://www.brevo.com/legal/privacypolicy/

== Privacidade ==

O endereço IP não é salvo em texto puro. O plugin armazena apenas um hash HMAC para controle de abuso. A exclusão de dados na desinstalação é opcional e permanece desativada por padrão.

== Perguntas frequentes ==

= O lead pode ser perdido se a API estiver indisponível? =

O contato é armazenado no WordPress antes da tentativa de envio. Falhas podem ser reenviadas manualmente ou pelo processo automático do plugin.

= Preciso usar o Brevo? =

Não. A notificação por e-mail é opcional e pode permanecer desativada.

= Preciso ser cliente da F10 Software? =

Para enviar leads à API F10, é necessário possuir credenciais válidas fornecidas para a integração. O armazenamento local e o formulário continuam disponíveis conforme a configuração do plugin.

= O plugin captura UTMs? =

Sim. UTM Source, UTM Medium, UTM Campaign, UTM Term e UTM Content são registrados quando estiverem presentes na URL.

= Posso usar mais de um formulário? =

Sim. O shortcode pode ser usado várias vezes com diferentes valores de `form_id`, `product`, `source` e `sub_source`.

== Changelog ==

= 1.0.2 =

* Nome público padronizado para F10 Lead Capture.
* Cabeçalho de licença GPL adicionado.
* Cabeçalho de atualização externa removido para compatibilidade com o WordPress.org.
* Compatibilidade declarada até o WordPress 7.0.
* Serviços externos e dados enviados documentados.
* Arquivos ocultos removidos do pacote de distribuição.

= 1.0.1 =

* Metadados e documentação pública otimizados para F10 Software, CRM escolar, captura de leads e Brevo.

= 1.0.0 =

* Primeira versão pública.
* Formulário por shortcode.
* Persistência local dos leads.
* Integração com a API F10.
* Notificação opcional pelo Brevo.
* Histórico, exportação e reenvio.
