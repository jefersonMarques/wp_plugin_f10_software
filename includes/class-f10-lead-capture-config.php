<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10_Lead_Capture_Config
{
    public const F10_ENDPOINT = 'https://nuvem.f10.com.br/fx-api/digitacao';
    public const F10_API_TYPE = 2;
    public const DEFAULT_FORM_ID = 'wordpress-form';
    public const FORMS_OPTION = 'f10_lead_capture_forms';

    public static function form_fields(): array
    {
        return array(
            'name' => array(
                'request_key' => 'name',
                'label' => 'Nome',
                'enabled' => '1',
                'required' => '1',
                'type' => 'text',
                'autocomplete' => 'name',
                'max_length' => 190,
            ),
            'course' => array(
                'request_key' => 'product',
                'label' => 'Curso ou interesse',
                'enabled' => '0',
                'required' => '0',
                'type' => 'text',
                'autocomplete' => '',
                'max_length' => 190,
            ),
            'phone' => array(
                'request_key' => 'phone',
                'label' => 'Telefone',
                'enabled' => '0',
                'required' => '0',
                'type' => 'tel',
                'autocomplete' => 'tel',
                'max_length' => 20,
            ),
            'whatsapp' => array(
                'request_key' => 'whatsapp',
                'label' => 'WhatsApp',
                'enabled' => '1',
                'required' => '1',
                'type' => 'tel',
                'autocomplete' => 'tel',
                'max_length' => 20,
            ),
            'email' => array(
                'request_key' => 'email',
                'label' => 'E-mail',
                'enabled' => '1',
                'required' => '1',
                'type' => 'email',
                'autocomplete' => 'email',
                'max_length' => 190,
            ),
            'school' => array(
                'request_key' => 'institution_name',
                'label' => 'Nome da escola ou empresa',
                'enabled' => '1',
                'required' => '0',
                'type' => 'text',
                'autocomplete' => 'organization',
                'max_length' => 190,
            ),
            'notes' => array(
                'request_key' => 'notes',
                'label' => 'Observações',
                'enabled' => '0',
                'required' => '0',
                'type' => 'textarea',
                'autocomplete' => '',
                'max_length' => 2000,
            ),
        );
    }

    public static function default_settings(): array
    {
        return array(
            'f10_enabled' => '1',
            'f10_token' => '',
            'f10_unit_id' => '',
            'f10_source' => '',
            'f10_media' => '',
            'brevo_enabled' => '0',
            'brevo_api_key' => '',
            'brevo_recipient_email' => '',
            'brevo_sender_email' => sanitize_email((string) get_option('admin_email')),
            'brevo_sender_name' => 'Leads F10',
            'require_consent' => '1',
            'consent_text' => 'Autorizo o contato da equipe comercial sobre as soluções apresentadas.',
            'success_message' => 'Dados recebidos com sucesso. Nossa equipe entrará em contato.',
            'max_retry_attempts' => '5',
            'delete_data_on_uninstall' => '0',
        );
    }

    public static function default_form(array $legacy_settings = array(), array $legacy_conversion = array()): array
    {
        $settings = wp_parse_args($legacy_settings, self::default_settings());
        $legacy_conversion = wp_parse_args($legacy_conversion, self::conversion_defaults());
        $fields = array();

        foreach (self::form_fields() as $field_key => $field) {
            $enabled_key = 'field_' . $field_key . '_enabled';
            $label_key = 'field_' . $field_key . '_label';
            $enabled = isset($settings[$enabled_key])
                ? ((string) $settings[$enabled_key] === '1' ? '1' : '0')
                : (string) $field['enabled'];
            $label = isset($settings[$label_key]) && trim((string) $settings[$label_key]) !== ''
                ? sanitize_text_field((string) $settings[$label_key])
                : (string) $field['label'];

            $fields[$field_key] = array(
                'enabled' => $enabled,
                'required' => $enabled === '1' ? (string) $field['required'] : '0',
                'label' => $label,
            );
        }

        return array(
            'id' => self::DEFAULT_FORM_ID,
            'name' => 'Formulário principal',
            'active' => '1',
            'title' => 'Fale com um especialista',
            'description' => 'Preencha seus dados e a equipe entrará em contato.',
            'button' => 'Quero saber mais',
            'success_message' => (string) $settings['success_message'],
            'product' => 'Software F10',
            'source' => 'WordPress',
            'sub_source' => 'Formulário de conteúdo',
            'fields' => $fields,
            'conversion' => self::normalize_conversion($legacy_conversion),
            'created_at' => current_time('mysql', true),
            'updated_at' => current_time('mysql', true),
        );
    }

    public static function normalize_form(array $form, string $form_id = ''): array
    {
        $default = self::default_form();
        $id = self::sanitize_form_id($form_id !== '' ? $form_id : (string) ($form['id'] ?? ''));

        if ($id === '') {
            $id = self::DEFAULT_FORM_ID;
        }

        $normalized = array(
            'id' => $id,
            'name' => trim((string) ($form['name'] ?? '')) ?: 'Formulário sem nome',
            'active' => ($form['active'] ?? '1') === '1' ? '1' : '0',
            'title' => (string) ($form['title'] ?? $default['title']),
            'description' => (string) ($form['description'] ?? $default['description']),
            'button' => trim((string) ($form['button'] ?? '')) ?: (string) $default['button'],
            'success_message' => trim((string) ($form['success_message'] ?? '')) ?: (string) $default['success_message'],
            'product' => (string) ($form['product'] ?? ''),
            'source' => trim((string) ($form['source'] ?? '')) ?: 'WordPress',
            'sub_source' => (string) ($form['sub_source'] ?? ''),
            'fields' => array(),
            'conversion' => self::normalize_conversion(is_array($form['conversion'] ?? null) ? $form['conversion'] : array()),
            'created_at' => (string) ($form['created_at'] ?? current_time('mysql', true)),
            'updated_at' => (string) ($form['updated_at'] ?? current_time('mysql', true)),
        );

        $input_fields = is_array($form['fields'] ?? null) ? $form['fields'] : array();

        foreach (self::form_fields() as $field_key => $field) {
            $configured = is_array($input_fields[$field_key] ?? null) ? $input_fields[$field_key] : array();
            $enabled = ($configured['enabled'] ?? $field['enabled']) === '1' ? '1' : '0';
            $required = $enabled === '1' && ($configured['required'] ?? $field['required']) === '1' ? '1' : '0';
            $label = trim((string) ($configured['label'] ?? ''));

            $normalized['fields'][$field_key] = array(
                'enabled' => $enabled,
                'required' => $required,
                'label' => $label !== '' ? $label : (string) $field['label'],
            );
        }

        return $normalized;
    }

    public static function normalize_conversion(array $conversion): array
    {
        $defaults = self::conversion_defaults();
        $has_legacy_enabled = array_key_exists('enabled', $conversion);
        $legacy_enabled = $has_legacy_enabled ? (string) $conversion['enabled'] : '1';
        $conversion = wp_parse_args($conversion, $defaults);
        $type = in_array((string) $conversion['type'], array('none', 'download', 'link'), true)
            ? (string) $conversion['type']
            : 'none';

        if ($has_legacy_enabled && $legacy_enabled !== '1') {
            $type = 'none';
        }

        return array(
            'type' => $type,
            'behavior' => (string) $conversion['behavior'] === 'automatic' ? 'automatic' : 'button',
            'title' => (string) $conversion['title'],
            'description' => (string) $conversion['description'],
            'label' => (string) $conversion['label'],
            'link_url' => esc_url_raw((string) $conversion['link_url']),
            'file_id' => (string) absint($conversion['file_id']),
            'file_url' => esc_url_raw((string) $conversion['file_url']),
            'open_new_tab' => (string) $conversion['open_new_tab'] === '1' ? '1' : '0',
            'delay_ms' => (string) max(0, min(10000, absint($conversion['delay_ms']))),
        );
    }

    public static function conversion_defaults(): array
    {
        return array(
            'enabled' => '0',
            'type' => 'none',
            'behavior' => 'button',
            'title' => 'Seu conteúdo está pronto',
            'description' => 'Clique no botão abaixo para continuar.',
            'label' => 'Baixar material',
            'link_url' => '',
            'file_id' => '0',
            'file_url' => '',
            'open_new_tab' => '1',
            'delay_ms' => '800',
        );
    }

    public static function appearance_presets(): array
    {
        return array(
            'classic_f10' => array(
                'label' => 'Clássico F10',
                'description' => 'Visual institucional com azul e laranja.',
                'settings' => self::appearance_defaults(),
            ),
            'minimal' => array(
                'label' => 'Minimalista',
                'description' => 'Formulário limpo, discreto e com pouco relevo.',
                'settings' => array_merge(
                    self::appearance_defaults(),
                    array(
                        'preset' => 'minimal',
                        'form_border_width' => '0',
                        'form_radius' => '8',
                        'field_radius' => '8',
                        'button_radius' => '8',
                        'button_background' => '#111827',
                        'button_hover_background' => '#000000',
                        'title_color' => '#111827',
                        'shadow' => 'none',
                        'conversion_radius' => '8',
                        'conversion_shadow' => 'none',
                        'conversion_button_background' => '#111827',
                        'conversion_button_hover_background' => '#000000',
                    )
                ),
            ),
            'soft' => array(
                'label' => 'Suave',
                'description' => 'Fundo claro e botão azul para páginas editoriais.',
                'settings' => array_merge(
                    self::appearance_defaults(),
                    array(
                        'preset' => 'soft',
                        'form_background' => '#f8fafc',
                        'form_border_color' => '#e2e8f0',
                        'field_border_color' => '#cbd5e1',
                        'button_background' => '#2563eb',
                        'button_hover_background' => '#1d4ed8',
                        'title_color' => '#0f172a',
                        'form_radius' => '20',
                        'conversion_background' => '#eff6ff',
                        'conversion_border_color' => '#bfdbfe',
                        'conversion_title_color' => '#1e3a8a',
                        'conversion_button_background' => '#2563eb',
                        'conversion_button_hover_background' => '#1d4ed8',
                    )
                ),
            ),
            'dark' => array(
                'label' => 'Escuro',
                'description' => 'Contraste alto para páginas com visual premium.',
                'settings' => array_merge(
                    self::appearance_defaults(),
                    array(
                        'preset' => 'dark',
                        'form_background' => '#111827',
                        'form_border_color' => '#374151',
                        'form_text_color' => '#f9fafb',
                        'title_color' => '#ffffff',
                        'description_color' => '#cbd5e1',
                        'field_background' => '#1f2937',
                        'field_border_color' => '#4b5563',
                        'field_text_color' => '#f9fafb',
                        'button_background' => '#f97316',
                        'button_hover_background' => '#ea580c',
                        'shadow' => 'strong',
                        'conversion_background' => '#1f2937',
                        'conversion_border_color' => '#4b5563',
                        'conversion_title_color' => '#ffffff',
                        'conversion_description_color' => '#cbd5e1',
                        'conversion_icon_color' => '#22c55e',
                        'conversion_button_background' => '#f97316',
                        'conversion_button_hover_background' => '#ea580c',
                        'conversion_shadow' => 'strong',
                    )
                ),
            ),
        );
    }

    public static function appearance_defaults(): array
    {
        return array(
            'preset' => 'classic_f10',
            'form_max_width' => '820',
            'alignment' => 'center',
            'desktop_columns' => '2',
            'mobile_columns' => '1',
            'padding_desktop' => '48',
            'padding_mobile' => '24',
            'field_gap' => '18',
            'form_background' => '#ffffff',
            'form_border_color' => '#d9dee8',
            'form_border_width' => '1',
            'form_radius' => '24',
            'form_text_color' => '#101828',
            'title_color' => '#000a57',
            'description_color' => '#667085',
            'field_background' => '#ffffff',
            'field_border_color' => '#d9dee8',
            'field_text_color' => '#101828',
            'field_radius' => '12',
            'button_background' => '#ea6d0b',
            'button_hover_background' => '#d85f00',
            'button_text_color' => '#ffffff',
            'button_radius' => '12',
            'button_width' => 'auto',
            'title_size_desktop' => '38',
            'title_size_mobile' => '30',
            'description_size' => '16',
            'shadow' => 'subtle',
            'conversion_background' => '#f8fafc',
            'conversion_border_color' => '#d9dee8',
            'conversion_border_width' => '1',
            'conversion_radius' => '16',
            'conversion_padding' => '24',
            'conversion_title_color' => '#000a57',
            'conversion_description_color' => '#667085',
            'conversion_icon_color' => '#067647',
            'conversion_button_background' => '#ea6d0b',
            'conversion_button_hover_background' => '#d85f00',
            'conversion_button_text_color' => '#ffffff',
            'conversion_button_radius' => '12',
            'conversion_button_width' => 'auto',
            'conversion_title_size' => '22',
            'conversion_shadow' => 'subtle',
        );
    }

    public static function get_settings(): array
    {
        return wp_parse_args((array) get_option('f10_lead_capture_settings', array()), self::default_settings());
    }

    public static function get_appearance(): array
    {
        return wp_parse_args((array) get_option('f10_lead_capture_appearance', array()), self::appearance_defaults());
    }

    public static function get_forms(): array
    {
        $stored = get_option(self::FORMS_OPTION, array());
        $forms = array();

        if (is_array($stored)) {
            foreach ($stored as $form_id => $form) {
                if (!is_array($form)) {
                    continue;
                }

                $normalized = self::normalize_form($form, (string) $form_id);
                $forms[$normalized['id']] = $normalized;
            }
        }

        if (!$forms) {
            $default = self::default_form(self::get_settings(), (array) get_option('f10_lead_capture_conversion', array()));
            $forms[$default['id']] = $default;
        }

        return $forms;
    }

    public static function get_form(string $form_id): ?array
    {
        $form_id = self::sanitize_form_id($form_id);
        $forms = self::get_forms();

        if (isset($forms[$form_id])) {
            return $forms[$form_id];
        }

        return null;
    }

    public static function get_default_form(): array
    {
        $forms = self::get_forms();

        if (isset($forms[self::DEFAULT_FORM_ID])) {
            return $forms[self::DEFAULT_FORM_ID];
        }

        $first = reset($forms);
        return is_array($first) ? $first : self::default_form();
    }

    public static function save_forms(array $forms): bool
    {
        $normalized = array();

        foreach ($forms as $form_id => $form) {
            if (!is_array($form)) {
                continue;
            }

            $item = self::normalize_form($form, (string) $form_id);
            $normalized[$item['id']] = $item;
        }

        if (!$normalized) {
            $default = self::default_form();
            $normalized[$default['id']] = $default;
        }

        return update_option(self::FORMS_OPTION, $normalized, false);
    }

    public static function sanitize_form_id(string $value): string
    {
        $value = sanitize_title($value);
        $value = preg_replace('/[^a-z0-9\-_]/', '', $value) ?: '';
        return substr($value, 0, 100);
    }

    public static function conversion_url(array $conversion): string
    {
        if (($conversion['type'] ?? '') === 'download') {
            $file_id = absint($conversion['file_id'] ?? 0);

            if ($file_id > 0) {
                $attachment_url = wp_get_attachment_url($file_id);

                if (is_string($attachment_url) && $attachment_url !== '') {
                    return esc_url_raw($attachment_url);
                }
            }

            return esc_url_raw((string) ($conversion['file_url'] ?? ''));
        }

        return esc_url_raw((string) ($conversion['link_url'] ?? ''));
    }

    public static function conversion_token(int $lead_id): string
    {
        return hash_hmac('sha256', 'f10-conversion|' . $lead_id, wp_salt('nonce'));
    }
}
