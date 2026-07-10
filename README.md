# Plugin WordPress F10 Software para captura de leads

O **F10 Lead Capture** é um plugin WordPress para criar formulários de captação de leads e enviar os contatos diretamente para o **F10 Software**, mantendo uma cópia segura no banco de dados do WordPress.

A solução foi desenvolvida para sites, blogs e landing pages de escolas, cursos livres, escolas de idiomas, cursos profissionalizantes, franquias educacionais e outras instituições que utilizam o WordPress para gerar oportunidades comerciais.

## Para que serve este plugin

O plugin conecta formulários WordPress ao fluxo comercial do F10 Software. Cada lead é salvo localmente antes das integrações externas e pode ser enviado para:

- API de leads do F10 Software;
- e-mail comercial por meio da API transacional do Brevo;
- painel administrativo do próprio WordPress, com histórico, filtros e exportação CSV.

Com isso, a instituição reduz o risco de perder contatos capturados em páginas de campanha, artigos do blog, páginas de produto e landing pages.

## Principais recursos

- Formulário responsivo por shortcode;
- campos de nome, WhatsApp, e-mail e escola ou empresa;
- armazenamento do lead no banco do WordPress antes do envio externo;
- integração autenticada com a API do F10 Software;
- notificação opcional por e-mail via Brevo;
- captura automática da URL da página, referência e parâmetros UTM;
- registro de origem, mídia, campanha, produto e identificador do formulário;
- histórico administrativo com status de cada integração;
- exportação de leads em CSV;
- reenvio manual e novas tentativas automáticas por WP-Cron;
- proteção por nonce, honeypot, limite de requisições e bloqueio de cliques repetidos;
- consentimento LGPD configurável;
- armazenamento do IP apenas como hash para controle de abuso.

## Integração com o F10 Software

O plugin envia o lead usando o contrato de integração do F10, com autenticação Bearer JWT e os dados de unidade, fonte, mídia, produto, contato e contexto da conversão.

O [F10 Software](https://f10.com.br/) é uma plataforma de gestão escolar com recursos para operação pedagógica, financeira, administrativa, comercial e de comunicação. A integração deste plugin é especialmente útil para instituições que desejam conectar o tráfego do site ao processo de atendimento e matrícula.

Conheça também:

- [Sistema de gestão escolar F10 Software](https://f10.com.br/)
- [CRM escolar com WhatsApp integrado](https://f10.com.br/solucoes/crm-escolar)
- [Marketing e captação de alunos](https://f10.com.br/solucoes/marketing-captacao-de-alunos)
- [Conteúdos sobre gestão escolar no Blog F10](https://blog.f10.com.br/)
- [Central de Ajuda F10](https://ajuda.f10.com.br/)

## Requisitos

- WordPress 6.0 ou superior;
- PHP 7.4 ou superior;
- credenciais válidas da API do F10 Software para envio ao sistema;
- conta Brevo e remetente autorizado, apenas quando a notificação por e-mail estiver habilitada.

## Instalação

### Pelo GitHub

1. Clique em **Code > Download ZIP**.
2. No WordPress, acesse **Plugins > Adicionar plugin > Enviar plugin**.
3. Selecione o ZIP baixado e ative o plugin.
4. Acesse **Leads F10 > Configurações**.

### Instalação manual

Copie os arquivos para:

```text
/wp-content/plugins/f10-lead-capture/
```

Depois, ative o plugin no painel do WordPress.

## Configuração da API F10

Em **Leads F10 > Configurações**, informe:

- URL completa do endpoint de leads;
- token JWT da API;
- ID da unidade;
- fonte padrão;
- mídia padrão.

O painel informa quando o JWT está inválido ou expirado, facilitando a manutenção da integração.

## Configuração do Brevo

Marque **Enviar e-mail quando gerar um lead?** e informe:

- chave da API do Brevo;
- e-mail destinatário;
- e-mail remetente autorizado no Brevo;
- nome do remetente.

A integração com Brevo é opcional. Mesmo quando o envio do e-mail falha, o lead permanece armazenado no WordPress.

## Como adicionar o formulário

Use um bloco **Shortcode** no editor do WordPress:

```text
[f10_lead_form]
```

Exemplo personalizado:

```text
[f10_lead_form title="Receba uma demonstração" button="Quero uma demonstração" product="Sistema de gestão escolar" source="Blog F10" sub_source="Artigo sobre gestão escolar"]
```

Para ocultar o campo de escola ou empresa:

```text
[f10_lead_form show_institution="no"]
```

### Atributos disponíveis

| Atributo | Finalidade |
|---|---|
| `title` | Título exibido no formulário |
| `description` | Texto de apoio abaixo do título |
| `button` | Texto do botão de envio |
| `product` | Produto ou interesse enviado ao F10 |
| `form_id` | Identificador interno do formulário |
| `source` | Origem descritiva do lead |
| `sub_source` | Suborigem da conversão |
| `show_institution` | Exibe ou oculta escola/empresa com `yes` ou `no` |
| `redirect_url` | URL permitida para redirecionamento após o envio |

## Armazenamento e recuperação de leads

Os registros ficam na tabela:

```text
{prefix}_f10_leads
```

O lead é gravado antes de qualquer chamada externa. O painel mantém as respostas HTTP, erros, quantidade de tentativas e próxima tentativa programada. Integrações concluídas com sucesso não são repetidas durante um reenvio.

## Segurança e privacidade

- Chaves e tokens são acessíveis somente por administradores autorizados;
- formulários usam nonce e honeypot;
- entradas são validadas e sanitizadas;
- o IP não é salvo em texto puro;
- a remoção dos dados durante a desinstalação é opcional e desativada por padrão;
- o texto de consentimento pode ser ajustado conforme a política de privacidade do site.

## Casos de uso

- formulário de demonstração de software escolar;
- captura de leads em artigos de blog;
- landing pages de matrícula e campanhas educacionais;
- páginas de cursos livres, idiomas e cursos profissionalizantes;
- campanhas com Google Ads, Meta Ads e parâmetros UTM;
- formulários de contato integrados ao CRM escolar;
- captação de alunos e organização do atendimento comercial.

## Licença

Distribuído sob a licença GPL v2 ou posterior. Consulte o arquivo [LICENSE](LICENSE).
