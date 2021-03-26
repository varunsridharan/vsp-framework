<?php

namespace VSP;

defined( 'ABSPATH' ) || exit;

/**
 * Class Framework_Base
 *
 * @package VSP
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 */
abstract class Framework_Base extends Base {

	/**
	 * Returns base_defaults Values.
	 *
	 * @return array
	 */
	protected function base_defaults() {
		return array(
			'version'   => false,
			'file'      => __FILE__,
			'slug'      => false,
			'db_slug'   => false,
			'hook_slug' => false,
			'name'      => false,
		);
	}

	/**
	 * Framework_Base constructor.
	 *
	 * @param array $options
	 */
	public function __construct( $options = array() ) {
		$this->set_args( $options );
	}

	/**
	 * Returns $this->file
	 *
	 * @return string
	 */
	public function file() {
		return $this->option( 'file', __FILE__ );
	}

	/**
	 * Returns $this->version
	 *
	 * @return bool|mixed
	 */
	public function version() {
		return $this->option( 'version' );
	}

	/**
	 * Returns with slug value for the given types (slug,db,hook)
	 *
	 * @param string $type .
	 *
	 * @return string
	 */
	public function slug( $type = 'slug' ) {
		switch ( $type ) {
			case 'db':
				$return = $this->option( 'db_slug' );
				break;
			case 'hook':
				$return = $this->option( 'hook_slug' );
				break;
			default:
				$return = $this->option( 'slug' );
				break;
		}
		return $return;
	}

	/**
	 * Returns $this->name
	 *
	 * @return bool|mixed
	 */
	public function name() {
		return $this->option( 'name' );
	}

	/**
	 * Returns A Valid Documentation Link
	 *
	 * @return string
	 * @since 0.8.9.8
	 */
	public function link_documentation() {
		return sprintf( 'https://p.sva.wiki/%1$s', $this->slug() );
	}

	/**
	 * Returns A Valid ChangeLog Link
	 *
	 * @return string
	 * @since 0.8.9.8
	 */
	public function link_changelog() {
		return sprintf( $this->link_documentation() . '/%1$s', 'change-log' );
	}

	/**
	 * Returns A Valid Support Link
	 *
	 * @return string
	 * @since 0.8.9.8
	 */
	public function link_support() {
		return sprintf( 'https://support.varunsridharan.in/?item=%1$s', $this->slug() );
	}

	/**
	 * Returns A Valid Demo Link
	 *
	 * @return string
	 * @since 0.8.9.8
	 */
	public function link_demo() {
		return sprintf( 'https://%1$s.sva.one/', $this->slug() );
	}
}
