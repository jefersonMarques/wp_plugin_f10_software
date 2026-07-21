<?php

if (!defined('ABSPATH')) {
    exit;
}

trait F10_Lead_Capture_Admin_WhatsApp_Trait
{
    public function render_whatsapp_page(): void
    {
        $this->require_capability();
        $view = sanitize_key($this->query_text('view', 30));
        $widget_id = F10_Lead_Capture_WhatsApp_Config::sanitize_widget_id(
            $this->query_text('widget', 100)
        );

        if ($view === 'edit' || $view === 'new') {
            $this->render_whatsapp_editor($view === 'new' ? '' : $widget_id);
            return;
        }

        $this->render_whatsapp_list();
    }

    public function handle_save_whatsapp(): void
    {
        $this->require_capability();
        check_admin_referer('f10_lead_capture_save_whatsapp');

        $raw = filter_input(INPUT_POST, 'f10_whatsapp', FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY);
        $input = is_array($raw) ? $raw : array();
        $widgets = F10_Lead_Capture_WhatsApp_Config::get_widgets();
        $original_id = F10_Lead_Capture_WhatsApp_Config::sanitize_widget_id(
            (string) ($input['original_id'] ?? '')
        );
        $requested_id = F10_Lead_Capture_WhatsApp_Config::sanitize_widget_id(
            (string) ($input['id'] ?? '')
        );
        $name = sanitize_text_field((string) ($input['name'] ?? ''));
        $phone = F10_Lead_Capture_WhatsApp_Config::normalize_phone(
            sanitize_text_field((string) ($input['phone'] ?? ''))
        );

        if ($phone === '') {
            wp_die(
                'Informe um número de WhatsApp válido, incluindo o DDD.',
                'WhatsApp inválido',
                array('back_link' => true)
            );
        }

        if ($requested_id === '') {
            $requested_id = F10_Lead_Capture_WhatsApp_Config::sanitize_widget_id($name);
        }

        if ($requested_id === '') {
            $requested_id = 'atendimento-' . strtolower(wp_generate_password(6, false, false));
        }

        if ($original_id === '') {
            $requested_id = $this->whatsapp_unique_id($requested_id, $widgets);
        } elseif ($requested_id !== $original_id && isset($widgets[$requested_id])) {
            $requested_id = $this->whatsapp_unique_id($requested_id, $widgets);
        }

        $existing = $original_id !== '' && isset($widgets[$original_id])
            ? $widgets[$original_id]
            : F10_Lead_Capture_WhatsApp_Config::default_widget();
        $schedule_input = is_array($input['schedule'] ?? null) ? $input['schedule'] : array();
        $schedule = array();

        foreach (F10_Lead_Capture_WhatsApp_Config::default_schedule() as $day_key => $day_defaults) {
            $day = is_array($schedule_input[$day_key] ?? null) ? $schedule_input[$day_key] : array();
            $schedule[$day_key] = array(
                'enabled' => !empty($day['enabled']) ? '1' : '0',
                'start' => sanitize_text_field((string) ($day['start'] ?? $day_defaults['start'])),
                'end' => sanitize_text_field((string) ($day['end'] ?? $day_defaults['end'])),
            );
        }

        $widget = array(
            'id' => $requested_id,
            'name' => $name !== '' ? $name : 'Atendimento comercial',
            'active' => !empty($input['active']) ? '1' : '0',
            'phone' => $phone,
            'targeting_mode' => sanitize_key((string) ($input['targeting_mode'] ?? 'all')),
            'content_ids' => $this->whatsapp_posted_ids($input['content_ids'] ?? array()),
            'category_ids' => $this->whatsapp_posted_ids($input['category_ids'] ?? array()),
            'excluded_content_ids' => $this->whatsapp_posted_ids($input['excluded_content_ids'] ?? array()),
            'position' => sanitize_key((string) ($input['position'] ?? 'right')),
            'design' => sanitize_key((string) ($input['design'] ?? 'pulse')),
            'color' => sanitize_hex_color((string) ($input['color'] ?? '')) ?: '#25D366',
            'badge_online' => sanitize_text_field((string) ($input['badge_online'] ?? '')),
            'badge_offline' => sanitize_text_field((string) ($input['badge_offline'] ?? '')),
            'delay_seconds' => (string) max(0, min(5, absint($input['delay_seconds'] ?? 2))),
            'show_desktop' => !empty($input['show_desktop']) ? '1' : '0',
            'show_mobile' => !empty($input['show_mobile']) ? '1' : '0',
            'form_title' => sanitize_text_field((string) ($input['form_title'] ?? '')),
            'form_description' => sanitize_textarea_field((string) ($input['form_description'] ?? '')),
            'form_offline_description' => sanitize_textarea_field(
                (string) ($input['form_offline_description'] ?? '')
            ),
            'button_label' => sanitize_text_field((string) ($input['button_label'] ?? '')),
            'message_template' => sanitize_textarea_field((string) ($input['message_template'] ?? '')),
            'schedule_enabled' => !empty($input['schedule_enabled']) ? '1' : '0',
            'outside_behavior' => sanitize_key((string) ($input['outside_behavior'] ?? 'open')),
            'schedule' => $schedule,
            'created_at' => (string) ($existing['created_at'] ?? current_time('mysql', true)),
            'updated_at' => current_time('mysql', true),
        );
        $widget = F10_Lead_Capture_WhatsApp_Config::normalize_widget($widget, $requested_id);

        if ($original_id !== '' && $original_id !== $requested_id) {
            unset($widgets[$original_id]);
        }

        $widgets[$requested_id] = $widget;
        F10_Lead_Capture_WhatsApp_Config::save_widgets($widgets);

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page' => 'f10-lead-whatsapp',
                    'view' => 'edit',
                    'widget' => $requested_id,
                    'f10_notice' => 'whatsapp_saved',
                ),
                admin_url('admin.php')
            )
        );
        exit;
    }

    public function handle_duplicate_whatsapp(): void
    {
        $this->require_capability();
        $widget_id = F10_Lead_Capture_WhatsApp_Config::sanitize_widget_id(
            $this->query_text('widget', 100)
        );
        check_admin_referer('f10_lead_capture_duplicate_whatsapp_' . $widget_id);
        $widgets = F10_Lead_Capture_WhatsApp_Config::get_widgets();

        if (!isset($widgets[$widget_id])) {
            $this->whatsapp_redirect_notice('whatsapp_missing');
        }

        $copy = $widgets[$widget_id];
        $new_id = $this->whatsapp_unique_id($widget_id . '-copia', $widgets);
        $copy['id'] = $new_id;
        $copy['name'] = (string) $copy['name'] . ' — cópia';
        $copy['created_at'] = current_time('mysql', true);
        $copy['updated_at'] = current_time('mysql', true);
        $widgets[$new_id] = $copy;
        F10_Lead_Capture_WhatsApp_Config::save_widgets($widgets);

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page' => 'f10-lead-whatsapp',
                    'view' => 'edit',
                    'widget' => $new_id,
                    'f10_notice' => 'whatsapp_duplicated',
                ),
                admin_url('admin.php')
            )
        );
        exit;
    }

    public function handle_delete_whatsapp(): void
    {
        $this->require_capability();
        $widget_id = F10_Lead_Capture_WhatsApp_Config::sanitize_widget_id(
            $this->query_text('widget', 100)
        );
        check_admin_referer('f10_lead_capture_delete_whatsapp_' . $widget_id);
        $widgets = F10_Lead_Capture_WhatsApp_Config::get_widgets();
        unset($widgets[$widget_id]);
        F10_Lead_Capture_WhatsApp_Config::save_widgets($widgets);
        $this->whatsapp_redirect_notice('whatsapp_deleted');
    }

    private function render_whatsapp_list(): void
    {
        $widgets = F10_Lead_Capture_WhatsApp_Config::get_widgets();

        uasort(
            $widgets,
            static function (array $left, array $right): int {
                return strcasecmp((string) $left['name'], (string) $right['name']);
            }
        );
        ?>
        <div class="wrap f10-admin-page">
            <h1 class="wp-heading-inline">WhatsApp</h1>
            <a class="page-title-action" href="<?php echo esc_url(admin_url('admin.php?page=f10-lead-whatsapp&view=new')); ?>">
                Adicionar WhatsApp
            </a>
            <p>Crie botões de atendimento para o site todo, páginas específicas ou categorias de conteúdo.</p>
            <?php $this->render_whatsapp_notice(); ?>

            <table class="widefat fixed striped f10-whatsapp-admin-list">
                <thead>
                    <tr>
                        <th>Atendimento</th>
                        <th style="width:170px">Número</th>
                        <th style="width:190px">Exibição</th>
                        <th style="width:120px">Design</th>
                        <th style="width:100px">Posição</th>
                        <th style="width:90px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$widgets) : ?>
                        <tr>
                            <td colspan="6">
                                Nenhum WhatsApp configurado.
                                <a href="<?php echo esc_url(admin_url('admin.php?page=f10-lead-whatsapp&view=new')); ?>">
                                    Adicione o primeiro atendimento.
                                </a>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($widgets as $widget) : ?>
                            <?php
                            $edit_url = add_query_arg(
                                array(
                                    'page' => 'f10-lead-whatsapp',
                                    'view' => 'edit',
                                    'widget' => $widget['id'],
                                ),
                                admin_url('admin.php')
                            );
                            $duplicate_url = wp_nonce_url(
                                add_query_arg(
                                    array(
                                        'action' => 'f10_lead_capture_duplicate_whatsapp',
                                        'widget' => $widget['id'],
                                    ),
                                    admin_url('admin-post.php')
                                ),
                                'f10_lead_capture_duplicate_whatsapp_' . $widget['id']
                            );
                            $delete_url = wp_nonce_url(
                                add_query_arg(
                                    array(
                                        'action' => 'f10_lead_capture_delete_whatsapp',
                                        'widget' => $widget['id'],
                                    ),
                                    admin_url('admin-post.php')
                                ),
                                'f10_lead_capture_delete_whatsapp_' . $widget['id']
                            );
                            ?>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="<?php echo esc_url($edit_url); ?>">
                                            <?php echo esc_html($widget['name']); ?>
                                        </a>
                                    </strong>
                                    <div class="row-actions">
                                        <span><a href="<?php echo esc_url($edit_url); ?>">Editar</a> | </span>
                                        <span><a href="<?php echo esc_url($duplicate_url); ?>">Duplicar</a> | </span>
                                        <span>
                                            <a
                                                class="submitdelete"
                                                href="<?php echo esc_url($delete_url); ?>"
                                                onclick="return confirm('Excluir esta configuração de WhatsApp?');"
                                            >Excluir</a>
                                        </span>
                                    </div>
                                    <small>ID: <code><?php echo esc_html($widget['id']); ?></code></small>
                                </td>
                                <td><?php echo esc_html($this->whatsapp_display_phone($widget['phone'])); ?></td>
                                <td><?php echo esc_html($this->whatsapp_targeting_label($widget)); ?></td>
                                <td><?php echo esc_html($this->whatsapp_design_label($widget['design'])); ?></td>
                                <td><?php echo esc_html($widget['position'] === 'left' ? 'Esquerda' : 'Direita'); ?></td>
                                <td>
                                    <?php if ($widget['active'] === '1') : ?>
                                        <span class="f10-status f10-status--active">Ativo</span>
                                    <?php else : ?>
                                        <span class="f10-status">Inativo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private function whatsapp_posted_ids($value): array
    {
        return is_array($value)
            ? array_values(array_unique(array_filter(array_map('absint', $value))))
            : array();
    }

    private function whatsapp_unique_id(string $base_id, array $widgets): string
    {
        $base_id = F10_Lead_Capture_WhatsApp_Config::sanitize_widget_id($base_id) ?: 'atendimento';
        $candidate = $base_id;
        $suffix = 2;

        while (isset($widgets[$candidate])) {
            $candidate = substr($base_id, 0, 90) . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function whatsapp_display_phone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        if (in_array(strlen($digits), array(12, 13), true) && substr($digits, 0, 2) === '55') {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 11) {
            return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $digits) ?: $digits;
        }

        if (strlen($digits) === 10) {
            return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $digits) ?: $digits;
        }

        return $phone;
    }

    private function whatsapp_targeting_label(array $widget): string
    {
        if ($widget['targeting_mode'] === 'specific') {
            return count($widget['content_ids']) . ' conteúdo(s)';
        }

        if ($widget['targeting_mode'] === 'categories') {
            return count($widget['category_ids']) . ' categoria(s)';
        }

        return 'Site todo';
    }

    private function whatsapp_design_label(string $design): string
    {
        return array(
            'static' => 'Padrão',
            'pulse' => 'Pulsante',
            'radar' => 'Radar',
            'attention' => 'Atenção',
        )[$design] ?? 'Pulsante';
    }

    private function render_whatsapp_notice(): void
    {
        $notice = sanitize_key($this->query_text('f10_notice', 50));
        $messages = array(
            'whatsapp_saved' => 'Configuração de WhatsApp salva.',
            'whatsapp_duplicated' => 'Configuração de WhatsApp duplicada.',
            'whatsapp_deleted' => 'Configuração de WhatsApp excluída.',
            'whatsapp_missing' => 'Configuração de WhatsApp não encontrada.',
        );

        if (isset($messages[$notice])) {
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                esc_html($messages[$notice])
            );
        }
    }

    private function whatsapp_redirect_notice(string $notice): void
    {
        wp_safe_redirect(
            add_query_arg(
                array('page' => 'f10-lead-whatsapp', 'f10_notice' => $notice),
                admin_url('admin.php')
            )
        );
        exit;
    }
}
