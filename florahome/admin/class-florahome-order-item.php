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

 class florahome_Order_item {

    public $productcode;
    public $quantity;
    public $text;
	public $sales_price_item;
	public $sales_price_total;
	public $sales_price_currency;
    

    public function __construct() {

		
        $this->productcode = ''; //Mandatory
        $this->quantity = ''; //Mandatory
        $this->text = ''; //Optional
		$this->sales_price_item = '';
		$this->sales_price_total = '';
		$this->sales_price_currency = '';
       

    }
}