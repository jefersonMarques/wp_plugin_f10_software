# Changelog

## 1.1.0 — 2026-07-10

### Adicionado

- Menu **Aparência** com modelos Clássico F10, Minimalista, Suave e Escuro.
- Personalização responsiva para desktop e mobile com pré-visualização.
- Controles de cores, espaçamento, largura, colunas, bordas, sombra, tipografia e botão.
- Menu **Pós-conversão** com download de arquivo da Biblioteca de Mídia ou abertura de link.
- Comportamento por botão ou abertura automática.
- Rastreamento de acionamentos com tipo, URL, data e contador por lead.
- Status de download/acesso na lista, detalhes e exportação CSV.
- Migração automática das novas colunas de rastreamento.

### Corrigido

- Argumento duplicado na consulta de paginação do repositório de leads.

## 1.0.7 — 2026-07-10

### Corrigido

- Respostas HTTP 200 da F10 agora são validadas pelo resultado de negócio.
- Sucesso exige `incluidos.digitacao` maior que zero e ausência de itens em `nao_incluidas`.
- Falha da integração principal F10 define o status geral do lead como falha, mesmo quando o Brevo envia o e-mail.
- Registros antigos marcados incorretamente como sucesso são reconciliados automaticamente na atualização.

## 1.0.6 — 2026-07-10

### Corrigido

- Envio do formulário corrigido para usar o atributo HTML `action` real, evitando requisições para `/[object HTMLInputElement]`.
- Colisão entre a propriedade `form.action` e o campo oculto `name="action"` eliminada.
- Prévia mascarada adicionada para o token JWT F10 e a chave da API Brevo salvos.
- Marcação duplicada na tabela de configurações removida.

## 1.0.5 — 2026-07-10

### Corrigido

- Erro fatal ao renderizar campos obrigatórios configuráveis no editor de blocos e nos autosaves da REST API.
- Chamada inválida `required()` substituída pelo atributo HTML nativo `required`.

## 1.0.4 — 2026-07-10

### Alterado

- Endpoint da API F10 fixado em `https://nuvem.f10.com.br/fx-api/digitacao`.
- Payload F10 atualizado para o formato plano com token no corpo e `tipo_api` igual a `2`.
- Campos do formulário passaram a permitir ativação individual e rótulo personalizado no frontend.
- Adicionados telefone e observações ao banco de leads, ao painel, ao CSV e ao Brevo.
- Adicionadas ajudas contextuais para token, unidade, fonte e mídia.
- Migração automática do banco adicionada para instalações existentes.

## 1.0.3 — 2026-07-10

### Corrigido

- Consultas SQL reestruturadas com placeholders de identificador e parâmetros preparados.
- Cache de objetos adicionado para consultas individuais de leads, com invalidação após alterações.
- Exportação CSV reestruturada sem operações diretas de arquivo e com proteção contra fórmulas.
- Avisos de nonce removidos do fluxo administrativo e do formulário AJAX.
- `readme.txt` reescrito em inglês para o diretório WordPress.org.
- Consulta de desinstalação preparada com identificador seguro.
- Paginação e campo UTM duplicados removidos.

## 1.0.2 — 2026-07-10

### Corrigido

- Nome público padronizado para `F10 Lead Capture`, mantendo o slug e o text domain `f10-lead-capture`.
- Licença GPL adicionada ao cabeçalho principal do plugin.
- Cabeçalho de atualização externa removido para compatibilidade com o diretório WordPress.org.
- Cabeçalho `Tested up to: 7.0` adicionado ao `readme.txt`.
- Serviços externos F10 Software e Brevo documentados com dados enviados, finalidade, termos e políticas de privacidade.
- Arquivos ocultos removidos do pacote destinado ao WordPress.org.

## 1.0.1 — 2026-07-10

### Alterado

- Metadados públicos do plugin otimizados para F10 Software, captura de leads, CRM escolar e Brevo.
- Documentação do GitHub ampliada com casos de uso, instalação, segurança e links institucionais.

## 1.0.0 — 2026-07-10

### Adicionado

- Formulário de captura por shortcode.
- Campos de nome, WhatsApp, e-mail e escola ou empresa.
- Persistência local em tabela própria do WordPress.
- Integração com a API de leads da F10 Software.
- Notificação opcional via API transacional do Brevo.
- Captura de página, referência e parâmetros UTM.
- Painel administrativo de leads.
- Exportação CSV.
- Reenvio manual e automático de integrações com falha.
- Proteções com nonce, honeypot, rate limit e hash do IP.
- Consentimento de privacidade configurável.
