<?php

/**
 *
 * @link       https://www.floraathome.nl/
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/admin/partials
 */
if (!function_exists('fah_write_log')) {
            function fah_write_log ( $log )  {
               if ( true === WP_DEBUG ) {
                    if ( is_array( $log ) || is_object( $log ) ) {
                        error_log( print_r( $log, true ) );
                    } else {
                    error_log( $log );
                    }

            }

        }

    }