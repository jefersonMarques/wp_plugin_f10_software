<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10_Lead_Capture_Repository
{
    private const CACHE_GROUP = 'f10_lead_capture';

    public static function table_name(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'f10_leads';
    }

    public static function create(array $data): int
    {
        global $wpdb;

        $now = current_time('mysql', true);
        $insert_data = array(
            'name' => $data['name'],
            'phone' => $data['phone'] ?: null,
            'whatsapp' => $data['whatsapp'],
            'email' => $data['email'],
            'institution_name' => $data['institution_name'] ?: null,
            'product' => $data['product'] ?: null,
            'notes' => $data['notes'] ?: null,
            'form_id' => $data['form_id'] ?: 'default',
            'source_label' => $data['source_label'] ?: null,
            'sub_source' => $data['sub_source'] ?: null,
            'page_url' => $data['page_url'] ?: null,
            'referrer_url' => $data['referrer_url'] ?: null,
            'utm_source' => $data['utm_source'] ?: null,
            'utm_medium' => $data['utm_medium'] ?: null,
            'utm_campaign' => $data['utm_campaign'] ?: null,
            'utm_term' => $data['utm_term'] ?: null,
            'utm_content' => $data['utm_content'] ?: null,
            'ip_hash' => $data['ip_hash'] ?: null,
            'user_agent' => $data['user_agent'] ?: null,
            'consent_at' => $data['consent_at'] ?: null,
            'status' => 'pending',
            'f10_status' => 'pending',
            'brevo_status' => 'pending',
            'attempts' => 0,
            'conversion_type' => $data['conversion_type'] ?: 'none',
            'conversion_status' => $data['conversion_status'] ?: 'none',
            'conversion_url' => $data['conversion_url'] ?: null,
            'conversion_label' => $data['conversion_label'] ?: null,
            'conversion_behavior' => $data['conversion_behavior'] ?: null,
            'conversion_count' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        );
        $formats = array(
            '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d',
            '%s', '%s',
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- A gravação ocorre em tabela própria do plugin e invalida o cache relacionado.
        $inserted = $wpdb->insert(self::table_name(), $insert_data, $formats);

        if ($inserted === false) {
            return 0;
        }

        $lead_id = (int) $wpdb->insert_id;
        self::clear_cache($lead_id);

        return $lead_id;
    }

    public static function get(int $lead_id): ?array
    {
        global $wpdb;

        $cache_key = self::cache_key($lead_id);
        $found = false;
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP, false, $found);

        if ($found) {
            return is_array($cached) ? $cached : null;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Consulta necessária em tabela própria; o resultado é armazenado no cache de objetos.
        $lead = $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM %i WHERE id = %d',
                self::table_name(),
                $lead_id
            ),
            ARRAY_A
        );

        $normalized_lead = is_array($lead) ? $lead : null;
        wp_cache_set($cache_key, $normalized_lead, self::CACHE_GROUP, MINUTE_IN_SECONDS);

        return $normalized_lead;
    }

    public static function update(int $lead_id, array $data): bool
    {
        global $wpdb;

        $data['updated_at'] = current_time('mysql', true);
        $formats = array();

        foreach ($data as $key => $value) {
            $formats[] = in_array(
                $key,
                array('attempts', 'f10_http_status', 'brevo_http_status', 'conversion_count'),
                true
            ) ? '%d' : '%s';
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- A atualização ocorre em tabela própria e invalida o cache relacionado.
        $result = $wpdb->update(
            self::table_name(),
            $data,
            array('id' => $lead_id),
            $formats,
            array('%d')
        );

        self::clear_cache($lead_id);

        return $result !== false;
    }

    public static function track_conversion(int $lead_id): bool
    {
        global $wpdb;

        $now = current_time('mysql', true);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Incremento atômico do contador de conversão na tabela própria do plugin.
        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE %i
                 SET conversion_status = %s,
                     conversion_count = conversion_count + 1,
                     conversion_at = COALESCE(conversion_at, %s),
                     updated_at = %s
                 WHERE id = %d
                   AND conversion_type IN (%s, %s)",
                self::table_name(),
                'completed',
                $now,
                $now,
                $lead_id,
                'download',
                'link'
            )
        );

        self::clear_cache($lead_id);

        return $updated !== false && $updated > 0;
    }

    public static function delete(int $lead_id): bool
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- A exclusão ocorre em tabela própria e invalida o cache relacionado.
        $deleted = $wpdb->delete(
            self::table_name(),
            array('id' => $lead_id),
            array('%d')
        ) !== false;

        self::clear_cache($lead_id);

        return $deleted;
    }

    public static function paginate(array $filters, int $page, int $per_page): array
    {
        global $wpdb;

        $normalized_filters = self::normalize_filters($filters);
        $status = $normalized_filters['status'];
        $search = $normalized_filters['search'];
        $search_like = '%' . $wpdb->esc_like($search) . '%';
        $offset = max(0, ($page - 1) * $per_page);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Listagem administrativa paginada de tabela própria; deve refletir o estado atual das integrações.
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM %i
                 WHERE (%s = '' OR status = %s)
                   AND (
                       %s = ''
                       OR name LIKE %s
                       OR email LIKE %s
                       OR phone LIKE %s
                       OR whatsapp LIKE %s
                       OR institution_name LIKE %s
                       OR product LIKE %s
                   )
                 ORDER BY created_at DESC, id DESC
                 LIMIT %d OFFSET %d",
                self::table_name(),
                $status,
                $status,
                $search,
                $search_like,
                $search_like,
                $search_like,
                $search_like,
                $search_like,
                $search_like,
                max(1, $per_page),
                $offset
            ),
            ARRAY_A
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Contagem administrativa de tabela própria; deve refletir o estado atual das integrações.
        $total = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM %i
                 WHERE (%s = '' OR status = %s)
                   AND (
                       %s = ''
                       OR name LIKE %s
                       OR email LIKE %s
                       OR phone LIKE %s
                       OR whatsapp LIKE %s
                       OR institution_name LIKE %s
                       OR product LIKE %s
                   )",
                self::table_name(),
                $status,
                $status,
                $search,
                $search_like,
                $search_like,
                $search_like,
                $search_like,
                $search_like,
                $search_like
            )
        );

        return array(
            'items' => is_array($items) ? $items : array(),
            'total' => $total,
        );
    }

    public static function find_retryable(int $limit, int $max_attempts): array
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- A fila precisa consultar o estado mais recente dos leads pendentes.
        $leads = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM %i
                 WHERE status IN (%s, %s, %s)
                   AND attempts < %d
                   AND (next_retry_at IS NULL OR next_retry_at <= %s)
                 ORDER BY created_at ASC
                 LIMIT %d',
                self::table_name(),
                'pending',
                'partial',
                'failed',
                max(1, $max_attempts),
                current_time('mysql', true),
                max(1, $limit)
            ),
            ARRAY_A
        );

        return is_array($leads) ? $leads : array();
    }

    public static function find_sent_f10_results(int $limit = 1000): array
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Reconciliação pontual de respostas F10 já armazenadas após atualização da regra de sucesso.
        $leads = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT id, status, f10_status, f10_http_status, f10_response, brevo_status, attempts
                 FROM %i
                 WHERE f10_status = %s
                   AND f10_response IS NOT NULL
                   AND f10_response <> %s
                 ORDER BY id ASC
                 LIMIT %d',
                self::table_name(),
                'sent',
                '',
                max(1, $limit)
            ),
            ARRAY_A
        );

        return is_array($leads) ? $leads : array();
    }

    public static function all_for_export(array $filters, int $limit = 10000): array
    {
        global $wpdb;

        $normalized_filters = self::normalize_filters($filters);
        $status = $normalized_filters['status'];
        $search = $normalized_filters['search'];
        $search_like = '%' . $wpdb->esc_like($search) . '%';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Exportação administrativa sob demanda de tabela própria.
        $leads = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM %i
                 WHERE (%s = '' OR status = %s)
                   AND (
                       %s = ''
                       OR name LIKE %s
                       OR email LIKE %s
                       OR phone LIKE %s
                       OR whatsapp LIKE %s
                       OR institution_name LIKE %s
                       OR product LIKE %s
                   )
                 ORDER BY created_at DESC
                 LIMIT %d",
                self::table_name(),
                $status,
                $status,
                $search,
                $search_like,
                $search_like,
                $search_like,
                $search_like,
                $search_like,
                $search_like,
                max(1, $limit)
            ),
            ARRAY_A
        );

        return is_array($leads) ? $leads : array();
    }

    private static function normalize_filters(array $filters): array
    {
        $status = isset($filters['status']) ? sanitize_key((string) $filters['status']) : '';
        $allowed_statuses = array('pending', 'completed', 'partial', 'failed', 'stored');

        if (!in_array($status, $allowed_statuses, true)) {
            $status = '';
        }

        $search = isset($filters['search'])
            ? sanitize_text_field((string) $filters['search'])
            : '';

        return array(
            'status' => $status,
            'search' => $search,
        );
    }

    private static function cache_key(int $lead_id): string
    {
        return 'lead_' . $lead_id;
    }

    private static function clear_cache(int $lead_id): void
    {
        wp_cache_delete(self::cache_key($lead_id), self::CACHE_GROUP);
    }
}
