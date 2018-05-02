<?php

/**
 *
 * @link       https://www.floraathome.nl/
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/admin/partials
 */

function fah_webshop_order_export($order) {
    
    $result = false;
    $orderProcess = '';

    $options = get_option( 'fah_settings' );
    
    $apiURL = $options[fah_text_api_url] ? $options[fah_text_api_url] : 'https://api.floraathome.nl/v1';
    $apiURL = rtrim($apiURL,'/').'/';
    $defaultAttributes = ['productcode','linnaeusname','description','promotionaltext'];
    
    if(!isset($options[fah_text_api_token])) {
         //Implement throw of exception to handle errors
         return array($result, 'API token not set');


    }
       
    
    $orderprep = fah_webshop_order_prepare($order);

    if(is_array($orderprep)) {
        if(!$orderprep[0])
            return array($result, $orderprep[1]);
        
        $orderExportData = $orderprep[1];
    } else
        return array($result,'Error in export');


    $apitoken = $options[fah_text_api_token];
    

    $path = 'orders/create?apitoken='.$apitoken.'&type=json';
    //print_r($apiURL.$path);
    if ($orderExportData) {
        $fahpost = wp_remote_post(  $apiURL.$path, array(
            'method' => 'POST',
            'timeout' => 45,
            'headers' => array("Content-type" => "application/x-www-form-urlencoded"),
            'body' => array('body' => $orderExportData)
            
            )
        );
       
        if ( is_wp_error( $fahpost ) ) {
            $error_message = $fahpost->get_error_message();
            $orderProcess = $error_message;
            return array($result, $orderProcess);
            
         } else {
            $fahbody = json_decode(wp_remote_retrieve_body($fahpost));
    
             if ($fahbody->success) {
               
        
                     add_post_meta($order->get_id(), 'fah_order_Export_code', $fahbody->ordercode);
                     $result = true;
                     return array($result, $orderProcess);
                     
        
                 } 
         }

    } else {
        //Error in Export
        $orderProcess = 'Error in order Export';
        return array($result, $orderProcess);

    }
    
    
   
    
}

function fah_webshop_order_prepare($order) {
    
    $orderExportObj = new florahome_Order();

    $prepare = false;
    $orderprepare = '';

    $options = get_option( 'fah_settings' );

    $orderExportObj->referenceWebshop =  $options[fah_webshop_ref];
    $orderExportObj->referenceCustomer = $order->get_order_number();
    
    $orderExportItems = [];

    if(count($order->get_items('line_item')) >0 ) {
        
        foreach ($order->get_items('line_item') as $orderItem) {
           

            if ($orderItem['product_id']) {
                
                $orderProduct = new WC_Product($orderItem['product_id']);
                
                if(!$orderProduct->get_meta('_flora_product'))
                    continue;

                if( $orderProduct->get_sku()) {
                    
                    $fahOrderLineItem = new florahome_Order_item();
                    $fahOrderLineItem->productcode = $orderProduct->get_sku();
                    $fahOrderLineItem->quantity = $orderItem['qty'];
                    

                    $orderExportItems[] = $fahOrderLineItem;

                } else {
                    
                    $orderprepare = 'Ordered product does not have SKU';
                    return array($prepare, $orderprepare);

                }
                


            } else {
                
                $orderprepare = 'Error getting product Line item';
                return array($prepare, $orderprepare);
                

            }
           

            


        }
       
        if (count($orderExportItems) > 0 )
            $orderExportObj->orderlines =  $orderExportItems;
        else {
            $orderprepare = 'No Valid Items in Order: '.$order->get_order_number();
            add_post_meta($order->get_id(), 'fah_orderExport', 'No Flora@home Products');
            return array($prepare, $orderprepare);

        }
        

    } else {
        $orderprepare = 'No Items in Order'. $order->get_order_number();
        add_post_meta($order->get_id(), 'fah_orderExport', 'No Products for export');
        return array($prepare, $orderprepare);

    }

    //Mandatory Fields
    if ($order->get_shipping_first_name()) {
        $orderExportObj->firstname = $order->get_shipping_first_name();
        
    } else {
        $orderprepare = 'No First Name in order';
        return array($prepare, $orderprepare);
        //return 'false;'
    }
    if ($order->get_shipping_last_name()) {
        $orderExportObj->lastname = $order->get_shipping_last_name();

    } else {
        $orderprepare = 'No Last Name in order';
        return array($prepare, $orderprepare);
    }
    
    if ($order->get_shipping_postcode()) {
        $orderExportObj->postalcode = $order->get_shipping_postcode();

    } else {
        $orderprepare = 'No Post code in order';
        return array($prepare, $orderprepare);

    }
    
    if ($order->get_shipping_address_1() || $order->get_shipping_address_2()) {
        $orderExportObj->street = $order->get_shipping_address_1().' '.$order->get_shipping_address_2();
        //$orderExportObj->street = $order->get_shipping_address_1() ;


    } else {
        $orderprepare = 'No Address in order';
        return array($prepare, $orderprepare);

    }

    if ($order->get_billing_email()) {
        $orderExportObj->email = $order->get_billing_email();

    } else {
        $orderprepare = 'No Email in order';
        return array($prepare, $orderprepare);

    }

    if ($order->get_shipping_city()) {
        $orderExportObj->city = $order->get_shipping_city();

    } else {
        $orderprepare = 'No city in order';
        return array($prepare, $orderprepare);

    }

    if ($order->get_shipping_country()) {
        $orderExportObj->country = $order->get_shipping_country();

    } else {
        $orderprepare = 'No Country  in order';
        return array($prepare, $orderprepare);

    }
    

    // Optional Fields

    if ($order->get_billing_phone()) {
        $orderExportObj->phone = $order->get_billing_phone();

    } 

    
    if ($order->get_customer_note()) {
       
        $orderExportObj->remark = $order->get_customer_note();

    } 

    if ($order->get_shipping_company()) {
        $orderExportObj->companyname = $order->get_shipping_company();

    } 
    
    
    

    $orderExportJson = json_encode($orderExportObj);
    
    $prepare = true;

    return array($prepare, $orderExportJson);
    //return $orderExportJson;




}





?>