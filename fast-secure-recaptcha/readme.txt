=== Fast Secure reCAPTCHA ===
Contributors: fastsecure
Author URI: http://www.642weather.com/weather/scripts.php
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2NA2XBX9WG98L
Tags: captcha, recaptcha, buddypress, bbpress, woocommerce, wpforo, multisite, jetpack, comment, comments, login, register, anti-spam, spam, security
Requires at least: 3.6.0
Tested up to: 4.8
Stable tag: 1.0.19
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 

Adds Google NO CAPTCHA reCAPTCHA on the forms for comments, login, registration, lost password, BuddyPress, bbPress, wpForo, and WooCommerce checkout.

== Description ==

Adds No CAPTCHA reCAPTCHA V2 anti-spam to WordPress pages for comments, login, registration, lost password, BuddyPress register, bbPress register, wpForo register, bbPress New Topic and Reply to Topic Forms, Jetpack Contact Form, and WooCommerce checkout. In order to post comments, login, or register, users will have to pass the reCAPTCHA V2 "I'm not a robot" test. This prevents spam from automated bots, adds security, and is even compatible Akismet. Compatible with Multisite Network Activate. 

= Help Keep This Plugin Free =

If you find this plugin useful to you, please consider [__making a small donation__](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2NA2XBX9WG98L) to help contribute to my time invested and to further development. Thanks for your kind support! - [__Mike Challis__](http://profiles.wordpress.org/users/MikeChallis/)


Features:
--------
 * Google No CAPTCHA reCAPTCHA V2 "I'm not a robot".
 * Configure your free reCAPTHCA V2 site keys on the Settings page.
 * Optional setting to hide the Comments reCAPTCHA from logged in users.
 * Enable or disable the reCAPTCHA on any of the pages for comments, login, registration, lost password, BuddyPress register, bbPress register, wpForo register, Jetpack Contact Form, and WooCommerce checkout.
 * Login form - WordPress, BuddyPress, bbPress, wpForo Forum, WooCommerce, WP Multisite
 * Lost Password form - WordPress, BuddyPress, bbPress, wpForo Forum, WooCommerce, WP Multisite. 
 * Register form - WordPress, BuddyPress, bbPress, wpForo Forum, WooCommerce, WP Multisite.
 * Comment form - WordPress, WP Multisite.  
 * Signup new site - WP Multisite.
 * Checkout form - WooCommerce.
 * Jetpack Contact Form.
 * bbPress New Topic, Reply to Topic Forms.
 * You can disable any of the forms you don't want reCAPTCHA on.
 * I18n language translation support.
 * Compatible with Akismet.
 * Compatible with Multisite Network Activate.
 * Compatible with the reCAPTCHA on Fast Secure Contact Form.


== Installation ==

= How to Install on WordPress =

1. Install automatically through the `Plugins`, `Add New` menu in WordPress, find in the Plugins directory, click Install, or upload the `fast-secure-recaptcha.zip` file.

2. Activate the plugin through the `Plugins` menu in WordPress.

3. Enter your reCAPTCHA site keys on the settings page, be sure to select all the forms you want to protect.

4. Updates are automatic. Click on "Upgrade Automatically" if prompted from the admin menu. If you ever have to manually upgrade, simply deactivate, uninstall, and repeat the installation steps with the new version. 

= How to install on WordPress Multisite with Network Activate and Main site control of the settings =

1. Install the plugin from Network Admin menu then click Network Activate.

2. Go to the Main site dashboard and click on settings for this new plugin. 

3. Enter your reCAPTCHA site keys on the settings page, be sure to select all the forms you want to protect. All the settings configured here will be applied to all the sites. Other site admins cannot see or change the settings.


= How to install on WordPress Multisite with Network Activate and individual site control of the settings =

1. Install the plugin from Network Admin menu then click Network Activate.

2. Go to the Main site dashboard and click on settings for this new plugin. Enter your reCAPTCHA site keys on the settings page, be sure to select all the forms you want to protect. 

3. Check the setting: "Allow Multisite network activated sites to have individual Fast Secure reCAPTCHA settings." Now each site admin will have to enter reCAPTCHA site keys on their dashboard Fast Secure reCAPTCHA settings page, and be sure to select all the forms to protect.

4. Optionally check the setting: "Allow Multisite network activated main site to copy main site reCAPTCHA API keys to all other sites." This will automatically copy main site API keys to all other sites.


== Screenshots ==

1. screenshot-1.png is the reCAPTCHA on the comment form.

2. screenshot-2.png is the reCAPTCHA on the login form.

3. screenshot-3.png is the `Settings` page.


== Configuration ==

After the plugin is activated, you can configure it by selecting the `Fast Secure reCAPTCHA` link on the `Settings` menu.
Be sure to configure your site keys and select which forms you want to protect.

== Usage ==

Enter your site keys and configure the settings, then test your pages to be sure the reCAPTCHA is working properly.


== Frequently Asked Questions ==

= How does it work? =

In the No CAPTCHA reCAPTCHA V2 system, users are asked to click on a "I'm not a robot" checkbox (the system will verify if the user is a human or not, for example, with some clues such as already-known ip addresses, cookies, or mouse movements within the ReCAPTCHA frame) or, if it fails, select one or more images from a selection of nine images.

= What are spammers doing anyway? =

= Human spammers = 
They actually visit your form and fill it out including the CAPTCHA.

= Human or Spambot probes =
Sometimes contain content that does not make any sense (jibberish). Humans or Spam bots will try to target any forms that they discover. They first attempt an email header injection attack to use your web form to relay spam emails. This form is to prevent relaying email to other addresses. After failing that, they simply submit the form with a spammy URL or black hat SEO text with embedded HTML, hoping someone will be phished or click the link.

= Blackhat SEO spammers = 
Spamming blog comment forms, contact forms, Wikis, etc. By using randomly generated unique "words", they can then do a Google search to find websites where their content has been posted un-moderated. Then they can go back to these websites, identify if the links have been posted without the rel="nofollow" attribute (which would prevent them contributing to Google's algorithm), and if not they can post whatever spam links they like on those websites, in an effort to boost Google rankings for certain sites. Or worse, use it to post whatever content they want onto those websites, even embedded malware.

= Human-powered CAPTCHA solvers =
It is easy and cheap for someone to hire a person to enter this spam. Usually it can be done for about $0.75 for 1,000 or so form submissions. The spammer gives their employee a list of sites and what to paste in and they go at it. Not all of your spam (and other trash) will be computer generated - using CAPTCHA proxy or farm the bad guys can have real people spamming you. A CAPTCHA farm has many cheap laborers (India, far east, etc) solving them. CAPTCHA proxy is when they use a bot to fetch and serve your image to users of other sites, e.g. porn, games, etc. After the CAPTCHA is solved, they post spam to your form.

= Will this plugin be updated to use Google Invisible reCAPTCHA or can it use Invisible reCAPTCHA already? =

Invisible reCAPTCHA needs totally different keys and all different coding for the javascript. I am not going to try changing it right now. I already have reCAPTCHA V2 working just fine and it's also compatible with my Fast Secure Contact Form plugin. I don’t see any any advantage for the Invisible version. The checkbox is really not that much effort anyway. There are no security advantages to it. Invisible ReCAPTCHA isn’t really invisible anyway because you still have to display the Google badge and it will even fully come up with “click the images with signs” if they fail the robot test. Nothing invisible about it. Maybe I will add it someday if the invisible version takes over in popularity or security.

= Spammers have been able to bypass my reCAPTCHA, what can I do? =

Make sure you have entered your site keys and enabled the reCAPTCHA on your forms.

The reCAPTCHA will not show to logged in users posting comments if you have enabled this setting: 'No comment form reCAPTCHA for logged in users'. Maybe a logged in user is the spammer.

Check for a plugin conflict.
A plugin conflict can break the validation test so that the reCAPTCHA is never checked.
Be sure to always test all the comments, login, registration, and lost password reCAPTCHA forms after installing or updating themes or plugins. 

Troubleshoot plugin conflicts, see troubleshooting below.


Sometimes your site becomes targeted by a human spammer or a spam bot and human captcha solver. Google reCAPTCHA is usually very good at blocking this, but if the issue persists, try the following suggestions:

Try allowing only Registered users to post, and or moderating comments.
Read more about [Combating Comment Spam](http://codex.wordpress.org/Combating_Comment_Spam)

Filter Spam with Akismet – The [Akismet plugin](https://docs.akismet.com/getting-started/activate/) filters spam comments. Akismet should able to block most of or all spam that comes in. 


= Troubleshooting the reCAPTCHA does not display, or it does not block the form properly =
This plugin automatically puts the google api JavaScript in the footer and loads it async defer. This plugin automatically loads all necessary HTML and JavaScript needed for the reCAPTCHA. Make sure you did not put any google reCAPTCHA javascript or HTML in your theme or it will for sure will break things.

Another plugin could be causing a conflict. You may have a conflict with another reCAPTCHA plugin. Do you have any other plugins that load a reCAPTCHA?
Temporarily deactivate other plugins to see if the reCAPTCHA starts working. 

Most reCAPTCHA plugins are not compatible with each other because of javascript onload conflicts but I know how to make my plugins compatible with each other. This plugin is compatible with the reCAPTCHA you can enable on my other plugin Fast Secure Contact Form.

Your theme could be missing the wp_head or wp_footer PHP tag. Your theme should be considered broken if the wp_head or wp_footer PHP tag is missing.

Do this as a test:
In Admin, click on Appearance, Themes. Temporarily activate your theme to one of the default default themes. 
It does not cause any harm to temporarily change the theme, test and then change back. Does it work properly with the default theme?
If it does then the theme you are using is the cause. 

= The reCAPTCHA does not display on JetPack comments form =
If you have JetPack comments module enabled then captcha/recaptca/anti-spam plugins will not work on your comments form because the comments are then loaded in an iFrame from WordPress.com The solution is to disable the comments module in JetPack, then the reCAPTCHA plugin will work correctly on your comments form.

= The reCAPTCHA does not display on the comments form =
Make sure that the theme comments.php file contains at least one of the standard hooks: 
`do_action ( 'comment_form_logged_in_after' );`
`do_action ( 'comment_form_after_fields' );` 
`do_action ( 'comment_form' );` 
If you didn't find one of these hooks, then put this string in the comment form: 
`<?php do_action( 'comment_form', $post->ID ); ?>` 

= The reCAPTCHA is not working and I cannot login at my login page =
This failure could have been caused by another plugin conflict with this one.
If you enabled reCAPTCHA on the login form and are locked out due to reCAPTCHA is broken, here is how to get back in:
FTP to your WordPress directory `/wp-content/plugins/` then delete this folder: 
`fast-secure-recaptcha`
This manually removes the plugin so you should be able to login again. 

= My reCAPTCHA has error for site owner: Invalid site key =
The google reCAPTCHA keys are domain specific, make sure to get keys for each web site domain you install it on.
On the plugin settings page, enter your two Google reCAPTCHA keys for the domain of your site. Included right there is a link to get free keys. Finally click the Save button. 


== Changelog ==

= 1.0.20 =
* (20 June 2017) - Fix readme

= 1.0.19 =
* (13 May 2017) - Fix possible Catchable fatal error on WooCommerce password reset.

= 1.0.18 =
* (04 May 2017) - Revert changes to last update to fix missing reCAPTCHA on JetPack Contact form.

= 1.0.17 =
* (04 May 2017) - Fix rare but possible double reCAPTCHA on JetPack Contact form.

= 1.0.16 =
* (02 May 2017) - Fix "You have selected an incorrect reCAPTCHA value" error on WooCommerce checkout page if "Create an account" is checked and Enable reCAPTCHA on WooCommerce checkout is disabled.

= 1.0.15 =
* (21 Apr 2017) - Fix "You have selected an incorrect reCAPTCHA value" error on WooCommerce checkout page if "Create an account" is checked.

= 1.0.14 =
* (20 Apr 2017) - Fix WooCommerce /my-account/lost-password/ page validation error causes cannot click "Reset password".

= 1.0.13 =
* (10 Apr 2017) - Fix double reCAPTCHA on WooCommerce register My Account forms WooCommerce 2.

= 1.0.12 =
* (10 Apr 2017) - Fix reCAPTCHA did not work on WooCommerce register My Account forms since WooCommerce 3.

= 1.0.11 =
* (21 Mar 2017) - Fix reCAPTCHA did not reset on WooCommerce checkout page during a checkout error event requiring a page reload to fill out the form again.
- Fix registration and login form reCAPTCHA not loading sometimes.

= 1.0.10 =
* (14 Mar 2017) - Fix the normal size reCAPTCHA was too wide on the login, register, and lost password forms.

= 1.0.9 =
* (10 Mar 2017) - Make sure it is branded as the reCAPTCHA V2 "I'm not a robot", not Invisible reCAPTCHA.
- The size setting now applies to all the forms. It defaults to normal (wide), and can be set to compact.

= 1.0.8 =
* (03 Mar 2017) - Fixed reCAPTCHA not loading on register form on BuddyPress when Extended Profiles is disabled.
- Fixed reCAPTCHA not loading on JetPack Contact Form in a widget.

= 1.0.7 =
* (27 Feb 2017) - Fixed WooCommerce checkout reCAPTCHA was still on the form when not enabled.

= 1.0.6 =
* (25 Feb 2017) - Fixed bbPress Register form did not have the reCAPTCHA.
- Added support for bbPress New Topic and Reply to Topic Forms.

= 1.0.5 =
* (18 Feb 2017) - Fix reCAPTCHA not showing on WooCommerce /my-account/ page when "My account page" is enabled in WooCommerce settings.
- Fix reCAPTCHA missing on comment form on some old themes.

= 1.0.4 =
* (14 Feb 2017) - Added reCAPTCHA for Jetpack Contact Form.
- Improved text on enable forms settings.

= 1.0.3 =
* (12 Feb 2017) - Fixed reCAPTCHA on wpForo Registration page was not working unless comment form was also checked.

= 1.0.2 =
* (12 Feb 2017) - Added reCAPTCHA for wpForo Registration page. (you can enable/disable it on the settings page).

= 1.0.1 =
* (12 Feb 2017) - Update Mike's plugin links

= 1.0.0 =
* (05 Feb 2017) - Initial Release

== Upgrade Notice ==

There is no need to upgrade just yet.



