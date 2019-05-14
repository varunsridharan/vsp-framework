<?php
/**
 * Lightweight PSR-4 PHP Autoloader Class.
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.0
 * @since 1.0
 * @link
 * @copyright 2018 Varun Sridharan
 * @license GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

namespace Varunsridharan\PHP;

use Exception;

if ( ! class_exists( '\Varunsridharan\PHP\Autoloader' ) ) {
	/**
	 * Class Autoloader
	 *
	 * @package Varunsridharan\PHP
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class Autoloader {
		/**
		 * Stores Subfolder Informations.
		 *
		 * @var array
		 * @access
		 * @static
		 */
		protected static $subfolders = array();

		/**
		 * @var array
		 * @access
		 */
		protected $options = array();

		/**
		 * Stores Namespace.
		 *
		 * @var null
		 * @access
		 */
		protected $namespace = null;

		/**
		 * Stores Base Path.
		 *
		 * @var bool
		 * @access
		 */
		protected $base_path = false;

		/**
		 * Stores Mapping.
		 * Stores an array of class name and file as key value in a array
		 *
		 * @example array(
		 *    '\somenamespace\someclass' => 'your-path/file.php'
		 *    '\somenamespace\someclass2' => 'your-path/file2.php'
		 * )
		 *
		 * @var bool
		 * @access
		 */
		protected $mappings = false;

		/**
		 * Autoloader constructor.
		 *
		 * @param $namespace
		 * @param $path
		 * @param $options
		 */
		public function __construct( $namespace, $path, $options = array() ) {
			$this->set_options( $options );

			if ( $this->register() ) {
				$this->namespace = $namespace;
				$this->base_path = $path;
			}
		}

		/**
		 * Sets Option.
		 *
		 * @param $options
		 *
		 * @return $this
		 */
		protected function set_options( $options ) {
			$this->options            = array_merge( array(
				'exclude'        => false,
				'prepend'        => false,
				'mapping'        => false,
				'search_folders' => false,
			), $options );
			$this->options['exclude'] = ( ! is_array( $this->options['exclude'] ) ) ? array_filter( array( $this->options['exclude'] ) ) : $this->options['exclude'];
			$this->options['mapping'] = ( ! is_array( $this->options['mapping'] ) ) ? array() : $this->options['mapping'];
			$this->mappings           = $this->options['mapping'];
			unset( $this->options['mapping'] );
			return $this;
		}

		/**
		 * Returns An Option.
		 *
		 * @param      $key
		 * @param bool $default
		 *
		 * @return bool|mixed
		 */
		protected function option( $key, $default = false ) {
			return ( isset( $this->options[ $key ] ) ) ? $this->options[ $key ] : $default;
		}

		/**
		 * Registers Autoloader With PHP.
		 *
		 * @return bool
		 */
		protected function register() {
			try {
				spl_autoload_register( array( &$this, 'autoload' ), true, $this->option( 'prepend' ) );
			} catch ( Exception $exception ) {
				return false;
			}
			return true;
		}

		/**
		 * De-Registers Autoloader In PHP.
		 *
		 * @return bool|boolean
		 */
		public function unregister() {
			return spl_autoload_unregister( array( &$this, 'autoload' ) );
		}

		/**
		 * Removes trailing forward slashes and backslashes if they exist.
		 *
		 * @param string $string What to remove the trailing slashes from.
		 *
		 * @return string String without the trailing slashes.
		 */
		protected function unslashit( $string ) {
			return rtrim( $string, '/\\' );
		}

		/**
		 * Appends a trailing slash.
		 *
		 * @param string $string What to add the trailing slash to.
		 *
		 * @return string String with trailing slash added.
		 */
		protected function slashit( $string ) {
			return $this->unslashit( $string ) . '/';
		}

		/**
		 * @param $class
		 */
		public function autoload( $class ) {
			if ( true === $this->is_valid_lookup( $class ) ) {
				$filenames = null;
				$folders   = null;
				$is_loaded = false;

				/**
				 * Checks and loads file if given class exists in mapping array.
				 *
				 * @example array(
				 *    '\somenamespace\someclass' => 'your-path/file.php'
				 *    '\somenamespace\someclass2' => 'your-path/file2.php'
				 * )
				 */
				if ( isset( $this->mappings[ $class ] ) ) {
					$is_loaded = $this->load_file( $this->mappings[ $class ], $class );
					if ( false === $is_loaded ) {
						$is_loaded = $this->load_file( $this->base_path . $this->mappings[ $class ], $class );
					}
				}

				/**
				 * Checks and loads class based on the files & folder found.
				 */
				if ( false === $is_loaded ) {
					$filenames = $this->get_possible_filenames( $class );
					$folders   = $this->get_possible_foldernames( $class );

					if ( is_array( $filenames ) && is_array( $folders ) && ! empty( $filenames ) && ! empty( $folders ) ) {
						foreach ( $folders as $folder ) {
							foreach ( $filenames as $file ) {
								$is_loaded = $this->load_file( $this->slashit( $this->base_path . $folder ) . $file, $class );
								if ( $is_loaded ) {
									break;
								}
							}
							if ( $is_loaded ) {
								break;
							}
						}
					}
				}

				/**
				 * Checks and loads class based on all subfolder in the given path
				 */
				if ( false === $is_loaded && true === $this->option( 'search_folders' ) ) {
					$folders = $this->get_folders( $this->base_path );

					if ( is_array( $folders ) && is_array( $filenames ) && ! empty( $filenames ) ) {
						foreach ( $folders as $folder ) {
							foreach ( $filenames as $file ) {
								$is_loaded = $this->load_file( $this->slashit( $folder ) . $file, $class );
								if ( $is_loaded ) {
									break;
								}
							}
						}
					}
				}
			}
		}

		/**
		 * Saves & Returns Core Folders.
		 *
		 * @param $path
		 *
		 * @return mixed
		 */
		protected function get_folders( $path ) {
			if ( isset( self::$subfolders[ $path ] ) ) {
				return self::$subfolders[ $path ];
			}
			self::$subfolders[ $path ] = array_unique( array_filter( $this->search_folders( $path ) ) );
			return self::$subfolders[ $path ];
		}

		/**
		 * Search folders & subfolders inside a given folder and returns it.
		 *
		 * @param null $path
		 *
		 * @return array|string
		 */
		protected function search_folders( $path = null ) {
			$return       = ( null === $path || $path === $this->base_path ) ? array( $this->base_path ) : array();
			$path         = ( null === $path ) ? $this->base_path : $path;
			$base_folders = array_filter( glob( $this->slashit( $path ) . '*', GLOB_ONLYDIR ) );

			if ( ! empty( $base_folders ) ) {
				foreach ( $base_folders as $folder ) {
					$return[] = $folder;
					$return   = array_merge( $return, $this->search_folders( $folder ) );
				}
			}
			return $return;
		}

		/**
		 * Returns Actual Class Name.
		 *
		 * @param $class
		 *
		 * @return string
		 * @example $class = \YourClassNameSpace\AnotherNamespace\YourClass then it returns as yourclass
		 *
		 */
		protected function get_actual_classname( $class ) {
			$file = explode( '\\', strtolower( $class ) );
			return strtolower( end( $file ) );
		}

		/**
		 * Returns Possible Filenames.
		 *
		 * @param $class
		 *
		 * @return array|bool
		 */
		protected function get_possible_filenames( $class ) {
			if ( ! empty( $class ) ) {
				$class_name = $this->get_actual_classname( $class );
				if ( empty( $class_name ) ) {
					return false;
				}
				// Replaces Strings that has underscores with - (your_string to your-string)
				$class_with_hypen = str_replace( '_', '-', $class_name );
				// Replaces Strings that has underscores with - (your_string to your.string)
				$class_with_dot = str_replace( '_', '.', $class_name );

				/**
				 * @example array (
				 *    'your_class.php',
				 *    'your-class.php',
				 *    'your.class.php',
				 *    'class_your_class.php',
				 *    'class-your-class.php',
				 *    'class.your.class.php',
				 *    'abstract_your_class.php',
				 *    'abstract-your-class.php',
				 *    'abstract.your.class.php',
				 *    'trait_your_class.php',
				 *    'trait-your-class.php',
				 *    'trait.your.class.php',
				 *    'interface_your_class.php',
				 *    'interface-your-class.php',
				 *    'interface.your.class.php',
				 * )
				 */
				$return = array();

				$return[] = 'class-' . $class_with_hypen . '.php';
				$return[] = $class_with_hypen . '.php';
				$return[] = 'abstract-' . $class_with_hypen . '.php';
				$return[] = 'trait-' . $class_with_hypen . '.php';
				$return[] = 'interface-' . $class_with_hypen . '.php';

				$return[] = 'class_' . $class_name . '.php';
				$return[] = $class_name . '.php';
				$return[] = 'abstract_' . $class_name . '.php';
				$return[] = 'trait_' . $class_name . '.php';
				$return[] = 'interface_' . $class_name . '.php';

				$return[] = 'class.' . $class_with_dot . '.php';
				$return[] = $class_with_dot . '.php';
				$return[] = 'abstract.' . $class_with_dot . '.php';
				$return[] = 'trait.' . $class_with_dot . '.php';
				$return[] = 'interface.' . $class_with_dot . '.php';

				return array_unique( array_filter( $return ) );
			}
			return false;
		}

		/**
		 * @param $class
		 *
		 * @return array|boolean
		 *
		 * @example array (size=3)
		 *    1 => string '/path1/path1_base/path2/' (length=24)
		 *    2 => string '/path1/path1-base/path2/' (length=24)
		 *    3 => string 'your_class' (length=10)
		 *    4 => string 'your-class' (length=10)
		 * )
		 */
		protected function get_possible_foldernames( $class ) {
			$class_name = $this->get_actual_classname( $class );
			if ( empty( $class_name ) ) {
				return false;
			}

			$_class = explode( '\\', $class );

			$_keys = array_keys( $_class );
			if ( ! empty( $_keys ) ) {
				$end = end( $_keys );
				if ( ! empty( $end ) ) {
					unset( $_class[ $end ] );
				}
			}
			$_class = strtolower( implode( '\\', $_class ) );

			$folder_class = str_replace( strtolower( $this->namespace ), '', $_class );
			$folder_class = str_replace( '\\', '/', $folder_class );
			$folder_class = trim( $folder_class, '/' );
			$folder_class = trim( $folder_class, '\\' );

			return array_unique( array_filter( array(
				$folder_class,
				str_replace( '_', '-', $folder_class ),
				$class_name,
				str_replace( '_', '-', $class_name ),
				'/',
			) ) );
		}

		/**
		 * @param $path
		 * @param $class
		 *
		 * @return bool
		 */
		protected function load_file( $path, $class ) {
			if ( is_readable( $path ) ) {
				include_once $path;
			}
			return ( class_exists( $class, false ) || trait_exists( $class, false ) || interface_exists( $class, false ) );
		}

		/**
		 * Checks if requested class is a valid lookup.
		 *
		 * @param string $class
		 *
		 * @return bool
		 */
		protected function is_valid_lookup( $class ) {
			$namespace = strtolower( $this->namespace );
			$lookup    = strtolower( $class );

			if ( false === strpos( $lookup, $namespace, false ) ) {
				return false;
			}

			if ( ! empty( $this->option( 'exclude' ) ) && is_array( $this->option( 'exclude' ) ) ) {
				foreach ( $this->option( 'exclude' ) as $namespace ) {
					$namespace = strtolower( $namespace );
					if ( false !== strpos( $lookup, strtolower( $namespace ) ) || false !== strpos( '\\' . $lookup, strtolower( $namespace ) ) ) {
						return false;
					}
				}
			}
			return true;
		}

		/**
		 * Adds Mapping.
		 *
		 * @param $class
		 * @param $file
		 *
		 * @return $this
		 */
		public function map( $class, $file ) {
			$this->mappings[ $class ] = $file;
			return $this;
		}

		/**
		 * Unmaps.
		 *
		 * @param $class
		 *
		 * @return $this
		 */
		public function unmap( $class ) {
			if ( isset( $this->mappings[ $class ] ) ) {
				unset( $this->mappings[ $class ] );
			}
			return $this;
		}
	}
}
