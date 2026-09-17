<?php
/**
 * Cut: SVG Support.
 *
 * Advanced mode loads DOMPurify + svgs-inline on every front-end page. Those
 * scripts only matter when an image uses the target class (`style-svg` by
 * default), or when "force inline" is on.
 *
 * @package Breakfast_Julienne
 */

defined( 'ABSPATH' ) || exit;

$target_class = static function () {
	$options = get_option( 'bodhi_svgs_settings', array() );
	$target  = ! empty( $options['css_target'] ) ? $options['css_target'] : 'style-svg';
	$target  = is_string( $target ) ? $target : 'style-svg';
	return ltrim( $target, '.' );
};

return array(
	'id'            => 'svg-support',
	'active'        => static function () {
		return function_exists( 'bodhi_svgs_advanced_mode' ) && bodhi_svgs_advanced_mode();
	},
	'styles'        => array( 'bodhi-svgs-attachment' ),
	'scripts'       => array( 'bodhi_svg_inline', 'bodhi-dompurify-library' ),
	'needles'       => static function () use ( $target_class ) {
		return array( $target_class() );
	},
	'image_classes' => static function () use ( $target_class ) {
		return array( $target_class() );
	},
	'needed'        => static function () {
		$options = get_option( 'bodhi_svgs_settings', array() );
		return ! empty( $options['force_inline_svg'] );
	},
);
