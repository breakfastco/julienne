<?php
/**
 * Bootstrap: load bundled cuts, accept registrations, start the engine.
 *
 * @package Breakfast_Julienne
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'Breakfast_Julienne_Plugin' ) ) {
	return;
}

/**
 * Plugin container.
 */
class Breakfast_Julienne_Plugin {

	/**
	 * @var self|null
	 */
	protected static $instance = null;

	/**
	 * @var Breakfast_Julienne_Cut[]
	 */
	protected $cuts = array();

	/**
	 * @var bool
	 */
	protected $booted = false;

	/**
	 * @return self
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Load bundled cuts and start the front-end engine.
	 *
	 * @return void
	 */
	public function boot() {
		if ( $this->booted ) {
			return;
		}
		$this->booted = true;

		$this->load_bundled_cuts();

		/**
		 * Register extra cuts.
		 *
		 * @param Breakfast_Julienne_Plugin $plugin Plugin instance.
		 */
		do_action( 'breakfast_julienne_register', $this );

		/**
		 * Filter the registered cuts, keyed by id.
		 *
		 * @param Breakfast_Julienne_Cut[] $cuts Cuts.
		 */
		$this->cuts = apply_filters( 'breakfast_julienne_cuts', $this->cuts );

		if ( is_admin() ) {
			return;
		}

		$engine = new Breakfast_Julienne_Engine( $this );
		$engine->add_hooks();
	}

	/**
	 * Register or replace a cut.
	 *
	 * @param array $args Cut arguments.
	 * @return void
	 */
	public function register( $args ) {
		$cut = Breakfast_Julienne_Cut::from_array( $args );
		if ( ! $cut ) {
			return;
		}
		$this->cuts[ $cut->id() ] = $cut;
	}

	/**
	 * @return Breakfast_Julienne_Cut[]
	 */
	public function cuts() {
		return $this->cuts;
	}

	/**
	 * Include every PHP file in cuts/.
	 *
	 * @return void
	 */
	protected function load_bundled_cuts() {
		$dir = dirname( __DIR__ ) . '/cuts';
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$files = glob( $dir . '/*.php' );
		if ( empty( $files ) ) {
			return;
		}

		sort( $files );

		foreach ( $files as $file ) {
			$config = include $file;
			if ( is_array( $config ) ) {
				$this->register( $config );
			}
		}
	}
}
