<?php
/**
 * Cut: Elementor (+ theme companion stylesheets).
 *
 * Elementor already loads `elementor-frontend` only on documents it built.
 * Blocksy still prints `ct-elementor-styles` whenever Elementor is active;
 * Sydney dumps `sydney-elementor` the same way. Elementor Pro's theme builder
 * can inject layouts on any URL, so this cut stays out of the way when Pro
 * is present.
 *
 * @package Breakfast_Julienne
 */

defined( 'ABSPATH' ) || exit;

return array(
	'id'      => 'elementor',
	'active'  => static function () {
		return defined( 'ELEMENTOR_VERSION' );
	},
	'styles'  => array(
		'ct-elementor-styles',
		'sydney-elementor',
	),
	'needed'  => static function () {
		if ( ! class_exists( '\Elementor\Plugin' ) ) {
			return false;
		}

		if ( class_exists( '\ElementorPro\Plugin' ) ) {
			return true;
		}

		$elementor = \Elementor\Plugin::$instance;
		if ( $elementor->preview->is_preview_mode() ) {
			return true;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id ) {
			return false;
		}

		$document = $elementor->documents->get( $post_id );
		return $document && $document->is_built_with_elementor();
	},
);
