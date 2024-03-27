<?php
/**
 *
 * @link       http://florahome.com
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/public/partials
 */
/*
 *
 *
 * Extra field for card types
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function fah_add_card_text_field() {
    global $product;
    if ($product) {
        //$catgs = $product->wc_get_product_category_list ();
        $catgs = wc_get_product_category_list($product->get_id());
        if ($catgs) {
            $catlist = explode(',', $catgs);
            if (count($catlist) > 0) {
                $catlistlower = array_map('strtolower', $catlist);
                $catlisttrim = array_map('trim', $catlistlower);
                $catlistnotags = array_map('strip_tags', $catlistlower);
                if (in_array('kaartje', $catlistnotags, false)) {
                    $message = '';
                    if (isset($_REQUEST['card-text-message'])) $message = sanitize_text_field($_REQUEST['card-text-message']);
                    echo '<table class="variations" cellspacing="0">
                        <tbody>
                            <tr>
                                <td class="label"><label for="card"> ' . esc_html( _e("Message for the card", 'florahome') ) . '</label></td>
                                <td class="value">
                                    <input type="text" name="card-text-message" style="width:100%;" value="' . esc_attr( $message ) . '" />
                                </td>
                            </tr>
                        </tbody>
                    </table>';

                }
            }
        }
    }
}
add_action('woocommerce_before_add_to_cart_button', 'fah_add_card_text_field');

/*
 * Validation for custom field
*/
function fah_fh_card_message_validation() {
    /*if ( empty( $_REQUEST['card-text-message'] ) ) {
        wc_add_notice( __( 'Please enter message for card&hellip;', 'woocommerce' ), 'error' );
        return false;
    }*/
    if (!empty($_REQUEST['card-text-message'])) {
        if (strlen($_REQUEST['card-text-message']) > 1000) {
            wc_add_notice(__('Card message should be less than 1000 characters&hellip;', 'woocommerce'), 'error');
            return false;
        }
    }
    return true;
}
add_action('woocommerce_add_to_cart_validation', 'fah_fh_card_message_validation', 10, 3);

/*
 * add card message in cart field
*/
function fah_save_card_message_field($cart_item_data, $product_id) {
    if (isset($_REQUEST['card-text-message'])) {
        $cart_item_data['card_message_text'] = sanitize_text_field($_REQUEST['card-text-message']);
        /* below statement make sure every add to cart action as unique line item */
        $cart_item_data['unique_key'] = md5(microtime() . rand());
    }
    return $cart_item_data;
}
add_action('woocommerce_add_cart_item_data', 'fah_save_card_message_field', 10, 2);

/*
 * add card message on checkouot
*/
function fah_render_meta_on_cart_and_checkout($cart_data, $cart_item = null) {
    $custom_items = array();
    /* Woo 2.4.2 updates */
    if (!empty($cart_data)) {
        $custom_items = $cart_data;
    }
    if (isset($cart_item['card_message_text'])) {
        $custom_items[] = array("name" => __('Message for the card', 'florahome'), "value" => $cart_item['card_message_text']);
    }
    return $custom_items;
}
add_filter('woocommerce_get_item_data', 'fah_render_meta_on_cart_and_checkout', 10, 2);

/*
 * add card message in order meta
*/
function fah_card_message_order_meta_handler($item, $cart_item_key, $values, $order) {
    //error_log(print_r($order,true));
    if (isset($values['card_message_text'])) {
        $item->update_meta_data('card_message_text', $values['card_message_text']);
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'fah_card_message_order_meta_handler', 1, 4);
?>