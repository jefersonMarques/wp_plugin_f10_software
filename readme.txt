=== F10 Escolar - Captura de Leads ===
Contributors: rafamarques, f10software
Tags: captura de leads, formulário de contato, whatsapp, crm escolar, gestão escolar
Requires at least: 6.2
Requires PHP: 7.4
Tested up to: 7.0
Stable tag: 1.3.10
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Crie formulários e botões de WhatsApp para captar leads, armazenar contatos e integrar o WordPress com a F10 Software e o Brevo.

== Description ==

O F10 Escolar - Captura de Leads permite criar formulários de captação de leads e botões flutuantes de WhatsApp para páginas, posts, landing pages e campanhas educacionais no WordPress.

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
2. Pesquise por F10 Escolar - Captura de Leads ou envie o arquivo ZIP do plugin.
3. Instale e ative o F10 Escolar - Captura de Leads.
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
* Privacidade: https://www.whatsapp.com/legal/privacy-policy/

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

1. Visão geral do F10 Escolar - Captura de Leads para capturar e organizar leads da escola em um único painel.
2. Acompanhamento de cada lead com origem, contato, conversão, status, filtros e exportação CSV.
3. Integração com a F10 Software e distribuição de leads em operações com múltiplas unidades.
4. Captação e acompanhamento de conversões pelo WhatsApp.
5. Criação de formulários para campanhas, landing pages e páginas institucionais.

== Changelog ==

= 1.3.10 =

* Altera o nome de exibição do plugin para `F10 Escolar - Captura de Leads`.
* Traduz a descrição do cabeçalho principal para português.
* Mantém inalterados o slug `f10-captura-de-leads`, o domínio de tradução e as configurações existentes.

= 1.3.9 =

* Substitui identificadores globais curtos pelo prefixo exclusivo `f10leca` e `F10LECA`.
* Atualiza classes, constantes, hooks, ações AJAX, shortcodes, opções, transients, menus e identificadores de assets.
* Mantém inalterados o domínio de tradução e o slug `f10-captura-de-leads`.

= 1.3.8 =

* Corrige um erro crítico no frontend causado por um callback registrado cujo método não existia.
* Salva o modo de exibição do formulário do WhatsApp diretamente no manipulador protegido da administração.
* Remove arquivos auxiliares redundantes do modo de exibição do formulário.

= 1.3.7 =

* Verifica o nonce AJAX antes de ler dados de envio ou rastreamento de conversão do WhatsApp.
* Sanitiza o payload da requisição uma única vez antes do processamento dos campos.
* Sanitiza o array do modo de formulário do WhatsApp antes da leitura dos campos.

= 1.3.6 =

* Adiciona três modos configuráveis de formulário ao widget flutuante do WhatsApp.
* Explica na administração o reaproveitamento de dados do navegador por sete dias.
* Permite captura sempre, captura inteligente uma única vez ou abertura direta do WhatsApp.

= 1.3.5 =

* Alinha o ícone e o botão de fechar no cabeçalho do diálogo do WhatsApp.

= 1.3.4 =

* Corrige a largura do formulário flutuante removendo o contexto de transformação após a exibição do widget.
* Mantém o overlay no viewport completo e o diálogo responsivo em telas estreitas.

= 1.3.3 =

* Corrige a ordem de renderização para que o HTML do WhatsApp exista antes da execução do script.
* Carrega o script com `defer` como proteção adicional para temas e plugins de cache.

= 1.3.2 =

* Substitui caixas de seleção múltipla por listas pesquisáveis de checkboxes.
* Destaca páginas, conteúdos e categorias selecionados.
* Centraliza o ícone do WhatsApp no botão flutuante e na pré-visualização.

= 1.3.1 =

* Corrige alinhamento e dimensionamento de campos no editor administrativo do WhatsApp.
* Melhora o layout responsivo e os seletores de conteúdo em largura total.
* Atualiza o ícone utilizado no widget e na pré-visualização.

= 1.3.0 =

* Adiciona widgets flutuantes configuráveis de captação pelo WhatsApp.
* Adiciona segmentação por site inteiro, conteúdos, categorias e exclusões.
* Adiciona textos voltados a escolas, horários de atendimento e pré-visualização ao vivo.
* Salva contatos do WhatsApp pelo fluxo local existente de leads e integrações.
* Adiciona rastreamento de conversões do WhatsApp no painel de leads.

= 1.2.3 =

* Alinha o domínio de tradução ao slug `f10-captura-de-leads`.
* Refatora a saída CSV para o escaping não HTML dedicado.
* Documenta e restringe a remoção do esquema do banco à desinstalação explícita.

= 1.2.2 =

* Reconstrói a transição pós-conversão para substituir a visualização completa do formulário.
* Move o componente pós-conversão para fora do formulário HTML em tempo de execução.
* Aplica regras de visibilidade resistentes a CSS de temas e construtores de páginas.

= 1.2.1 =

* Adiciona a primeira transição entre formulário e pós-conversão.
* Adiciona confirmação independente quando não existe download ou link.

= 1.2.0 =

* Adiciona múltiplos formulários, campos e textos individuais, downloads, links e abas de aparência.

= 1.1.0 =

* Adiciona modelos de aparência, rastreamento pós-conversão, status e campos CSV.

= 1.0.7 =

* Adiciona validação de resposta de negócio da F10 e reconciliação de falsos positivos.

= 1.0.6 =

* Corrige a resolução do endpoint AJAX e adiciona pré-visualizações mascaradas de credenciais.

= 1.0.5 =

* Corrige a renderização de campos obrigatórios durante autosaves REST do WordPress.

= 1.0.0 =

* Primeira versão.
