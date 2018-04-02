<?php
/**
 * Project: vsp-framework
 * File: class-shortcode.php
 * Date: 27-03-2018
 * Time: 11:49 AM
 *
 * @link: http://github.com/varunsridharan/vsp-framework
 * @version: 1.0
 * @since: 1.0
 *
 * @package: vsp-framework
 * @subpackage: core
 * @author: Varun Sridharan <varunsridharan23@gmail.com>
 * @copyright: 2018 Varun Sridharan
 * @license: GPLV3 Or Greater
 */

abstract class VSP_Shortcode extends VSP_Class_Handler {
	/**
	 * Shortcode Name
	 *
	 * @var string
	 */
	protected $shortcode_name = '';

	/**
	 * Default Shortcode Options
	 *
	 * @var array
	 */
	protected $defaults = array();

	/**
	 * Actual Shortcode Options
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Shortcode Content
	 *
	 * @var null
	 */
	protected $content = null;

	/**
	 * VSP_Shortcode constructor.
	 *
	 * @param array $options
	 * @param array $defaults
	 */
	public function __construct( $options = array(), $defaults = array() ) {
		parent::__construct( $options, $defaults );
		add_shortcode( $this->shortcode_name, array( &$this, 'render_shortcode' ) );
		if ( empty( $this->defaults ) ) {
			$this->defaults = $this->defaults();
		}
	}

	/**
	 * Function To Return Defaults Array.
	 *
	 * @return array
	 */
	protected function defaults() {
		return array();
	}

	/**
	 * Renders Shortcode.
	 *
	 * @param mixed  $atts .
	 * @param string $content .
	 *
	 * @return mixed
	 */
	public function render_shortcode( $atts, $content = '' ) {
		$this->shortcode_args( $atts );
		$this->content = $content;
		$output        = $this->output();
		return $output;
	}

	/**
	 * Merges Default Shortcode Args With Given Shortcode
	 *
	 * @param $atts
	 */
	protected function shortcode_args( $atts ) {
		$this->options = shortcode_atts( $this->defaults, $atts, $this->shortcode_name );
		$this->after_merge();
	}

	/**
	 * Triggers Once $this->options isset.
	 */
	protected function after_merge() {

	}

	/**
	 * Hookable Function to render shortcode HTML.
	 *
	 * @return mixed
	 */
	abstract protected function output();
}
