<?php

/**
 *
 * @link       https://www.floraathome.nl/
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/admin
 */

/**
 *
 *
 * @package    florahome
 * @subpackage florahome/admin
 * @author     Gaurav Solanki <gaurav@inshoring-pros.com>
 */
class florahome_productfields {

    private $textfield_id;
 
    public function __construct($field_id) {
        $this->textfield_id = $field_id;
    }
 
    public function init() {
 
            add_action(
                'woocommerce_product_options_grouping',
                array( $this, 'product_options_grouping' )
            );
    }
 
    public function product_options_grouping() {
 
            $description = sanitize_text_field( '' );
            $placeholder = sanitize_text_field( 'Information from Flora@home' );
 
            $args = array(
                'id'            => $this->textfield_id,
                'label'         => sanitize_text_field( 'Product Teaser' ),
                'placeholder'   => 'Tease your product with a short description',
                'desc_tip'      => true,
                'description'   => $description,
            );
            woocommerce_wp_text_input( $args );
    }
}

