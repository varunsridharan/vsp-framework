<?php
/**
 * VSP Plugin Settings Abstract Class
 * Used to auto hook with WPOnion Integration
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 27-02-2018
 * Time: 09:11 AM
 *
 * @link    http://github.com/varunsridharan/vsp-framework/
 * @version 1.0
 * @since   1.0
 *
 * @package   vsp-framework/core/abstract
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @copyright 2018 Varun Sridharan
 * @license   GPLV3 Or Greater
 */

namespace VSP\Core\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class VSP_Plugin_Settings
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
abstract class Plugin_Settings implements \VSP\Core\Interfaces\Plugin_Settings {
	/**
	 * VSP_Settings_Plugin constructor.
	 *
	 * @param string $hook_slug .
	 */
	public function __construct( $hook_slug = '' ) {
		add_filter( $hook_slug . 'settings_pages', array( &$this, 'add_pages' ) );
		add_filter( $hook_slug . 'settings_sections', array( &$this, 'add_sections' ) );
		add_filter( $hook_slug . 'settings_fields', array( &$this, 'add_fields' ) );
	}
}
