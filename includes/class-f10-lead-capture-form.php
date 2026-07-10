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
        $require_consent = $settings['require_consent'] === '1';
        $show_institution = strtolower((string) $attributes['show_institution']) !== 'no';

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
        <div class="f10-lead-capture" data-f10-lead-container>
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
                <input type="hidden" name="product" value="<?php echo esc_attr((string) $attributes['product']); ?>">
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
                    <label class="f10-lead-capture__field">
                        <span>Nome</span>
                        <input type="text" name="name" autocomplete="name" maxlength="190" required>
                    </label>

                    <label class="f10-lead-capture__field">
                        <span>WhatsApp</span>
                        <input
                            type="tel"
                            name="whatsapp"
                            inputmode="tel"
                            autocomplete="tel"
                            maxlength="20"
                            placeholder="(00) 00000-0000"
                            data-f10-phone
                            required
                        >
                    </label>

                    <label class="f10-lead-capture__field">
                        <span>E-mail</span>
                        <input type="email" name="email" autocomplete="email" maxlength="190" required>
                    </label>

                    <?php if ($show_institution) : ?>
                        <label class="f10-lead-capture__field">
                            <span>Nome da escola ou empresa <small>(opcional)</small></span>
                            <input type="text" name="institution_name" autocomplete="organization" maxlength="190">
                        </label>
                    <?php endif; ?>
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
            </form>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    public function handle_submission(): void
    {
        $nonce = $this->posted_text('nonce', 200);

        if (!wp_verify_nonce($nonce, 'f10_lead_submit')) {
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
        $name = $this->posted_text('name', 190);
        $whatsapp = preg_replace('/\D+/', '', $this->posted_text('whatsapp', 30)) ?: '';
        $email = sanitize_email($this->posted_text('email', 190));
        $institution_name = $this->posted_text('institution_name', 190);
        $consent = $this->posted_text('consent', 5) === '1';

        if ($name === '' || strlen($whatsapp) < 10 || strlen($whatsapp) > 13 || !is_email($email)) {
            wp_send_json_error(
                array('message' => 'Informe nome, WhatsApp com DDD e um e-mail válido.'),
                422
            );
        }

        if ($settings['require_consent'] === '1' && !$consent) {
            wp_send_json_error(
                array('message' => 'É necessário autorizar o contato para enviar o formulário.'),
                422
            );
        }

        $lead_data = array(
            'name' => $name,
            'whatsapp' => $whatsapp,
            'email' => $email,
            'institution_name' => $institution_name,
            'product' => $this->posted_text('product', 190),
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

        $redirect_url = wp_validate_redirect($this->posted_url('redirect_url'), '');

        wp_send_json_success(
            array(
                'message' => (string) $settings['success_message'],
                'redirectUrl' => $redirect_url,
            )
        );
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
        if (!isset($_POST[$key]) || is_array($_POST[$key])) {
            return '';
        }

        $value = sanitize_text_field(wp_unslash((string) $_POST[$key]));

        return function_exists('mb_substr')
            ? mb_substr($value, 0, $max_length)
            : substr($value, 0, $max_length);
    }

    private function posted_url(string $key): string
    {
        if (!isset($_POST[$key]) || is_array($_POST[$key])) {
            return '';
        }

        $value = esc_url_raw(wp_unslash((string) $_POST[$key]));
        return substr($value, 0, 2000);
    }

    private function get_settings(): array
    {
        return wp_parse_args(
            (array) get_option('f10_lead_capture_settings', array()),
            array(
                'require_consent' => '1',
                'consent_text' => 'Autorizo o contato da equipe comercial sobre as soluções apresentadas.',
                'success_message' => 'Dados recebidos com sucesso. Nossa equipe entrará em contato.',
            )
        );
    }
}
