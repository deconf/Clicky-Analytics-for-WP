/*-
 * Author: Alin Marcu 
 * Author URI: https://deconf.com 
 * Copyright 2013 Alin Marcu 
 * License: GPLv2 or later 
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

"use strict";

if ( cawpItemData.mapsApiKey ) {
	google.charts.load( 'current', {
		'mapsApiKey' : cawpItemData.mapsApiKey,
		'packages' : [ 'corechart', 'table', 'orgchart', 'geochart', 'controls' ]
	} );
} else {
	google.charts.load( 'current', {
		'packages' : [ 'corechart', 'table', 'orgchart', 'geochart', 'controls' ]
	} );
}

google.charts.setOnLoadCallback( CAWPReportLoad );

// Get the numeric ID
cawpItemData.getID = function ( item ) {
	if ( cawpItemData.scope == 'admin-item' ) {
		if ( typeof item.id == "undefined" ) {
			return 0
		}
		if ( item.id.split( '-' )[ 1 ] == "undefined" ) {
			return 0;
		} else {
			return item.id.split( '-' )[ 1 ];
		}
	} else {
		if ( typeof item.id == "undefined" ) {
			return 1;
		}
		if ( item.id.split( '-' )[ 4 ] == "undefined" ) {
			return 1;
		} else {
			return item.id.split( '-' )[ 4 ];
		}
	}
}

// Get the selector
cawpItemData.getSelector = function ( scope ) {
	if ( scope == 'admin-item' ) {
		return 'a[id^="cawp-"]';
	} else {
		return 'li[id^="wp-admin-bar-cawp"] a';
	}
}

cawpItemData.responsiveDialog = function () {
	var dialog, wWidth, visible;

	visible = jQuery( ".ui-dialog:visible" );

	// on each visible dialog
	visible.each( function () {
		dialog = jQuery( this ).find( ".ui-dialog-content" ).data( "ui-dialog" );
		// on each fluid dialog
		if ( dialog.options.fluid ) {
			wWidth = jQuery( window ).width();
			// window width vs dialog width
			if ( wWidth < ( parseInt( dialog.options.maxWidth ) + 50 ) ) {
				// don't fill the entire screen
				jQuery( this ).css( "max-width", "90%" );
			} else {
				// maxWidth bug fix
				jQuery( this ).css( "max-width", dialog.options.maxWidth + "px" );
			}
			// change dialog position
			dialog.option( "position", dialog.options.position );
		}
	} );
}

jQuery.fn.extend( {
	cawpItemReport : function ( itemId ) {
		var postData, tools, template, reports, refresh, init, swmetric, slug = "-" + itemId;

		tools = {
			setCookie : function ( name, value ) {
				var expires, dateItem = new Date();

				if ( cawpItemData.scope == 'admin-widgets' ) {
					name = "cawp_wg_" + name;
				} else {
					name = "cawp_ir_" + name;
				}
				dateItem.setTime( dateItem.getTime() + ( 24 * 60 * 60 * 1000 * 365 ) );
				expires = "expires=" + dateItem.toUTCString();
				document.cookie = name + "=" + value + "; " + expires + "; path=/";
			},
			getCookie : function ( name ) {
				var cookie, cookiesArray, div, i = 0;

				if ( cawpItemData.scope == 'admin-widgets' ) {
					name = "cawp_wg_" + name + "=";
				} else {
					name = "cawp_ir_" + name + "=";
				}
				cookiesArray = document.cookie.split( ';' );
				for ( i = 0; i < cookiesArray.length; i++ ) {
					cookie = cookiesArray[ i ];
					while ( cookie.charAt( 0 ) == ' ' )
						cookie = cookie.substring( 1 );
					if ( cookie.indexOf( name ) == 0 )
						return cookie.substring( name.length, cookie.length );
				}
				return false;
			},
			escape : function ( str ) {
				div = document.createElement( 'div' );
				div.appendChild( document.createTextNode( str ) );
				return div.innerHTML;
			},
			isNumeric : function (string) {
				return !isNaN(parseFloat(string)) && isFinite(string);
			},				
		}

		template = {

			addOptions : function ( id, list ) {
				var defaultMetric, defaultDimension, defaultView, defaultInterval, output = [];

				if ( !tools.getCookie( 'default_metric' ) || !tools.getCookie( 'default_dimension' ) || !tools.getCookie( 'default_swmetric' ) || !tools.getCookie( 'default_interval' ) ) {
					defaultMetric = 'sessions';
					defaultDimension = moment().subtract( 30, 'days' ).format( "YYYY-MM-DD" ) + ' - ' + moment().subtract( 1, 'days' ).format( "YYYY-MM-DD" );
					swmetric = 'visitors';
					defaultInterval = 'Last 30 Days'; 
					tools.setCookie( 'default_metric', defaultMetric );
					tools.setCookie( 'default_dimension', defaultDimension );
					tools.setCookie( 'default_interval', defaultInterval );
					tools.setCookie( 'default_swmetric', swmetric );
				} else {
					defaultMetric = tools.getCookie( 'default_metric' );
					defaultDimension = tools.getCookie( 'default_dimension' );
					defaultView = tools.getCookie( 'default_view' );
					defaultInterval = tools.getCookie( 'default_interval' );
					
					switch ( defaultInterval ) {

						case "Today":
							defaultDimension = moment().subtract( 0, 'days' ).format( "YYYY-MM-DD" ) + ' - ' + moment().format( "YYYY-MM-DD" );
							break;
						case "Yesterday":
							defaultDimension = moment().subtract( 1, 'days' ).format( "YYYY-MM-DD" ) + ' - ' + moment().subtract( 1, 'days').format( "YYYY-MM-DD" );						
							break;
						case "Last 7 Days":
							defaultDimension = moment().subtract( 6, 'days' ).format( "YYYY-MM-DD" ) + ' - ' + moment().format( "YYYY-MM-DD" );						
							break;
						case "Last 30 Days":
							defaultDimension = moment().subtract( 29, 'days' ).format( "YYYY-MM-DD" ) + ' - ' + moment().format( "YYYY-MM-DD" );						
							break;
						case "Last 90 Days":
							defaultDimension = moment().subtract( 89, 'days' ).format( "YYYY-MM-DD" ) + ' - ' + moment().format( "YYYY-MM-DD" );						
							break;
						case "This Month":
							defaultDimension = moment().startOf( 'month' ).format( "YYYY-MM-DD" ) + ' - ' + moment().endOf( 'month' ).format( "YYYY-MM-DD" );						
							break;
						case "Last Month":
							defaultDimension = moment().subtract( 1, 'month' ).startOf( 'month' ).format( "YYYY-MM-DD" ) + ' - ' + moment().subtract( 1, 'month' ).endOf( 'month' ).format( "YYYY-MM-DD" );						
							break;														
					}					
					
					swmetric = tools.getCookie( 'default_swmetric' );
				}

				if ( list == 'submetrics' ) {

					jQuery( id ).html( output );

				} else if ( list == 'range' ) {
					jQuery( id ).val( defaultDimension );
				} else {
					jQuery.each( list, function ( key, value ) {
						if ( key == defaultMetric || key == defaultDimension || key == defaultView ) {
							output.push( '<option value="' + key + '" selected="selected">' + value + '</option>' );
						} else {
							output.push( '<option value="' + key + '">' + value + '</option>' );
						}
					} );
					jQuery( id ).html( output.join( '' ) );
				}
			},

			init : function () {
				var tpl;

				if ( !jQuery( '#cawp-window' + slug ).length ) {
					return;
				}

				if ( jQuery( '#cawp-window' + slug ).html().length ) { // add main template once
					return;
				}

				tpl = '<div id="cawp-container' + slug + '">';

				if ( cawpItemData.propertyList != false ) {
					tpl += '<select id="cawp-sel-property' + slug + '"></select>';
				}

				tpl += '<input type="text" id="cawp-sel-period' + slug + '" name="cawp-sel-period' + slug + '" size="21"/>';
				tpl += '<select id="cawp-sel-report' + slug + '"></select>';
				tpl += '<div id="cawp-sel-metric' + slug + '" style="float:right;display:none;">';
				tpl += '</div>';
				tpl += '<div id="cawp-progressbar' + slug + '"></div>';
				tpl += '<div id="cawp-status' + slug + '"></div>';
				tpl += '<div id="cawp-reports' + slug + '"></div>';
				tpl += '<div style="text-align:right;width:100%;font-size:0.8em;clear:both;margin-right:5px;margin-top:10px;">';
				tpl += cawpItemData.i18n[ 14 ];
				tpl += ' <a href="https://deconf.com/clicky-analytics-dashboard-wordpress/" rel="nofollow" style="text-decoration:none;font-size:1em;color:#0073aa;">Clicky Analytics</a>&nbsp;';
				tpl += '</div>';
				tpl += '</div>',

				jQuery( '#cawp-window' + slug ).append( tpl );

				template.addOptions( '#cawp-sel-period' + slug, 'range' );
				template.addOptions( '#cawp-sel-property' + slug, cawpItemData.propertyList );
				template.addOptions( '#cawp-sel-report' + slug, cawpItemData.reportList );
				template.addOptions( '#cawp-sel-metric' + slug, 'submetrics' );

			}
		}

		reports = {
			oldViewPort : 0,
			inProgress : 0,
			orgChartTableChartData : '',
			tableChartData : '',
			orgChartPieChartsData : '',
			geoChartTableChartData : '',
			areachartSummaryData : '',
			rtRuns : null,
			i18n : null,

			getTitle : function ( scope ) {
				if ( scope == 'admin-item' ) {
					return jQuery( '#cawp' + slug ).attr( "title" );
				} else {
					return cawpItemData.i18n[ 16 ];
					//return document.getElementsByTagName( "title" )[ 0 ].innerHTML;
				}
			},

			alertMessage : function ( msg ) {
				jQuery( "#cawp-status" + slug ).css( {
					"margin-top" : "3px",
					"padding-left" : "5px",
					"height" : "auto",
					"color" : "#000",
					"border-left" : "5px solid red"
				} );
				jQuery( "#cawp-status" + slug ).html( msg );
			},

			areachartSummary : function ( response ) {
				var tpl;
				jQuery( '#cawp-sel-metric' + slug ).hide();
				tpl = '<div id="cawp-areachartsummary' + slug + '">';
				tpl += '<div id="cawp-summary' + slug + '">';
				tpl += '<div class="inside">';
				tpl += '<div class="small-box first"><h3>' + cawpItemData.i18n[ 5 ] + '</h3><p id="cawpvisitors' + slug + '">&nbsp;</p></div>';
				tpl += '<div class="small-box second"><h3>' + cawpItemData.i18n[ 6 ] + '</h3><p id="cawppageviews' + slug + '">&nbsp;</p></div>';
				tpl += '<div class="small-box third"><h3>' + cawpItemData.i18n[ 7 ] + '</h3><p id="cawptimeaverage' + slug + '">&nbsp;</p></div>';
				tpl += '<div class="small-box last"><h3>' + cawpItemData.i18n[ 8 ] + '</h3><p id="cawpbouncerate' + slug + '">&nbsp;</p></div>';
				tpl += '</div>';
				tpl += '<div id="cawp-areachart' + slug + '"></div>';
				tpl += '</div>';
				tpl += '</div>';

				if ( !jQuery( '#cawp-areachartsummary' + slug ).length ) {
					jQuery( '#cawp-reports' + slug ).html( tpl );
				}

				reports.areachartSummaryData = response;
				if ( Array.isArray( response ) ) {
					if ( !tools.isNumeric( response[ 0 ] ) ) {
						if ( Array.isArray( response[ 0 ] ) ) {
							if ( postData.query == 'visitBounceRate,summary' ) {
								reports.drawareachart( response[ 0 ], true );
							} else {
								reports.drawareachart( response[ 0 ], false );
							}
						} else {
							reports.throwDebug( response[ 0 ] );
						}
					} else {
						reports.throwError( '#cawp-areachart' + slug, response[ 0 ], "125px" );
					}
					if ( !tools.isNumeric( response[ 1 ] ) ) {
						if ( Array.isArray( response[ 1 ] ) ) {
							reports.drawSummary( response[ 1 ] );
						} else {
							reports.throwDebug( response[ 1 ] );
						}
					} else {
						reports.throwError( '#cawp-summary' + slug, response[ 1 ], "40px" );
					}
				} else {
					reports.throwDebug( response );
				}
				CAWPNProgress.done();
			},

			orgChartPieCharts : function ( response ) {
				var i = 0;
				var tpl;

				tpl = '<div id="cawp-orgchartpiecharts' + slug + '">';
				tpl += '<div id="cawp-orgchart' + slug + '"></div>';
				tpl += '<div class="cawp-floatwraper">';
				tpl += '<div id="cawp-piechart-1' + slug + '" class="halfsize floatleft"></div>';
				tpl += '<div id="cawp-piechart-2' + slug + '" class="halfsize floatright"></div>';
				tpl += '</div>';
				tpl += '<div class="cawp-floatwraper">';
				tpl += '<div id="cawp-piechart-3' + slug + '" class="halfsize floatleft"></div>';
				tpl += '<div id="cawp-piechart-4' + slug + '" class="halfsize floatright"></div>';
				tpl += '</div>';
				tpl += '</div>';

				if ( !jQuery( '#cawp-orgchartpiecharts' + slug ).length ) {
					jQuery( '#cawp-reports' + slug ).html( tpl );
				}

				reports.orgChartPieChartsData = response;
				if ( Array.isArray( response ) ) {
					if ( !tools.isNumeric( response[ 0 ] ) ) {
						if ( Array.isArray( response[ 0 ] ) ) {
							
							reports.drawOrgChart( response[ 0 ] );
						} else {
							reports.throwDebug( response[ 0 ] );
						}
					} else {
						
						reports.throwError( '#cawp-orgchart' + slug, response[ 0 ], "125px" );
					}

					for ( i = 1; i < response.length; i++ ) {
						if ( !tools.isNumeric( response[ i ] ) ) {
							if ( Array.isArray( response[ i ] ) ) {
								
								reports.drawPieChart( 'piechart-' + i, response[ i ], reports.i18n[ i ] );
							} else {
								reports.throwDebug( response[ i ] );
							}
						} else {
							
							reports.throwError( '#cawp-piechart-' + i + slug, response[ i ], "80px" );
						}
					}
				} else {
					reports.throwDebug( response );
				}
				CAWPNProgress.done();
			},

			geoChartTableChart : function ( response ) {
				var tpl;

				tpl = '<div id="cawp-geocharttablechart' + slug + '">';
				tpl += '<div id="cawp-geochart' + slug + '"></div>';
				tpl += '<div id="cawp-dashboard' + slug + '">';
				tpl += '<div id="cawp-control' + slug + '"></div>';
				tpl += '<div id="cawp-tablechart' + slug + '"></div>';
				tpl += '</div>';
				tpl += '</div>';
				
				if ( !jQuery( '#cawp-geocharttablechart' + slug ).length ) {
					jQuery( '#cawp-reports' + slug ).html( tpl );
				}

				reports.geoChartTableChartData = response;
				if ( Array.isArray( response ) ) {
					if ( !tools.isNumeric( response[ 0 ] ) ) {
						if ( Array.isArray( response[ 0 ] ) ) {
							reports.drawGeoChart( response[ 0 ] );
							reports.drawTableChart( response[ 0 ] );
						} else {
							reports.throwDebug( response[ 0 ] );
						}
					} else {
						
						reports.throwError( '#cawp-geochart' + slug, response[ 0 ], "125px" );
						reports.throwError( '#cawp-tablechart' + slug, response[ 0 ], "125px" );
					}
				} else {
					reports.throwDebug( response );
				}
				CAWPNProgress.done();
			},

			orgChartTableChart : function ( response ) {
				var tpl;

				tpl = '<div id="cawp-orgcharttablechart' + slug + '">';
				tpl += '<div id="cawp-orgchart' + slug + '"></div>';
				tpl += '<div id="cawp-dashboard' + slug + '">';
				tpl += '<div id="cawp-control' + slug + '"></div>';
				tpl += '<div id="cawp-tablechart' + slug + '"></div>';
				tpl += '</div>';
				tpl += '</div>';

				if ( !jQuery( '#cawp-orgcharttablechart' + slug ).length ) {
					jQuery( '#cawp-reports' + slug ).html( tpl );
				}

				reports.orgChartTableChartData = response
				if ( Array.isArray( response ) ) {
					if ( !tools.isNumeric( response[ 0 ] ) ) {
						if ( Array.isArray( response[ 0 ] ) ) {
							
							reports.drawOrgChart( response[ 0 ] );
						} else {
							reports.throwDebug( response[ 0 ] );
						}
					} else {
						
						reports.throwError( '#cawp-orgchart' + slug, response[ 0 ], "125px" );
					}

					if ( !tools.isNumeric( response[ 1 ] ) ) {
						if ( Array.isArray( response[ 1 ] ) ) {
							reports.drawTableChart( response[ 1 ] );
						} else {
							reports.throwDebug( response[ 1 ] );
						}
					} else {
						reports.throwError( '#cawp-tablechart' + slug, response[ 1 ], "125px" );
					}
				} else {
					reports.throwDebug( response );
				}
				CAWPNProgress.done();
			},

			tableChart : function ( response ) {
				var tpl;

				tpl = '<div id="cawp-404tablechart' + slug + '">';
				tpl += '<div id="cawp-tablechart' + slug + '"></div>';
				tpl += '</div>';

				if ( !jQuery( '#cawp-404tablechart' + slug ).length ) {
					jQuery( '#cawp-reports' + slug ).html( tpl );
				}

				reports.tableChartData = response
				if ( Array.isArray( response ) ) {
					if ( !tools.isNumeric( response[ 0 ] ) ) {
						if ( Array.isArray( response[ 0 ] ) ) {
							
							reports.drawTableChart( response[ 0 ] );
						} else {
							reports.throwDebug( response[ 0 ] );
						}
					} else {
						
						reports.throwError( '#cawp-tablechart' + slug, response[ 0 ], "125px" );
					}
				} else {
					reports.throwDebug( response );
				}
				CAWPNProgress.done();
			},

			drawTableChart : function ( data ) {
				var chartData, options, chart, ascending, dashboard, control, wrapper;

				ascending = false;

				chartData = google.visualization.arrayToDataTable( data );
				options = {
					page : 'enable',
					pageSize : 10,
					width : '100%',
					allowHtml : true,
					sortColumn : 1,
					sortAscending : ascending,
				};
				
				dashboard = new google.visualization.Dashboard(document.getElementById( 'cawp-dashboard' + slug ));
				
			    control = new google.visualization.ControlWrapper({
			        controlType: 'StringFilter',
			        containerId: 'cawp-control' + slug,
			        options: {
			            filterColumnIndex: 0, 
			            matchType : 'any',
			            ui : { label : '', cssClass : 'cawp-dashboard-control' },
			        }
			    });
			    
			    google.visualization.events.addListener(control, 'ready', function () {
			        jQuery('.cawp-dashboard-control input').prop('placeholder', cawpItemData.i18n[ 1 ]);
			    });
				
			    wrapper = new google.visualization.ChartWrapper({
			    	  'chartType' : 'Table',
			    	  'containerId' : 'cawp-tablechart' + slug,
			    	  'options' : options,
		    	});
			    
			    dashboard.bind(control, wrapper);
			    
			    dashboard.draw( chartData );
			    
			    // outputs selection
			    google.visualization.events.addListener(wrapper, 'select', function() {
			    	console.log(wrapper.getDataTable().getValue(wrapper.getChart().getSelection()[0].row, 0));
			    });
			},

			drawOrgChart : function ( data ) {
				var chartData, options, chart;

				chartData = google.visualization.arrayToDataTable( data );
				options = {
					allowCollapse : true,
					allowHtml : true,
					height : '100%',
					nodeClass : 'cawp-orgchart',
					selectedNodeClass : 'cawp-orgchart-selected',
				};
				chart = new google.visualization.OrgChart( document.getElementById( 'cawp-orgchart' + slug ) );

				chart.draw( chartData, options );
			},

			drawPieChart : function ( id, data, title ) {
				var chartData, options, chart;

				chartData = google.visualization.arrayToDataTable( data );
				options = {
					is3D : false,
					tooltipText : 'percentage',
					legend : 'none',
					chartArea : {
						width : '99%',
						height : '80%'
					},
					title : title,
					pieSliceText : 'value',
					colors : cawpItemData.colorVariations
				};
				chart = new google.visualization.PieChart( document.getElementById( 'cawp-' + id + slug ) );

				chart.draw( chartData, options );
			},

			drawGeoChart : function ( data ) {
				var chartData, options, chart;

				chartData = google.visualization.arrayToDataTable( data );
				options = {
					chartArea : {
						width : '99%',
						height : '90%'
					},
					colors : [ cawpItemData.colorVariations[ 5 ], cawpItemData.colorVariations[ 4 ] ]
				}

				chart = new google.visualization.GeoChart( document.getElementById( 'cawp-geochart' + slug ) );

				chart.draw( chartData, options );
			},

			drawareachart : function ( data, format ) {
				var chartData, options, chart, formatter;

				chartData = google.visualization.arrayToDataTable( data );

				if ( format ) {
					formatter = new google.visualization.NumberFormat( {
						suffix : '%',
						fractionDigits : 2
					} );

					formatter.format( chartData, 1 );
				}

				options = {
					legend : {
						position : 'none'
					},
					pointSize : 3,
					colors : [ cawpItemData.colorVariations[ 0 ], cawpItemData.colorVariations[ 4 ] ],
					areaOpacity : 0.7,
					chartArea : {
						width : '99%',
						height : '90%'
					},
					vAxis : {
						textPosition : "in",
						minValue : 0,
						textStyle : {
							auraColor : 'white',
							color : 'black'
						},
					},
					hAxis : {
						textPosition : 'none'
					},
					curveType : 'function',
				};
				chart = new google.visualization.AreaChart( document.getElementById( 'cawp-areachart' + slug ) );

				chart.draw( chartData, options );
			},
			
			drawSummary : function ( data ) {
				jQuery( "#cawpvisitors" + slug ).html( data[ 0 ] );
				jQuery( "#cawppageviews" + slug ).html( data[ 1 ] );
				jQuery( "#cawptimeaverage" + slug ).html( data[ 2 ] );
				jQuery( "#cawpbouncerate" + slug ).html( data[ 3 ] );
				jQuery( "#cawpservererrors" + slug ).html( data[ 5 ] );
				jQuery( "#cawpnotfound" + slug ).html( data[ 4 ] );
			},

			throwDebug : function ( response ) {
				jQuery( "#cawp-status" + slug ).css( {
					"margin-top" : "3px",
					"padding-left" : "5px",
					"height" : "auto",
					"color" : "#000",
					"border-left" : "5px solid red"
				} );
				if ( response == '-24' ) {
					jQuery( "#cawp-status" + slug ).html( cawpItemData.i18n[ 15 ] );
				} else {
					jQuery( "#cawp-reports" + slug ).css( {
						"background-color" : "#F7F7F7",
						"height" : "auto",
						"margin-top" : "10px",
						"padding-top" : "50px",
						"padding-bottom" : "50px",
						"color" : "#000",
						"text-align" : "center"
					} );
					jQuery( "#cawp-reports" + slug ).html( response );
					jQuery( "#cawp-reports" + slug ).show();
					jQuery( "#cawp-status" + slug ).html( cawpItemData.i18n[ 11 ] );
					console.log( "\n********************* CAWP Log ********************* \n\n" + response );
					postData = {
						action : 'cawp_set_error',
						response : response,
						cawp_security_set_error : cawpItemData.security,
					}
					jQuery.post( cawpItemData.ajaxurl, postData );
				}
			},

			throwError : function ( target, response, p ) {
				jQuery( target ).css( {
					"background-color" : "#F7F7F7",
					"height" : "auto",
					"padding-top" : p,
					"padding-bottom" : p,
					"color" : "#000",
					"text-align" : "center"
				} );
				if ( response == -21 ) {
					jQuery( target ).html( '<p><span style="font-size:4em;color:#778899;margin-left:-20px;" class="dashicons dashicons-clock"></span></p><br><p style="font-size:1.1em;color:#778899;">' + cawpItemData.i18n[ 12 ] + '</p>' );
				} else {
					jQuery( target ).html( cawpItemData.i18n[ 13 ] + ' (' + response + ')' );
				}
			},

			render : function ( view, period, query ) {
				var projectId, from, to, tpl, focusFlag;

				jQuery( '#cawp-sel-report' + slug ).show();

				jQuery( '#cawp-status' + slug ).html( '' );

				if ( period ) {
					from = period.split( " - " )[ 0 ];
					to = period.split( " - " )[ 1 ];
				} else {
					var date = new Date();
					date.setDate( date.getDate() - 30 );
					from = date.toISOString().split( 'T' )[ 0 ]; // "2016-06-08"
					date = new Date();
					to = date.toISOString().split( 'T' )[ 0 ]; // "2016-06-08"
				}

				tools.setCookie( 'default_metric', query );
				if ( period ) {
					tools.setCookie( 'default_dimension', period );
				}

				if ( typeof view !== 'undefined' ) {
					tools.setCookie( 'default_view', view );
					projectId = view;
				} else {
					projectId = false;
				}

				if ( cawpItemData.scope == 'admin-item' ) {
					postData = {
						action : 'cawp_backend_item_reports',
						cawp_security_backend_item_reports : cawpItemData.security,
						from : from,
						to : to,
						filter : itemId
					}
				} else if ( cawpItemData.scope == 'front-item' ) {
					postData = {
						action : 'cawp_frontend_item_reports',
						cawp_security_frontend_item_reports : cawpItemData.security,
						from : from,
						to : to,
						filter : cawpItemData.filter
					}
				} else {
					postData = {
						action : 'cawp_backend_item_reports',
						cawp_security_backend_item_reports : cawpItemData.security,
						projectId : projectId,
						from : from,
						to : to
					}
				}
				if ( jQuery.inArray( query, [ 'pages', 'referrers' ] ) > -1 ) {


					jQuery( '#cawp-sel-metric' + slug ).show();

					postData.query = 'channelGrouping,' + query;
					postData.metric = swmetric;

					jQuery.post( cawpItemData.ajaxurl, postData, function ( response ) {
						reports.orgChartTableChart( response );
					} );
				} else if ( query == '404errors' ) {


					jQuery( '#cawp-sel-metric' + slug ).show();

					postData.query = query;
					postData.metric = swmetric;

					jQuery.post( cawpItemData.ajaxurl, postData, function ( response ) {
						reports.tableChart( response );
					} );
				} else if ( query == 'siteperformance' || query == 'technologydetails' ) {


					jQuery( '#cawp-sel-metric' + slug ).show();

					if ( query == 'siteperformance' ) {
						postData.query = 'channelGrouping,medium,visitorType,source,socialNetwork';
						reports.i18n = cawpItemData.i18n.slice( 0, 5 );
					} else {
						reports.i18n = cawpItemData.i18n.slice( 15, 20 );
						postData.query = 'deviceCategory,browser,operatingSystem,screenResolution,mobileDeviceBranding';
					}
					postData.metric = swmetric;

					jQuery.post( cawpItemData.ajaxurl, postData, function ( response ) {
						reports.orgChartPieCharts( response )
					} );

				} else if ( query == 'locations' ) {


					jQuery( '#cawp-sel-metric' + slug ).show();

					postData.query = query;
					postData.metric = swmetric;

					jQuery.post( cawpItemData.ajaxurl, postData, function ( response ) {
						reports.geoChartTableChart( response );
					} );

				} else {

					postData.query = query + ',summary';

					jQuery.post( cawpItemData.ajaxurl, postData, function ( response ) {
						reports.areachartSummary( response );
					} );

				}
			},

			refresh : function () {
				if ( jQuery( '#cawp-areachartsummary' + slug ).length > 0 && Array.isArray( reports.areachartSummaryData ) ) {
					reports.areachartSummary( reports.areachartSummaryData );
				}
				if ( jQuery( '#cawp-orgchartpiecharts' + slug ).length > 0 && Array.isArray( reports.orgChartPieChartsData ) ) {
					reports.orgChartPieCharts( reports.orgChartPieChartsData );
				}
				if ( jQuery( '#cawp-geocharttablechart' + slug ).length > 0 && Array.isArray( reports.geoChartTableChartData ) ) {
					reports.geoChartTableChart( reports.geoChartTableChartData );
				}
				if ( jQuery( '#cawp-orgcharttablechart' + slug ).length > 0 && Array.isArray( reports.orgChartTableChartData ) ) {
					reports.orgChartTableChart( reports.orgChartTableChartData );
				}
				if ( jQuery( '#cawp-404tablechart' + slug ).length > 0 && Array.isArray( reports.tableChartData ) ) {
					reports.tableChart( reports.tableChartData );
				}
			},

			init : function () {
			
				if ( !reports.inProgress ) {
					
					reports.inProgress = 1;
					
					try {
						CAWPNProgress.configure( {
							parent : "#cawp-progressbar" + slug,
							showSpinner : false
						} );
						CAWPNProgress.start();
					} catch ( e ) {
						reports.alertMessage( cawpItemData.i18n[ 0 ] );
					}
	
					reports.render( jQuery( '#cawp-sel-property' + slug ).val(), jQuery( 'input[name="cawp-sel-period' + slug + '"]' ).val(), jQuery( '#cawp-sel-report' + slug ).val() );
	
					jQuery( window ).on("resize",  function () {
						var diff = jQuery( window ).width() - reports.oldViewPort;
						if ( ( diff < -5 ) || ( diff > 5 ) ) {
							reports.oldViewPort = jQuery( window ).width();
							reports.refresh(); // refresh only on over 5px viewport width changes
						}
					} );
					
					reports.inProgress = 0;
				}	
			}
		}

		template.init();

		reports.init();
		
		setTimeout(
		  function() {
			jQuery( '#cawp-sel-property' + slug ).on("change",  function () {
				reports.init();
			} );
			
			jQuery( 'input[name="cawp-sel-period' + slug + '"]' ).on("change",  function () {
				reports.init();
			} );
	
			jQuery( '#cawp-sel-report' + slug ).on("change",  function () {
				reports.init();
			} );		
		}, 1000);		

		jQuery( function () {
			jQuery( 'input[name="cawp-sel-period' + slug + '"]' ).daterangepicker( {
				ranges : {
					'Today' : [ moment().subtract( 0, 'days' ), moment() ],
					'Yesterday' : [ moment().subtract( 1, 'days' ), moment().subtract( 1, 'days' ) ],
					'Last 7 Days' : [ moment().subtract( 6, 'days' ), moment() ],
					'Last 30 Days' : [ moment().subtract( 29, 'days' ), moment() ],
					'Last 90 Days' : [ moment().subtract( 89, 'days' ), moment() ],
					'This Month' : [ moment().startOf( 'month' ), moment().endOf( 'month' ) ],
					'Last Month' : [ moment().subtract( 1, 'month' ).startOf( 'month' ), moment().subtract( 1, 'month' ).endOf( 'month' ) ]
				},
				minDate : moment().subtract( 16, 'months' ),
				maxDate : moment(),
				autoUpdateInput : true,
				locale : {
					format : 'YYYY-MM-DD'
				}
			}, function(start, end, label) { tools.setCookie( 'default_interval', label ); } );
		} );

		jQuery( '[id^=cawp-swmetric-]' ).on("click",  function () {
			swmetric = this.id.replace( 'cawp-swmetric-', '' );
			tools.setCookie( 'default_swmetric', swmetric );
			jQuery( '#' + this.id ).css( "color", "#008ec2" );

			reports.init();
		} );

		if ( cawpItemData.scope == 'admin-widgets' ) {
			return;
		} else {
			return this.dialog( {
				width : 'auto',
				maxWidth : 510,
				height : 'auto',
				modal : true,
				fluid : true,
				dialogClass : 'cawp wp-dialog',
				resizable : false,
				title : reports.getTitle( cawpItemData.scope ),
				position : {
					my : "top",
					at : "top+100",
					of : window
				}
			} );
		}
	}
} );

function CAWPReportLoad () {
	if ( cawpItemData.scope == 'admin-widgets' ) {
		jQuery( '#cawp-window-1' ).cawpItemReport( 1 );
	} else if ( cawpItemData.scope == 'front-item' ) {
		jQuery( cawpItemData.getSelector( cawpItemData.scope ) ).on("click",  function () {
			if ( !jQuery( "#cawp-window-1" ).length > 0 ) {
				jQuery( "body" ).append( '<div id="cawp-window-1"></div>' );
			}
			jQuery( '#cawp-window-1' ).cawpItemReport( 1 );
		} );
	} else {
		jQuery( cawpItemData.getSelector( cawpItemData.scope ) ).on("click",  function () {
			if ( !jQuery( "#cawp-window-" + cawpItemData.getID( this ) ).length > 0 ) {
				jQuery( "body" ).append( '<div id="cawp-window-' + cawpItemData.getID( this ) + '"></div>' );
			}
			jQuery( '#cawp-window-' + cawpItemData.getID( this ) ).cawpItemReport( cawpItemData.getID( this ) );
		} );
	}

	// on window resize
	jQuery( window ).on("resize",  function () {
		cawpItemData.responsiveDialog();
	} );

	// dialog width larger than viewport
	jQuery( document ).on( "dialogopen", ".ui-dialog", function ( event, ui ) {
		cawpItemData.responsiveDialog();
	} );
}
