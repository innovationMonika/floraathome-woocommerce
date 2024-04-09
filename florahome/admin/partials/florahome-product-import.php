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


function fah_webshop_get_products() {
    $options = get_option('fah_settings');
    $apiURL = $options['fah_text_api_url'] ? $options['fah_text_api_url'] : 'https://api.floraathome.nl/v1';
    $apiURL = rtrim($apiURL, '/') . '/';
    $defaultAttributes = ['productcode', 'linnaeusname', 'description', 'promotionaltext'];
    if (!isset($options['fah_text_api_token'])) {
        $message = 'Flora@home Product Import Error: API Key not found. Please add API key in Flora@ home plugin settings';
        if (!get_option('fah_full_import_error')) add_option('fah_full_import_error', $message, "", false);
        return false;
    }
    //Implement throw of exception to handle errors
    $apitoken = $options['fah_text_api_token'];
    $path = 'products/get?apitoken=' . $apitoken . '&type=json';
    $fahfetch = wp_remote_get($apiURL . $path, ['timeout' => 30]);
    $fahResponse = json_decode(wp_remote_retrieve_body($fahfetch));
    if ($fahResponse->success == 1) {
        //WP Product Sync
        $productsList = $fahResponse->data;
        $importProducts = 0;
        $deletedProducts = 0;
        $totalProducts = count($productsList);
        $floraProductsList = []; //List to Match existing products
        $skippedProducts = 0;
        if (count($productsList) > 0) {
            foreach ($productsList as $floraProduct) {
                $floraProductsList[trim($floraProduct->productcode) ] = $floraProduct;
                if (isset($floraProduct->type)) if ($floraProduct->type === 'card') { // Ignore card type products

                }
                if (empty(wc_get_product_id_by_sku(trim($floraProduct->productcode)))) {
                    // Create New Product
                    $updateProduct = fah_webshop_create_update_product($options, $floraProduct, null, false);
                    $importProducts++;
                } else {
                    // sync product
                    fah_webshop_create_update_product($options, $floraProduct, null, true);
                    $skippedProducts++;
                }
            }
        }
        //Sync Existing Product
        $deletedProducts = fah_daily_product_sync($floraProductsList);
        $message = 'Flora@Home Product Import completed: Total: ' . $totalProducts . ' products, Imported: ' . $importProducts . ' , Updated: ' . $skippedProducts . ' , Deleted:  ' . $deletedProducts . ' ';
        if ($importProducts > 0) {
            if (!get_option('fah_import_success_images')) add_option('fah_import_success_images', 'Flora@home: The images of the imported products are getting downloaded in the background.', "", false);
            // Add Cron to download product images
            if ($time = wp_next_scheduled('task_flora_image_import')) wp_unschedule_event($time, 'task_flora_image_import');
            wp_schedule_event(time(), '5min', 'task_flora_image_import');
        }
        if (!get_option('fah_full_import_success')) add_option('fah_full_import_success', $message, "", false);
    } else {
        // Error in API response implement
        if (isset($fahResponse->error) && $fahResponse->error) $message = 'Flora@home Product Import Error: ' . $fahResponse->error;
        else $message = 'Flora@home Product Import Error: Incorrect settings, Please check API URL and key.' . json_encode($fahfetch);
        if (!get_option('fah_full_import_error')) add_option('fah_full_import_error', $message, "", false);
    }
}
function fah_webshop_create_update_product($options, $floraProduct, $floraWooProduct, $update) {
    if ($update) {
        //update
        $floraWooProductId = wc_get_product_id_by_sku($floraProduct->productcode);
        $floraWooProduct = wc_get_product($floraWooProductId);
        if ($floraWooProduct->get_status('view') === 'draft') $floraWooProduct->set_status('publish');
        if (isset($options['fah_text_outofstock_deleted_product_value']) && $options['fah_text_outofstock_deleted_product_value']) {
            $floraWooProduct->set_stock_status('instock');
        }
        //Todo Download Images if No Images present in Update
        if (isset($options['fah_check_update_attr']) && $options['fah_check_update_attr']) {
            $floraWooProduct = fah_product_update_price($floraProduct, $floraWooProduct, $update);
            $floraWooProduct->save();
            return true;
        }
    } else {
        //add
        $floraWooProduct = new WC_Product();
    }
    // Set True if download images during import itself.
    $downloadImages = false;
    $floraWooProduct->set_sku(trim($floraProduct->productcode));
    //$floraWooProduct->set_name($floraProduct->linnaeusname);//Use Dutch name Instead of Linnaeusname
    $floraWooProduct->set_name($floraProduct->dutchname);
    $floraWooProduct->set_description($floraProduct->description);
    $floraWooProduct->set_short_description($floraProduct->promotionaltext);
    $category = get_term_by('slug', $floraProduct->category, 'product_cat');
    if (!empty($category)) {
        $floraWooProduct->set_category_ids(array($category->term_id));
    } else {
        $cid = wp_insert_term($floraProduct->category, 'product_cat', array('description' => $floraProduct->category, 'slug' => $floraProduct->category));
        if ($cid && isset($cid->term_id)) $floraWooProduct->set_category_ids(array($cid->term_id));
    }
    $globalattributes = wc_get_attribute_taxonomies();
    $att_products = [];
    $position = 0;
    if (isset($floraProduct->aboutgrower)) {
        $floraAttribute = new WC_Product_Attribute();
        $floraAttribute->set_id('aboutgrower');
        $floraAttribute->set_name('aboutgrower');
        $floraAttribute->set_options(array($floraProduct->aboutgrower));
        $floraAttribute->set_visible(0);
        $floraAttribute->set_position($position);
        $floraAttribute->set_variation(0);
        $att_products[] = $floraAttribute;
    }
    if (isset($floraProduct->caretips)) {
        $floraAttribute = new WC_Product_Attribute();
        $floraAttribute->set_id('caretips');
        $floraAttribute->set_name('caretips');
        $floraAttribute->set_options(array($floraProduct->caretips));
        $floraAttribute->set_visible(0);
        $floraAttribute->set_position($position);
        $floraAttribute->set_variation(0);
        $att_products[] = $floraAttribute;
    }
    foreach ($globalattributes as $productAtt) {
        if ($productAtt->attribute_name === 'fah_caretips' || $productAtt->attribute_name === 'fah_aboutgrower') continue;
        $attName = substr($productAtt->attribute_name, (strpos($productAtt->attribute_name, '_') + 1));
        if (isset($floraProduct->$attName) && !empty($floraProduct->$attName)) {
            $floraAttribute = new WC_Product_Attribute();
            $floraAttribute->set_id(wc_attribute_taxonomy_id_by_name('pa_fah_' . $productAtt->attribute_name));
            $floraAttribute->set_name($attName);
            $floraAttribute->set_options(array($floraProduct->$attName));
            $floraAttribute->set_visible(0);
            $floraAttribute->set_position($position);
            $floraAttribute->set_variation(0);
            $position++;
            $att_products[] = $floraAttribute;
        }
    }

    $floraWooProduct->set_attributes($att_products);
    $floraWooProduct = fah_product_update_price($floraProduct, $floraWooProduct, $update);
    $imagejson = '';
    $imagejson = json_encode($floraProduct->images);
    if (!$update && $downloadImages) {
        $imageErrors = [];
        $imageIds = [];
        if (isset($floraProduct->images)) {
            if (count($floraProduct->images) > 0) {
                foreach ($floraProduct->images as $productImage) {
                    $upload = fah_save_external_files(0, $productImage);
                    if ($upload['result'] == 'success') {
                        $imageIds[] = $upload['image_id'];
                    }
                }
            }
        }
        if (count($imageIds) > 0) {
            $floraWooProduct->set_gallery_image_ids($imageIds);
            $floraWooProduct->set_image_id($imageIds[0]);
        }
    }
    $product_id = $floraWooProduct->save();
    if (!$downloadImages && !$update && !get_post_meta($product_id, 'pending_images')) add_post_meta($product_id, 'pending_images', $imagejson);
    if (!get_post_meta($product_id, '_flora_product')) add_post_meta($product_id, '_flora_product', true);
    return true;
}
function fah_webshop_recent_products() {
    $currentDate = date('Y-m-d');
    $updateProds = fah_webshop_recent_products_date($currentDate);
    echo esc_attr($updateProds->processMessage);
}
function fah_webshop_recent_products_date($daterecent) {
    $options = get_option('fah_settings');
    $fahprocess = new florahome_process();
    if (empty($daterecent)) {
        $fahprocess->result = false;
        $fahprocess->processMessage = 'Incorrect processing date';
        return $fahprocess;
    }
    $apiURL = $options['fah_text_api_url'] ? $options['fah_text_api_url'] : 'https://api.floraathome.nl/v1';
    $apiURL = rtrim($apiURL, '/') . '/';
    $defaultAttributes = ['productcode', 'linnaeusname', 'description', 'promotionaltext'];
    if (!isset($options['fah_text_api_token'])) {
        //Implement throw of exception to handle errors
        $fahprocess->result = false;
        $fahprocess->processMessage = 'API Token not set';
        return $fahprocess;
    }
    $apitoken = $options['fah_text_api_token'];
    $path = 'products/recent?apitoken=' . $apitoken . '&fromdt=' . $daterecent . '&type=json';
    $fahfetch = wp_remote_get($apiURL . $path, ['timeout' => 30]);
    $fahResponse = json_decode(wp_remote_retrieve_body($fahfetch));
    if (isset($fahResponse->success) && $fahResponse->success) {
        $updateList = isset($fahResponse->data) && isset($fahResponse->data->updated) ? $fahResponse->data->updated : [];
        $deletedList = isset($fahResponse->data) && isset($fahResponse->data->deleted) ? $fahResponse->data->deleted : [];
        $addedList = isset($fahResponse->data) && isset($fahResponse->data->added) ? $fahResponse->data->added : [];
        $updatedCount = is_array($updateList) ? count($updateList) : 0;
        $addedCount = is_array($addedList) ? count($addedList) : 0;
        $deletedCount = is_array($deletedList) ? count($deletedList) : 0;
        $actualAdded = 0;
        $actualUpdated = 0;
        $actualDeleted = 0;
        if (!empty($deletedList)) {
            foreach ($deletedList as $floraDelProduct) {
                if (!empty(wc_get_product_id_by_sku($floraDelProduct->productcode))) {
                    $floraWooProductId = wc_get_product_id_by_sku($floraDelProduct->productcode);
                    $floraWooDelProduct = wc_get_product($floraWooProductId);
                    if ($floraWooDelProduct->get_status('view') != 'draft' || !$floraWooDelProduct->is_in_stock()) $actualDeleted++;
                    if (isset($options['fah_text_outofstock_deleted_product_value']) && $options['fah_text_outofstock_deleted_product_value']) {
                        $floraWooDelProduct->set_stock_status('outofstock');
                    } else {
                        $floraWooDelProduct->set_status('draft');
                    }
                    $floraWooDelProduct->save();
                    //wp_trash_post($floraWooProductId); // Do not delete the products

                }
            }
        }
        if (!empty($updateList)) {
            foreach ($updateList as $floraUpdateProduct) {
                if (empty(wc_get_product_id_by_sku($floraUpdateProduct->productcode))) {
                    //Add as product is not found
                    fah_webshop_create_update_product($options, $floraUpdateProduct, null, false);
                    $actualUpdated++;
                } else {
                    //update
                    fah_webshop_create_update_product($options, $floraUpdateProduct, null, true);
                    $actualAdded++;
                }
            }
        }
        if (!empty($addedList)) {
            foreach ($addedList as $floraAddProduct) {
                if (empty(wc_get_product_id_by_sku($floraAddProduct->productcode))) {
                    fah_webshop_create_update_product($options, $floraAddProduct, null, false);
                    $actualAdded++;
                } else {
                    fah_webshop_create_update_product($options, $floraAddProduct, null, true);
                    $actualUpdated++;
                }
            }
        }
        $message = 'Flora@Home Product update completed: Total: Successfully Added: ' . $actualAdded . ' , Updated: ' . $actualUpdated . ' , Deleted: ' . $actualDeleted . ' products';
        if ($actualAdded > 0) {
            if (!get_option('fah_update_success_images')) add_option('fah_import_success_images', 'Flora@home: The images of the added products are getting downloaded in the background.', "", false);
            // Add Cron to download product images
            if ($time = wp_next_scheduled('task_flora_image_import')) wp_unschedule_event($time, 'task_flora_image_import');
            wp_schedule_event(time(), '5min', 'task_flora_image_import');
        }
        if ($actualAdded > 0 || $actualUpdated > 0 || $actualDeleted > 0) {
            if (!get_option('fah_full_update_success')) add_option('fah_full_update_success', $message, "", false);
        }
        $fahprocess->result = true;
        $fahprocess->processMessage = 'Successfully added ' . $addedCount . ' products, updated ' . $updatedCount . ' products, deleted ' . $deletedCount . ' products';
        return $fahprocess;
    } else {
        //Log Error
        $fahprocess->result = false;
        if (isset($fahResponse->error) && $fahResponse->error) $fahprocess->processMessage = $fahResponse->error;
        else $fahprocess->processMessage = 'Error in processing product updates';
        return $fahprocess;
    }
}
/*
 * Input - Flora API Product Object
 * Input - Flora Woo Commerce Product Object
*/
function fah_product_update_price($floraProduct, $floraWooProduct, $update) {
    $options = get_option('fah_settings');
    if ($options['fah_select_publish_price'] != 'disable') {
        $productPrice = (float)$floraProduct->purchaseprice;
        if (!empty($options['fah_text_publish_price_value'])) {
            if ($options['fah_select_publish_price'] == 'fixed') $productPrice = $productPrice + $options['fah_text_publish_price_value'];
            else {
                $percentInc = $options['fah_text_publish_price_value'];
                $productPrice = round(($productPrice + $productPrice * $percentInc / 100), 2);
            }
            $floraWooProduct->set_price(sprintf("%.2f", $productPrice));
            $floraWooProduct->set_regular_price(sprintf("%.2f", $productPrice));
            if (!empty($options['fah_check_publish'])) {
                $floraWooProduct->set_catalog_visibility('visible');
            } else {
                if (!$update) $floraWooProduct->set_catalog_visibility('hidden');
            }
        }
    } else {
        if (!$update) $floraWooProduct->set_catalog_visibility('hidden');
    }
    return $floraWooProduct;
}
function fah_show_progress_import($total, $imported, $skipped) {
    if ($total > 0) {
?><script type="text/javascript">
var total = <?php echo $total ? esc_attr($total) : 0 ?>;
var imported = <?php echo $imported ? esc_attr($imported) : 0 ?>;
var skipped = <?php echo $skipped ? esc_attr($skipped) : 0 ?>;

update_progress(total, imported, skipped);
</script>


<?php
    }
}
function fah_update_product_image($product) {
    $jsonImage = get_post_meta($product->ID, 'pending_images');

    if (!$jsonImage) {
        delete_post_meta($product->ID, 'pending_images');
        return;
    }
    if (empty($jsonImage)) {
        delete_post_meta($product->ID, 'pending_images');
        return;
    }
    if (is_array($jsonImage)) {
        if (empty($jsonImage[0])) {
            delete_post_meta($product->ID, 'pending_images');
            return;
        }
        $productImages = json_decode($jsonImage[0]);
    } else $productImages = json_decode($jsonImage);
    if (count($productImages) > 0) {
        $imageIds = [];
        $imageErrors = [];
        foreach ($productImages as $productImage) {
            try {
                //Check if image already exists
                $imageUrlData = parse_url($productImage);
                if (is_array($imageUrlData) && isset($imageUrlData['path'])) {
                    $fileData = pathinfo($imageUrlData['path']);
                    if (is_array($fileData)) {
                        if (isset($fileData['extension']) && isset($fileData['filename'])) {
                            if (in_array($fileData['extension'], ['jpg', 'jpeg', 'png', 'bmp', 'gif', 'webp'])) {
                                $query = new WP_Query(array('post_type' => 'attachment', 'title' => $fileData['filename'], 'post_status' => 'all', 'posts_per_page' => 1, 'no_found_rows' => true, 'ignore_sticky_posts' => true, 'update_post_term_cache' => false, 'update_post_meta_cache' => false, 'orderby' => 'post_date ID', 'order' => 'ASC',));
                                if (!empty($query->post)) {
                                    $existingImage = $query->post;
                                } else {
                                    $existingImage = null;
                                }
                                if (empty($existingImage)) {
                                    $upload = fah_save_external_files(0, $productImage);
                                    if ($upload['result'] == 'success') {
                                        $imageIds[] = $upload['image_id'];
                                    } elseif ($upload['result'] == 'error') {
                                        $imageErrors[] = $productImage;
                                    } else {
                                        $imageErrors[] = $productImage;
                                    }
                                } else {
                                    $imageIds[] = $existingImage->ID;
                                }
                            } else {
                                $imageErrors[] = $productImage;
                                continue;
                            }
                        } else {
                            $imageErrors[] = $productImage;
                            continue;
                        }
                    } else {
                        $imageErrors[] = $productImage;
                        continue;
                    }
                } else {
                    $imageErrors[] = $productImage;
                    continue;
                }
            }
            catch(\Exception $e) {
                $imageErrors[] = 'Error while Importing Image : ' . $e->getMessage();
            }
        }
        $floraWooProduct = new WC_Product($product->ID);
        if (count($imageIds) > 0) {
            //Check if Image already associated with product
            $floraWooProduct->set_gallery_image_ids($imageIds);
            $floraWooProduct->set_image_id($imageIds[0]);
            if (get_post_meta($product->ID, 'pending_images')) {
                $floraWooProduct->save();
                delete_post_meta($product->ID, 'pending_images');
                if (!get_option('fah_download_success_images')) add_option('fah_download_success_images', 'Flora@home: The images of the imported products are downloaded successfully.', "", false);
            } else delete_post_meta($product->ID, 'pending_images');

        }
        if (count($imageErrors) > 0) {
            delete_post_meta($product->ID, 'pending_images');
        }
    }
}
function fah_daily_product_sync($floraProducts) {
    $deletedProducts = 0;

    $products_woo_flora = get_posts(array('post_type' => 'product', 'meta_query' => array(array('key' => '_flora_product', 'compare' => 'EXISTS')), 'posts_per_page' => - 1));
    $options = get_option('fah_settings');
    if ($products_woo_flora && count($products_woo_flora) > 0) {
        foreach ($products_woo_flora as $wooProductPost) {
            $product = wc_get_product($wooProductPost);
            if (!$product) continue;
            $productSku = $product->get_sku();
            if (!$productSku) continue;
            if (!isset($floraProducts[$productSku])) {
                if ($product->get_status('view') != 'draft') {
                    $deletedProducts++;
                    if (isset($options['fah_text_outofstock_deleted_product_value']) && $options['fah_text_outofstock_deleted_product_value']) {
                        $product->set_stock_status('outofstock');
                    } else {
                        $product->set_status('draft');
                    }
                    $product->save();
                }
            }
        }
    }
    return $deletedProducts;
}
add_action('wp_ajax_flora_ajaximport', 'fah_webshop_get_products');
add_action('wp_ajax_flora_ajaxupdate', 'fah_webshop_recent_products')
?>