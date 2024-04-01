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

    public function __construct($message, $isError) {
        $this->noticeMessage = $message;
        $this->error = $isError;
        if ($isError) add_action('admin_notices', $this, array($this, 'fah_render_flora_error'));
        else add_action('admin_notices', array($this, 'fah_render_flora'));
    }
    function fah_render_flora() {
        //printf( '<div class="updated">%s</div>', $this->_message );
        ?>
<div class="notice notice-success  is-dismissible">

 <p><?php
  printf(
        esc_html__( '%s', 'florahome' ),
        esc_html($this->noticeMessage)
        );
 ?></p>
</div><?php
    }

    function fah_render_flora_error() {
        //printf( '<div class="updated">%s</div>', $this->_message );
        ?>
<div class="notice notice-error is-dismissible">

 <p><?php
  printf(
        esc_html__( 'Please install %s ++php CURL to use this module', 'florahome' ),
        esc_html($this->noticeMessage)
        );
 ?></p>
</div><?php
    }
}