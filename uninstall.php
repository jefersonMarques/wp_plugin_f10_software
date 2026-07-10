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

$wpdb->query("DROP TABLE IF EXISTS {$table_name}");
delete_option('f10_lead_capture_settings');

wp_clear_scheduled_hook('f10_lead_capture_retry_event');
