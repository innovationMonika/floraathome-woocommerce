<?php

/**
 *
 * @link       https://www.floraathome.nl/
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/admin/partials
 */




function flora_admin_notice () {
    
    if ( isset( $_GET['flora-import'] ) ) {
        
        if (get_option( 'fah_full_import_success' )) {

            ?>
            <div class="notice notice-info is-dismissible">
                <p><?php _e(get_option( 'fah_full_import_success' ), 'default' ); ?></p>
            </div>
            <?php
            if (get_option( 'fah_import_success_images' )) {
                ?>
                <div class="notice notice-info is-dismissible">
                    <p><?php _e(get_option( 'fah_import_success_images' ), 'default' ); ?></p>
                </div>
                <?php
                delete_option('fah_import_success_images');
            }
            delete_option('fah_full_import_success');

        } else if (get_option( 'fah_full_import_error' )) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e(get_option( 'fah_full_import_error' ), 'default' ); ?></p>
            </div>
            <?php

        }
   
    }

    if (get_option( 'fah_download_success_images' )) {

        $products_pending_check = get_posts(array(
            'post_type' => 'product',
            'meta_query' => array(
                array(
                    'key' => 'pending_images',
                    'compare' =>  'EXISTS'
                )

            ) 
		));
		
		//$productcount = 0;
        if (count($products_pending_check) > 0)
        return;
        
        ?>
        <div class="notice notice-info is-dismissible">
            <p><?php _e(get_option( 'fah_download_success_images' ), 'default' ); ?></p>
        </div>
        <?php
        delete_option('fah_download_success_images');


    }
    

    
    if (get_option( 'fah_full_update_success' )) {
        ?>
        <div class="notice notice-info is-dismissible">
            <p><?php _e(get_option( 'fah_full_update_success' ), 'default' ); ?></p>
        </div>
        <?php
        delete_option('fah_full_update_success');


    }

}

add_action('admin_notices', 'flora_admin_notice');
