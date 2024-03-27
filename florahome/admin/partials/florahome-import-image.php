<?php
/**
 *
 * @link       https://www.floraathome.nl/
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/admin/partials
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly
require_once (ABSPATH . "wp-admin" . '/includes/file.php');
require_once (ABSPATH . "wp-admin" . '/includes/media.php');
require_once (ABSPATH . "wp-admin" . '/includes/image.php');
function fah_save_external_files($post_id = 0, $url = null) {
    $data = array();
    if ($url == null) {
        $sanitized_url = isset($_POST['link']) ? sanitize_text_field($_POST['link']) : '';
        $url = esc_url($sanitized_url);
    }
    $validLink = fah_checkValidLink($url);
    //$validLink = true;
    if ($validLink == true) {
        $timeout = 300;
        $tmp = download_url($url, $timeout);
        //error_log(print_r($tmp,true));
        $emptyFile = false;
        if (is_string($tmp)) {
            $file_array = array();
            if (!filesize($tmp)) $emptyFile = true;
            if (function_exists('exif_imagetype')) {
                $fileextension = image_type_to_extension(exif_imagetype($url));
            } else {
                $imageT = getimagesize($url);
                $fileextension = image_type_to_extension($imageT[2]);
            }
            $path = pathinfo($tmp);
            if (!isset($path['extension'])) {
                $tmpnew = $tmp . '.tmp';
                $file_array['tmp_name'] = $tmpnew;
            } else {
                $file_array['tmp_name'] = $tmp;
            }
            $name = pathinfo($url, PATHINFO_FILENAME) . $fileextension;
            $file_array['name'] = $name;
            // $file_array['type'] = mime_content_type( $file_array['tmp_name'] );

        }
        // If error storing temporarily, unlink
        if (is_wp_error($tmp) || $emptyFile) {
            if (isset($file_array['tmp_name'])) {
                @unlink($file_array['tmp_name']);
                $file_array['tmp_name'] = '';
            }
            $data['result'] = 'error';
            $data['message'] = $tmp->get_error_message();
            $data['file_size'] = '0 KB';
            $data['actions'] = '-';
            return $data;
        }
        // do the validation and storage stuff
        if (!isset($file_array)) {
            $data['result'] = 'error';
            $data['message'] = 'File not found or empty';
            $data['file_size'] = '0 KB';
            $data['actions'] = '-';
            return $data;
        }
        $id = media_handle_sideload($file_array, $post_id, $desc = null);
        $local_url = wp_get_attachment_url($id);
        if ($local_url != false) {
            $fullPath = $local_url;
        } else {
            $fullPath = '#';
        }
        // If error storing permanently, unlink
        if (is_wp_error($id)) {
            @unlink($file_array['tmp_name']);
        }
        $file = fah_check_headers($local_url);
        $data['result'] = 'success';
        $data['image_id'] = $id;
        $data['message'] = 'Uploaded Successfully';
        $data['file_size'] = fah_format_size(preg_replace("/[^0-9]/", "", $file['size']));
        $data['actions'] = '<a href="' . $fullPath . '" target="blank">View</a> | <a href="' . admin_url() . 'post.php?post=' . $id . '&action=edit' . '" target="blank">Edit</a>';
        return $data;
    } else {
        $data['result'] = 'error';
        $data['message'] = 'File not found';
        $data['file_size'] = '0 KB';
        $data['actions'] = '-';
        return $data;
    }
}
/*
 ** Check Headers
*/
function fah_check_headers($link) {
    $response = wp_remote_head($link);
    // Check for errors
    if (is_wp_error($response)) {
        return $response->get_error_message();
    }
    // Get headers
    $headers = wp_remote_retrieve_headers($response);
    // Get content length
    $size = wp_remote_retrieve_header($response, 'content-length');
    // Construct result array
    $file_headers = array();
    foreach ($headers as $header => $value) {
        $file_headers[] = "$header: $value";
    }
    $file_headers['size'] = absint($size);
    return $file_headers;
}
/*
 ** Check valid link
*/
function fah_checkValidLink($link) {
    $file_headers = fah_check_headers($link);
    $headerStatus = trim(preg_replace('/\s\s+/', ' ', $file_headers[0]));
    //$allow_files = array('HTTP/1.1 200 OK', 'HTTP/1.0 200 OK');
    preg_match('/^HTTP.+\s(\d\d\d)/', $headerStatus, $headerCode);
    if (is_array($headerCode)) {
        if (isset($headerCode[1])) {
            if (!$headerCode[1] == '200') {
                return false;
            }
        }
    }
    //if( in_array( $headerStatus , $allow_files ) && !empty( $file_headers ) && $file_headers['size'] > 1 ) {
    if (!empty($file_headers) && $file_headers['size'] > 1) {
        return true;
    } else {
        return false;
    }
}
/*
 ** Get the file size
*/
function fah_format_size($size) {
    $sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    if ($size == 0) {
        return ('n/a');
    } else {
        return (round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]);
    }
}
?>