<?php
/**
 * Cut: WPForms (+ Blocksy companion stylesheet).
 *
 * WPForms already gates its own assets when it can see a form in post content.
 * Blocksy still prints `ct-wpforms-styles` on every page because WPFORMS_VERSION
 * is defined. This cut also catches the WPForms "global assets" setting.
 *
 * @package Breakfast_Julienne
 */

defined( 'ABSPATH' ) || exit;

return array(
	'id'         => 'wpforms',
	'active'     => static function () {
		return defined( 'WPFORMS_VERSION' );
	},
	'styles'     => array(
		'ct-wpforms-styles',
		'wpforms-modern-full',
		'wpforms-modern-base',
		'wpforms-classic-full',
		'wpforms-classic-base',
		'wpforms-no-styles',
	),
	'scripts'    => array(
		'wpforms',
		'wpforms-modern',
		'wpforms-validation',
		'wpforms-generic-utils',
		'wpforms-maskedinput',
		'wpforms-mailcheck',
		'wpforms-punycode',
	),
	'shortcodes' => array( 'wpforms' ),
	'blocks'     => array( 'wpforms/form-selector' ),
	'actions'    => array( 'wpforms_frontend_output' ),
);
