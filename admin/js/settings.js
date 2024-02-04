/*-
 * Author: Alin Marcu 
 * Author URI: https://deconf.com 
 * Copyright 2013 Alin Marcu 
 * License: GPLv2 or later 
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

/*
 * Navigation Tabs
 */
jQuery( document ).ready( function () {
	if ( window.location.href.indexOf( "page=cawp_" ) != -1 ) {
		var ident = 'basic';

		if ( window.location.hash ) {
			ident = window.location.hash.split( '#' )[ 2 ].split( '-' )[ 1 ];
		} else if ( window.location.href.indexOf( "page=cawp_errors_debugging" ) != -1 ) {
			ident = 'errors';
		}

		jQuery( ".nav-tab-wrapper a" ).each( function ( index ) {
			jQuery( this ).removeClass( "nav-tab-active" );
			jQuery( "#" + this.hash.split( '#' )[ 2 ] ).hide();
		} );
		jQuery( "#tab-" + ident ).addClass( "nav-tab-active" );
		jQuery( "#cawp-" + ident ).show();
	}

	jQuery( 'a[href^="#"]' ).on("click",  function ( e ) {
		if ( window.location.href.indexOf( "page=cawp_" ) != -1 ) {
			jQuery( ".nav-tab-wrapper a" ).each( function ( index ) {
				jQuery( this ).removeClass( "nav-tab-active" );
				jQuery( "#" + this.hash.split( '#' )[ 2 ] ).hide();
			} );
			jQuery( this ).addClass( "nav-tab-active" );
			jQuery( "#" + this.hash.split( '#' )[ 2 ] ).show();
		}
	} );
	
	jQuery( '#cawp-autodismiss' ).delay( 2000 ).fadeOut( 'slow' );
	
} );