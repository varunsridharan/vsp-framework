<?php

namespace VSP\Modules;

use VSP\Base;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class VSP_Shortcode
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
abstract class Shortcode extends Base {
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
	 * Shortcode constructor.
	 *
	 * @param array $options
	 */
	public function __construct( $options = array() ) {
		$this->set_args( $options );
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
		return $this->output();
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
