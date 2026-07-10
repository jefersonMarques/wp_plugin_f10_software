<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10_Lead_Capture_Integrations
{
    private const BREVO_ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    public static function process_lead(int $lead_id): array
    {
        $lead = F10_Lead_Capture_Repository::get($lead_id);

        if (!$lead) {
            return array(
                'ok' => false,
                'error' => 'lead_not_found',
            );
        }

        $settings = self::get_settings();
        $attempts = ((int) $lead['attempts']) + 1;
        $now = current_time('mysql', true);

        F10_Lead_Capture_Repository::update(
            $lead_id,
            array(
                'attempts' => $attempts,
                'last_attempt_at' => $now,
                'next_retry_at' => null,
            )
        );

        $f10_result = self::resolve_f10_result($lead, $settings);
        $brevo_result = self::resolve_brevo_result($lead, $settings);

        $update_data = array(
            'f10_status' => $f10_result['status'],
            'f10_http_status' => $f10_result['http_status'],
            'f10_response' => self::limit_response($f10_result['response']),
            'brevo_status' => $brevo_result['status'],
            'brevo_http_status' => $brevo_result['http_status'],
            'brevo_response' => self::limit_response($brevo_result['response']),
        );

        $final_result = self::build_final_result(
            $settings,
            $f10_result,
            $brevo_result,
            $attempts
        );

        $update_data['status'] = $final_result['status'];
        $update_data['last_error'] = $final_result['last_error'];
        $update_data['next_retry_at'] = $final_result['next_retry_at'];

        F10_Lead_Capture_Repository::update($lead_id, $update_data);

        return array(
            'ok' => in_array($final_result['status'], array('completed', 'stored'), true),
            'status' => $final_result['status'],
            'f10' => $f10_result,
            'brevo' => $brevo_result,
        );
    }

    public static function retry_pending_leads(): void
    {
        $settings = self::get_settings();
        $max_attempts = max(1, min(10, (int) $settings['max_retry_attempts']));
        $leads = F10_Lead_Capture_Repository::find_retryable(20, $max_attempts);

        foreach ($leads as $lead) {
            self::process_lead((int) $lead['id']);
        }
    }

    public static function inspect_jwt(string $token): array
    {
        $token = trim($token);

        if ($token === '') {
            return array(
                'valid' => false,
                'expired' => false,
                'message' => 'Token não informado.',
            );
        }

        $parts = explode('.', $token);

        if (count($parts) < 2) {
            return array(
                'valid' => false,
                'expired' => false,
                'message' => 'O token informado não possui o formato JWT esperado pela integração F10.',
            );
        }

        $payload = strtr($parts[1], '-_', '+/');
        $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);
        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            return array(
                'valid' => false,
                'expired' => false,
                'message' => 'Não foi possível decodificar o token F10.',
            );
        }

        $data = json_decode($decoded, true);

        if (!is_array($data)) {
            return array(
                'valid' => false,
                'expired' => false,
                'message' => 'O conteúdo do token F10 é inválido.',
            );
        }

        $expires_at = isset($data['exp']) && is_numeric($data['exp'])
            ? (int) $data['exp']
            : null;
        $expired = $expires_at !== null && $expires_at <= time();

        return array(
            'valid' => true,
            'expired' => $expired,
            'expires_at' => $expires_at,
            'payload' => $data,
            'message' => $expired ? 'O token F10 está vencido.' : 'Token F10 válido.',
        );
    }

    private static function resolve_f10_result(array $lead, array $settings): array
    {
        if ($lead['f10_status'] === 'sent') {
            return self::result('sent', (int) $lead['f10_http_status'], (string) $lead['f10_response'], '');
        }

        if ($settings['f10_enabled'] !== '1') {
            return self::result('skipped', null, 'Integração F10 desativada.', '');
        }

        return self::send_to_f10($lead, $settings);
    }

    private static function resolve_brevo_result(array $lead, array $settings): array
    {
        if ($lead['brevo_status'] === 'sent') {
            return self::result('sent', (int) $lead['brevo_http_status'], (string) $lead['brevo_response'], '');
        }

        if ($settings['brevo_enabled'] !== '1') {
            return self::result('skipped', null, 'Envio por Brevo desativado.', '');
        }

        return self::send_to_brevo($lead, $settings);
    }

    private static function send_to_f10(array $lead, array $settings): array
    {
        $token = trim((string) $settings['f10_token']);
        $unit_id = absint($settings['f10_unit_id']);
        $source = trim((string) $settings['f10_source']);
        $media = trim((string) $settings['f10_media']);

        if ($token === '' || $unit_id <= 0 || $source === '' || $media === '') {
            return self::result(
                'failed',
                null,
                'Configuração incompleta: token, unidade, fonte e mídia são obrigatórios para enviar à F10.',
                'incomplete_f10_configuration'
            );
        }

        $jwt_status = self::inspect_jwt($token);

        if (!$jwt_status['valid'] || $jwt_status['expired']) {
            return self::result('failed', null, $jwt_status['message'], 'invalid_or_expired_f10_token');
        }

        $phone = self::normalize_phone((string) ($lead['phone'] ?? ''));
        $whatsapp = self::normalize_phone((string) ($lead['whatsapp'] ?? ''));
        $page_url = trim((string) ($lead['page_url'] ?? ''));
        $page_path = wp_parse_url($page_url, PHP_URL_PATH);
        $observation = self::build_lead_description($lead);

        if ($phone === '') {
            $phone = $whatsapp;
        }

        if ($whatsapp === '') {
            $whatsapp = $phone;
        }

        $body = array(
            'token' => $token,
            'tipo_api' => F10_Lead_Capture_Config::F10_API_TYPE,
            'unidade_id' => (string) $unit_id,
            'fonte' => $source,
            'midia' => $media,
            'nome' => (string) $lead['name'],
            'curso' => (string) ($lead['product'] ?? ''),
            'telefone' => $phone,
            'celular' => $whatsapp,
            'email' => (string) ($lead['email'] ?? ''),
            'colegio' => (string) ($lead['institution_name'] ?? ''),
            'obs' => $observation,
            'extra1' => is_string($page_path) && $page_path !== '' ? $page_path : '/',
            'extra2' => $page_url !== '' ? $page_url : home_url('/'),
        );

        $response = wp_safe_remote_post(
            F10_Lead_Capture_Config::F10_ENDPOINT,
            array(
                'timeout' => 10,
                'redirection' => 2,
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ),
                'body' => wp_json_encode($body),
                'data_format' => 'body',
            )
        );

        if (is_wp_error($response)) {
            return self::result('failed', null, $response->get_error_message(), $response->get_error_code());
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        $response_body = (string) wp_remote_retrieve_body($response);

        if ($status_code < 200 || $status_code >= 300) {
            return self::result('failed', $status_code, $response_body, 'f10_http_error');
        }

        return self::result('sent', $status_code, $response_body, '');
    }

    private static function send_to_brevo(array $lead, array $settings): array
    {
        $api_key = trim((string) $settings['brevo_api_key']);
        $recipient_email = sanitize_email((string) $settings['brevo_recipient_email']);
        $sender_email = sanitize_email((string) $settings['brevo_sender_email']);
        $sender_name = trim((string) $settings['brevo_sender_name']) ?: 'Leads F10';

        if ($api_key === '' || !is_email($recipient_email) || !is_email($sender_email)) {
            return self::result(
                'failed',
                null,
                'Configuração incompleta: chave da API, destinatário e remetente verificado são obrigatórios no Brevo.',
                'incomplete_brevo_configuration'
            );
        }

        $subject = sprintf('[Lead WordPress] %s', (string) $lead['name']);
        $body = array(
            'sender' => array(
                'email' => $sender_email,
                'name' => $sender_name,
            ),
            'to' => array(
                array(
                    'email' => $recipient_email,
                    'name' => 'Comercial',
                ),
            ),
            'subject' => $subject,
            'htmlContent' => self::build_brevo_html($lead),
            'textContent' => self::build_lead_description($lead),
            'tags' => array('lead', 'wordpress', 'f10'),
        );

        if (is_email((string) ($lead['email'] ?? ''))) {
            $body['replyTo'] = array(
                'email' => (string) $lead['email'],
                'name' => (string) $lead['name'],
            );
        }

        $response = wp_safe_remote_post(
            self::BREVO_ENDPOINT,
            array(
                'timeout' => 10,
                'redirection' => 2,
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'api-key' => $api_key,
                ),
                'body' => wp_json_encode($body),
                'data_format' => 'body',
            )
        );

        if (is_wp_error($response)) {
            return self::result('failed', null, $response->get_error_message(), $response->get_error_code());
        }

        $status_code = (int) wp_remote_retrieve_response_code($response);
        $response_body = (string) wp_remote_retrieve_body($response);

        if ($status_code < 200 || $status_code >= 300) {
            return self::result('failed', $status_code, $response_body, 'brevo_http_error');
        }

        return self::result('sent', $status_code, $response_body, '');
    }

    private static function build_final_result(
        array $settings,
        array $f10_result,
        array $brevo_result,
        int $attempts
    ): array {
        $enabled_results = array();

        if ($settings['f10_enabled'] === '1') {
            $enabled_results['F10'] = $f10_result;
        }

        if ($settings['brevo_enabled'] === '1') {
            $enabled_results['Brevo'] = $brevo_result;
        }

        if (!$enabled_results) {
            return array(
                'status' => 'stored',
                'last_error' => null,
                'next_retry_at' => null,
            );
        }

        $sent_count = 0;
        $errors = array();

        foreach ($enabled_results as $integration_name => $result) {
            if ($result['status'] === 'sent') {
                $sent_count++;
                continue;
            }

            $errors[] = $integration_name . ': ' . ($result['error'] ?: $result['response']);
        }

        if ($sent_count === count($enabled_results)) {
            return array(
                'status' => 'completed',
                'last_error' => null,
                'next_retry_at' => null,
            );
        }

        $max_attempts = max(1, min(10, (int) $settings['max_retry_attempts']));
        $next_retry_at = $attempts < $max_attempts
            ? gmdate('Y-m-d H:i:s', time() + self::retry_delay_seconds($attempts))
            : null;

        return array(
            'status' => $sent_count > 0 ? 'partial' : 'failed',
            'last_error' => implode(' | ', array_filter($errors)),
            'next_retry_at' => $next_retry_at,
        );
    }

    private static function retry_delay_seconds(int $attempts): int
    {
        $delays = array(
            1 => 5 * MINUTE_IN_SECONDS,
            2 => 30 * MINUTE_IN_SECONDS,
            3 => 2 * HOUR_IN_SECONDS,
            4 => 6 * HOUR_IN_SECONDS,
        );

        return $delays[$attempts] ?? 12 * HOUR_IN_SECONDS;
    }

    private static function build_lead_description(array $lead): string
    {
        $lines = array(
            'Lead capturado pelo plugin F10 Lead Capture no WordPress.',
            'Nome: ' . (string) $lead['name'],
        );

        if (!empty($lead['phone'])) {
            $lines[] = 'Telefone: ' . (string) $lead['phone'];
        }

        if (!empty($lead['whatsapp'])) {
            $lines[] = 'WhatsApp: ' . (string) $lead['whatsapp'];
        }

        if (!empty($lead['email'])) {
            $lines[] = 'E-mail: ' . (string) $lead['email'];
        }

        if (!empty($lead['institution_name'])) {
            $lines[] = 'Escola/empresa: ' . (string) $lead['institution_name'];
        }

        if (!empty($lead['product'])) {
            $lines[] = 'Produto/interesse: ' . (string) $lead['product'];
        }

        if (!empty($lead['notes'])) {
            $lines[] = 'Observações: ' . (string) $lead['notes'];
        }

        if (!empty($lead['source_label'])) {
            $lines[] = 'Origem: ' . (string) $lead['source_label'];
        }

        if (!empty($lead['sub_source'])) {
            $lines[] = 'Suborigem: ' . (string) $lead['sub_source'];
        }

        if (!empty($lead['page_url'])) {
            $lines[] = 'Página de captura: ' . (string) $lead['page_url'];
        }

        if (!empty($lead['referrer_url'])) {
            $lines[] = 'Referência: ' . (string) $lead['referrer_url'];
        }

        foreach (array('utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content') as $utm_key) {
            if (!empty($lead[$utm_key])) {
                $lines[] = strtoupper($utm_key) . ': ' . (string) $lead[$utm_key];
            }
        }

        $lines[] = 'Criado em: ' . (string) $lead['created_at'] . ' UTC';

        return implode("\n", $lines);
    }

    private static function build_brevo_html(array $lead): string
    {
        $rows = array(
            'Nome' => $lead['name'],
            'Telefone' => $lead['phone'] ?? '',
            'WhatsApp' => $lead['whatsapp'] ?? '',
            'E-mail' => $lead['email'] ?? '',
            'Escola/empresa' => $lead['institution_name'],
            'Produto/interesse' => $lead['product'],
            'Observações' => $lead['notes'] ?? '',
            'Origem' => $lead['source_label'],
            'Suborigem' => $lead['sub_source'],
            'Página' => $lead['page_url'],
            'Referência' => $lead['referrer_url'],
            'UTM source' => $lead['utm_source'],
            'UTM medium' => $lead['utm_medium'],
            'UTM campaign' => $lead['utm_campaign'],
            'Recebido em' => get_date_from_gmt((string) $lead['created_at'], 'd/m/Y H:i:s'),
        );

        $rows_html = '';

        foreach ($rows as $label => $value) {
            if (trim((string) $value) === '') {
                continue;
            }

            $rows_html .= sprintf(
                '<tr><td style="padding:10px 12px;border-bottom:1px solid #e5e7eb;font-weight:700;width:180px;">%s</td><td style="padding:10px 12px;border-bottom:1px solid #e5e7eb;">%s</td></tr>',
                esc_html($label),
                esc_html((string) $value)
            );
        }

        return sprintf(
            '<div style="font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;padding:24px"><div style="max-width:720px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:16px;overflow:hidden"><div style="background:#000a57;color:#fff;padding:20px"><strong style="font-size:20px">Novo lead capturado</strong><div style="margin-top:5px;opacity:.85">F10 Lead Capture</div></div><div style="padding:20px"><table style="width:100%%;border-collapse:collapse">%s</table><p style="margin:20px 0 0;color:#6b7280;font-size:12px">O lead foi armazenado no banco de dados do WordPress antes do envio desta notificação.</p></div></div></div>',
            $rows_html
        );
    }

    private static function result(string $status, ?int $http_status, string $response, string $error): array
    {
        return array(
            'status' => $status,
            'http_status' => $http_status,
            'response' => $response,
            'error' => $error,
        );
    }

    private static function normalize_phone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?: '';
    }

    private static function limit_response(string $response): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($response, 0, 20000);
        }

        return substr($response, 0, 20000);
    }

    private static function get_settings(): array
    {
        return F10_Lead_Capture_Config::get_settings();
    }
}
