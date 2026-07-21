<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10_Lead_Capture_WhatsApp_Config
{
    public const OPTION_NAME = 'f10_lead_capture_whatsapp_widgets';

    public static function default_widget(): array
    {
        $now = current_time('mysql', true);

        return array(
            'id' => 'atendimento-comercial',
            'name' => 'Atendimento comercial',
            'active' => '1',
            'phone' => '',
            'targeting_mode' => 'all',
            'content_ids' => array(),
            'category_ids' => array(),
            'excluded_content_ids' => array(),
            'position' => 'right',
            'design' => 'pulse',
            'color' => '#25D366',
            'badge_online' => 'Estamos online',
            'badge_offline' => 'Deixe seus dados',
            'delay_seconds' => '2',
            'show_desktop' => '1',
            'show_mobile' => '1',
            'form_title' => 'Fale com nossa escola',
            'form_description' => 'Preencha seus dados para continuar o atendimento pelo WhatsApp.',
            'form_offline_description' => 'Estamos fora do horário de atendimento, mas você pode deixar seus dados e continuar pelo WhatsApp.',
            'button_label' => 'Continuar no WhatsApp',
            'form_display_mode' => 'smart',
            'message_template' => 'Olá! Meu nome é {name}. Gostaria de saber mais sobre os cursos da {site_name}.',
            'schedule_enabled' => '0',
            'outside_behavior' => 'open',
            'schedule' => self::default_schedule(),
            'created_at' => $now,
            'updated_at' => $now,
        );
    }

    public static function default_schedule(): array
    {
        return array(
            '1' => array('enabled' => '1', 'start' => '08:00', 'end' => '18:00'),
            '2' => array('enabled' => '1', 'start' => '08:00', 'end' => '18:00'),
            '3' => array('enabled' => '1', 'start' => '08:00', 'end' => '18:00'),
            '4' => array('enabled' => '1', 'start' => '08:00', 'end' => '18:00'),
            '5' => array('enabled' => '1', 'start' => '08:00', 'end' => '18:00'),
            '6' => array('enabled' => '1', 'start' => '08:00', 'end' => '12:00'),
            '7' => array('enabled' => '0', 'start' => '08:00', 'end' => '12:00'),
        );
    }

    public static function get_widgets(): array
    {
        $stored = get_option(self::OPTION_NAME, array());
        $widgets = array();

        if (!is_array($stored)) {
            return $widgets;
        }

        foreach ($stored as $widget_id => $widget) {
            if (!is_array($widget)) {
                continue;
            }

            $normalized = self::normalize_widget($widget, (string) $widget_id);
            $widgets[$normalized['id']] = $normalized;
        }

        return $widgets;
    }

    public static function get_widget(string $widget_id): ?array
    {
        $widget_id = self::sanitize_widget_id($widget_id);
        $widgets = self::get_widgets();

        return isset($widgets[$widget_id]) ? $widgets[$widget_id] : null;
    }

    public static function save_widgets(array $widgets): bool
    {
        $normalized = array();

        foreach ($widgets as $widget_id => $widget) {
            if (!is_array($widget)) {
                continue;
            }

            $item = self::normalize_widget($widget, (string) $widget_id);
            $normalized[$item['id']] = $item;
        }

        return update_option(self::OPTION_NAME, $normalized, false);
    }

    public static function normalize_widget(array $widget, string $widget_id = ''): array
    {
        $defaults = self::default_widget();
        $widget = wp_parse_args($widget, $defaults);
        $id = self::sanitize_widget_id($widget_id !== '' ? $widget_id : (string) $widget['id']);

        if ($id === '') {
            $id = 'whatsapp-' . strtolower(wp_generate_password(6, false, false));
        }

        $targeting_mode = in_array(
            (string) $widget['targeting_mode'],
            array('all', 'specific', 'categories'),
            true
        ) ? (string) $widget['targeting_mode'] : 'all';
        $design = in_array(
            (string) $widget['design'],
            array('static', 'pulse', 'radar', 'attention'),
            true
        ) ? (string) $widget['design'] : 'pulse';
        $outside_behavior = in_array(
            (string) $widget['outside_behavior'],
            array('open', 'capture_only', 'hide'),
            true
        ) ? (string) $widget['outside_behavior'] : 'open';
        $form_display_mode = in_array(
            (string) $widget['form_display_mode'],
            array('always', 'smart', 'never'),
            true
        ) ? (string) $widget['form_display_mode'] : 'smart';

        return array(
            'id' => $id,
            'name' => self::limited_text((string) $widget['name'], 190, 'Atendimento comercial'),
            'active' => (string) $widget['active'] === '1' ? '1' : '0',
            'phone' => self::normalize_phone((string) $widget['phone']),
            'targeting_mode' => $targeting_mode,
            'content_ids' => self::normalize_id_list($widget['content_ids']),
            'category_ids' => self::normalize_id_list($widget['category_ids']),
            'excluded_content_ids' => self::normalize_id_list($widget['excluded_content_ids']),
            'position' => (string) $widget['position'] === 'left' ? 'left' : 'right',
            'design' => $design,
            'color' => sanitize_hex_color((string) $widget['color']) ?: '#25D366',
            'badge_online' => self::limited_text((string) $widget['badge_online'], 80, 'Estamos online'),
            'badge_offline' => self::limited_text((string) $widget['badge_offline'], 80, 'Deixe seus dados'),
            'delay_seconds' => (string) max(0, min(5, absint($widget['delay_seconds']))),
            'show_desktop' => (string) $widget['show_desktop'] === '1' ? '1' : '0',
            'show_mobile' => (string) $widget['show_mobile'] === '1' ? '1' : '0',
            'form_title' => self::limited_text((string) $widget['form_title'], 190, 'Fale com nossa escola'),
            'form_description' => self::limited_textarea(
                (string) $widget['form_description'],
                500,
                'Preencha seus dados para continuar o atendimento pelo WhatsApp.'
            ),
            'form_offline_description' => self::limited_textarea(
                (string) $widget['form_offline_description'],
                500,
                'Estamos fora do horário de atendimento, mas você pode deixar seus dados e continuar pelo WhatsApp.'
            ),
            'button_label' => self::limited_text(
                (string) $widget['button_label'],
                100,
                'Continuar no WhatsApp'
            ),
            'form_display_mode' => $form_display_mode,
            'message_template' => self::limited_textarea(
                (string) $widget['message_template'],
                1000,
                'Olá! Meu nome é {name}. Gostaria de saber mais sobre os cursos da {site_name}.'
            ),
            'schedule_enabled' => (string) $widget['schedule_enabled'] === '1' ? '1' : '0',
            'outside_behavior' => $outside_behavior,
            'schedule' => self::normalize_schedule($widget['schedule']),
            'created_at' => (string) ($widget['created_at'] ?: current_time('mysql', true)),
            'updated_at' => (string) ($widget['updated_at'] ?: current_time('mysql', true)),
        );
    }

    public static function resolve_current_widget(): ?array
    {
        $matches = array();

        foreach (self::get_widgets() as $widget) {
            $score = self::targeting_score($widget);

            if ($score <= 0) {
                continue;
            }

            $matches[] = array(
                'score' => $score,
                'updated_at' => strtotime((string) $widget['updated_at']) ?: 0,
                'widget' => $widget,
            );
        }

        if (!$matches) {
            return null;
        }

        usort(
            $matches,
            static function (array $left, array $right): int {
                if ($left['score'] !== $right['score']) {
                    return $right['score'] <=> $left['score'];
                }

                return $right['updated_at'] <=> $left['updated_at'];
            }
        );

        return $matches[0]['widget'];
    }

    public static function schedule_state(array $widget, ?DateTimeImmutable $now = null): array
    {
        if (($widget['schedule_enabled'] ?? '0') !== '1') {
            return array('online' => true, 'behavior' => 'open');
        }

        $now = $now ?: current_datetime();
        $day_key = $now->format('N');
        $schedule = is_array($widget['schedule'] ?? null) ? $widget['schedule'] : self::default_schedule();
        $day = is_array($schedule[$day_key] ?? null)
            ? $schedule[$day_key]
            : array('enabled' => '0', 'start' => '08:00', 'end' => '18:00');
        $online = false;

        if (($day['enabled'] ?? '0') === '1') {
            $current_minutes = ((int) $now->format('H') * 60) + (int) $now->format('i');
            $start_minutes = self::time_to_minutes((string) ($day['start'] ?? '08:00'));
            $end_minutes = self::time_to_minutes((string) ($day['end'] ?? '18:00'));

            if ($start_minutes <= $end_minutes) {
                $online = $current_minutes >= $start_minutes && $current_minutes <= $end_minutes;
            } else {
                $online = $current_minutes >= $start_minutes || $current_minutes <= $end_minutes;
            }
        }

        return array(
            'online' => $online,
            'behavior' => $online ? 'open' : (string) ($widget['outside_behavior'] ?? 'open'),
        );
    }

    public static function sanitize_widget_id(string $value): string
    {
        $value = sanitize_title($value);
        $value = preg_replace('/[^a-z0-9\-_]/', '', $value) ?: '';

        return substr($value, 0, 100);
    }

    public static function normalize_phone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        if (strlen($digits) >= 10 && strlen($digits) <= 11) {
            $digits = '55' . $digits;
        }

        return strlen($digits) >= 10 && strlen($digits) <= 15 ? $digits : '';
    }

    public static function build_message(array $widget, array $values): string
    {
        $replacements = array(
            '{name}' => (string) ($values['name'] ?? ''),
            '{visitor_whatsapp}' => (string) ($values['visitor_whatsapp'] ?? ''),
            '{site_name}' => (string) ($values['site_name'] ?? get_bloginfo('name')),
            '{page_title}' => (string) ($values['page_title'] ?? ''),
            '{page_url}' => (string) ($values['page_url'] ?? ''),
            '{utm_source}' => (string) ($values['utm_source'] ?? ''),
            '{utm_campaign}' => (string) ($values['utm_campaign'] ?? ''),
        );

        return trim(strtr((string) $widget['message_template'], $replacements));
    }

    public static function build_url(array $widget, string $message): string
    {
        $phone = self::normalize_phone((string) $widget['phone']);

        if ($phone === '') {
            return '';
        }

        return 'https://wa.me/' . rawurlencode($phone) . '?text=' . rawurlencode($message);
    }

    private static function targeting_score(array $widget): int
    {
        if (($widget['active'] ?? '0') !== '1' || (string) ($widget['phone'] ?? '') === '') {
            return 0;
        }

        $content_id = is_singular() ? (int) get_queried_object_id() : 0;

        if ($content_id > 0 && in_array($content_id, (array) $widget['excluded_content_ids'], true)) {
            return 0;
        }

        if ($widget['targeting_mode'] === 'all') {
            return 100;
        }

        if ($widget['targeting_mode'] === 'specific') {
            return $content_id > 0 && in_array($content_id, (array) $widget['content_ids'], true)
                ? 300
                : 0;
        }

        if ($widget['targeting_mode'] === 'categories') {
            $selected_categories = array_map('intval', (array) $widget['category_ids']);

            if (is_category()) {
                return in_array((int) get_queried_object_id(), $selected_categories, true) ? 200 : 0;
            }

            if ($content_id > 0) {
                $post_categories = wp_get_post_categories($content_id);
                return array_intersect($selected_categories, array_map('intval', $post_categories))
                    ? 200
                    : 0;
            }
        }

        return 0;
    }

    private static function normalize_id_list($value): array
    {
        if (!is_array($value)) {
            return array();
        }

        $ids = array_values(array_unique(array_filter(array_map('absint', $value))));

        return array_slice($ids, 0, 500);
    }

    private static function normalize_schedule($value): array
    {
        $input = is_array($value) ? $value : array();
        $defaults = self::default_schedule();
        $schedule = array();

        foreach ($defaults as $day_key => $day_defaults) {
            $day = is_array($input[$day_key] ?? null) ? $input[$day_key] : array();
            $schedule[$day_key] = array(
                'enabled' => !empty($day['enabled']) && (string) $day['enabled'] !== '0' ? '1' : '0',
                'start' => self::sanitize_time((string) ($day['start'] ?? $day_defaults['start'])),
                'end' => self::sanitize_time((string) ($day['end'] ?? $day_defaults['end'])),
            );
        }

        return $schedule;
    }

    private static function sanitize_time(string $value): string
    {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value) === 1 ? $value : '08:00';
    }

    private static function time_to_minutes(string $value): int
    {
        $parts = array_map('intval', explode(':', self::sanitize_time($value)));

        return ($parts[0] * 60) + $parts[1];
    }

    private static function limited_text(string $value, int $length, string $fallback): string
    {
        $value = sanitize_text_field($value);

        if ($value === '') {
            return $fallback;
        }

        return function_exists('mb_substr')
            ? mb_substr($value, 0, $length)
            : substr($value, 0, $length);
    }

    private static function limited_textarea(string $value, int $length, string $fallback): string
    {
        $value = sanitize_textarea_field($value);

        if ($value === '') {
            return $fallback;
        }

        return function_exists('mb_substr')
            ? mb_substr($value, 0, $length)
            : substr($value, 0, $length);
    }
}
