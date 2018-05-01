(function($) {
	'use strict';
	

		
		$( document ).ready( function() {
				var $product_screen = $( '.edit-php.post-type-product' );
				var $title_action   = $product_screen.find( '.page-title-action:last' );
				var $blankslate     = $product_screen.find( '.woocommerce-BlankState' );

				var buttonName = 'Import Flora Products';
				var updatebutton = 'Check Flora updates';

			if ( 0 === $blankslate.length ) {
				//$title_action.after( '<a href="' + woocommerce_admin.urls.export_products + '" class="page-title-action">' + buttonName + '</a>' );
				$title_action.after( '<input type="button" id="full-import" class="page-title-action" value="' + buttonName + '"/><div class="spinner" style="float:initial; visibility: visible; display: none;"></div>' );
				$title_action.after( '<input type="button" id="update-flora" class="page-title-action" value="' + updatebutton + '"/>' );
				$('#full-import').after('<div id="show-flora-progress" class="flora-progress" style="float:initial; visibility: visible; display: none;"></div>');
				//$title_action.after( '' );
				
			} else {
				$title_action.hide();
			}

			$('#full-import').click(function(){
				var data = {
					action: 'flora_ajaximport'
					
				};
				$('.spinner').show();
				$.post(ajaxurl, data, function(response) {
					//alert('Got this from the server: ' + response);
					$('.spinner').hide();
					if (location.href.indexOf("?") === -1) {
						window.location = location.href += "?flora-import=success";
					}
					else {
						window.location = location.href += "&flora-import=success";
					}
					window.location.load();
					
				});
				
			
			
			
			});

			$('#update-flora').click(function(){
				$('.spinner').show();
				//$('.spinner').attr("css", "visibility: visible; float: initial");
				var data = {
					action: 'flora_ajaxupdate'
					
				};
				$('.spinner').show();
				$.post(ajaxurl, data, function(response) {
					loading: true, 
					//alert('Got this from the server: ' + response);
					$('.spinner').hide();
					location.reload();
					
					
				});
			
				//$(".spinner").attr("style", "visibility: hidden; float: initial");
			
			});

			function update_progress( totalproducts, productImported, productskipped ) {

				alert('TEST');


			};

		});

})(jQuery);
