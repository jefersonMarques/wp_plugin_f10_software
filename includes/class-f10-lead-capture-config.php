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

    public static function get_settings(): array
    {
        return wp_parse_args(
            (array) get_option('f10_lead_capture_settings', array()),
            self::default_settings()
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
}
