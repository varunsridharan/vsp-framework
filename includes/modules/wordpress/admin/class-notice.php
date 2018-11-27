<?php

namespace VSP\Modules\WordPress\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class WordPress
 *VSP_WP_Admin_Notice_Interface
 *
 * @author    Panagiotis Vagenas <pan.vagenas@gmail.com>
 * @since     2.0.0
 * @package   vsp-framework/core/modules/admin-notices
 * @copyright 2018 Varun Sridharan
 * @license   GPLV3 Or Greater
 */
class Notice implements \VSP\Core\Interfaces\WP_Admin_Notice {
	/**
	 * @param \VSP\Modules\WordPress\WP_Notice $notice
	 *
	 * @return string
	 * @author Panagiotis Vagenas <pan.vagenas@gmail.com>
	 * @since  2.0.0
	 */
	public function formatOutput( \VSP\Modules\WordPress\WP_Notice $notice ) {
		$out = "
		<div style=\"position: relative;\" class=\"{$notice->getType()}\">
			<h4 style=\"margin-top: 4px; margin-bottom: 0;\">{$notice->getTitle()}</h4>
			<p>
				{$notice->getContent()}
			</p>
		</div>
		";
		return $out;
	}
}
