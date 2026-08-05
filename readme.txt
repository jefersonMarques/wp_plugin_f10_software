=== F10 Lead Capture ===
Contributors: rafamarques, f10software
Tags: captura de leads, formulário de contato, whatsapp, crm escolar, gestão escolar
Requires at least: 6.2
Requires PHP: 7.4
Tested up to: 7.0
Stable tag: 1.3.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Crie formulários e botões de WhatsApp para captar leads, armazenar contatos e integrar o WordPress com a F10 Software e o Brevo.

== Description ==

O F10 Lead Capture permite criar formulários de captação de leads e botões flutuantes de WhatsApp para páginas, posts, landing pages e campanhas educacionais no WordPress.

Cada formulário pode ter título, descrição, botão, mensagem de sucesso, campos, regras de obrigatoriedade, origem, produto e ação pós-conversão próprios.

Os widgets flutuantes de WhatsApp podem ser exibidos no site inteiro, em conteúdos específicos ou em categorias de posts. Antes de abrir a conversa, o visitante informa nome e WhatsApp, o lead é armazenado localmente e a conversa configurada pode ser iniciada.

Os leads são armazenados no WordPress antes da execução das integrações externas. Administradores podem visualizar, filtrar, exportar, excluir e reenviar integrações com falha.

O plugin foi desenvolvido para escolas, cursos livres, escolas de idiomas, instituições de ensino e empresas que precisam organizar a captação comercial. Para uma operação acadêmica, financeira e comercial completa, conheça o [sistema de gestão escolar da F10](https://f10.com.br/sistema-de-gestao-escolar).

= Principais recursos =

* Múltiplos formulários com configurações independentes.
* Shortcodes gerados para cada formulário salvo.
* Campos configuráveis de nome, curso, telefone, WhatsApp, e-mail, escola ou empresa e observações.
* Botões flutuantes de WhatsApp com textos voltados à captação educacional.
* Segmentação do WhatsApp para o site inteiro, conteúdos específicos ou categorias.
* Posição à esquerda ou à direita, quatro efeitos visuais, cor, selo e atraso de exibição.
* Horários semanais de atendimento com comportamento online e offline.
* Pré-visualização ao vivo do widget de WhatsApp no painel do WordPress.
* Rótulos e regras de obrigatoriedade independentes por formulário.
* Quatro modelos visuais prontos para formulários.
* Controles responsivos para desktop e dispositivos móveis.
* Pós-conversão com confirmação, download da Biblioteca de Mídia, link de destino ou abertura do WhatsApp.
* Armazenamento local antes do envio para serviços externos.
* Integração opcional com a API da F10 Software.
* Notificação opcional por e-mail transacional via Brevo.
* Captura de parâmetros UTM, URL da página e referenciador.
* Histórico de leads, exportação CSV, tentativas de reenvio, honeypot, nonce e limitação de requisições.

== Installation ==

1. No painel do WordPress, acesse Plugins > Adicionar plugin.
2. Pesquise por F10 Lead Capture ou envie o arquivo ZIP do plugin.
3. Instale e ative o F10 Lead Capture.
4. Acesse Leads F10 > Configurações para configurar as integrações opcionais.
5. Acesse Leads F10 > Formulários para criar e configurar formulários com shortcode.
6. Acesse Leads F10 > WhatsApp para criar um botão flutuante de captação.
7. Copie o shortcode gerado e cole em um bloco Shortcode do WordPress quando necessário.

== Shortcode ==

Formulário principal:

`[f10leca_lead_form]`

Formulário salvo específico:

`[f10leca_lead_form id="ebook-school-management"]`

Os atributos existentes continuam disponíveis como sobrescritas opcionais em tempo de execução:

* `title`
* `description`
* `button`
* `product`
* `form_id`
* `source`
* `sub_source`
* `show_institution`
* `redirect_url`

== Forms ==

Acesse Leads F10 > Formulários para criar, editar, duplicar, ativar, desativar ou excluir formulários.

Cada formulário inclui:

* nome interno e identificador;
* título e descrição exibidos no site;
* botão de envio e mensagem de sucesso;
* valores padrão de produto, origem e suborigem;
* campos habilitados, opcionais e obrigatórios;
* confirmação simples, download de arquivo ou link de destino após a conversão.

== WhatsApp ==

Acesse Leads F10 > WhatsApp para criar, editar, duplicar, ativar, desativar ou excluir botões flutuantes de WhatsApp.

Cada configuração de WhatsApp inclui:

* nome interno e número de destino;
* exibição no site inteiro, em conteúdos selecionados ou em categorias;
* exclusões opcionais de conteúdo;
* posição à esquerda ou à direita;
* efeito visual estático, pulsante, radar ou de atenção;
* cor, selos online e offline e atraso de zero a cinco segundos;
* visibilidade independente para desktop e dispositivos móveis;
* textos de formulário voltados a escolas e variáveis configuráveis na mensagem;
* horários semanais opcionais e comportamento fora do atendimento;
* pré-visualização ao vivo no painel do WordPress.

O visitante informa nome e WhatsApp antes da abertura da conversa. O contato é armazenado na mesma tabela local de leads e pode utilizar as integrações opcionais com F10 Software e Brevo.

As variáveis de mensagem disponíveis incluem `{name}`, `{visitor_whatsapp}`, `{site_name}`, `{page_title}`, `{page_url}`, `{utm_source}` e `{utm_campaign}`.

Após um envio bem-sucedido, os dados do visitante são mantidos no navegador por sete dias. Durante esse período, novos cliques podem abrir a conversa configurada sem solicitar os mesmos campos novamente. Esse armazenamento é local ao navegador e não é utilizado para publicidade ou análise de terceiros.

== Appearance ==

Acesse Leads F10 > Aparência.

A aba Formulário controla modelos visuais, largura, alinhamento, colunas, espaçamento, cores, bordas, tipografia, sombras e estilo do botão.

A aba Pós-conversão controla fundo, borda, ícone, título, descrição, espaçamento, cores do botão, largura, raio e sombra do painel de resultado.

== Post-conversion replacement ==

Após o envio bem-sucedido, o plugin substitui toda a visualização do formulário pelo painel pós-conversão. O painel de download ou link é movido para fora do elemento HTML do formulário antes da ocultação da tela original, evitando conflitos com CSS de temas e construtores de páginas.

Quando nenhum download ou link está configurado, o mesmo painel apresenta a mensagem de confirmação definida pelo administrador.

== Local storage and retries ==

Os leads são inseridos na tabela `{prefix}_f10leca_leads` antes da execução das integrações externas.

O plugin armazena status de integração, respostas HTTP, erros de negócio, número de tentativas, datas de reenvio e atividades pós-conversão.

== External services ==

Nenhum dado de lead é enviado para uma integração externa opcional até que um administrador a habilite e configure. O destino do WhatsApp é aberto somente após o visitante enviar explicitamente o formulário flutuante ou reutilizar dados previamente enviados e armazenados no mesmo navegador.

= API da F10 Software =

Quando habilitado, o plugin envia os dados do lead para:

`https://nuvem.f10.com.br/fx-api/digitacao`

A requisição pode conter token JWT configurado, tipo de API, ID da unidade, origem, mídia, dados de contato, curso ou interesse, escola ou empresa, observações e informações da página de captura.

Uma resposta HTTP bem-sucedida também é validada pelo conteúdo de negócio. O envio para a F10 somente é considerado concluído quando `incluidos.digitacao` é maior que zero e não existem erros em `nao_incluidas`.

* Site do serviço: https://f10.com.br/
* Termos: https://f10.com.br/termos-de-uso
* Privacidade: https://f10.com.br/politica-de-privacidade

= API de e-mail transacional do Brevo =

Quando habilitado, o plugin envia os dados do lead ao Brevo para criar um e-mail transacional destinado ao endereço configurado.

* Site do serviço: https://www.brevo.com/
* Termos: https://www.brevo.com/legal/termsofuse/
* Privacidade: https://www.brevo.com/legal/privacypolicy/

= WhatsApp =

Quando um administrador configura um botão flutuante e o visitante envia o formulário, o plugin cria uma URL `https://wa.me/` com o número de destino e a mensagem configurada. Conforme o modelo da mensagem, a URL pode conter nome do visitante, WhatsApp informado, página atual, nome do site e parâmetros da campanha. O navegador é então direcionado ao serviço do WhatsApp.

* Site do serviço: https://www.whatsapp.com/
* Termos: https://www.whatsapp.com/legal/terms-of-service
* Privacidade: https://www.whatsapp.com/legal/privacy-policy

== Privacy ==

O plugin armazena no banco de dados do WordPress as informações enviadas pelos visitantes. Os administradores do site são responsáveis por apresentar aviso de privacidade adequado e definir uma base legal para o tratamento desses dados.

Endereços IP são armazenados somente como hashes HMAC para prevenção de abuso. Eventos pós-conversão são armazenados localmente. O plugin não inclui telemetria de terceiros, publicidade, rastreamento de afiliados ou rastreamento automático de usuários.

O recurso de WhatsApp flutuante pode armazenar no navegador o nome do visitante, número do WhatsApp e data de expiração durante sete dias após um envio bem-sucedido. Esses dados são usados somente para evitar a solicitação repetida das mesmas informações em cliques posteriores.

== Frequently Asked Questions ==

= Posso criar mais de um formulário? =

Sim. Cada formulário recebe seu próprio identificador de shortcode.

= Posso configurar mais de um número de WhatsApp? =

Sim. Cada botão pode utilizar um número diferente e ser exibido no site inteiro, em conteúdos selecionados ou em categorias. Quando mais de uma configuração é compatível, conteúdos específicos têm prioridade sobre categorias, e categorias têm prioridade sobre a configuração do site inteiro.

= O botão flutuante salva o lead antes de abrir o WhatsApp? =

Sim. O visitante informa nome e WhatsApp, o plugin armazena o lead, processa as integrações habilitadas e abre o WhatsApp quando permitido pelo horário configurado.

= Cada formulário pode utilizar campos diferentes? =

Sim. Campos, rótulos e regras de obrigatoriedade são configurados de forma independente.

= Um formulário pode entregar um PDF ou e-book? =

Sim. Selecione ou envie o arquivo pela Biblioteca de Mídia do WordPress no editor do formulário.

= O Brevo é obrigatório? =

Não. As notificações pelo Brevo são opcionais.

= É possível reenviar solicitações da F10 que falharam? =

Sim. Os leads permanecem armazenados localmente, e integrações com falha podem ser reenviadas manualmente ou automaticamente.

== Screenshots ==

1. Visão geral do F10 Lead Capture para capturar e organizar leads da escola em um único painel.
2. Acompanhamento de cada lead com origem, contato, conversão, status, filtros e exportação CSV.
3. Integração com a F10 Software e distribuição de leads em operações com múltiplas unidades.
4. Captação e acompanhamento de conversões pelo WhatsApp.
5. Criação de formulários para campanhas, landing pages e páginas institucionais.

== Changelog ==

= 1.3.9 =

* Replaces short global identifiers with the unique f10leca/F10LECA prefix.
* Updates classes, constants, hooks, AJAX actions, shortcodes, options, transients, menus and asset handles.
* Keeps the assigned f10-captura-de-leads text domain and plugin slug unchanged.

= 1.3.8 =

* Fixes a critical frontend error caused by a registered callback whose method did not exist.
* Saves the WhatsApp form display mode directly in the protected administration handler.
* Removes redundant helper files for the form display mode.

= 1.3.7 =

* Verifies the AJAX nonce before reading WhatsApp submission or conversion-tracking data.
* Sanitizes the request payload once before field processing.
* Sanitizes the WhatsApp form-mode array before reading its fields.

= 1.3.6 =

* Adds three configurable form display modes for the floating WhatsApp widget.
* Explains the seven-day browser reuse behavior in the administration screen.
* Allows always capturing, smart one-time capture, or direct WhatsApp opening without lead capture.

= 1.3.5 =

* Aligns the WhatsApp dialog icon and close button inside the modal header.

= 1.3.4 =

* Fixes the floating form width by removing the transformed containing block after the widget appears.
* Forces the overlay to use the full viewport and keeps the dialog responsive on narrow screens.

= 1.3.3 =

* Fixes the footer render order so the floating WhatsApp markup exists before its script executes.
* Defers the WhatsApp script as an additional compatibility safeguard for themes and cache plugins.

= 1.3.2 =

* Replaces multiple-selection boxes with searchable checkbox lists.
* Highlights selected pages, content, and categories.
* Centers the WhatsApp icon inside the floating button and preview.

= 1.3.1 =

* Fixes alignment and field sizing in the WhatsApp administration editor.
* Improves responsive form layout and full-width content selectors.
* Updates the WhatsApp icon used in the widget and preview.

= 1.3.0 =

* Adds configurable floating WhatsApp lead capture widgets.
* Adds whole-site, selected-content, category, and exclusion targeting.
* Adds school-oriented defaults, business hours, online and offline states, and live preview.
* Saves WhatsApp contacts through the existing local lead and integration workflow.
* Adds WhatsApp conversion tracking and clear WhatsApp labels to the lead dashboard.

= 1.2.3 =

* Aligns the plugin text domain with the WordPress.org slug `f10-captura-de-leads`.
* Refactors CSV output so Plugin Check recognizes the dedicated non-HTML CSV escaping at the final output point.
* Documents and scopes the intentional database schema removal performed only during explicit plugin uninstallation.

= 1.2.2 =

* Rebuilt the post-conversion transition so the complete form view is replaced.
* Moves the post-conversion component outside the HTML form at runtime.
* Uses inline important visibility rules to resist theme or page-builder CSS.
* Confirmation-only forms also replace the original fields with a result panel.

= 1.2.1 =

* Added the first form-to-post-conversion view transition.
* Added standalone confirmation when no download or link is configured.

= 1.2.0 =

* Added multiple saved forms, individual fields and texts, Media Library downloads, destination links, and appearance tabs.

= 1.1.0 =

* Added appearance presets, post-conversion tracking, lead conversion statuses, and CSV fields.

= 1.0.7 =

* Added F10 business-response validation and reconciliation of false-positive successes.

= 1.0.6 =

* Fixed AJAX form endpoint resolution and added masked credential previews.

= 1.0.5 =

* Fixed required-field rendering inside WordPress REST autosaves.

= 1.0.0 =

* Initial release.
