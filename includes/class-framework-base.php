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
		 * Sets Core Values like (plugin_slug,db_slug,hook_slug) and more
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
		 * @param array $options .
		 * @param array $defaults .
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
			$return = false;
			switch ( $type ) {
				case 'slug':
					$return = $this->slug;
					break;
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
	}
}
