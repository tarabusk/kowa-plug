<?php
/*
Plugin Name: SearchWP bbPress Integration
Plugin URI: https://searchwp.com/
Description: Integrates SearchWP and bbPress
Version: 1.2.4
Author: SearchWP, LLC
Author URI: https://searchwp.com/

Copyright 2013-2016 Jonathan Christopher

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'SEARCHWP_BBPRESS_VERSION' ) ) {
	define( 'SEARCHWP_BBPRESS_VERSION', '1.2.4' );
}

/**
 * instantiate the updater
 */
if ( ! class_exists( 'SWP_Bbpress_Updater' ) ) {
	// load our custom updater
	include_once( dirname( __FILE__ ) . '/vendor/updater.php' );
}

// set up the updater
function searchwp_bbpress_update_check() {

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return false;
	}

	// environment check
	if ( ! defined( 'SEARCHWP_PREFIX' ) ) {
		return false;
	}

	if ( ! defined( 'SEARCHWP_EDD_STORE_URL' ) ) {
		return false;
	}

	if ( ! defined( 'SEARCHWP_BBPRESS_VERSION' ) ) {
		return false;
	}

	// retrieve stored license key
	$license_key = trim( get_option( SEARCHWP_PREFIX . 'license_key' ) );
	$license_key = sanitize_text_field( $license_key );

	// instantiate the updater to prep the environment
	$searchwp_bbpress_updater = new SWP_Bbpress_Updater( SEARCHWP_EDD_STORE_URL, __FILE__, array(
			'item_id' 	=> 33686,
			'version'   => SEARCHWP_BBPRESS_VERSION,
			'license'   => $license_key,
			'item_name' => 'bbPress Integration',
			'author'    => 'Jonathan Christopher',
			'url'       => site_url(),
		)
	);

	return $searchwp_bbpress_updater;
}

add_action( 'admin_init', 'searchwp_bbpress_update_check' );

class SearchWPbbPress {

	function __construct() {
		add_filter( 'bbp_register_forum_post_type', array( $this, 'include_post_type_in_search' ) );
		add_filter( 'bbp_register_topic_post_type', array( $this, 'include_post_type_in_search' ) );
		add_filter( 'bbp_register_reply_post_type', array( $this, 'include_post_type_in_search' ) );

		add_filter( 'searchwp_enable_attribution_forum', '__return_false' );

		add_filter( 'searchwp_enable_parent_attribution_topic', '__return_true' );
		add_filter( 'searchwp_enable_parent_attribution_reply', '__return_true' );

		add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), array( $this, 'plugin_row' ), 11 );

		// Exclude Private Forums
		add_filter( 'searchwp_exclude',          array( $this, 'get_topic_reply_ids_from_private_forums' ) );
		add_filter( 'searchwp_prevent_indexing', array( $this, 'get_topic_reply_ids_from_private_forums' ) );

		// Exclude Hidden Forums
		add_filter( 'searchwp_exclude',          array( $this, 'get_topic_reply_ids_from_hidden_forums' ) );
		add_filter( 'searchwp_prevent_indexing', array( $this, 'get_topic_reply_ids_from_hidden_forums' ) );
	}

	function include_post_type_in_search( $args ) {
		$args['exclude_from_search'] = false;

		return $args;
	}

	/**
	 * Exclude any Topics or Replies that have parents that are within a Private Forum
	 *
	 * @return array Post IDs
	 */
	function get_topic_reply_ids_from_private_forums( $excluded ) {

		if ( ! apply_filters( 'searchwp_bbpress_exclude_private', true ) ) {
			return $excluded;
		}

		// Get all Private Forums
		$private_forum_ids = get_posts( array(
			'nopaging'    => true,
			'post_type'   => 'forum',
			'post_status' => array( 'private' ),
			'fields'      => 'ids'
		) );

		// If there were no Private forums, short circuit
		if ( empty( $private_forum_ids ) ) {
			return $excluded;
		}

		// Get all IDs of Topics and Replies from Private Forums
		$private_topic_ids = get_posts( array(
			'nopaging'         => true,
			'post_type'        => 'any',
			'post_parent__in'  => array_map( 'absint', $private_forum_ids ),
			'fields'           => 'ids'
		) );

		$private_reply_ids = array();

		if ( ! empty( $private_topic_ids ) ) {
			$private_reply_ids = get_posts( array(
				'nopaging'         => true,
				'post_type'        => 'any',
				'post_parent__in'  => array_map( 'absint', $private_topic_ids ),
				'fields'           => 'ids'
			) );
		}

		if ( is_array( $excluded ) ) {
			$excluded = array_merge( $excluded, $private_topic_ids );
			$excluded = array_merge( $excluded, $private_reply_ids );
			$excluded = array_unique( $excluded );
		}

		return array_map( 'absint', $excluded );
	}

	/**
	 * Exclude any Topics or Replies that have parents that are within a Hidden Forum
	 *
	 * @return array Post IDs
	 */
	function get_topic_reply_ids_from_hidden_forums( $excluded ) {

		if ( ! apply_filters( 'searchwp_bbpress_exclude_hidden', true ) ) {
			return $excluded;
		}

		// Get all Hidden Forums
		$hidden_forum_ids = get_posts( array(
			'nopaging'    => true,
			'post_type'   => 'forum',
			'post_status' => array( 'hidden' ),
			'fields'      => 'ids'
		) );

		// If there were no Hidden forums, short circuit
		if ( empty( $hidden_forum_ids ) ) {
			return $excluded;
		}

		// Get all IDs of Topics and Replies from Hidden Forums
		$hidden_topic_ids = get_posts( array(
			'nopaging'         => true,
			'post_type'        => 'any',
			'post_parent__in'  => array_map( 'absint', $hidden_forum_ids ),
			'fields'           => 'ids'
		) );

		$hidden_reply_ids = array();
		if ( ! empty( $hidden_topic_ids ) ) {
			$hidden_reply_ids = get_posts( array(
				'nopaging'         => true,
				'post_type'        => 'any',
				'post_parent__in'  => array_map( 'absint', $hidden_topic_ids ),
				'fields'           => 'ids'
			) );
		}

		if ( is_array( $excluded ) ) {
			$excluded = array_merge( $excluded, $hidden_topic_ids );
			$excluded = array_merge( $excluded, $hidden_reply_ids );
			$excluded = array_unique( $excluded );
		}

		return array_map( 'absint', $excluded );
	}

	function plugin_row() {
		if ( ! class_exists( 'SearchWP' ) ) {
			return;
		}

		$searchwp = SWP();
		if ( version_compare( $searchwp->version, '1.0.10', '<' ) ) { ?>
			<tr class="plugin-update-tr searchwp">
				<td colspan="3" class="plugin-update">
					<div class="update-message">
						<?php esc_html_e( 'SearchWP bbPress Integration requires SearchWP 1.0.10 or greater', 'searchwp' ); ?>
					</div>
				</td>
			</tr>
		<?php }
	}
}

new SearchWPbbPress();
