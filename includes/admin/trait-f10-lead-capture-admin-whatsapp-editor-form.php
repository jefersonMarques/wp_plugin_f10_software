<?php

if (!defined('ABSPATH')) {
    exit;
}

trait F10_Lead_Capture_Admin_WhatsApp_Editor_Form_Trait
{
    private function render_whatsapp_form_section(array $widget): void
    {
        ?>
        <section class="f10-admin-card">
            <h2>Formulário</h2>
            <div class="f10-whatsapp-form-behavior">
                <div class="f10-whatsapp-form-behavior__heading">
                    <strong>Quando solicitar os dados?</strong>
                    <p>Defina se o formulário deve aparecer novamente depois que o visitante já enviou nome e WhatsApp.</p>
                </div>
                <div class="f10-whatsapp-form-behavior__options">
                    <label class="f10-whatsapp-form-behavior__option">
                        <input type="radio" name="f10_whatsapp[form_display_mode]" value="always" <?php checked($widget['form_display_mode'], 'always'); ?>>
                        <span>
                            <strong>Sempre abrir formulário</strong>
                            <small>Solicita os dados em todos os cliques. Cada envio registra um novo lead.</small>
                        </span>
                    </label>
                    <label class="f10-whatsapp-form-behavior__option">
                        <input type="radio" name="f10_whatsapp[form_display_mode]" value="smart" <?php checked($widget['form_display_mode'], 'smart'); ?>>
                        <span>
                            <strong>Abrir somente se os dados não foram enviados</strong>
                            <small>Recomendado. Após o primeiro envio, abre direto por 7 dias neste navegador e nesta configuração.</small>
                        </span>
                    </label>
                    <label class="f10-whatsapp-form-behavior__option f10-whatsapp-form-behavior__option--warning">
                        <input type="radio" name="f10_whatsapp[form_display_mode]" value="never" <?php checked($widget['form_display_mode'], 'never'); ?>>
                        <span>
                            <strong>Nunca abrir formulário</strong>
                            <small>Abre o WhatsApp diretamente. O visitante não será cadastrado como lead pelo plugin.</small>
                        </span>
                    </label>
                </div>
                <p class="f10-whatsapp-form-behavior__notice">
                    Na opção recomendada, o formulário volta a aparecer após 7 dias, ao limpar os dados do navegador, usar aba anônima ou acessar por outro dispositivo.
                </p>
            </div>
            <div class="f10-control-grid">
                <label class="f10-control">
                    <span>Título</span>
                    <input type="text" name="f10_whatsapp[form_title]" value="<?php echo esc_attr($widget['form_title']); ?>" maxlength="190" data-f10-whatsapp-preview-title>
                </label>
                <label class="f10-control">
                    <span>Texto do botão</span>
                    <input type="text" name="f10_whatsapp[button_label]" value="<?php echo esc_attr($widget['button_label']); ?>" maxlength="100" data-f10-whatsapp-preview-button>
                </label>
                <label class="f10-control f10-control--full">
                    <span>Descrição</span>
                    <textarea name="f10_whatsapp[form_description]" rows="3" maxlength="500" data-f10-whatsapp-preview-description><?php echo esc_textarea($widget['form_description']); ?></textarea>
                </label>
                <label class="f10-control f10-control--full">
                    <span>Descrição fora do horário</span>
                    <textarea name="f10_whatsapp[form_offline_description]" rows="3" maxlength="500"><?php echo esc_textarea($widget['form_offline_description']); ?></textarea>
                </label>
                <label class="f10-control f10-control--full">
                    <span>Mensagem enviada ao WhatsApp</span>
                    <textarea name="f10_whatsapp[message_template]" rows="4" maxlength="1000"><?php echo esc_textarea($widget['message_template']); ?></textarea>
                    <small>Variáveis: <code>{name}</code>, <code>{visitor_whatsapp}</code>, <code>{site_name}</code>, <code>{page_title}</code>, <code>{page_url}</code>, <code>{utm_source}</code> e <code>{utm_campaign}</code>.</small>
                </label>
            </div>
        </section>
        <?php
    }

    private function render_whatsapp_schedule_section(array $widget): void
    {
        $day_labels = array(
            '1' => 'Segunda-feira',
            '2' => 'Terça-feira',
            '3' => 'Quarta-feira',
            '4' => 'Quinta-feira',
            '5' => 'Sexta-feira',
            '6' => 'Sábado',
            '7' => 'Domingo',
        );
        ?>
        <section class="f10-admin-card">
            <h2>Horário de atendimento</h2>
            <label class="f10-checkbox-control">
                <input type="checkbox" name="f10_whatsapp[schedule_enabled]" value="1" <?php checked($widget['schedule_enabled'], '1'); ?> data-f10-whatsapp-schedule-toggle>
                <span>Usar horário de atendimento</span>
            </label>

            <div data-f10-whatsapp-schedule-panel>
                <table class="widefat striped f10-whatsapp-schedule">
                    <thead><tr><th>Dia</th><th style="width:90px">Ativo</th><th style="width:130px">Início</th><th style="width:130px">Fim</th></tr></thead>
                    <tbody>
                        <?php foreach ($day_labels as $day_key => $day_label) : ?>
                            <?php $day = $widget['schedule'][$day_key]; ?>
                            <tr>
                                <td><?php echo esc_html($day_label); ?></td>
                                <td><input type="checkbox" name="f10_whatsapp[schedule][<?php echo esc_attr($day_key); ?>][enabled]" value="1" <?php checked($day['enabled'], '1'); ?>></td>
                                <td><input type="time" name="f10_whatsapp[schedule][<?php echo esc_attr($day_key); ?>][start]" value="<?php echo esc_attr($day['start']); ?>"></td>
                                <td><input type="time" name="f10_whatsapp[schedule][<?php echo esc_attr($day_key); ?>][end]" value="<?php echo esc_attr($day['end']); ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <label class="f10-whatsapp-outside-behavior">
                    <span>Fora do horário</span>
                    <select name="f10_whatsapp[outside_behavior]">
                        <option value="open" <?php selected($widget['outside_behavior'], 'open'); ?>>Capturar e abrir o WhatsApp</option>
                        <option value="capture_only" <?php selected($widget['outside_behavior'], 'capture_only'); ?>>Capturar sem abrir o WhatsApp</option>
                        <option value="hide" <?php selected($widget['outside_behavior'], 'hide'); ?>>Ocultar completamente</option>
                    </select>
                </label>
            </div>
        </section>
        <?php
    }

    private function render_whatsapp_preview(array $widget): void
    {
        ?>
        <aside class="f10-whatsapp-admin-preview-column">
            <div class="f10-whatsapp-admin-preview-card">
                <h2>Pré-visualização</h2>
                <p>O exemplo é atualizado conforme as configurações visuais.</p>
                <div class="f10-whatsapp-admin-preview" data-f10-whatsapp-preview>
                    <div class="f10-whatsapp-admin-preview__page">
                        <div class="f10-whatsapp-admin-preview__content"><span></span><span></span><span></span></div>
                        <div class="f10-whatsapp-admin-preview__widget f10-whatsapp-admin-preview__widget--<?php echo esc_attr($widget['position']); ?> f10-whatsapp-widget--<?php echo esc_attr($widget['design']); ?>" style="--f10-whatsapp-color:<?php echo esc_attr($widget['color']); ?>" data-f10-whatsapp-preview-widget>
                            <button type="button" class="f10-whatsapp-widget__trigger" tabindex="-1">
                                <span class="f10-whatsapp-widget__radar" aria-hidden="true"></span>
                                <span class="f10-whatsapp-widget__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16.6,14c-0.2-0.1-1.5-0.7-1.7-0.8c-0.2-0.1-0.4-0.1-0.6,0.1c-0.2,0.2-0.6,0.8-0.8,1c-0.1,0.2-0.3,0.2-0.5,0.1c-0.7-0.3-1.4-0.7-2-1.2c-0.5-0.5-1-1.1-1.4-1.7c-0.1-0.2,0-0.4,0.1-0.5c0.1-0.1,0.2-0.3,0.4-0.4c0.1-0.1,0.2-0.3,0.2-0.4c0.1-0.1,0.1-0.3,0-0.4c-0.1-0.1-0.6-1.3-0.8-1.8C9.4,7.3,9.2,7.3,9,7.3c-0.1,0-0.3,0-0.5,0C8.3,7.3,8,7.5,7.9,7.6C7.3,8.2,7,8.9,7,9.7c0.1,0.9,0.4,1.8,1,2.6c1.1,1.6,2.5,2.9,4.2,3.7c0.5,0.2,0.9,0.4,1.4,0.5c0.5,0.2,1,0.2,1.6,0.1c0.7-0.1,1.3-0.6,1.7-1.2c0.2-0.4,0.2-0.8,0.1-1.2C17,14.2,16.8,14.1,16.6,14 M19.1,4.9C15.2,1,8.9,1,5,4.9c-3.2,3.2-3.8,8.1-1.6,12L2,22l5.3-1.4c1.5,0.8,3.1,1.2,4.7,1.2h0c5.5,0,9.9-4.4,9.9-9.9C22,9.3,20.9,6.8,19.1,4.9 M16.4,18.9c-1.3,0.8-2.8,1.3-4.4,1.3h0c-1.5,0-2.9-0.4-4.2-1.1l-0.3-0.2l-3.1,0.8l0.8-3l-0.2-0.3C2.6,12.4,3.8,7.4,7.7,4.9S16.6,3.7,19,7.5C21.4,11.4,20.3,16.5,16.4,18.9"/></svg>
                                </span>
                            </button>
                            <span class="f10-whatsapp-widget__badge" data-f10-whatsapp-preview-badge-output><?php echo esc_html($widget['badge_online']); ?></span>
                        </div>
                    </div>
                    <div class="f10-whatsapp-admin-preview__dialog">
                        <strong data-f10-whatsapp-preview-title-output><?php echo esc_html($widget['form_title']); ?></strong>
                        <p data-f10-whatsapp-preview-description-output><?php echo esc_html($widget['form_description']); ?></p>
                        <label>Nome<input type="text" disabled></label>
                        <label>WhatsApp<input type="text" disabled></label>
                        <button type="button" data-f10-whatsapp-preview-button-output><?php echo esc_html($widget['button_label']); ?></button>
                    </div>
                </div>
            </div>
        </aside>
        <?php
    }
}
