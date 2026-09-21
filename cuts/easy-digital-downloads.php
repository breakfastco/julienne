<?php
/**
 * Cut: Easy Digital Downloads (+ theme companion stylesheets).
 *
 * EDD prints `edd-styles` (and `edd-ajax` when AJAX is on) on every front-end
 * request. Astra, OceanWP, and Neve also dump companion CSS whenever EDD is
 * active. Checkout, success, purchase history, and download archives need
 * those in the head. Everywhere else, drop them unless a shortcode or block
 * actually renders.
 *
 * @package Breakfast_Julienne
 */

defined( 'ABSPATH' ) || exit;

return array(
	'id'         => 'easy-digital-downloads',
	'active'     => static function () {
		return function_exists( 'EDD' );
	},
	'styles'     => array(
		'edd-styles',
		'astra-edd',
		'oceanwp-edd',
		'neve-easy-digital-downloads',
	),
	'scripts'    => array(
		'edd-ajax',
		'oceanwp-edd*',
	),
	'shortcodes' => array(
		'download_checkout',
		'download_cart',
		'download_history',
		'downloads',
		'edd_downloads',
		'edd_login',
		'edd_register',
		'edd_price',
		'edd_receipt',
		'edd_profile_editor',
		'edd_discounts',
		'download_discounts',
	),
	'blocks'     => array( 'edd/' ),
	'needed'     => static function () {
		if ( function_exists( 'edd_is_checkout' ) && edd_is_checkout() ) {
			return true;
		}
		if ( function_exists( 'edd_is_success_page' ) && edd_is_success_page() ) {
			return true;
		}
		if ( function_exists( 'edd_is_purchase_history_page' ) && edd_is_purchase_history_page() ) {
			return true;
		}
		if ( is_singular( 'download' ) || is_post_type_archive( 'download' ) ) {
			return true;
		}
		if ( is_tax( array( 'download_category', 'download_tag' ) ) ) {
			return true;
		}

		return false;
	},
);
