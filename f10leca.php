<?php
/**
 * Plugin Name: F10 Escolar - Captura de Leads
 * Plugin URI: https://github.com/jefersonMarques/wp_plugin_f10_software
 * Description: Crie formulários e botões flutuantes de WhatsApp, armazene contatos localmente e integre o WordPress com a F10 Software e o Brevo.
 * Version: 1.3.10
 * Author: F10 Software
 * Author URI: https://f10.com.br/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: f10-captura-de-leads
 * Requires at least: 6.2
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('F10LECA_VERSION', '1.3.10');
define('F10LECA_FILE', __FILE__);
define('F10LECA_PATH', plugin_dir_path(__FILE__));
define('F10LECA_URL', plugin_dir_url(__FILE__));

require_once F10LECA_PATH . 'includes/class-f10leca-config.php';
require_once F10LECA_PATH . 'includes/class-f10leca-whatsapp-config.php';
require_once F10LECA_PATH . 'includes/class-f10leca-activator.php';
require_once F10LECA_PATH . 'includes/class-f10leca-deactivator.php';
require_once F10LECA_PATH . 'includes/class-f10leca-repository.php';
require_once F10LECA_PATH . 'includes/class-f10leca-integrations.php';
require_once F10LECA_PATH . 'includes/class-f10leca-submission-service.php';
require_once F10LECA_PATH . 'includes/class-f10leca-form.php';
require_once F10LECA_PATH . 'includes/class-f10leca-whatsapp.php';
require_once F10LECA_PATH . 'includes/class-f10leca-admin.php';
require_once F10LECA_PATH . 'includes/class-f10leca-plugin.php';

register_activation_hook(__FILE__, array('F10LECA_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('F10LECA_Deactivator', 'deactivate'));

F10LECA_Plugin::instance()->run();
