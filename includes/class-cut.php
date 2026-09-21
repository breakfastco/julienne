<?php
/**
 * One asset-to-content rule ("cut") for a plugin.
 *
 * Conditions stay as named keys — shortcodes, blocks, late hooks, or a
 * `needed` callback — instead of a single packed `check` lambda.
 *
 * @package Breakfast_Julienne
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'Breakfast_Julienne_Cut' ) ) {
	return;
}

/**
 * Value object plus used/early flags for the current request.
 */
class Breakfast_Julienne_Cut {

	/**
	 * Unique slug, usually the plugin directory name.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Whether the target plugin is present. Bool or callable.
	 *
	 * @var bool|callable
	 */
	protected $active = true;

	/**
	 * Style handles to dequeue.
	 *
	 * @var array|callable
	 */
	protected $styles = array();

	/**
	 * Script handles to dequeue.
	 *
	 * @var array|callable
	 */
	protected $scripts = array();

	/**
	 * Shortcode tags that mean this cut is needed.
	 *
	 * @var array|callable
	 */
	protected $shortcodes = array();

	/**
	 * Block names that mean this cut is needed. A value ending in `/` is a namespace prefix.
	 *
	 * @var array|callable
	 */
	protected $blocks = array();

	/**
	 * Substrings in post_content (or later HTML) that mean this cut is needed.
	 *
	 * @var array|callable
	 */
	protected $needles = array();

	/**
	 * Image class names that mean this cut is needed.
	 *
	 * @var array|callable
	 */
	protected $image_classes = array();

	/**
	 * Filter names that fire when the plugin actually renders.
	 *
	 * @var array
	 */
	protected $filters = array();

	/**
	 * Action names that fire when the plugin actually renders.
	 *
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Extra front-end condition (checkout page, force-inline, etc.).
	 *
	 * @var callable|null
	 */
	protected $needed = null;

	/**
	 * Whether styles should be restored if the cut is used. Bool or callable.
	 *
	 * @var bool|callable
	 */
	protected $load_styles = true;

	/**
	 * True once a render hook, shortcode, or block has fired.
	 *
	 * @var bool
	 */
	protected $used = false;

	/**
	 * True when the queried document needs the assets in the head.
	 *
	 * @var bool
	 */
	protected $early = false;

	/**
	 * Build a cut from a cuts/*.php return array.
	 *
	 * @param array $args Cut arguments.
	 * @return self|null
	 */
	public static function from_array( $args ) {
		if ( empty( $args['id'] ) || ! is_string( $args['id'] ) ) {
			return null;
		}

		$cut                 = new self();
		$cut->id             = $args['id'];
		$cut->active         = array_key_exists( 'active', $args ) ? $args['active'] : true;
		$cut->styles         = isset( $args['styles'] ) ? $args['styles'] : array();
		$cut->scripts        = isset( $args['scripts'] ) ? $args['scripts'] : array();
		$cut->shortcodes     = isset( $args['shortcodes'] ) ? $args['shortcodes'] : array();
		$cut->blocks         = isset( $args['blocks'] ) ? $args['blocks'] : array();
		$cut->needles        = isset( $args['needles'] ) ? $args['needles'] : array();
		$cut->image_classes  = isset( $args['image_classes'] ) ? $args['image_classes'] : array();
		$cut->filters        = isset( $args['filters'] ) ? (array) $args['filters'] : array();
		$cut->actions        = isset( $args['actions'] ) ? (array) $args['actions'] : array();
		$cut->needed         = isset( $args['needed'] ) && is_callable( $args['needed'] ) ? $args['needed'] : null;
		$cut->load_styles    = array_key_exists( 'load_styles', $args ) ? $args['load_styles'] : true;

		return $cut;
	}

	/**
	 * Cut id.
	 *
	 * @return string
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Whether the target plugin is active for this request.
	 *
	 * @return bool
	 */
	public function is_active() {
		return (bool) $this->resolve_bool( $this->active, true );
	}

	/**
	 * Style handles.
	 *
	 * @return string[]
	 */
	public function styles() {
		return $this->resolve_list( $this->styles );
	}

	/**
	 * Script handles.
	 *
	 * @return string[]
	 */
	public function scripts() {
		return $this->resolve_list( $this->scripts );
	}

	/**
	 * Shortcode tags.
	 *
	 * @return string[]
	 */
	public function shortcodes() {
		return $this->resolve_list( $this->shortcodes );
	}

	/**
	 * Block names or namespace prefixes.
	 *
	 * @return string[]
	 */
	public function blocks() {
		return $this->resolve_list( $this->blocks );
	}

	/**
	 * Content needles.
	 *
	 * @return string[]
	 */
	public function needles() {
		return $this->resolve_list( $this->needles );
	}

	/**
	 * Image class names.
	 *
	 * @return string[]
	 */
	public function image_classes() {
		return $this->resolve_list( $this->image_classes );
	}

	/**
	 * Render filters.
	 *
	 * @return string[]
	 */
	public function filters() {
		return array_values( array_filter( $this->filters ) );
	}

	/**
	 * Render actions.
	 *
	 * @return string[]
	 */
	public function actions() {
		return array_values( array_filter( $this->actions ) );
	}

	/**
	 * Whether styles should be put back when the cut is used.
	 *
	 * @return bool
	 */
	public function should_load_styles() {
		return (bool) $this->resolve_bool( $this->load_styles, true );
	}

	/**
	 * Decide whether the queried document already needs these assets in the head.
	 *
	 * @return void
	 */
	public function prime() {
		if ( $this->compute_early() ) {
			$this->early = true;
			$this->used  = true;
		}
	}

	/**
	 * Assets should stay in the head.
	 *
	 * @return bool
	 */
	public function is_early_needed() {
		return $this->early;
	}

	/**
	 * Mark the cut used after a late render.
	 *
	 * @return void
	 */
	public function mark_used() {
		$this->used = true;
	}

	/**
	 * Filter callback that marks the cut used and passes the value through.
	 *
	 * @param mixed $value Filter value.
	 * @return mixed
	 */
	public function mark_used_filter( $value ) {
		$this->used = true;
		return $value;
	}

	/**
	 * Whether a form, block, shortcode, or needle has been seen.
	 *
	 * @return bool
	 */
	public function was_used() {
		return $this->used;
	}

	/**
	 * @param string $tag Shortcode tag.
	 * @return bool
	 */
	public function matches_shortcode( $tag ) {
		return in_array( $tag, $this->shortcodes(), true );
	}

	/**
	 * @param string|null $block_name Block name.
	 * @return bool
	 */
	public function matches_block( $block_name ) {
		if ( ! is_string( $block_name ) || '' === $block_name ) {
			return false;
		}

		foreach ( $this->blocks() as $block ) {
			if ( substr( $block, -1 ) === '/' ) {
				if ( strpos( $block_name, $block ) === 0 ) {
					return true;
				}
				continue;
			}
			if ( $block_name === $block ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $html HTML or post_content.
	 * @return bool
	 */
	public function matches_html( $html ) {
		if ( ! is_string( $html ) || '' === $html ) {
			return false;
		}

		foreach ( $this->needles() as $needle ) {
			if ( '' !== $needle && false !== strpos( $html, $needle ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $class_attr Space-separated class attribute.
	 * @return bool
	 */
	public function matches_image_class( $class_attr ) {
		if ( ! is_string( $class_attr ) || '' === $class_attr ) {
			return false;
		}

		$classes = preg_split( '/\s+/', $class_attr, -1, PREG_SPLIT_NO_EMPTY );
		if ( empty( $classes ) ) {
			return false;
		}

		return (bool) array_intersect( $this->image_classes(), $classes );
	}

	/**
	 * Early need: explicit callback, then the queried post.
	 *
	 * @return bool
	 */
	protected function compute_early() {
		if ( $this->needed && (bool) call_user_func( $this->needed ) ) {
			return true;
		}

		$post = $this->queried_post();
		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		foreach ( $this->shortcodes() as $tag ) {
			if ( has_shortcode( $post->post_content, $tag ) ) {
				return true;
			}
		}

		foreach ( $this->blocks() as $block ) {
			if ( $this->post_has_block( $post, $block ) ) {
				return true;
			}
		}

		if ( $this->matches_html( $post->post_content ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param WP_Post $post  Post.
	 * @param string  $block Block name or namespace prefix.
	 * @return bool
	 */
	protected function post_has_block( $post, $block ) {
		if ( substr( $block, -1 ) === '/' ) {
			return false !== strpos( $post->post_content, '<!-- wp:' . $block );
		}

		return has_block( $block, $post );
	}

	/**
	 * @return WP_Post|null
	 */
	protected function queried_post() {
		$post = get_queried_object();
		return $post instanceof WP_Post ? $post : null;
	}

	/**
	 * @param mixed $value   Array or callable.
	 * @return string[]
	 */
	protected function resolve_list( $value ) {
		if ( is_callable( $value ) ) {
			$value = call_user_func( $value );
		}

		if ( ! is_array( $value ) ) {
			return array();
		}

		return array_values( array_filter( array_map( 'strval', $value ) ) );
	}

	/**
	 * @param mixed $value    Bool or callable.
	 * @param bool  $default  Fallback.
	 * @return bool
	 */
	protected function resolve_bool( $value, $default ) {
		if ( is_callable( $value ) ) {
			return (bool) call_user_func( $value );
		}
		if ( is_bool( $value ) ) {
			return $value;
		}
		return $default;
	}
}
