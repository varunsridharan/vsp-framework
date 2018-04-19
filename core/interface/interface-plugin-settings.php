<?php
/**
 * Interface class for plugins that uses VSP-Framework/wpsf
 *
 * Created by PhpStorm.
 * User: varun
 * Date: 27-02-2018
 * Time: 09:09 AM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */

/**
 * Interface VSP_Plugin_Settings_Interface
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @since 1.0
 */
interface VSP_Plugin_Settings_Interface {
	/**
	 * Add Pages
	 *
	 * @param array $pages .
	 *
	 * @return mixed
	 */
	public function add_pages( $pages = array() );

	/**
	 * Add Sections
	 *
	 * @param array $sections .
	 *
	 * @return mixed
	 */
	public function add_sections( $sections = array() );

	/**
	 * Add Fields
	 *
	 * @param array $fields .
	 *
	 * @return mixed
	 */
	public function add_fields( $fields = array() );
}
