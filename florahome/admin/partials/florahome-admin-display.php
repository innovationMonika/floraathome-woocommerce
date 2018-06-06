<?php

/**
 *
 *
 * @link       https://www.floraathome.nl/
 * @since      1.0.0
 *
 * @package    florahome
 * @subpackage florahome/admin/partials
 */

function fah_webshop_ref_render() {
	
    
    $options = get_option( 'fah_settings' );
    ?>
	<input type='text' class='flora-field' name='fah_settings[fah_webshop_ref]' value='<?php echo $options['fah_webshop_ref']; ?>'>
	<?php

}


function fah_text_api_url_render() { 

	$options = get_option( 'fah_settings' );
	?>
	<input type='text' class='flora-field' name='fah_settings[fah_text_api_url]' value='<?php echo $options['fah_text_api_url']; ?>'>
	<div class="helper">Default: https://api.floraathome.nl/v1/</div>
	
	<?php

}



function fah_text_api_token_render() { 

	$options = get_option( 'fah_settings' );
	?>
	<input type='text' class='flora-field' name='fah_settings[fah_text_api_token]' value='<?php echo $options['fah_text_api_token']; ?>'>
	<?php

}


function fah_text_admin_email_render() { 

	$options = get_option( 'fah_settings' );
	?>
	<input type='email' class='flora-field' name='fah_settings[fah_text_admin_email]' value='<?php echo $options['fah_text_admin_email']; ?>'>
	<?php

}



function fah_settings_section_callback() { 

	//echo __( 'www.floraathome.nl', 'fah' );
	?> <!--<hr>--> 
	<?php

}

function fah_settings_products_section_callback() {
    
    //echo __( 'Settings for importing products', 'fah' );
    ?> <!--<hr>--> 
	<?php
}

function fah_settings_orders_section_callback() {
    ?> <div class="flora-header">	<?php
    //echo __( 'Settings for exporting orders to Flora@home', 'fah' );
    ?> <!--<hr>--> 
	</div>
	<?php
}

function fah_import_schedule_render() {
    
    $options = get_option( 'fah_settings' );
    if ( !isset( $options['fah_select_import_schedule'] ) ) {
        $options['fah_select_import_schedule'] = 'disable';
    } else {
		if ($options['fah_select_import_schedule'] === 'disable' ) 
					wp_clear_scheduled_hook('task_flora_product_update');
		else  {
			if (wp_get_schedule('task_flora_product_update') !== $options['fah_select_import_schedule'] ) {
				if ( $time = wp_next_scheduled( 'task_flora_product_update' ) )
       					 wp_unschedule_event( $time, 'task_flora_product_update' );
				
				wp_schedule_event(time(), $options['fah_select_import_schedule'], 'task_flora_product_update');
			}
		}


	}
	
	$importlastrun = get_option( 'fah_select_import_run' );
    ?>
	<select class='flora-field' name='fah_settings[fah_select_import_schedule]'>
		<option value='disable' <?php if( $options['fah_select_import_schedule'] === 'disable'  ){ echo 'selected'; } ?>>Disable</option>
		<option value='5min' <?php if( $options['fah_select_import_schedule'] === '5min'  ){ echo 'selected'; } ?>>Every 5 Minutes</option>
		<option value='hourly' <?php if( $options['fah_select_import_schedule'] === 'hourly'  ){ echo 'selected'; } ?>>every hour</option>
		<option value='twicedaily' <?php if( $options['fah_select_import_schedule'] === 'twicedaily'  ){ echo 'selected'; } ?>>12 hours</option>
		<option value='daily' <?php if( $options['fah_select_import_schedule'] === 'daily'  ){ echo 'selected'; } ?>>Once daily</option>
	</select>
	<div class="helper">Last import run: <?php if (!empty($importlastrun)) echo $importlastrun; ?></div>
<?php

}

function fah_check_publish_render(  ) {
    
	$options = get_option( 'fah_settings' );
	
    ?>
	<input type='checkbox' name='fah_settings[fah_check_publish]' <?php checked( $options['fah_check_publish'], 1 ); ?> value='1'>
	<?php

}

function fah_check_update_attr_render(  ) {
    
	$options = get_option( 'fah_settings' );
	$checkvalue = 0;
	if (isset($options['fah_check_update_attr']))
		$checkvalue = $options['fah_check_update_attr'];
    ?>
	<input type='checkbox' name='fah_settings[fah_check_update_attr]' <?php checked( $checkvalue, 1 ); ?> value='1'>
	<?php

}


function fah_publish_price_render() {
    
    $options = get_option( 'fah_settings' );
    if ( !isset( $options['fah_select_publish_price'] ) ) {
        $options['fah_select_publish_price'] = 'disable';
    }
    ?>
	<select class='flora-field' name='fah_settings[fah_select_publish_price]' onchange="pricebox(this);">
		<option value='disable' <?php if( $options['fah_select_publish_price'] === 'disable'  ){ echo 'selected'; }  ?>>Disable</option>
		<option value='fixed' <?php if( $options['fah_select_publish_price'] === 'fixed'  ){ echo 'selected'; }  ?>>Fixed</option>
		<option value='percent' <?php if( $options['fah_select_publish_price'] === 'percent'  ){ echo 'selected'; }  ?>>Percentage</option>
		
	</select>
	<div class="helper"> Disable: Publishing price is not auto calculated, publishing price has to be input manually.</div>
	<div class="helper"> Fixed: A fixed price is added to Flora@home price</div>
	<div class="helper"> Percent: A percentage is added to Flora@home price</div>
	<script type="text/javascript">
	function pricebox(sel) {
		if (sel.value == "disable") {
			jQuery('#autoprice').prop('disabled', true);
			jQuery('#autoprice').prop('required', false);
			
			}
		else {
			jQuery('#autoprice').prop('disabled', false);
			jQuery('#autoprice').prop('required', true);
		}
	}
	
<!--

//-->
</script>
<?php

}
function fah_text_publish_price_value_render() {
    
    $options = get_option( 'fah_settings' );
    
    ?>
	<input class='flora-field' id=autoprice <?php if ( $options['fah_select_publish_price'] === 'disable') { echo 'disabled required="false"';} else echo 'required=true'; ?>  type='number' min=0 step=0.01 name='fah_settings[fah_text_publish_price_value]' value='<?php echo $options['fah_text_publish_price_value']; ?>'>
	<?php

}


function fah_export_schedule_render() {
    
    $options = get_option( 'fah_settings' );
    if ( !isset( $options['fah_select_export_schedule'] ) ) {
        $options['fah_select_export_schedule'] = 'disable';
	} else {
		if ($options['fah_select_export_schedule'] === 'disable' ) 
					wp_clear_scheduled_hook('task_flora_order_export');
		else  {
			if (wp_get_schedule('fah_select_export_schedule') !== $options['fah_select_export_schedule'] ) {
				if ( $time = wp_next_scheduled( 'task_flora_order_export' ) )
       					 wp_unschedule_event( $time, 'task_flora_order_export' );
				
				wp_schedule_event(time(), $options['fah_select_export_schedule'], 'task_flora_order_export');

			}
			
		}
			

	}
	
	
	$exportlastrun = get_option( 'fah_select_export_run' );
    
    ?>
	<select class='flora-field' name='fah_settings[fah_select_export_schedule]'>
		<option value='disable' <?php if( $options['fah_select_export_schedule'] === 'disable'  ){ echo 'selected'; } ?>>Disable</option>
		<option value='5min' <?php if( $options['fah_select_export_schedule'] === '5min'  ){ echo 'selected'; } ?>>Every 5 Minutes</option>
		<option value='hourly' <?php if( $options['fah_select_export_schedule'] === 'hourly'  ){ echo 'selected'; } ?>>Every hour</option>
		<option value='twicedaily' <?php if( $options['fah_select_export_schedule'] === 'twicedaily'  ){ echo 'selected'; } ?>>12 hours</option>
		<option value='daily' <?php if( $options['fah_select_export_schedule'] === 'daily'  ){ echo 'selected'; } ?>>Once daily</option>
	</select>
	<div class="helper">Last export run: <?php if (!empty( $exportlastrun)) echo $exportlastrun; ?></div>
<?php

}

function fah_order_status_render() {
    
    $options = get_option( 'fah_settings' );
    
    $wcOrderStatus = wc_get_order_statuses();
	
   
   
    ?>
	
	<select class='flora-field' name='fah_settings[fah_select_order_status][]'  multiple="multiple">
	<?php
    foreach ($wcOrderStatus as $orderstatuskey => $orderstatusValue) {
                   
        if (in_array($orderstatuskey, $options['fah_select_order_status'])) 
            echo '<option value="'.$orderstatuskey.'" selected="selected">'.$orderstatusValue.'</option>';
        else 
            echo '<option value="'.$orderstatuskey.'" >'.$orderstatusValue.'</option>';
        
    }
    
   
	?>
	</select>

<?php

}

// Add custom Schedule of 5 Mins & 2 mins

function florahome_custom_cron_schedules($schedules){
    if(!isset($schedules["5min"])){
        $schedules["5min"] = array(
            'interval' => 5*60,
            'display' => __('Once every 5 minutes'));
    }
	
	if(!isset($schedules["5min"])){
        $schedules["2min"] = array(
            'interval' => 2*60,
            'display' => __('Once every 2 minutes'));
	}
	
	if(!isset($schedules["10min"])){
        $schedules["2min"] = array(
            'interval' => 10*60,
            'display' => __('Once every 10 minutes'));
    }
    return $schedules;
}
add_filter('cron_schedules','florahome_custom_cron_schedules');

function fah_options_page() { 

	?>
	<h1></h1>
	<form action='options.php' class='flora-form' method='post'>

		<h2 class='flora-set-header'>Flora@home Woo Commerce plugin settings</h2>

		<div class='flora-settings'>
		<?php
		settings_fields( 'pluginPage_general' );
		do_settings_sections( 'pluginPage_general' );
		?></div>

		<div class='flora-settings'>
		<?php
		settings_fields( 'pluginPage_product' );
		do_settings_sections( 'pluginPage_product' );
		?></div>

		<div class='flora-settings'>
		<?php
		settings_fields( 'pluginPage_order' );
		do_settings_sections( 'pluginPage_order' );
		?></div>

		<?php
		submit_button();
		
		?>

	</form>

	 
	<?php

}

?>