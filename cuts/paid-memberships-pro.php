<?php
/**
 * Cut: Paid Memberships Pro.
 *
 * PMPro prints frontend CSS on every request. Checkout and the assigned
 * membership pages need it in the head (FOUC on those screens is visible).
 * Everywhere else, drop `pmpro_frontend*` unless a shortcode or block renders.
 *
 * @package Breakfast_Julienne
 */

defined( 'ABSPATH' ) || exit;

return array(
	'id'         => 'paid-memberships-pro',
	'active'     => static function () {
		return defined( 'PMPRO_VERSION' );
	},
	'styles'     => array( 'pmpro_frontend*' ),
	'shortcodes' => array(
		'pmpro_account',
		'pmpro_login',
		'pmpro_member_profile_edit',
		'pmpro_membership_level',
		'pmpro_signup',
	),
	'blocks'     => array( 'pmpro/' ),
	'needed'     => static function () {
		if ( function_exists( 'pmpro_is_checkout' ) && pmpro_is_checkout() ) {
			return true;
		}

		global $pmpro_pages;
		if ( empty( $pmpro_pages ) || ! is_array( $pmpro_pages ) ) {
			return false;
		}

		foreach ( $pmpro_pages as $page_id ) {
			if ( $page_id && is_page( (int) $page_id ) ) {
				return true;
			}
		}

		return false;
	},
);
