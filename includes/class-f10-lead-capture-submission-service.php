<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10_Lead_Capture_Submission_Service
{
    public static function submit(array $lead_data): array
    {
        $defaults = array(
            'name' => 'Lead WordPress',
            'phone' => '',
            'whatsapp' => '',
            'email' => '',
            'institution_name' => '',
            'product' => '',
            'notes' => '',
            'form_id' => 'default',
            'source_label' => 'WordPress',
            'sub_source' => '',
            'page_url' => '',
            'referrer_url' => '',
            'utm_source' => '',
            'utm_medium' => '',
            'utm_campaign' => '',
            'utm_term' => '',
            'utm_content' => '',
            'ip_hash' => '',
            'user_agent' => '',
            'consent_at' => null,
            'conversion_type' => 'none',
            'conversion_status' => 'none',
            'conversion_url' => '',
            'conversion_label' => '',
            'conversion_behavior' => '',
        );
        $lead_data = wp_parse_args($lead_data, $defaults);
        $lead_id = F10_Lead_Capture_Repository::create($lead_data);

        if ($lead_id <= 0) {
            return array(
                'ok' => false,
                'lead_id' => 0,
                'message' => 'Não foi possível registrar seus dados. Tente novamente em instantes.',
            );
        }

        do_action('f10_lead_capture_created', $lead_id, $lead_data);

        try {
            F10_Lead_Capture_Integrations::process_lead($lead_id);
        } catch (Throwable $exception) {
            F10_Lead_Capture_Repository::update(
                $lead_id,
                array(
                    'status' => 'failed',
                    'last_error' => $exception->getMessage(),
                    'next_retry_at' => gmdate('Y-m-d H:i:s', time() + 5 * MINUTE_IN_SECONDS),
                )
            );
        }

        return array(
            'ok' => true,
            'lead_id' => $lead_id,
            'message' => '',
        );
    }
}
