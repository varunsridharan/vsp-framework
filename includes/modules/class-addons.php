<?php
/**
 * VSP Plugin Addons Class.
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

namespace VSP\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'Addons' ) ) {
	/**
	 * Class VSP_Addons
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class Addons extends Addons\Admin {

		/**
		 * User_options
		 *
		 * @var array
		 */
		protected $user_options = array();

		/**
		 * Default_options
		 *
		 * @var array
		 */
		protected $default_options = array(
			'base_path'               => '',
			'base_url'                => '',
			'addon_listing_tab_name'  => 'addons',
			'addon_listing_tab_title' => 'Addons',
			'addon_listing_tab_icon'  => 'fa fa-plus',
			'file_headers'            => array(),
			'show_category_count'     => true,
		);

		/**
		 * Active_addons
		 *
		 * @var array
		 */
		public $active_addons = array();

		/**
		 * VSP_Addons constructor.
		 *
		 * @param array $options .
		 */
		public function __construct( $options = array() ) {
			$this->active_addons = array();
			$this->user_options  = $options;
			parent::__construct();

			if ( vsp_is_admin() ) {
				add_action( $this->slug( 'hook' ) . '_settings_options', array( $this, 'set_settings_page' ), 99, 100 );
			}

			if ( vsp_is_ajax() ) {
				add_action( $this->slug( 'hook' ) . '_handle_addon_request', array( $this, 'handle_ajax_request' ) );
			}

			$this->load_active_addons();
		}

		/**
		 * Loads Active Addons
		 */
		public function load_active_addons() {
			$active_addons = $this->get_active_addons();

			$msg = sprintf( __( '%s Has deactivated the following addons because its required plugins are deactivated', 'vsp-framework' ), '<strong>' . $this->option( 'plugin_name' ) . '</strong>' );

			$deactivated_plugins = '';

			if ( ! empty( $active_addons ) ) {
				foreach ( $active_addons as $pathid => $addon_slug ) {
					$is_active = $this->is_active( $pathid, true );
					if ( false !== $is_active ) {
						$addon_data = $this->search_get_addon( $addon_slug, $pathid );
						if ( empty( $addon_data ) ) {
							$deactivated_plugins .= '<li>' . $addon_slug . '</li>';
							$this->deactivate_addon( $addon_slug, $pathid );
							continue;
						}

						if ( isset( $addon_data['required_plugins'] ) && is_array( $addon_data['required_plugins'] ) ) {
							if ( true !== $addon_data['required_plugins']['fulfilled'] ) {
								$deactivated_plugins .= '<li>' . $addon_data['Name'] . '</li>';
								$this->deactivate_addon( $addon_slug, $pathid );
								continue;
							}
						}

						$full_path = $addon_data['addon_path'] . $addon_data['addon_file'];
						vsp_load_file( $full_path );
					}
				}
			}

			if ( ! empty( $deactivated_plugins ) ) {
				$msg = $msg . '<ul>' . $deactivated_plugins . '</ul>';
				wponion_error_admin_notice( false, $msg );
			}
		}

		/**
		 * Handles Ajax Request Params For Addons
		 *
		 * @param string $request .
		 * @param string $msg .
		 *
		 * @return bool
		 */
		public function handle_ajax_params( $request, $msg ) {
			if ( isset( $_REQUEST[ $request ] ) ) {
				return $_REQUEST[ $request ];
			}

			wp_send_json_error( array( 'msg' => $msg ) );
			return false;
		}

		/**
		 * Handles Ajax Requests For Addons
		 */
		public function handle_ajax_request() {
			if ( isset( $_REQUEST['addon_action'] ) ) {
				$action = $this->handle_ajax_params( 'addon_action', __( 'Addon Action Not Provided', 'vsp-framework' ) );
				$addon  = urldecode( $this->handle_ajax_params( 'addon_slug', __( 'No Addon Selected', 'vsp-framework' ) ) );
				$pathid = $this->handle_ajax_params( 'addon_pathid', __( 'Unable To Process Your Request', 'vsp-framework' ) );
				if ( empty( $addon ) ) {
					wp_send_json_error( array( 'msg' => __( 'Invalid Addon', 'vsp-framework' ) ) );
				}

				if ( 'activate' === $action ) {
					if ( ! $this->is_active( $addon ) ) {
						$addon_data = $this->search_get_addon( $addon, $pathid );

						if ( isset( $addon_data['required_plugins'] ) && is_array( $addon_data['required_plugins'] ) ) {
							if ( true !== $addon_data['required_plugins']['fulfilled'] ) {
								vsp_send_callback_error( array(
									'process_failed' => vsp_js_alert_error( __( 'Activation Failed', 'vsp-framework' ), __( 'Addon\'s Requried Plugins Not Active / Installed', 'vsp-framework' ) ),
								) );
							}
						}

						$slug = $this->activate_addon( $addon, $pathid );

						if ( $slug ) {
							vsp_send_callback_success( array(
								'process_success' => vsp_js_alert_success( __( 'Addon Activated', 'vsp-framework' ) ),
							) );
						}
					} else {
						vsp_send_callback_error( array(
							'process_warning' => vsp_js_alert_warning( __( 'Addon Already Active', 'vsp-framework' ) ),
						) );
					}
				}

				if ( 'deactivate' === $action ) {
					if ( $this->is_active( $addon ) ) {
						$slug = $this->deactivate_addon( $addon, $pathid );
						if ( $slug ) {
							vsp_send_callback_success( array(
								'process_success' => vsp_js_alert_warning( __( 'Addon De-Activated', 'vsp-framework' ) ),
							) );
						}
					} else {
						vsp_send_callback_error( array(
							'process_error' => vsp_js_alert_warning( __( 'Addon Is Not Active', 'vsp-framework' ) ),
						) );
					}
				}
			}
			wp_die();
		}


		/**
		 * Returns All Active Addons For current plugin
		 *
		 * @return array|false
		 */
		public function get_active_addons() {
			if ( empty( $this->active_addons ) ) {
				$this->active_addons = get_option( $this->slug( 'db' ) . '_active_addons', array() );
			}

			$this->active_addons = is_array( $this->active_addons ) ? $this->active_addons : array();
			return $this->active_addons;
		}

		/**
		 * Updates Active Addons in db
		 *
		 * @param array $addons .
		 *
		 * @return array
		 */
		public function update_active_addons( $addons ) {
			update_option( $this->slug( 'db' ) . '_active_addons', $addons );
			$this->active_addons = $addons;
			return $this->active_addons;
		}

		/**
		 * Activates Selected Addon.
		 *
		 * @param string $addons_slug .
		 * @param string $pathid .
		 *
		 * @return bool
		 */
		public function activate_addon( $addons_slug = '', $pathid = '' ) {
			$active_addons = $this->get_active_addons();
			if ( ! isset( $active_addons[ $pathid ] ) ) {
				$active_addons[ $pathid ] = $addons_slug;
				$this->update_active_addons( $active_addons );
				return true;
			}

			return false;
		}

		/**
		 * Deactivates Selected Addon
		 *
		 * @param string $addons_slug .
		 * @param string $pathid .
		 *
		 * @return bool
		 */
		public function deactivate_addon( $addons_slug = '', $pathid = '' ) {
			$active_addons = $this->get_active_addons();
			if ( isset( $active_addons[ $pathid ] ) ) {
				if ( $active_addons[ $pathid ] === $addons_slug ) {
					unset( $active_addons[ $pathid ] );
					$this->update_active_addons( $active_addons );
					return true;
				}
			}

			return false;
		}

		/**
		 * Checks if given addon is active
		 *
		 * @param string $slug .
		 * @param bool   $is_pathid .
		 *
		 * @return bool|mixed
		 */
		public function is_active( $slug, $is_pathid = false ) {
			$addons = $this->get_active_addons();

			if ( true === $is_pathid ) {
				if ( isset( $addons[ $slug ] ) ) {
					return $addons[ $slug ];
				}
			} else {
				if ( in_array( $slug, $addons ) ) {
					return $slug;
				}
			}

			return false;
		}
	}
}
