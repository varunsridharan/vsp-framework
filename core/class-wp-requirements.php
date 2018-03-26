<?php
if ( ! defined( "WPINC" ) ) {
	die;
}

Class VSP_WP_Requirements {
	protected $php        = '5.5';
	protected $wp         = '3.5';
	protected $plugins    = array();
	protected $php_ext    = array();
	protected $message    = array();
	protected $hasFailure = false;

	public function __construct( $args ) {
		$args = wp_parse_args( $args, array(
			'plugin_name' => '',
			'php'         => '5.5',
			'wordpress'   => '3.5',
			'plugins'     => false,
			'php_ext'     => false,
			'add_notice'  => true,
		) );

		$this->args    = $args;
		$this->php     = $args['php'];
		$this->wp      = $args['wordpress'];
		$this->plugins = is_array( $args['plugins'] ) ? $args['plugins'] : array();
		$this->php_ext = $args['php_ext'];
		$this->get_message();
		if ( $args['add_notice'] === true && $this->status() === true ) {
			add_action( "admin_notices", array( &$this, 'add_Notice' ) );
		}
	}

	public function get_message() {
		if ( isset( $this->message ) && ! empty( $this->message ) ) {
			return $this->message;
		}
		$message = array();

		$heading = '<h4>' . sprintf( __( "%s Plugin Could Not Be Activated / Loaded Due to the following reasons", 'vsp-framework' ), '<strong>' . $this->args['name'] . '</strong>' ) . '</h4>';
		if ( ! empty( $this->php ) ) {
			$php_status = new VS_Version_Compare( $this->php, 'PHP', phpversion() );
			if ( $php_status->status() === false ) {
				$message[] = '<li class="phpErr">' . $php_status->message() . '</li>';
			}
		}
		if ( ! empty( $this->wp ) ) {
			global $wp_version;
			$wp_status = new VS_Version_Compare( $this->wp, 'WordPress', $wp_version );
			if ( $wp_status->status() === false ) {
				$message[] = '<li class="wpErr">' . $wp_status->message() . '</li>';
			}
		}
		if ( ! empty( $this->php_ext ) ) {
			$php_ext_status = new VS_PHP_Exts_Requirements( $this->php_ext );
			if ( $php_ext_status->status() === false ) {
				$message[] = $php_ext_status->message();
			}
		}
		if ( ! empty( $this->plugins ) ) {
			$plugins = new VS_WP_Plugin_Requirements( $this->plugins );
			if ( $plugins->status() === false ) {
				$message[] = $plugins->message();
			}
		}

		if ( empty( $message ) ) {
			$this->hasFailure = false;
		} else {
			$this->hasFailure         = true;
			$this->message['message'] = '<div class="vs_wp_requirement"> ' . $heading . ' <ul>' . implode( " ", $message ) . '</ul> </div>';
			$this->message['style']   = '.vs_wp_requirement h4{margin-top:0; font-size: 1.2em;} .vs_wp_requirement{padding:10px 0;} .vs_wp_requirement > ul{background: #f0f0f0;padding: 10px; border: 1px solid #bcbcbc; border-radius: 5px; max-height: 150px; overflow-y: scroll !important; overflow: hidden;} .vs_wp_requirement .phpErr, .vs_wp_requirement .wpErr {color:#ce3030;} .vs_wp_requirement > ul > li > strong{ display: inline-block; margin-bottom:10px; } .vs_wp_requirement > li { margin-bottom:10px; } .vs_wp_requirement li ul{ margin-left: 15px; color: #ce3030; display: inline-block; width: 100%; font-size: 14px; }';
		}
		return $this->message;
	}

	public function status() {
		return $this->hasFailure;
	}

	public function add_Notice() {
		echo '<div class="updated">' . $this->message['message'] . '<style>' . $this->message['style'] . '</style></div>';
	}
}

Class VS_PHP_Exts_Requirements {
	protected $exts                   = array();
	protected $installed              = array();
	protected $not_installed          = array();
	protected $not_required_installed = array();
	protected $not_required           = array();

	public function __construct( $exts = array() ) {
		$this->exts = $exts;
		$this->validate();
	}

	public function validate() {
		foreach ( $this->exts as $id => $data ) {
			$is_bool      = is_bool( $data );
			$ext_name     = ( $is_bool === true ) ? $id : $data;
			$g_status     = ( $is_bool === true && $data === false ) ? false : true;
			$is_installed = extension_loaded( $ext_name );

			if ( $g_status === false ) {
				if ( $is_installed === true ) {
					$this->not_required_installed[] = $ext_name;
				} else {
					$this->not_required[] = $ext_name;
				}

			} elseif ( $g_status === true ) {
				if ( $is_installed === true ) {
					$this->installed[] = $ext_name;
				} else {
					$this->not_installed[] = $ext_name;
				}
			}
		}
	}

	public function status() {
		if ( empty( $this->not_installed ) && empty( $this->not_required_installed ) ) {
			return true;
		}
		return false;
	}

	public function message() {
		$html = '';

		if ( ! empty( $this->not_installed ) ) {
			$html .= '<li><strong>' . __( "Required PHP Extensitions :", 'vsp-framework' ) . '</strong>';
			$html .= '<ul>';
			foreach ( $this->not_installed as $a ) {
				$html .= '<li>' . $a . '</li>';
			}
			$html .= '</ul></li>';
		}

		if ( ! empty( $this->not_required_installed ) ) {
			$html .= '<li><strong>' . __( "PHP Extensitions Installed & Not Supported By This Plugin", 'vsp-framework' ) . '</strong>';
			$html .= '<ul>';
			foreach ( $this->not_required_installed as $a ) {
				$html .= '<li>' . $a . '</li>';
			}
			$html .= '</ul></li>';
		}

		return $html;
	}

}

Class VS_Version_Compare {

	public function __construct( $version, $type = 'PHP', $current_version = '' ) {
		if ( is_array( $version ) ) {
			$compare  = ( isset( $version[1] ) && is_string( $version[1] ) && is_string( $version[0] ) ) ? $version[1] : $version[0];
			$_version = $version[0];
		} else {
			$compare  = '>=';
			$_version = $version;
		}

		$this->status          = version_compare( $current_version, $_version, $compare );
		$this->compare         = $compare;
		$this->type            = $type;
		$this->current_version = $current_version;
		$this->version         = $_version;
	}

	public function status() {
		return $this->status;
	}

	public function message() {
		$html = '';
		if ( $this->compare === '>=' ) {
			$html .= sprintf( __( "Required %s : %s+ ", 'vsp-framework' ), $this->type, $this->version );
		} elseif ( $this->compare === '<=' ) {
			$html .= sprintf( __( "Required %s : %s or lower", 'vsp-framework' ), $this->type, $this->version );
		} elseif ( $this->compare === '==' ) {
			$html .= sprintf( __( "Required %s : %s ", 'vsp-framework' ), $this->type, $this->version );
		}

		$html .= ' | ' . sprintf( __( "Detected : %s", 'vsp-framework' ), $this->current_version );
		return $html;
	}
}

Class VS_WP_Plugin_Requirements {
	protected static $active_plugins = array();
	protected static $_plugins       = array();
	private          $inactive       = array();
	private          $versionIssue   = array();
	private          $active         = array();

	public function __construct( $data ) {
		$this->plugins = $data;
		$this->validate();
	}

	public function validate() {
		foreach ( $this->plugins as $_slug => $data ) {
			if ( is_string( $data ) === true && is_string( $_slug ) === false ) {
				$_slug = $data;
				$data  = array();
			}

			$args = wp_parse_args( $data, array(
				'compare' => '==',
				'name'    => $_slug,
				'version' => false,
			) );

			$is_active = self::is_active( $_slug );

			if ( $is_active === true && $args['version'] !== false ) {
				if ( ! function_exists( 'is_plugin_active' ) ) {
					include_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				if ( ! isset( self::$_plugins[ $_slug ] ) ) {
					self::$_plugins[ $_slug ] = get_plugin_data( WP_PLUGIN_DIR . '/' . $_slug, false, false );
				}

				if ( self::$_plugins[ $_slug ] !== false ) {
					//self::$_plugins[$_slug]
					if ( $args['name'] === $_slug && isset( self::$_plugins[ $_slug ]['Name'] ) ) {
						$args['name'] = self::$_plugins[ $_slug ]['Name'];
					}

					$plugin_V = new VS_Version_Compare( array(
						$args['version'],
						$args['compare'],
					), $args['name'], self::$_plugins[ $_slug ]['Version'] );
					if ( $plugin_V->status() === false ) {
						$this->versionIssue[ $_slug ] = array_merge( $args, array( 'message' => $plugin_V->message() ) );
					} else {
						$this->active[ $_slug ] = $args;
					}
				}

			} elseif ( $is_active && $args['version'] === false ) {
				$this->active[ $_slug ] = $args;
			} else {
				$this->inactive[ $_slug ]            = $args;
				$this->inactive[ $_slug ]['message'] = sprintf( __( "%s Plugin Must Be Active", 'vsp-framework' ), $args['name'] );

			}
		}
	}

	public static function is_active( $file ) {
		if ( ! self::$active_plugins ) {
			self::init();
		}
		return in_array( $file, self::$active_plugins ) || array_key_exists( $file, self::$active_plugins );
	}

	public static function init() {
		self::$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
	}

	public function status() {
		if ( ! empty( $this->inactive ) && ! empty( $this->versionIssue ) ) {
			return false;
		} elseif ( ! empty( $this->inactive ) || ! empty( $this->versionIssue ) ) {
			return false;
		}

		return true;
	}

	public function message() {
		$html = '';
		if ( ! empty( $this->inactive ) ) {
			$message = '<li><strong>' . __( "Version Compatibility", 'vsp-framework' ) . '</strong><ul>';
			$arr     = wp_list_pluck( $this->versionIssue, 'message' );
			foreach ( $arr as $r ) {
				$message .= '<li>' . $r . '</li>';
			}
			$message .= '</ul></li>';
			$html    .= $message;
		}
		if ( ! empty( $this->inactive ) ) {
			$message = '<li><strong>' . __( "Inactive Plugins", 'vsp-framework' ) . '</strong><ul>';
			$arr     = wp_list_pluck( $this->inactive, 'message' );
			foreach ( $arr as $r ) {
				$message .= '<li>' . $r . '</li>';
			}
			$message .= '</ul></li>';
			$html    .= $message;
		}
		return $html;
	}
}
