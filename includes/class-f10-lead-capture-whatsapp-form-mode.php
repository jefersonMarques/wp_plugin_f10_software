<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10_Lead_Capture_WhatsApp_Form_Mode
{
    public static function apply_posted_mode($new_value, $old_value)
    {
        if (!is_array($new_value) || !is_admin() || !current_user_can('manage_options')) {
            return $new_value;
        }

        $action = isset($_POST['action']) && is_scalar($_POST['action'])
            ? sanitize_key(wp_unslash((string) $_POST['action']))
            : '';

        if ($action !== 'f10_lead_capture_save_whatsapp') {
            return $new_value;
        }

        $nonce = isset($_POST['_wpnonce']) && is_scalar($_POST['_wpnonce'])
            ? sanitize_text_field(wp_unslash((string) $_POST['_wpnonce']))
            : '';

        if ($nonce === '' || !wp_verify_nonce($nonce, 'f10_lead_capture_save_whatsapp')) {
            return $new_value;
        }

        $raw_input = filter_input(
            INPUT_POST,
            'f10_whatsapp',
            FILTER_UNSAFE_RAW,
            FILTER_REQUIRE_ARRAY
        );
        $input = is_array($raw_input)
            ? map_deep($raw_input, 'sanitize_text_field')
            : array();
        $mode = sanitize_key((string) ($input['form_display_mode'] ?? 'smart'));

        if (!in_array($mode, array('always', 'smart', 'never'), true)) {
            $mode = 'smart';
        }

        $requested_id = F10_Lead_Capture_WhatsApp_Config::sanitize_widget_id(
            (string) ($input['id'] ?? '')
        );
        $original_id = F10_Lead_Capture_WhatsApp_Config::sanitize_widget_id(
            (string) ($input['original_id'] ?? '')
        );

        if ($requested_id === '') {
            $requested_id = F10_Lead_Capture_WhatsApp_Config::sanitize_widget_id(
                (string) ($input['name'] ?? '')
            );
        }

        $target_id = '';

        if ($requested_id !== '' && isset($new_value[$requested_id])) {
            $target_id = $requested_id;
        } elseif ($original_id !== '' && isset($new_value[$original_id])) {
            $target_id = $original_id;
        } else {
            $posted_phone = F10_Lead_Capture_WhatsApp_Config::normalize_phone(
                (string) ($input['phone'] ?? '')
            );
            $posted_name = sanitize_text_field((string) ($input['name'] ?? ''));

            foreach ($new_value as $widget_id => $widget) {
                if (!is_array($widget)) {
                    continue;
                }

                if (
                    (string) ($widget['phone'] ?? '') === $posted_phone
                    && (string) ($widget['name'] ?? '') === $posted_name
                ) {
                    $target_id = (string) $widget_id;
                    break;
                }
            }
        }

        if ($target_id !== '' && isset($new_value[$target_id]) && is_array($new_value[$target_id])) {
            $new_value[$target_id]['form_display_mode'] = $mode;
        }

        return $new_value;
    }
}
