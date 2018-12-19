<?php
/**
 * VSP WP Post Helper
 *
 * User: varun
 * Date: 01-04-2018
 * Time: 07:22 AM
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @version   1.0
 * @copyright GPL V3 Or greater
 * @package vsp-framework/core/helpers
 */

namespace VSP\Modules\WordPress;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}


if ( ! class_exists( 'Post' ) ) {
	/**
	 * Class Post
	 *
	 * @package VSP\Modules\WordPress
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class Post extends \Varunsridharan\WordPress\Post {
	}
}
