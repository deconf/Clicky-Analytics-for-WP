/*-
 * Author: Alin Marcu 
 * Author URI: https://deconf.com 
 * Copyright 2013 Alin Marcu 
 * License: GPLv2 or later 
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

"use strict";

jQuery( document ).ready( function () {

	var cawp_ui = {
		action : 'cawp_dismiss_notices',
		cawp_security_dismiss_notices : cawp_ui_data.security,
	}

	jQuery( "#cawp-notice .notice-dismiss" ).on("click",  function () {
		jQuery.post( cawp_ui_data.ajaxurl, cawp_ui );
	} );

	if ( cawp_ui_data.ed_bubble != '' ) {
		jQuery( '#toplevel_page_cawp_settings li > a[href*="page=cawp_errors_debugging"]' ).append( '&nbsp;<span class="awaiting-mod count-1"><span class="pending-count" style="padding:0 7px;">' + cawp_ui_data.ed_bubble + '</span></span>' );
	}

} );