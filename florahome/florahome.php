<?php

/**
 * The plugin bootstrap file
 *
 *
 * @link              http://example.com
 * @since             1.0.0
 * @package           florahome
 * @wordpress-plugin
 * Plugin Name:       Flora@home plugin
 * Description:       www.floraathome.nl
 * Version:           1.0.0
 * Author:            Inshoring Pros
 * Author URI:        http://www.resourcing-pros.com
 * Text Domain:       florahome
 * 
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * 
 */
define( 'florahome_VERSION', '1.0.7' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-florahome-activator.php
 */
function activate_florahome() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-florahome-activator.php';
	
	florahome_Activator::activate();
	
	
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-florahome-deactivator.php
 */
function deactivate_florahome() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-florahome-deactivator.php';
	florahome_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_florahome' );
register_deactivation_hook( __FILE__, 'deactivate_florahome' );

/**
 * The core plugin class that is used to define 
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-florahome.php';

/**
 * Begins execution of the plugin.
 *
 *
 * @since    1.0.0
 */
function run_florahome() {

	$plugin = new florahome();
	$plugin->run();

}

run_florahome();
