<?php
/**
 * Plugin Name: Flora@home Plugin
 * Plugin URI: https://wordpress.org/plugins/florahome/
 * Description: Flora@Home stelt elke webshop in staat een assortiment verse bloemen en planten te verkopen. Geheel geïntegreerd in de webshop. Zonder risico of investeringen. Direct vanaf Nederlandse kwekers, bezorgd in heel Europa.
 * Version: 1.2.1
 * Author: Inshoring Pros
 * Author URI: https://www.floraathome.nl
 * Text Domain: florahome
 * WC requires at least: 7.6
 * WC tested up to: 8.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package Flora@home
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 *
 */
define('florahome_VERSION', '1.2.1');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-florahome-activator.php
 */
function fah_activate_florahome() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-florahome-activator.php';
    fah_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-florahome-deactivator.php
 */
function fah_deactivate_florahome() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-florahome-deactivator.php';
    fah_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'fah_activate_florahome');
register_deactivation_hook(__FILE__, 'fah_deactivate_florahome');

/**
 * The core plugin class that is used to define
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-florahome.php';

/**
 * Begins execution of the plugin.
 *
 *
 * @since    1.0.0
 */
function fah_run_florahome() {
    $plugin = new florahome();
    $plugin->run();
}

fah_run_florahome();