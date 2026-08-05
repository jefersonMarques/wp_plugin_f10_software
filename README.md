# F10 Escolar - Captura de Leads

O **F10 Escolar - Captura de Leads** cria e gerencia formulários e botões flutuantes de WhatsApp no WordPress, salva os contatos no banco de dados do site e pode enviá-los para a **F10 Software** e para o **Brevo**.

O plugin foi desenvolvido para escolas, cursos livres, escolas de idiomas, instituições de ensino e empresas que precisam organizar a captação comercial.

Para integrar a captação de leads a uma operação acadêmica, financeira e comercial completa, conheça o [sistema de gestão escolar da F10](https://f10.com.br/sistema-de-gestao-escolar).

## Principais recursos

- Vários formulários independentes no mesmo site.
- Título, descrição, botão e mensagem de sucesso por formulário.
- Campos, rótulos e obrigatoriedade configurados individualmente.
- Shortcodes gerados para cada formulário salvo.
- Download de arquivos da Biblioteca de Mídia ou redirecionamento para outra página.
- Rastreamento de downloads e acessos associado ao lead.
- Quatro modelos visuais: Clássico F10, Minimalista, Suave e Escuro.
- Aparência responsiva para desktop e dispositivos móveis.
- Armazenamento local antes das integrações externas.
- Integração opcional com a F10 Software e o Brevo.
- Botões flutuantes de WhatsApp com captura de nome e número antes de abrir a conversa.
- Segmentação por site inteiro, conteúdos ou categorias, com exclusões opcionais.
- Horário de atendimento, estados online e offline e pré-visualização ao vivo.
- Histórico de leads, filtros, exportação CSV e reenvio de integrações com falha.
- Captura de parâmetros UTM, URL da página e referenciador.

## Instalação

1. No painel do WordPress, acesse **Plugins → Adicionar plugin**.
2. Pesquise por **F10 Escolar - Captura de Leads** ou envie o arquivo ZIP do plugin.
3. Instale e ative o plugin.
4. Acesse **Leads F10 → Configurações** para configurar as integrações opcionais.
5. Acesse **Leads F10 → Formulários** para criar formulários com shortcode.
6. Acesse **Leads F10 → WhatsApp** para configurar um botão flutuante de captação.

## Shortcodes

Formulário principal:

```text
[f10leca_lead_form]
```

Formulário salvo específico:

```text
[f10leca_lead_form id="ebook-gestao-escolar"]
```

## WhatsApp flutuante

Cada configuração pode utilizar um número e regras diferentes, incluindo:

- exibição no site inteiro, em conteúdos específicos ou categorias;
- exclusão opcional de páginas e outros conteúdos;
- posição à direita ou à esquerda;
- visual padrão, pulsante, radar ou atenção;
- cor, selo online ou offline e atraso de exibição;
- visibilidade independente em desktop e dispositivos móveis;
- formulário compacto com nome e WhatsApp;
- agenda semanal e comportamento fora do horário;
- pré-visualização durante a configuração.

O lead é salvo antes da abertura de `https://wa.me/` e pode seguir para as integrações da F10 Software e do Brevo.

## Segurança e privacidade

- configurações restritas a administradores;
- nonce, honeypot e limitação de requisições;
- endereço IP armazenado apenas como hash HMAC;
- tokens exibidos apenas de forma mascarada;
- exportação CSV protegida contra fórmulas;
- nenhuma telemetria, publicidade ou rastreamento automático de terceiros;
- exclusão de dados na desinstalação desativada por padrão.

## Requisitos

- WordPress 6.2 ou superior;
- PHP 7.4 ou superior.

## Versão atual

`1.3.10`

A versão 1.3.10 altera o nome de exibição para **F10 Escolar - Captura de Leads**, preservando o slug e o domínio de tradução `f10-captura-de-leads`.

## Licença

GPL-2.0-or-later.
