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

        $whatsapp = new F10_Lead_Capture_WhatsApp();
        $whatsapp->register_hooks();

        // O HTML precisa existir antes de o script do rodapé executar.
        remove_action('wp_footer', array($whatsapp, 'render_widget'), 30);
        add_action('wp_footer', array($whatsapp, 'render_widget'), 5);
        add_filter('script_loader_tag', array($this, 'defer_whatsapp_script'), 10, 2);
        add_action('wp_enqueue_scripts', array($this, 'add_whatsapp_layout_fix'), 40);

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

    public function defer_whatsapp_script(string $tag, string $handle): string
    {
        if ($handle !== 'f10-lead-capture-whatsapp' || strpos($tag, ' defer') !== false) {
            return $tag;
        }

        return str_replace(' src=', ' defer src=', $tag);
    }

    public function add_whatsapp_layout_fix(): void
    {
        if (!wp_style_is('f10-lead-capture-whatsapp', 'enqueued')) {
            return;
        }

        $css = '.f10-whatsapp-widget.is-visible{transform:none!important}'
            . '.f10-whatsapp-widget__overlay{width:100vw!important;height:100vh!important;height:100dvh!important;max-width:none!important;box-sizing:border-box!important}'
            . '.f10-whatsapp-widget__dialog{width:min(390px,calc(100vw - 48px))!important;max-width:390px!important;min-width:0!important;box-sizing:border-box!important;max-height:calc(100vh - 48px)!important;max-height:calc(100dvh - 48px)!important}'
            . '.f10-whatsapp-widget__dialog-header{align-items:center!important;padding-right:52px!important}'
            . '.f10-whatsapp-widget__dialog-icon{position:relative!important;overflow:hidden!important}'
            . '.f10-whatsapp-widget__dialog-icon svg,.f10-whatsapp-widget__dialog-icon::after{position:absolute!important;top:50%!important;left:50%!important;transform:translate(-50%,-50%)!important}'
            . '.f10-whatsapp-widget__close{display:grid!important;place-items:center!important;padding:0!important;font-size:22px!important;line-height:0!important}'
            . '@media(max-width:767px){.f10-whatsapp-widget__dialog{width:100%!important;max-width:none!important;max-height:92vh!important;max-height:92dvh!important}}';

        wp_add_inline_style('f10-lead-capture-whatsapp', $css);
    }

    public function add_settings_link(array $links): array
    {
        array_unshift(
            $links,
            '<a href="' . esc_url(admin_url('admin.php?page=f10-lead-settings')) . '">Configurações</a>',
            '<a href="' . esc_url(admin_url('admin.php?page=f10-lead-forms')) . '">Formulários</a>',
            '<a href="' . esc_url(admin_url('admin.php?page=f10-lead-whatsapp')) . '">WhatsApp</a>',
            '<a href="' . esc_url(admin_url('admin.php?page=f10-lead-appearance')) . '">Aparência</a>'
        );

        return $links;
    }
}
