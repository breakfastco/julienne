<?php
/**
 * Cut: WooCommerce (+ theme companion stylesheets).
 *
 * WooCommerce prints layout/general CSS and the `woocommerce` script on every
 * front-end request. Blocksy, Kadence, OceanWP, Neve, Storefront, Hestia,
 * PopularFX, and Sydney dump companion CSS the same way whenever Woo is
 * active. Shop, product, cart, checkout, and account need those in the head.
 * Everywhere else, drop them unless a shortcode, block, or mini-cart actually
 * renders. Storefront always shows a header cart, so this cut stays out of
 * the way there.
 *
 * @package Breakfast_Julienne
 */

defined( 'ABSPATH' ) || exit;

return array(
	'id'         => 'woocommerce',
	'active'     => static function () {
		return class_exists( 'WooCommerce' );
	},
	'styles'     => array(
		'woocommerce*',
		'wc-blocks*',
		'wc-block-style',
		'photoswipe-default-skin',
		'ct-woocommerce-styles',
		'ct-woocommerce-cart-checkout-blocks',
		'ct-elementor-woocommerce-styles',
		'kadence-woocommerce',
		'kadence-account-woocommerce',
		'oceanwp-woocommerce*',
		'oceanwp-woo-*',
		'neve-woocommerce',
		'storefront-woocommerce*',
		'hestia_woocommerce_style',
		'popularfx-woocommerce',
		'sydney-wc-css',
	),
	'scripts'    => array(
		'woocommerce',
		'wc-*',
		'selectWoo',
		'storefront-header-cart',
		'oceanwp-woo-*',
	),
	'shortcodes' => array(
		'woocommerce_cart',
		'woocommerce_checkout',
		'woocommerce_my_account',
		'woocommerce_order_tracking',
		'product_page',
		'products',
		'product_category',
		'product_categories',
		'add_to_cart',
		'shop_messages',
		'related_products',
		'featured_products',
		'sale_products',
		'best_selling_products',
		'recent_products',
		'top_rated_products',
		'product_attribute',
	),
	'blocks'     => array( 'woocommerce/' ),
	'actions'    => array( 'woocommerce_before_mini_cart' ),
	'needed'     => static function () {
		if ( ! function_exists( 'is_woocommerce' ) ) {
			return false;
		}

		if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
			return true;
		}

		if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url() ) {
			return true;
		}

		if ( 'storefront' === get_template() ) {
			return true;
		}

		if ( class_exists( 'Blocksy_Header_Builder_Render' ) ) {
			$render = new Blocksy_Header_Builder_Render();
			if ( method_exists( $render, 'contains_item' ) && $render->contains_item( 'cart' ) ) {
				return true;
			}
		}

		return false;
	},
);
