<?php

/**
 *
 * @link       https://www.floraathome.nl/
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/admin/partials
 */

function fah_webshop_get_products() {
    
    
    $options = get_option( 'fah_settings' );
    
    $apiURL = $options['fah_text_api_url'] ? $options['fah_text_api_url'] : 'https://api.floraathome.nl/v1';
    $apiURL = rtrim($apiURL,'/').'/';
    $defaultAttributes = ['productcode','linnaeusname','description','promotionaltext'];
    
    if(!isset($options['fah_text_api_token'])) {
        $message = 'Flora@home Product Import Error: API Key not found. Please add API key in Flora@ home plugin settings';
        if (!get_option( 'fah_full_import_error' )) 
            add_option('fah_full_import_error', $message,null,false);
        
        
        return false;



    }
        //Implement throw of exception to handle errors
        
    
        
    $apitoken = $options[fah_text_api_token];
    
    $path = 'products/get?apitoken='.$apitoken.'&type=json';
    //print_r($apiURL.$path);
    $fahfetch = wp_remote_get( $apiURL.$path, ['timeout' => 30]);
    
    
    $fahResponse = json_decode(wp_remote_retrieve_body($fahfetch));
    
    if ($fahResponse->success) {
        //WP Product Sync 
        $productsList = $fahResponse->data;
        $importProducts = 0;
        $totalProducts = count($productsList);
       
        $skippedProducts = 0;
        //show_progress_import($totalProducts,$importProducts,$skippedProducts);

        if(count($productsList) > 0){
            foreach($productsList as $floraProduct) {
                //$floraProduct = $productsList[0];
                
               
                if(isset($floraProduct->type)) 
                if ($floraProduct->type === 'card' ) {// Ignore card type products
                    //continue;
                }
            
                if (empty(wc_get_product_id_by_sku(trim($floraProduct->productcode)))) {
                // Create New Product
                    $updateProduct = fah_webshop_create_update_product($options,$floraProduct, null, false );
                    $importProducts++;
            
                
                 } else {
                    // sync product
                    //echo 'PRODUCT FOUND'.$floraProduct->productcode;
                    $skippedProducts++;
                    
                } 


            }


        }
        
        $message = 'Flora@Home Product Import completed: Total: '.$totalProducts. ' products, Imported: '. $importProducts. ' , Skipped: '.$skippedProducts.' ';
        if ($importProducts > 0) {
            if (!get_option( 'fah_import_success_images' )) 
                add_option('fah_import_success_images', 'Flora@home: The images of the imported products are getting downloaded in the background.',null,false); 

            // Add Cron to download product images
            error_log('ADD SCHEDULE');
            if ( $time = wp_next_scheduled( 'task_flora_image_import' ) )
       					 wp_unschedule_event( $time, 'task_flora_image_import' );
            wp_schedule_event(time(), 'hourly', 'task_flora_image_import');
                
        }
        
        if (!get_option( 'fah_full_import_success' )) 
            add_option('fah_full_import_success', $message,null,false);
            
        
        
        
        
        
        
        
        
    }
        
    else {
        // Error in API response implement
        //print_r($fahResponse->error);
        if ($fahResponse->error)
            $message = 'Flora@home Product Import Error: '.$fahResponse->error;
        else 
             $message = 'Flora@home Product Import Error: Incorrect settings, Please check API URL and key.';
        if (!get_option( 'fah_full_import_error' )) 
            add_option('fah_full_import_error', $message,null,false);
     
    }
    
    
    
    
}


function fah_webshop_create_update_product($options,$floraProduct, $floraWooProduct, $update) {
    if($update){
        //update
        $floraWooProductId = wc_get_product_id_by_sku($floraProduct->productcode);
        $floraWooProduct = wc_get_product($floraWooProductId);
        if ($floraWooProduct->get_status('view') === 'draft')
            $floraWooProduct->set_status('publish');
           
        if($options['fah_check_update_attr']){
            
            $floraWooProduct = update_price($floraProduct, $floraWooProduct);
        
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
    $category = get_term_by('slug',$floraProduct->category, 'product_cat' );
    
    if(!empty($category)) {
        
        $floraWooProduct->set_category_ids(array($category->term_id));
    
    } else {
        
        $cid = wp_insert_term(
            $floraProduct->category,
            'product_cat',
            array(
                'description'=> $floraProduct->category,
                'slug'=> $floraProduct->category
            )
        );
       
        $floraWooProduct->set_category_ids(array($cid->term_id));
    } 
   
    
    
    $globalattributes = wc_get_attribute_taxonomies();

    $att_products = [];
    $position = 0;
    
    foreach ($globalattributes as $productAtt) {
        
        $attName = $productAtt->attribute_name;
        
        //print_r($productAtt);
        //die;
        
        if(isset($floraProduct->$attName)) {
        
           $floraAttribute = new WC_Product_Attribute();
           //$floraAttribute->set_id()
           $floraAttribute->set_id(wc_attribute_taxonomy_id_by_name('pa_'.$productAtt->attribute_name));
           $floraAttribute->set_name('pa_'.$productAtt->attribute_name);
           $floraAttribute->set_options(array($floraProduct->$attName));
           $floraAttribute->set_visible(0);
           $floraAttribute->set_position($position);
           $floraAttribute->set_variation(0);
           $position++;
           
           //$floraWooProduct->set_meta_data()
           $att_products[] = $floraAttribute;
           


        }
      


    }
    //print_r($att_products);
    //die;
    $floraWooProduct->set_attributes($att_products);
    
   
    $floraWooProduct = update_price($floraProduct, $floraWooProduct);
        
    $imagejson = '';
    $imagejson = json_encode($floraProduct->images);
    

    if(!$update && $downloadImages) {
        $imageErrors = [];
        $imageIds = [];
        if(isset($floraProduct->images)) {
            if (count($floraProduct->images)>0) {
                foreach ($floraProduct->images as $productImage) {
                    $upload = save_external_files(0,$productImage);
                    if ($upload['result'] == 'success') {
                        $imageIds[] = $upload['image_id'];

                    }


                }



            }



        }
        if(count($imageIds) > 0) {
            $floraWooProduct->set_gallery_image_ids($imageIds);
            $floraWooProduct->set_image_id($imageIds[0]);
        }
    }
    
   


    $product_id = $floraWooProduct->save();
    if(!$downloadImages && !$update) 
        add_post_meta($product_id, 'pending_images', $imagejson);
    
    add_post_meta($product_id, '_flora_product', true);
   
    return true;
    


}


function fah_webshop_recent_products() {

    $currentDate = date('Y-m-d');

    $updateProds = fah_webshop_recent_products_date($currentDate);
    
    echo $updateProds->processMessage;
    
        


}

function fah_webshop_recent_products_date($daterecent) {
    $options = get_option( 'fah_settings' );
    $fahprocess = new florahome_process();


    if(empty($daterecent)) {
        // return false
        $fahprocess->result = false;
        $fahprocess->processMessage = 'Incorrect processing date';
         return $fahprocess;

    }
        
    
    

    $apiURL = $options['fah_text_api_url'] ? $options['fah_text_api_url'] : 'https://api.floraathome.nl/v1';
    $apiURL = rtrim($apiURL,'/').'/';
    $defaultAttributes = ['productcode','linnaeusname','description','promotionaltext'];
    
    if(!isset($options['fah_text_api_token'])) {
        //Implement throw of exception to handle errors
        //return false;
        $fahprocess->result = false;
        $fahprocess->processMessage = 'API Token not set';
        return $fahprocess;
    
    }
    
   
    $apitoken = $options['fah_text_api_token'];
    
    $path = 'products/recent?apitoken='.$apitoken.'&fromdt='.$daterecent.'&type=json';
    //print_r($apiURL.$path);
    $fahfetch = wp_remote_get( $apiURL.$path, ['timeout' => 30]);
    

    $fahResponse = json_decode(wp_remote_retrieve_body($fahfetch));
    if ($fahResponse->success) {
        
        $updateList = $fahResponse->data->updated;
        $deletedList = $fahResponse->data->deleted;
        $addedList = $fahResponse->data->added;
       
        $updatedCount = is_array($updateList) ? count($updateList) : 0 ;
        $addedCount =  is_array($addedList) ? count($addedList) : 0 ;
        $deletedCount = is_array($deletedList) ? count($deletedList) : 0 ;

        $actualAdded = 0;
        $actualUpdated = 0;
        $actualDeleted = 0;


        if(!empty($deletedList)) {
            
            foreach ($deletedList as $floraDelProduct) {
              
                if (!empty(wc_get_product_id_by_sku($floraDelProduct->productcode))) {
                    $floraWooProductId = wc_get_product_id_by_sku($floraDelProduct->productcode);
                    $floraWooDelProduct = wc_get_product($floraWooProductId);
                    if ($floraWooDelProduct->get_status('view') != 'draft')
                        $actualDeleted++;
                    $floraWooDelProduct->set_status('draft');
                    $floraWooDelProduct->save();
                    

                    //wp_trash_post($floraWooProductId); // Do not delete the products

                   
                    

                }
            
            }


        }

        if(!empty($updateList)) {
            
            foreach ($updateList as $floraUpdateProduct) {
                if (empty(wc_get_product_id_by_sku($floraUpdateProduct->productcode))) {
                    //Add as product is not found
                    fah_webshop_create_update_product($options,$floraUpdateProduct, null, false );
                    $actualUpdated++;


                } else {
                    //update
                    fah_webshop_create_update_product($options,$floraUpdateProduct, null, true );
                    $actualAdded++;
                
                }

            }
        }

        if(!empty($addedList)) {
            
            foreach ($addedList as $floraAddProduct) {
                if (empty(wc_get_product_id_by_sku($floraAddProduct->productcode))) {
                    fah_webshop_create_update_product($options,$floraAddProduct, null, false );
                    $actualAdded++;



                } else {
                    fah_webshop_create_update_product($options,$floraAddProduct, null, true );
                    $actualUpdated++;

                }

            }
        }
        
        
       
       

        $message = 'Flora@Home Product update completed: Total: Successfully Added: '.$actualAdded.' , Updated: '.$actualUpdated.' , Deleted: '.$actualDeleted.' products';
        if ($actualAdded > 0 ) {
            if (!get_option( 'fah_update_success_images' )) 
                add_option('fah_import_success_images', 'Flora@home: The images of the added products are getting downloaded in the background.',null,false); 

            // Add Cron to download product images

            if ( $time = wp_next_scheduled( 'task_flora_image_import' ) )
       					 wp_unschedule_event( $time, 'task_flora_image_import' );
            wp_schedule_event(time(), 'hourly', 'task_flora_image_import');
                
        }
        if($actualAdded > 0 || $actualUpdated > 0 || $actualDeleted > 0 ) {
            if (!get_option( 'fah_full_update_success' ))         
                add_option('fah_full_update_success', $message,null,false);
        }
        
        $fahprocess->result = true;
        $fahprocess->processMessage = 'Successfully added '.$addedCount.' products, updated '.$updatedCount.' products, deleted '.$deletedCount.' products';
        return $fahprocess;


    } else {
        //Log Error
        //return false;
        $fahprocess->result = false;
        if ($fahResponse->error)
            $fahprocess->processMessage = $fahResponse->error;
        else 
            $fahprocess->processMessage = 'Error in processing product updates';
        return $fahprocess;
    }

}

/*
* Input - Flora API Product Object
* Input - Flora Woo Commerce Product Object
 */

function update_price($floraProduct, $floraWooProduct) {

    $options = get_option( 'fah_settings' );

    if ($options['fah_select_publish_price'] !=  'disable') { 
        
            $productPrice = (float) $floraProduct->purchaseprice;
            if(!empty($options['fah_text_publish_price_value'])) {
                if($options['fah_select_publish_price'] == 'fixed')
                    
                    $productPrice = $productPrice + $options['fah_text_publish_price_value'];
                else {
                    $percentInc =   $options['fah_text_publish_price_value'];   
                    $productPrice = round(($productPrice + $productPrice*$percentInc/100),2);
                }
                    
                $floraWooProduct->set_price(sprintf("%.2f", $productPrice));
                $floraWooProduct->set_regular_price(sprintf("%.2f", $productPrice));

                

                if($options['fah_check_publish']) {

                    $floraWooProduct->set_catalog_visibility('visible');

                }
                else {
                    $floraWooProduct->set_catalog_visibility('hidden');
                }
            }
        } else {
            $floraWooProduct->set_catalog_visibility('hidden');
        }

        return $floraWooProduct;


}

function show_progress_import ($total, $imported , $skipped) {
    if ($total > 0) {
        ?><script type="text/javascript">

            var total = <?php echo $total ? $total : 0 ?>;
            var imported = <?php echo $imported ? $imported : 0 ?>;
            var skipped = <?php echo $skipped ? $skipped : 0 ?>;

            update_progress(total, imported, skipped);
        
        </script>
        
        
        <?php



    }



}

function update_product_image ($product) {
    
    $jsonImage = get_post_meta($product->ID, 'pending_images');
    error_log(print_r($jsonImage,true));
    

    if(!$jsonImage)
        return;
    
    
    delete_post_meta($product->ID, 'pending_images');
    add_post_meta($product->ID, 'download_flora_images', $jsonImage);
    
    
    if (is_array($jsonImage))
        $productImages = json_decode($jsonImage[0]);
    else 
        $productImages = json_decode($jsonImage);
    if(count($productImages) > 0 ) {
        $imageIds = [];
        foreach($productImages as $productImage) {
            error_log(print_r( $productImage, true));
            $upload = save_external_files(0,$productImage);
            if ($upload['result'] == 'success') {
                $imageIds[] = $upload['image_id'];



            }


        }
        $floraWooProduct = new WC_Product($product->ID);
        if (count($imageIds) > 0) {
            $floraWooProduct->set_gallery_image_ids($imageIds);
            $floraWooProduct->set_image_id($imageIds[0]);
            $floraWooProduct->save();
            $imageIds = json_encode($imageIds);

            add_post_meta($product->ID, 'set_flora_images', $imageIds);
            
        }
    }
}

    




//}
add_action('wp_ajax_flora_ajaximport',  'fah_webshop_get_products');
add_action('wp_ajax_flora_ajaxupdate',  'fah_webshop_recent_products')



?>