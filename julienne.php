<?php
/**
 * Plugin Name: Julienne
 * Description: Unload unused plugin CSS and JS unless the current request actually needs them.
 * Version: 0.1.1
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Plugin URI: https://breakfastco.xyz
 * Author: Breakfast
 * Author URI: https://breakfastco.xyz
 * License: GPL-2.0-or-later
 * GitHub Plugin URI: https://github.com/breakfastco/julienne
 * Primary Branch: main
 *
 * @package Breakfast_Julienne
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/includes/class-cut.php';
require_once __DIR__ . '/includes/class-engine.php';
require_once __DIR__ . '/includes/class-plugin.php';

if ( ! function_exists( 'breakfast_julienne' ) ) {
	/**
	 * Plugin instance.
	 *
	 * @return Breakfast_Julienne_Plugin
	 */
	function breakfast_julienne() {
		return Breakfast_Julienne_Plugin::instance();
	}
}

if ( ! class_exists( 'Breakfast_Julienne' ) ) {
	/**
	 * Public registration API.
	 *
	 *   Breakfast_Julienne::register( array(
	 *     'id'         => 'my-plugin',
	 *     'active'     => static function () { return defined( 'MY_PLUGIN_VERSION' ); },
	 *     'styles'     => array( 'my-plugin' ),
	 *     'scripts'    => array( 'my-plugin' ),
	 *     'shortcodes' => array( 'my-shortcode' ),
	 *     'blocks'     => array( 'my-plugin/widget' ),
	 *   ) );
	 */
	final class Breakfast_Julienne {

		/**
		 * Register a cut. Safe to call before or after Julienne boots.
		 *
		 * @param array $args Cut arguments. See Breakfast_Julienne_Cut::from_array().
		 * @return void
		 */
		public static function register( $args ) {
			breakfast_julienne()->register( $args );
		}
	}
}

add_action( 'plugins_loaded', array( breakfast_julienne(), 'boot' ), 20 );
