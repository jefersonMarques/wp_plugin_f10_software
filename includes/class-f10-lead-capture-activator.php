<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10_Lead_Capture_Activator
{
    private const DB_VERSION = '1.4.0';
    private const DB_VERSION_OPTION = 'f10_lead_capture_db_version';

    public static function activate(): void
    {
        self::create_table();
        self::ensure_settings();
        self::ensure_forms();
        self::schedule_retry_event();
        F10_Lead_Capture_Integrations::reconcile_stored_f10_results();
        update_option(self::DB_VERSION_OPTION, self::DB_VERSION, false);
    }

    public static function maybe_upgrade(): void
    {
        if ((string) get_option(self::DB_VERSION_OPTION, '') === self::DB_VERSION) {
            return;
        }

        self::create_table();
        self::ensure_settings();
        self::ensure_forms();
        F10_Lead_Capture_Integrations::reconcile_stored_f10_results();
        update_option(self::DB_VERSION_OPTION, self::DB_VERSION, false);
    }

    private static function create_table(): void
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'f10_leads';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(190) NOT NULL,
            phone varchar(30) NULL,
            whatsapp varchar(30) NOT NULL,
            email varchar(190) NOT NULL,
            institution_name varchar(190) NULL,
            product varchar(190) NULL,
            notes text NULL,
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
            conversion_type varchar(20) NOT NULL DEFAULT 'none',
            conversion_status varchar(20) NOT NULL DEFAULT 'none',
            conversion_url text NULL,
            conversion_label varchar(190) NULL,
            conversion_behavior varchar(20) NULL,
            conversion_count int(10) unsigned NOT NULL DEFAULT 0,
            conversion_at datetime NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY status_retry (status, next_retry_at),
            KEY created_at (created_at),
            KEY email (email),
            KEY conversion_status (conversion_status, conversion_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    private static function ensure_settings(): void
    {
        $option_name = 'f10_lead_capture_settings';
        $current = get_option($option_name, null);
        $settings = wp_parse_args(
            is_array($current) ? $current : array(),
            F10_Lead_Capture_Config::default_settings()
        );

        unset($settings['f10_url']);

        if ($current === null) {
            add_option($option_name, $settings, '', false);
        } else {
            update_option($option_name, $settings, false);
        }

        self::ensure_option(
            'f10_lead_capture_appearance',
            F10_Lead_Capture_Config::appearance_defaults()
        );
    }

    private static function ensure_forms(): void
    {
        $current = get_option(F10_Lead_Capture_Config::FORMS_OPTION, null);

        if (is_array($current) && !empty($current)) {
            F10_Lead_Capture_Config::save_forms($current);
            return;
        }

        $default = F10_Lead_Capture_Config::default_form(
            F10_Lead_Capture_Config::get_settings(),
            (array) get_option('f10_lead_capture_conversion', array())
        );

        add_option(
            F10_Lead_Capture_Config::FORMS_OPTION,
            array($default['id'] => $default),
            '',
            false
        );
    }

    private static function ensure_option(string $option_name, array $defaults): void
    {
        $current = get_option($option_name, null);
        $value = wp_parse_args(is_array($current) ? $current : array(), $defaults);

        if ($current === null) {
            add_option($option_name, $value, '', false);
            return;
        }

        update_option($option_name, $value, false);
    }

    private static function schedule_retry_event(): void
    {
        if (!wp_next_scheduled('f10_lead_capture_retry_event')) {
            wp_schedule_event(time() + 300, 'hourly', 'f10_lead_capture_retry_event');
        }
    }
}
