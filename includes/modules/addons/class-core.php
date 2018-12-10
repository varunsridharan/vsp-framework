<?php
/**
 * VSP Plugin Addon Core Class.
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 27-02-2018
 * Time: 09:11 AM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework/core/modules/addons
 * @copyright GPL V3 Or greater
 */

namespace VSP\Modules\Addons;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}


if ( ! class_exists( 'Core' ) ) {
	/**
	 * Class VSP_Addons_Core
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	abstract class Core extends Detailed_View {

		/**
		 * VSP_Addons_Core constructor.
		 */
		public function __construct() {
			$this->default_cats      = array(
				'all'      => __( 'All', 'vsp-framework' ),
				'active'   => __( 'Active', 'vsp-framework' ),
				'inactive' => __( 'In Active', 'vsp-framework' ),
			);
			$this->addon_cats        = $this->default_cats;
			$this->addons_cats_count = array(
				'all'      => 0,
				'active'   => 0,
				'inactive' => 0,
			);
			parent::__construct();
		}

		/**
		 * Search And returns addons
		 *
		 * @param bool $single_addon .
		 *
		 * @return array
		 */
		public function search_get_addons( $single_addon = false ) {
			$this->addon_metadatas = array();
			$dirs                  = apply_filters( $this->slug( 'hook' ) . 'addons_dirs', array() );
			$internal_addons       = $this->search_plugins( $this->option( 'base_path' ), $single_addon );
			$this->get_metadata( $internal_addons );

			if ( ! empty( $dirs ) ) {
				foreach ( $dirs as $dir ) {
					$addons = $this->search_plugins( $dir, $single_addon );
					$this->get_metadata( $addons );
				}
			}

			return $this->addon_metadatas;
		}

		/**
		 * Search And Gets A Single Addon
		 *
		 * @param string|boolean|bool $addon_slug .
		 * @param string              $path_id .
		 *
		 * @return array
		 */
		public function search_get_addon( $addon_slug = false, $path_id = '' ) {
			$addons      = $this->search_get_addons( $addon_slug );
			$return_data = array();
			if ( ! empty( $addons ) ) {
				foreach ( $addons as $slug => $data ) {
					if ( $slug === $addon_slug ) {
						if ( md5( $data['addon_path'] ) === $path_id ) {
							$return_data = $data;
							break;
						}
					}
				}
			}

			return $return_data;
		}

		/**
		 * Extract Category From Addons Data
		 *
		 * @param string $category .
		 *
		 * @return array
		 */
		protected function handle_addon_category( $category ) {
			$category = explode( ',', $category );
			$return   = array();
			foreach ( $category as $cat ) {
				$cat             = $this->strip_space( $cat, ' ' );
				$slug            = sanitize_title( $cat );
				$return[ $slug ] = $cat;
			}
			return $return;
		}

		/**
		 * Search For Addons
		 *
		 * @param mixed  $search_path .
		 * @param bool   $single_addon .
		 * @param string $subpath .
		 *
		 * @return array
		 */
		public function search_plugins( $search_path, $single_addon = false, $subpath = '' ) {
			$search_path = rtrim( $search_path, '/' );
			$subpath     = rtrim( $subpath, '/' );
			$r           = array();

			if ( ! empty( $search_path ) ) {
				$_dir = @ opendir( $search_path . $subpath );

				if ( $_dir ) {
					while ( false !== ( $file = readdir( $_dir ) ) ) {
						if ( substr( $file, 0, 1 ) === '.' ) {
							continue;
						}

						$_ipath = $search_path . '/' . $file;
						if ( is_dir( $_ipath ) ) {
							$r = array_merge( $r, $this->search_plugins( $search_path, $single_addon, '/' . $file ) );
						} else {
							if ( false !== $single_addon ) {
								$single_addon = ltrim( $single_addon, '/' );
								if ( $search_path . $subpath . '/' . $file !== $search_path . '/' . $single_addon ) {
									continue;
								}
							}

							if ( substr( $file, -4 ) === '.php' ) {
								$r[] = array(
									'full_path'  => $search_path . $subpath . '/' . $file,
									'sub_folder' => $subpath . '/',
									'file_name'  => $file,
								);
							}
						}
					}
					closedir( $_dir );
				}
			}

			return $r;
		}

		/**
		 * Returns Default Headers for addons.
		 *
		 * @return array
		 */
		protected function get_default_headers() {
			return array(
				'Name'         => 'Addon Name',
				'addon_url'    => 'Addon URI',
				'icon'         => 'Addon icon',
				'Version'      => 'Version',
				'Description'  => 'Description',
				'Author'       => 'Author',
				'AuthorURI'    => 'Author URI',
				'last_updated' => 'Last updated',
				'created_on'   => 'Created On',
				'category'     => 'Category',
			);
		}

		/**
		 * Extract Addons Required Plugins
		 *
		 * @param array $meta .
		 *
		 * @return mixed
		 */
		protected function fix_addon_metadata( $meta ) {
			$meta = $this->__extract_required_plugins( $meta );
			return $meta;
		}

		/**
		 * Strips spaces from and end
		 *
		 * @param string $string .
		 * @param string $char .
		 *
		 * @return string
		 */
		protected function strip_space( $string, $char ) {
			$string = ltrim( $string, $char );
			$string = rtrim( $string, $char );
			return $string;
		}

		/**
		 * Checks Required Plugin Status
		 *
		 * @param string $slug .
		 *
		 * @return bool|string
		 */
		protected function check_plugin_status( $slug ) {
			if ( ! function_exists( 'validate_plugin' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$val_plugin = validate_plugin( $slug );
			if ( is_wp_error( $val_plugin ) ) {
				return 'notexist';
			} elseif ( is_plugin_active( $slug ) ) {
				return 'activated';
			} elseif ( is_plugin_inactive( $slug ) ) {
				return 'exists';
			}
			return false;
		}

		/**
		 * Extracts Required Plugin Information
		 *
		 * @param array $meta .
		 *
		 * @return mixed
		 */
		protected function __extract_required_plugins( $meta ) {
			if ( empty( $meta['rplugins'] ) ) {
				$_rplugins = array();
				$_rpc      = 1;
				$_apc      = 1;
			} else {
				$rplugins    = $meta['rplugins'];
				$_rpc        = count( $rplugins );
				$_apc        = 1;
				$r_plugins_a = explode( ',', $rplugins );
				$_rplugins   = array();
				foreach ( $r_plugins_a as $r_plugin ) {
					$r_plugin = $this->strip_space( $r_plugin, ' ' );
					$r_plugin = $this->strip_space( $r_plugin, ']' );
					$r_plugin = $this->strip_space( $r_plugin, '[' );
					$r_plugin = $this->strip_space( $r_plugin, ' ' );

					$r_plugin = explode( '|', $r_plugin );
					if ( is_array( $r_plugin ) ) {
						$pd = array();
						foreach ( $r_plugin as $data ) {
							$data = $this->strip_space( $data, ' ' );
							$data = explode( ':', $data, 2 );

							if ( count( $data ) > 1 ) {
								if ( isset( $data[0] ) ) {
									$key        = strtolower( $this->strip_space( $data[0], ' ' ) );
									$value      = $this->strip_space( $data[1], ' ' );
									$pd[ $key ] = $value;
								}
							}
						}

						if ( ! empty( $pd ) ) {
							$pd['status'] = $this->check_plugin_status( $pd['slug'] );
							if ( 'activated' === $pd['status'] ) {
								$_apc++;
							}
							$_rplugins[ $pd['slug'] ] = $pd;
						}
					}
				}
			}
			$meta['rplugins']              = $_rplugins;
			$meta['requirement_fullfiled'] = ( $_rpc === $_apc );
			return $meta;
		}

		/**
		 * Returns Status Labels
		 *
		 * @param string|bool $status .
		 *
		 * @return bool|string
		 */
		protected function get_plugin_status_label( $status = false ) {
			if ( 'exists' === $status ) {
				return __( 'In Active', 'vsp-framework' );
			}
			if ( 'notexist' === $status ) {
				return __( 'Not Exist', 'vsp-framework' );
			}
			if ( 'activated' === $status ) {
				return __( 'Active', 'vsp-framework' );
			}
			return false;
		}
	}
}
