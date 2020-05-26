<?php

namespace VSP\Core\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Trait VSP_Framework_IP_Trait
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
trait URL {
	/**
	 * Get first item segment.
	 *
	 * @param $segments
	 *
	 * @return string
	 */
	public static function get_first_segment( $segments ) {
		$var = is_array( $segments ) ? $segments : self::segment_uri( $segments );
		return array_shift( $var );
	}

	/**
	 * Get all URL parts based on a / seperator.
	 *
	 * @param null $uri
	 *
	 * @return array string → segments
	 */
	public static function segment_uri( $uri = null ) {
		$uri = ( ! is_null( $uri ) ) ? $uri : $_SERVER['REQUEST_URI'];
		return explode( '/', trim( $uri, '/' ) );
	}

	/**
	 * This function converts and URL segment to an safe one.
	 * For example: `test name @132` will be converted to `test-name--123`.
	 * It will also return all letters in lowercase
	 *
	 * @param string $slug → URL slug to clean up
	 *
	 * @return null|string|string[]
	 */
	public static function generate_safe_slug( $slug ) {
		$slug = preg_replace( '/[^a-zA-Z0-9]/', '-', $slug );
		$slug = strtolower( trim( $slug, '-' ) );
		$slug = preg_replace( '/\-{2,}/', '-', $slug );
		return $slug;
	}

	/**
	 * Converts plain text URLS into HTML links.
	 * Second argument will be used as the URL label <a href=''>$custom</a>.
	 *
	 * @param string $url → URL
	 * @param string $custom → if provided, this is used for the link label
	 *
	 * @return string → returns the data with links created around URLS
	 */
	public static function auto_link( $url, $custom = null ) {
		$replace = ( null === $custom ) ? '<a href="http$2://$4">$1$2$3$4</a>' : '<a href="http$2://$4">' . $custom . '</a>';
		return preg_replace( '@(http)?(s)?(://)?(([-\w]+\.)+([^\s]+)+[^,.\s])@', $replace, $url );
	}

	/**
	 * Get the server port.
	 *
	 * @return int → server port
	 */
	public static function get_port() {
		return self::global_vars( 'SERVER_PORT' );
	}
}

