<?php

if (!defined('ABSPATH')) {
    exit;
}

trait F10_Lead_Capture_Admin_Forms_Trait
{
    public function render_forms_page(): void
    {
        $this->require_capability();
        $action = sanitize_key($this->query_text('view', 30));
        $form_id = F10_Lead_Capture_Config::sanitize_form_id($this->query_text('form', 100));

        if ($action === 'edit' || $action === 'new') {
            $this->render_form_editor($action === 'new' ? '' : $form_id);
            return;
        }

        $this->render_forms_list();
    }

    public function handle_save_form(): void
    {
        $this->require_capability();
        check_admin_referer('f10_save_form');

        $raw = filter_input(INPUT_POST, 'f10_form', FILTER_UNSAFE_RAW, FILTER_REQUIRE_ARRAY);
        $input = is_array($raw) ? $raw : array();
        $original_id = F10_Lead_Capture_Config::sanitize_form_id((string) ($input['original_id'] ?? ''));
        $requested_id = F10_Lead_Capture_Config::sanitize_form_id((string) ($input['id'] ?? ''));
        $name = sanitize_text_field((string) ($input['name'] ?? ''));
        $forms = F10_Lead_Capture_Config::get_forms();

        if ($requested_id === '') {
            $requested_id = F10_Lead_Capture_Config::sanitize_form_id($name);
        }

        if ($requested_id === '') {
            $requested_id = 'formulario-' . wp_generate_password(6, false, false);
        }

        if ($original_id === '') {
            $requested_id = $this->unique_form_id($requested_id, $forms);
        } elseif ($requested_id !== $original_id && isset($forms[$requested_id])) {
            $requested_id = $this->unique_form_id($requested_id, $forms);
        }

        $existing = $original_id !== '' && isset($forms[$original_id])
            ? $forms[$original_id]
            : F10_Lead_Capture_Config::default_form();
        $created_at = (string) ($existing['created_at'] ?? current_time('mysql', true));
        $fields = array();
        $input_fields = is_array($input['fields'] ?? null) ? $input['fields'] : array();
        $enabled_count = 0;

        foreach (F10_Lead_Capture_Config::form_fields() as $field_key => $definition) {
            $configured = is_array($input_fields[$field_key] ?? null) ? $input_fields[$field_key] : array();
            $enabled = !empty($configured['enabled']) ? '1' : '0';
            $required = $enabled === '1' && !empty($configured['required']) ? '1' : '0';
            $label = sanitize_text_field((string) ($configured['label'] ?? ''));

            if ($enabled === '1') {
                $enabled_count++;
            }

            $fields[$field_key] = array(
                'enabled' => $enabled,
                'required' => $required,
                'label' => $label !== '' ? $label : (string) $definition['label'],
            );
        }

        if ($enabled_count === 0) {
            $fields['name']['enabled'] = '1';
            $fields['name']['required'] = '1';
        }

        $conversion_input = is_array($input['conversion'] ?? null) ? $input['conversion'] : array();
        $conversion = $this->sanitize_form_conversion($conversion_input);
        $form = array(
            'id' => $requested_id,
            'name' => $name !== '' ? $name : 'Formulário sem nome',
            'active' => !empty($input['active']) ? '1' : '0',
            'title' => sanitize_text_field((string) ($input['title'] ?? '')),
            'description' => sanitize_textarea_field((string) ($input['description'] ?? '')),
            'button' => sanitize_text_field((string) ($input['button'] ?? '')),
            'success_message' => sanitize_text_field((string) ($input['success_message'] ?? '')),
            'product' => sanitize_text_field((string) ($input['product'] ?? '')),
            'source' => sanitize_text_field((string) ($input['source'] ?? '')),
            'sub_source' => sanitize_text_field((string) ($input['sub_source'] ?? '')),
            'fields' => $fields,
            'conversion' => $conversion,
            'created_at' => $created_at,
            'updated_at' => current_time('mysql', true),
        );
        $form = F10_Lead_Capture_Config::normalize_form($form, $requested_id);

        if ($original_id !== '' && $original_id !== $requested_id) {
            unset($forms[$original_id]);
        }

        $forms[$requested_id] = $form;
        F10_Lead_Capture_Config::save_forms($forms);

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page' => 'f10-lead-forms',
                    'view' => 'edit',
                    'form' => $requested_id,
                    'f10_notice' => 'form_saved',
                ),
                admin_url('admin.php')
            )
        );
        exit;
    }

    public function handle_duplicate_form(): void
    {
        $this->require_capability();
        $form_id = F10_Lead_Capture_Config::sanitize_form_id($this->query_text('form', 100));
        check_admin_referer('f10_duplicate_form_' . $form_id);
        $forms = F10_Lead_Capture_Config::get_forms();

        if (!isset($forms[$form_id])) {
            wp_safe_redirect(add_query_arg(array('page' => 'f10-lead-forms', 'f10_notice' => 'form_missing'), admin_url('admin.php')));
            exit;
        }

        $copy = $forms[$form_id];
        $new_id = $this->unique_form_id($form_id . '-copia', $forms);
        $copy['id'] = $new_id;
        $copy['name'] = (string) $copy['name'] . ' — cópia';
        $copy['created_at'] = current_time('mysql', true);
        $copy['updated_at'] = current_time('mysql', true);
        $forms[$new_id] = $copy;
        F10_Lead_Capture_Config::save_forms($forms);

        wp_safe_redirect(add_query_arg(array('page' => 'f10-lead-forms', 'view' => 'edit', 'form' => $new_id, 'f10_notice' => 'form_duplicated'), admin_url('admin.php')));
        exit;
    }

    public function handle_delete_form(): void
    {
        $this->require_capability();
        $form_id = F10_Lead_Capture_Config::sanitize_form_id($this->query_text('form', 100));
        check_admin_referer('f10_delete_form_' . $form_id);
        $forms = F10_Lead_Capture_Config::get_forms();

        if ($form_id === F10_Lead_Capture_Config::DEFAULT_FORM_ID) {
            wp_safe_redirect(add_query_arg(array('page' => 'f10-lead-forms', 'f10_notice' => 'default_protected'), admin_url('admin.php')));
            exit;
        }

        unset($forms[$form_id]);
        F10_Lead_Capture_Config::save_forms($forms);
        wp_safe_redirect(add_query_arg(array('page' => 'f10-lead-forms', 'f10_notice' => 'form_deleted'), admin_url('admin.php')));
        exit;
    }

    private function render_forms_list(): void
    {
        $forms = F10_Lead_Capture_Config::get_forms();
        uasort(
            $forms,
            static function (array $left, array $right): int {
                return strcasecmp((string) $left['name'], (string) $right['name']);
            }
        );
        ?>
        <div class="wrap f10-admin-page">
            <h1 class="wp-heading-inline">Formulários</h1>
            <a class="page-title-action" href="<?php echo esc_url(admin_url('admin.php?page=f10-lead-forms&view=new')); ?>">Adicionar novo</a>
            <p>Crie formulários diferentes para demonstrações, e-books, landing pages e campanhas. Cada formulário possui conteúdo, campos e ação pós-conversão próprios.</p>
            <?php $this->render_forms_notice(); ?>

            <table class="widefat fixed striped f10-forms-table">
                <thead>
                    <tr>
                        <th>Formulário</th>
                        <th style="width:235px">Shortcode</th>
                        <th style="width:120px">Campos</th>
                        <th style="width:150px">Pós-conversão</th>
                        <th style="width:100px">Status</th>
                        <th style="width:145px">Atualizado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms as $form) : ?>
                        <?php
                        $edit_url = add_query_arg(array('page' => 'f10-lead-forms', 'view' => 'edit', 'form' => $form['id']), admin_url('admin.php'));
                        $duplicate_url = wp_nonce_url(
                            add_query_arg(array('action' => 'f10_duplicate_form', 'form' => $form['id']), admin_url('admin-post.php')),
                            'f10_duplicate_form_' . $form['id']
                        );
                        $delete_url = wp_nonce_url(
                            add_query_arg(array('action' => 'f10_delete_form', 'form' => $form['id']), admin_url('admin-post.php')),
                            'f10_delete_form_' . $form['id']
                        );
                        $enabled_fields = array_filter(
                            (array) $form['fields'],
                            static function (array $field): bool {
                                return ($field['enabled'] ?? '0') === '1';
                            }
                        );
                        ?>
                        <tr>
                            <td>
                                <strong><a href="<?php echo esc_url($edit_url); ?>"><?php echo esc_html((string) $form['name']); ?></a></strong>
                                <div class="row-actions">
                                    <span><a href="<?php echo esc_url($edit_url); ?>">Editar</a> | </span>
                                    <span><a href="<?php echo esc_url($duplicate_url); ?>">Duplicar</a></span>
                                    <?php if ((string) $form['id'] !== F10_Lead_Capture_Config::DEFAULT_FORM_ID) : ?>
                                        <span> | <a class="submitdelete" href="<?php echo esc_url($delete_url); ?>" onclick="return confirm('Excluir este formulário? Os leads já capturados serão mantidos.');">Excluir</a></span>
                                    <?php endif; ?>
                                </div>
                                <small>ID: <code><?php echo esc_html((string) $form['id']); ?></code></small>
                            </td>
                            <td>
                                <code class="f10-shortcode-code">[f10_lead_form id=&quot;<?php echo esc_attr((string) $form['id']); ?>&quot;]</code>
                                <button type="button" class="button button-small" data-f10-copy-shortcode="[f10_lead_form id=&quot;<?php echo esc_attr((string) $form['id']); ?>&quot;]">Copiar</button>
                            </td>
                            <td><?php echo esc_html((string) count($enabled_fields)); ?> ativos</td>
                            <td><?php echo esc_html($this->form_conversion_label((array) $form['conversion'])); ?></td>
                            <td><?php echo ($form['active'] ?? '0') === '1' ? '<span class="f10-status f10-status--active">Ativo</span>' : '<span class="f10-status">Inativo</span>'; ?></td>
                            <td><?php echo esc_html($this->format_date((string) $form['updated_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private function render_form_editor(string $form_id): void
    {
        $forms = F10_Lead_Capture_Config::get_forms();
        $is_new = $form_id === '';
        $form = $is_new
            ? F10_Lead_Capture_Config::normalize_form(
                array_merge(
                    F10_Lead_Capture_Config::default_form(),
                    array(
                        'id' => '',
                        'name' => 'Novo formulário',
                        'conversion' => F10_Lead_Capture_Config::conversion_defaults(),
                    )
                ),
                'novo-formulario'
            )
            : ($forms[$form_id] ?? null);

        if (!is_array($form)) {
            echo '<div class="wrap"><h1>Formulário não encontrado</h1><p><a href="' . esc_url(admin_url('admin.php?page=f10-lead-forms')) . '">Voltar para a lista</a></p></div>';
            return;
        }

        if ($is_new) {
            $form['id'] = '';
        }

        $conversion = (array) $form['conversion'];
        ?>
        <div class="wrap f10-admin-page">
            <h1><?php echo $is_new ? 'Adicionar formulário' : 'Editar formulário'; ?></h1>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=f10-lead-forms')); ?>">← Voltar para a lista</a></p>
            <?php $this->render_forms_notice(); ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-f10-form-editor>
                <input type="hidden" name="action" value="f10_save_form">
                <input type="hidden" name="f10_form[original_id]" value="<?php echo esc_attr($is_new ? '' : (string) $form['id']); ?>">
                <?php wp_nonce_field('f10_save_form'); ?>

                <div class="f10-admin-layout f10-admin-layout--forms">
                    <div class="f10-admin-settings-column">
                        <section class="f10-admin-card">
                            <h2>Identificação e textos</h2>
                            <div class="f10-control-grid">
                                <label class="f10-control">
                                    <span>Nome interno</span>
                                    <input type="text" name="f10_form[name]" value="<?php echo esc_attr((string) $form['name']); ?>" maxlength="190" required>
                                    <small>Usado apenas no painel administrativo.</small>
                                </label>
                                <label class="f10-control">
                                    <span>Identificador</span>
                                    <input type="text" name="f10_form[id]" value="<?php echo esc_attr((string) $form['id']); ?>" maxlength="100" placeholder="ex.: ebook-gestao-escolar" <?php echo !$is_new && (string) $form['id'] === F10_Lead_Capture_Config::DEFAULT_FORM_ID ? 'readonly' : ''; ?>>
                                    <small>Forma o shortcode e não deve conter espaços.</small>
                                </label>
                                <label class="f10-switch-row f10-switch-row--compact f10-control--full">
                                    <input type="checkbox" name="f10_form[active]" value="1" <?php checked($form['active'], '1'); ?>>
                                    <span><strong>Formulário ativo</strong><small>Formulários inativos não são exibidos no site.</small></span>
                                </label>
                                <label class="f10-control f10-control--full">
                                    <span>Título do formulário</span>
                                    <input type="text" name="f10_form[title]" value="<?php echo esc_attr((string) $form['title']); ?>" maxlength="190" data-f10-form-preview="title">
                                </label>
                                <label class="f10-control f10-control--full">
                                    <span>Descrição</span>
                                    <textarea name="f10_form[description]" rows="3" maxlength="500" data-f10-form-preview="description"><?php echo esc_textarea((string) $form['description']); ?></textarea>
                                </label>
                                <label class="f10-control">
                                    <span>Texto do botão</span>
                                    <input type="text" name="f10_form[button]" value="<?php echo esc_attr((string) $form['button']); ?>" maxlength="120" data-f10-form-preview="button">
                                </label>
                                <label class="f10-control">
                                    <span>Mensagem após o envio</span>
                                    <input type="text" name="f10_form[success_message]" value="<?php echo esc_attr((string) $form['success_message']); ?>" maxlength="250">
                                </label>
                            </div>
                        </section>

                        <section class="f10-admin-card">
                            <h2>Contexto enviado com o lead</h2>
                            <div class="f10-control-grid">
                                <label class="f10-control">
                                    <span>Produto ou interesse padrão</span>
                                    <input type="text" name="f10_form[product]" value="<?php echo esc_attr((string) $form['product']); ?>" maxlength="190">
                                </label>
                                <label class="f10-control">
                                    <span>Origem</span>
                                    <input type="text" name="f10_form[source]" value="<?php echo esc_attr((string) $form['source']); ?>" maxlength="190">
                                </label>
                                <label class="f10-control f10-control--full">
                                    <span>Suborigem</span>
                                    <input type="text" name="f10_form[sub_source]" value="<?php echo esc_attr((string) $form['sub_source']); ?>" maxlength="190">
                                </label>
                            </div>
                        </section>

                        <section class="f10-admin-card">
                            <h2>Campos do formulário</h2>
                            <p class="description">Escolha o que será solicitado em cada formulário. O nome técnico enviado à F10 não é alterado.</p>
                            <table class="widefat striped f10-fields-table">
                                <thead><tr><th>Campo</th><th style="width:90px">Exibir</th><th style="width:110px">Obrigatório</th><th>Texto exibido</th></tr></thead>
                                <tbody>
                                    <?php foreach (F10_Lead_Capture_Config::form_fields() as $field_key => $definition) : ?>
                                        <?php $field = (array) $form['fields'][$field_key]; ?>
                                        <tr data-f10-field-row>
                                            <td><strong><?php echo esc_html((string) $definition['label']); ?></strong><br><code><?php echo esc_html((string) $definition['request_key']); ?></code></td>
                                            <td><input type="checkbox" name="f10_form[fields][<?php echo esc_attr($field_key); ?>][enabled]" value="1" <?php checked($field['enabled'], '1'); ?> data-f10-field-enabled></td>
                                            <td><input type="checkbox" name="f10_form[fields][<?php echo esc_attr($field_key); ?>][required]" value="1" <?php checked($field['required'], '1'); ?> data-f10-field-required></td>
                                            <td><input type="text" class="regular-text" name="f10_form[fields][<?php echo esc_attr($field_key); ?>][label]" value="<?php echo esc_attr((string) $field['label']); ?>" maxlength="120"></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </section>

                        <section class="f10-admin-card">
                            <h2>Pós-conversão</h2>
                            <div class="f10-choice-grid f10-choice-grid--three">
                                <?php
                                $choices = array(
                                    'none' => array('dashicons-yes', 'Somente confirmar', 'Exibe apenas a mensagem de sucesso.'),
                                    'download' => array('dashicons-download', 'Liberar download', 'Entrega um arquivo da Biblioteca de Mídia.'),
                                    'link' => array('dashicons-external', 'Abrir uma página', 'Direciona o visitante para outra URL.'),
                                );
                                foreach ($choices as $value => $choice) :
                                    ?>
                                    <label class="f10-choice-card">
                                        <input type="radio" name="f10_form[conversion][type]" value="<?php echo esc_attr($value); ?>" <?php checked($conversion['type'], $value); ?> data-f10-conversion-type>
                                        <span class="dashicons <?php echo esc_attr($choice[0]); ?>" aria-hidden="true"></span>
                                        <strong><?php echo esc_html($choice[1]); ?></strong>
                                        <small><?php echo esc_html($choice[2]); ?></small>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div data-f10-conversion-settings>
                                <div class="f10-conversion-source" data-f10-source="download">
                                    <label class="f10-control f10-control--full">
                                        <span>Arquivo para download</span>
                                        <div class="f10-media-field">
                                            <input type="url" class="large-text" name="f10_form[conversion][file_url]" value="<?php echo esc_attr((string) $conversion['file_url']); ?>" placeholder="Selecione ou envie um arquivo" data-f10-file-url>
                                            <input type="hidden" name="f10_form[conversion][file_id]" value="<?php echo esc_attr((string) $conversion['file_id']); ?>" data-f10-file-id>
                                            <button type="button" class="button button-primary" data-f10-select-file>Enviar ou selecionar arquivo</button>
                                            <button type="button" class="button" data-f10-clear-file>Limpar</button>
                                        </div>
                                        <small>O arquivo fica salvo na Biblioteca de Mídia do WordPress.</small>
                                    </label>
                                </div>

                                <div class="f10-conversion-source" data-f10-source="link">
                                    <label class="f10-control f10-control--full">
                                        <span>URL de destino</span>
                                        <input type="url" name="f10_form[conversion][link_url]" value="<?php echo esc_attr((string) $conversion['link_url']); ?>" placeholder="https://exemplo.com.br/proxima-etapa">
                                    </label>
                                </div>

                                <div class="f10-control-grid f10-conversion-copy">
                                    <label class="f10-control f10-control--full"><span>Título da pós-conversão</span><input type="text" name="f10_form[conversion][title]" value="<?php echo esc_attr((string) $conversion['title']); ?>" maxlength="190"></label>
                                    <label class="f10-control f10-control--full"><span>Descrição</span><textarea name="f10_form[conversion][description]" rows="3" maxlength="500"><?php echo esc_textarea((string) $conversion['description']); ?></textarea></label>
                                    <label class="f10-control"><span>Texto do botão</span><input type="text" name="f10_form[conversion][label]" value="<?php echo esc_attr((string) $conversion['label']); ?>" maxlength="120"></label>
                                    <label class="f10-control"><span>Comportamento</span><select name="f10_form[conversion][behavior]" data-f10-conversion-behavior><option value="button" <?php selected($conversion['behavior'], 'button'); ?>>Mostrar botão</option><option value="automatic" <?php selected($conversion['behavior'], 'automatic'); ?>>Abrir automaticamente</option></select></label>
                                    <label class="f10-control" data-f10-delay-control><span>Aguardar antes de abrir</span><span class="f10-number-control"><input type="number" min="0" max="10000" step="100" name="f10_form[conversion][delay_ms]" value="<?php echo esc_attr((string) $conversion['delay_ms']); ?>"><small>ms</small></span></label>
                                    <label class="f10-switch-row f10-switch-row--compact"><input type="checkbox" name="f10_form[conversion][open_new_tab]" value="1" <?php checked($conversion['open_new_tab'], '1'); ?>><span><strong>Abrir em nova aba</strong><small>Aplicado ao clique manual.</small></span></label>
                                </div>
                            </div>
                        </section>

                        <?php submit_button($is_new ? 'Criar formulário' : 'Salvar formulário'); ?>
                    </div>

                    <aside class="f10-admin-preview-column">
                        <div class="f10-preview-toolbar"><strong>Pré-visualização</strong></div>
                        <div class="f10-preview-stage f10-preview-stage--compact">
                            <div class="f10-lead-capture">
                                <div class="f10-lead-capture__header">
                                    <h2 class="f10-lead-capture__title" data-f10-preview-title><?php echo esc_html((string) $form['title']); ?></h2>
                                    <p class="f10-lead-capture__description" data-f10-preview-description><?php echo esc_html((string) $form['description']); ?></p>
                                </div>
                                <div class="f10-lead-capture__grid"><label class="f10-lead-capture__field"><span>Nome</span><input type="text" value="Visitante" readonly></label><label class="f10-lead-capture__field"><span>WhatsApp</span><input type="text" value="(41) 99999-9999" readonly></label></div>
                                <button type="button" class="f10-lead-capture__button" data-f10-preview-button><?php echo esc_html((string) $form['button']); ?></button>
                            </div>
                        </div>
                        <section class="f10-admin-card f10-shortcode-panel">
                            <h2>Shortcode</h2>
                            <code data-f10-editor-shortcode>[f10_lead_form id=&quot;<?php echo esc_attr((string) ($form['id'] ?: 'identificador')); ?>&quot;]</code>
                            <p class="description">Após salvar, copie este shortcode para qualquer página ou post.</p>
                        </section>
                    </aside>
                </div>
            </form>
        </div>
        <?php
    }

    private function sanitize_form_conversion(array $input): array
    {
        $type = isset($input['type']) ? sanitize_key((string) $input['type']) : 'none';

        if (!in_array($type, array('none', 'download', 'link'), true)) {
            $type = 'none';
        }

        $conversion = array(
            'type' => $type,
            'behavior' => isset($input['behavior']) && (string) $input['behavior'] === 'automatic' ? 'automatic' : 'button',
            'title' => sanitize_text_field((string) ($input['title'] ?? '')),
            'description' => sanitize_textarea_field((string) ($input['description'] ?? '')),
            'label' => sanitize_text_field((string) ($input['label'] ?? '')),
            'link_url' => esc_url_raw((string) ($input['link_url'] ?? '')),
            'file_id' => (string) absint($input['file_id'] ?? 0),
            'file_url' => esc_url_raw((string) ($input['file_url'] ?? '')),
            'open_new_tab' => !empty($input['open_new_tab']) ? '1' : '0',
            'delay_ms' => (string) max(0, min(10000, absint($input['delay_ms'] ?? 800))),
        );
        $conversion = F10_Lead_Capture_Config::normalize_conversion($conversion);

        if ($type !== 'none' && F10_Lead_Capture_Config::conversion_url($conversion) === '') {
            $conversion['type'] = 'none';
            add_settings_error('f10_forms', 'missing_conversion_url', 'A pós-conversão foi salva como “Somente confirmar” porque nenhum arquivo ou link válido foi informado.', 'warning');
        }

        return $conversion;
    }

    private function unique_form_id(string $base, array $forms): string
    {
        $base = F10_Lead_Capture_Config::sanitize_form_id($base);
        $base = $base !== '' ? $base : 'formulario';
        $candidate = $base;
        $counter = 2;

        while (isset($forms[$candidate])) {
            $suffix = '-' . $counter;
            $candidate = substr($base, 0, max(1, 100 - strlen($suffix))) . $suffix;
            $counter++;
        }

        return $candidate;
    }

    private function form_conversion_label(array $conversion): string
    {
        $type = (string) ($conversion['type'] ?? 'none');

        if ($type === 'download') {
            return 'Download';
        }

        if ($type === 'link') {
            return 'Link';
        }

        return 'Somente mensagem';
    }

    private function render_forms_notice(): void
    {
        $notice = sanitize_key($this->query_text('f10_notice', 50));
        $messages = array(
            'form_saved' => array('success', 'Formulário salvo.'),
            'form_duplicated' => array('success', 'Formulário duplicado. Ajuste os dados e salve.'),
            'form_deleted' => array('success', 'Formulário excluído. Os leads já capturados foram mantidos.'),
            'default_protected' => array('error', 'O formulário principal não pode ser excluído.'),
            'form_missing' => array('error', 'Formulário não encontrado.'),
        );

        if (!isset($messages[$notice])) {
            return;
        }

        printf(
            '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
            esc_attr($messages[$notice][0]),
            esc_html($messages[$notice][1])
        );
    }
}
