<?php
/**
 * Cut: Contact Form 7 (+ theme companion stylesheets).
 *
 * CF7 enqueues CSS/JS globally. Blocksy dumps `ct-cf-7-styles` whenever the
 * plugin is active; Astra dumps `astra-contact-form-7` on the same hook CF7
 * uses to load its own CSS. Restore only after a form actually renders — or
 * keep them in the head when the queried post already contains the shortcode/block.
 *
 * @package Breakfast_Julienne
 */

defined( 'ABSPATH' ) || exit;

return array(
	'id'         => 'contact-form-7',
	'active'     => static function () {
		return defined( 'WPCF7_VERSION' );
	},
	'styles'     => array(
		'contact-form-7',
		'contact-form-7-rtl',
		'jquery-ui-smoothness',
		'ct-cf-7-styles',
		'astra-contact-form-7',
	),
	'scripts'    => array(
		'contact-form-7',
		'swv',
		'contact-form-7-html5-fallback',
	),
	'shortcodes' => array( 'contact-form-7' ),
	'blocks'     => array( 'contact-form-7/contact-form-selector' ),
	'filters'    => array( 'wpcf7_form_action_url' ),
	'load_styles' => static function () {
		return ! defined( 'WPCF7_LOAD_CSS' ) || WPCF7_LOAD_CSS;
	},
);
