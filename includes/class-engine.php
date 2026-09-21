<?php
/**
 * Dequeue unused assets, watch render, restore in the footer if needed.
 *
 * @package Breakfast_Julienne
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'Breakfast_Julienne_Engine' ) ) {
	return;
}

/**
 * Front-end runner.
 */
class Breakfast_Julienne_Engine {

	/**
	 * @var Breakfast_Julienne_Plugin
	 */
	protected $plugin;

	/**
	 * @param Breakfast_Julienne_Plugin $plugin Plugin instance.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * @return void
	 */
	public function add_hooks() {
		add_action( 'wp', array( $this, 'prime' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_dequeue' ), 20 );
		add_action( 'wp_footer', array( $this, 'maybe_restore' ), 5 );

		add_filter( 'do_shortcode_tag', array( $this, 'on_shortcode' ), 10, 2 );
		add_filter( 'render_block', array( $this, 'on_block' ), 10, 2 );
		add_filter( 'the_content', array( $this, 'on_html' ), 5 );
		add_filter( 'widget_text', array( $this, 'on_html' ), 5 );
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'on_image_attrs' ), 10, 1 );

		foreach ( $this->plugin->cuts() as $cut ) {
			if ( ! $cut->is_active() ) {
				continue;
			}
			foreach ( $cut->filters() as $hook ) {
				add_filter( $hook, array( $cut, 'mark_used_filter' ), 10, 1 );
			}
			foreach ( $cut->actions() as $hook ) {
				add_action( $hook, array( $cut, 'mark_used' ) );
			}
		}
	}

	/**
	 * Query is ready: decide which cuts belong in the head.
	 *
	 * @return void
	 */
	public function prime() {
		foreach ( $this->active_cuts() as $cut ) {
			$cut->prime();
		}
	}

	/**
	 * Drop assets that are not already known to be needed.
	 *
	 * @return void
	 */
	public function maybe_dequeue() {
		foreach ( $this->active_cuts() as $cut ) {
			if ( $cut->is_early_needed() ) {
				continue;
			}
			$this->dequeue_handles( $cut->styles(), 'style' );
			$this->dequeue_handles( $cut->scripts(), 'script' );
		}
	}

	/**
	 * If a late render happened, put the assets back before footer print.
	 *
	 * @return void
	 */
	public function maybe_restore() {
		foreach ( $this->active_cuts() as $cut ) {
			if ( $cut->is_early_needed() || ! $cut->was_used() ) {
				continue;
			}
			if ( $cut->should_load_styles() ) {
				$this->enqueue_handles( $cut->styles(), 'style' );
			}
			$this->enqueue_handles( $cut->scripts(), 'script' );
		}
	}

	/**
	 * @param string $output Shortcode HTML.
	 * @param string $tag    Tag.
	 * @return string
	 */
	public function on_shortcode( $output, $tag ) {
		foreach ( $this->active_cuts() as $cut ) {
			if ( $cut->matches_shortcode( $tag ) ) {
				$cut->mark_used();
			}
		}
		return $output;
	}

	/**
	 * @param string $block_content Block HTML.
	 * @param array  $block         Parsed block.
	 * @return string
	 */
	public function on_block( $block_content, $block ) {
		$name = isset( $block['blockName'] ) ? $block['blockName'] : '';
		foreach ( $this->active_cuts() as $cut ) {
			if ( $cut->matches_block( $name ) ) {
				$cut->mark_used();
			}
		}
		return $block_content;
	}

	/**
	 * @param string $html Content or widget HTML.
	 * @return string
	 */
	public function on_html( $html ) {
		foreach ( $this->active_cuts() as $cut ) {
			if ( $cut->matches_html( $html ) ) {
				$cut->mark_used();
			}
		}
		return $html;
	}

	/**
	 * @param array $attrs Image attributes.
	 * @return array
	 */
	public function on_image_attrs( $attrs ) {
		$class = isset( $attrs['class'] ) ? $attrs['class'] : '';
		foreach ( $this->active_cuts() as $cut ) {
			if ( $cut->matches_image_class( $class ) ) {
				$cut->mark_used();
			}
		}
		return $attrs;
	}

	/**
	 * @return Breakfast_Julienne_Cut[]
	 */
	protected function active_cuts() {
		$cuts = array();
		foreach ( $this->plugin->cuts() as $cut ) {
			if ( $cut->is_active() ) {
				$cuts[] = $cut;
			}
		}
		return $cuts;
	}

	/**
	 * @param string[] $handles Style or script handles.
	 * @param string   $type    style|script.
	 * @return void
	 */
	protected function dequeue_handles( $handles, $type ) {
		foreach ( $handles as $handle ) {
			if ( 'style' === $type ) {
				wp_dequeue_style( $handle );
			} else {
				wp_dequeue_script( $handle );
			}
		}
	}

	/**
	 * @param string[] $handles Style or script handles.
	 * @param string   $type    style|script.
	 * @return void
	 */
	protected function enqueue_handles( $handles, $type ) {
		foreach ( $handles as $handle ) {
			if ( 'style' === $type ) {
				wp_enqueue_style( $handle );
			} else {
				wp_enqueue_script( $handle );
			}
		}
	}
}
