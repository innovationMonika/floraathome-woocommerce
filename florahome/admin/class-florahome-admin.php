<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.floraathome.nl/
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/admin
 */

class florahome_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $florahome    The ID of this plugin.
	 */
	private $florahome;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $florahome       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $florahome, $version ) {

		$this->florahome = $florahome;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		//wp_die($this->version);
		wp_enqueue_style( $this->florahome, plugin_dir_url( __FILE__ ) . 'css/florahome-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		global $post;
		

   		 //if ( $hook == 'post-new.php' || $hook == 'post.php' ) {
			
			wp_enqueue_script( $this->florahome, plugin_dir_url( __FILE__ ) . 'js/florahome-import.js', array( 'jquery' ), $this->version, false );

			/*if ( 'product' === $post->post_type ) {  
				wp_enqueue_script( $this->florahome, plugin_dir_url( __FILE__ ) . 'js/florahome-import.js', array( 'jquery' ), $this->version, false );
			}*/
			

		//}
		

	}
    
	/**
	 * Admin menu of the plugin.
	 *
	 * @since    1.0.0
	 */

	 function fah_add_admin_menu() {
	    
	    add_menu_page( 'Flora@home Woo Commerce plugin settings', 'Flora@home', 'manage_options', 'flora@home_woo_commerce_plugin_settings', 'fah_options_page' );
	    
	}
	

	/**
	 * Admin menu product options groupings for woocommerce product.
	 *
	 * @since    1.0.0
	 */


	function product_options_grouping() {
		exit;
		$args = [
				'id'            => 'text_about_grower',
                'label'         => sanitize_text_field( 'About Grower' ),
                'placeholder'   => 'Flora@home about grower ',
                'desc_tip'      => '',
                'description'   => '',


		];
		
		woocommerce_wp_textarea_input( $args );

		
	}
	

	/**
	 * Settings initilization
	 *
	 * @since    1.0.0
	 */


	function fah_settings_init() {
	    
		//register_setting( 'pluginPage', 'fah_settings' );
		register_setting( 'pluginPage_general', 'fah_settings' );
		register_setting( 'pluginPage_product', 'fah_settings' );
		register_setting( 'pluginPage_order', 'fah_settings' );
		
	    
	    add_settings_section(
	        'fah_pluginPage_section',
	        __( 'General settings', 'fah' ),
	        'fah_settings_section_callback',
	        'pluginPage_general'
	        );
	    
	    add_settings_field(
	        'fah_webshop_ref',
	        __( 'Webshop Reference name', 'fah' ),
	        'fah_webshop_ref_render',
	        'pluginPage_general',
	        'fah_pluginPage_section'
	        );
	    
	    add_settings_field(
	        'fah_text_api_url',
	        __( 'API URL', 'fah' ),
	        'fah_text_api_url_render',
	        'pluginPage_general',
	        'fah_pluginPage_section'
	        );
	    
	    add_settings_field(
	        'fah_text_api_token',
	        __( 'API token', 'fah' ),
	        'fah_text_api_token_render',
	        'pluginPage_general',
	        'fah_pluginPage_section'
	        );
	    
	    add_settings_field(
	        'fah_text_admin_email',
	        __( 'Admin email', 'fah' ),
	        'fah_text_admin_email_render',
	        'pluginPage_general',
	        'fah_pluginPage_section'
	        );
	    
	    add_settings_section(
	        'fah_pluginPage_products',
	        __( 'Products import settings', 'fah' ),
	        'fah_settings_products_section_callback',
	        'pluginPage_product'
	        );
	    add_settings_field(
	        'fah_select_import_schedule',
	        __( 'Import Schedule ', 'fah' ),
	        'fah_import_schedule_render',
	        'pluginPage_product',
	        'fah_pluginPage_products'
			);
		add_settings_field(
			'fah_check_update_attr',
			__( 'Update product Attributes', 'fah' ),
			'fah_check_update_attr_render',
			'pluginPage_product',
			'fah_pluginPage_products'
			);
	    add_settings_field(
	        'fah_check_publish',
	        __( 'Auto publish on webshop', 'fah' ),
	        'fah_check_publish_render',
	        'pluginPage_product',
	        'fah_pluginPage_products'
	        );
	    add_settings_field(
	        'fah_select_publish_price',
	        __( 'Auto calculate publish price ', 'fah' ),
	        'fah_publish_price_render',
	        'pluginPage_product',
	        'fah_pluginPage_products'
	        );
	    
	    //$fahOptions = get_option( 'fah_settings' );
	    //if ($fahOptions['fah_select_publish_price'] && !$fahOptions['fah_select_publish_price'] === 'disable')
    	    add_settings_field(
    	        'fah_text_publish_price_value',
    	        __( 'Auto calculate price', 'fah' ),
    	        'fah_text_publish_price_value_render',
    	        'pluginPage_product',
    	        'fah_pluginPage_products'
    	        );
    	    
    	    add_settings_section(
    	        'fah_pluginPage_orders',
    	        __( 'Order export settings', 'fah' ),
    	        'fah_settings_orders_section_callback',
    	        'pluginPage_order'
    	        );
    	    add_settings_field(
    	        'fah_select_export_schedule',
    	        __( 'Export Schedule ', 'fah' ),
    	        'fah_export_schedule_render',
    	        'pluginPage_order',
    	        'fah_pluginPage_orders'
    	        );
    	    add_settings_field(
    	        'fah_select_order_status',
    	        __( 'Export Schedule ', 'fah' ),
    	        'fah_order_status_render',
    	        'pluginPage_order',
    	        'fah_pluginPage_orders'
    	        );
    	    
			//fah_webshop_get_products();
			
	    
	}
	
	/**
	 * Cron export of the order
	 *
	 * @since    1.0.0
	 */

	public function cron_order_export() {
		
		$options = get_option( 'fah_settings' );
		$selectedStatus = $options['fah_select_order_status'];

		

		/*$query = new WC_Order_Query();
		$query->set('status', $selectedStatus);
		$query->set('fah_orderExport', 'Not Exported');
		$orders = $query->get_orders();*/

		$ordersE = get_posts(array(
            'post_type' => 'shop_order',
            'post_status' => $selectedStatus,
            'meta_query' => array(
                array(
                    'key' => 'fah_orderExport',
                    'value' => array('Exported','No Flora@home Products','No Products for export'),
                    'compare' => 'NOT IN'
                )

            ) 
        ));
        $ordersN = get_posts(array(
            'post_type' => 'shop_order',
            'post_status' => $selectedStatus,
            'meta_query' => array(
                array(
                    'key' => 'fah_orderExport',
                    'compare' => 'NOT EXISTS'
                )

            ) 
		));
		$ordersW = array();
        if(count($ordersE) > 0 && count($ordersN) > 0)
            $ordersW = array_merge($ordersE, $ordersN);
        else if (count($ordersE) > 0)
            $ordersW = $ordersE;
        else if (count($ordersN) > 0)
            $ordersW = $ordersN;
		
		$exportprocess = new florahome_process();
		$errors = [];

		if(count($ordersW) > 0) {
			foreach ($ordersW as $orderPost) {
				$order = new WC_Order( $orderPost->ID );
				if($order->get_meta('fah_orderExport') != 'Exported') {
               
					$fahexport = fah_webshop_order_export($order);
					if (!empty($fahexport)) {
						
							if(isset($fahexport[0])){
								if($fahexport[0])
									add_post_meta($order->get_id(), 'fah_orderExport', 'Exported');
								else 
									$errors[$order->get_id()] = $fahexport[1];
							
							}
						
						
					} 
				} 


			}

			if(count($errors) > 0) {
				$exportprocess->errors = $errors;
				$exportprocess->sendErrorMail();

			}


		}


		
		if (get_option( 'fah_select_export_run' )) {
			//update_option('fah_select_export_run',date('Y-m-d h:i:s'));
			update_option('fah_select_export_run',current_time('mysql',false));
		} else 
			//add_option('fah_select_export_run', date('Y-m-d h:i:s'),null,false);
			add_option('fah_select_export_run', current_time('mysql',false),null,false);


	}
	
	

	/**
	 * Cron Import of the product update
	 *
	 * @since    1.0.0
	 */



	public function cron_product_update() {
		
			$currentDate = date('Y-m-d');
			set_time_limit(120);
			$updateProds = fah_webshop_recent_products_date($currentDate);
			
			if (!$updateProds->result) {
				
				$updateProds->productErr = true;
				$updateProds->sendErrorMail();


			}

			if (get_option( 'fah_select_import_run' )) {
				
				//update_option('fah_select_import_run',date('Y-m-d h:i:s'));
				update_option('fah_select_import_run',current_time('mysql',false));

			} else 
				//add_option('fah_select_import_run', date('Y-m-d h:i:s'),null,false);
				add_option('fah_select_import_run', current_time('mysql',false),null,false);
			
		


	}
	
	public function cron_image_import () {

		$products_pending = get_posts(array(
            'post_type' => 'product',
            'meta_query' => array(
                array(
                    'key' => 'pending_images',
                    'compare' =>  'EXISTS'
                )

            ) 
		));
		
		//$productcount = 0;
		$productList = [];
		$calculateTime = true;
		$timeRemaining = 90;
		if (count($products_pending) > 0) {
			//set_time_limit(90); //If time limit does not work
			set_time_limit(0);
			foreach ($products_pending as $productitem) {
				/* if there is a restriction when executing the time 
				*
				$pendingImages = get_post_meta($productitem->ID, 'pending_images');
				$productcount = count(json_decode($pendingImages));
				if ($calculateTime) {
					$starttime = microtime(true);
					update_product_image($productitem);
					$timediff = microttime(true) - $starttime;
					$perItemtime = $timediff/$productcount;
					$calculateTime = false;
					$timeRemaining -= $timediff;
				} else {
					$estimatedtime = $perItemtime * $productcount ;


				}*/

				//Alternate if unlimited time works
				//error_log('In Product Image Download');
				update_product_image($productitem);


				


			}

			if (!get_option( 'fah_download_success_images' ))   
				add_option('fah_download_success_images', 'Flora@home: The images of the imported products are downloaded successfully. If',null,false); 



		} else {

			//unlink cron if no products found
			wp_clear_scheduled_hook('task_flora_image_import');

		}


	}
		
	
	
}
