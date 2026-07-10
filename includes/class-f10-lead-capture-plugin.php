<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10_Lead_Capture_Plugin
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function run(): void
    {
        F10_Lead_Capture_Activator::maybe_upgrade();

        $form = new F10_Lead_Capture_Form();
        $form->register_hooks();

        if (is_admin()) {
            $admin = new F10_Lead_Capture_Admin();
            $admin->register_hooks();
        }

        add_action(
            'f10_lead_capture_retry_event',
            array('F10_Lead_Capture_Integrations', 'retry_pending_leads')
        );

        add_filter(
            'plugin_action_links_' . plugin_basename(F10_LEAD_CAPTURE_FILE),
            array($this, 'add_settings_link')
        );
    }

    public function add_settings_link(array $links): array
    {
        array_unshift(
            $links,
            '<a href="' . esc_url(admin_url('admin.php?page=f10-lead-settings')) . '">Configurações</a>',
            '<a href="' . esc_url(admin_url('admin.php?page=f10-lead-appearance')) . '">Aparência</a>'
        );

        return $links;
    }
}
