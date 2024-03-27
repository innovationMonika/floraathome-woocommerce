<?php
/**
 *
 * @link       https://www.floraathome.nl/
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/admin/partials
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_filter('manage_edit-shop_order_columns', 'fah_custom_shop_order_column', 11);
function fah_custom_shop_order_column($columns) {
    //add columns
    $columns['flora-order-export'] = __('Export Status', 'theme_slug');
    $columns['flora-order-code'] = __('Order Code', 'theme_slug');
    return $columns;
}
add_action('manage_shop_order_posts_custom_column', 'fah_add_exportedColumn', 10, 2);
function fah_add_exportedColumn($column) {
    global $post, $woocommerce, $the_order, $item;
    $the_order = wc_get_order($post->ID);
    $order_id = $the_order->get_id();
    switch ($column) {
        case 'flora-order-export':
            $isExported = get_post_meta($order_id, 'fah_orderExport', true);
            //ecsExport
            if (empty($isExported)) {
                echo 'NOT EXPORTED';
            } else echo $isExported;
            break;
        case 'flora-order-code':
            $fah_order_code = get_post_meta($order_id, 'fah_order_Export_code', true);
            if (empty($fah_order_code)) {
                echo 'NOT EXPORTED';
            } else echo esc_html($fah_order_code);
            break;
        }
    }
    add_filter('bulk_actions-edit-shop_order', 'fah_order_export_bulk'); // edit-shop_order is the screen ID of the orders page
    function fah_order_export_bulk($bulk_actions) {
        $bulk_actions['fah_orderexport_bulk'] = 'Export to Flora'; // <option value="fah_orderexport_bulk">Export to Flora</option>
        return $bulk_actions;
    }
    add_action('admin_action_fah_orderexport_bulk', 'fah_order_export_bulk_action'); // admin_action_{action name}
    function fah_order_export_bulk_action() {
        // if an array with order IDs is not presented, exit the function
        if (!isset($_REQUEST['post']) && !is_array($_REQUEST['post'])) return;
        $lastProcessedTime = (float)get_option('FLoraathome_bulk_order_execution');
        if (!$lastProcessedTime) add_option('FLoraathome_bulk_order_execution', microtime('return_float'));
        else {
            $currentTime = microtime('return_float');
            if (($currentTime - $lastProcessedTime) < 10) return;
            else update_option('FLoraathome_bulk_order_execution', microtime('return_float'));
        }
        $count = 0;
        foreach ($_REQUEST['post'] as $order_id) {
            $order = new WC_Order(sanitize_text_field($order_id));
            //
            $orderExportStatus = $order->get_meta('fah_orderExport');
            if ($orderExportStatus != 'Exported' && $orderExportStatus != 'processing') {
                if ($orderExportStatus) update_post_meta($order_id, 'fah_orderExport', 'processing');
                else add_post_meta($order_id, 'fah_orderExport', 'processing');
                try {
                    $fahexport = fah_webshop_order_export($order);
                    if (is_array($fahexport)) {
                        if ($fahexport[0]) {
                            if (get_post_meta($order_id, 'fah_orderExport')) update_post_meta($order_id, 'fah_orderExport', 'Exported');
                            else add_post_meta($order_id, 'fah_orderExport', 'Exported');
                            $count++;
                        } else {
                            if (get_post_meta($order_id, 'fah_orderExport')) update_post_meta($order_id, 'fah_orderExport', $fahexport[1]);
                            else add_post_meta($order_id, 'fah_orderExport', $fahexport[1]);
                        }
                    }
                }
                catch(\Exception $e) {
                    $orderExportStatus = $order->get_meta('fah_orderExport');
                    if ($orderExportStatus != 'Exported') update_post_meta($order_id, 'fah_orderExport', 'Error: Not exported Try again');
                }
            }
        }
        // of course using add_query_arg() is not required, you can build your URL inline
        $location = add_query_arg(array('post_type' => 'shop_order', 'fah_orderexport_bulk' => 1, 'changed' => $count, // number of changed orders
        'ids' => join(',', $_REQUEST['post']), 'post_status' => 'all'), 'edit.php');
        wp_redirect(admin_url($location));
        exit;
    }
?>