<?php
/**
 *
 *
 * @link       https://www.floraathome.nl/
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/public
 */
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    florahome
 * @subpackage florahome/public
 */
class florahome_Public {
    private $florahome;
    private $version;
    public function __construct($florahome, $version) {
        $this->florahome = $florahome;
        $this->version = $version;
    }
    public function fah_enqueue_styles() {

    }
    public function fah_enqueue_scripts() {

    }
}