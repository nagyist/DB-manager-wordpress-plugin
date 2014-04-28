( function( $ ) {
	$( document ).ready( function() {
		/* Ajax download phpmyadmin */
		var data_phpmyadmin = {
			action: 'dbmngr_phpmyadmin',
		};
		$( '#dbmngr_download_ajax_pma' ).click( function() {
			dbmngr_download_ajax_pma( pma );
			return false;
		} );
		$( '#dbmngr_update_ajax_pma' ).click( function() {
			dbmngr_download_ajax_pma( pma_update );
			return false;
		} );
		function dbmngr_download_ajax_pma( pma_message ) {
			$( '#dbmngr_download_ajax_pma' ).remove();
			$( '#dbmngr_update_ajax_pma' ).remove();
			$( '#dbmngr-submit-loader-pma' ).show();
			$.ajax( {
				type: "POST",
				url: ajaxurl,
				data: data_phpmyadmin,
				success: function( response ) {
					if ( 'error' != response ) {
						$( '.updated' ).css( 'display', 'block' );
						$( '.updated p strong' ).text( pma_message );
						$( '.dbmngr-phpmyadmin' ).html( response );
						$( '#dbmngr-submit-loader-pma' ).fadeOut();
						$( '#dbmngr_download_ajax_pma' ).remove();
					} else {
						$( '.error' ).css( 'display', 'block' );
						$( '.error p strong' ).text( pma_error );
						$( '#dbmngr-submit-loader-pma' ).fadeOut();
						$( '#dbmngr_download_ajax_pma' ).remove();
					}
				}
			} );
		}

		/* Ajax download dumper */
		var data_dumper = {
			action: 'dbmngr_dumper',
		};
		$( '#dbmngr_download_ajax_dumper' ).click( function() {
			dbmngr_download_ajax_dumper( dumper );
			return false;
		} );
		$( '#dbmngr_update_ajax_dumper' ).click( function() {
			dbmngr_download_ajax_dumper( dumper_udate );
			return false;
		} );
		function dbmngr_download_ajax_dumper( dumper_message ) {
			$( '#dbmngr_download_ajax_dumper' ).remove();
			$( '#dbmngr_update_ajax_dumper' ).remove();
			$( '#dbmngr-submit-loader-dumper' ).show();
			$.ajax( {
				type: "POST",
				url: ajaxurl,
				data: data_dumper,
				success: function( response ) {
					if ( 'error' != response ) {
						$( '.updated' ).css( 'display', 'block' );
						$( '.updated p strong' ).text( dumper_message );
						$( '.dbmngr-dumper' ).html( response );
						$( '#dbmngr-submit-loader-dumper' ).fadeOut();
						$( '#dbmngr_download_ajax_dumper' ).remove();
					} else {
						$( '.error' ).css( 'display', 'block' );
						$( '.error p strong' ).text( dumper_error );
						$( '#dbmngr-submit-loader-dumper' ).fadeOut();
						$( '#dbmngr_download_ajax_dumper' ).remove();
					}
				}
			} );
		}
	} );
} )( jQuery );
