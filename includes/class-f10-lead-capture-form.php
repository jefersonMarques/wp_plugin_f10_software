<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10_Lead_Capture_Form
{
    private static int $instance_count = 0;

    public function register_hooks(): void
    {
        add_shortcode('f10_lead_form', array($this, 'render_shortcode'));
        add_action('wp_ajax_f10_submit_lead', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_f10_submit_lead', array($this, 'handle_submission'));
        add_action('wp_ajax_f10_track_conversion', array($this, 'handle_conversion_tracking'));
        add_action('wp_ajax_nopriv_f10_track_conversion', array($this, 'handle_conversion_tracking'));
    }

    public function render_shortcode(array $attributes = array()): string
    {
        $attributes = shortcode_atts(
            array(
                'title' => 'Fale com um especialista',
                'description' => 'Preencha seus dados e a equipe entrará em contato.',
                'button' => 'Quero saber mais',
                'product' => 'Software F10',
                'form_id' => 'wordpress-form',
                'source' => 'WordPress',
                'sub_source' => 'Formulário de conteúdo',
                'show_institution' => 'yes',
                'redirect_url' => '',
            ),
            $attributes,
            'f10_lead_form'
        );

        self::$instance_count++;
        $form_identifier = 'f10-lead-form-' . self::$instance_count;
        $settings = $this->get_settings();
        $appearance = F10_Lead_Capture_Config::get_appearance();
        $require_consent = $settings['require_consent'] === '1';
        $show_institution = strtolower((string) $attributes['show_institution']) !== 'no';
        $wrapper_classes = $this->appearance_classes($appearance);
        $wrapper_style = $this->appearance_style($appearance);

        wp_enqueue_style(
            'f10-lead-capture-form',
            F10_LEAD_CAPTURE_URL . 'assets/css/form.css',
            array(),
            F10_LEAD_CAPTURE_VERSION
        );

        wp_enqueue_script(
            'f10-lead-capture-form',
            F10_LEAD_CAPTURE_URL . 'assets/js/form.js',
            array(),
            F10_LEAD_CAPTURE_VERSION,
            true
        );

        ob_start();
        ?>
        <div
            class="<?php echo esc_attr(implode(' ', $wrapper_classes)); ?>"
            style="<?php echo esc_attr($wrapper_style); ?>"
            data-f10-lead-container
        >
            <div class="f10-lead-capture__header">
                <?php if (trim((string) $attributes['title']) !== '') : ?>
                    <h2 class="f10-lead-capture__title"><?php echo esc_html((string) $attributes['title']); ?></h2>
                <?php endif; ?>

                <?php if (trim((string) $attributes['description']) !== '') : ?>
                    <p class="f10-lead-capture__description"><?php echo esc_html((string) $attributes['description']); ?></p>
                <?php endif; ?>
            </div>

            <form
                id="<?php echo esc_attr($form_identifier); ?>"
                class="f10-lead-capture__form"
                method="post"
                action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
                data-f10-lead-form
                novalidate
            >
                <input type="hidden" name="action" value="f10_submit_lead">
                <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('f10_lead_submit')); ?>">
                <input type="hidden" name="form_loaded_at" value="<?php echo esc_attr((string) time()); ?>">
                <input type="hidden" name="form_id" value="<?php echo esc_attr((string) $attributes['form_id']); ?>">
                <input type="hidden" name="default_product" value="<?php echo esc_attr((string) $attributes['product']); ?>">
                <input type="hidden" name="source_label" value="<?php echo esc_attr((string) $attributes['source']); ?>">
                <input type="hidden" name="sub_source" value="<?php echo esc_attr((string) $attributes['sub_source']); ?>">
                <input type="hidden" name="redirect_url" value="<?php echo esc_url((string) $attributes['redirect_url']); ?>">
                <input type="hidden" name="page_url" value="" data-f10-page-url>
                <input type="hidden" name="referrer_url" value="" data-f10-referrer-url>
                <input type="hidden" name="utm_source" value="" data-f10-utm="utm_source">
                <input type="hidden" name="utm_medium" value="" data-f10-utm="utm_medium">
                <input type="hidden" name="utm_campaign" value="" data-f10-utm="utm_campaign">
                <input type="hidden" name="utm_term" value="" data-f10-utm="utm_term">
                <input type="hidden" name="utm_content" value="" data-f10-utm="utm_content">

                <div class="f10-lead-capture__honeypot" aria-hidden="true">
                    <label for="<?php echo esc_attr($form_identifier); ?>-website">Website</label>
                    <input
                        id="<?php echo esc_attr($form_identifier); ?>-website"
                        type="text"
                        name="website"
                        value=""
                        tabindex="-1"
                        autocomplete="off"
                    >
                </div>

                <div class="f10-lead-capture__grid">
                    <?php foreach (F10_Lead_Capture_Config::form_fields() as $field_key => $field) : ?>
                        <?php
                        if (!F10_Lead_Capture_Config::is_field_enabled($field_key, $settings)) {
                            continue;
                        }

                        if ($field_key === 'school' && !$show_institution) {
                            continue;
                        }

                        $this->render_field(
                            $form_identifier,
                            $field_key,
                            $field,
                            F10_Lead_Capture_Config::field_label($field_key, $settings),
                            $field_key === 'course' ? (string) $attributes['product'] : ''
                        );
                        ?>
                    <?php endforeach; ?>
                </div>

                <?php if ($require_consent) : ?>
                    <label class="f10-lead-capture__consent">
                        <input type="checkbox" name="consent" value="1" required>
                        <span><?php echo esc_html((string) $settings['consent_text']); ?></span>
                    </label>
                <?php endif; ?>

                <button class="f10-lead-capture__button" type="submit" data-f10-submit>
                    <span data-f10-button-label><?php echo esc_html((string) $attributes['button']); ?></span>
                    <span class="f10-lead-capture__spinner" aria-hidden="true"></span>
                </button>

                <div class="f10-lead-capture__message" data-f10-message role="status" aria-live="polite"></div>
                <div class="f10-lead-capture__conversion" data-f10-conversion aria-live="polite"></div>
            </form>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    public function handle_submission(): void
    {
        if (check_ajax_referer('f10_lead_submit', 'nonce', false) === false) {
            wp_send_json_error(
                array('message' => 'Não foi possível validar o formulário. Atualize a página e tente novamente.'),
                403
            );
        }

        if ($this->posted_text('website', 200) !== '') {
            wp_send_json_success(array('message' => 'Dados recebidos com sucesso.'));
        }

        $loaded_at = absint($this->posted_text('form_loaded_at', 20));
        $elapsed_seconds = time() - $loaded_at;

        if ($loaded_at <= 0 || $elapsed_seconds < 2 || $elapsed_seconds > DAY_IN_SECONDS) {
            wp_send_json_error(
                array('message' => 'Não foi possível validar o tempo do formulário. Atualize a página e tente novamente.'),
                400
            );
        }

        if (!$this->consume_rate_limit()) {
            wp_send_json_error(
                array('message' => 'Muitas tentativas foram registradas. Aguarde alguns minutos e tente novamente.'),
                429
            );
        }

        $settings = $this->get_settings();
        $values = $this->read_configured_fields($settings);
        $validation_error = $this->validate_configured_fields($settings, $values);

        if ($validation_error !== '') {
            wp_send_json_error(array('message' => $validation_error), 422);
        }

        $consent = $this->posted_text('consent', 5) === '1';

        if ($settings['require_consent'] === '1' && !$consent) {
            wp_send_json_error(
                array('message' => 'É necessário autorizar o contato para enviar o formulário.'),
                422
            );
        }

        $conversion_action = $this->resolve_conversion_action();
        $lead_data = array(
            'name' => $values['name'] !== '' ? $values['name'] : 'Lead WordPress',
            'phone' => $values['phone'],
            'whatsapp' => $values['whatsapp'],
            'email' => $values['email'],
            'institution_name' => $values['school'],
            'product' => $values['course'] !== ''
                ? $values['course']
                : $this->posted_text('default_product', 190),
            'notes' => $values['notes'],
            'form_id' => $this->posted_text('form_id', 100) ?: 'wordpress-form',
            'source_label' => $this->posted_text('source_label', 190) ?: 'WordPress',
            'sub_source' => $this->posted_text('sub_source', 190),
            'page_url' => $this->posted_url('page_url'),
            'referrer_url' => $this->posted_url('referrer_url'),
            'utm_source' => $this->posted_text('utm_source', 190),
            'utm_medium' => $this->posted_text('utm_medium', 190),
            'utm_campaign' => $this->posted_text('utm_campaign', 190),
            'utm_term' => $this->posted_text('utm_term', 190),
            'utm_content' => $this->posted_text('utm_content', 190),
            'ip_hash' => $this->get_ip_hash(),
            'user_agent' => $this->get_user_agent(),
            'consent_at' => $consent ? current_time('mysql', true) : null,
            'conversion_type' => $conversion_action['type'],
            'conversion_status' => $conversion_action['type'] === 'none' ? 'none' : 'pending',
            'conversion_url' => $conversion_action['url'],
            'conversion_label' => $conversion_action['label'],
            'conversion_behavior' => $conversion_action['behavior'],
        );

        $lead_id = F10_Lead_Capture_Repository::create($lead_data);

        if ($lead_id <= 0) {
            wp_send_json_error(
                array('message' => 'Não foi possível registrar seus dados. Tente novamente em instantes.'),
                500
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

        wp_send_json_success(
            array(
                'message' => (string) $settings['success_message'],
                'conversionAction' => $this->build_conversion_response($lead_id, $conversion_action),
            )
        );
    }

    public function handle_conversion_tracking(): void
    {
        $lead_id = $this->posted_int('lead_id');
        $token = $this->posted_text('token', 128);

        if ($lead_id <= 0 || $token === '') {
            wp_send_json_error(array('message' => 'Ação inválida.'), 400);
        }

        $expected_token = F10_Lead_Capture_Config::conversion_token($lead_id);

        if (!hash_equals($expected_token, $token)) {
            wp_send_json_error(array('message' => 'Não foi possível validar a ação.'), 403);
        }

        $lead = F10_Lead_Capture_Repository::get($lead_id);

        if (!$lead || !in_array((string) ($lead['conversion_type'] ?? ''), array('download', 'link'), true)) {
            wp_send_json_error(array('message' => 'Ação não encontrada.'), 404);
        }

        if (!F10_Lead_Capture_Repository::track_conversion($lead_id)) {
            wp_send_json_error(array('message' => 'Não foi possível registrar a ação.'), 500);
        }

        wp_send_json_success(array('tracked' => true));
    }

    private function render_field(
        string $form_identifier,
        string $field_key,
        array $field,
        string $label,
        string $default_value
    ): void {
        $field_id = $form_identifier . '-' . $field_key;
        $required = !empty($field['required']);
        $request_key = (string) $field['request_key'];
        $field_classes = array('f10-lead-capture__field');

        if ($field['type'] === 'textarea') {
            $field_classes[] = 'f10-lead-capture__field--wide';
        }
        ?>
        <label class="<?php echo esc_attr(implode(' ', $field_classes)); ?>" for="<?php echo esc_attr($field_id); ?>">
            <span>
                <?php echo esc_html($label); ?>
                <?php if (!$required) : ?><small>(opcional)</small><?php endif; ?>
            </span>
            <?php if ($field['type'] === 'textarea') : ?>
                <textarea
                    id="<?php echo esc_attr($field_id); ?>"
                    name="<?php echo esc_attr($request_key); ?>"
                    rows="4"
                    maxlength="<?php echo esc_attr((string) $field['max_length']); ?>"
                    <?php if ($required) : ?>required<?php endif; ?>
                ><?php echo esc_textarea($default_value); ?></textarea>
            <?php else : ?>
                <input
                    id="<?php echo esc_attr($field_id); ?>"
                    type="<?php echo esc_attr((string) $field['type']); ?>"
                    name="<?php echo esc_attr($request_key); ?>"
                    value="<?php echo esc_attr($default_value); ?>"
                    maxlength="<?php echo esc_attr((string) $field['max_length']); ?>"
                    <?php if ($field['autocomplete'] !== '') : ?>autocomplete="<?php echo esc_attr((string) $field['autocomplete']); ?>"<?php endif; ?>
                    <?php if ($field['type'] === 'tel') : ?>inputmode="tel" placeholder="(00) 00000-0000" data-f10-phone<?php endif; ?>
                    <?php if ($required) : ?>required<?php endif; ?>
                >
            <?php endif; ?>
        </label>
        <?php
    }

    private function read_configured_fields(array $settings): array
    {
        $values = array(
            'name' => '',
            'course' => '',
            'phone' => '',
            'whatsapp' => '',
            'email' => '',
            'school' => '',
            'notes' => '',
        );

        foreach (F10_Lead_Capture_Config::form_fields() as $field_key => $field) {
            if (!F10_Lead_Capture_Config::is_field_enabled($field_key, $settings)) {
                continue;
            }

            $request_key = (string) $field['request_key'];
            $value = $field['type'] === 'textarea'
                ? $this->posted_textarea($request_key, (int) $field['max_length'])
                : $this->posted_text($request_key, (int) $field['max_length']);

            if ($field['type'] === 'tel') {
                $value = preg_replace('/\D+/', '', $value) ?: '';
            }

            if ($field['type'] === 'email') {
                $value = sanitize_email($value);
            }

            $values[$field_key] = $value;
        }

        return $values;
    }

    private function validate_configured_fields(array $settings, array $values): string
    {
        foreach (F10_Lead_Capture_Config::form_fields() as $field_key => $field) {
            if (!F10_Lead_Capture_Config::is_field_enabled($field_key, $settings)) {
                continue;
            }

            $label = F10_Lead_Capture_Config::field_label($field_key, $settings);
            $value = isset($values[$field_key]) ? (string) $values[$field_key] : '';

            if (!empty($field['required']) && $value === '') {
                return 'Preencha o campo ' . $label . '.';
            }

            if ($field['type'] === 'tel' && $value !== '' && (strlen($value) < 10 || strlen($value) > 13)) {
                return 'Informe um número válido no campo ' . $label . ', incluindo o DDD.';
            }

            if ($field['type'] === 'email' && $value !== '' && !is_email($value)) {
                return 'Informe um e-mail válido no campo ' . $label . '.';
            }
        }

        return '';
    }

    private function resolve_conversion_action(): array
    {
        $shortcode_redirect = $this->sanitize_public_url($this->posted_url('redirect_url'));
        $conversion = F10_Lead_Capture_Config::get_conversion();

        if ($shortcode_redirect !== '') {
            return array(
                'type' => 'link',
                'behavior' => 'automatic',
                'url' => $shortcode_redirect,
                'label' => 'Continuar',
                'title' => '',
                'description' => '',
                'open_new_tab' => false,
                'delay_ms' => 700,
            );
        }

        if (($conversion['enabled'] ?? '0') !== '1') {
            return $this->empty_conversion_action();
        }

        $type = in_array((string) ($conversion['type'] ?? ''), array('download', 'link'), true)
            ? (string) $conversion['type']
            : 'none';
        $url = $this->sanitize_public_url(F10_Lead_Capture_Config::conversion_url($conversion));

        if ($type === 'none' || $url === '') {
            return $this->empty_conversion_action();
        }

        return array(
            'type' => $type,
            'behavior' => ($conversion['behavior'] ?? '') === 'automatic' ? 'automatic' : 'button',
            'url' => $url,
            'label' => trim((string) ($conversion['label'] ?? '')) ?: ($type === 'download' ? 'Baixar material' : 'Acessar conteúdo'),
            'title' => trim((string) ($conversion['title'] ?? '')),
            'description' => trim((string) ($conversion['description'] ?? '')),
            'open_new_tab' => ($conversion['open_new_tab'] ?? '0') === '1',
            'delay_ms' => max(0, min(10000, absint($conversion['delay_ms'] ?? 800))),
        );
    }

    private function empty_conversion_action(): array
    {
        return array(
            'type' => 'none',
            'behavior' => 'button',
            'url' => '',
            'label' => '',
            'title' => '',
            'description' => '',
            'open_new_tab' => false,
            'delay_ms' => 0,
        );
    }

    private function build_conversion_response(int $lead_id, array $action): ?array
    {
        if ($action['type'] === 'none' || $action['url'] === '') {
            return null;
        }

        return array(
            'leadId' => $lead_id,
            'token' => F10_Lead_Capture_Config::conversion_token($lead_id),
            'trackEndpoint' => admin_url('admin-ajax.php'),
            'type' => $action['type'],
            'behavior' => $action['behavior'],
            'url' => $action['url'],
            'label' => $action['label'],
            'title' => $action['title'],
            'description' => $action['description'],
            'openNewTab' => (bool) $action['open_new_tab'],
            'delayMs' => (int) $action['delay_ms'],
        );
    }

    private function appearance_classes(array $appearance): array
    {
        $alignment = in_array((string) ($appearance['alignment'] ?? ''), array('left', 'center', 'full'), true)
            ? (string) $appearance['alignment']
            : 'center';
        $shadow = in_array((string) ($appearance['shadow'] ?? ''), array('none', 'subtle', 'strong'), true)
            ? (string) $appearance['shadow']
            : 'subtle';
        $classes = array(
            'f10-lead-capture',
            'f10-lead-capture--align-' . $alignment,
            'f10-lead-capture--shadow-' . $shadow,
        );

        if (($appearance['button_width'] ?? 'auto') === 'full') {
            $classes[] = 'f10-lead-capture--button-full';
        }

        return $classes;
    }

    private function appearance_style(array $appearance): string
    {
        $numeric_variables = array(
            '--f10-form-max-width' => array('form_max_width', 320, 1600, 820),
            '--f10-desktop-columns' => array('desktop_columns', 1, 2, 2),
            '--f10-mobile-columns' => array('mobile_columns', 1, 2, 1),
            '--f10-padding-desktop' => array('padding_desktop', 0, 100, 48),
            '--f10-padding-mobile' => array('padding_mobile', 0, 64, 24),
            '--f10-field-gap' => array('field_gap', 0, 48, 18),
            '--f10-form-border-width' => array('form_border_width', 0, 8, 1),
            '--f10-form-radius' => array('form_radius', 0, 60, 24),
            '--f10-field-radius' => array('field_radius', 0, 40, 12),
            '--f10-button-radius' => array('button_radius', 0, 40, 12),
            '--f10-title-size-desktop' => array('title_size_desktop', 18, 72, 38),
            '--f10-title-size-mobile' => array('title_size_mobile', 18, 56, 30),
            '--f10-description-size' => array('description_size', 12, 24, 16),
        );
        $color_variables = array(
            '--f10-form-background' => array('form_background', '#ffffff'),
            '--f10-form-border-color' => array('form_border_color', '#d9dee8'),
            '--f10-form-text-color' => array('form_text_color', '#101828'),
            '--f10-title-color' => array('title_color', '#000a57'),
            '--f10-description-color' => array('description_color', '#667085'),
            '--f10-field-background' => array('field_background', '#ffffff'),
            '--f10-field-border-color' => array('field_border_color', '#d9dee8'),
            '--f10-field-text-color' => array('field_text_color', '#101828'),
            '--f10-button-background' => array('button_background', '#ea6d0b'),
            '--f10-button-hover-background' => array('button_hover_background', '#d85f00'),
            '--f10-button-text-color' => array('button_text_color', '#ffffff'),
        );
        $parts = array();

        foreach ($numeric_variables as $variable => $config) {
            $value = isset($appearance[$config[0]]) ? absint($appearance[$config[0]]) : $config[3];
            $value = max($config[1], min($config[2], $value));
            $suffix = in_array($variable, array('--f10-desktop-columns', '--f10-mobile-columns'), true) ? '' : 'px';
            $parts[] = $variable . ':' . $value . $suffix;
        }

        foreach ($color_variables as $variable => $config) {
            $value = isset($appearance[$config[0]]) ? sanitize_hex_color((string) $appearance[$config[0]]) : null;
            $parts[] = $variable . ':' . ($value ?: $config[1]);
        }

        return implode(';', $parts);
    }

    private function sanitize_public_url(string $url): string
    {
        $url = esc_url_raw(trim($url));

        if ($url === '') {
            return '';
        }

        $scheme = strtolower((string) wp_parse_url($url, PHP_URL_SCHEME));
        return in_array($scheme, array('http', 'https'), true) ? $url : '';
    }

    private function consume_rate_limit(): bool
    {
        $key = 'f10_lead_rate_' . substr($this->get_ip_hash(), 0, 32);
        $attempts = (int) get_transient($key);

        if ($attempts >= 5) {
            return false;
        }

        set_transient($key, $attempts + 1, 15 * MINUTE_IN_SECONDS);
        return true;
    }

    private function get_ip_hash(): string
    {
        $ip_address = isset($_SERVER['REMOTE_ADDR'])
            ? sanitize_text_field(wp_unslash((string) $_SERVER['REMOTE_ADDR']))
            : 'unknown';

        return hash_hmac('sha256', $ip_address, wp_salt('auth'));
    }

    private function get_user_agent(): string
    {
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return '';
        }

        $user_agent = sanitize_text_field(wp_unslash((string) $_SERVER['HTTP_USER_AGENT']));
        return function_exists('mb_substr')
            ? mb_substr($user_agent, 0, 500)
            : substr($user_agent, 0, 500);
    }

    private function posted_text(string $key, int $max_length): string
    {
        $raw_value = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);

        if (!is_string($raw_value)) {
            return '';
        }

        $value = sanitize_text_field($raw_value);

        return function_exists('mb_substr')
            ? mb_substr($value, 0, $max_length)
            : substr($value, 0, $max_length);
    }

    private function posted_textarea(string $key, int $max_length): string
    {
        $raw_value = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);

        if (!is_string($raw_value)) {
            return '';
        }

        $value = sanitize_textarea_field($raw_value);

        return function_exists('mb_substr')
            ? mb_substr($value, 0, $max_length)
            : substr($value, 0, $max_length);
    }

    private function posted_url(string $key): string
    {
        $raw_value = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);

        if (!is_string($raw_value)) {
            return '';
        }

        return substr(esc_url_raw($raw_value), 0, 2000);
    }

    private function posted_int(string $key): int
    {
        $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT);
        return is_int($value) ? $value : 0;
    }

    private function get_settings(): array
    {
        return F10_Lead_Capture_Config::get_settings();
    }
}
