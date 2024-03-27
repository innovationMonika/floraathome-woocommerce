<?php

/**
 *
 * @link       https://www.floraathome.nl/
 * @since      1.0.0
 *
 * @package    florahome
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option('fah_settings');
wp_clear_scheduled_hook( 'task_flora_product_update' );
wp_clear_scheduled_hook( 'task_flora_order_export' );
wp_clear_scheduled_hook( 'task_flora_image_import' );
wp_clear_scheduled_hook( 'task_flora_product_sync' );