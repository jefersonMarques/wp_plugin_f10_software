# F10 Lead Capture — formulários WordPress integrados ao F10 Software

O **F10 Lead Capture** cria e gerencia formulários e botões flutuantes de WhatsApp no WordPress, salva os contatos no banco do site e pode enviá-los ao **F10 Software** e ao **Brevo**.

## Principais recursos

- Vários formulários independentes no mesmo site.
- Título, descrição, botão e mensagem de sucesso por formulário.
- Campos, rótulos e obrigatoriedade configurados individualmente.
- Download de arquivos da Biblioteca de Mídia ou redirecionamento para outra página.
- Rastreamento de downloads e acessos associado ao lead.
- Quatro modelos visuais: Clássico F10, Minimalista, Suave e Escuro.
- Aparência responsiva para desktop e mobile.
- Abas de aparência para **Formulário** e **Pós-conversão**.
- Armazenamento local antes das integrações externas.
- Integração opcional com F10 Software e Brevo.
- Histórico, filtros, respostas técnicas, reenvio e exportação CSV.
- Botões flutuantes de WhatsApp com captura de nome e número antes de abrir a conversa.
- Segmentação por site todo, conteúdos ou categorias, com exclusões opcionais.
- Horário de atendimento, estados online/offline e pré-visualização ao vivo.

## Formulários

Acesse **Leads F10 → Formulários**.

A tela permite:

- listar os formulários existentes;
- criar um formulário;
- editar, duplicar, ativar ou desativar;
- excluir formulários que não sejam o principal;
- copiar o shortcode pronto.

Cada formulário possui:

- nome interno e identificador;
- título e descrição exibidos ao visitante;
- texto do botão e mensagem de sucesso;
- produto/interesse, origem e suborigem;
- campos ativos, obrigatórios e rótulos;
- ação pós-conversão própria.

Campos disponíveis:

- nome;
- curso ou interesse;
- telefone;
- WhatsApp, enviado como `celular`;
- e-mail;
- escola ou empresa, enviada como `colegio`;
- observações, enviadas dentro de `obs`.

## Pós-conversão por formulário

Dentro do editor do formulário, escolha uma das opções:

1. **Somente confirmar:** mostra a mensagem de sucesso.
2. **Liberar download:** permite enviar ou selecionar um arquivo da Biblioteca de Mídia.
3. **Abrir uma página:** direciona para uma URL interna ou externa.

Downloads e links podem ser apresentados como botão ou abertos automaticamente. O lead registra o tipo, a URL, o primeiro acionamento e o total de acionamentos.

## Aparência

Acesse **Leads F10 → Aparência**.

### Aba Formulário

- modelos prontos;
- largura e alinhamento;
- uma ou duas colunas;
- configurações separadas para desktop e mobile;
- espaçamentos, bordas e arredondamentos;
- cores do fundo, campos, textos e botão;
- tipografia e sombra.

### Aba Pós-conversão

- fundo, borda e espaçamento;
- cor do ícone, título e descrição;
- cores e largura do botão;
- arredondamento, tipografia e sombra.

Os textos e destinos continuam pertencendo a cada formulário; a aba controla somente o visual.

## Shortcode

O formulário principal continua funcionando sem alteração:

```text
[f10_lead_form]
```

Um formulário específico utiliza o identificador mostrado na lista:

```text
[f10_lead_form id="ebook-gestao-escolar"]
```

Atributos antigos continuam disponíveis como sobrescritas opcionais:

| Atributo | Finalidade |
|---|---|
| `id` | Seleciona o formulário salvo. |
| `title` | Sobrescreve o título. |
| `description` | Sobrescreve a descrição. |
| `button` | Sobrescreve o texto do botão. |
| `product` | Sobrescreve o produto/interesse. |
| `form_id` | Identificador gravado no lead. |
| `source` | Sobrescreve a origem. |
| `sub_source` | Sobrescreve a suborigem. |
| `show_institution` | `no` oculta o campo de escola/empresa. |
| `redirect_url` | Sobrescreve a ação pós-conversão com redirecionamento automático. |

## WhatsApp flutuante

Acesse **Leads F10 → WhatsApp** para adicionar atendimentos com números e regras diferentes.

Cada configuração possui:

- nome interno e número de destino;
- exibição no site todo, em conteúdos específicos ou em categorias;
- exclusão opcional de páginas e outros conteúdos;
- posição à direita ou à esquerda;
- visual padrão, pulsante, radar ou atenção;
- cor, badge online/offline e atraso de 0 a 5 segundos;
- visibilidade em desktop e mobile;
- formulário compacto com nome e WhatsApp;
- mensagem enviada ao WhatsApp com variáveis de página e campanha;
- agenda semanal e comportamento fora do horário;
- pré-visualização durante a configuração.

O lead é salvo na mesma tabela dos formulários antes de o navegador abrir `https://wa.me/`. As integrações F10 e Brevo utilizam o mesmo fluxo já existente. Após uma conversão bem-sucedida, nome e WhatsApp permanecem no armazenamento local do navegador por sete dias para evitar novo preenchimento.

Variáveis disponíveis na mensagem:

```text
{name}
{visitor_whatsapp}
{site_name}
{page_title}
{page_url}
{utm_source}
{utm_campaign}
```

## Configuração da integração F10

Acesse **Leads F10 → Configurações** e informe:

- token JWT;
- ID da unidade;
- fonte cadastrada no F10;
- mídia cadastrada no F10.

Endpoint fixo:

```text
https://nuvem.f10.com.br/fx-api/digitacao
```

Ajuda sobre fonte e mídia:

https://ajuda.f10.com.br/kb/pt-br/article/119833/fontes-eventos-e-cadastro-de-visitas

## Payload enviado à F10

```json
{
  "token": "JWT_CONFIGURADO",
  "tipo_api": 2,
  "unidade_id": "1",
  "fonte": "Site F10",
  "midia": "Site F10",
  "nome": "Nome do lead",
  "curso": "Curso ou interesse",
  "telefone": "41999999999",
  "celular": "41999999999",
  "email": "lead@example.com",
  "colegio": "Escola Exemplo",
  "obs": "Observações e contexto da captura",
  "extra1": "/pagina/",
  "extra2": "https://example.com/pagina/"
}
```

Uma resposta é considerada bem-sucedida somente quando `incluidos.digitacao` é maior que zero e não existem erros em `nao_incluidas`.

## Migração e atualização

A atualização preserva os leads e formulários existentes. A migração original cria o **Formulário principal** usando automaticamente:

- os textos padrão já utilizados;
- os campos e rótulos salvos anteriormente;
- a antiga configuração global de pós-conversão.

O shortcode `[f10_lead_form]` continua funcionando e nenhum lead existente é removido.

## Segurança e privacidade

- configurações restritas a administradores;
- nonce, honeypot e rate limit;
- IP armazenado apenas como hash;
- tokens exibidos apenas de forma mascarada;
- CSV protegido contra fórmulas;
- exclusão de dados na desinstalação desativada por padrão.

## Requisitos

- WordPress 6.2 ou superior;
- PHP 7.4 ou superior;
- credenciais F10 válidas para envio ao sistema;
- conta Brevo apenas quando a notificação estiver ativa.

## Estrutura

```text
assets/
  css/
    admin-whatsapp.css
    admin.css
    form.css
    whatsapp.css
  js/
    admin-appearance.js
    admin-forms.js
    admin-whatsapp.js
    form.js
    whatsapp.js
includes/
  admin/
    trait-f10-lead-capture-admin-appearance.php
    trait-f10-lead-capture-admin-forms.php
    trait-f10-lead-capture-admin-leads.php
    trait-f10-lead-capture-admin-settings.php
    trait-f10-lead-capture-admin-whatsapp.php
    trait-f10-lead-capture-admin-whatsapp-editor.php
    trait-f10-lead-capture-admin-whatsapp-editor-fields.php
    trait-f10-lead-capture-admin-whatsapp-editor-form.php
    trait-f10-lead-capture-admin-whatsapp-lead-labels.php
  class-f10-lead-capture-activator.php
  class-f10-lead-capture-admin.php
  class-f10-lead-capture-config.php
  class-f10-lead-capture-form.php
  class-f10-lead-capture-integrations.php
  class-f10-lead-capture-plugin.php
  class-f10-lead-capture-repository.php
  class-f10-lead-capture-submission-service.php
  class-f10-lead-capture-whatsapp-config.php
  class-f10-lead-capture-whatsapp.php
```

## Licença

GPL-2.0-or-later.

## Correção 1.2.1

Após um envio bem-sucedido, o formulário é substituído pelo componente de pós-conversão. O componente não é mais acrescentado abaixo dos campos. Quando não há download ou link configurado, a mesma área apresenta somente a confirmação de sucesso.
## Correção 1.2.2

Após o envio bem-sucedido, o formulário completo é removido da visualização e substituído pela caixa de pós-conversão. O resultado não é mais renderizado dentro da tag `<form>`, evitando interferência de estilos do tema ou do construtor de páginas.
## Correção 1.2.3

A versão 1.2.3 alinha o text domain ao slug `f10-captura-de-leads`, reorganiza a escrita do CSV para atender ao Plugin Check sem aplicar escape HTML aos dados exportados e restringe a exceção de alteração de esquema exclusivamente à remoção opcional da tabela durante a desinstalação.

## Versão 1.3.0

A versão 1.3.0 adiciona widgets flutuantes de WhatsApp voltados a escolas e negócios de ensino livre. O visitante informa nome e WhatsApp, o lead é armazenado localmente e processado pelas integrações habilitadas, e a conversa configurada é aberta em seguida. A atualização também adiciona rastreamento da abertura do WhatsApp, segmentação por conteúdo e agenda semanal.
