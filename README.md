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
- Armazenamento local antes das integrações externas.
- Integração opcional com F10 Software e Brevo.
- Botões flutuantes de WhatsApp com captura de nome e número antes de abrir a conversa.
- Segmentação por site todo, conteúdos ou categorias, com exclusões opcionais.
- Horário de atendimento, estados online/offline e pré-visualização ao vivo.

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
- agenda semanal e comportamento fora do horário;
- pré-visualização durante a configuração.

O lead é salvo antes da abertura de `https://wa.me/` e pode seguir para as integrações F10 Software e Brevo.

## Correção 1.3.3

A versão 1.3.3 corrige a ordem de renderização do botão flutuante. O HTML do widget agora é impresso antes do script do rodapé, e o arquivo JavaScript é carregado com `defer` como proteção adicional contra temas e plugins de cache que alterem a ordem dos scripts.

## Shortcode

```text
[f10_lead_form]
```

```text
[f10_lead_form id="ebook-gestao-escolar"]
```

## Segurança e privacidade

- configurações restritas a administradores;
- nonce, honeypot e rate limit;
- IP armazenado apenas como hash;
- tokens exibidos apenas de forma mascarada;
- CSV protegido contra fórmulas;
- exclusão de dados na desinstalação desativada por padrão.

## Requisitos

- WordPress 6.2 ou superior;
- PHP 7.4 ou superior.

## Licença

GPL-2.0-or-later.
