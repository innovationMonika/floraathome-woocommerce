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
    $options = get_option('fah_settings');
    $apiURL = $options['fah_text_api_url'] ? $options['fah_text_api_url'] : 'https://api.floraathome.nl/v1';
    $apiURL = rtrim($apiURL, '/') . '/';
    if (!isset($options['fah_text_api_token'])) {
        //Implement throw of exception to handle errors
        return array($result, 'API token not set');
    }
    $orderprep = fah_webshop_order_prepare($order);
    if (is_array($orderprep)) {
        if (!$orderprep[0]) return array($result, $orderprep[1]);
        $orderExportData = $orderprep[1];
    } else return array($result, 'Error in export');
    $apitoken = $options['fah_text_api_token'];
    $path = 'orders/create?apitoken=' . $apitoken . '&type=json';
    if ($orderExportData) {
        $fahpost = wp_remote_post($apiURL . $path, array('method' => 'POST', 'timeout' => 45, 'headers' => array("Content-type" => "application/x-www-form-urlencoded"), 'body' => array('body' => $orderExportData)));
        if (is_wp_error($fahpost)) {
            $error_message = $fahpost->get_error_message();
            $orderProcess = $error_message;
            return array($result, $orderProcess);
        } else {
            $fahbody = json_decode(wp_remote_retrieve_body($fahpost));
            if ($fahbody->success) {
                add_post_meta($order->get_id(), 'fah_order_Export_code', $fahbody->ordercode);
                $result = true;
                return array($result, $orderProcess);
            } elseif ($fahbody->error) {
                foreach ($fahbody->error as $errorvalue) $orderProcess = 'Error: ' . $errorvalue;
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
    $options = get_option('fah_settings');
    $orderExportObj->referenceWebshop = $options['fah_webshop_ref'];
    $orderExportObj->referenceCustomer = $order->get_order_number();
    $orderExportItems = [];
    if (count($order->get_items('line_item')) > 0) {
        foreach ($order->get_items('line_item') as $orderItem) {
            if ($orderItem['product_id']) {
                $orderProduct = new WC_Product($orderItem['product_id']);
                if (!$orderProduct->get_meta('_flora_product')) continue;
                if ($orderProduct->get_sku()) {
                    $fahOrderLineItem = new florahome_Order_item();
                    $fahOrderLineItem->productcode = $orderProduct->get_sku();
                    $fahOrderLineItem->quantity = $orderItem['qty'];
                    $fahOrderLineItem->sales_price_item = round(wc_get_price_excluding_tax($orderProduct), 2);
                    $fahOrderLineItem->sales_price_total = round($orderItem->get_total(), 2);
                    $fahOrderLineItem->sales_price_currency = get_woocommerce_currency();
                    $itemmeta = $orderItem->get_meta_data();
                    if (is_array($itemmeta)) {
                        foreach ($itemmeta as $metaobject) {
                            $itemmetadata = $metaobject->get_data();
                            if ($itemmetadata['key'] === 'card_message_text') $fahOrderLineItem->text = $itemmetadata['value'];
                        }
                    }
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
        if (count($orderExportItems) > 0) $orderExportObj->orderlines = $orderExportItems;
        else {
            $orderprepare = 'No Valid Items in Order: ' . $order->get_order_number();
            add_post_meta($order->get_id(), 'fah_orderExport', 'No Flora@home Products');
            return array($prepare, $orderprepare);
        }
    } else {
        $orderprepare = 'No Items in Order' . $order->get_order_number();
        add_post_meta($order->get_id(), 'fah_orderExport', 'No Products for export');
        return array($prepare, $orderprepare);
    }
    //Mandatory Fields
    if ($order->get_shipping_first_name()) {
        $orderExportObj->firstname = $order->get_shipping_first_name();
    } else {
        $orderprepare = 'No First Name in order';
        return array($prepare, $orderprepare);

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
        $orderExportObj->street = $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2();

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
    if ($order->get_shipping_state()) {
        $orderExportObj->region = $order->get_shipping_state();
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
    $postNLDeliveryFlag = false;
    // PostCode Fields
    if (class_exists('WooCommerce_PostNL') && $postNLDeliveryFlag) {
        $shippingOptions = $order->get_meta('_postnl_delivery_options');
        $wooCommPostNlpackages = WooCommerce_PostNL()->export->get_package_type_for_order($order);
        $shippingCountry = $order->get_shipping_country();
        $shippingCode = '';
        $optionCode = '';
        if ($shippingOptions) {
            $homeAddressOnly = '';
            $sinatureOption = '';
            if (isset($shippingOptions['only_recipient']) && ($shippingOptions['only_recipient'] != 0))
            $optionCode = '3385';
            if (isset($shippingOptions['signature']) && ($shippingOptions['signature'] != 0))
            $optionCode = '3189';
            if (isset($shippingOptions['signature']) && ($shippingOptions['signature'])) $optionCode = '3089';
            if (class_exists('WooCommerce_PostNL')) $wooCommPostNlpackages = WooCommerce_PostNL()->export->get_package_type_for_order($order);
            else $wooCommPostNlpackages = false;
            if ($wooCommPostNlpackages) {
                if ($wooCommPostNlpackages === 3) $optionCode = '';
                if ($wooCommPostNlpackages === 2) {
                    if (strtolower($shippingCountry) === 'nl') $optionCode = '2928';
                    else $optionCode = fah_get_outside_nl_shipping($shippingCountry);
                    $orderExportObj->productcodedelivery = $optionCode;
                }
                if ($wooCommPostNlpackages === 1) {
                    $shipmentTypeCode = 0;
                    if (isset($shippingOptions['time'])) {
                        foreach ($shippingOptions['time'] as $optionDetails) {
                            if (isset($optionDetails['type'])) {
                                $start_time = isset($optionDetails['start']) ? $optionDetails['start'] : false;
                                $end_time = isset($optionDetails['end']) ? $optionDetails['end'] : false;
                                $shipmentTypeCode = $optionDetails['type'];
                                if ($optionDetails['type'] == 5) break;
                            }
                        }
                        if (!empty($optionDetails) && $shipmentTypeCode) {
                            $postNlCode = fah_getpostnlMappingCodes($optionDetails['type'], $start_time, $end_time, $shippingCountry);
                            if (!empty($optionCode) && !empty($postNlCode['optionCode'])) {
                                $postNlCode['optionCode'] = $optionCode;
                            }
                            if ($postNlCode['deliveryDate']) $orderExportObj->deliverydate = isset($shippingOptions['date']) ? $shippingOptions['date'] : '';
                            $orderExportObj->productcodedelivery = $postNlCode['optionCode'];
                            if (!empty($postNlCode['optionchar']) && !empty($postNlCode['option'])) {
                                $optionObj = new florahome_Order_productoptions();
                                $optionObj->characteristic = $postNlCode['optionchar'];
                                $optionObj->option = $postNlCode['option'];
                                $orderExportObj->productoptions = [$optionObj];
                            }
                        }
                    }
                }
            }
        }
    }
    $orderExportJson = json_encode($orderExportObj);
    $prepare = true;
    return array($prepare, $orderExportJson);
}
function fah_getpostnlMappingCodes($optionType, $start_time, $end_time, $countryCode) {
    $postnlshippingCode = ['optionCode' => '3085', 'optionchar' => '', 'option' => '', 'deliveryDate' => false];
    switch ($optionType) {
        case 1:
            if (strtolower($countryCode) !== 'nl') $postnlshippingCode['optionCode'] = fah_get_outside_nl_shipping($countryCode);
            else {
                $postnlshippingCode['optionchar'] = '118';
                $postnlshippingCode['option'] = '006';
                $postnlshippingCode['deliveryDate'] = true;
            }
            break;
        case 2:
            if (strtolower($countryCode) !== 'nl') $postnlshippingCode['optionCode'] = fah_get_outside_nl_shipping($countryCode);
            else $postnlshippingCode['deliveryDate'] = true;
            break;
        case 3:
            if (strtolower($countryCode) === 'nl') {
                $postnlshippingCode['optionchar'] = '118';
                $postnlshippingCode['option'] = '008';
                $postnlshippingCode['deliveryDate'] = true;
            } else $postnlshippingCode['optionCode'] = fah_get_outside_nl_shipping($countryCode);
            break;
        case 4:
            if (strtolower($countryCode) === 'nl') {
                $postnlshippingCode['optionCode'] = '3533';
            } else
            $postnlshippingCode['optionCode'] = '';
            break;
        case 5:
            if (strtolower($countryCode) === 'nl') {
                $postnlshippingCode['optionCode'] = '3533';;
            } else
            $postnlshippingCode['optionCode'] = '';
            break;
        default:
            $postnlshippingCode['optionCode'] = '';
            break;
        }
        return $postnlshippingCode;
    }
    function fah_ecs_eu_country_check($country_code) {
        $euro_countries = array('AT', 'BE', 'BG', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GB', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'MC', 'AL', 'AD', 'BA', 'IC', 'FO', 'GI', 'GL', 'GG', 'JE', 'HR', 'LI', 'MK', 'MD', 'ME', 'UA', 'SM', 'RS', 'VA', 'BY');
        return in_array($country_code, $euro_countries);
    }
    function fah_get_outside_nl_shipping($countryCode) {
        if (fah_ecs_eu_country_check(strtoupper($countryCode))) return '4944';
        else return '4945';
    }
?>