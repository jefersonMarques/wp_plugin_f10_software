<?php

if (!defined('ABSPATH')) {
    exit;
}

trait F10_Lead_Capture_Admin_Conversion_Trait
{
    public function sanitize_conversion(array $input): array
    {
        $defaults = F10_Lead_Capture_Config::conversion_defaults();
        $type = isset($input['type']) ? sanitize_key((string) $input['type']) : $defaults['type'];
        $behavior = isset($input['behavior']) ? sanitize_key((string) $input['behavior']) : $defaults['behavior'];
        $file_id = isset($input['file_id']) ? absint($input['file_id']) : 0;
        $file_url = isset($input['file_url']) ? esc_url_raw((string) $input['file_url']) : '';

        if (!in_array($type, array('link', 'download'), true)) {
            $type = $defaults['type'];
        }

        if (!in_array($behavior, array('button', 'automatic'), true)) {
            $behavior = $defaults['behavior'];
        }

        if ($file_id > 0) {
            $attachment_url = wp_get_attachment_url($file_id);

            if (is_string($attachment_url) && $attachment_url !== '') {
                $file_url = esc_url_raw($attachment_url);
            }
        }

        return array(
            'enabled' => !empty($input['enabled']) ? '1' : '0',
            'type' => $type,
            'behavior' => $behavior,
            'title' => isset($input['title']) ? sanitize_text_field((string) $input['title']) : $defaults['title'],
            'description' => isset($input['description']) ? sanitize_textarea_field((string) $input['description']) : $defaults['description'],
            'label' => isset($input['label']) ? sanitize_text_field((string) $input['label']) : $defaults['label'],
            'link_url' => isset($input['link_url']) ? esc_url_raw((string) $input['link_url']) : '',
            'file_id' => (string) $file_id,
            'file_url' => $file_url,
            'open_new_tab' => !empty($input['open_new_tab']) ? '1' : '0',
            'delay_ms' => isset($input['delay_ms'])
                ? (string) max(0, min(10000, absint($input['delay_ms'])))
                : $defaults['delay_ms'],
        );
    }

    public function render_conversion_page(): void
    {
        $this->require_capability();
        $conversion = F10_Lead_Capture_Config::get_conversion();
        ?>
        <div class="wrap f10-admin-page">
            <h1>Pós-conversão</h1>
            <p>Defina o que acontece depois que o visitante envia o formulário. O acionamento do conteúdo fica registrado no lead.</p>
            <?php settings_errors(); ?>

            <form method="post" action="options.php" data-f10-conversion-form>
                <?php settings_fields('f10_lead_capture_conversion_group'); ?>

                <div class="f10-admin-layout f10-admin-layout--conversion">
                    <div class="f10-admin-settings-column">
                        <section class="f10-admin-card">
                            <h2>Ativação</h2>
                            <label class="f10-switch-row">
                                <input
                                    type="checkbox"
                                    name="<?php echo esc_attr(self::CONVERSION_OPTION); ?>[enabled]"
                                    value="1"
                                    <?php checked($conversion['enabled'], '1'); ?>
                                    data-f10-conversion-enabled
                                >
                                <span>
                                    <strong>Oferecer uma ação após o envio</strong>
                                    <small>Quando desativado, o visitante verá apenas a mensagem de sucesso.</small>
                                </span>
                            </label>
                        </section>

                        <div data-f10-conversion-settings>
                            <section class="f10-admin-card">
                                <h2>Tipo de ação</h2>
                                <div class="f10-choice-grid">
                                    <label class="f10-choice-card">
                                        <input type="radio" name="<?php echo esc_attr(self::CONVERSION_OPTION); ?>[type]" value="download" <?php checked($conversion['type'], 'download'); ?> data-f10-conversion-type>
                                        <span class="dashicons dashicons-download" aria-hidden="true"></span>
                                        <strong>Download de arquivo</strong>
                                        <small>Selecione um PDF, e-book, planilha ou outro material da biblioteca.</small>
                                    </label>
                                    <label class="f10-choice-card">
                                        <input type="radio" name="<?php echo esc_attr(self::CONVERSION_OPTION); ?>[type]" value="link" <?php checked($conversion['type'], 'link'); ?> data-f10-conversion-type>
                                        <span class="dashicons dashicons-external" aria-hidden="true"></span>
                                        <strong>Abrir um link</strong>
                                        <small>Direcione para uma página, agenda, vídeo, área restrita ou outra URL.</small>
                                    </label>
                                </div>
                            </section>

                            <section class="f10-admin-card">
                                <h2>Conteúdo</h2>
                                <div class="f10-conversion-source" data-f10-source="download">
                                    <label class="f10-control f10-control--full">
                                        <span>Arquivo para download</span>
                                        <div class="f10-media-field">
                                            <input
                                                type="url"
                                                class="large-text"
                                                name="<?php echo esc_attr(self::CONVERSION_OPTION); ?>[file_url]"
                                                value="<?php echo esc_attr((string) $conversion['file_url']); ?>"
                                                placeholder="Selecione um arquivo da biblioteca ou informe a URL"
                                                data-f10-file-url
                                            >
                                            <input type="hidden" name="<?php echo esc_attr(self::CONVERSION_OPTION); ?>[file_id]" value="<?php echo esc_attr((string) $conversion['file_id']); ?>" data-f10-file-id>
                                            <button type="button" class="button button-secondary" data-f10-select-file>Selecionar arquivo</button>
                                            <button type="button" class="button" data-f10-clear-file>Limpar</button>
                                        </div>
                                    </label>
                                </div>

                                <div class="f10-conversion-source" data-f10-source="link">
                                    <label class="f10-control f10-control--full">
                                        <span>URL de destino</span>
                                        <input
                                            type="url"
                                            class="large-text"
                                            name="<?php echo esc_attr(self::CONVERSION_OPTION); ?>[link_url]"
                                            value="<?php echo esc_attr((string) $conversion['link_url']); ?>"
                                            placeholder="https://exemplo.com.br/proxima-etapa"
                                            data-f10-conversion-input="url"
                                        >
                                    </label>
                                </div>
                            </section>

                            <section class="f10-admin-card">
                                <h2>Apresentação ao visitante</h2>
                                <div class="f10-control-grid">
                                    <label class="f10-control f10-control--full">
                                        <span>Título</span>
                                        <input type="text" name="<?php echo esc_attr(self::CONVERSION_OPTION); ?>[title]" value="<?php echo esc_attr((string) $conversion['title']); ?>" maxlength="190" data-f10-conversion-input="title">
                                    </label>
                                    <label class="f10-control f10-control--full">
                                        <span>Descrição</span>
                                        <textarea name="<?php echo esc_attr(self::CONVERSION_OPTION); ?>[description]" rows="3" maxlength="500" data-f10-conversion-input="description"><?php echo esc_textarea((string) $conversion['description']); ?></textarea>
                                    </label>
                                    <label class="f10-control">
                                        <span>Texto do botão</span>
                                        <input type="text" name="<?php echo esc_attr(self::CONVERSION_OPTION); ?>[label]" value="<?php echo esc_attr((string) $conversion['label']); ?>" maxlength="120" data-f10-conversion-input="label">
                                    </label>
                                    <label class="f10-control">
                                        <span>Comportamento</span>
                                        <select name="<?php echo esc_attr(self::CONVERSION_OPTION); ?>[behavior]" data-f10-conversion-behavior>
                                            <option value="button" <?php selected($conversion['behavior'], 'button'); ?>>Mostrar botão para o visitante</option>
                                            <option value="automatic" <?php selected($conversion['behavior'], 'automatic'); ?>>Abrir automaticamente</option>
                                        </select>
                                    </label>
                                    <label class="f10-control" data-f10-delay-control>
                                        <span>Aguardar antes de abrir</span>
                                        <span class="f10-number-control">
                                            <input type="number" min="0" max="10000" step="100" name="<?php echo esc_attr(self::CONVERSION_OPTION); ?>[delay_ms]" value="<?php echo esc_attr((string) $conversion['delay_ms']); ?>">
                                            <small>ms</small>
                                        </span>
                                    </label>
                                    <label class="f10-switch-row f10-switch-row--compact">
                                        <input type="checkbox" name="<?php echo esc_attr(self::CONVERSION_OPTION); ?>[open_new_tab]" value="1" <?php checked($conversion['open_new_tab'], '1'); ?>>
                                        <span>
                                            <strong>Abrir em nova aba</strong>
                                            <small>Aplicado ao clique manual. A abertura automática usa a aba atual para evitar bloqueios do navegador.</small>
                                        </span>
                                    </label>
                                </div>
                            </section>

                            <section class="f10-admin-card">
                                <h2>Como o rastreamento funciona</h2>
                                <p>O lead é salvo normalmente. Quando o visitante clica no botão ou a ação automática é executada, o plugin registra:</p>
                                <ul class="ul-disc">
                                    <li>tipo da ação: download ou link;</li>
                                    <li>data e hora do primeiro acionamento;</li>
                                    <li>quantidade total de acionamentos.</li>
                                </ul>
                                <p class="description">O plugin contabiliza o acionamento do download. O navegador não informa ao WordPress se o arquivo foi efetivamente aberto após ser baixado.</p>
                            </section>
                        </div>

                        <?php submit_button('Salvar pós-conversão'); ?>
                    </div>

                    <aside class="f10-admin-preview-column">
                        <div class="f10-preview-toolbar"><strong>Pré-visualização</strong></div>
                        <div class="f10-preview-stage f10-preview-stage--conversion">
                            <div class="f10-conversion-preview" data-f10-conversion-preview>
                                <span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
                                <h3 data-f10-preview-title><?php echo esc_html((string) $conversion['title']); ?></h3>
                                <p data-f10-preview-description><?php echo esc_html((string) $conversion['description']); ?></p>
                                <button type="button" class="button button-primary button-hero" data-f10-preview-label><?php echo esc_html((string) $conversion['label']); ?></button>
                                <small data-f10-preview-meta></small>
                            </div>
                        </div>
                    </aside>
                </div>
            </form>
        </div>
        <?php
    }
}
