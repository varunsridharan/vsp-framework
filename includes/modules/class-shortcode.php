<?php

namespace VSP\Modules;

defined( 'ABSPATH' ) || exit;

use VSP\Base;

/**
 * Class VSP_Shortcode
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
abstract class Shortcode extends Base {
	/**
	 * Shortcode Name
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Shortcode Content
	 *
	 * @var null
	 */
	protected $content = null;

	/**
	 * Shortcode constructor.
	 */
	public function __construct() {
		add_shortcode( $this->name, array( &$this, 'render_shortcode' ) );
	}

	/**
	 * Function To Return Defaults Array.
	 *
	 * @return array
	 */
	abstract protected function defaults();

	/**
	 * Renders Shortcode.
	 *
	 * @param mixed  $atts .
	 * @param string $content .
	 *
	 * @return mixed
	 */
	public function render_shortcode( $atts, $content = '' ) {
		$this->settings = shortcode_atts( $this->defaults(), $atts, $this->name );
		$this->after_merge();
		$this->content = $content;
		return $this->output();
	}

	/**
	 * Triggers Once $this->options isset.
	 */
	protected function after_merge() {
	}

	/**
	 * Hookable Function to render shortcode HTML.
	 *
	 * @return string
	 */
	abstract protected function output();
}
