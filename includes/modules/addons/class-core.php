<?php

namespace VSP\Modules\Addons;

use VSP\Base;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'Core' ) ) {
	/**
	 * Class Core
	 *
	 * @package VSP\Modules\Addons
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	abstract class Core extends Base {
		/**
		 * Set To True If its in Display Mode.
		 *
		 * @var bool
		 * @access
		 */
		protected $in_display = false;

		/**
		 * Stores Default Categories.
		 *
		 * @var array
		 * @access
		 * @static
		 */
		protected static $default_addon_cats = array();

		/**
		 * Stores Required Plugins Status.
		 *
		 * @var array
		 * @access
		 * @static
		 */
		protected static $required_plugins_status = array();

		/**
		 * @var array
		 * @access
		 */
		protected $addon_cats = array();

		/**
		 * @var array
		 * @access
		 */
		protected $addon_counts = array();

		/**
		 * Stores All Addon Information
		 *
		 * @var array
		 * @access
		 */
		protected $addons = array();

		/**
		 * Stores Default Headers.
		 *
		 * @var array
		 * @access
		 */
		protected $default_headers = array(
			'file'             => false,
			'name'             => '',
			'url'              => '',
			'version'          => '',
			'description'      => '',
			'author'           => '',
			'author_url'       => '',
			'last_updated'     => '',
			'category'         => '',
			'required_plugins' => array(),
			'screenshots'      => array(),
		);

		/**
		 * Stores All Active Addons.
		 *
		 * @var array
		 * @access
		 */
		protected $active_addons = false;

		/**
		 * @var array
		 * @access
		 */
		protected $headers = array();

		/**
		 * Searchs For Addons.
		 *
		 * @return array
		 */
		protected function search_addons() {
			$slug = $this->plugin();
			$dirs = apply_filters( $slug->slug( 'hook' ) . '_addons_dirs', array( $this->option( 'base_path' ) ) );

			if ( ! empty( $dirs ) ) {
				foreach ( $dirs as $dir ) {
					$addons = $this->search_addon_folder( $dir );
					if ( ! empty( $addons ) ) {
						$this->get_addons_informations( $addons );
					}
				}
			}
			return $this->addons;
		}

		/**
		 * Returns Only Selected Addon.
		 *
		 * @param $addon
		 *
		 * @return bool|mixed
		 */
		protected function search_addon( $addon ) {
			$this->search_addons();
			return ( isset( $this->addons[ $addon ] ) ) ? $this->addons[ $addon ] : false;
		}

		/**
		 * Search For Addons in given folder.
		 *
		 * @param      $dir
		 *
		 * @return array
		 */
		protected function search_addon_folder( $dir ) {
			$is_internal = ( vsp_unslashit( $dir ) === vsp_unslashit( $this->option( 'base_path' ) ) );
			$files       = vsp_get_file_paths( vsp_slashit( $dir ) . '*/addon.json' );
			$return      = array();
			foreach ( $files as $file ) {
				$dir = plugin_dir_path( $file );
				$url = plugin_dir_url( $file );
				$uid = md5( $dir );

				$return[ $uid ] = array(
					'uid'         => $uid, // Plugin UID MD5 Path
					'addon_url'   => $url, // Addon URL
					'addon_path'  => $dir, // Addon Path
					'is_internal' => $is_internal, //if the addon is an internal addon found in the main plugin itself
				);
			}
			return $return;
		}

		/**
		 * Checks and returns addon information.
		 *
		 * @param $addons
		 */
		protected function get_addons_informations( $addons ) {
			if ( ! empty( $addons ) ) {
				foreach ( $addons as $addon ) {
					if ( isset( $addon['addon_path'] ) ) {
						$data = $this->get_addon_information( $addon );
						if ( ! empty( $data ) ) {
							if ( ! isset( $this->addons[ $data['uid'] ] ) ) {
								$this->addons[ $data['uid'] ] = $data;
							}
						}
					}
				}
			}
		}

		/**
		 * @param $addon
		 *
		 * @return array
		 */
		protected function get_addon_information( $addon ) {
			$data = $this->read_addon_json( $addon['addon_path'] );
			if ( ! empty( $data ) && ! empty( $data['file'] ) ) {
				$data                               = $this->parse_args( $data, $addon );
				$data['required_plugins']           = ( isset( $data['required_plugins'] ) ) ? $data['required_plugins'] : array();
				$data['required_plugins']           = $this->handle_required_plugins( $data['required_plugins'] );
				$data['required_plugins_fulfilled'] = 0;
				$total                              = count( $data['required_plugins'] );
				foreach ( $data['required_plugins'] as $plugins ) {
					if ( 'active' === $plugins['status'] ) {
						$data['required_plugins_fulfilled'] = $data['required_plugins_fulfilled'] + 1;
					}
				}
				$data['required_plugins_fulfilled'] = ( $data['required_plugins_fulfilled'] > 0 && $total === $data['required_plugins_fulfilled'] );

				if ( $this->in_display ) {
					$data['category']     = ( isset( $data['category'] ) ) ? $data['category'] : array();
					$data['category']     = $this->handle_category( $data['category'] );
					$data['screenshots']  = ( isset( $data['screenshots'] ) ) ? $data['screenshots'] : array();
					$data['screenshots']  = $this->handle_screenshots( $data['screenshots'], $data );
					$data['icon']         = ( isset( $data['icon'] ) ) ? $data['icon'] : false;
					$data['icon']         = $this->handle_icon( $data['icon'], $addon );
					$data['last_updated'] = $this->handle_last_updated( $data['last_updated'] );
				}
			} else {
				return array();
			}
			return $data;
		}

		/**
		 * Handles Date Format.
		 *
		 * @param $last_updated
		 *
		 * @return false|string
		 */
		protected function handle_last_updated( $last_updated ) {
			return ( ! empty( $last_updated ) ) ? date( vsp_date_format(), strtotime( $last_updated ) ) : $last_updated;
		}

		/**
		 * Handles Addon Icon.
		 *
		 * @param $icon
		 * @param $addon
		 *
		 * @return mixed|string
		 */
		protected function handle_icon( $icon, $addon ) {
			if ( $icon ) {
				if ( true !== wponion_is_url( $icon ) && file_exists( $addon['addon_path'] . $icon ) ) {
					return $addon['addon_path'] . $icon;
				} elseif ( true === wponion_is_url( $icon ) ) {
					return $icon;
				}
			}
			return vsp_placeholder_img();
		}

		/**
		 * Handles Each Addons Required Plugins.
		 *
		 * @param $plugins
		 *
		 * @return mixed
		 */
		protected function handle_required_plugins( $plugins ) {
			if ( ! empty( $plugins ) ) {
				foreach ( $plugins as $slug => $plugin ) {
					$plugins[ $slug ] = $this->parse_args( $plugin, array(
						'name'    => null,
						'author'  => null,
						'version' => null,
						'url'     => null,
					) );

					if ( false === wp_is_plugin_installed( $slug ) ) {
						$plugins[ $slug ]['status'] = 'notexists';
					} elseif ( wp_is_plugin_inactive( $slug ) ) {
						$plugins[ $slug ]['status'] = 'exists';
					} elseif ( wp_is_plugin_active( $slug ) ) {
						$plugins[ $slug ]['status'] = 'active';
					}
				}
			}
			return $plugins;
		}

		/**
		 * Handles Addon Category.
		 *
		 * @param $category
		 *
		 * @return array
		 */
		protected function handle_category( $category ) {
			if ( empty( $category ) ) {
				return array( 'general', 'all' );
			}
			$category = ( is_string( $category ) ) ? explode( ',', $category ) : $category;
			$new      = array( 'all' );

			foreach ( $category as $cat ) {
				$sub_cats = explode( ',', $cat );
				foreach ( $sub_cats as $_cat ) {
					$_cat = trim( $_cat );
					$slug = sanitize_title( $_cat );
					if ( ! isset( $this->addon_cats[ $slug ] ) ) {
						$this->addon_cats[ $slug ]   = $_cat;
						$this->addon_counts[ $slug ] = 0;
					}
					$this->addon_counts[ $slug ]++;
					$new[] = $slug;
				}
			}
			return array_unique( $new );
		}

		/**
		 * Handles Addon Screenshots.
		 *
		 * @param $screenshots
		 * @param $addon
		 *
		 * @return mixed
		 */
		protected function handle_screenshots( $screenshots, $addon ) {
			if ( ! empty( $screenshots ) && is_array( $screenshots ) ) {
				$return = array();
				foreach ( $screenshots as $key => $screenshot ) {
					$s       = explode( '|', $screenshot, 2 );
					$src     = false;
					$content = false;
					if ( isset( $s[0] ) && isset( $s[1] ) ) {
						$content = $s[1];
						if ( true !== wponion_is_url( $s[0] ) && file_exists( $addon['addon_path'] . $s[0] ) ) {
							$src = $addon['addon_url'] . $s[0];
						} elseif ( true === wponion_is_url( $s[0] ) ) {
							$src = $s[0];
						}
					} elseif ( isset( $s[0] ) && ! isset( $s[1] ) ) {
						if ( true !== wponion_is_url( $s[0] ) && file_exists( $addon['addon_path'] . $s[0] ) ) {
							$src = $addon['addon_url'] . $s[0];
						} elseif ( true === wponion_is_url( $s[0] ) ) {
							$src = $s[0];
						}
					}

					$return[] = array(
						'src'     => $src,
						'content' => $content,
					);
				}
				return $return;
			}
			return $screenshots;
		}

		/**
		 * @param      $path
		 * @param bool $raw If set to true then it returns raw information that is passed in addon.json
		 *
		 * @return array|mixed|object
		 */
		protected function read_addon_json( $path, $raw = false ) {
			$return = array();
			if ( file_exists( vsp_slashit( $path ) . 'addon.json' ) ) {
				$return = json_decode( @file_get_contents( vsp_slashit( $path ) . 'addon.json' ), true );
				if ( false === $raw && is_array( $return ) && ! empty( $return ) ) {
					$return = $this->parse_args( $return, $this->headers );
				}
			}
			return ( is_array( $return ) && ! empty( $return ) ) ? $return : array();
		}
	}
}
