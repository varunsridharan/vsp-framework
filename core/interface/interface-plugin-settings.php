<?php

/**
 * Interface VSP_Plugin_Settings_Interface
 * Created by PhpStorm.
 * User: varun
 * Date: 27-02-2018
 * Time: 09:09 AM
 *
 * @package vsp-framework
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