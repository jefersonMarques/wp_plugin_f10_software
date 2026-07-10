<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10_Lead_Capture_Activator
{
    public static function activate(): void
    {
        self::create_table();
        self::create_default_settings();
        self::schedule_retry_event();
    }

    private static function create_table(): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'f10_leads';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(190) NOT NULL,
            whatsapp varchar(30) NOT NULL,
            email varchar(190) NOT NULL,
            institution_name varchar(190) NULL,
            product varchar(190) NULL,
            form_id varchar(100) NOT NULL DEFAULT 'default',
            source_label varchar(190) NULL,
            sub_source varchar(190) NULL,
            page_url text NULL,
            referrer_url text NULL,
            utm_source varchar(190) NULL,
            utm_medium varchar(190) NULL,
            utm_campaign varchar(190) NULL,
            utm_term varchar(190) NULL,
            utm_content varchar(190) NULL,
            ip_hash char(64) NULL,
            user_agent text NULL,
            consent_at datetime NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            f10_status varchar(20) NOT NULL DEFAULT 'pending',
            brevo_status varchar(20) NOT NULL DEFAULT 'pending',
            f10_http_status smallint(5) unsigned NULL,
            brevo_http_status smallint(5) unsigned NULL,
            f10_response longtext NULL,
            brevo_response longtext NULL,
            attempts smallint(5) unsigned NOT NULL DEFAULT 0,
            last_error text NULL,
            last_attempt_at datetime NULL,
            next_retry_at datetime NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY status_retry (status, next_retry_at),
            KEY created_at (created_at),
            KEY email (email)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    private static function create_default_settings(): void
    {
        $option_name = 'f10_lead_capture_settings';

        if (get_option($option_name, null) !== null) {
            return;
        }

        add_option(
            $option_name,
            array(
                'f10_enabled' => '1',
                'f10_url' => '',
                'f10_token' => '',
                'f10_unit_id' => '',
                'f10_source' => 'Site',
                'f10_media' => 'WordPress',
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
            ),
            '',
            false
        );
    }

    private static function schedule_retry_event(): void
    {
        if (!wp_next_scheduled('f10_lead_capture_retry_event')) {
            wp_schedule_event(time() + 300, 'hourly', 'f10_lead_capture_retry_event');
        }
    }
}
