<?php
/**
 * Name: WPSF
 * Version: 1.0
 *
 * @author     Varun Sridharan <varunsridharan23@gmail.com>
 * @since      1.0
 * @package    vsp-framework
 * @subpackage vsp-framework/integrations/
 * @copyright  GPL V3 Or greater
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VSP_WPSF_Integration
 */
class VSP_WPSF_Integration {
	/**
	 * _instance
	 *
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * tax_fields
	 *
	 * @var array
	 */
	public $tax_fields = array();

	/**
	 * metabox_fields
	 *
	 * @var array
	 */
	public $metabox_fields = array();

	/**
	 * shortcode_fields
	 *
	 * @var array
	 */
	public $shortcode_fields = array();

	/**
	 * wc_metabox_fields
	 *
	 * @var array
	 */
	public $wc_metabox_fields = array();

	/**
	 * tax_instance
	 *
	 * @var null
	 */
	public $tax_instance = null;

	/**
	 * metabox_instance
	 *
	 * @var null
	 */
	public $metabox_instance = null;

	/**
	 * wc_metabox_instance
	 *
	 * @var null
	 */
	public $wc_metabox_instance = null;

	/**
	 * shortcode_instance
	 *
	 * @var null
	 */
	public $shortcode_instance = null;

	/**
	 * VSP_WPSF_Integration constructor.
	 */
	public function __construct() {
		$this->tax_fields        = array();
		$this->metabox_fields    = array();
		$this->shortcode_fields  = array();
		$this->wc_metabox_fields = array();
		add_action( 'init', array( &$this, 'init_wpsf' ), 10 );
	}

	/**
	 * Creates Instance.
	 *
	 * @return VSP_WPSF_Integration
	 * @static
	 */
	public static function instance() {
		if ( null == self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Inits WPSF.
	 */
	public function init_wpsf() {
		$this->get_fields();

		if ( is_array( $this->tax_fields ) && ! empty( $this->tax_fields ) ) {
			$this->tax_instance = new WPSFramework_Taxonomy( $this->tax_fields );
		}

		if ( is_array( $this->metabox_fields ) && ! empty( $this->metabox_fields ) ) {
			$this->metabox_instance = new WPSFramework_Metabox( $this->metabox_fields );
		}

		if ( is_array( $this->wc_metabox_fields ) && ! empty( $this->wc_metabox_fields ) ) {
			$this->wc_metabox_instance = new WPSFramework_WC_Metabox( $this->wc_metabox_fields );
		}

		if ( is_array( $this->shortcode_fields ) && ! empty( $this->shortcode_fields ) ) {
			$this->shortcode_fields['settings'] = array(
				'button_title' => __( 'VSP Shortcodes', 'vsp-framework' ),
			);
			$this->shortcode_instance           = new WPSFramework_Shortcode_Manager( $this->shortcode_fields );
		}
	}

	/**
	 * Returns Fields.
	 */
	public function get_fields() {
		$this->tax_fields        = apply_filters( 'vsp_taxonomy_fields', $this->tax_fields );
		$this->metabox_fields    = apply_filters( 'vsp_metabox_fields', $this->metabox_fields );
		$this->shortcode_fields  = apply_filters( 'vsp_shortcode_fields', $this->shortcode_fields );
		$this->wc_metabox_fields = apply_filters( 'vsp_wc_metabox_fields', $this->wc_metabox_fields );
	}

}

return VSP_WPSF_Integration::instance();
