<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
require_once 'functions.php';
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
if ( isset( $_POST['Clear'] ) ) {
	ca_clear_cache();
	?>
<div class="updated">
	<p>
		<strong><?php _e('Cleared Cache.', 'clicky-analytics' ); ?></strong>
	</p>
</div>
<?php
}
if ( ca_safe_get( 'ca_hidden' ) == 'Y' ) {
	if ( isset( $_POST['cadash_security'] ) && wp_verify_nonce( $_POST['cadash_security'], 'cadash_form' ) ) {
		// Form data sent
		$sitekey = ca_safe_get( 'ca_sitekey' );
		update_option( 'ca_sitekey', sanitize_text_field( $sitekey ) );
		$siteid = ca_safe_get( 'ca_siteid' );
		update_option( 'ca_siteid', sanitize_text_field( $siteid ) );
		$dashaccess = ca_safe_get( 'ca_access' );
		update_option( 'ca_access', $dashaccess );
		$ca_pgd = ca_safe_get( 'ca_pgd' );
		update_option( 'ca_pgd', $ca_pgd );
		$ca_rd = ca_safe_get( 'ca_rd' );
		update_option( 'ca_rd', $ca_rd );
		$ca_sd = ca_safe_get( 'ca_sd' );
		update_option( 'ca_sd', $ca_sd );
		$ca_frontend = ca_safe_get( 'ca_frontend' );
		update_option( 'ca_frontend', $ca_frontend );
		$ca_cachetime = ca_safe_get( 'ca_cachetime' );
		update_option( 'ca_cachetime', $ca_cachetime );
		$ca_tracking = ca_safe_get( 'ca_tracking' );
		update_option( 'ca_tracking', $ca_tracking );
		$ca_track_username = ca_safe_get( 'ca_track_username' );
		update_option( 'ca_track_username', $ca_track_username );
		$ca_track_email = ca_safe_get( 'ca_track_email' );
		update_option( 'ca_track_email', $ca_track_email );
		$ca_track_youtube = ca_safe_get( 'ca_track_youtube' );
		update_option( 'ca_track_youtube', $ca_track_youtube );
		$ca_track_html5 = ca_safe_get( 'ca_track_html5' );
		update_option( 'ca_track_html5', $ca_track_html5 );
		$ca_track_olp = ca_safe_get( 'ca_track_olp' );
		update_option( 'ca_track_olp', sanitize_text_field( $ca_track_olp ) );
		if ( ! isset( $_POST['Clear'] ) ) {
			?>
<div class="updated">
	<p>
		<strong><?php _e('Options saved.', 'clicky-analytics'); ?></strong>
	</p>
</div>
<?php
		}
	} else {
		?>
<div class="error">
	<p>
		<strong><?php _e('Cheating Huh?', 'clicky-analytics' ); ?></strong>
	</p>
</div>
<?php
	}
}
if ( ! get_option( 'ca_access' ) ) {
	update_option( 'ca_access', "manage_options" );
}
$sitekey = get_option( 'ca_sitekey' );
$siteid = get_option( 'ca_siteid' );
$dashaccess = get_option( 'ca_access' );
$ca_pgd = get_option( 'ca_pgd' );
$ca_rd = get_option( 'ca_rd' );
$ca_sd = get_option( 'ca_sd' );
$ca_frontend = get_option( 'ca_frontend' );
$ca_cachetime = get_option( 'ca_cachetime' );
$ca_tracking = get_option( 'ca_tracking' );
$ca_track_username = get_option( 'ca_track_username' );
$ca_track_email = get_option( 'ca_track_email' );
$ca_track_youtube = get_option( 'ca_track_youtube' );
$ca_track_html5 = get_option( 'ca_track_html5' );
$ca_track_olp = get_option( 'ca_track_olp' );
if ( is_rtl() ) {
	$float_main = "right";
	$float_note = "left";
} else {
	$float_main = "left";
	$float_note = "right";
}
?>
<div class="wrap">
		<?php echo "<h2>" . __( "Clicky Analytics Settings",'clicky-analytics' ) . "</h2>"; ?><hr>
</div>
<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
		<div id="post-body-content">
			<div class="settings-wrapper">
				<div class="inside">
					<form name="cadash_form" method="post" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
						<?php wp_nonce_field('cadash_form','cadash_security'); ?>
						<input type="hidden" name="ca_hidden" value="Y">
		<?php echo "<h2>". __( 'Clicky Analytics API', 'clicky-analytics' )."</h2>"; ?>
        <?php echo __("You should watch this", 'clicky-analytics')." <a href='https://deconf.com/clicky-analytics-dashboard-wordpress/' target='_blank'>". __("Step by step video tutorial")."</a> ".__("to learn how to properly setup this plugin", 'clicky-analytics').". ".__("If you don't have a Clicky Account, you can", 'clicky-analytics')." <a href='https://clicky.com/66508224' target='_blank'>". __("create one here")."</a>.";?>
		<p><?php echo "<strong>".__("Site ID:", 'clicky-analytics')." </strong>"; ?><input type="text" name="ca_siteid" value="<?php echo esc_attr($siteid); ?>" size="30">
						</p>
						<p><?php echo "<strong>".__("Site Key:", 'clicky-analytics')." </strong>"; ?><input type="text" name="ca_sitekey" value="<?php echo esc_attr($sitekey); ?>" size="30">
						</p>
						<hr>
		<?php echo "<h2>" . __( 'Access Level', 'clicky-analytics' ). "</h2>";?>
		<p><?php _e("View Access Level: ", 'clicky-analytics' ); ?>
		<select id="ca_access" name="ca_access">
								<option value="manage_options" <?php selected( $dashaccess, "manage_options" ); ?>><?php echo __("Administrators", 'clicky-analytics');?></option>
								<option value="edit_pages" <?php selected( $dashaccess, "edit_pages" ); ?>><?php echo __("Editors", 'clicky-analytics');?></option>
								<option value="publish_posts" <?php selected( $dashaccess, "publish_posts" ); ?>><?php echo __("Authors", 'clicky-analytics');?></option>
								<option value="edit_posts" <?php selected( $dashaccess, "edit_posts" ); ?>><?php echo __("Contributors", 'clicky-analytics');?></option>
							</select>
						</p>
						<hr>
		<?php echo "<h2>" . __( 'Frontend Settings', 'clicky-analytics' ). "</h2>";?>
		<p>
							<input name="ca_frontend" type="checkbox" id="ca_frontend" value="1" <?php if (get_option('ca_frontend')) echo " checked='checked'"; ?> /><?php _e(" show page visits and top searches in frontend (after each article)", 'clicky-analytics' ); ?></p>
		<?php echo "<h2>" . __( 'Backend Settings', 'clicky-analytics' ). "</h2>";?>
		<p>
							<input name="ca_pgd" type="checkbox" id="ca_pgd" value="1" <?php if (get_option('ca_pgd')) echo " checked='checked'"; ?> /><?php _e(" show top pages", 'clicky-analytics' ); ?></p>
						<p>
							<input name="ca_rd" type="checkbox" id="ca_rd" value="1" <?php if (get_option('ca_rd')) echo " checked='checked'"; ?> /><?php _e(" show top referrers", 'clicky-analytics' ); ?></p>
						<p>
							<input name="ca_sd" type="checkbox" id="ca_sd" value="1" <?php if (get_option('ca_sd')) echo " checked='checked'"; ?> /><?php _e(" show top searches", 'clicky-analytics' ); ?></p>
						<hr>
		<?php echo "<h2>" . __( 'Cache Settings', 'clicky-analytics' ). "</h2>";?>
		<p><?php _e("Cache Time: ", 'clicky-analytics' ); ?>
		<select id="ca_cachetime" name="ca_cachetime">
								<option value="1800" <?php selected( $ca_cachetime, 1800 ); ?>><?php echo __("30 minutes", 'clicky-analytics');?></option>
								<option value="3600" <?php selected( $ca_cachetime, 3600 ); ?>><?php echo __("1 hour", 'clicky-analytics');?></option>
								<option value="10800" <?php selected( $ca_cachetime, 10800 ); ?>><?php echo __("3 hours", 'clicky-analytics');?></option>
							</select>
						</p>
						<hr>
		<?php echo "<h2>" . __( 'Clicky Analytics Tracking', 'clicky-analytics' ). "</h2>";?>

		<p><?php _e("Enable Tracking: ", 'clicky-analytics' ); ?>
		<select id="ca_tracking" name="ca_tracking">
								<option value="1" <?php selected( $ca_tracking, 1 ); ?>><?php echo __("Enabled", 'clicky-analytics');?></option>
								<option value="2" <?php selected( $ca_tracking, 2 ); ?>><?php echo __("Disabled", 'clicky-analytics');?></option>
							</select>
						</p>
						<p>
							<input name="ca_track_username" type="checkbox" id="ca_track_username" value="1" <?php if (get_option('ca_track_username')) echo " checked='checked'"; ?> /><?php _e(" track usernames", 'clicky-analytics' ); ?></p>
						<p>
							<input name="ca_track_email" type="checkbox" id="ca_track_email" value="1" <?php if (get_option('ca_track_email')) echo " checked='checked'"; ?> /><?php _e(" track emails", 'clicky-analytics' ); ?></p>
						<p>
							<input name="ca_track_youtube" type="checkbox" id="ca_track_youtube" value="1" <?php if (get_option('ca_track_youtube')) echo " checked='checked'"; ?> /><?php _e(" track Youtube videos", 'clicky-analytics' ); ?></p>
						<p>
							<input name="ca_track_html5" type="checkbox" id="ca_track_html5" value="1" <?php if (get_option('ca_track_html5')) echo " checked='checked'"; ?> /><?php _e(" track HTML5 videos", 'clicky-analytics' ); ?></p>
						<p><?php _e("Outbound Link Pattern:", 'clicky-analytics'); ?> <input type="text" name="ca_track_olp" value="<?php echo esc_attr($ca_track_olp); ?>" size="30">
						</p>
						<hr>
						<p class="submit">
							<input type="submit" name="Submit" class="button button-primary" value="<?php _e('Save Changes', 'clicky-analytics' ) ?>" />
							&nbsp;&nbsp;&nbsp;
							<input type="submit" name="Clear" class="button button-secondary" value="<?php _e('Clear Cache', 'clicky-analytics' ) ?>" />
						</p>
					</form>
				</div>
			</div>
		</div>
		<div id="postbox-container-1" class="postbox-container">
			<div class="meta-box-sortables">
				<div class="postbox">
					<h3>
						<span><?php _e("WordPress Plugins",'clicky-analytics')?></span>
					</h3>
					<div class="inside">
						<div class="cadash-title">
							<a href="https://wordpress.org/plugins/analytics-insights/"><img src="<?php echo plugins_url( 'images/aiwp.png' , __FILE__ ); ?>" /></a>
						</div>
						<div class="cadash-desc">
								<?php printf(__('%s - Connects Google Analytics with your WordPress site.', 'clicky-analytics'), sprintf('<a href="https://wordpress.org/plugins/analytics-insights/">%s</a>', __('Analytics Insights', 'clicky-analytics')));?>
						</div>
						<br />
						<div class="cadash-title">
							<a href="https://wordpress.org/plugins/search-engine-insights/"><img src="<?php echo plugins_url( 'images/seiwp.png' , __FILE__ ); ?>" /></a>
						</div>
						<div class="cadash-desc">
								<?php printf(__('%s - Add your website to Google Search Console!', 'clicky-analytics'), sprintf('<a href="https://wordpress.org/plugins/search-engine-insights/">%s</a>', __('Search Engine Insights', 'clicky-analytics')));?>
					</div>
					</div>
				</div>
				<div class="postbox">
					<h3>
						<span><?php _e("Stay Updated",'clicky-analytics')?></span>
					</h3>
					<div class="inside">
						<div class="cadash-desc">
							<div class="g-ytsubscribe" data-channel="TheDeConf" data-layout="default" data-count="default"></div>
							<script src="https://apis.google.com/js/platform.js" async defer></script>
						</div>
						<br />
						<div class="cadash-desc">
							<a href="https://twitter.com/deconfcom" class="twitter-follow-button" data-show-screen-name="false"></a>
							<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
						</div>
					</div>
				</div>
			</div>
		</div>