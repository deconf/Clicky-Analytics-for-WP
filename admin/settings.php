<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2013 Alin Marcu
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
//@formatter:off
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

use Google\Service\Exception as GoogleServiceException;

final class CAWP_Settings {

	private static function update_options( $who ) {
		$cawp = CAWP();
		$options = $cawp->config->options; // Get current options
		if ( isset( $_POST['options']['cawp_hidden'] ) && isset( $_POST['options'] ) && ( isset( $_POST['cawp_security'] ) && wp_verify_nonce( $_POST['cawp_security'], 'cawp_form' ) ) ) {
			$new_options = $_POST['options'];
			if ( 'settings' == $who ) {
				$options['backend_item_reports'] = 0;
				$options['dashboard_widget'] = 0;
				$options['track_username'] = 0;
				$options['track_email'] = 0;
				$options['track_youtube'] = 0;
				$options['track_html5'] = 0;
				$options['track_outbound'] = 0;
				$options['tracking'] = 1;
				if ( empty( $new_options['access_back'] ) ) {
					$new_options['access_back'][] = 'administrator';
				}
				$options['frontend_item_reports'] = 0;
				if ( empty( $new_options['access_front'] ) ) {
					$new_options['access_front'][] = 'administrator';
				}
			} else if ( 'setup' == $who ) {
				$options['sitekey'] = '';
				$options['siteid'] = '';
			}

			$options = array_merge( $options, $new_options );
			$cawp->config->options = $options;
			$cawp->config->set_plugin_options();
		}
		return $options;
	}

	private static function navigation_tabs( $tabs ) {
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $name ) {
			echo "<a class='nav-tab' id='tab-$tab' href='#top#cawp-$tab'>$name</a>";
		}
		echo '</h2>';
	}

	public static function settings() {

		$cawp = CAWP();

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'settings' );
		if ( isset( $_POST['options']['cawp_hidden'] ) ) {
			$message = "<div class='updated' id='cawp-autodismiss'><p>" . __( "Settings saved.", 'clicky-analytics' ) . "</p></div>";
			if ( ! ( isset( $_POST['cawp_security'] ) && wp_verify_nonce( $_POST['cawp_security'], 'cawp_form' ) ) ) {
				$message = "<div class='error' id='cawp-autodismiss'><p>" . __( "You don’t have permission to do this.", 'clicky-analytics' ) . "</p></div>";
			}
		}
		if ( ! $cawp->config->options['sitekey'] || ! $cawp->config->options['siteid'] ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'clicky-analytics' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'cawp_errors_debugging', false ), __( 'Debug', 'clicky-analytics' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'cawp_setup', false ), __( 'enter the credentials', 'clicky-analytics' ) ) ) );
		}
		?>
<form name="cawp_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
	<div class="wrap">
			<?php echo "<h2>" . __( "Clicky Analytics - Settings", 'clicky-analytics' ) . "</h2>"; ?><hr>
	</div>
	<div id="poststuff" class="cawp">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="settings-wrapper">
					<div class="inside">
					<?php if (isset($message)) echo $message; ?>
						<table class="cawp-settings-options">
							<tr>
								<td colspan="2"><?php echo "<h2>" . __( "Tracking Code Settings", 'clicky-analytics' ) . "</h2>"; ?></td>
							</tr>
							<tr>
												<tr>
													<td class="cawp-settings-title">
														<label for="site_jail"><?php _e("Tracking Code:", 'clicky-analytics' ); ?></label>
													</td>
													<td>
														<select id="tracking" name="options[tracking]">
																			<option value="1" <?php selected( 1, $options['tracking'] ); ?> title="Enabled">
																				<?php _e( "Enabled", 'clicky-analytics' )?>
																			</option>
																			<option value="0" <?php selected( 0, $options['tracking'] ); ?> title="Disabled">
																				<?php _e( "Disabled", 'clicky-analytics' )?>
																			</option>
														</select>
													 </td>
								</tr>

								<td colspan="2" class="cawp-settings-title">
									<div class="cawp-togglegroup">
										<input type="checkbox" name="options[track_username]" value="1" id="track_username" <?php checked( $options['track_username'], 1 ); ?>>
										<label for="track_username">
									        <?php _e ( "track usernames", 'clicky-analytics' );?>
									    </label>
										<div class="cawp-onoffswitch pull-right" aria-hidden="true">
											<div class="cawp-onoffswitch-label">
												<div class="cawp-onoffswitch-inner"></div>
												<div class="cawp-onoffswitch-switch"></div>
											</div>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="cawp-settings-title">
									<div class="cawp-togglegroup">
										<input type="checkbox" name="options[track_email]" value="1" id="track_email" <?php checked( $options['track_email'], 1 ); ?>>
										<label for="track_email">
									        <?php _e ( "track emails", 'clicky-analytics' );?>
									    </label>
										<div class="cawp-onoffswitch pull-right" aria-hidden="true">
											<div class="cawp-onoffswitch-label">
												<div class="cawp-onoffswitch-inner"></div>
												<div class="cawp-onoffswitch-switch"></div>
											</div>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="cawp-settings-title">
									<div class="cawp-togglegroup">
										<input type="checkbox" name="options[track_youtube]" value="1" id="track_youtube" <?php checked( $options['track_youtube'], 1 ); ?>>
										<label for="track_youtube">
									        <?php _e ( "track YouTube videos", 'clicky-analytics' );?>
									    </label>
										<div class="cawp-onoffswitch pull-right" aria-hidden="true">
											<div class="cawp-onoffswitch-label">
												<div class="cawp-onoffswitch-inner"></div>
												<div class="cawp-onoffswitch-switch"></div>
											</div>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="cawp-settings-title">
									<div class="cawp-togglegroup">
										<input type="checkbox" name="options[track_html5]" value="1" id="track_html5" <?php checked( $options['track_html5'], 1 ); ?>>
										<label for="track_html5">
									        <?php _e ( "track HTML5 videos", 'clicky-analytics' );?>
									    </label>
										<div class="cawp-onoffswitch pull-right" aria-hidden="true">
											<div class="cawp-onoffswitch-label">
												<div class="cawp-onoffswitch-inner"></div>
												<div class="cawp-onoffswitch-switch"></div>
											</div>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="cawp-settings-title">
									<?php echo __("Outboung links pattern:", 'clicky-analytics'); ?>
									<input type="text" style="text-align: left;" name="options[track_outbound]" value="<?php echo esc_attr($options['track_outbound']); ?>" size="20">
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr>
								</td>
							</tr>
							<tr>
								<td colspan="2"><?php echo "<h2>" . __( "Backend Permissions", 'clicky-analytics' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td class="roles cawp-settings-title">
									<label for="access_back"><?php _e("Show stats to:", 'clicky-analytics' ); ?>
									</label>
								</td>
								<td class="cawp-settings-roles">
									<table>
										<tr>
										<?php if ( ! isset( $wp_roles ) ) : ?>
											<?php $wp_roles = new WP_Roles(); ?>
										<?php endif; ?>
										<?php $i = 0; ?>
										<?php foreach ( $wp_roles->role_names as $role => $name ) : ?>
											<?php if ( 'subscriber' != $role ) : ?>
												<?php $i++; ?>
											<td>
												<label>
													<input type="checkbox" name="options[access_back][]" value="<?php echo $role; ?>" <?php if ( in_array($role,$options['access_back']) || 'administrator' == $role ) echo 'checked="checked"'; if ( 'administrator' == $role ) echo 'disabled="disabled"';?> /> <?php echo $name; ?>
												</label>
											</td>
											<?php endif; ?>
											<?php if ( 0 == $i % 4 ) : ?>
										</tr>
										<tr>
											<?php endif; ?>
										<?php endforeach; ?>
									</table>
								</td>
							</tr>
							<tr style="display: none;">
								<td colspan="2" class="cawp-settings-title">
									<div class="cawp-togglegroup">
										<input type="checkbox" name="options[backend_item_reports]" value="1" id="backend_item_reports" <?php checked( $options['backend_item_reports'], 1 ); ?>>
										<label for="backend_item_reports">
									        <?php _e ( "enable reports on Posts List and Pages List", 'clicky-analytics' );?>
									    </label>
										<div class="cawp-onoffswitch pull-right" aria-hidden="true">
											<div class="cawp-onoffswitch-label">
												<div class="cawp-onoffswitch-inner"></div>
												<div class="cawp-onoffswitch-switch"></div>
											</div>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="cawp-settings-title">
									<div class="cawp-togglegroup">
										<input type="checkbox" name="options[dashboard_widget]" value="1" id="dashboard_widget" <?php checked( $options['dashboard_widget'], 1 ); ?>>
										<label for="dashboard_widget">
									        <?php _e ( "enable the reports widget on main dashboard", 'clicky-analytics' );?>
									    </label>
										<div class="cawp-onoffswitch pull-right" aria-hidden="true">
											<div class="cawp-onoffswitch-label">
												<div class="cawp-onoffswitch-inner"></div>
												<div class="cawp-onoffswitch-switch"></div>
											</div>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2"><?php echo "<h2>" . __( "Frontend Permissions", 'clicky-analytics' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td class="roles cawp-settings-title">
									<label for="access_front"><?php _e("Show stats to:", 'clicky-analytics' ); ?>
									</label>
								</td>
								<td class="cawp-settings-roles">
									<table>
										<tr>
										<?php if ( ! isset( $wp_roles ) ) : ?>
											<?php $wp_roles = new WP_Roles(); ?>
										<?php endif; ?>
										<?php $i = 0; ?>
										<?php foreach ( $wp_roles->role_names as $role => $name ) : ?>
											<?php if ( 'subscriber' != $role ) : ?>
												<?php $i++; ?>
												<td>
												<label>
													<input type="checkbox" name="options[access_front][]" value="<?php echo $role; ?>" <?php if ( in_array($role,$options['access_front']) || 'administrator' == $role ) echo 'checked="checked"'; if ( 'administrator' == $role ) echo 'disabled="disabled"';?> /><?php echo $name; ?>
												  </label>
											</td>
											<?php endif; ?>
											<?php if ( 0 == $i % 4 ) : ?>
										 </tr>
										<tr>
											<?php endif; ?>
										<?php endforeach; ?>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="cawp-settings-title">
									<div class="cawp-togglegroup">
										<input type="checkbox" name="options[frontend_item_reports]" value="1" id="frontend_item_reports" <?php checked( $options['frontend_item_reports'], 1 ); ?>>
										<label for="frontend_item_reports">
									        <?php echo " ".__("enable the reports widget on frontend admin bar", 'clicky-analytics' );?>
									    </label>
										<div class="cawp-onoffswitch pull-right" aria-hidden="true">
											<div class="cawp-onoffswitch-label">
												<div class="cawp-onoffswitch-inner"></div>
												<div class="cawp-onoffswitch-switch"></div>
											</div>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr><?php echo "<h2>" . __( "Google Maps API", 'clicky-analytics' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td colspan="2" class="cawp-settings-title">
									<?php echo __("Maps API Key:", 'clicky-analytics'); ?>
									<input type="text" style="text-align: center;" name="options[maps_api_key]" value="<?php echo esc_attr($options['maps_api_key']); ?>" size="50">
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<hr>
								</td>
							</tr>
							<tr>
								<td colspan="2" class="submit">
									<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'clicky-analytics' ) ?>" />
								</td>
							</tr>
						</table>
						<input type="hidden" name="options[cawp_hidden]" value="Y">
						<?php wp_nonce_field('cawp_form','cawp_security'); ?>
</form>
<?php
		CAWP_Tools::load_view( 'admin/views/settings-sidebar.php', array() );
	}

	public static function errors_debugging() {
		$cawp = CAWP();

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$anonim = CAWP_Tools::anonymize_options( $cawp->config->options );

		$options = self::update_options( 'frontend' );
		if ( ! $cawp->config->options['sitekey'] || ! $cawp->config->options['siteid'] ) {
			$message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'clicky-analytics' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'cawp_errors_debugging', false ), __( 'Debug', 'clicky-analytics' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'cawp_setup', false ), __( 'enter the credentials', 'clicky-analytics' ) ) ) );
		}
		?>
<div class="wrap">
		<?php echo "<h2>" . __( "Clicky Analytics - Debug", 'clicky-analytics' ) . "</h2>"; ?>
</div>
<div id="poststuff" class="cawp">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">
			<div class="settings-wrapper">
				<div class="inside">
						<?php if (isset($message)) echo $message; ?>
						<?php $tabs = array( 'errors' => __( "Errors", 'clicky-analytics' ), 'config' => __( "Plugin Settings", 'clicky-analytics' ), 'sysinfo' => __( "System", 'clicky-analytics' ) ); ?>
						<?php self::navigation_tabs( $tabs ); ?>
						<div id="cawp-errors">
						<table class="cawp-settings-logdata">
							<tr>
								<td>
									<?php echo "<h2>" . __( "Detected Errors", 'clicky-analytics' ) . "</h2>"; ?>
								</td>
							</tr>
							<tr>
							<td>
								<?php $errors_count = CAWP_Tools::get_cache( 'errors_count' ); ?>
								<pre class="cawp-settings-logdata"><?php echo '<span>' . __("Count: ", 'clicky-analytics') . '</span>' . (int)$errors_count;?></pre>
								<?php $error = CAWP_Tools::get_cache( 'capi_errors' ) ?>
								<?php $error_code = isset( $error[0] ) ? $error[0] : 'None' ?>
								<?php $error_reason = ( isset( $error[1] ) && !empty($error[1]) ) ? print_r( $error[1], true) : 'None' ?>
								<?php $error_details = isset( $error[2] ) ? print_r( $error[2], true) : 'None' ?>
								<pre class="cawp-settings-logdata"><?php echo '<span>' . __("Error Code: ", 'clicky-analytics') . '</span>' . esc_html( $error_code );?></pre>
								<pre class="cawp-settings-logdata"><?php echo '<span>' . __("Error Reason: ", 'clicky-analytics') . '</span>' . "\n" . esc_html( $error_reason );?></pre>
								<?php $error_details = str_replace( 'Deconf_', 'Google_', $error_details); ?>
								<pre class="cawp-settings-logdata"><?php echo '<span>' . __("Error Details: ", 'clicky-analytics') . '</span>' . "\n" . esc_html( $error_details );?></pre>
								<br />
								<hr>
							</td>
							</tr>
							<tr>
								<td>
									<?php echo "<h2>" . __( "Sampled Data", 'clicky-analytics' ) . "</h2>"; ?>
								</td>
							</tr>
							<tr>
								<td>
									<?php $sampling = CAWP_TOOLS::get_cache( 'sampleddata' ); ?>
									<?php if ( $sampling ) :?>
									<?php printf( __( "Last Detected on %s.", 'clicky-analytics' ), '<strong>'. $sampling['date'] . '</strong>' );?>
									<br />
									<?php printf( __( "The report was based on %s of sessions.", 'clicky-analytics' ), '<strong>'. $sampling['percent'] . '</strong>' );?>
									<br />
									<?php printf( __( "Sessions ratio: %s.", 'clicky-analytics' ), '<strong>'. $sampling['sessions'] . '</strong>' ); ?>
									<?php else :?>
									<?php _e( "None", 'clicky-analytics' ); ?>
									<?php endif;?>
								</td>
							</tr>
						</table>
					</div>
					<div id="cawp-config">
						<table class="cawp-settings-options">
							<tr>
								<td><?php echo "<h2>" . __( "Plugin Configuration", 'clicky-analytics' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td>
									<pre class="cawp-settings-logdata"><?php echo esc_html(print_r($anonim, true));?></pre>
									<br />
									<hr>
								</td>
							</tr>
						</table>
					</div>
					<div id="cawp-sysinfo">
						<table class="cawp-settings-options">
							<tr>
								<td><?php echo "<h2>" . __( "System Information", 'clicky-analytics' ) . "</h2>"; ?></td>
							</tr>
							<tr>
								<td>
									<pre class="cawp-settings-logdata"><?php echo esc_html(CAWP_Tools::system_info());?></pre>
									<br />
									<hr>
								</td>
							</tr>
						</table>
					</div>
	<?php
			CAWP_Tools::load_view( 'admin/views/settings-sidebar.php', array() );
	}

	public static function setup() {
		$cawp = CAWP();

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options = self::update_options( 'setup' );
		printf( '<div id="capi-warning" class="updated"><p>%1$s <a href="https://deconf.com/clicky-analytics-dashboard-wordpress/">%2$s</a></p></div>', __( 'Loading the required libraries. If this results in a blank screen or a fatal error, try this solution:', 'clicky-analytics' ), __( 'Library conflicts between WordPress plugins', 'clicky-analytics' ) );
		if ( null === $cawp->capi_controller ) {
			$cawp->capi_controller = new CAWP_CAPI_Controller();
		}
		echo '<script type="text/javascript">jQuery("#capi-warning").hide()</script>';

		if ( isset( $_POST['Clear'] ) ) {
			if ( isset( $_POST['cawp_security'] ) && wp_verify_nonce( $_POST['cawp_security'], 'cawp_form' ) ) {
				CAWP_Tools::clear_cache();
				$message = "<div class='updated' id='cawp-autodismiss'><p>" . __( "Cleared Cache.", 'clicky-analytics' ) . "</p></div>";
			} else {
				$message = "<div class='error' id='cawp-autodismiss'><p>" . __( "You don’t have permission to do this.", 'clicky-analytics' ) . "</p></div>";
			}
		}

		if ( isset( $_POST['Reset_Err'] ) ) {
			if ( isset( $_POST['cawp_security'] ) && wp_verify_nonce( $_POST['cawp_security'], 'cawp_form' ) ) {

				if ( CAWP_Tools::get_cache( 'capi_errors' ) ) {

					$info = CAWP_Tools::system_info();
					$info .= 'CAWP Version: ' . CAWP_CURRENT_VERSION;

					$sep = "\n---------------------------\n";
					$error_report = $sep . print_r( CAWP_Tools::get_cache( 'capi_errors' ), true );
					$error_report .= $sep . CAWP_Tools::get_cache( 'errors_count' );
					$error_report .= $sep . $info;

					$error_report = urldecode( $error_report );

					$url = CAWP_ENDPOINT_URL . 'cawp-report.php';
					/* @formatter:off */
					$response = wp_remote_post( $url, array(
							'method' => 'POST',
							'timeout' => 45,
							'redirection' => 5,
							'httpversion' => '1.0',
							'blocking' => true,
							'headers' => array(),
							'body' => array( 'error_report' => $error_report ),
							'cookies' => array()
						)
					);
				}

				/* @formatter:on */
				CAWP_Tools::delete_cache( 'capi_errors' );
				delete_option( 'cawp_got_updated' );
				$message = "<div class='updated' id='cawp-autodismiss'><p>" . __( "All errors reseted.", 'clicky-analytics' ) . "</p></div>";
			} else {
				$message = "<div class='error' id='cawp-autodismiss'><p>" . __( "You don’t have permission to do this.", 'clicky-analytics' ) . "</p></div>";
			}
		}

		if ( isset( $_POST['options']['cawp_hidden'] ) && ! isset( $_POST['Reset_Err'] ) && ! isset( $_POST['Clear'] ) ) {
			$message = "<div class='updated' id='cawp-autodismiss'><p>" . __( "Settings saved.", 'clicky-analytics' ) . "</p></div>";
			if ( ! ( isset( $_POST['cawp_security'] ) && wp_verify_nonce( $_POST['cawp_security'], 'cawp_form' ) ) ) {
				$message = "<div class='error' id='cawp-autodismiss'><p>" . __( "You don’t have permission to do this.", 'clicky-analytics' ) . "</p></div>";
			}
		}
		?>
	<div class="wrap">
	<?php echo "<h2>" . __( "Clicky Analytics - Setup", 'clicky-analytics' ) . "</h2>"; ?>
					<hr>
					</div>
					<div id="poststuff" class="cawp">
						<div id="post-body" class="metabox-holder columns-2">
							<div id="post-body-content">
								<div class="settings-wrapper">
									<div class="inside">
										<?php if ( $cawp->capi_controller->capi_errors_handler() ) : ?>
													<?php $message = sprintf( '<div class="error"><p>%s</p></div>', sprintf( __( 'Something went wrong, check %1$s or %2$s.', 'clicky-analytics' ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'cawp_errors_debugging', false ), __( 'Debug', 'clicky-analytics' ) ), sprintf( '<a href="%1$s">%2$s</a>', menu_page_url( 'cawp_setup', false ), __( 'authorize the plugin', 'clicky-analytics' ) ) ) );?>
										<?php endif;?>
										<?php if ( isset( $message ) ) :?>
											<?php echo $message;?>
										<?php endif; ?>
										<form name="cawp_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
											<input type="hidden" name="options[cawp_hidden]" value="Y">
											<?php wp_nonce_field('cawp_form','cawp_security'); ?>
											<table class="cawp-settings-options">
												<tr>
													<td colspan="2">
														<?php echo "<h2>" . __( "Clicky Analytics Credentials", 'clicky-analytics' ) . "</h2>";?>
													</td>
												</tr>
												<tr>
													<td colspan="2" class="cawp-settings-info">
														<?php printf(__('You need to create a %1$s and follow %2$s before proceeding to authorization.', 'clicky-analytics'), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/go/clicky/', __("Clicky Analytics account", 'clicky-analytics')), sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://deconf.com/clicky-analytics-dashboard-wordpress/', __("this tutorial", 'clicky-analytics')));?>
													</td>
												<tr>
													<td class="cawp-settings-title">
														<?php echo __("Site ID:", 'clicky-analytics'); ?>
													</td>
													<td>
														<input type="text" style="text-align: left;" name="options[siteid]" value="<?php echo esc_attr($options['siteid']); ?>" size="30">
													</td>
												</tr>
												<tr>
													<td class="cawp-settings-title">
														<?php echo __("Site Key:", 'clicky-analytics'); ?>
													</td>
													<td>
														<input type="text" style="text-align: left;" name="options[sitekey]" value="<?php echo esc_attr($options['sitekey']); ?>" size="30">
													</td>
												</tr>
												<tr>
													<td colspan="2">
														<input type="submit" name="Clear" class="button button-secondary" value="<?php _e( "Clear Cache", 'clicky-analytics' ); ?>" />
														<input type="submit" name="Reset_Err" class="button button-secondary" value="<?php _e( "Report & Reset Errors", 'clicky-analytics' ); ?>" />
													</td>
												</tr>
												<tr>
													<td colspan="2">
													</td>
												</tr>
												<tr>
													<td colspan="2">
														<hr><?php echo "<h2>" . __( "Appearance", 'clicky-analytics' ) . "</h2>"; ?></td>
												</tr>
												<tr>
													<td class="cawp-settings-title">
														<label for="theme_color"><?php _e("Chart Color:", 'clicky-analytics' ); ?></label>
													</td>
													<td>
														<input type="text" id="theme_color" class="theme_color" name="options[theme_color]" value="<?php echo esc_attr($options['theme_color']); ?>" size="10">
													</td>
												</tr>
												<tr>
													<td colspan="2">
													</td>
												</tr>
												<tr>
													<td colspan="2" class="submit">
														<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'clicky-analytics' ) ?>" />
													</td>
												</tr>
											</table>
										</form>
				<?php CAWP_Tools::load_view( 'admin/views/settings-sidebar.php', array() ); ?>
				<?php return; ?>
											</table>
										</form>
			<?php

			CAWP_Tools::load_view( 'admin/views/settings-sidebar.php', array() );
	}
}
