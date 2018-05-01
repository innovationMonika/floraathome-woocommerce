<?php

    /**
     *
     * @link       https://www.floraathome.nl/
     * @since      1.0.0
     *
     * @package    florahome
     * @subpackage florahome/admin/partials
     */

    function save_external_files( $post_id = 0 , $url = null ){

        $data = array();

        if( $url == null ){

            $url = $_POST['link'];	

        } 

        $validLink = checkValidLink( $url );

        //$validLink = true;



        if( $validLink == true ){



            $timeout = 300;

            $tmp = download_url( $url , $timeout );

            $file_array = array();

            if(function_exists('exif_imagetype')) {
                $fileextension = image_type_to_extension( exif_imagetype( $url ) );

            }                
            else {
                
                $imageT = getimagesize( $url );
			
			    $fileextension = image_type_to_extension( $imageT[2] );
            }
            
            $path = pathinfo( $tmp );

            if( ! isset( $path['extension'] ) ){

                $tmpnew = $tmp . '.tmp';
                $file_array['tmp_name'] = $tmpnew;				 
                
            } else {
                $file_array['tmp_name'] = $tmp;
            }	

            $name = pathinfo( $url, PATHINFO_FILENAME )  . $fileextension;
            $file_array['name'] = $name;
            // $file_array['type'] = mime_content_type( $file_array['tmp_name'] );		

            // If error storing temporarily, unlink

            if ( is_wp_error( $tmp ) ) {

                @unlink($file_array['tmp_name']);

                $file_array['tmp_name'] = '';

                $data['result'] = 'error';

                $data['message'] = $tmp->get_error_message();

                $data['file_size'] = '0 KB';

                $data['actions'] = '-';

                // If ajax call

                //if( $post_id != 0 ){

                return $data;

                
            }

            // do the validation and storage stuff			

            $id = media_handle_sideload( $file_array, $post_id , $desc = null );

            $local_url = wp_get_attachment_url( $id );

            if( $local_url != false ){

                $fullPath = $local_url;

            } else {

                $fullPath = '#';

            }

            // If error storing permanently, unlink

            if ( is_wp_error($id) ) {

                @unlink($file_array['tmp_name']);

            }

            $file = check_headers( $local_url );

            $data['result'] = 'success';

            $data['image_id'] = $id;

            $data['message'] = 'Uploaded Succesfully';

            $data['file_size'] = format_size( preg_replace( "/[^0-9]/" , "" , $file['size'] ) );

            $data['actions'] = '<a href="' . $fullPath . '" target="blank">View</a> | <a href="' . admin_url() . 'post.php?post=' . $id . '&action=edit' . '" target="blank">Edit</a>';

            

            // If ajax call

            //if( $post_id != 0 ){

                return $data;

            /*} else {

                echo json_encode( $data );	

            }*/



            



        } else {



            $data['result'] = 'error';

            $data['message'] = 'File not found';

            $data['file_size'] = '0 KB';

            $data['actions'] = '-';



            // If ajax call

            //if( $post_id != 0 ){

                return $data;

            /*} else {

                echo json_encode( $data );	

            }*/

            

            //die;



        }



    }



    /*

    ** Check Headers

    */



    function check_headers( $link ){

        $curl = curl_init();

        curl_setopt_array( $curl, array(

            CURLOPT_HEADER => true,

            CURLOPT_NOBODY => true,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_SSL_VERIFYPEER => false,

            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',

            CURLOPT_URL => $link ) );

        $file_headers = explode( "\n", curl_exec( $curl ) );
        $size = curl_getinfo( $curl , CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close( $curl );

        $file_headers['size'] = absint( $size );
        return $file_headers;



    }



    /*

    ** Check valid link

    */



    function checkValidLink( $link ){



        $file_headers = check_headers( $link );

        $headerStatus = trim(preg_replace('/\s\s+/', ' ', $file_headers[0] ));



        $allow_files = array( 'HTTP/1.1 200 OK' , 'HTTP/1.0 200 OK' );



        if( in_array( $headerStatus , $allow_files ) && !empty( $file_headers ) && $file_headers['size'] > 1 ) {

            return true;

        } else {

            return false;

        }		



    }

    /*
    ** Get the file size
    */

    function format_size($size) {

        $sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        if ($size == 0) { return('n/a'); } else {

        return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]); 

        }

    }


?>