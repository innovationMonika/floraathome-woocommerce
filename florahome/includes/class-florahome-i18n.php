<?php

/**
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/includes
 */

class florahome_i18n {


	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'florahome',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
