<?php

namespace VSP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( '\VSP\Framework_Base' ) ) {
	/**
	 * Class Framework_Base
	 *
	 * @package VSP
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	abstract class Framework_Base extends Base {
		/**
		 * Version
		 *
		 * @var null
		 */
		public $version = null;

		/**
		 * File
		 *
		 * @var null
		 */
		public $file = null;

		/**
		 * Plugin Slug
		 *
		 * @var null
		 */
		public $slug = null;

		/**
		 * DB_slug
		 *
		 * @var null
		 */
		public $db_slug = null;

		/**
		 * Name
		 *
		 * @var null
		 */
		public $name = null;

		/**
		 * Hook_slug
		 *
		 * @var null
		 */
		public $hook_slug = null;

		/**
		 * User_options
		 *
		 * @var array
		 */
		protected $user_options = array();

		/**
		 * Base_defaults
		 *
		 * @var array
		 */
		protected $base_defaults = array(
			'version'   => '',
			'file'      => '',
			'slug'      => '',
			'db_slug'   => '',
			'hook_slug' => '',
			'name'      => '',
		);

		/**
		 * Framework_Base constructor.
		 *
		 * @param array $options
		 */
		public function __construct( $options = array() ) {
			$this->set_args( $options );
		}

		/**
		 * Sets Core Values like
		 *
		 * @param string $key .
		 * @param string $default .
		 *
		 * @return $this
		 */
		protected function _set_core( $key = '', $default = '' ) {
			if ( empty( $this->{$key} ) || is_null( $this->{$key} ) ) {
				$this->{$key} = $default;
			}
			return $this;
		}

		/**
		 * Merges And sets the given args
		 *
		 * @param array $options
		 * @param array $defaults
		 */
		public function set_args( $options = array(), $defaults = array() ) {
			$defaults              = empty( $defaults ) ? $this->default_options : $defaults;
			$this->default_options = $this->parse_args( $defaults, $this->base_defaults );
			$this->options         = empty( $options ) ? $this->user_options : $options;
			$this->options         = $this->parse_args( $this->options, $this->default_options );
			$this->_set_core( 'version', $this->options['version'] )
				->_set_core( 'file', $this->options['file'] )
				->_set_core( 'slug', $this->options['slug'] )
				->_set_core( 'db_slug', $this->options['db_slug'] )
				->_set_core( 'hook_slug', $this->options['hook_slug'] )
				->_set_core( 'name', $this->options['name'] );
		}

		/**
		 * Returns $this->file
		 *
		 * @return string
		 */
		public function file() {
			return empty( $this->file ) ? __FILE__ : $this->file;
		}

		/**
		 * Returns $this->version
		 *
		 * @return bool|mixed
		 */
		public function version() {
			return $this->version;
		}

		/**
		 * Returns with slug value for the given type
		 * Types (slug,db,hook)
		 *
		 * @param string $type .
		 *
		 * @return string|bool
		 */
		public function slug( $type = 'slug' ) {
			$return = $this->slug;
			switch ( $type ) {
				case 'db':
					$return = $this->db_slug;
					break;
				case 'hook':
					$return = $this->hook_slug;
					break;
			}
			return $return;
		}

		/**
		 * Returns $this->name
		 *
		 * @return bool|mixed
		 */
		public function plugin_name() {
			return $this->name;
		}

		/**
		 * Triggers Given function
		 *
		 * @param string $type
		 * @param array  $args
		 *
		 * @return mixed
		 */
		private function action_filter( $type = '', $args = array() ) {
			$args[0] = $this->plugin()
					->slug( 'hook' ) . '_' . $args[0];
			return call_user_func_array( $type, $args );
		}

		/**
		 * Triggers apply_filters
		 *
		 * @return mixed
		 * @uses \apply_filters()
		 */
		public function filter() {
			return $this->action_filter( 'apply_filters', func_get_args() );
		}

		/**
		 * Triggers do_action
		 *
		 * @return mixed
		 * @uses \do_action()
		 *
		 */
		public function action() {
			return $this->action_filter( 'do_action', func_get_args() );
		}
	}
}
