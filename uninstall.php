<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$settings = (array) get_option('f10_lead_capture_settings', array());

if (($settings['delete_data_on_uninstall'] ?? '0') !== '1') {
    return;
}

global $wpdb;
$table_name = $wpdb->prefix . 'f10_leads';

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- A exclusão da tabela ocorre somente após consentimento explícito do administrador na desinstalação.
$wpdb->query(
    $wpdb->prepare(
        'DROP TABLE IF EXISTS %i',
        $table_name
    )
);

delete_option('f10_lead_capture_settings');
delete_option('f10_lead_capture_appearance');
delete_option('f10_lead_capture_conversion');
delete_option('f10_lead_capture_forms');
delete_option('f10_lead_capture_db_version');
wp_clear_scheduled_hook('f10_lead_capture_retry_event');
