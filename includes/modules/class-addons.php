<?php

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
			'headers'                 => array(),
			'show_category_count'     => true,
			'hook_priority'           => 99,
		);

		/**
		 * VSP_Addons constructor.
		 *
		 * @param array $options .
		 */
		public function __construct( $options = array() ) {
			$this->set_args( $options );
			self::$default_addon_cats      = array(
				'all'      => __( 'All', 'vsp-framework' ),
				'active'   => __( 'Active', 'vsp-framework' ),
				'inactive' => __( 'Inactive', 'vsp-framework' ),
				'general'  => __( 'General', 'vsp-framework' ),
			);
			self::$required_plugins_status = array(
				'notexists' => __( 'Not Installed', 'vsp-framework' ),
				'exists'    => __( 'In Active', 'vsp-framework' ),
				'active'    => __( 'Active', 'vsp-framework' ),
			);
			$this->headers                 = $this->parse_args( $this->option( 'headers' ), $this->default_headers );
			$slug                          = $this->plugin()
				->slug( 'hook' );
			$hook                          = $this->option( 'hook_priority' );
			$this->active_addons();
			$this->load_active_addons();

			if ( vsp_is_admin() ) {
				add_action( $slug . '_settings_options', array( $this, 'link_with_wponion' ), $hook );
			}

			if ( vsp_is_ajax() ) {
				add_action( $slug . '_handle_addon_request', array( $this, 'handle_ajax_request' ) );
			}
		}

		/**
		 * @param \VSP\Ajax $ajax
		 */
		public function handle_ajax_request( $ajax ) {
			$addon  = $ajax->post( 'addon' );
			$action = $ajax->request( 'addon_action' );

			if ( ! empty( $addon ) ) {
				$ajax->error( __( 'Invalid Addon', 'vsp-framework' ) );
			}

			if ( ! in_array( $action, array( 'activate', 'deactivate' ), true ) ) {
				$ajax->error( __( 'Invalid Addon Action', 'vsp-framework' ) );
			}

			switch ( $action ) {
				case 'activate':
					if ( ! $this->is_active( $addon ) ) {
						$data = $this->search_addon( $addon );

						if ( ! is_array( $data ) ) {
							vsp_send_callback_error( swal2_error( __( 'Addon Not Found', 'vsp-framework' ), __( 'Selected Addon Not Found. Please Contact The Developer', 'vsp-framework' ) ) );
						}

						if ( isset( $data['required_plugins'] ) && is_array( $data['required_plugins'] ) && ! empty( $data['required_plugins'] ) && true !== $data['required_plugins_fulfilled'] ) {
							$msg = swal2_error( __( 'Activation Failed', 'vsp-framework' ), __( 'Addon\'s Requried Plugins Not Active / Installed', 'vsp-framework' ) );
							vsp_send_callback_error( $msg );
						}

						if ( $this->activate_addon( $addon ) ) {
							vsp_send_callback_success( swal2_success( __( 'Addon Activated', 'vsp-framework' ) ) );
						}
					}
					vsp_send_callback_error( swal2_warning( __( 'Addon Already Active', 'vsp-framework' ) ) );
					break;
				case 'deactivate':
					if ( $this->is_active( $addon ) && $this->deactivate_addon( $addon ) ) {
						vsp_send_callback_success( swal2_warning( __( 'Addon De-Activated', 'vsp-framework' ) ) );
					}
					vsp_send_callback_error( swal2_warning( __( 'Addon Is Not Active', 'vsp-framework' ) ) );
					break;
			}
		}

		/**
		 * Loads Active Addons.
		 */
		public function load_active_addons() {
			$active_addons       = $this->active_addons();
			$msg                 = __( 'Following addons are deactivated because some of its required plugins are deactivated / uninstalled', 'vsp-framework' );
			$deactivated_plugins = '';
			if ( ! empty( $active_addons ) ) {
				foreach ( $active_addons as $pathid ) {
					$is_active = $this->is_active( $pathid );
					if ( false !== $is_active ) {
						$addon_data = $this->search_addon( $pathid );
						if ( empty( $addon_data ) ) {
							$deactivated_plugins .= '<li>' . vsp_slashit( basename( $addon_data['addon_path'] ) ) . $addon_data['file'] . '</li>';
							$this->deactivate_addon( $pathid );
							continue;
						}
						if ( isset( $addon_data['required_plugins'] ) && is_array( $addon_data['required_plugins'] ) && ! empty( $addon_data['required_plugins'] ) && true !== $addon_data['required_plugins_fulfilled'] ) {
							$deactivated_plugins .= '<li>' . $addon_data['name'] . '</li>';
							$this->deactivate_addon( $pathid );
							continue;
						}
						$full_path = vsp_slashit( $addon_data['addon_path'] ) . $addon_data['file'];
						vsp_load_file( $full_path );
					}
				}
			}
			if ( ! empty( $deactivated_plugins ) ) {
				$title = '<strong>' . $this->plugin()
						->plugin_name() . '</strong>' . __( ' Has Deactivated Some of its addons', 'vsp-framework' );
				$msg   = $msg . '<ul>' . $deactivated_plugins . '</ul>';
				$msg   .= '<p><button class="button button-secondary wpo-stick-dismiss">' . __( 'I Understand. Will Fix It', 'vsp-framework' ) . '</button></p>';

				wponion_error_admin_notice( $msg, $title, array(
					'large' => true,
				) )->setSticky( true );
			}
		}

		/**
		 * Returns All Active Addons For current plugin
		 *
		 * @return array|false
		 */
		public function active_addons() {
			if ( false === $this->active_addons ) {
				$this->active_addons = get_option( $this->plugin()
						->slug( 'db' ) . '_active_addons', false );
				$this->active_addons = ( is_array( $this->active_addons ) && ! empty( $this->active_addons ) ) ? $this->active_addons : array();
			}
			return $this->active_addons;
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
			if ( isset( $_POST[ $request ] ) ) {
				return $_POST[ $request ];
			}
			wp_send_json_error( $msg );
			return false;
		}

		/**
		 * Updates Active Addons in db
		 *
		 * @param array $addons .
		 *
		 * @return array
		 */
		public function update_active_addons( $addons ) {
			update_option( $this->plugin()
					->slug( 'db' ) . '_active_addons', $addons );
			$this->active_addons = $addons;
			return $this->active_addons;
		}

		/**
		 * Activates Selected Addon.
		 *
		 * @param string $addon_uid Addons MD5 Unique ID
		 *
		 * @return bool
		 */
		public function activate_addon( $addon_uid = '' ) {
			$active_addons = $this->active_addons();
			if ( ! in_array( $addon_uid, $active_addons, true ) ) {
				$active_addons[] = $addon_uid;
				$this->update_active_addons( $active_addons );
				return true;
			}
			return false;
		}

		/**
		 * Deactivates Selected Addon
		 *
		 * @param string $addon_uid Addons MD5 Unique ID
		 *
		 * @return bool
		 */
		public function deactivate_addon( $addon_uid = '' ) {
			$active_addons = $this->active_addons();
			if ( in_array( $addon_uid, $active_addons, true ) ) {
				foreach ( $active_addons as $k => $id ) {
					if ( $id === $addon_uid ) {
						unset( $active_addons[ $k ] );
					}
				}
				$this->update_active_addons( $active_addons );
				return true;
			}
			return false;
		}

		/**
		 * Checks if given addon is active
		 *
		 * @param string $addon_uid Addons MD5 Unique ID
		 *
		 * @return bool|mixed
		 */
		public function is_active( $addon_uid = '' ) {
			$addons = $this->active_addons();
			return ( in_array( $addon_uid, $addons, true ) );
		}
	}
}
