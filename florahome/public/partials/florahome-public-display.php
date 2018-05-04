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
function add_card_text_field() {
    global $wp_query;
    global $product;
    global $post;

    

    
    if($product) {
        //$catgs = $product->wc_get_product_category_list ();
        $catgs = wc_get_product_category_list($product->get_id());
        if( $catgs) {
            $catlist = explode(',',$catgs);
           
            if(count($catlist) > 0 ) {
                $catlistlower = array_map('strtolower',  $catlist);
                $catlisttrim = array_map('trim',  $catlistlower);
                $catlistnotags =  array_map('strip_tags',  $catlistlower);
               
                if (in_array('kaartje',$catlistnotags, false)) {

                    $message = '';
                    if(isset($_REQUEST['card-text-message']))
                        $message = $_REQUEST['card-text-message'];

                    
                    echo '<table class="variations" cellspacing="0">
                    <tbody>
                        <tr>
                        <td class="label"><label for="card">Message on card</label></td>
                        <td class="value">
                             <input type="text" name="card-text-message" style="width:100%;" value="'. $message .'" />  
                                                 
                        </td>
                    </tr>                               
                    </tbody>
                </table>';
                    
                

                } 
                   
                    

            }
            
                
        }
       
       
       

    }
        
    
  
}
add_action( 'woocommerce_before_add_to_cart_button', 'add_card_text_field' );


/*
* Validation for custom field
*/

function card_message_validation() { 
    /*if ( empty( $_REQUEST['card-text-message'] ) ) {
        wc_add_notice( __( 'Please enter message for card&hellip;', 'woocommerce' ), 'error' );
        return false;
    }*/
    if ( !empty( $_REQUEST['card-text-message'] ) ) {
        if(strlen($_REQUEST['card-text-message']) > 1000) {
        wc_add_notice( __( 'Card message should be less than 1000 characters&hellip;', 'woocommerce' ), 'error' );
        return false;
    }
    }
    return true;
}
add_action( 'woocommerce_add_to_cart_validation', 'card_message_validation', 10, 3 );


/*
* add card message in cart field
*/

function save_card_message_field( $cart_item_data, $product_id ) {
    if( isset( $_REQUEST['card-text-message'] ) ) {
        $cart_item_data[ 'card_message_text' ] = $_REQUEST['card-text-message'];
        /* below statement make sure every add to cart action as unique line item */
        $cart_item_data['unique_key'] = md5( microtime().rand() );
    }
    return $cart_item_data;
}
add_action( 'woocommerce_add_cart_item_data', 'save_card_message_field', 10, 2 );

/*
* add card message on checkouot
*/

function render_meta_on_cart_and_checkout( $cart_data, $cart_item = null ) {
    $custom_items = array();
    /* Woo 2.4.2 updates */
    if( !empty( $cart_data ) ) {
        $custom_items = $cart_data;
    }
    if( isset( $cart_item['card_message_text'] ) ) {
        $custom_items[] = array( "name" => 'Card Message', "value" => $cart_item['card_message_text'] );
    }
    return $custom_items;
}
add_filter( 'woocommerce_get_item_data', 'render_meta_on_cart_and_checkout', 10, 2 );



/*
* add card message in order meta
*/
function card_message_order_meta_handler( $item, $cart_item_key, $values, $order ) {
    
    
    if( isset( $values['card_message_text'] ) ) {
        $item->update_meta_data( 'card_message_text', $values['card_message_text'] );
    }
  
}
add_action( 'woocommerce_checkout_create_order_line_item', 'card_message_order_meta_handler', 1, 4 );

?>
