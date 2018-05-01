<?php

/**
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/includes
 */
define ( 'WCCF_REQUIRED_WC_VERSION', '3.0' );     

class florahome_Activator {

	
	public static function activate() {

		if(!function_exists('exif_imagetype') && !function_exists('image_type_to_extension') && !function_exists('getimagesize')) {
			// Deactivate the plugin
			deactivate_plugins(plugin_basename(__FILE__));
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
			exit( _e('Please install php exif extension to activate this plugin'));


		}


		global $wp_version ;
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' ) ; 
		$woocommer_data = get_plugin_data(WP_PLUGIN_DIR .'/woocommerce/woocommerce.php', false, false);
		if (version_compare ($woocommer_data['Version'] , WCCF_REQUIRED_WC_VERSION, '<')){
			deactivate_plugins(plugin_basename(__FILE__));
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
			exit( _e('Please install WooCommerce Version '. WCCF_REQUIRED_WC_VERSION. ' to activate Flora @ home plugin'));
		}

		if(!function_exists('curl_exec'))
		{
			// Deactivate the plugin
			deactivate_plugins(plugin_basename(__FILE__));
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
			exit( _e('Please install php CURL extension to activate this plugin'));
			
			
		
		}

		


		$floraProductAttributes = [
			'potsize',
			'height',
			'purchaseprice',
			'linnaeusname', 
			'numberofprovider',
			'specifications', 
			'grower', 
			'aboutgrower',
			'branches' 
			
		];

		foreach ($floraProductAttributes as $floraAttribute) {
			
			$insert = self::process_add_attribute(array(
				'attribute_name' => $floraAttribute , 
				'attribute_label' => $floraAttribute, 
				'attribute_type' => 'text', 
				'attribute_orderby' => 'menu_order', 
				'attribute_public' => false)
			);
		
			if (is_wp_error($insert)) { 
		
				//Log Error
				
		
			}
		}

		




	}



	private  static function process_add_attribute($attribute)
	{
		
		global $wpdb;
		//      check_admin_referer( 'woocommerce-add-new_attribute' );

		if (empty($attribute['attribute_type'])) { $attribute['attribute_type'] = 'text';}
		if (empty($attribute['attribute_orderby'])) { $attribute['attribute_orderby'] = 'menu_order';}
		if (empty($attribute['attribute_public'])) { $attribute['attribute_public'] = 0;}

		if ( empty( $attribute['attribute_name'] ) || empty( $attribute['attribute_label'] ) ) {
				return new WP_Error( 'error', __( 'Please, provide an attribute name and slug.', 'woocommerce' ) );
		} elseif ( ( $valid_attribute_name = self::valid_attribute_name( $attribute['attribute_name'] ) ) && is_wp_error( $valid_attribute_name ) ) {
				return $valid_attribute_name;
		} elseif ( taxonomy_exists( wc_attribute_taxonomy_name( $attribute['attribute_name'] ) ) ) {
				return new WP_Error( 'error', sprintf( __( 'Slug "%s" is already in use. Change it, please.', 'woocommerce' ), sanitize_title( $attribute['attribute_name'] ) ) );
		}

		$wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute );

		do_action( 'woocommerce_attribute_added', $wpdb->insert_id, $attribute );

		flush_rewrite_rules();
		delete_transient( 'wc_attribute_taxonomies' );

		return true;
	}

	private static function valid_attribute_name( $attribute_name ) {
		if ( strlen( $attribute_name ) >= 28 ) {
				return new WP_Error( 'error', sprintf( __( 'Slug "%s" is too long (28 characters max). Shorten it, please.', 'woocommerce' ), sanitize_title( $attribute_name ) ) );
		} elseif ( wc_check_if_attribute_name_is_reserved( $attribute_name ) ) {
				return new WP_Error( 'error', sprintf( __( 'Slug "%s" is not allowed because it is a reserved term. Change it, please.', 'woocommerce' ), sanitize_title( $attribute_name ) ) );
		}

		return true;
	}

	

	

}
