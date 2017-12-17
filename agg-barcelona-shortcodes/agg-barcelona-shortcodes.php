<?php
/*
 * Plugin Name:  Barcelona. Shortcodes
 * Description:  Provides shortcodes for Barcelona theme
 * Version:      1.0.0
 * Author:       Aggressive Motions
 * Author URI:   http://themeforest.net/licenses/terms/extended
 */

$barcelona_shortcodes = array(
	'ad',
	'icon',
	'accordion',
	'accordion_item',
	'tabs',
	'tab',
	'row',
	'col'
);

foreach ( $barcelona_shortcodes as $k ) {
	add_shortcode( 'agg_'. $k, 'barcelona_'. $k .'_sc' );
}

function barcelona_icon_sc( $atts, $content=NULL ) {

	$atts = shortcode_atts( array(
		'icon' => '',
		'color' => ''
	), $atts );

	$output = '';

	if ( ! empty( $atts['icon'] ) ) {

		$atts['icon'] = preg_replace( '#^fa\-#', '', $atts['icon'] );

		$barcelona_attr_color = empty( $atts['color'] ) ? '' : ' style="color: ' . esc_attr( $atts['color'] ) . ';"';

		$output = '<span class="fa fa-'. sanitize_html_class( $atts['icon'] ) .' sc-fa"'. $barcelona_attr_color .'></span>';

	}

	return $output;

}

function barcelona_ad_sc( $atts, $content=NULL ) {

	$atts = shortcode_atts( array(
		'image' => '',
		'width' => '',
		'height' => '',
		'url' => '',
		'target' => '_self',
		'alt' => ''
	), $atts );

	$output = '<div class="barcelona-sc-ad">
				<a href="'. esc_url( $atts['url'] ) .'" target="'. esc_attr( $atts['target'] ) .'">
					<img src="'. esc_url( $atts['image'] ) .'" width="'. esc_attr( $atts['width'] ) .'" height="'. esc_attr( $atts['width'] ) .'" alt="'. esc_attr( $atts['alt'] ) .'" />
				</a>
			   </div>';

	return $output;

}

function barcelona_accordion_sc( $atts, $content=NULL ) {

	static $barcelona_accordion_sc_count = 1;

	$atts = shortcode_atts( array(
		'multiselectable' => 'false'
	), $atts );

	if ( $atts['multiselectable'] != 'false' ) {
		$atts['multiselectable'] = 'true';
	}

	$id = 'accordion'. intval( $barcelona_accordion_sc_count );

	$content = do_shortcode( $content );

	if ( $atts['multiselectable'] == 'false' ) {
		$content = str_replace( ' data-parent="#accordion"', ' data-parent="#' . esc_attr( $id ) . '"', $content );
	}

	$output = '<div id="'. $id .'" class="barcelona-sc-accordion panel-group" role="tablist" aria-multiselectable="'. esc_attr( $atts['multiselectable'] ) .'">'. $content .'</div>';

	$barcelona_accordion_sc_count++;

	return $output;

}

function barcelona_accordion_item_sc( $atts, $content=NULL ) {

	static $barcelona_accordion_item_sc_count = 1;

	$atts = shortcode_atts( array(
		'title' => 'Title'
	), $atts );

	$headingId = 'heading'. intval( $barcelona_accordion_item_sc_count );
	$collapseId = 'collapse'. intval( $barcelona_accordion_item_sc_count );
	$in_cls = $barcelona_accordion_item_sc_count === 1 ? ' in' : '';

	$content = apply_filters( 'the_content', $content );

	$output = '<div class="panel">
				<div class="panel-heading" role="tab" id="'. esc_attr( $headingId ) .'">
					<h3 class="panel-title">
						<a role="button" data-toggle="collapse" data-parent="#accordion" href="#'. esc_attr( $collapseId ) .'" aria-controls="'. esc_attr( $collapseId ) .'"'. ( empty( $in_cls ) ? ' class="collapsed" aria-expanded="false"' : ' aria-expanded="true"' ) .'>
							<span class="barcelona-toggle-icon"><span class="fa fa-plus"></span><span class="fa fa-minus"></span></span><span class="inner">'. esc_html( $atts['title'] ) .'</span>
						</a>
					</h3>
				</div>
				<div id="'. esc_attr( $collapseId ) .'" class="panel-collapse collapse'. $in_cls .'" aria-labelledby="'. esc_attr( $headingId ) .'">
					<div class="panel-body">'. $content .'</div>
				</div>
			   </div>';

	$barcelona_accordion_item_sc_count++;

	return $output;

}

function barcelona_tabs_sc( $atts, $content=NULL ) {

	$atts = shortcode_atts( array(
		'theme' => 'light'
	), $atts );

	if ( $atts['theme'] != 'dark' ) {
		$atts['theme'] = 'light';
	}

	$output = '<div class="barcelona-sc-tab '. sanitize_html_class( $atts['theme'] ) .'"><div class="tab-body">'. do_shortcode( $content ) .'</div></div>';

	return $output;

}

function barcelona_tab_sc( $atts, $content=NULL ) {

	static $barcelona_tab_sc_count = 1;

	$atts = shortcode_atts( array(
		'title' => 'Title'
	), $atts );

	$content = apply_filters( 'the_content', $content );
	$tabId = 'section'. intval( $barcelona_tab_sc_count );

	$output = '<div class="tab-content post-content" id="'. esc_attr( $tabId ) .'"><button type="button" class="btn btn-default" data-controls="#'. esc_attr( $tabId ) .'">'. esc_html( $atts['title'] ) .'</button>'. $content .'</div>';

	$barcelona_tab_sc_count++;

	return $output;

}

function barcelona_row_sc( $atts, $content=NULL ) {

	$atts = shortcode_atts( array(), $atts );

	return '<div class="row barcelona-sc-row">'. do_shortcode( $content ) .'</div>';

}

function barcelona_col_sc( $atts, $content=NULL ) {

	$atts = shortcode_atts( array(
		'size' => '1/2'
	), $atts );

	$barcelona_size_map = array(
		'1/1' => 'col-xs-12',
		'1/2' => 'col-xs-6',
		'2/2' => 'col-xs-12',
		'1/3' => 'col-xs-4',
		'2/3' => 'col-xs-8',
		'3/3' => 'col-xs-12',
		'1/4' => 'col-xs-3',
		'2/4' => 'col-xs-6',
		'3/4' => 'col-xs-9',
		'4/4' => 'col-xs-12',
		'1/5' => 'col-xs-5ths',
		'2/5' => 'col-xs-10ths',
		'3/5' => 'col-xs-15ths',
		'4/5' => 'col-xs-20ths',
		'5/5' => 'col-xs-12',
		'1/6' => 'col-xs-2',
		'2/6' => 'col-xs-4',
		'3/6' => 'col-xs-6',
		'4/6' => 'col-xs-8',
		'5/6' => 'col-xs-10',
		'6/6' => 'col-xs-12'
	);

	$barcelona_size_class = 'col-xs-6';
	if ( array_key_exists( $atts['size'], $barcelona_size_map ) ) {
		$barcelona_size_class = $barcelona_size_map[ $atts['size'] ];
	}

	return '<div class="barcelona-sc-col '. sanitize_html_class( $barcelona_size_class ) .'">'. do_shortcode( $content ) .'</div>';

}

function barcelona_clean_shortcodes( $content ) {

	$array = array (
		'<p>[' => '[',
		']</p>' => ']',
		']<br />' => ']'
	);

	$content = strtr( $content, $array );

	return $content;

}
add_filter( 'the_content', 'barcelona_clean_shortcodes' );