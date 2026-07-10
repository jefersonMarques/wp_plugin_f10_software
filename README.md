# F10 Lead Capture — plugin WordPress para captura de leads

O **F10 Lead Capture** cria formulários de captação de leads no WordPress, salva os contatos no banco de dados do site e pode enviá-los diretamente para o **F10 Software**. A notificação de novos leads por e-mail através do Brevo é opcional.

A solução foi desenvolvida para sites, blogs e landing pages de escolas, cursos livres, escolas de idiomas, cursos técnicos, escolas profissionalizantes e redes educacionais que precisam integrar o marketing digital ao atendimento comercial.

## Principais recursos

- Campos configuráveis de nome, curso/interesse, telefone, WhatsApp, e-mail, escola/empresa e observações.
- Ativação individual e rótulos personalizados no frontend.
- Armazenamento local antes do envio externo.
- Integração com a API F10 e notificação opcional via Brevo.
- Registro da página, referência e parâmetros UTM.
- Histórico, detalhes técnicos, CSV e reenvio de falhas.
- Quatro modelos visuais prontos com personalização responsiva.
- Pré-visualização para desktop e mobile.
- Pós-conversão com download de arquivo ou abertura de link.
- Rastreamento de downloads e acessos dentro do histórico e do CSV.

## Aparência do formulário

Acesse **Leads F10 → Aparência** para escolher entre os modelos **Clássico F10**, **Minimalista**, **Suave** e **Escuro**.

A tela permite ajustar com pré-visualização em desktop e mobile:

- largura máxima e alinhamento;
- quantidade de colunas;
- espaçamento interno e distância entre campos;
- cores do formulário, campos, textos e botão;
- bordas, arredondamentos e sombra;
- tamanhos do título;
- largura do botão.

As alterações são aplicadas aos shortcodes existentes, sem precisar editar os posts.

## Pós-conversão e materiais

Acesse **Leads F10 → Pós-conversão** para definir o que será oferecido depois do envio:

- download de arquivo selecionado na Biblioteca de Mídia;
- abertura de uma URL;
- botão manual;
- abertura automática após um atraso configurável.

O lead registra o tipo da ação, URL, texto do botão, primeiro acionamento e quantidade total de acionamentos. A lista mostra **Baixou**, **Acessou** ou **Pendente**, e os mesmos dados são exportados no CSV.

O rastreamento confirma que o visitante acionou o download ou link. O navegador não informa se o arquivo baixado foi posteriormente aberto.

## Configuração da F10

Acesse **Leads F10 → Configurações** e informe o token JWT, o ID da unidade, a fonte e a mídia fornecidos ou cadastrados com a equipe F10.

O endpoint é fixo:

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

Quando apenas telefone ou WhatsApp estiver preenchido, o número disponível é usado como fallback para `telefone` e `celular`.

## Instalação

1. Instale o ZIP no WordPress.
2. Ative **F10 Lead Capture**.
3. Acesse **Leads F10 → Configurações**.
4. Configure a integração e os campos.
5. Ajuste **Aparência** e **Pós-conversão** quando necessário.
6. Insira `[f10_lead_form]` em um bloco Shortcode.

## Shortcode

```text
[f10_lead_form]
```

Exemplo:

```text
[f10_lead_form title="Receba uma demonstração" button="Quero uma demonstração" product="Sistema de gestão escolar" source="Blog F10" sub_source="Artigo"]
```

## Persistência, segurança e privacidade

Cada contato é gravado em `{prefix}_f10_leads` antes das chamadas externas. Respostas HTTP 200 da F10 são validadas pelo conteúdo: somente `incluidos.digitacao` maior que zero, sem `nao_incluidas`, representa sucesso.

- Nonce, honeypot e rate limit no formulário.
- IP armazenado apenas como hash.
- Exportação CSV protegida contra fórmulas.
- Rastreamento de pós-conversão armazenado localmente, sem telemetria externa.
- Exclusão de dados na desinstalação desativada por padrão.

## Requisitos

- WordPress 6.2 ou superior.
- PHP 7.4 ou superior.
- Credenciais F10 para envio à API.
- Conta Brevo somente quando a notificação estiver ativa.

## Licença

GPL-2.0-or-later.

## Versão 1.1.0

A versão 1.1.0 adiciona personalização visual responsiva, modelos prontos e ações pós-conversão rastreáveis. Downloads e acessos a links aparecem na lista de leads, nos detalhes e no CSV.
