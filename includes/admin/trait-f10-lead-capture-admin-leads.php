<?php

if (!defined('ABSPATH')) {
    exit;
}

trait F10_Lead_Capture_Admin_Leads_Trait
{
    public function render_leads_page(): void
    {
        $this->require_capability();

        $action = isset($_GET['action']) ? sanitize_key(wp_unslash((string) $_GET['action'])) : '';
        $lead_id = isset($_GET['lead_id']) ? absint($_GET['lead_id']) : 0;

        if ($action === 'view' && $lead_id > 0) {
            $this->render_lead_details($lead_id);
            return;
        }

        $status = isset($_GET['status']) ? sanitize_key(wp_unslash((string) $_GET['status'])) : '';
        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash((string) $_GET['s'])) : '';
        $page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $per_page = 20;
        $result = F10_Lead_Capture_Repository::paginate(
            array('status' => $status, 'search' => $search),
            $page,
            $per_page
        );

        $total_pages = max(1, (int) ceil($result['total'] / $per_page));
        $export_url = wp_nonce_url(
            add_query_arg(
                array(
                    'action' => 'f10_export_leads',
                    'status' => $status,
                    's' => $search,
                ),
                admin_url('admin-post.php')
            ),
            'f10_export_leads'
        );
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Leads F10</h1>
            <a class="page-title-action" href="<?php echo esc_url($export_url); ?>">Exportar CSV</a>
            <a class="page-title-action" href="<?php echo esc_url(admin_url('admin.php?page=f10-lead-settings')); ?>">Configurações</a>
            <hr class="wp-header-end">

            <?php $this->render_notice(); ?>

            <form method="get" style="margin:16px 0;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                <input type="hidden" name="page" value="f10-leads">
                <select name="status">
                    <option value="">Todos os status</option>
                    <?php foreach ($this->status_labels() as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($status, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Nome, e-mail, WhatsApp ou escola" style="min-width:280px">
                <button type="submit" class="button">Filtrar</button>
                <?php if ($status !== '' || $search !== '') : ?>
                    <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=f10-leads')); ?>">Limpar</a>
                <?php endif; ?>
            </form>

            <p><strong><?php echo esc_html(number_format_i18n((int) $result['total'])); ?></strong> lead(s) encontrado(s).</p>

            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:70px">ID</th>
                        <th style="width:150px">Data</th>
                        <th>Lead</th>
                        <th>Contato</th>
                        <th>Origem</th>
                        <th style="width:115px">Status</th>
                        <th style="width:160px">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$result['items']) : ?>
                        <tr><td colspan="7">Nenhum lead encontrado.</td></tr>
                    <?php else : ?>
                        <?php foreach ($result['items'] as $lead) : ?>
                            <tr>
                                <td>#<?php echo esc_html((string) $lead['id']); ?></td>
                                <td><?php echo esc_html($this->format_date((string) $lead['created_at'])); ?></td>
                                <td>
                                    <strong><?php echo esc_html((string) $lead['name']); ?></strong>
                                    <?php if (!empty($lead['institution_name'])) : ?>
                                        <br><span style="color:#646970"><?php echo esc_html((string) $lead['institution_name']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo esc_attr((string) $lead['email']); ?>"><?php echo esc_html((string) $lead['email']); ?></a>
                                    <br><?php echo esc_html($this->format_phone((string) $lead['whatsapp'])); ?>
                                </td>
                                <td>
                                    <?php echo esc_html((string) ($lead['source_label'] ?: '—')); ?>
                                    <?php if (!empty($lead['utm_campaign'])) : ?>
                                        <br><span style="color:#646970">UTM: <?php echo esc_html((string) $lead['utm_campaign']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php $this->render_status_badge((string) $lead['status']); ?></td>
                                <td>
                                    <a class="button button-small" href="<?php echo esc_url($this->view_url((int) $lead['id'])); ?>">Detalhes</a>
                                    <?php if (!in_array($lead['status'], array('completed', 'stored'), true)) : ?>
                                        <a class="button button-small" href="<?php echo esc_url($this->retry_url((int) $lead['id'])); ?>">Reenviar</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($total_pages > 1) : ?>
                <div class="tablenav"><div class="tablenav-pages" style="margin:16px 0">
                    <?php
                    echo wp_kses_post(
                        paginate_links(
                            array(
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'current' => $page,
                                'total' => $total_pages,
                                'type' => 'list',
                            )
                        )
                    );
                    ?>
                </div></div>
            <?php endif; ?>
        </div>
        <?php
    }

    public function handle_retry(): void
    {
        $this->require_capability();
        $lead_id = isset($_GET['lead_id']) ? absint($_GET['lead_id']) : 0;
        check_admin_referer('f10_retry_lead_' . $lead_id);

        if ($lead_id > 0) {
            F10_Lead_Capture_Integrations::process_lead($lead_id);
        }

        wp_safe_redirect(
            add_query_arg(
                array('page' => 'f10-leads', 'action' => 'view', 'lead_id' => $lead_id, 'f10_notice' => 'retried'),
                admin_url('admin.php')
            )
        );
        exit;
    }

    public function handle_delete(): void
    {
        $this->require_capability();
        $lead_id = isset($_GET['lead_id']) ? absint($_GET['lead_id']) : 0;
        check_admin_referer('f10_delete_lead_' . $lead_id);

        if ($lead_id > 0) {
            F10_Lead_Capture_Repository::delete($lead_id);
        }

        wp_safe_redirect(
            add_query_arg(
                array('page' => 'f10-leads', 'f10_notice' => 'deleted'),
                admin_url('admin.php')
            )
        );
        exit;
    }

    public function handle_export(): void
    {
        $this->require_capability();
        check_admin_referer('f10_export_leads');

        $status = isset($_GET['status']) ? sanitize_key(wp_unslash((string) $_GET['status'])) : '';
        $search = isset($_GET['s']) ? sanitize_text_field(wp_unslash((string) $_GET['s'])) : '';
        $leads = F10_Lead_Capture_Repository::all_for_export(
            array('status' => $status, 'search' => $search)
        );

        nocache_headers();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="f10-leads-' . gmdate('Y-m-d-His') . '.csv"');

        $output = fopen('php://output', 'wb');

        if ($output === false) {
            wp_die('Não foi possível gerar o arquivo CSV.');
        }

        fwrite($output, "\xEF\xBB\xBF");
        fputcsv(
            $output,
            array(
                'ID', 'Data', 'Nome', 'WhatsApp', 'E-mail', 'Escola/empresa', 'Produto',
                'Origem', 'Suborigem', 'Página', 'Referência', 'UTM Source', 'UTM Medium',
                'UTM Campaign', 'UTM Term', 'UTM Content', 'Status', 'F10', 'Brevo', 'Tentativas'
            ),
            ';'
        );

        foreach ($leads as $lead) {
            fputcsv(
                $output,
                array(
                    $lead['id'],
                    $this->format_date((string) $lead['created_at']),
                    $lead['name'],
                    $lead['whatsapp'],
                    $lead['email'],
                    $lead['institution_name'],
                    $lead['product'],
                    $lead['source_label'],
                    $lead['sub_source'],
                    $lead['page_url'],
                    $lead['referrer_url'],
                    $lead['utm_source'],
                    $lead['utm_medium'],
                    $lead['utm_campaign'],
                    $lead['utm_term'],
                    $lead['utm_content'],
                    $lead['status'],
                    $lead['f10_status'],
                    $lead['brevo_status'],
                    $lead['attempts'],
                ),
                ';'
            );
        }

        fclose($output);
        exit;
    }

    private function render_lead_details(int $lead_id): void
    {
        $lead = F10_Lead_Capture_Repository::get($lead_id);

        if (!$lead) {
            wp_die('Lead não encontrado.');
        }

        $fields = array(
            'ID' => $lead['id'],
            'Criado em' => $this->format_date((string) $lead['created_at']),
            'Atualizado em' => $this->format_date((string) $lead['updated_at']),
            'Nome' => $lead['name'],
            'WhatsApp' => $this->format_phone((string) $lead['whatsapp']),
            'E-mail' => $lead['email'],
            'Escola/empresa' => $lead['institution_name'],
            'Produto/interesse' => $lead['product'],
            'Identificador do formulário' => $lead['form_id'],
            'Origem' => $lead['source_label'],
            'Suborigem' => $lead['sub_source'],
            'Página de captura' => $lead['page_url'],
            'Referência' => $lead['referrer_url'],
            'UTM source' => $lead['utm_source'],
            'UTM medium' => $lead['utm_medium'],
            'UTM campaign' => $lead['utm_campaign'],
            'UTM term' => $lead['utm_term'],
            'UTM content' => $lead['utm_content'],
            'Consentimento' => $lead['consent_at'] ? $this->format_date((string) $lead['consent_at']) : 'Não registrado',
            'Status geral' => $this->status_labels()[$lead['status']] ?? $lead['status'],
            'Status F10' => $lead['f10_status'],
            'HTTP F10' => $lead['f10_http_status'],
            'Status Brevo' => $lead['brevo_status'],
            'HTTP Brevo' => $lead['brevo_http_status'],
            'Tentativas' => $lead['attempts'],
            'Última tentativa' => $lead['last_attempt_at'] ? $this->format_date((string) $lead['last_attempt_at']) : '—',
            'Próxima tentativa' => $lead['next_retry_at'] ? $this->format_date((string) $lead['next_retry_at']) : '—',
            'Último erro' => $lead['last_error'],
        );
        ?>
        <div class="wrap">
            <h1>Lead #<?php echo esc_html((string) $lead_id); ?></h1>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=f10-leads')); ?>">&larr; Voltar para a lista</a></p>
            <?php $this->render_notice(); ?>

            <table class="widefat striped" style="max-width:1000px">
                <tbody>
                    <?php foreach ($fields as $label => $value) : ?>
                        <tr>
                            <th style="width:220px"><?php echo esc_html($label); ?></th>
                            <td style="word-break:break-word"><?php echo nl2br(esc_html((string) ($value !== '' ? $value : '—'))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="display:flex;gap:8px;margin:16px 0">
                <a class="button button-primary" href="<?php echo esc_url($this->retry_url($lead_id)); ?>">Reenviar integrações com falha</a>
                <a
                    class="button button-link-delete"
                    href="<?php echo esc_url($this->delete_url($lead_id)); ?>"
                    onclick="return confirm('Excluir permanentemente este lead do WordPress?')"
                >Excluir lead</a>
            </div>

            <h2>Resposta da F10</h2>
            <pre style="max-width:1000px;white-space:pre-wrap;word-break:break-word;background:#fff;border:1px solid #c3c4c7;padding:16px"><?php echo esc_html((string) ($lead['f10_response'] ?: 'Sem resposta registrada.')); ?></pre>

            <h2>Resposta do Brevo</h2>
            <pre style="max-width:1000px;white-space:pre-wrap;word-break:break-word;background:#fff;border:1px solid #c3c4c7;padding:16px"><?php echo esc_html((string) ($lead['brevo_response'] ?: 'Sem resposta registrada.')); ?></pre>
        </div>
        <?php
    }

    private function view_url(int $lead_id): string
    {
        return add_query_arg(
            array('page' => 'f10-leads', 'action' => 'view', 'lead_id' => $lead_id),
            admin_url('admin.php')
        );
    }

    private function retry_url(int $lead_id): string
    {
        return wp_nonce_url(
            add_query_arg(
                array('action' => 'f10_retry_lead', 'lead_id' => $lead_id),
                admin_url('admin-post.php')
            ),
            'f10_retry_lead_' . $lead_id
        );
    }

    private function delete_url(int $lead_id): string
    {
        return wp_nonce_url(
            add_query_arg(
                array('action' => 'f10_delete_lead', 'lead_id' => $lead_id),
                admin_url('admin-post.php')
            ),
            'f10_delete_lead_' . $lead_id
        );
    }
}
