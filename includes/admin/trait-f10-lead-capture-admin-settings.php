<?php

if (!defined('ABSPATH')) {
    exit;
}

trait F10_Lead_Capture_Admin_Settings_Trait
{
    public function sanitize_settings(array $input): array
    {
        $current = $this->get_settings();
        $token_input = isset($input['f10_token']) ? trim((string) $input['f10_token']) : '';
        $api_key_input = isset($input['brevo_api_key']) ? trim((string) $input['brevo_api_key']) : '';

        $f10_token = !empty($input['clear_f10_token'])
            ? ''
            : ($token_input !== '' ? sanitize_text_field($token_input) : (string) $current['f10_token']);

        $brevo_api_key = !empty($input['clear_brevo_api_key'])
            ? ''
            : ($api_key_input !== '' ? sanitize_text_field($api_key_input) : (string) $current['brevo_api_key']);

        return array(
            'f10_enabled' => !empty($input['f10_enabled']) ? '1' : '0',
            'f10_url' => isset($input['f10_url']) ? esc_url_raw((string) $input['f10_url']) : '',
            'f10_token' => $f10_token,
            'f10_unit_id' => isset($input['f10_unit_id']) ? (string) absint($input['f10_unit_id']) : '',
            'f10_source' => isset($input['f10_source']) ? sanitize_text_field((string) $input['f10_source']) : 'Site',
            'f10_media' => isset($input['f10_media']) ? sanitize_text_field((string) $input['f10_media']) : 'WordPress',
            'brevo_enabled' => !empty($input['brevo_enabled']) ? '1' : '0',
            'brevo_api_key' => $brevo_api_key,
            'brevo_recipient_email' => isset($input['brevo_recipient_email'])
                ? sanitize_email((string) $input['brevo_recipient_email'])
                : '',
            'brevo_sender_email' => isset($input['brevo_sender_email'])
                ? sanitize_email((string) $input['brevo_sender_email'])
                : '',
            'brevo_sender_name' => isset($input['brevo_sender_name'])
                ? sanitize_text_field((string) $input['brevo_sender_name'])
                : 'Leads F10',
            'require_consent' => !empty($input['require_consent']) ? '1' : '0',
            'consent_text' => isset($input['consent_text'])
                ? sanitize_textarea_field((string) $input['consent_text'])
                : '',
            'success_message' => isset($input['success_message'])
                ? sanitize_text_field((string) $input['success_message'])
                : '',
            'max_retry_attempts' => isset($input['max_retry_attempts'])
                ? (string) max(1, min(10, absint($input['max_retry_attempts'])))
                : '5',
            'delete_data_on_uninstall' => !empty($input['delete_data_on_uninstall']) ? '1' : '0',
        );
    }

    public function render_settings_page(): void
    {
        $this->require_capability();
        $settings = $this->get_settings();
        $jwt_status = F10_Lead_Capture_Integrations::inspect_jwt((string) $settings['f10_token']);
        ?>
        <div class="wrap">
            <h1>Configurações do F10 Lead Capture</h1>
            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php settings_fields('f10_lead_capture_settings_group'); ?>

                <h2>Integração com a F10</h2>
                <p>Os campos abaixo correspondem ao contrato utilizado pelo site da F10 para criar leads.</p>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Ativar envio para F10</th>
                        <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_NAME); ?>[f10_enabled]" value="1" <?php checked($settings['f10_enabled'], '1'); ?>> Enviar cada novo lead para a API da F10</label></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="f10_url">URL da API F10</label></th>
                        <td>
                            <input id="f10_url" class="regular-text code" type="url" name="<?php echo esc_attr(self::OPTION_NAME); ?>[f10_url]" value="<?php echo esc_attr((string) $settings['f10_url']); ?>" placeholder="https://...">
                            <p class="description">Endpoint completo que recebe o payload de leads.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="f10_token">Token JWT F10</label></th>
                        <td>
                            <input id="f10_token" class="regular-text code" type="password" name="<?php echo esc_attr(self::OPTION_NAME); ?>[f10_token]" value="" autocomplete="new-password" placeholder="<?php echo $settings['f10_token'] ? 'Token configurado — deixe em branco para manter' : 'Informe o token JWT'; ?>">
                            <label style="display:block;margin-top:8px"><input type="checkbox" name="<?php echo esc_attr(self::OPTION_NAME); ?>[clear_f10_token]" value="1"> Remover o token salvo</label>
                            <p class="description"><?php echo esc_html($this->jwt_status_message($jwt_status)); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="f10_unit_id">ID da unidade</label></th>
                        <td><input id="f10_unit_id" type="number" min="1" name="<?php echo esc_attr(self::OPTION_NAME); ?>[f10_unit_id]" value="<?php echo esc_attr((string) $settings['f10_unit_id']); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="f10_source">Fonte</label></th>
                        <td><input id="f10_source" class="regular-text" type="text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[f10_source]" value="<?php echo esc_attr((string) $settings['f10_source']); ?>" placeholder="Site"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="f10_media">Mídia</label></th>
                        <td><input id="f10_media" class="regular-text" type="text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[f10_media]" value="<?php echo esc_attr((string) $settings['f10_media']); ?>" placeholder="WordPress"></td>
                    </tr>
                </table>

                <hr>
                <h2>Notificação por e-mail via Brevo</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Enviar e-mail quando gerar um lead?</th>
                        <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_NAME); ?>[brevo_enabled]" value="1" <?php checked($settings['brevo_enabled'], '1'); ?>> Sim, enviar uma notificação pelo Brevo</label></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="brevo_api_key">Chave da API Brevo</label></th>
                        <td>
                            <input id="brevo_api_key" class="regular-text code" type="password" name="<?php echo esc_attr(self::OPTION_NAME); ?>[brevo_api_key]" value="" autocomplete="new-password" placeholder="<?php echo $settings['brevo_api_key'] ? 'Chave configurada — deixe em branco para manter' : 'xkeysib-...'; ?>">
                            <label style="display:block;margin-top:8px"><input type="checkbox" name="<?php echo esc_attr(self::OPTION_NAME); ?>[clear_brevo_api_key]" value="1"> Remover a chave salva</label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="brevo_recipient_email">E-mail que receberá os leads</label></th>
                        <td><input id="brevo_recipient_email" class="regular-text" type="email" name="<?php echo esc_attr(self::OPTION_NAME); ?>[brevo_recipient_email]" value="<?php echo esc_attr((string) $settings['brevo_recipient_email']); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="brevo_sender_email">E-mail remetente</label></th>
                        <td>
                            <input id="brevo_sender_email" class="regular-text" type="email" name="<?php echo esc_attr(self::OPTION_NAME); ?>[brevo_sender_email]" value="<?php echo esc_attr((string) $settings['brevo_sender_email']); ?>">
                            <p class="description">Este endereço precisa estar verificado como remetente na conta Brevo.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="brevo_sender_name">Nome do remetente</label></th>
                        <td><input id="brevo_sender_name" class="regular-text" type="text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[brevo_sender_name]" value="<?php echo esc_attr((string) $settings['brevo_sender_name']); ?>"></td>
                    </tr>
                </table>

                <hr>
                <h2>Formulário, segurança e retenção</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">Consentimento</th>
                        <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_NAME); ?>[require_consent]" value="1" <?php checked($settings['require_consent'], '1'); ?>> Exigir aceite antes do envio</label></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="consent_text">Texto do consentimento</label></th>
                        <td><textarea id="consent_text" class="large-text" rows="3" name="<?php echo esc_attr(self::OPTION_NAME); ?>[consent_text]"><?php echo esc_textarea((string) $settings['consent_text']); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="success_message">Mensagem de sucesso</label></th>
                        <td><input id="success_message" class="large-text" type="text" name="<?php echo esc_attr(self::OPTION_NAME); ?>[success_message]" value="<?php echo esc_attr((string) $settings['success_message']); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="max_retry_attempts">Tentativas automáticas</label></th>
                        <td>
                            <input id="max_retry_attempts" type="number" min="1" max="10" name="<?php echo esc_attr(self::OPTION_NAME); ?>[max_retry_attempts]" value="<?php echo esc_attr((string) $settings['max_retry_attempts']); ?>">
                            <p class="description">Leads com falha permanecem no banco e são reenviados pelo WP-Cron.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Desinstalação</th>
                        <td><label><input type="checkbox" name="<?php echo esc_attr(self::OPTION_NAME); ?>[delete_data_on_uninstall]" value="1" <?php checked($settings['delete_data_on_uninstall'], '1'); ?>> Excluir permanentemente configurações e leads ao remover o plugin</label></td>
                    </tr>
                </table>

                <?php submit_button('Salvar configurações'); ?>
            </form>

            <hr>
            <h2>Como inserir o formulário</h2>
            <p>Use o shortcode abaixo em qualquer página ou post:</p>
            <p><code>[f10_lead_form]</code></p>
            <p>Exemplo personalizado:</p>
            <p><code>[f10_lead_form title="Receba uma demonstração" button="Quero uma demonstração" product="Sistema de gestão escolar" source="Blog F10" sub_source="Post sobre cursos livres"]</code></p>
            <p>Para ocultar o campo de escola/empresa, use <code>show_institution="no"</code>.</p>
        </div>
        <?php
    }

    private function jwt_status_message(array $jwt_status): string
    {
        if (!$jwt_status['valid']) {
            return (string) $jwt_status['message'];
        }

        if (!empty($jwt_status['expires_at'])) {
            $date = wp_date(
                'd/m/Y H:i:s',
                (int) $jwt_status['expires_at'],
                wp_timezone()
            );

            return $jwt_status['expired']
                ? 'Token vencido em ' . $date . '. Informe um novo token.'
                : 'Token válido até ' . $date . '.';
        }

        return 'Token JWT decodificado, mas sem data de expiração.';
    }

    private function get_settings(): array
    {
        return wp_parse_args(
            (array) get_option(self::OPTION_NAME, array()),
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
            )
        );
    }
}
