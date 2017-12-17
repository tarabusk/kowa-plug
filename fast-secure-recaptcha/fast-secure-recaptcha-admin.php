<?php
/*
Fast Secure reCAPTCHA (admin settings page)
*/

//do not allow direct access
if ( strpos(strtolower($_SERVER['SCRIPT_NAME']),strtolower(basename(__FILE__))) ) {
    header('HTTP/1.0 403 Forbidden');
	exit('Forbidden');
}

  if (isset($_POST['submit'])) {

      if ( function_exists('current_user_can') && !current_user_can('manage_options') )
            die(__('You do not have permissions for managing this option', 'fast-secure-recaptcha'));

        check_admin_referer( 'fast-secure-recaptcha-options_update'); // nonce
   // post changes to the options array
   $optionarray_update = array(
         'donated' =>                    (isset( $_POST['fs_recaptcha_donated'] ) ) ? 'true' : 'false',// true or false
         'site_key'	=>      sanitize_text_field( $_POST['fs_recaptcha_site_key'] ),
         'secret_key'	=>  sanitize_text_field( $_POST['fs_recaptcha_secret_key'] ),
         'bypass_comment' =>             (isset( $_POST['fs_recaptcha_bypass_comment'] ) ) ? 'true' : 'false',
         'comment' =>                    (isset( $_POST['fs_recaptcha_comment'] ) ) ? 'true' : 'false',
         'login' =>                      (isset( $_POST['fs_recaptcha_login'] ) ) ? 'true' : 'false',
         'register' =>                   (isset( $_POST['fs_recaptcha_register'] ) ) ? 'true' : 'false',
         'bp_register' =>                (isset( $_POST['fs_recaptcha_bp_register'] ) ) ? 'true' : 'false',
         'ms_register' =>                (isset( $_POST['fs_recaptcha_ms_register'] ) ) ? 'true' : 'false',
         'wpforo_register' =>            (isset( $_POST['fs_recaptcha_wpforo_register'] ) ) ? 'true' : 'false',
         'lostpwd' =>                    (isset( $_POST['fs_recaptcha_lostpwd'] ) ) ? 'true' : 'false',
         'wc_checkout' =>                (isset( $_POST['fs_recaptcha_wc_checkout'] ) ) ? 'true' : 'false',
         'jetpack' =>                    (isset( $_POST['fs_recaptcha_jetpack'] ) ) ? 'true' : 'false',
         'bbpress_topic' =>              (isset( $_POST['fs_recaptcha_bbpress_topic'] ) ) ? 'true' : 'false',
         'bbpress_reply' =>              (isset( $_POST['fs_recaptcha_bbpress_reply'] ) ) ? 'true' : 'false',
         'captcha_small' =>              (isset( $_POST['fs_recaptcha_captcha_small'] ) ) ? 'true' : 'false',
         'language'	=>      sanitize_text_field( $_POST['fs_recaptcha_language'] ),
         'dark' =>                       (isset( $_POST['fs_recaptcha_dark'] ) && $_POST['fs_recaptcha_dark'] == 'true' ) ? 'true' : 'false',
         'external_css' =>               (isset( $_POST['fs_recaptcha_external_css'] ) ) ? 'true' : 'false',
         'network_individual_on' =>      (isset( $_POST['fs_recaptcha_network_individual_on'] ) ) ? 'true' : 'false',
         'network_keys_copy' =>          (isset( $_POST['fs_recaptcha_network_keys_copy'] ) ) ? 'true' : 'false',
         'text_incorrect' =>sanitize_text_field( $_POST['fs_recaptcha_text_incorrect']),
         'text_error' =>    sanitize_text_field( $_POST['fs_recaptcha_text_error']),
                   );

   // deal with quotes
   foreach($optionarray_update as $key => $val) {
          $optionarray_update[$key] = str_replace('&quot;','"',$val);
   }

   // update the settings then set the options array
   $network_individual_on = false;
   if ($fs_recaptcha_networkwide && get_current_blog_id() == 1) {
            if ($optionarray_update['network_individual_on'] == 'true')
                 $network_individual_on = true;
            // multisite with network activation individual site control, this is main site
            update_site_option('fast_secure_recaptcha', $optionarray_update);
            $fs_recaptcha_opt = get_site_option('fast_secure_recaptcha');
   } else if ($fs_recaptcha_networkwide && get_current_blog_id() > 1) {
        $fs_recaptcha_main_opt = get_site_option('fast_secure_recaptcha');
        if ($fs_recaptcha_main_opt['network_individual_on'] == 'true') {
                $network_individual_on = true;
                $optionarray_update['network_individual_on'] = $fs_recaptcha_main_opt['network_individual_on'];
                $optionarray_update['network_keys_copy'] = $fs_recaptcha_main_opt['network_keys_copy'];
               // multisite with network activation individual site control, this is not main site
               update_option('fast_secure_recaptcha', $optionarray_update);
               $fs_recaptcha_opt = get_option('fast_secure_recaptcha');
        } else {
                // multisite with network activation master site control, update master site settings
                update_site_option('fast_secure_recaptcha', $optionarray_update);
                $fs_recaptcha_opt = get_site_option('fast_secure_recaptcha');
        }
   } else {
           // no multisite
           update_option('fast_secure_recaptcha', $optionarray_update);
           $fs_recaptcha_opt = get_option('fast_secure_recaptcha');
   }

    // strip slashes on get options array
    foreach($fs_recaptcha_opt as $key => $val) {
           $fs_recaptcha_opt[$key] = stripslashes($val);
    }


  } // end if (isset($_POST['submit']))
?>
<?php if ( !empty($_POST ) ) : ?>
<div id="message" class="updated"><p><strong><?php _e('Your settings have been saved.', 'fast-secure-recaptcha') ?></strong></p></div>
<?php endif; ?>
<div class="wrap">
<h2><?php _e('Fast Secure reCAPTCHA settings', 'fast-secure-recaptcha') ?></h2>

<script type="text/javascript">
    function toggleVisibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
    }
</script>

<?php
     // find out if multisite with network activation master site control
     $network_individual_on = false;
     if ($fs_recaptcha_networkwide && get_current_blog_id() > 1) {
        $fs_recaptcha_main_opt = get_site_option('fast_secure_recaptcha');
        if ($fs_recaptcha_main_opt['network_individual_on'] == 'true') {
              $network_individual_on = true;
        }
     }

  // get the options
   if ( $fs_recaptcha_networkwide && ! $network_individual_on ) {
        // multisite with network activation master site control
             $this_blog_id = get_current_blog_id();
             if ($this_blog_id > 1 ) {
               echo '<div class="fs-notice">';
		       echo __( 'Fast Secure reCAPTCHA is Network Activated and Main site configured. It is not necessary to change any settings here.', 'fast-secure-recaptcha' ).' ';
               echo __( 'Settings are controlled at the main site and are Networkwide. If you want individual sites to each have their own unique settings: go to the main site Fast Secure reCAPTCHA settings and enable "Allow Multisite network activated sites to have individual Fast Secure reCAPTCHA settings".', 'fast-secure-recaptcha' );

	       echo "</div>\n";
               return;
             }
   }

$si_captcha_plugin_url = plugin_dir_url( __FILE__ );

if (function_exists('get_transient')) {
  require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

  // Before, try to access the data, check the cache.
  if (false === ($api = get_transient('fast_secure_recaptcha_info'))) {
    // The cache data doesn't exist or it's expired.

    $api = plugins_api('plugin_information', array('slug' => 'fast-secure-recaptcha' ));
    if ( !is_wp_error($api) ) {
      // cache isn't up to date, write this fresh information to it now to avoid the query for xx time.
      $myexpire = 60 * 15; // Cache data for 15 minutes
      set_transient('fast_secure_recaptcha_info', $api, $myexpire);
    }
  }
  if ( !is_wp_error($api) ) {
	$plugins_allowedtags = array('a' => array('href' => array(), 'title' => array(), 'target' => array()),
								'abbr' => array('title' => array()), 'acronym' => array('title' => array()),
								'code' => array(), 'pre' => array(), 'em' => array(), 'strong' => array(),
								'div' => array(), 'p' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(),
								'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(),
								'img' => array('src' => array(), 'class' => array(), 'alt' => array()));
	//Sanitize HTML
	foreach ( (array)$api->sections as $section_name => $content )
		$api->sections[$section_name] = wp_kses($content, $plugins_allowedtags);
	foreach ( array('version', 'author', 'requires', 'tested', 'homepage', 'downloaded', 'slug') as $key )
		$api->$key = wp_kses($api->$key, $plugins_allowedtags);

      if ( ! empty($api->downloaded) ) {
        echo sprintf(__('Downloaded %s times', 'fast-secure-recaptcha'),number_format_i18n($api->downloaded));
        echo '.';
      }
?>
		<?php if ( ! empty($api->rating) ) : ?>
		<div class="fs-star-holder" title="<?php echo esc_attr(sprintf(__('(Average rating based on %s ratings)', 'fast-secure-recaptcha'),number_format_i18n($api->num_ratings))); ?>">
			<div class="fs-star fs-star-rating" style="width: <?php echo esc_attr($api->rating) ?>px"></div>
			<div class="fs-star fs-star5"><img src="<?php echo $si_captcha_plugin_url; ?>star.png" alt="<?php _e('5 stars', 'fast-secure-recaptcha') ?>" /></div>
			<div class="fs-star fs-star4"><img src="<?php echo $si_captcha_plugin_url; ?>star.png" alt="<?php _e('4 stars', 'fast-secure-recaptcha') ?>" /></div>
			<div class="fs-star fs-star3"><img src="<?php echo $si_captcha_plugin_url; ?>star.png" alt="<?php _e('3 stars', 'fast-secure-recaptcha') ?>" /></div>
			<div class="fs-star fs-star2"><img src="<?php echo $si_captcha_plugin_url; ?>star.png" alt="<?php _e('2 stars', 'fast-secure-recaptcha') ?>" /></div>
			<div class="fs-star fs-star1"><img src="<?php echo $si_captcha_plugin_url; ?>star.png" alt="<?php _e('1 star', 'fast-secure-recaptcha') ?>" /></div>
		</div>
		<small><?php echo sprintf(__('(Average rating based on %s ratings)', 'fast-secure-recaptcha'),number_format_i18n($api->num_ratings)); ?> <a target="_blank" href="https://wordpress.org/support/plugin/fast-secure-recaptcha/reviews/"> <?php _e('rate', 'fast-secure-recaptcha') ?></a></small>
        <br />
		<?php endif; ?>

<?php
  } // if ( !is_wp_error($api)
 }// end if (function_exists('get_transient'

$fs_recaptcha_update = '';
if (isset($api->version)) {
 if ( version_compare($api->version, $fs_recaptcha_version, '>') ) {
     $fs_recaptcha_update = ', <a href="'.admin_url( 'plugins.php' ).'">'.sprintf(__('a newer version is available: %s', 'fast-secure-recaptcha'),$api->version).'</a>';
     echo '<div id="message" class="updated">';
     echo '<a href="'.admin_url( 'plugins.php' ).'">'.sprintf(__('A newer version of Fast Secure reCAPTCHA is available: %s', 'fast-secure-recaptcha'),$api->version).'</a>';
     echo "</div>\n";
  }else{
     $fs_recaptcha_update = ' '. __('(latest version)', 'fast-secure-recaptcha');
  }
}
?>

<p>
<?php echo __('Version:', 'fast-secure-recaptcha'). ' '.$fs_recaptcha_version.$fs_recaptcha_update; ?> |
<a href="https://wordpress.org/plugins/fast-secure-recaptcha/changelog/" target="_blank"><?php echo __('Changelog', 'fast-secure-recaptcha'); ?></a> |
<a href="https://wordpress.org/plugins/fast-secure-recaptcha/faq/" target="_blank"><?php echo __('FAQ', 'fast-secure-recaptcha'); ?></a> |
<a href="https://wordpress.org/support/plugin/fast-secure-recaptcha/reviews/" target="_blank"><?php echo __('Rate This', 'fast-secure-recaptcha'); ?></a> |
<a href="https://wordpress.org/support/plugin/fast-secure-recaptcha" target="_blank"><?php echo __('Support', 'fast-secure-recaptcha'); ?></a> |
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2NA2XBX9WG98L" target="_blank"><?php echo __('Donate', 'fast-secure-recaptcha'); ?></a> |
<a href="http://www.642weather.com/weather/scripts.php" target="_blank"><?php echo __('Mikes Free PHP Scripts', 'fast-secure-recaptcha'); ?></a>
</p>


<form name="formoptions" action="<?php echo admin_url( 'options-general.php?page=fast-secure-recaptcha/fast-secure-recaptcha.php' ); ?>" method="post">
        <input type="hidden" name="action" value="update" />
        <input type="hidden" name="form_type" value="upload_options" />
        <?php wp_nonce_field('fast-secure-recaptcha-options_update');

 if ( is_multisite() && (! $fs_recaptcha_networkwide ) ) {
        // multisite without network activation
               echo '<div class="fs-notice">';
		       echo __( '<strong>Individual Site Settings Enabled</strong><br />Note: Fast Secure reCAPTCHA is not Network Activated, this means each site will have individual Fast Secure reCAPTCHA settings.', 'fast-secure-recaptcha' ).' ';
               echo __( 'If you want it this way, that is OK, but if you want the master site to control all the sites: go to the main site, then Network Activate this plugin, then go to Fast Secure reCAPTCHA settings and disable "Allow Multisite network activated sites to have individual Fast Secure reCAPTCHA settings".', 'fast-secure-recaptcha' );
	       echo "</div>\n";
   }


    ?>
        <p class="submit">
          <input type="submit" name="submit"class="button button-primary" value="<?php _e('Save Changes', 'fast-secure-recaptcha') ?>" />
        </p>

        <fieldset class="options">

        <table width="100%" cellspacing="2" cellpadding="5" class="form-table">

     <tr>
       <th scope="row" style="width: 75px;"><?php _e('API Keys:', 'fast-secure-recaptcha'); ?></th>
      <td>


    	<?php
		if ( $fs_recaptcha_opt['site_key'] == '' || $fs_recaptcha_opt['secret_key'] == '' ) {

			echo '<div class="fs-notice">';
			echo __( 'Warning: reCAPTCHA V2 API key(s) missing. Please enter your Google reCAPTCHA V2 API keys for this site.', 'fast-secure-recaptcha' );
			echo "</div>\n";

           if ($fs_recaptcha_networkwide && $network_individual_on && $fs_recaptcha_main_opt['network_keys_copy'] == 'true'  ) {
              // multisite with network activation and individual site control turned on
               echo '<div class="fs-notice">';
			   echo __( 'Click Save Settings to automatically copy API keys from the main site, or if you have Domain Mapping enabled, you can enter new Google reCAPTCHA V2 API keys for this site.', 'fast-secure-recaptcha' );
			   echo "</div>\n";
            }
		}
		?>

		<label for="fs_recaptcha_site_key"><?php _e( 'reCAPTCHA V2 Site Key', 'fast-secure-recaptcha' ); ?>:</label>
		<input name="fs_recaptcha_site_key" id="fs_recaptcha_site_key" type="text" value="<?php echo $fs_recaptcha_opt['site_key']; ?>" size="50" />
		<a style="cursor:pointer;" title="<?php esc_attr_e( 'Click for Help!', 'fast-secure-recaptcha' ); ?>" onclick="toggleVisibility('fs_recaptcha_site_key_tip');"><?php _e( 'help', 'fast-secure-recaptcha' ); ?></a>
		<div class="fscf_tip" id="fs_recaptcha_site_key_tip">
		<?php _e( 'The key in the HTML code your site serves to users', 'fast-secure-recaptcha' ); ?>
		</div>
		<br />

        <label for="fs_recaptcha_secret_key"><?php _e( 'reCAPTCHA V2 Secret Key', 'fast-secure-recaptcha' ); ?>:</label>
		<input name="fs_recaptcha_secret_key" id="fs_recaptcha_secret_key" type="text" value="<?php echo $fs_recaptcha_opt['secret_key']; ?>" size="50" />
		<a style="cursor:pointer;" title="<?php esc_attr_e( 'Click for Help!', 'fast-secure-recaptcha' ); ?>" onclick="toggleVisibility('fs_recaptcha_secret_key_tip');"><?php _e( 'help', 'fast-secure-recaptcha' ); ?></a>
		<div class="fscf_tip" id="fs_recaptcha_secret_key_tip">
		<?php _e( 'For communication between your site and Google.  Be sure to keep it a secret.', 'fast-secure-recaptcha' ); ?>
		</div>
		<br />

        <a href="https://www.google.com/recaptcha/admin/create" target="_new"><?php _e( 'Get reCAPTCHA V2 keys for your site here', 'fast-secure-recaptcha' ); ?></a><br />
        <?php _e( 'Note: Do not copy any google HTML code to your site HTML. Google instructions might say to, but this plugin does all that for you!', 'fast-secure-recaptcha' ); ?>
        <br />

    </td>
    </tr>

     <tr>
       <th scope="row" style="width: 75px;"><?php _e('Enable reCAPTCHA:', 'fast-secure-recaptcha'); ?></th>
      <td>

    <input name="fs_recaptcha_login" id="fs_recaptcha_login" type="checkbox" <?php if ( $fs_recaptcha_opt['login'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_login"><?php _e('Login form.', 'fast-secure-recaptcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_login_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_login_tip">
    <?php _e('Require that the user pass a reCAPTCHA V2 test before login.', 'fast-secure-recaptcha') ?>
    </div>
    <br />

    <input name="fs_recaptcha_register" id="fs_recaptcha_register" type="checkbox" <?php if ( $fs_recaptcha_opt['register'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_register"><?php _e('Register form.', 'fast-secure-recaptcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_register_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_register_tip">
    <?php _e('Require that the user pass a reCAPTCHA test before registering.', 'fast-secure-recaptcha') ?>
    </div>
    <br />

    <input name="fs_recaptcha_lostpwd" id="fs_recaptcha_lostpwd" type="checkbox" <?php if ( $fs_recaptcha_opt['lostpwd'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_lostpwd"><?php _e('Lost password form.', 'fast-secure-recaptcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_lostpwd_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_lostpwd_tip">
    <?php _e('Require that the user pass a reCAPTCHA test before lost password request.', 'fast-secure-recaptcha') ?>
    </div>
    <br />

    <input name="fs_recaptcha_comment" id="fs_recaptcha_comment" type="checkbox" <?php if ( $fs_recaptcha_opt['comment'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_comment"><?php _e('Comment form.', 'fast-secure-recaptcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_comment_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_comment_tip">
    <?php _e('Require that the user pass a reCAPTCHA test before posting comments.', 'fast-secure-recaptcha') ?>
    </div>
    <br />

    <input name="fs_recaptcha_ms_register" id="fs_recaptcha_ms_register" type="checkbox" <?php if ( $fs_recaptcha_opt['ms_register'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_ms_register"><?php _e('Multisite register form.', 'fast-secure-recaptcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_ms_register_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_ms_register_tip">
    <?php _e('Require that the user pass a reCAPTCHA test before registering in Multisite.', 'fast-secure-recaptcha') ?>
    </div>
    <br />

    <input name="fs_recaptcha_bp_register" id="fs_recaptcha_bp_register" type="checkbox" <?php if ( $fs_recaptcha_opt['bp_register'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_bp_register"><?php _e('BuddyPress register form.', 'fast-secure-recaptcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_bp_register_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_bp_register_tip">
    <?php _e('Require that the user pass a reCAPTCHA test before registering in BuddyPress.', 'fast-secure-recaptcha') ?>
    </div>
    <br />

    <input name="fs_recaptcha_wpforo_register" id="fs_recaptcha_wpforo_register" type="checkbox" <?php if ( $fs_recaptcha_opt['wpforo_register'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_wpforo_register"><?php _e('wpForo Forum register form.', 'fast-secure-recaptcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_wpforo_register_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_wpforo_register_tip">
    <?php _e('Require that the user pass a reCAPTCHA test before registering in wpForo Forum.', 'fast-secure-recaptcha') ?>
    </div>
    <br />

    <input name="fs_recaptcha_wc_checkout" id="fs_recaptcha_woocommerce" type="checkbox" <?php if ( $fs_recaptcha_opt['wc_checkout'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_wc_checkout"><?php _e('WooCommerce checkout.', 'fast-secure-recaptcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_wc_checkout_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_wc_checkout_tip">
    <?php _e('Require that the user pass a reCAPTCHA test on WooCommerce checkout form when not logged in.', 'fast-secure-recaptcha') ?>
    </div>
    <br />

    <input name="fs_recaptcha_jetpack" id="fs_recaptcha_jetpack" type="checkbox" <?php if ( $fs_recaptcha_opt['jetpack'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_jetpackt"><?php _e('Jetpack Contact Form', 'fast-secure-recaptcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_jetpack_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_jetpack_tip">
    <?php _e('Require that the user pass a reCAPTCHA test on Jetpack Contact Form.', 'fast-secure-recaptcha') ?>
    </div>
    <br />

    <input name="fs_recaptcha_bbpress_reply" id="fs_recaptcha_bbpress_topic" type="checkbox" <?php if ( $fs_recaptcha_opt['bbpress_reply'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_bbpress_reply"><?php _e('bbPress Reply to Topic Form', 'fast-secure-recaptcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_bbpress_reply_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_bbpress_reply_tip">
    <?php _e('Require that the user pass a reCAPTCHA test on bbPress Reply to Topic Form.', 'fast-secure-recaptcha') ?>
    </div>
    <br />

    <input name="fs_recaptcha_bbpress_topic" id="fs_recaptcha_bbpress_topic" type="checkbox" <?php if ( $fs_recaptcha_opt['bbpress_topic'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_bbpress_topic"><?php _e('bbPress New Topic Form', 'fast-secure-recaptcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_bbpress_topic_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_bbpress_topic_tip">
    <?php _e('Require that the user pass a reCAPTCHA test on bbPress New Topic Form.', 'fast-secure-recaptcha') ?>
    </div>
    <br />

    <input name="fs_recaptcha_bypass_comment" id="fs_recaptcha_bypass_comment" type="checkbox" <?php if( $fs_recaptcha_opt['bypass_comment'] == 'true' ) echo 'checked="checked"'; ?> />
    <label name="fs_recaptcha_bypass_comment" for="fs_recaptcha_bypass_comment"><?php _e('No comment form reCAPTCHA for logged in users', 'fast-secure-recaptcha') ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_bypass_comment_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_bypass_comment_tip">
    <?php _e('Logged in users will not have to pass a reCAPTCHA test on comments form.', 'fast-secure-recaptcha') ?>
    </div>
    <br />

   <?php
      $show_this = true;
     if ( is_multisite() && get_current_blog_id() > 1 ) {
            $show_this = false;
     }
    if ( $show_this ) {

       ?>

    <input name="fs_recaptcha_network_individual_on" id="fs_recaptcha_network_individual_on" type="checkbox" <?php if ( $fs_recaptcha_opt['network_individual_on'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_network_individual_on"><?php echo __('Allow Multisite network activated sites to have individual Fast Secure reCAPTCHA settings.', 'fast-secure-recaptcha'); ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_network_individual_on_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_network_individual_on_tip">
    <?php _e('Enabling this setting allows: individual site settings for this plugin on multisite with network activation turned on. The default is for all sites to use the master site settings.', 'fast-secure-recaptcha'); ?>
    </div>
    <br />

     <input name="fs_recaptcha_network_keys_copy" id="fs_recaptcha_network_individual_on" type="checkbox" <?php if ( $fs_recaptcha_opt['network_keys_copy'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_network_keys_copy"><?php echo __('Allow Multisite network activated main site to copy main site reCAPTCHA API keys to all other sites.', 'fast-secure-recaptcha'); ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_network_keys_copy_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_network_keys_copy_tip">
    <?php _e('Enabling this setting allows: automatically copy main site API keys to all other sites, but only when multisite with network activation turned on and when individual site settings are also enabled.', 'fast-secure-recaptcha'); ?>
    </div>
    <br />


 <?php }
    if ($fs_recaptcha_networkwide && $network_individual_on) {
      // multisite with network activation and individual site control turned on, but this site cannot change these two settings

      echo '<div class="fs-notice">';
	  echo __( 'Fast Secure reCAPTCHA is Network Activated and individual site configured. The next two settings can only be modified by the main site settings menu.', 'fast-secure-recaptcha' );
     echo "</div>\n";

    ?>

     <?php echo ( $fs_recaptcha_main_opt['network_individual_on'] == 'true' ) ? __('Main site admin enabled:', 'fast-secure-recaptcha') : __('Main site admin disabled:', 'fast-secure-recaptcha') ; ?>
    <?php echo  ' ' . __('Allow Multisite network activated sites to have individual Fast Secure reCAPTCHA settings.', 'fast-secure-recaptcha'); ?>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_network_individual_on_disabled_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_network_individual_on_disabled_tip">
    <?php _e('Enabling this setting allows: individual site settings for this plugin on multisite with network activation turned on. The default is for all sites to use the master site settings.', 'fast-secure-recaptcha'); ?>
    </div>
    <br />

     <?php echo ( $fs_recaptcha_main_opt['network_keys_copy'] == 'true' ) ? __('Main site admin enabled:', 'fast-secure-recaptcha') : __('Main site admin disabled:', 'fast-secure-recaptcha') ; ?>
    <?php echo ' ' .__('Allow Multisite network activated main site to automatically copy main site reCAPTCHA API keys to all other sites.', 'fast-secure-recaptcha'); ?>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_network_keys_copy_disabled_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_network_keys_copy_disabled_tip">
    <?php _e('Enabling this setting allows: automatically copy main site API keys to all other sites, but only when multisite with network activation turned on and when individual site settings are also enabled.', 'fast-secure-recaptcha'); ?>
    </div>
    <br />

  <?php } ?>

    </td>
    </tr>

     <tr>
       <th scope="row" style="width: 75px;"><?php _e('Style:', 'fast-secure-recaptcha'); ?></th>
      <td>

    <input name="fs_recaptcha_captcha_small" id="fs_recaptcha_captcha_small" type="checkbox" <?php if ( $fs_recaptcha_opt['captcha_small'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_captcha_small"><?php echo __('Enable small size reCAPTCHA on all forms.', 'fast-secure-recaptcha'); ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_captcha_small_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_captcha_small_tip">
    <?php _e('Makes the reCAPTCHA size compact instead of normal on all the forms.', 'fast-secure-recaptcha'); ?>
    </div>
    <br />


      <label for="fs_recaptcha_dark"><?php _e( 'reCAPTCHA Theme: dark or light?', 'fast-secure-recaptcha' ); ?></label>
			<select id="fs_recaptcha_dark" name="fs_recaptcha_dark">
			<?php
			$recaptcha_dark_array = array(
				'false' => __( 'Light theme', 'fast-secure-recaptcha'  ),
				'true'	=> __( 'Dark theme', 'fast-secure-recaptcha'  ),
			);
			$selected = '';
			foreach ( $recaptcha_dark_array as $k => $v ) {
				if ( $fs_recaptcha_opt['dark'] == "$k" )
					$selected = ' selected="selected"';
				echo '<option value="' . esc_attr($k) . '"' . $selected . '>' . esc_html($v) . '</option>' . "\n";
				$selected = '';
			}
			?>
			</select>
			<a style="cursor:pointer;" title="<?php esc_attr_e( 'Click for Help!', 'fast-secure-recaptcha' ); ?>" onclick="toggleVisibility('fs_recaptcha_dark_tip');"><?php _e( 'help', 'fast-secure-recaptcha' ); ?></a>
			<div class="fscf_tip" id="fs_recaptcha_dark_tip">
			<?php _e( 'The color theme of the reCAPTCHA widget.', 'fast-secure-recaptcha' ); ?>
			</div>
          <br />

          <label for="fs_recaptcha_language"><?php _e( 'reCAPTCHA Language', 'fast-secure-recaptcha' ); ?></label>
			<select id="fs_recaptcha_language" name="fs_recaptcha_language">
<?php
	  $recaptcha_languages_array = array(
							__( 'Auto Detect', 'fast-secure-recaptcha' )         	=> 'auto',
							__( 'Arabic', 'fast-secure-recaptcha' )              	=> 'ar',
   							__( 'Afrikaans', 'fast-secure-recaptcha' )              	=> 'af',
							__( 'Amharic', 'fast-secure-recaptcha' )              	=> 'am',
							__( 'Armenian', 'fast-secure-recaptcha' )              	=> 'hy',
							__( 'Basque', 'fast-secure-recaptcha' )           	=> 'eu',
							__( 'Bengali', 'fast-secure-recaptcha' )           	=> 'bn',
							__( 'Bulgarian', 'fast-secure-recaptcha' )           	=> 'bg',
							__( 'Catalan', 'fast-secure-recaptcha' )             	=> 'ca',
 							__( 'Chinese (Hong Kong)', 'fast-secure-recaptcha' )	=> 'zh-HK',
							__( 'Chinese (Simplified)', 'fast-secure-recaptcha' )	=> 'zh-CN',
							__( 'Chinese (Traditional)', 'fast-secure-recaptcha' ) => 'zh-TW',
							__( 'Croatian', 'fast-secure-recaptcha' )           	=> 'hr',
							__( 'Czech', 'fast-secure-recaptcha' )             	=> 'cs',
							__( 'Danish', 'fast-secure-recaptcha' )             	=> 'da',
							__( 'Dutch', 'fast-secure-recaptcha' )              	=> 'nl',
							__( 'English (UK)', 'fast-secure-recaptcha' )         => 'en-GB',
							__( 'English (US)', 'fast-secure-recaptcha' )         => 'en',
 							__( 'Estonian', 'fast-secure-recaptcha' )         => 'et',
							__( 'Filipino', 'fast-secure-recaptcha' )				=> 'fil',
							__( 'Finnish', 'fast-secure-recaptcha' ) 				=> 'fi',
							__( 'French', 'fast-secure-recaptcha' )           	=> 'fr',
							__( 'French (Canadian)', 'fast-secure-recaptcha' )   	=> 'fr-CA',
 							__( 'Galician', 'fast-secure-recaptcha' )   			=> 'gl',
  							__( 'Georgian', 'fast-secure-recaptcha' )   			=> 'ka',
							__( 'German', 'fast-secure-recaptcha' )   			=> 'de',
							__( 'German (Austria)', 'fast-secure-recaptcha' )		=> 'de-AT',
							__( 'German (Switzerland)', 'fast-secure-recaptcha' ) => 'de-CH',
							__( 'Greek', 'fast-secure-recaptcha' )           		=> 'el',
 							__( 'Gujarati', 'fast-secure-recaptcha' )           		=> 'gu',
							__( 'Hebrew', 'fast-secure-recaptcha' )             	=> 'iw',
							__( 'Hindi', 'fast-secure-recaptcha' )             	=> 'hi',
							__( 'Hungarain', 'fast-secure-recaptcha' )            => 'hu',
							__( 'Indonesian', 'fast-secure-recaptcha' )         	=> 'id',
							__( 'Italian', 'fast-secure-recaptcha' )         		=> 'it',
							__( 'Japanese', 'fast-secure-recaptcha' )				=> 'ja',
							__( 'Kannada', 'fast-secure-recaptcha' ) 				=> 'kn',
							__( 'Korean', 'fast-secure-recaptcha' ) 				=> 'ko',
 							__( 'Laothian', 'fast-secure-recaptcha' )           	=> 'lo',
							__( 'Latvian', 'fast-secure-recaptcha' )           	=> 'lv',
							__( 'Lithuanian', 'fast-secure-recaptcha' )   		=> 'lt',
							__( 'Malay', 'fast-secure-recaptcha' )           	=> 'ms',
							__( 'Malayalam', 'fast-secure-recaptcha' )           	=> 'ml',
							__( 'Marathi', 'fast-secure-recaptcha' )           	=> 'mr',
 							__( 'Mongolian', 'fast-secure-recaptcha' )           	=> 'mn',
							__( 'Norwegian', 'fast-secure-recaptcha' )   			=> 'no',
							__( 'Persian', 'fast-secure-recaptcha' )           	=> 'fa',
							__( 'Polish', 'fast-secure-recaptcha' )   			=> 'pl',
							__( 'Portuguese', 'fast-secure-recaptcha' )   		=> 'pt',
							__( 'Portuguese (Brazil)', 'fast-secure-recaptcha' )  => 'pt-BR',
							__( 'Portuguese (Portugal)', 'fast-secure-recaptcha' )=> 'pt-PT',
							__( 'Romanian', 'fast-secure-recaptcha' )         	=> 'ro',
							__( 'Russian', 'fast-secure-recaptcha' )         		=> 'ru',
							__( 'Serbian', 'fast-secure-recaptcha' )				=> 'sr',
 							__( 'Sinhalese', 'fast-secure-recaptcha' )				=> 'si',
   					    	__( 'Slovak', 'fast-secure-recaptcha' ) 				=> 'sk',
							__( 'Slovenian', 'fast-secure-recaptcha' )           	=> 'sl',
							__( 'Spanish', 'fast-secure-recaptcha' )   			=> 'es',
							__( 'Spanish (Latin America)', 'fast-secure-recaptcha' )=> 'es-419',
							__( 'Swahili', 'fast-secure-recaptcha' )           	=> 'sw',
							__( 'Swedish', 'fast-secure-recaptcha' )           	=> 'sv',
							__( 'Tamil', 'fast-secure-recaptcha' )   				=> 'ta',
							__( 'Telugu', 'fast-secure-recaptcha' )   				=> 'te',
							__( 'Thai', 'fast-secure-recaptcha' )   				=> 'th',
							__( 'Turkish', 'fast-secure-recaptcha' )   			=> 'tr',
							__( 'Ukrainian', 'fast-secure-recaptcha' )   			=> 'uk',
							__( 'Vietnamese', 'fast-secure-recaptcha' )   		=> 'vi',
							__( 'Zulu', 'fast-secure-recaptcha' )   		=> 'zu'
							);
 			foreach ( $recaptcha_languages_array as $k => $v ) {
				if ( $fs_recaptcha_opt['language'] == "$v" )
					$selected = ' selected="selected"';
				echo '<option value="' . esc_attr($v) . '"' . $selected . '>' . esc_html($k) . '</option>' . "\n";
				$selected = '';
			}
			?>
			</select>
			<a style="cursor:pointer;" title="<?php esc_attr_e( 'Click for Help!', 'fast-secure-recaptcha' ); ?>" onclick="toggleVisibility('fs_recaptcha_language_tip');"><?php _e( 'help', 'fast-secure-recaptcha' ); ?></a>
			<div class="fscf_tip" id="fs_recaptcha_language_tip">
			<?php _e( 'Forces the reCAPTCHA widget to render in a specific language. Default is to Auto-detect the language.', 'fast-secure-recaptcha' ); ?>
			</div>
          <br />

    <input name="fs_recaptcha_external_css" id="fs_recaptcha_external_css" type="checkbox" <?php if ( $fs_recaptcha_opt['external_css'] == 'true' ) echo ' checked="checked" '; ?> />
    <label for="fs_recaptcha_external_css"><?php echo __('Enable external reCAPTCHA CSS.', 'fast-secure-recaptcha'); ?></label>
    <a style="cursor:pointer;" title="<?php esc_attr_e('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_external_css_tip');"><?php _e('help', 'fast-secure-recaptcha'); ?></a>
    <div class="fscf_tip" id="fs_recaptcha_external_css_tip">
    <?php _e('Enable to not load this plugin CSS into wp_head. This allows you to load your own modified CSS into your theme instead. This plugin loads some CSS for the reCAPTCHA DIV. You can look in the HTML head section to see what is there before you enable external CSS.', 'fast-secure-recaptcha'); ?>
    </div>
    <br />


       </td>
    </tr>

        </table>



  <table cellspacing="2" cellpadding="5" class="form-table">

        <tr>
          <th scope="row" style="width: 75px;"><?php echo __('Error Messages:', 'fast-secure-recaptcha'); ?></th>
         <td>


        <strong><?php _e('Customize Text:', 'fast-secure-recaptcha'); ?></strong>
        <a style="cursor:pointer;" title="<?php echo __('Click for Help!', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_labels_tip');"><?php echo __('help', 'fast-secure-recaptcha'); ?></a>
       <div class="fscf_tip" id="fs_recaptcha_labels_tip">
       <?php echo __('These fields can be filled in to override the error message that displays when the form is submitted and the reCAPTCHA does not pass validation.', 'fast-secure-recaptcha'); ?>
       </div>
       <br />
       <label for="fs_recaptcha_text_error"><?php echo __('ERROR', 'fast-secure-recaptcha'); ?></label><br />
       <input name="fs_recaptcha_text_error" id="fs_recaptcha_text_error" type="text" value="<?php echo esc_attr($fs_recaptcha_opt['text_error']);  ?>" size="50" /><br />
       <label for="fs_recaptcha_text_incorrect"><?php echo __('You have selected an incorrect reCAPTCHA value', 'fast-secure-recaptcha'); ?></label><br />
       <input name="fs_recaptcha_text_incorrect" id="fs_recaptcha_text_incorrect" type="text" value="<?php echo esc_attr($fs_recaptcha_opt['text_incorrect']);  ?>" size="50" /><br />

        </td>
    </tr>

          </table>
        </fieldset>

    <input name="fs_recaptcha_donated" id="fs_recaptcha_donated" type="checkbox" <?php if( $fs_recaptcha_opt['donated'] == 'true' ) echo 'checked="checked"'; ?> />
    <label name="fs_recaptcha_donated" for="fs_recaptcha_donated"><?php echo __('I have donated to help contribute for the development of this plugin. This checkbox makes the donate button go away', 'fast-secure-recaptcha'); ?></label>
    <br />

    <p class="submit">
       <input type="submit" name="submit" class="button button-primary" value="<?php _e('Save Changes', 'fast-secure-recaptcha') ?>" />
    </p>

</form>

<h3><?php _e('Don\'t Forget to Test the reCAPTCHA', 'fast-secure-recaptcha') ?></h3>

<p>
<?php  _e('After installing or updating plugins and themes, be sure to test the reCAPTCHA on each form where it is enabled. It should display, allow actions on valid code, and block actions on invalid code.', 'fast-secure-recaptcha');
?>
</p>

<?php
if ($fs_recaptcha_opt['donated'] != 'true') {
 ?>

  <table style="border:none; width:850px;">
  <tr>
  <td>
  <div style="width:385px;height:200px; float:left;background-color:white;padding: 10px 10px 10px 10px; border: 1px solid #ddd; background-color:#FFFFE0;">
		<div>
         <h3><?php echo __('Donate', 'fast-secure-recaptcha'); ?></h3>

<?php
_e('If you find this plugin useful to you, please consider making a donation to help contribute to my time invested and to further development. Thanks for your kind support!', 'fast-secure-recaptcha') ?><br />
<a style="cursor:pointer;" title="<?php esc_attr_e('You have 1 message from Mike Challis', 'fast-secure-recaptcha'); ?>" onclick="toggleVisibility('fs_recaptcha_mike_challis_tip');"><?php _e('You have 1 message from Mike Challis', 'fast-secure-recaptcha'); ?></a>
<br /><br />
   </div>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="2NA2XBX9WG98L" />
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" style="border:none;" name="submit" alt="Paypal Donate" />
<img alt="" style="border:none;" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</form>
  </td>
 </tr>
 </table>

<br />

<div class="fscf_tip" id="fs_recaptcha_mike_challis_tip">
<img src="<?php echo $si_captcha_plugin_url; ?>fast-secure-recaptcha.jpg" class="fscf_left fscf_img" width="250" height="185" alt="Mike Challis" /><br />
<?php _e('Mike Challis says: "Hello, I have many hours coding this plugin just for you. Please consider making a donation. If you are not able to, that is OK.', 'fast-secure-recaptcha'); ?>
<?php echo ' '; _e('Please also rate my plugin."', 'fast-secure-recaptcha'); ?>
 <a href="https://wordpress.org/support/plugin/fast-secure-recaptcha/reviews/" target="_blank"><?php _e('Rate This', 'fast-secure-recaptcha'); ?></a>.
<br /><br />
<a style="cursor:pointer;" title="Close" onclick="toggleVisibility('fs_recaptcha_mike_challis_tip');"><?php _e('Close this message', 'fast-secure-recaptcha'); ?></a>
<div class="clear"></div><br />
</div>

<?php
}
?>

<p><strong><?php _e('WordPress plugins by Mike Challis:', 'fast-secure-recaptcha') ?></strong></p>
<ul>
<li><a href="https://wordpress.org/plugins/si-contact-form/" target="_blank"><?php echo __('Fast Secure Contact Form', 'fast-secure-recaptcha'); ?></a></li>
<li><a href="https://wordpress.org/plugins/fast-secure-recaptcha/" target="_blank"><?php echo __('Fast Secure reCAPTCHA', 'fast-secure-recaptcha'); ?></a></li>
<li><a href="https://wordpress.org/plugins/si-captcha-for-wordpress/" target="_blank"><?php echo __('SI CAPTCHA Anti-Spam', 'fast-secure-recaptcha'); ?></a></li>
<li><a href="https://wordpress.org/plugins/visitor-maps/" target="_blank"><?php echo __('Visitor Maps and Who\'s Online', 'fast-secure-recaptcha'); ?></a></li>
</ul>



