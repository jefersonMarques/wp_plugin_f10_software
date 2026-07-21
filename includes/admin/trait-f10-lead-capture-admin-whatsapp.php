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
            'form_display_mode' => sanitize_key((string) ($input['form_display_mode'] ?? 'smart')),
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
}
