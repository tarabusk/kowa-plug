<?php

/*
Fast Secure reCAPTCHA (uninstall file)
author Mike Challis
*/

/*
 * Wordpress will run the code in this file when the user deletes the plugin
 * 
 */

// Be sure that Wordpress is deleting the plugin
if(defined('WP_UNINSTALL_PLUGIN') ){

    // settings get deleted when plugin is deleted from admin plugins page
    delete_option('fast_secure_recaptcha');
    delete_transient('fast_secure_recaptcha_admin_notice');
    delete_transient('fast_secure_recaptcha_info');

    if ( is_multisite() && get_current_blog_id() == 1 )
        delete_site_option('fast_secure_recaptcha');

    // Clear any cached data that has been removed
	wp_cache_flush();

}  
// end of file  