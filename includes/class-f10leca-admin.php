<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/admin/trait-f10leca-admin-settings.php';
require_once __DIR__ . '/admin/trait-f10leca-admin-leads.php';
require_once __DIR__ . '/admin/trait-f10leca-admin-appearance.php';
require_once __DIR__ . '/admin/trait-f10leca-admin-forms.php';
require_once __DIR__ . '/admin/trait-f10leca-admin-whatsapp.php';
require_once __DIR__ . '/admin/trait-f10leca-admin-whatsapp-editor.php';
require_once __DIR__ . '/admin/trait-f10leca-admin-whatsapp-editor-fields.php';
require_once __DIR__ . '/admin/trait-f10leca-admin-whatsapp-editor-form.php';
require_once __DIR__ . '/admin/trait-f10leca-admin-whatsapp-lead-labels.php';

final class F10LECA_Admin
{
    use F10LECA_Admin_Settings_Trait;
    use F10LECA_Admin_Leads_Trait, F10LECA_Admin_WhatsApp_Lead_Labels_Trait {
        F10LECA_Admin_WhatsApp_Lead_Labels_Trait::lead_form_name insteadof F10LECA_Admin_Leads_Trait;
        F10LECA_Admin_WhatsApp_Lead_Labels_Trait::render_conversion_status insteadof F10LECA_Admin_Leads_Trait;
        F10LECA_Admin_WhatsApp_Lead_Labels_Trait::conversion_type_label insteadof F10LECA_Admin_Leads_Trait;
        F10LECA_Admin_WhatsApp_Lead_Labels_Trait::conversion_status_label insteadof F10LECA_Admin_Leads_Trait;
    }
    use F10LECA_Admin_Appearance_Trait;
    use F10LECA_Admin_Forms_Trait;
    use F10LECA_Admin_WhatsApp_Trait;
    use F10LECA_Admin_WhatsApp_Editor_Trait;
    use F10LECA_Admin_WhatsApp_Editor_Fields_Trait;
    use F10LECA_Admin_WhatsApp_Editor_Form_Trait;

    private const OPTION_NAME = 'f10leca_settings';
    private const APPEARANCE_OPTION = 'f10leca_appearance';

    public function register_hooks(): void
    {
        add_action('admin_menu', array($this, 'register_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_post_f10leca_retry_lead', array($this, 'handle_retry'));
        add_action('admin_post_f10leca_delete_lead', array($this, 'handle_delete'));
        add_action('admin_post_f10leca_export_leads', array($this, 'handle_export'));
        add_action('admin_post_f10leca_save_form', array($this, 'handle_save_form'));
        add_action('admin_post_f10leca_duplicate_form', array($this, 'handle_duplicate_form'));
        add_action('admin_post_f10leca_delete_form', array($this, 'handle_delete_form'));
        add_action('admin_post_f10leca_save_whatsapp', array($this, 'handle_save_whatsapp'));
        add_action('admin_post_f10leca_duplicate_whatsapp', array($this, 'handle_duplicate_whatsapp'));
        add_action('admin_post_f10leca_delete_whatsapp', array($this, 'handle_delete_whatsapp'));
    }

    public function register_menu(): void
    {
        add_menu_page(
            'Leads F10',
            'Leads F10',
            'manage_options',
            'f10leca-leads',
            array($this, 'render_leads_page'),
            'dashicons-groups',
            26
        );

        add_submenu_page(
            'f10leca-leads',
            'Leads capturados',
            'Leads',
            'manage_options',
            'f10leca-leads',
            array($this, 'render_leads_page')
        );

        add_submenu_page(
            'f10leca-leads',
            'Formulários',
            'Formulários',
            'manage_options',
            'f10leca-lead-forms',
            array($this, 'render_forms_page')
        );

        add_submenu_page(
            'f10leca-leads',
            'Atendimento por WhatsApp',
            'WhatsApp',
            'manage_options',
            'f10leca-lead-whatsapp',
            array($this, 'render_whatsapp_page')
        );

        add_submenu_page(
            'f10leca-leads',
            'Aparência do formulário',
            'Aparência',
            'manage_options',
            'f10leca-lead-appearance',
            array($this, 'render_appearance_page')
        );

        add_submenu_page(
            'f10leca-leads',
            'Configurações',
            'Configurações',
            'manage_options',
            'f10leca-lead-settings',
            array($this, 'render_settings_page')
        );
    }

    public function register_settings(): void
    {
        register_setting(
            'f10leca_settings_group',
            self::OPTION_NAME,
            array($this, 'sanitize_settings')
        );

        register_setting(
            'f10leca_appearance_group',
            self::APPEARANCE_OPTION,
            array($this, 'sanitize_appearance')
        );
    }

    public function enqueue_admin_assets(string $hook_suffix): void
    {
        $page = sanitize_key($this->query_text('page', 80));

        if (!in_array($page, array('f10leca-lead-appearance', 'f10leca-lead-forms', 'f10leca-lead-whatsapp'), true)) {
            return;
        }

        wp_enqueue_style(
            'f10leca-form',
            F10LECA_URL . 'assets/css/form.css',
            array(),
            F10LECA_VERSION
        );

        wp_enqueue_style(
            'f10leca-admin',
            F10LECA_URL . 'assets/css/admin.css',
            array(),
            F10LECA_VERSION
        );

        if ($page === 'f10leca-lead-appearance') {
            wp_enqueue_script(
                'f10leca-admin-appearance',
                F10LECA_URL . 'assets/js/admin-appearance.js',
                array(),
                F10LECA_VERSION,
                true
            );

            wp_localize_script(
                'f10leca-admin-appearance',
                'F10LECAAppearance',
                array('presets' => F10LECA_Config::appearance_presets())
            );
        }

        if ($page === 'f10leca-lead-whatsapp') {
            wp_enqueue_style(
                'f10leca-whatsapp',
                F10LECA_URL . 'assets/css/whatsapp.css',
                array(),
                F10LECA_VERSION
            );
            wp_enqueue_style(
                'f10leca-admin-whatsapp',
                F10LECA_URL . 'assets/css/admin-whatsapp.css',
                array('f10leca-whatsapp'),
                F10LECA_VERSION
            );
            wp_enqueue_script(
                'f10leca-admin-whatsapp',
                F10LECA_URL . 'assets/js/admin-whatsapp.js',
                array(),
                F10LECA_VERSION,
                true
            );
        }

        if ($page === 'f10leca-lead-forms') {
            wp_enqueue_media();
            wp_enqueue_script(
                'f10leca-admin-forms',
                F10LECA_URL . 'assets/js/admin-forms.js',
                array(),
                F10LECA_VERSION,
                true
            );
        }
    }

    private function render_notice(): void
    {
        $notice = sanitize_key($this->query_text('f10leca_notice', 50));

        if ($notice === 'retried') {
            echo '<div class="notice notice-success is-dismissible"><p>O reenvio foi processado. Consulte os status e respostas abaixo.</p></div>';
        }

        if ($notice === 'deleted') {
            echo '<div class="notice notice-success is-dismissible"><p>Lead excluído permanentemente.</p></div>';
        }
    }

    private function render_status_badge(string $status): void
    {
        $colors = array(
            'completed' => array('#067647', '#ecfdf3'),
            'stored' => array('#175cd3', '#eff8ff'),
            'partial' => array('#b54708', '#fffaeb'),
            'failed' => array('#b42318', '#fef3f2'),
            'pending' => array('#344054', '#f2f4f7'),
        );
        $palette = $colors[$status] ?? array('#344054', '#f2f4f7');
        $label = $this->status_labels()[$status] ?? ucfirst($status);

        printf(
            '<span style="display:inline-block;padding:4px 8px;border-radius:999px;color:%s;background:%s;font-weight:600">%s</span>',
            esc_attr($palette[0]),
            esc_attr($palette[1]),
            esc_html($label)
        );
    }

    private function status_labels(): array
    {
        return array(
            'pending' => 'Pendente',
            'completed' => 'Concluído',
            'partial' => 'Parcial',
            'failed' => 'Falhou',
            'stored' => 'Somente salvo',
        );
    }

    private function format_date(string $utc_date): string
    {
        if ($utc_date === '') {
            return '—';
        }

        return get_date_from_gmt($utc_date, 'd/m/Y H:i:s');
    }

    private function format_phone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        if (strlen($digits) === 11) {
            return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $digits) ?: $digits;
        }

        if (strlen($digits) === 10) {
            return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $digits) ?: $digits;
        }

        return $digits;
    }

    private function query_text(string $key, int $max_length): string
    {
        $raw_value = filter_input(
            INPUT_GET,
            $key,
            FILTER_UNSAFE_RAW,
            FILTER_REQUIRE_SCALAR
        );

        if (!is_string($raw_value)) {
            return '';
        }

        $value = sanitize_text_field($raw_value);

        return function_exists('mb_substr')
            ? mb_substr($value, 0, $max_length)
            : substr($value, 0, $max_length);
    }

    private function query_int(string $key, int $default = 0): int
    {
        $value = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
        return is_int($value) ? $value : $default;
    }

    private function require_capability(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Você não possui permissão para acessar esta página.');
        }
    }
}
