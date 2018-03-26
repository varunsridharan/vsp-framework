<?php
/**
 * VSP/WPSF Settings Metaboxes.
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 27-02-2018
 * Time: 09:11 AM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'VSP_Settings_Metabox' ) ) {
	/**
	 * Class VSP_Settings_Metabox
	 */
	class VSP_Settings_Metabox extends VSP_Class_Handler {

		/**
		 * Default_options
		 *
		 * @var array
		 */
		protected $default_options = array(
			'show_faqs' => true,
		);

		/**
		 * VSP_Settings_Metabox constructor.
		 *
		 * @param array $options .
		 */
		public function __construct( $options = array() ) {
			parent::__construct( $options );
		}

		/**
		 * Renders Metabox
		 */
		public function render_metaboxes() {
			echo '<div id="postbox-container-1" class="postbox-container"> <div class="meta-box-sortables">';
			$this->render_faqs();
			echo '</div></div>';
		}

		/**
		 * Handles FAQ And saves in DB
		 *
		 * @param array|boolean|bool $cache .
		 *
		 * @return array
		 */
		public function handle_faqs( $cache ) {
			$return = array();
			foreach ( $cache as $page => $sections ) {
				if ( ! isset( $return[ $page ] ) ) {
					$return[ $page ] = array();
				}

				foreach ( $sections as $sec_id => $section ) {
					if ( isset( $section['question'] ) ) {
						$return[ $page ][ vsp_fix_title( $section['question'] ) ] = $section;
					} else {
						foreach ( $section as $faq ) {
							$return[ $page ][ $sec_id ][ vsp_fix_title( $faq['question'] ) ] = $faq;
						}
					}
				}
			}

			return $return;
		}

		/**
		 * Gets FAQ Info from CDN
		 *
		 * @return array|bool|mixed|object|\WP_Error
		 */
		private function get_faq_datas() {
			$cache = vsp_get_cache( $this->slug() . '_faqs' );
			if ( false === $cache ) {
				$url   = $this->slug() . '/faq.json';
				$cache = vsp_get_cdn( $url, true );
				if ( empty( $cache ) ) {
					return false;
				}

				if ( is_wp_error( $cache ) ) {
					return false;
				}
				$cache = $this->handle_faqs( $cache );
				vsp_set_cache( $this->slug() . '_faqs', $cache, '10_days' );
			}
			return $cache;
		}

		/**
		 * Renders FAQ HTML
		 */
		public function render_faqs() {
			if ( $this->option( 'show_faqs' ) === false ) {
				return;
			}
			$faqs = $this->get_faq_datas();
			if ( empty( $faqs ) ) {
				return;
			}
			vsp_load_script( 'vsp-simscroll' );
			echo '<div class="postbox" id="vsp-settings-faq">';
			echo '<button type="button" class="handlediv" aria-expanded="true"><span class="toggle-indicator" aria-hidden="true"></span></button><h2 class="hndle"><span>' . __( 'F A Q\'s', 'vsp-framework' ) . '</span></h2>';
			$current_faqs = array(
				'prefix_sec_id' => $this->slug( 'db' ),
				'faqs'          => $faqs,
			);
			echo vsp_js_vars( 'vspFramework_Settings_Faqs', $current_faqs, true );
			echo '<div class="inside">';
			echo '</div>';
			echo '</div>';
		}
	}
}
