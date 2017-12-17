<?php
/*
Plugin Name: Fast Secure reCAPTCHA
Plugin URI: https://wordpress.org/plugins/fast-secure-recaptcha/
Description: Adds No CAPTCHA reCAPTCHA V2 anti-spam to WordPress pages for comments, login, registration, lost password, BuddyPress register, bbPress register, wpForo register, bbPress New Topic and Reply to Topic Forms, Jetpack Contact Form, and WooCommerce checkout. In order to post comments, login, or register, users will have to pass the reCAPTCHA V2 "I'm not a robot" test. Prevents spam from automated bots. Compatible with Akismet and Multisite Network Activate.
Author: fastsecure
Author URI: http://www.642weather.com/weather/scripts.php
Text Domain: fast-secure-recaptcha
Domain Path: /languages
License: GPLv2 or later
Version: 1.0.20
*/

$fs_recaptcha_version = '1.0.20';

/*  Copyright (C) 2017 Mike Challis  (http://www.642weather.com/weather/contact_us.php)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}


if (!class_exists('fsRecaptcha')) {

class fsRecaptcha {

    public $fs_recaptcha_version;
    public $fs_recaptcha_add_script = false;
    private $fs_recaptcha_add_reg = false;
    private $fs_recaptcha_add_jetpack = false;
    public $fs_add_recaptcha_js_array = array();
    private $fs_recaptcha_networkwide = false;
    private $fs_recaptcha_on_comments = false;
    private $fs_recaptcha_checkout_validated = false;

function fs_recaptcha_admin_menu() {
   global $wp_version;

    add_options_page( __('Fast Secure reCAPTCHA settings', 'fast-secure-recaptcha'), __('Fast Secure reCAPTCHA', 'fast-secure-recaptcha'), 'manage_options', __FILE__,array(&$this,'fs_recaptcha_options_page'));
}

function fs_recaptcha_plugin_row_meta( $links, $file ) {
    if ( $file == plugin_basename( __FILE__ ) ) {
	    $links[] = '<a href="https://wordpress.org/support/plugin/fast-secure-recaptcha" target="_new">'.__('Support', 'fast-secure-recaptcha').'</a>';
		$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2NA2XBX9WG98L" target="_new">'.__('Donate', 'fast-secure-recaptcha').'</a>';
	}
	return $links;
}


function fs_recaptcha_api_key_notice() {
    // remind admin to enter API keys upon activation
    global $fs_recaptcha_opt;

    if ( empty($fs_recaptcha_opt['site_key']) || empty($fs_recaptcha_opt['secret_key']) ) {
	   if ( current_user_can('manage_options') && get_transient('fast_secure_recaptcha_admin_notice') ){
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php
			printf(
				__( '<strong>Fast Secure reCAPTCHA needs your attention:</strong> To make it work, you need to enter API keys. <br />You can do so at the <a href="%s">Fast Secure reCAPTCHA settings page</a>.' , 'fast-secure-recaptcha' ),
				admin_url( add_query_arg( 'page' , 'fast-secure-recaptcha/fast-secure-recaptcha.php' , 'options-general.php' ) )
			);
		?></p></div>

        <?php
        // Delete transient, only display this notice once.
        delete_transient('fast_secure_recaptcha_admin_notice');
      }
    }
} // end  function fs_recaptcha_api_key_notice


// set a transient used to remind admin to enter API keys upon activation
function fs_recaptcha_admin_activated() {
         global $fs_recaptcha_opt;

          if ( empty($fs_recaptcha_opt['site_key']) || empty($fs_recaptcha_opt['secret_key']) ) {
		      if ( current_user_can( 'manage_options' ) ) {
		          set_transient( 'fast_secure_recaptcha_admin_notice', true, 5 );
              }
          }
} // end function fs_recaptcha_admin_activated


function fs_recaptcha_plugin_action_links( $links, $file ) {
    //Static so we don't call plugin_basename on every plugin row.
	static $this_plugin;
	if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if ( $file == $this_plugin ){
	     $settings_link = '<a href="options-general.php?page=fast-secure-recaptcha/fast-secure-recaptcha.php">' . __('Settings', 'fast-secure-recaptcha') . '</a>';
	     array_unshift( $links, $settings_link );
    }
	return $links;
} // end function fs_recaptcha_plugin_action_links


function fs_recaptcha_init() {
         global $wp_version, $fs_recaptcha_opt, $fs_recaptcha_networkwide;

         load_plugin_textdomain('fast-secure-recaptcha', false, dirname(plugin_basename(__FILE__)).'/languages' );

  	add_filter( 'plugin_row_meta', array($this,'fs_recaptcha_plugin_row_meta'), 10, 2 );

  // is it networkwide installed?
 if ( ! function_exists( 'is_plugin_active_for_network' ) )
     require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

  if ( is_multisite() && is_plugin_active_for_network('fast-secure-recaptcha/fast-secure-recaptcha.php') )
     $fs_recaptcha_networkwide = true;

  $this->fs_recaptcha_get_options();
  $this->fs_recaptcha_determine_current_page();

  // if google reCAPTCHA APY keys are empty, do nothing with display of reCAPTCHA because it will NOT work at all
  if ( !empty($fs_recaptcha_opt['site_key']) && !empty($fs_recaptcha_opt['secret_key']) ) {

     add_filter('script_loader_tag', array(&$this,'fs_recaptcha_make_script_async'), 10, 3);  // for loading recaptcha js async defer

     if ($fs_recaptcha_opt['external_css'] == 'false') {
         add_action('wp_head', array($this, 'fs_recaptcha_wp_head'));
         add_action('login_head', array($this, 'fs_recaptcha_wp_head'));
     }

     // comment form
     if ($fs_recaptcha_opt['comment'] == 'true') {
       if( ! is_user_logged_in() ) {
		       add_action('comment_form_after_fields', array($this, 'fs_recaptcha_comment_form'), 99);
               add_action('comment_form', array($this, 'fs_recaptcha_comment_form_legacy'), 99); // legacy themes
	   } else {
               add_filter ('comment_form_field_comment', array($this, 'fs_recaptcha_comment_form_logged_in'), 11);
               add_action('comment_form', array($this, 'fs_recaptcha_comment_form_legacy'), 99); // legacy themes
	   }
        add_action('wp_footer', array($this, 'fs_recaptcha_add_script'));
        add_filter('preprocess_comment', array($this, 'fs_recaptcha_comment_post'), 1);
     }

     // register form
     //if ($fs_recaptcha_opt['register'] == 'true' && isset($this->is_reg)) { // was not working on bbPress [bbp-register] shortcode
     if ($fs_recaptcha_opt['register'] == 'true' ) {
        add_action('register_form', array($this, 'fs_recaptcha_register_form'), 99);
        add_filter('registration_errors', array($this, 'fs_recaptcha_register_post'), 10, 3);
        add_action('woocommerce_register_form', array($this, 'fs_recaptcha_register_form'), 99);
        add_filter('woocommerce_registration_errors', array($this, 'fs_recaptcha_register_post'), 10, 3);
        add_action('login_footer', array($this, 'fs_recaptcha_add_script'), 10);
        add_action('wp_footer', array($this, 'fs_recaptcha_add_script'));

     }

     // login form
     if ($fs_recaptcha_opt['login'] == 'true' ) {
        add_action('login_form', array($this, 'fs_recaptcha_login_form' ), 99);
        add_filter('login_form_middle', array($this, 'fs_recaptcha_inline_login_form'), 99);
        add_action('woocommerce_login_form' ,array($this, 'fs_recaptcha_login_form' ), 99);
	    add_filter('authenticate', array($this, 'fs_recaptcha_check_login_recaptcha'), 15);
        add_action('login_footer', array($this, 'fs_recaptcha_add_script'), 10);
        add_action('wp_footer', array($this, 'fs_recaptcha_add_script'));
     }

     // lost passwordform
     if ($fs_recaptcha_opt['lostpwd'] == 'true' && isset($this->is_lostpassword)) {
 	    add_action('lostpassword_form', array( $this, 'fs_recaptcha_lostpassword_form'), 99);
        add_action('woocommerce_lostpassword_form', array($this, 'fs_recaptcha_lostpassword_form'), 99);
	    add_action('lostpassword_post', array($this, 'fs_recaptcha_lostpassword_post'), 10);
        add_action('login_footer', array($this, 'fs_recaptcha_add_script'), 10);
        add_action('wp_footer', array($this, 'fs_recaptcha_add_script'));
     }

     // woocommerce checkout form
     if ( ! is_user_logged_in() ) {
           // show recaptcha for woocommerce checkout but only when the setting is enabled and not logged in
 		   add_action('woocommerce_checkout_after_order_review', array($this, 'fs_recaptcha_wc_checkout_form'), 99);
           add_action('woocommerce_after_checkout_validation', array($this, 'fs_recaptcha_wc_checkout_post') );
     }

     // wpForo registration
     if(function_exists('is_wpforo_page') && $fs_recaptcha_opt['wpforo_register'] == 'true' ){
	   if(is_wpforo_page()){
         add_action('register_form', array($this, 'fs_recaptcha_register_form'), 99);
         add_filter('registration_errors', array($this, 'fs_recaptcha_register_post'), 10, 3);
         add_action('wp_footer', array($this, 'fs_recaptcha_add_script'));
       }
     }

     // buddyPress register - create an account
     if ($fs_recaptcha_opt['bp_register'] == 'true') {
        add_action('bp_account_details_fields', array($this, 'fs_recaptcha_bp_register_form'), 99);
        add_action('bp_signup_validate', array($this, 'fs_recaptcha_bp_signup_validate'), 10);
     }

     // wp multisite
     if ($fs_recaptcha_opt['ms_register'] == 'true' && isset($this->is_signup)) {
       // register for multisite
        add_action('signup_extra_fields', array($this, 'fs_recaptcha_ms_register_form'));
        add_action('signup_blogform', array($this, 'fs_recaptcha_ms_register_form'));
       // logged in user signup new site
	    add_filter('wpmu_validate_user_signup', array($this, 'fs_recaptcha_mu_signup_validate'));
        add_filter('wpmu_validate_blog_signup', array($this, 'fs_recaptcha_mu_site_signup_validate'));
     }

    // bbPress New Topic, Reply to Topic
     if(class_exists( 'bbPress' )) {
            if ($fs_recaptcha_opt['bbpress_topic'] == 'true') {
                add_action('bbp_theme_after_topic_form_content', array($this, 'fs_recaptcha_bbpress_topic_form'));
                add_action('bbp_new_topic_pre_extras', array($this, 'fs_recaptcha_bbpress_topic_validate'));
            }
            if ($fs_recaptcha_opt['bbpress_reply'] == 'true') {
                add_action('bbp_theme_after_reply_form_content', array($this, 'fs_recaptcha_bbpress_topic_form'));
                add_action('bbp_new_reply_pre_extras', array($this, 'fs_recaptcha_bbpress_topic_validate'));
            }
     }

     // jetpack contact form
      if ($fs_recaptcha_opt['jetpack'] == 'true') {
        add_filter('jetpack_contact_form_is_spam', array($this, 'fs_recaptcha_jetpack_validate'));
        add_filter('the_content', array($this, 'fs_recaptcha_jetpack_form'));
        add_filter('widget_text', array($this, 'fs_recaptcha_jetpack_form'), 0);
        add_filter('widget_text', 'shortcode_unautop');
        add_filter('widget_text', 'do_shortcode');
        add_shortcode('fs-recaptcha', array($this, 'fs_recaptcha_jetpack_shortcode'));
        add_action('wp_footer', array($this, 'fs_recaptcha_add_script'));
     }


  } // end if !empty keys
} // end function fs_recaptcha_init


function fs_recaptcha_get_options() {
  global $fs_recaptcha_opt, $fs_recaptcha_option_defaults, $fs_recaptcha_networkwide;

  $fs_recaptcha_option_defaults = array(
         'donated' => 'false',        // a checkbox that makes the donate button go away
         'site_key'	=> '',            // reCAPTCHA API Site Key
         'secret_key'	=> '',        // reCAPTCHA API Secret Key
         'bypass_comment' => 'true',  // enable No comment form reCAPTCHA for logged in users
         'comment' => 'true',         // enable on comment form
         'login' => 'false',          // enable on login form
         'register' => 'true',        // enable on register form
         'bp_register' => 'true',     // enable on buddypress register form
         'ms_register' => 'true',     // enable on multisite register form
         'wpforo_register' => 'true', // enable on wpForo Forum register form
         'lostpwd'  => 'true',        // enable on lost password form
         'wc_checkout' => 'true',     // enable on WooCommerce checkout form
         'jetpack' => 'true',         // enable on Jetpack contact form
         'bbpress_topic' => 'true',   // enable on bbPress New Topic form
         'bbpress_reply' => 'true',   // enable on bbPress Reply to Topic form
         'captcha_small' => 'false',  // enable small reCAPTCHA size
         'external_css' => 'false',   // enable use external css for reCAPTCHA divs
         'language' =>    'auto',     // reCAPTCHA languaged selected
         'network_individual_on' => 'false',  // Allow Multisite network activated sites to have individual FS reCAPTCHA settings
         'network_keys_copy' => 'false',      // Allow Multisite network activated main site to copy main site reCAPTCHA API keys to all other sites
         'text_incorrect' =>    '',           // optional custom text for "ERROR"
         'text_error' =>    '',               // optional custom text for "You have selected an incorrect reCAPTCHA value"

  );

     $network_individual_on = false;
     if ($fs_recaptcha_networkwide && get_current_blog_id() > 1) {
        $fs_recaptcha_main_opt = get_site_option('fast_secure_recaptcha');
        if ($fs_recaptcha_main_opt['network_individual_on'] == 'true')
              $network_individual_on = true; // this is like a global that is also used in admin settings
     }

  // get the options
   if ( $fs_recaptcha_networkwide && get_current_blog_id() == 1 ) {
          // multisite with network activation, this is main site
          add_site_option('fast_secure_recaptcha', $fs_recaptcha_option_defaults, '', 'yes');
          $fs_recaptcha_opt = get_site_option('fast_secure_recaptcha');
  } else  if ( $fs_recaptcha_networkwide && get_current_blog_id() > 1 ) {
          if ( $network_individual_on ) {
             // multisite with network activation individual site control
             add_option('fast_secure_recaptcha', $fs_recaptcha_option_defaults, '', 'yes');
             $fs_recaptcha_opt = get_option('fast_secure_recaptcha');
             if ( empty($fs_recaptcha_opt['network_keys_copy']) )
                       $fs_recaptcha_opt['network_keys_copy'] = 'false';
            if ( !empty($fs_recaptcha_opt['network_keys_copy']) && $fs_recaptcha_opt['network_keys_copy'] == 'true') {
                // auto copy main site keys to individual sites with no keys
                if ( $fs_recaptcha_opt['site_key'] == '' && !empty($fs_recaptcha_main_opt['site_key']) ) {
                    $fs_recaptcha_opt['site_key'] = $fs_recaptcha_main_opt['site_key'];
                    update_option('fast_secure_recaptcha', $fs_recaptcha_opt);
                }
                if ( $fs_recaptcha_opt['secret_key'] == '' && !empty($fs_recaptcha_main_opt['secret_key']) ) {
                    $fs_recaptcha_opt['secret_key'] = $fs_recaptcha_main_opt['secret_key'];
                    update_option('fast_secure_recaptcha', $fs_recaptcha_opt);
                }
             }
          } else {
              // multisite with network activation master site control
              add_site_option('fast_secure_recaptcha', $fs_recaptcha_option_defaults, '', 'yes');
              $fs_recaptcha_opt = get_site_option('fast_secure_recaptcha');
          }
  } else {
          // no multisite
          add_option('fast_secure_recaptcha', $fs_recaptcha_option_defaults, '', 'yes');
          $fs_recaptcha_opt = get_option('fast_secure_recaptcha');
  }

  // array merge incase this version has added new options
  $fs_recaptcha_opt = array_merge($fs_recaptcha_option_defaults, $fs_recaptcha_opt);

  // strip slashes on get options array
  foreach($fs_recaptcha_opt as $key => $val) {
           $fs_recaptcha_opt[$key] = stripslashes($val);
  }

  if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST )
      $fs_recaptcha_opt['login'] = 'false'; // always disable recaptcha on xmlrpc login connections

} // end function fs_recaptcha_get_options


function fs_recaptcha_options_page() {
  global $fs_recaptcha_opt, $fs_recaptcha_option_defaults, $fs_recaptcha_version, $fs_recaptcha_networkwide;

  require_once('fast-secure-recaptcha-admin.php');

}// end function fs_recaptcha_options_page


function fs_recaptcha_admin_head() {
 // only load this header css on the admin settings page for this plugin
if(isset($_GET['page']) && is_string($_GET['page']) && preg_match('/fast-secure-recaptcha.php$/',$_GET['page']) ) {
?>
<!-- begin Fast Secure reCAPTCHA - admin settings page header css -->
<style type="text/css">
div.fs-star-holder { position: relative; height:19px; width:100px; font-size:19px;}
div.fs-star {height: 100%; position:absolute; top:0px; left:0px; background-color: transparent; letter-spacing:1ex; border:none;}
.fs-star1 {width:20%;} .fs-star2 {width:40%;} .fs-star3 {width:60%;} .fs-star4 {width:80%;} .fs-star5 {width:100%;}
.fs-star.fs-star-rating {background-color: #fc0;}
.fs-star img{display:block; position:absolute; right:0px; border:none; text-decoration:none;}
div.fs-star img {width:19px; height:19px; border-left:1px solid #fff; border-right:1px solid #fff;}
.fs-notice{background-color:#ffffe0;border-color:#e6db55;border-width:1px;border-style:solid;padding:5px;margin:5px 5px 20px;-moz-border-radius:3px;-khtml-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;}
.fscf_left {clear:left; float:left;}
.fscf_img {margin:0 10px 10px 0;}
.fscf_tip {text-align:left; display:none;color:#006B00;padding:5px;}
</style>
<!-- end Fast Secure reCAPTCHA - admin settings page header css -->
<?php
  } // end if(isset($_GET['page'])

} // end function fs_recaptcha_admin_head


function fs_recaptcha_wp_head() {
?>
<!-- begin Fast Secure reCAPTCHA - page header css -->
<style type="text/css">
div.fs-recaptcha-comments { display:block; clear:both; margin-bottom:1em; }
div.fs-recaptcha-bp-comments { display:block; clear:both; margin-bottom:1em; }
div.fs-recaptcha-login { display:block; clear:both; margin-bottom:1em; }
div.fs-recaptcha-side-login { display:block; clear:both; margin-bottom:1em; }
div.fs-recaptcha-registration { display:block; clear:both; margin-bottom:1em; }
div.fs-recaptcha-bp-registration { display:block; clear:both; margin-bottom:1em; }
div.fs-recaptcha-ms-registration { display:block; clear:both; margin-bottom:1em; }
div.fs-recaptcha-lostpassword { display:block; clear:both; margin-bottom:1em; }
div.fs-recaptcha-wc-checkout { display:block; clear:both; margin-bottom:1em; }
div.fs-recaptcha-jetpack { display:block; clear:both; margin-bottom:1em; }
.fs-recaptcha-jetpack p { color:#DC3232; }
</style>
<!-- end Fast Secure reCAPTCHA - page header css -->
<?php
} // end function fs_recaptcha_wp_head


// this function adds the recaptcha to the comment form
function fs_recaptcha_comment_form() {
    global $fs_recaptcha_opt, $fs_recaptcha_add_script, $fs_recaptcha_on_comments;

    // skip the captcha if user is logged in and the settings allow
    if (is_user_logged_in() && $fs_recaptcha_opt['bypass_comment'] == 'true')
               // logged in user can bypass recaptcha
               return;

    if ($fs_recaptcha_on_comments)
      return;

  $fs_recaptcha_add_script = true;

echo '
<div class="fs-recaptcha-comments">';
$this->fs_recaptcha_captcha_html('comments');
echo '</div>
';
    // prevent double captcha fields
    $fs_recaptcha_on_comments = true;
    return;
} // end function fs_recaptcha_comment_form


// this function adds the recaptcha to the comment form
function fs_recaptcha_comment_form_legacy() {
    global $fs_recaptcha_opt, $fs_recaptcha_add_script, $fs_recaptcha_on_comments;

    // skip the captcha if user is logged in and the settings allow
    if (is_user_logged_in() && $fs_recaptcha_opt['bypass_comment'] == 'true')
               // logged in user can bypass recaptcha
               return;

    if ($fs_recaptcha_on_comments)
      return;

  $fs_recaptcha_add_script = true;

echo '
<div id="fs-recaptcha-comments1" class="fs-recaptcha-comments">';
$this->fs_recaptcha_captcha_html('comments');
echo '</div>
';

// rearrange submit button display order
//if ($si_captcha_opt['captcha_rearrange'] == 'true') {
     print  <<<EOT
      <script type='text/javascript'>
          var fsUrlInput = document.getElementById("comment");
          var oParent = fsUrlInput.parentNode;
          var fsSubstitue = document.getElementById("fs-recaptcha-comments1");
                  oParent.appendChild(fsSubstitue, fsUrlInput);
      </script>
            <noscript>
          <style type='text/css'>#submit {display:none;}</style><br />
EOT;
  echo '           <input name="submit" type="submit" id="submit-alt" tabindex="6" value="'.__('Submit Comment', 'si-captcha').'" />
          </noscript>
  ';

//}
    // prevent double captcha fields
    $fs_recaptcha_on_comments = true;
    return;
} // end function fs_recaptcha_comment_form


// this function adds the recaptcha to the comment form when user is logged in
function fs_recaptcha_comment_form_logged_in($comment_field) {
    global $fs_recaptcha_opt, $fs_recaptcha_add_script, $fs_recaptcha_on_comments;

    // skip the captcha if user is logged in and the settings allow
    if (is_user_logged_in() && $fs_recaptcha_opt['bypass_comment'] == 'true')
               // logged in user can bypass recaptcha
               return $comment_field;

    if ($fs_recaptcha_on_comments)
      return $comment_field;

  $fs_recaptcha_add_script = true;

$html = '
<div class="fs-recaptcha-comments">';
$html .= $this->fs_recaptcha_captcha_html('comments', true);
$html .= '</div>
';

    // prevent double captcha fields
    $fs_recaptcha_on_comments = true;
    return $comment_field . "\n" . $html;
} // end function fs_recaptcha_comment_form_logged_in


// this function adds the recaptcha to the login form
function fs_recaptcha_login_form() {
   global $fs_recaptcha_add_script;

  $fs_recaptcha_add_script = true;

// the captcha html - login form
echo '
<div class="fs-recaptcha-login">';
$this->fs_recaptcha_captcha_html('login');
echo '</div>
';

  return true;
} //  end function fs_recaptcha_login_form


// this function adds the captcha to the buddypress inline login form
function fs_recaptcha_inline_login_form() {
   global $fs_recaptcha_add_script;

$fs_recaptcha_add_script = true;

// the captcha html - buddypress sidebar login form
$html = '
<div class="fs-recaptcha-side-login">';
$html .= $this->fs_recaptcha_captcha_html('side_login', true);
$html .=  '</div>
';

  return $html;
} //  end function fs_recaptcha_inline_login_form


// this function adds the captcha to the woocommerce checkout form
function fs_recaptcha_wc_checkout_form() {
   global $fs_recaptcha_opt, $fs_recaptcha_add_script;

 if ($fs_recaptcha_opt['wc_checkout'] == 'true' ) {
    $fs_recaptcha_add_script = true;

// the recaptcha html - woocommerce checkout form
echo '
<div class="fs-recaptcha-wc-checkout">';
$this->fs_recaptcha_captcha_html('wc_checkout');
echo '</div>
';

}
  return true;
} // end function fs_recaptcha_wc_checkout_form


// this function adds the captcha to the register form
function fs_recaptcha_register_form() {
   global $fs_recaptcha_add_script, $fs_recaptcha_add_reg;

  if ( $fs_recaptcha_add_reg )  // prevent double reg captcha fields woocommerce 2
          return true;

   $fs_recaptcha_add_script = true;

// the recaptcha html - register form
echo '
<div class="fs-recaptcha-registration">';
$this->fs_recaptcha_captcha_html('registration');
echo '</div>
';
      // prevent double captcha fields woocommerce 2
  $fs_recaptcha_add_reg = true;

  return true;
} // end function fs_recaptcha_register_form


// this function adds the captcha to the bp register form
function fs_recaptcha_bp_register_form() {
   global $bp, $fs_recaptcha_add_script;

   $fs_recaptcha_add_script = true;

// the recaptcha html - bp register form
if (!empty($bp->signup->errors['fast_secure_recaptcha_field']))
    echo '<div class="error">'. $bp->signup->errors['fast_secure_recaptcha_field']. '</div>';

echo '
<div class="fs-recaptcha-bp-registration">';
$this->fs_recaptcha_captcha_html('registration');
echo '</div>
';

  return true;
} // end function fs_recaptcha_bp_register_form


// this function adds the captcha to the multisite register form
function fs_recaptcha_ms_register_form( $errors ) {
   global $fs_recaptcha_add_script;

   $fs_recaptcha_add_script = true;

   if ( $errmsg = $errors->get_error_message('fast_secure_recaptcha_error') )
			echo '<p class="error">' . $errmsg . '</p>';

// the recaptcha html - ms register form
echo '
<div class="fs-recaptcha-ms-registration">';
$this->fs_recaptcha_captcha_html('registration');
echo '</div>
';

  return true;
} // end function fs_recaptcha_ms_register_form


// this function adds the captcha to the lostpassword form
function fs_recaptcha_lostpassword_form() {
   global $fs_recaptcha_opt, $fs_recaptcha_add_script;

   $fs_recaptcha_add_script = true;

// the recaptcha html - lostpassword form
echo '
<div class="fs-recaptcha-lostpassword">';
$this->fs_recaptcha_captcha_html('lostpassword');
echo '</div>
';

  return true;
} // end function fs_recaptcha_lostpassword_form


  // this function checks the recaptcha posted with BuddyPress registration page
function fs_recaptcha_bp_signup_validate() {
   global $bp, $fs_recaptcha_opt;

   $validate_result = $this->fs_recaptcha_validate_code('registration');
   if($validate_result != 'valid')
        $bp->signup->errors['fast_secure_recaptcha_field'] = $validate_result;
   return;
} // end function fs_recaptcha_bp_signup_validate


  // this function checks the recaptcha posted with multisite registration page
function fs_recaptcha_mu_signup_validate( $result ) {
   global $fs_recaptcha_opt;

   if (isset($_POST['stage']) && 'validate-blog-signup' == $_POST['stage'])
		// user is registering a new blog, recaptcha is not required at this stage
		return $result;

   $validate_result = $this->fs_recaptcha_validate_code('registration');
   if($validate_result != 'valid')
		$result['errors']->add( 'fast_secure_recaptcha_error', $validate_result );
   return $result;
} // end function fs_recaptcha_mu_signup_validate


// multisite
// the user is already registered and is registering a new site
function fs_recaptcha_mu_site_signup_validate(array $result) {

   if (is_user_logged_in()) {
       $validate_result = $this->fs_recaptcha_validate_code('registration');
       if($validate_result != 'valid')
		   $result['errors']->add( 'fast_secure_recaptcha_error', $validate_result );
   }
   return $result;
 } // end fs_recaptcha_mu_site_signup_validate


// this function adds the captcha to the bbPress New Topic and Reply form
function fs_recaptcha_bbpress_topic_form() {
   global $fs_recaptcha_add_script;

   $fs_recaptcha_add_script = true;


echo '
<div class="fs-recaptcha-bbpress-topic">';
$this->fs_recaptcha_captcha_html('bbpress_topic');
echo '</div>
';

  return true;
} // end function fs_recaptcha_bbpress_topic_form


  // this function checks the recaptcha posted with bbPress New Topic and Reply form
function fs_recaptcha_bbpress_topic_validate() {
   global $fs_recaptcha_opt;

   $validate_result = $this->fs_recaptcha_validate_code('bbpress_topic');
      if($validate_result != 'valid') {
       $error = ($fs_recaptcha_opt['text_error'] != '') ? $fs_recaptcha_opt['text_error'] : __('ERROR', 'fast-secure-recaptcha');
       bbp_add_error('fast-secure-recaptcha-wrong', "<strong>$error</strong>: $validate_result");
   }


   return;
} // end function fs_recaptcha_bp_signup_validate


function fs_recaptcha_jetpack_validate($bool) {
       $success = false;

       $validate_result = $this->fs_recaptcha_validate_code('jetpack');
       if($validate_result == 'valid') {
           $success = true;
       }
       if ( !$success && apply_filters('fs_recaptcha_fail', true, $bool) ) {
            $this->jetpack_failed = true;
            return new WP_Error( 'fast_secure_recaptcha_error', $validate_result );
       }
       return $bool;
} // end function fs_recaptcha_jetpack_validate


// append field to jetpack contact form shortcode
function fs_recaptcha_jetpack_form($content) {
  global $fs_recaptcha_add_jetpack;

  //if ( $fs_recaptcha_add_jetpack )     // prevent double captcha fields jetpack
   //       return $content;

   $fs_recaptcha_add_jetpack = true;

        return preg_replace_callback(
            '/\[contact-form(.*?)?\](.*?)?\[\/contact-form\]/si',
            array($this, 'fs_recaptcha_jetpack_append_field_callback'),
            $content );
} // end function fs_recaptcha_jetpack_form


function fs_recaptcha_jetpack_append_field_callback($m)   {
        $fields = isset($m[2]) ? $m[2] : null;
        $_fields = $fields . '[fs-recaptcha]';
        return str_replace($fields, $_fields, array_shift($m));
} // end function fs_recaptcha_jetpack_append_field_callback


function fs_recaptcha_jetpack_shortcode($atts) {
   global $fs_recaptcha_opt, $fs_recaptcha_add_script;

   $fs_recaptcha_add_script = true;

   $text_incorrect = ($fs_recaptcha_opt['text_incorrect'] != '') ? $fs_recaptcha_opt['text_incorrect'] : __('You have selected an incorrect reCAPTCHA value', 'fast-secure-recaptcha');

// the recaptcha html - jetpack contact form
        ob_start();
        ?>

<div class="fs-recaptcha-jetpack">
            <?php $this->fs_recaptcha_captcha_html('jetpack');
            if ( isset($this->jetpack_failed) && $this->jetpack_failed ) : ?>
                <p>
                    <?php echo $text_incorrect; ?>
                </p>
            <?php endif; ?>
</div>
        <?php

        return apply_filters('fs_recaptcha_field', ob_get_clean());
} // end  function fs_recaptcha_jetpack_shortcode


// this function checks the recaptcha posted with registration page
function fs_recaptcha_register_post( $errors = '' ) {
   global $fs_recaptcha_opt, $fs_recaptcha_checkout_validated;

    if ( ! is_wp_error( $errors ) )
          $errors = new WP_Error();

   if ($fs_recaptcha_checkout_validated)
       return $errors; // skip because already validated a captcha at woocommerce checkout, checked the box "Create an account"

   $validate_result = $this->fs_recaptcha_validate_code('registration');
   if($validate_result != 'valid') {
       $error = ($fs_recaptcha_opt['text_error'] != '') ? $fs_recaptcha_opt['text_error'] : __('ERROR', 'fast-secure-recaptcha');
       $errors->add('captcha_error', "<strong>$error</strong>: $validate_result");
   }
   return $errors;
} // end function fs_recaptcha_register_post


// this function checks the recaptcha posted with lost password page
function fs_recaptcha_lostpassword_post( $errors = '' ) {
   global $fs_recaptcha_opt;

   if ( ! is_wp_error( $errors ) )
          $errors = new WP_Error();

   $validate_result = $this->fs_recaptcha_validate_code('lostpassword');
   if($validate_result != 'valid') {
       $error = ($fs_recaptcha_opt['text_error'] != '') ? $fs_recaptcha_opt['text_error'] : __('ERROR', 'fast-secure-recaptcha');

      if ( isset($_POST['wc_reset_password']) && isset($_POST['_wp_http_referer']) ) {
               // woocommerce  /my-account/lost-password/ needs in page error
               $errors->add('fs_recaptcha_error', "<strong>$error</strong>: $validate_result");
               return $errors;
       } else {
               // wp-login.php needs >> Back link
               wp_die( "<strong>$error</strong>: $validate_result", $error, array( 'back_link' => true ) ); // back link makes go back link
       }
   }
   return;

} // function fs_recaptcha_lostpassword_post


// this function checks the recaptcha posted with comments
function fs_recaptcha_comment_post($comment) {
    global $fs_recaptcha_opt;

    // skip the recaptcha if user is logged in and the settings allow
    if (is_user_logged_in() && $fs_recaptcha_opt['bypass_comment'] == 'true') {
           // skip recaptcha
           return $comment;
    }

    // skip recaptcha for comment replies from admin menu
    if ( isset($_POST['action']) && $_POST['action'] == 'replyto-comment' &&
    ( check_ajax_referer( 'replyto-comment', '_ajax_nonce', false ) || check_ajax_referer( 'replyto-comment', '_ajax_nonce-replyto-comment', false )) ) {
          // skip recaptcha
          return $comment;
    }

    // Skip captcha for trackback or pingback
    if ( $comment['comment_type'] != '' && $comment['comment_type'] != 'comment' ) {
               // skip recaptcha
               return $comment;
    }

   $validate_result = $this->fs_recaptcha_validate_code('comments');
   if($validate_result != 'valid') {
       $error = ($fs_recaptcha_opt['text_error'] != '') ? $fs_recaptcha_opt['text_error'] : __('ERROR', 'fast-secure-recaptcha');
       wp_die( "<strong>$error</strong>: $validate_result", $error, array( 'back_link' => true ) ); // back_link makes go back link

   }
   return $comment;
} // end function fs_recaptcha_comment_post


// this is checking login post for recaptcha validation on WP and woocommerce
function fs_recaptcha_check_login_recaptcha($user) {
     global $fs_recaptcha_opt;

     if ( isset($this->is_login) && empty($_POST['log']) && empty($_POST['pwd'])) {
            // woocommerce uses 'logon' and 'password' post vars instead of 'log' and 'pwd', so check this on main wp login page only
            // this is main wp login page and the page just loaded, or form not filled out, don't bother trying to validate recaptcha now
	 		return $user;
     }

		// if the $user object itself is a WP_Error object, we simply append
		// errors to it, otherwise we create a new one.
		$errors = is_wp_error($user) ? $user : new WP_Error();

        // begin Fast Secure reCAPTCHA check
        $validate_result = $this->fs_recaptcha_validate_code('login');
        if($validate_result != 'valid') {
            $print_error = ($fs_recaptcha_opt['text_error'] != '') ? $fs_recaptcha_opt['text_error'] : __('ERROR', 'fast-secure-recaptcha');
			$errors->add('recaptcha-error', "<strong>$print_error</strong>: $validate_result");

			// invalid recaptcha detected, the returned $user object should be a WP_Error object
			$user = is_wp_error($user) ? $user : $errors;

			// do not allow WordPress to try authenticating the user, either using cookie or username/password pair
			remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
			remove_filter('authenticate', 'wp_authenticate_cookie', 30, 3);
		}

		return $user;
} // end function fs_recaptcha_check_login_recaptcha


// this function checks the recaptcha posted with woocommerce checkout page
function fs_recaptcha_wc_checkout_post() {
   global $fs_recaptcha_opt, $fs_recaptcha_checkout_validated;

   if ($fs_recaptcha_opt['wc_checkout'] == 'true' ) {
      $validate_result = $this->fs_recaptcha_validate_code('wc_checkout');
      if($validate_result != 'valid') {
            wc_add_notice( $validate_result, 'error' );
      }  else {
            $fs_recaptcha_checkout_validated = true;
      }
   } else {
           $fs_recaptcha_checkout_validated = true;   // always allow registering during checkot
   }
   return;

} // function fs_recaptcha_wc_checkout_post


// check if the posted recaptcha code was valid for any of the forms called
function fs_recaptcha_validate_code($form_id = 'comments') {
       global $fs_recaptcha_opt;

  if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'lostpassword' && $form_id == 'login')
        return 'valid';  // fixes lostpassword page because add_filter('login_errors' is also being called before

        $text_incorrect = ($fs_recaptcha_opt['text_incorrect'] != '') ? $fs_recaptcha_opt['text_incorrect'] : __('You have selected an incorrect reCAPTCHA value', 'fast-secure-recaptcha');

        // validate the recaptcha now
        if( isset( $_POST['g-recaptcha-response'] ) ) {
               $ip = (isset( $_SERVER['REMOTE_ADDR'] )) ? $_SERVER['REMOTE_ADDR'] : '';
               $result =  wp_remote_get( "https://www.google.com/recaptcha/api/siteverify?secret=".sanitize_text_field(urlencode($fs_recaptcha_opt['secret_key']))."&response=" .sanitize_text_field(urlencode($_POST['g-recaptcha-response']))."&remoteip=" . sanitize_text_field(urlencode($ip)) );
               if ( is_wp_error( $result ) || empty( $result['body'] ) ) {
                   $return_text =  __( 'Error connecting to the reCAPTCHA API host.', 'fast-secure-recaptcha' );
                   if( WP_DEBUG === true )  // if debugging is on, maybe add more detailed info on why it failed
                       $return_text .=  ' ( '. $result->get_error_message() .' )';
			       return $return_text;
		       }
              $response = json_decode( wp_remote_retrieve_body( $result ), true );

             if( $response["success"] ) {
                     // ok, can continue
                     return 'valid';
             } else {
                     // failed the recaptcha
                     return $text_incorrect;
             }
        } else {
                 // did not check the box I am not a robot?
                 return $text_incorrect;
        }
        return $text_incorrect;

} // end function fs_recaptcha_validate_code


// displays the reCAPTCHA html in all the forms as called
function fs_recaptcha_captcha_html( $form_id = 'comments', $no_echo = false) {
  global $fs_recaptcha_opt, $fs_add_recaptcha_js_array;

        $theme = ($fs_recaptcha_opt['dark'] == 'true') ? 'dark' : 'light';
        $size = ($fs_recaptcha_opt['captcha_small'] == 'true') ? 'compact' : 'normal';
        $fix_size = ($size == 'normal' ) ? 'style="transform:scale(0.9);-webkit-transform:scale(0.9);transform-origin:0 0;-webkit-transform-origin:0 0;"' : '';

        // print the recaptcha div and make compatible with multiforms
        $fs_add_recaptcha_js_array[] = "$form_id||".esc_attr($fs_recaptcha_opt['site_key'])."||$size||$theme"; // used to build js
        $html = "\n<div " . 'id="fs_recaptcha_'. $form_id .'" '. $fix_size .'></div>
';

  if ( $no_echo ) return $html;
  echo $html;

} // end function fs_recaptcha_captcha_html


// only load this javascript on the blog pages where recaptcha needs to display
// for loading recaptcha js in footer conditionally if form has recaptcha enabled or not
// makes multiforms compatible on same page
function fs_recaptcha_add_script(){
   global $fs_recaptcha_opt, $fs_recaptcha_add_script, $fs_add_recaptcha_js_array;

   if (!$fs_recaptcha_add_script)
      return;

                $string = '
<!-- Fast Secure reCAPTCHA plugin - begin recaptcha js -->
<script type="text/javascript">
';
		foreach ( $fs_add_recaptcha_js_array as $v ) {
                  //"$form_id||$site_key||$size||$theme";
                   $pieces = explode("||", $v);
			$string .= "var fs_recaptcha_$pieces[0];
";
        }

      $string .= 'var fsReCAPTCHA = function() {
// render all collected Fast Secure reCAPTCHA instances
// note if you have other recaptcha plugins, one of the plugins might not load any recaptchas
// however this plugin is compatible with the recaptcha on Fast Secure Contact Form plugin';
      $wc_checkout = false;
       foreach ( $fs_add_recaptcha_js_array as $v ) {
                  //"$form_id||$site_key||$size||$theme";
          $pieces = explode("||", $v);
          if ($pieces[0] == 'wc_checkout')
              $wc_checkout = true;
		  $string .= "
fs_recaptcha_".esc_js($pieces[0])." = grecaptcha.render('fs_recaptcha_".esc_js($pieces[0])."', {'sitekey' : '".esc_js($pieces[1])."', 'size' : '".esc_js($pieces[2])."', 'theme' : '".esc_js($pieces[3])."'});";

	   }

        $string .= '
};
';
if ($wc_checkout) {
$string .= "
// resets the woocommerce checkout reCAPTCHA on checkout_error event
jQuery(function() {
  // Add an event listener.
  jQuery( document.body ).on('checkout_error', function() {
    grecaptcha.reset(fs_recaptcha_wc_checkout);
  });
});
";
}
$string .= "
</script>
<!-- Fast Secure reCAPTCHA plugin - end recaptcha js -->  \n\n";

        echo $string;
        $lang = ($fs_recaptcha_opt['language'] != 'auto' ) ? '&hl='.$fs_recaptcha_opt['language'] : '' ;
        // makes multiforms compatible on same page
        wp_enqueue_script( 'fast-secure-recaptcha', 'https://www.google.com/recaptcha/api.js?onload=fsReCAPTCHA&render=explicit'. esc_js( $lang ) );

  // prevent double action here
  $fs_recaptcha_add_script = false;

} // end  function fs_recaptcha_add_script


// to support multiple forms on one page, google recaptcha js needs to be loded async defer
// make wordpress wp_enqueue_script load js async defer, but only for this fast-secure-recaptcha handle
function fs_recaptcha_make_script_async( $tag, $handle, $src ) {

    if ( 'fast-secure-recaptcha' != $handle )
        return $tag;

    return str_replace( '<script', '<script async defer', $tag );

} // end function fs_recaptcha_make_script_async


function fs_recaptcha_determine_current_page() {
		// only strip the host and scheme (including https), so
		// we can properly compare with REQUEST_URI later on.
		$login_path    = preg_replace('#https?://[^/]+/#i', '', wp_login_url());
		$register_path = preg_replace('#https?://[^/]+/#i', '', wp_registration_url());
        $lostpassword_path = preg_replace('#https?://[^/]+/#i', '', wp_lostpassword_url());
        $myaccount_page_url = '';
        if ( class_exists( 'WooCommerce' ) ) {
              $myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
             if ( $myaccount_page ) {
               $myaccount_page_url =  preg_replace('#https?://[^/]+/#i', '',  get_permalink( $myaccount_page ) );
              }
        }

		global $pagenow;

		$request_uri = ltrim($_SERVER['REQUEST_URI'], '/');
        if (!empty($lostpassword_path) && strpos($request_uri, $lostpassword_path) === 0) {
			// user is requesting lost password page
			$this->is_lostpassword = true;
		}
		elseif (!empty($register_path) && strpos($request_uri, $register_path) === 0) {
			// user is requesting regular user registration page
			$this->is_reg = true;
		}
        elseif (  (class_exists( 'WooCommerce' ) && $myaccount_page_url != '') && strpos($request_uri, $myaccount_page_url) === 0) {
            // user is requesting woocommerce registration page
			$this->is_reg = true;
        }
		elseif (!empty($login_path) && strpos($request_uri, $login_path) === 0) {
			// user is requesting the wp-login page
			$this->is_login = true;
		}
		elseif (!empty($pagenow) && $pagenow == 'wp-signup.php') {
			// user is requesting wp-signup page (multi-site page for user/site registration)
			$this->is_signup = true;
		}
} // function fs_recaptcha_determine_current_page


} // end of class
} // end of if ! class


if (class_exists("fsRecaptcha")) {
 $fs_recaptcha = new fsRecaptcha();
}

if (isset($fs_recaptcha)) {
   global $fs_recaptcha_opt;

  // init actions
  add_action('init', array(&$fs_recaptcha, 'fs_recaptcha_init'));

  // admin options
  add_action('admin_menu', array(&$fs_recaptcha,'fs_recaptcha_admin_menu'),1);
  add_action('admin_head', array(&$fs_recaptcha,'fs_recaptcha_admin_head'),1);

  register_activation_hook(__FILE__, array(&$fs_recaptcha, 'fs_recaptcha_admin_activated'), 1);

  add_action('admin_notices',array( &$fs_recaptcha , 'fs_recaptcha_api_key_notice'));
  add_action('network_admin_notices',array( &$fs_recaptcha , 'fs_recaptcha_api_key_notice'));

  // adds "Settings" link to the plugin action page
  add_filter( 'plugin_action_links', array(&$fs_recaptcha,'fs_recaptcha_plugin_action_links'),10,2);

}

// end of file