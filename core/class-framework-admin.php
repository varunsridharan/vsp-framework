<?php
/**
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VSP_Framework_Admin' ) ) {
	/**
	 * Class VSP_Framework_Admin
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class VSP_Framework_Admin extends VSP_Class_Handler {

		/**
		 * Row_actions
		 *
		 * @var array
		 */
		protected $row_actions = array();

		/**
		 * Action_links
		 *
		 * @var array
		 */
		protected $action_links = array();

		/**
		 * VSP_Framework_Admin constructor.
		 *
		 * @param array $options .
		 */
		public function __construct( $options = array() ) {
			parent::__construct( $options );
		}

		/**
		 * Registers Admin hook
		 *
		 * @see \VSP_Framework::__register_hooks
		 */
		public function __register_admin_hooks() {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ), 99 );
			add_action( 'admin_init', array( $this, 'wp_admin_init' ) );
			add_filter( 'plugin_row_meta', array( $this, 'row_links' ), 10, 2 );
			add_filter( 'plugin_action_links_' . $this->file(), array( $this, 'action_links' ), 10, 10 );
		}

		/**
		 * Adds Plugin Row Link
		 *
		 * @param array  $plugin_meta .
		 * @param string $plugin_file .
		 *
		 * @return mixed
		 */
		public function row_links( $plugin_meta, $plugin_file ) {
			if ( $this->file() === $plugin_file ) {
				if ( is_array( $this->row_actions ) && ! empty( $this->row_actions ) ) {
					$is_before = ( isset( $this->row_actions['before'] ) ) ? true : false;
					unset( $this->row_actions['before'] );
					if ( true === $is_before ) {
						$plugin_meta = array_merge( $this->row_actions, $plugin_meta );
					} else {
						$plugin_meta = array_merge( $plugin_meta, $this->row_actions );
					}
				}
			}

			return $plugin_meta;
		}

		/**
		 * Adds Action link
		 *
		 * @param array  $action .
		 * @param string $plugin_file .
		 * @param array  $plugin_meta .
		 * @param string $status .
		 *
		 * @return mixed
		 */
		public function action_links( $action, $plugin_file, $plugin_meta, $status ) {
			if ( $this->file() === $plugin_file ) {
				if ( is_array( $this->action_links ) && ! empty( $this->action_links ) ) {
					$is_before = ( isset( $this->action_links['before'] ) ) ? true : false;
					unset( $this->action_links['before'] );
					if ( true === $is_before ) {
						$action = array_merge( $this->action_links, $action );
					} else {
						$action = array_merge( $action, $this->action_links );
					}
				}
			}
			return $action;
		}
	}
}
