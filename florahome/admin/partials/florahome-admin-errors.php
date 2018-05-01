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
    
}

add_action('admin_notices', 'flora_admin_notice');
