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

function fah_admin_notice() {
    if (isset($_GET['flora-import'])) {
        if (get_option('fah_full_import_success')) {
?>
<div class="notice notice-info is-dismissible">
 <p>
  <?php
 printf(
	esc_html__( '%s', 'florahome' ),
	esc_html(get_option('fah_full_import_success'))
  );
 ?>
 </p>
</div>
<?php
            if (get_option('fah_import_success_images')) {
?>
<div class="notice notice-info is-dismissible">
 <p><?php
        printf(
        esc_html__( '%s', 'florahome' ),
        esc_html(get_option('fah_import_success_images'))
        ); ?>
 </p>
</div>
<?php
                delete_option('fah_import_success_images');
            }
            delete_option('fah_full_import_success');
        } else if (get_option('fah_full_import_error')) {
?>
<div class="notice notice-error is-dismissible">
 <p><?php
  printf(
        esc_html__( '%s', 'florahome' ),
        esc_html(get_option('fah_full_import_error'))
        );
  ?></p>
</div>
<?php
            delete_option('fah_full_import_error');
        }
    }
    if (get_option('fah_download_success_images')) {
        $products_pending_check = get_posts(array('post_type' => 'product', 'meta_query' => array(array('key' => 'pending_images', 'compare' => 'EXISTS'))));
        //$productcount = 0;
        if (count($products_pending_check) > 0) return;
?>
<div class="notice notice-info is-dismissible">
 <p><?php
 printf(
        esc_html__( '%s', 'florahome' ),
        esc_html(get_option('fah_download_success_images'))
        );?>
 </p>
</div>
<?php
        delete_option('fah_download_success_images');
    }
    if (get_option('fah_full_update_success')) {
?>
<div class="notice notice-info is-dismissible">
 <p><?php
 printf(
        esc_html__( '%s', 'florahome' ),
        esc_html(get_option('fah_full_update_success'))
        );
 ?></p>
</div>
<?php
        delete_option('fah_full_update_success');
    }
}
add_action('admin_notices', 'fah_admin_notice');