<?php
/**
 *
 * Initial version created 12-07-2018 / 03:53 PM
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.0
 * @since 1.0
 * @package
 * @link
 * @copyright 2018 Varun Sridharan
 * @license GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'VSP_WP_Page' ) ) {
	/**
	 * Class VSP_WP_Page
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	abstract class VSP_WP_Page extends VSP_Class_Handler {
		/**
		 * VSP_WP_Page constructor.
		 *
		 * @param array $options
		 */
		public function __construct( array $options = array() ) {
			parent::__construct( $options, array(
				'submenu'       => false,
				'menu_title'    => false,
				'page_title'    => false,
				'capability'    => false,
				'menu_slug'     => false,
				'icon'          => false,
				'position'      => false,
				'hook_priority' => 10,
			) );
			add_action( 'admin_menu', array( &$this, 'register_menu' ), $this->option( 'hook_priority' ) );
		}

		/**
		 * Registers WP Menu.
		 */
		public function register_menu() {
			$menu_slug = ( empty( $this->option( 'menu_slug' ) ) ) ? sanitize_title( $this->option( 'menu_title' ) ) : $this->option( 'menu_slug' );
			if ( false === $this->option( 'submenu' ) ) {
				$page_slug = add_menu_page( $this->option( 'page_title' ), $this->option( 'menu_title' ), $this->option( 'capability' ), $menu_slug, array(
					&$this,
					'render',
				), $this->option( 'icon' ), $this->option( 'position' ) );
			} else {
				$page_slug = add_submenu_page( $this->option( 'submenu' ), $this->option( 'page_title' ), $this->option( 'menu_title' ), $this->option( 'capability' ), $menu_slug, array(
					&$this,
					'render',
				) );
			}
			add_action( 'load-' . $page_slug, array( &$this, 'on_page_load' ) );
		}

		public function on_page_load() {
			add_action( 'admin_enqueue_scripts', array( &$this, 'handle_assets' ) );
			$this->page_load();
		}

		/**
		 * Runs On Page Load.
		 */
		abstract public function page_load();

		/**
		 * Handles Page Assets.
		 *
		 * @return mixed
		 */
		abstract public function handle_assets();

		/**
		 * Renders Page HTML.
		 */
		abstract public function render();
	}
}
