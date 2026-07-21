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
            <div class="f10-control-grid">
                <label>
                    <span>Título</span>
                    <input type="text" name="f10_whatsapp[form_title]" value="<?php echo esc_attr($widget['form_title']); ?>" maxlength="190" data-f10-whatsapp-preview-title>
                </label>
                <label>
                    <span>Texto do botão</span>
                    <input type="text" name="f10_whatsapp[button_label]" value="<?php echo esc_attr($widget['button_label']); ?>" maxlength="100" data-f10-whatsapp-preview-button>
                </label>
                <label class="f10-control-wide">
                    <span>Descrição</span>
                    <textarea name="f10_whatsapp[form_description]" rows="3" maxlength="500" data-f10-whatsapp-preview-description><?php echo esc_textarea($widget['form_description']); ?></textarea>
                </label>
                <label class="f10-control-wide">
                    <span>Descrição fora do horário</span>
                    <textarea name="f10_whatsapp[form_offline_description]" rows="3" maxlength="500"><?php echo esc_textarea($widget['form_offline_description']); ?></textarea>
                </label>
                <label class="f10-control-wide">
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
                                    <svg viewBox="0 0 24 24"><path d="M20.5 11.7a8.5 8.5 0 0 1-12.6 7.5L3 20.5l1.3-4.7a8.5 8.5 0 1 1 16.2-4.1Zm-8.4-6.5a6.5 6.5 0 0 0-5.5 10l.3.5-.7 2.3 2.4-.6.5.3a6.5 6.5 0 1 0 3-12.5Z"/></svg>
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
