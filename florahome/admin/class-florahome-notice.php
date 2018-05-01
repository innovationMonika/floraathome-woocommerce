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
 *
 * @package    florahome
 * @subpackage florahome/admin
 * @author     Gaurav Solanki <gaurav@inshoring-pros.com>
 */
class florahome_notices {

    private $noticeMessage;
    private $error;
    

    public function __construct( $message, $isError) {

        $this->noticeMessage = $message;
        $this->error = $isError;

       
        if($isError)
             add_action( 'admin_notices', $this, array( $this, 'render_flora_error' ) );
        else 
            add_action( 'admin_notices', array( $this, 'render_flora' ));
            
           
     
    }


    function render_flora() {
        //printf( '<div class="updated">%s</div>', $this->_message );
        
        ?>
        <div class="notice notice-success  is-dismissible">
           
        <p><?php  _e( ' '.$this->noticeMessage, 'default' ); ?></p>
    </div><?php
    }

    function render_flora_error() {
        //printf( '<div class="updated">%s</div>', $this->_message );
        
        ?>
        <div class="notice notice-error is-dismissible">
        
        <p><?php  _e( 'Please install '.$this->noticeMessage.'++php CURL to use this module', 'default' ); ?></p>
    </div><?php
    }

}