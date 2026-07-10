<?php
/**
 * Plugin Name: F10 Lead Capture - Integração F10 Software e Brevo
 * Plugin URI: https://github.com/jefersonMarques/wp_plugin_f10_software
 * Description: Formulário WordPress para captar leads, salvar contatos no banco e integrar o site ao F10 Software e ao Brevo.
 * Version: 1.0.1
 * Author: F10 Software
 * Author URI: https://f10.com.br
 * Text Domain: f10-lead-capture
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('F10_LEAD_CAPTURE_VERSION', '1.0.1');
define('F10_LEAD_CAPTURE_FILE', __FILE__);
define('F10_LEAD_CAPTURE_PATH', plugin_dir_path(__FILE__));
define('F10_LEAD_CAPTURE_URL', plugin_dir_url(__FILE__));

require_once F10_LEAD_CAPTURE_PATH . 'includes/class-f10-lead-capture-activator.php';
require_once F10_LEAD_CAPTURE_PATH . 'includes/class-f10-lead-capture-deactivator.php';
require_once F10_LEAD_CAPTURE_PATH . 'includes/class-f10-lead-capture-repository.php';
require_once F10_LEAD_CAPTURE_PATH . 'includes/class-f10-lead-capture-integrations.php';
require_once F10_LEAD_CAPTURE_PATH . 'includes/class-f10-lead-capture-form.php';
require_once F10_LEAD_CAPTURE_PATH . 'includes/class-f10-lead-capture-admin.php';
require_once F10_LEAD_CAPTURE_PATH . 'includes/class-f10-lead-capture-plugin.php';

register_activation_hook(__FILE__, array('F10_Lead_Capture_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('F10_Lead_Capture_Deactivator', 'deactivate'));

F10_Lead_Capture_Plugin::instance()->run();
