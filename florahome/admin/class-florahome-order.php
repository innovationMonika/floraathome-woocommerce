<?php

/**
 *
 * @link       https://www.floraathome.nl/
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/admin
 */

/**
 *
 * @package    florahome
 * @subpackage florahome/admin
 * @author     Gaurav Solanki <gaurav@inshoring-pros.com>
 */
class florahome_Order {

    public $referenceWebshop;
    public $referenceCustomer;
    public $companyname;
    public $department ;
    public $firstname ; //Mandatory
    public $lastname ; //Mandatory
    public $street ; //Mandatory - For full address
    public $buildingName ;
    public $floor ;
    public $housenumber ;
    public $housenumberAddition ;
    public $postalcode ; //Mandatory
    public $city ; //Mandatory
    public $district ;
    public $region ;
    public $country ; //Mandatory - Two Digit
    public $doorcode ; 
    public $remark ;
    public $phone ;
    public $email ; //Mandatory
    public $orderlines;

    public function __construct() {

		
        $this->referenceWebshop = '';
        $this->referenceCustomer = '';
        $this->companyname = '';
        $this->department = '';
        $this->firstname = ''; //Mandatory
        $this->lastname = ''; //Mandatory
        $this->street = ''; //Mandatory - For full address
        $this->buildingName = '';
        $this->floor = '';
        $this->housenumber = '';
        $this->housenumberAddition = '';
        $this->postalcode = ''; //Mandatory
        $this->city = ''; //Mandatory
        $this->district = '';
        $this->region = '';
        $this->country = ''; //Mandatory - Two Digit
        $this->doorcode = ''; 
        $this->remark = '';
        $this->phone = '';
        $this->email = ''; //Mandatory
        $this->orderlines = array(); //Mandatory

    }
}