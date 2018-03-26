<?php
/**
 * Project: wp-admin-notices
 * File: FormatterInterface.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 1/11/2015
 * Time: 9:04 μμ
 * Since: 2.0.0
 * Copyright: 2015 Panagiotis Vagenas
 * Ifc FormatterInterface
 *
 * @package vsp-framework
 * @author  Panagiotis Vagenas <pan.vagenas@gmail.com>
 * @since   2.0.0
 */

/**
 * Interface VSP_WP_Admin_Notice_Interface
 */
interface VSP_WP_Admin_Notice_Interface {
	/**
	 * Returns the output of the notice formatted
	 *
	 * @param VSP_WP_Notice $notice .
	 *
	 * @return mixed
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since  2.0.0
	 */
	public function formatOutput( VSP_WP_Notice $notice );
}
