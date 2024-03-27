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
class florahome_process {
    public $result;
    public $processMessage;
    public $errors;
    public $orderErr;
    public $productErr;
    public function __construct() {
        $this->result = false;
        $this->processMessage = '';
        $this->errors = []; //Mandatory
        $this->orderErr = false;
        $this->productErr = false;
    }
    public function sendErrorMail() {
        //Implement Error Email
        $options = get_option('fah_settings');
        $headers = array('Content-Type: text/html; charset=UTF-8');
        if ($options['fah_text_admin_email']) {
            if ($this->orderErr) $subject = 'Flora at home Order export processing error';
            elseif ($this->productErr) $subject = 'Flora at home product import processing error';
            else $subject = 'Flora at home processing error';
            $to = sanitize_email($options['fah_text_admin_email']);
            if (count($this->errors) > 0) {
                $body = 'Error in exporting orders';
                foreach ($this->errors as $id => $errorValue) {
                    $body.= 'Order Id: ' . $id . ' : ' . $errorValue;
                    $body.= ' <br>';
                }
                wp_mail($to, $subject, $body, $headers);
            } else {
                $body = $this->processMessage;
                wp_mail($to, $subject, $body, $headers);
            }
        } else { // No Email in admin settings
            fah_write_log('Error sending Email: Flora@Home: No Email defined to send email ');
        }
    }
}