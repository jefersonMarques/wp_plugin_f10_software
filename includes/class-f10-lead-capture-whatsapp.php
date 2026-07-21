<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10_Lead_Capture_WhatsApp
{
    private ?array $widget = null;

    public function register_hooks(): void
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'), 30);
        add_action('wp_footer', array($this, 'render_widget'), 30);
        add_action('wp_ajax_f10_lead_capture_submit_whatsapp', array($this, 'handle_submission'));
        add_action('wp_ajax_nopriv_f10_lead_capture_submit_whatsapp', array($this, 'handle_submission'));
        add_action('wp_ajax_f10_lead_capture_track_whatsapp', array($this, 'handle_tracking'));
        add_action('wp_ajax_nopriv_f10_lead_capture_track_whatsapp', array($this, 'handle_tracking'));
    }

    public function enqueue_assets(): void
    {
        if (is_admin()) {
            return;
        }

        $this->widget = F10_Lead_Capture_WhatsApp_Config::resolve_current_widget();

        if (!is_array($this->widget)) {
            return;
        }

        wp_enqueue_style(
            'f10-lead-capture-whatsapp',
            F10_LEAD_CAPTURE_URL . 'assets/css/whatsapp.css',
            array(),
            F10_LEAD_CAPTURE_VERSION
        );
        wp_enqueue_script(
            'f10-lead-capture-whatsapp',
            F10_LEAD_CAPTURE_URL . 'assets/js/whatsapp.js',
            array(),
            F10_LEAD_CAPTURE_VERSION,
            true
        );

        $settings = F10_Lead_Capture_Config::get_settings();
        $schedule_state = F10_Lead_Capture_WhatsApp_Config::schedule_state($this->widget);
        $client_widget = $this->widget;
        unset($client_widget['created_at'], $client_widget['updated_at']);

        wp_localize_script(
            'f10-lead-capture-whatsapp',
            'F10LeadWhatsApp',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('f10_lead_capture_whatsapp_submit'),
                'widget' => $client_widget,
                'siteName' => get_bloginfo('name'),
                'timezone' => wp_timezone()->getName(),
                'serverOnline' => (bool) $schedule_state['online'],
                'consentRequired' => $settings['require_consent'] === '1',
                'storageDays' => 7,
            )
        );
    }

    public function render_widget(): void
    {
        if (!is_array($this->widget)) {
            return;
        }

        $settings = F10_Lead_Capture_Config::get_settings();
        $widget = $this->widget;
        $position = $widget['position'] === 'left' ? 'left' : 'right';
        $design = in_array($widget['design'], array('static', 'pulse', 'radar', 'attention'), true)
            ? $widget['design']
            : 'pulse';
        ?>
        <div
            class="f10-whatsapp-widget f10-whatsapp-widget--<?php echo esc_attr($position); ?> f10-whatsapp-widget--<?php echo esc_attr($design); ?>"
            style="--f10-whatsapp-color:<?php echo esc_attr($widget['color']); ?>"
            data-f10-whatsapp-widget
            data-widget-id="<?php echo esc_attr($widget['id']); ?>"
            hidden
        >
            <button
                type="button"
                class="f10-whatsapp-widget__trigger"
                aria-label="Abrir atendimento pelo WhatsApp"
                aria-haspopup="dialog"
                data-f10-whatsapp-trigger
            >
                <span class="f10-whatsapp-widget__radar" aria-hidden="true"></span>
                <span class="f10-whatsapp-widget__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" role="img" focusable="false">
                        <path d="M20.5 11.7a8.5 8.5 0 0 1-12.6 7.5L3 20.5l1.3-4.7a8.5 8.5 0 1 1 16.2-4.1Zm-8.4-6.5a6.5 6.5 0 0 0-5.5 10l.3.5-.7 2.3 2.4-.6.5.3a6.5 6.5 0 1 0 3-12.5Zm-3.3 3c.2 0 .4 0 .5.3l.8 1.8c.1.3.1.5-.1.7l-.6.7c-.2.2-.1.4 0 .6.7 1.2 1.7 2.1 3 2.7.2.1.4.1.6-.1l.8-1c.2-.2.4-.3.7-.2l1.8.9c.3.1.4.3.4.5 0 .5-.2 1.5-1 2-.5.4-1.2.6-2 .4-1.1-.3-2.6-.9-4.1-2.2-1.2-1-2-2.3-2.4-3.3-.5-1.3 0-2.5.5-3 .3-.4.7-.6 1.1-.6Z"/>
                    </svg>
                </span>
            </button>

            <button type="button" class="f10-whatsapp-widget__badge" data-f10-whatsapp-badge>
                <?php echo esc_html($widget['badge_online']); ?>
            </button>

            <div class="f10-whatsapp-widget__overlay" data-f10-whatsapp-overlay hidden>
                <section
                    class="f10-whatsapp-widget__dialog"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="f10-whatsapp-title-<?php echo esc_attr($widget['id']); ?>"
                    data-f10-whatsapp-dialog
                >
                    <button
                        type="button"
                        class="f10-whatsapp-widget__close"
                        aria-label="Fechar"
                        data-f10-whatsapp-close
                    >&times;</button>

                    <div class="f10-whatsapp-widget__dialog-header">
                        <span class="f10-whatsapp-widget__dialog-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" focusable="false">
                                <path d="M20.5 11.7a8.5 8.5 0 0 1-12.6 7.5L3 20.5l1.3-4.7a8.5 8.5 0 1 1 16.2-4.1Zm-8.4-6.5a6.5 6.5 0 0 0-5.5 10l.3.5-.7 2.3 2.4-.6.5.3a6.5 6.5 0 1 0 3-12.5Z"/>
                            </svg>
                        </span>
                        <div>
                            <h2 id="f10-whatsapp-title-<?php echo esc_attr($widget['id']); ?>">
                                <?php echo esc_html($widget['form_title']); ?>
                            </h2>
                            <p data-f10-whatsapp-description><?php echo esc_html($widget['form_description']); ?></p>
                        </div>
                    </div>

                    <form
                        class="f10-whatsapp-widget__form"
                        method="post"
                        action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
                        data-f10-whatsapp-form
                        novalidate
                    >
                        <input type="hidden" name="action" value="f10_lead_capture_submit_whatsapp">
                        <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('f10_lead_capture_whatsapp_submit')); ?>">
                        <input type="hidden" name="widget_id" value="<?php echo esc_attr($widget['id']); ?>">
                        <input type="hidden" name="form_loaded_at" value="<?php echo esc_attr((string) time()); ?>">
                        <input type="hidden" name="page_url" value="" data-f10-whatsapp-page-url>
                        <input type="hidden" name="referrer_url" value="" data-f10-whatsapp-referrer-url>
                        <input type="hidden" name="page_title" value="" data-f10-whatsapp-page-title>
                        <input type="hidden" name="utm_source" value="" data-f10-whatsapp-utm="utm_source">
                        <input type="hidden" name="utm_medium" value="" data-f10-whatsapp-utm="utm_medium">
                        <input type="hidden" name="utm_campaign" value="" data-f10-whatsapp-utm="utm_campaign">
                        <input type="hidden" name="utm_term" value="" data-f10-whatsapp-utm="utm_term">
                        <input type="hidden" name="utm_content" value="" data-f10-whatsapp-utm="utm_content">

                        <div class="f10-whatsapp-widget__honeypot" aria-hidden="true">
                            <label>
                                Website
                                <input type="text" name="website" value="" tabindex="-1" autocomplete="off">
                            </label>
                        </div>

                        <label class="f10-whatsapp-widget__field">
                            <span>Nome</span>
                            <input type="text" name="name" maxlength="190" autocomplete="name" required>
                        </label>

                        <label class="f10-whatsapp-widget__field">
                            <span>WhatsApp</span>
                            <input
                                type="tel"
                                name="whatsapp"
                                maxlength="20"
                                autocomplete="tel"
                                inputmode="tel"
                                placeholder="(00) 00000-0000"
                                data-f10-whatsapp-phone
                                required
                            >
                        </label>

                        <?php if ($settings['require_consent'] === '1') : ?>
                            <label class="f10-whatsapp-widget__consent">
                                <input type="checkbox" name="consent" value="1" required>
                                <span><?php echo esc_html((string) $settings['consent_text']); ?></span>
                            </label>
                        <?php endif; ?>

                        <button type="submit" class="f10-whatsapp-widget__submit" data-f10-whatsapp-submit>
                            <span data-f10-whatsapp-submit-label><?php echo esc_html($widget['button_label']); ?></span>
                            <span class="f10-whatsapp-widget__spinner" aria-hidden="true"></span>
                        </button>

                        <div
                            class="f10-whatsapp-widget__message"
                            role="status"
                            aria-live="polite"
                            data-f10-whatsapp-message
                        ></div>
                    </form>
                </section>
            </div>
        </div>
        <?php
    }

    public function handle_submission(): void
    {
        if (check_ajax_referer('f10_lead_capture_whatsapp_submit', 'nonce', false) === false) {
            wp_send_json_error(array('message' => 'Não foi possível validar o formulário. Atualize a página.'), 403);
        }

        if ($this->posted_text('website', 200) !== '') {
            wp_send_json_success(array('message' => 'Dados recebidos com sucesso.'));
        }

        $loaded_at = absint($this->posted_text('form_loaded_at', 20));
        $elapsed_seconds = time() - $loaded_at;

        if ($loaded_at <= 0 || $elapsed_seconds < 1 || $elapsed_seconds > DAY_IN_SECONDS) {
            wp_send_json_error(array('message' => 'Não foi possível validar o tempo do formulário.'), 400);
        }

        if (!$this->consume_rate_limit()) {
            wp_send_json_error(
                array('message' => 'Muitas tentativas foram registradas. Aguarde alguns minutos.'),
                429
            );
        }

        $widget_id = F10_Lead_Capture_WhatsApp_Config::sanitize_widget_id(
            $this->posted_text('widget_id', 100)
        );
        $widget = F10_Lead_Capture_WhatsApp_Config::get_widget($widget_id);

        if (!is_array($widget) || $widget['active'] !== '1' || $widget['phone'] === '') {
            wp_send_json_error(array('message' => 'Este atendimento não está disponível.'), 404);
        }

        $schedule_state = F10_Lead_Capture_WhatsApp_Config::schedule_state($widget);

        if (!$schedule_state['online'] && $schedule_state['behavior'] === 'hide') {
            wp_send_json_error(array('message' => 'O atendimento está fora do horário disponível.'), 403);
        }

        $name = $this->posted_text('name', 190);
        $visitor_whatsapp = F10_Lead_Capture_WhatsApp_Config::normalize_phone(
            $this->posted_text('whatsapp', 30)
        );

        if ($name === '') {
            wp_send_json_error(array('message' => 'Informe seu nome.'), 422);
        }

        if ($visitor_whatsapp === '') {
            wp_send_json_error(array('message' => 'Informe um WhatsApp válido, incluindo o DDD.'), 422);
        }

        $settings = F10_Lead_Capture_Config::get_settings();
        $consent = $this->posted_text('consent', 5) === '1';

        if ($settings['require_consent'] === '1' && !$consent) {
            wp_send_json_error(array('message' => 'É necessário autorizar o contato.'), 422);
        }

        $page_url = $this->posted_url('page_url');
        $message = F10_Lead_Capture_WhatsApp_Config::build_message(
            $widget,
            array(
                'name' => $name,
                'visitor_whatsapp' => $visitor_whatsapp,
                'site_name' => get_bloginfo('name'),
                'page_title' => $this->posted_text('page_title', 250),
                'page_url' => $page_url,
                'utm_source' => $this->posted_text('utm_source', 190),
                'utm_campaign' => $this->posted_text('utm_campaign', 190),
            )
        );
        $should_open = $schedule_state['online'] || $schedule_state['behavior'] === 'open';
        $whatsapp_url = $should_open
            ? F10_Lead_Capture_WhatsApp_Config::build_url($widget, $message)
            : '';
        $conversion_type = $whatsapp_url !== '' ? 'whatsapp' : 'none';
        $lead_data = array(
            'name' => $name,
            'phone' => $visitor_whatsapp,
            'whatsapp' => $visitor_whatsapp,
            'email' => '',
            'institution_name' => '',
            'product' => $this->posted_text('page_title', 190) ?: 'Atendimento WhatsApp',
            'notes' => 'Lead capturado pelo botão flutuante do WhatsApp.',
            'form_id' => 'whatsapp-' . $widget['id'],
            'source_label' => 'WhatsApp flutuante',
            'sub_source' => $widget['name'],
            'page_url' => $page_url,
            'referrer_url' => $this->posted_url('referrer_url'),
            'utm_source' => $this->posted_text('utm_source', 190),
            'utm_medium' => $this->posted_text('utm_medium', 190),
            'utm_campaign' => $this->posted_text('utm_campaign', 190),
            'utm_term' => $this->posted_text('utm_term', 190),
            'utm_content' => $this->posted_text('utm_content', 190),
            'ip_hash' => $this->get_ip_hash(),
            'user_agent' => $this->get_user_agent(),
            'consent_at' => $consent ? current_time('mysql', true) : null,
            'conversion_type' => $conversion_type,
            'conversion_status' => $conversion_type === 'none' ? 'none' : 'pending',
            'conversion_url' => $whatsapp_url,
            'conversion_label' => 'Abrir WhatsApp',
            'conversion_behavior' => $conversion_type === 'none' ? '' : 'automatic',
        );
        $result = F10_Lead_Capture_Submission_Service::submit($lead_data);

        if (!$result['ok']) {
            wp_send_json_error(array('message' => $result['message']), 500);
        }

        wp_send_json_success(
            array(
                'message' => $should_open
                    ? 'Dados registrados. Abrindo o WhatsApp...'
                    : 'Dados registrados. A escola entrará em contato no próximo horário de atendimento.',
                'shouldOpen' => $should_open,
                'whatsappUrl' => $whatsapp_url,
                'leadId' => (int) $result['lead_id'],
                'token' => F10_Lead_Capture_Config::conversion_token((int) $result['lead_id']),
                'trackEndpoint' => admin_url('admin-ajax.php'),
                'reuse' => array(
                    'name' => $name,
                    'whatsapp' => $visitor_whatsapp,
                    'expiresAt' => time() + (7 * DAY_IN_SECONDS),
                ),
            )
        );
    }

    public function handle_tracking(): void
    {
        $lead_id = absint($this->posted_text('lead_id', 20));
        $token = $this->posted_text('token', 128);

        if ($lead_id <= 0 || $token === '') {
            wp_send_json_error(array('message' => 'Ação inválida.'), 400);
        }

        if (!hash_equals(F10_Lead_Capture_Config::conversion_token($lead_id), $token)) {
            wp_send_json_error(array('message' => 'Não foi possível validar a ação.'), 403);
        }

        $lead = F10_Lead_Capture_Repository::get($lead_id);

        if (!$lead || (string) ($lead['conversion_type'] ?? '') !== 'whatsapp') {
            wp_send_json_error(array('message' => 'Ação não encontrada.'), 404);
        }

        if (!F10_Lead_Capture_Repository::track_conversion($lead_id)) {
            wp_send_json_error(array('message' => 'Não foi possível registrar a ação.'), 500);
        }

        wp_send_json_success(array('tracked' => true));
    }

    private function posted_text(string $key, int $max_length): string
    {
        if (!isset($_POST[$key]) || !is_scalar($_POST[$key])) {
            return '';
        }

        $value = sanitize_text_field(wp_unslash((string) $_POST[$key]));

        return function_exists('mb_substr')
            ? mb_substr($value, 0, $max_length)
            : substr($value, 0, $max_length);
    }

    private function posted_url(string $key): string
    {
        return esc_url_raw($this->posted_text($key, 2000));
    }

    private function consume_rate_limit(): bool
    {
        $key = 'f10_lead_whatsapp_rate_' . substr($this->get_ip_hash(), 0, 32);
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
            ? mb_substr($user_agent, 0, 1000)
            : substr($user_agent, 0, 1000);
    }
}
