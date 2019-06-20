<?php

namespace VSP\Modules\WordPress;

use VSP\Base;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( ! class_exists( '\VSP\Modules\WordPress\Importers' ) ) {
	/**
	 * Class VSP_WP_Importers
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	final class Importers extends Base {
		/**
		 * Importers
		 *
		 * @var array
		 */
		protected static $_importers = array();

		/**
		 * VSP_WP_Importers constructor.
		 */
		public function __construct() {
			parent::__construct();
		}

		/**
		 * Registers Importers
		 *
		 * @param string $slug .
		 * @param string $name .
		 * @param string $description .
		 * @param string $file .
		 * @param bool   $wpsf .
		 *
		 * @return mixed
		 */
		public function add( $slug = '', $name = '', $description = '', $file = '', $wpsf = false ) {
			if ( ! isset( self::$_importers[ $slug ] ) ) {
				register_importer( $slug, $name, $description, array( __CLASS__, 'load_importer' ) );
				self::$_importers[ $slug ] = array(
					'file' => $file,
					'wpsf' => $wpsf,
				);
			}
			return true;
		}

		/**
		 * Loads Importer once its needed
		 */
		public static function load_importer() {
			if ( isset( $_GET['import'] ) ) {
				$importer = $_GET['import'];
				if ( isset( self::$_importers[ $importer ] ) ) {
					$class = include self::$_importers[ $importer ]['file'];
					$class->dispatch();
				}
			}

		}


	}

	if ( ! function_exists( 'vsp_add_importer' ) ) {
		/**
		 * Registers Importers
		 *
		 * @param string $slug .
		 * @param string $title .
		 * @param string $description .
		 * @param string $file .
		 * @param bool   $wpsf .
		 *
		 * @return mixed
		 */
		function vsp_add_importer( $slug = '', $title = '', $description = '', $file = '', $wpsf = true ) {
			return Importers::instance()
				->add( $slug, $title, $description, $file, $wpsf );
		}
	}
}
