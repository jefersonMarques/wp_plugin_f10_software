# F10 Lead Capture — plugin WordPress para captura de leads

O **F10 Lead Capture** cria formulários de captação de leads no WordPress, salva os contatos no banco de dados do site e pode enviá-los diretamente para o **F10 Software**. A notificação de novos leads por e-mail através do Brevo é opcional.

A solução foi desenvolvida para sites, blogs e landing pages de escolas, cursos livres, escolas de idiomas, cursos técnicos, escolas profissionalizantes e redes educacionais que precisam integrar o marketing digital ao atendimento comercial.

## Para que serve

O plugin resolve quatro pontos principais:

1. Exibe um formulário responsivo por shortcode.
2. Permite escolher quais dados serão solicitados ao visitante.
3. Salva o lead localmente antes de chamar serviços externos.
4. Envia o contato para a API da F10 e, opcionalmente, para o Brevo.

Mesmo quando uma integração externa apresenta instabilidade, o contato permanece armazenado no WordPress e pode ser reenviado posteriormente.

## Sobre a F10 Software

A [F10 Software](https://f10.com.br/) oferece tecnologia para gestão escolar, captação de alunos, atendimento comercial, financeiro, pedagógico e comunicação entre escolas, alunos e responsáveis.

- [Conheça a F10 Software](https://f10.com.br/)
- [Veja o CRM Escolar da F10](https://f10.com.br/solucoes/crm-escolar)
- [Solicite uma demonstração](https://f10.com.br/contato)
- [Conteúdos sobre gestão escolar](https://blog.f10.com.br/)

## Principais recursos

- Campos configuráveis de nome, curso/interesse, telefone, WhatsApp, e-mail, escola/empresa e observações.
- Ativação individual de cada campo.
- Rótulos personalizados apenas no frontend, sem alterar as chaves técnicas da API.
- Shortcode configurável para páginas, posts e landing pages.
- Armazenamento em tabela própria antes do envio externo.
- Integração com o endpoint oficial da API F10.
- Notificação opcional por e-mail transacional via Brevo.
- Registro automático da página de captura, referência e parâmetros UTM.
- Histórico administrativo com filtros, detalhes técnicos e exportação CSV.
- Reenvio manual e tentativas automáticas via WP-Cron.
- Proteção com nonce, honeypot, rate limit e hash do IP.
- Consentimento de privacidade configurável.

## Configuração da F10

Acesse **Leads F10 → Configurações** e informe:

- token JWT fornecido pela equipe F10;
- ID da unidade fornecido pela equipe F10;
- fonte cadastrada no F10;
- mídia cadastrada no F10.

O endpoint não precisa ser informado. O plugin utiliza sempre:

```text
https://nuvem.f10.com.br/fx-api/digitacao
```

Ajuda sobre fonte e mídia:

https://ajuda.f10.com.br/kb/pt-br/article/119833/fontes-eventos-e-cadastro-de-visitas

## Campos configuráveis do formulário

Na seção **Campos do formulário**, cada campo possui:

- opção **Ativar**;
- nome exibido no formulário.

A personalização altera apenas o texto mostrado ao visitante. Os campos enviados à API continuam usando os nomes técnicos esperados pela F10.

Campos disponíveis:

- nome;
- curso ou interesse;
- telefone;
- WhatsApp, enviado como `celular`;
- e-mail;
- escola ou empresa, enviada como `colegio`;
- observações, enviadas dentro de `obs`.

Os campos `extra1` e `extra2` são preenchidos automaticamente com o caminho e a URL completa da página de captura.

## Payload enviado à F10

A chamada utiliza um objeto JSON plano:

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

O token é enviado no corpo da requisição, conforme o contrato da API F10. O campo `tipo_api` é sempre `2`.

Quando apenas telefone ou WhatsApp estiver preenchido, o plugin utiliza o número disponível como fallback para os campos `telefone` e `celular`.

## Instalação

1. Baixe o ZIP ou copie os arquivos para `/wp-content/plugins/f10-lead-capture/`.
2. Ative **F10 Lead Capture** no WordPress.
3. Acesse **Leads F10 → Configurações**.
4. Configure a integração F10 e os campos do formulário.
5. Configure o Brevo apenas quando desejar notificações por e-mail.
6. Insira o shortcode em um bloco **Shortcode**.

## Shortcode

Uso básico:

```text
[f10_lead_form]
```

Exemplo personalizado:

```text
[f10_lead_form title="Receba uma demonstração" button="Quero uma demonstração" product="Sistema de gestão escolar" source="Blog F10" sub_source="Artigo"]
```

Atributos disponíveis:

| Atributo | Finalidade |
|---|---|
| `title` | Título exibido no formulário. |
| `description` | Texto complementar abaixo do título. |
| `button` | Texto do botão de envio. |
| `product` | Valor padrão do curso/interesse. |
| `form_id` | Identificador interno do formulário. |
| `source` | Origem descritiva da captura no WordPress. |
| `sub_source` | Suborigem ou campanha. |
| `show_institution` | Mantido para compatibilidade; `no` oculta escola/empresa. |
| `redirect_url` | URL permitida para redirecionamento após o sucesso. |

## Persistência e recuperação

Cada contato é gravado na tabela `{prefix}_f10_leads` antes das chamadas externas. O histórico mantém respostas HTTP, erros, quantidade de tentativas e próxima tentativa programada.

A versão 1.0.4 inclui migração automática para adicionar telefone, observações e as novas preferências de campos sem apagar leads existentes.

## Segurança e privacidade

- Configurações restritas a administradores.
- Tokens e chaves não são devolvidos ao navegador durante a edição.
- Nonce e honeypot no formulário.
- Rate limit contra envios repetitivos.
- IP armazenado apenas como hash.
- Exportação CSV protegida contra fórmulas de planilha.
- Exclusão de dados na desinstalação desativada por padrão.

Nunca publique tokens JWT, chaves do Brevo ou credenciais reais no repositório.

## Requisitos

- WordPress 6.2 ou superior.
- PHP 7.4 ou superior.
- Credenciais válidas da integração F10 para envio ao sistema.
- Conta Brevo com remetente autorizado apenas quando a notificação estiver ativa.

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
  class-f10-lead-capture-config.php
  class-f10-lead-capture-deactivator.php
  class-f10-lead-capture-form.php
  class-f10-lead-capture-integrations.php
  class-f10-lead-capture-plugin.php
  class-f10-lead-capture-repository.php
f10-lead-capture.php
readme.txt
uninstall.php
```

## Licença

GPL-2.0-or-later.
