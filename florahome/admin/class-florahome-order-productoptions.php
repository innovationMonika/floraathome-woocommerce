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
class florahome_Order_productoptions {
    public $characteristic;
    public $option;
    public function __construct() {
        $this->characteristic = ''; //MandOptionalatory
        $this->option = ''; //Optional

    }
}