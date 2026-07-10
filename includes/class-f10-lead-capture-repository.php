<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10_Lead_Capture_Repository
{
    public static function table_name(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'f10_leads';
    }

    public static function create(array $data): int
    {
        global $wpdb;

        $now = current_time('mysql', true);
        $formats = array_fill(0, 21, '%s');
        $formats[] = '%d';
        $formats[] = '%s';
        $formats[] = '%s';

        $inserted = $wpdb->insert(
            self::table_name(),
            array(
                'name' => $data['name'],
                'whatsapp' => $data['whatsapp'],
                'email' => $data['email'],
                'institution_name' => $data['institution_name'] ?: null,
                'product' => $data['product'] ?: null,
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
                'created_at' => $now,
                'updated_at' => $now,
            ),
            $formats
        );

        if ($inserted === false) {
            return 0;
        }

        return (int) $wpdb->insert_id;
    }

    public static function get(int $lead_id): ?array
    {
        global $wpdb;

        $lead = $wpdb->get_row(
            $wpdb->prepare(
                'SELECT * FROM ' . self::table_name() . ' WHERE id = %d',
                $lead_id
            ),
            ARRAY_A
        );

        return is_array($lead) ? $lead : null;
    }

    public static function update(int $lead_id, array $data): bool
    {
        global $wpdb;

        $data['updated_at'] = current_time('mysql', true);
        $formats = array();

        foreach ($data as $key => $value) {
            $formats[] = in_array(
                $key,
                array('attempts', 'f10_http_status', 'brevo_http_status'),
                true
            ) ? '%d' : '%s';
        }

        $result = $wpdb->update(
            self::table_name(),
            $data,
            array('id' => $lead_id),
            $formats,
            array('%d')
        );

        return $result !== false;
    }

    public static function delete(int $lead_id): bool
    {
        global $wpdb;

        return $wpdb->delete(
            self::table_name(),
            array('id' => $lead_id),
            array('%d')
        ) !== false;
    }

    public static function paginate(array $filters, int $page, int $per_page): array
    {
        global $wpdb;

        $table_name = self::table_name();
        $where = array('1=1');
        $params = array();

        if (!empty($filters['status'])) {
            $where[] = 'status = %s';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where[] = '(name LIKE %s OR email LIKE %s OR whatsapp LIKE %s OR institution_name LIKE %s)';
            array_push($params, $search, $search, $search, $search);
        }

        $where_sql = implode(' AND ', $where);
        $count_sql = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_sql}";
        $items_sql = "SELECT * FROM {$table_name} WHERE {$where_sql} ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d";

        $offset = max(0, ($page - 1) * $per_page);
        $count_query = $params
            ? $wpdb->prepare($count_sql, $params)
            : $count_sql;

        $items_params = array_merge($params, array($per_page, $offset));
        $items_query = $wpdb->prepare($items_sql, $items_params);

        return array(
            'items' => $wpdb->get_results($items_query, ARRAY_A),
            'total' => (int) $wpdb->get_var($count_query),
        );
    }

    public static function find_retryable(int $limit, int $max_attempts): array
    {
        global $wpdb;

        $now = current_time('mysql', true);
        $query = $wpdb->prepare(
            'SELECT * FROM ' . self::table_name() . '
             WHERE status IN (%s, %s, %s)
               AND attempts < %d
               AND (next_retry_at IS NULL OR next_retry_at <= %s)
             ORDER BY created_at ASC
             LIMIT %d',
            'pending',
            'partial',
            'failed',
            $max_attempts,
            $now,
            $limit
        );

        return $wpdb->get_results($query, ARRAY_A);
    }

    public static function all_for_export(array $filters, int $limit = 10000): array
    {
        global $wpdb;

        $table_name = self::table_name();
        $where = array('1=1');
        $params = array();

        if (!empty($filters['status'])) {
            $where[] = 'status = %s';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where[] = '(name LIKE %s OR email LIKE %s OR whatsapp LIKE %s OR institution_name LIKE %s)';
            array_push($params, $search, $search, $search, $search);
        }

        $sql = "SELECT * FROM {$table_name} WHERE " . implode(' AND ', $where) . ' ORDER BY created_at DESC LIMIT %d';
        $params[] = $limit;

        return $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
    }
}
