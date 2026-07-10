<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10_Lead_Capture_Config
{
    public const F10_ENDPOINT = 'https://nuvem.f10.com.br/fx-api/digitacao';
    public const F10_API_TYPE = 2;

    public static function form_fields(): array
    {
        return array(
            'name' => array(
                'request_key' => 'name',
                'label' => 'Nome',
                'enabled' => '1',
                'required' => true,
                'type' => 'text',
                'autocomplete' => 'name',
                'max_length' => 190,
            ),
            'course' => array(
                'request_key' => 'product',
                'label' => 'Curso ou interesse',
                'enabled' => '0',
                'required' => false,
                'type' => 'text',
                'autocomplete' => '',
                'max_length' => 190,
            ),
            'phone' => array(
                'request_key' => 'phone',
                'label' => 'Telefone',
                'enabled' => '0',
                'required' => false,
                'type' => 'tel',
                'autocomplete' => 'tel',
                'max_length' => 20,
            ),
            'whatsapp' => array(
                'request_key' => 'whatsapp',
                'label' => 'WhatsApp',
                'enabled' => '1',
                'required' => true,
                'type' => 'tel',
                'autocomplete' => 'tel',
                'max_length' => 20,
            ),
            'email' => array(
                'request_key' => 'email',
                'label' => 'E-mail',
                'enabled' => '1',
                'required' => true,
                'type' => 'email',
                'autocomplete' => 'email',
                'max_length' => 190,
            ),
            'school' => array(
                'request_key' => 'institution_name',
                'label' => 'Nome da escola ou empresa',
                'enabled' => '1',
                'required' => false,
                'type' => 'text',
                'autocomplete' => 'organization',
                'max_length' => 190,
            ),
            'notes' => array(
                'request_key' => 'notes',
                'label' => 'Observações',
                'enabled' => '0',
                'required' => false,
                'type' => 'textarea',
                'autocomplete' => '',
                'max_length' => 2000,
            ),
        );
    }

    public static function default_settings(): array
    {
        $defaults = array(
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

        foreach (self::form_fields() as $field_key => $field) {
            $defaults['field_' . $field_key . '_enabled'] = (string) $field['enabled'];
            $defaults['field_' . $field_key . '_label'] = (string) $field['label'];
        }

        return $defaults;
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
                        'form_background' => '#ffffff',
                        'form_border_color' => '#e5e7eb',
                        'form_border_width' => '0',
                        'form_radius' => '8',
                        'field_radius' => '8',
                        'button_radius' => '8',
                        'button_background' => '#111827',
                        'button_hover_background' => '#000000',
                        'title_color' => '#111827',
                        'shadow' => 'none',
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
                        'field_background' => '#ffffff',
                        'field_border_color' => '#cbd5e1',
                        'button_background' => '#2563eb',
                        'button_hover_background' => '#1d4ed8',
                        'title_color' => '#0f172a',
                        'form_radius' => '20',
                        'shadow' => 'subtle',
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
                        'button_text_color' => '#ffffff',
                        'shadow' => 'strong',
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
        );
    }

    public static function conversion_defaults(): array
    {
        return array(
            'enabled' => '0',
            'type' => 'download',
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

    public static function get_settings(): array
    {
        return wp_parse_args(
            (array) get_option('f10_lead_capture_settings', array()),
            self::default_settings()
        );
    }

    public static function get_appearance(): array
    {
        return wp_parse_args(
            (array) get_option('f10_lead_capture_appearance', array()),
            self::appearance_defaults()
        );
    }

    public static function get_conversion(): array
    {
        return wp_parse_args(
            (array) get_option('f10_lead_capture_conversion', array()),
            self::conversion_defaults()
        );
    }

    public static function is_field_enabled(string $field_key, array $settings): bool
    {
        return isset($settings['field_' . $field_key . '_enabled'])
            && $settings['field_' . $field_key . '_enabled'] === '1';
    }

    public static function field_label(string $field_key, array $settings): string
    {
        $fields = self::form_fields();
        $default_label = isset($fields[$field_key]['label']) ? (string) $fields[$field_key]['label'] : $field_key;
        $configured_label = isset($settings['field_' . $field_key . '_label'])
            ? trim((string) $settings['field_' . $field_key . '_label'])
            : '';

        return $configured_label !== '' ? $configured_label : $default_label;
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
