# Julienne

WordPress plugins often enqueue CSS and JS on every front-end request. Julienne unloads those assets unless the current page actually uses them. No settings. Activate it and it works.

## Bundled cuts

Each file in `cuts/` is one plugin. The file returns an array so shortcodes, blocks, late render hooks, and extra conditions stay readable.

| Cut | What it unloads unless needed |
| --- | --- |
| `contact-form-7.php` | CF7 CSS/JS, plus Blocksy `ct-cf-7-styles` |
| `wpforms.php` | WPForms CSS/JS, plus Blocksy `ct-wpforms-styles` |
| `paid-memberships-pro.php` | `pmpro_frontend*` CSS (kept in the head on checkout and PMPro pages) |
| `woocommerce.php` | WooCommerce CSS/JS, plus common theme companion sheets |
| `easy-digital-downloads.php` | `edd-styles` / `edd-ajax`, plus Astra/OceanWP/Neve companions |
| `elementor.php` | Blocksy/Sydney Elementor companion CSS (left alone when Elementor Pro is active) |
| `svg-support.php` | DOMPurify / svgs-inline unless `style-svg` (or force-inline) is in play |

We take requests. If you want a cut for another plugin, [open an issue or a pull request](https://github.com/breakfastco/julienne) or send a note at [https://breakfastco.xyz/contact](https://breakfastco.xyz/contact).

## Register another cut

```php
add_action( 'breakfast_julienne_register', function () {
	Breakfast_Julienne::register( array(
		'id'         => 'some-slider-plugin',
		'active'     => static function () {
			return defined( 'SOME_SLIDER_VERSION' );
		},
		'styles'     => array( 'slick-theme', 'slick-main' ),
		'scripts'    => array( 'slick-main' ),
		'blocks'     => array( 'vendor/slider-block' ),
		'shortcodes' => array( 'slider' ),
	) );
} );
```

Useful keys:

- `active` — bool or callback. Skip the cut when the plugin is absent.
- `styles` / `scripts` — handles. A trailing `*` is a prefix match (`pmpro_frontend*`).
- `shortcodes` / `blocks` — early (queried post) and late (render) detection. A block value ending in `/` matches a namespace (`pmpro/`).
- `filters` / `actions` — extra render hooks that mark the cut used.
- `needles` / `image_classes` — content substrings and `<img>` classes.
- `needed` — extra early condition (`pmpro_is_checkout()`, force-inline SVG, …).
- `load_styles` — bool or callback. CF7 uses this to honor `WPCF7_LOAD_CSS`.

Drop a matching PHP file in `cuts/` instead of calling `Breakfast_Julienne::register()` if you are shipping the rule with this plugin.

Remove a bundled cut with:

```php
add_filter( 'breakfast_julienne_cuts', function ( $cuts ) {
	unset( $cuts['contact-form-7'] );
	return $cuts;
} );
```

There is no admin UI in v0.1.0. A screen that toggles cuts or maps handles would come later; the registration API is the product for now.
