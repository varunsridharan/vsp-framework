<?php
/**
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @since     1.0
 * @package   vsp-framework
 * @copyright GPL V3 Or greater
 */


if ( ! class_exists( 'SweetAlert2' ) ) {
	/**
	 * Class SweetAlert2
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class SweetAlert2 implements \JsonSerializable {
		/**
		 * swal_type
		 *
		 * @var string
		 */
		protected $swal_type = '';

		/**
		 * json_data
		 *
		 * @var array
		 */
		protected $json_data = array();

		/**
		 * before
		 *
		 * @var string
		 */
		protected $before = '';

		/**
		 * after
		 *
		 * @var string
		 */
		protected $after = '';

		/**
		 * then
		 *
		 * @var array
		 */
		protected $then = array();

		/**
		 * common_functions
		 *
		 * @var bool
		 */
		protected static $common_functions = false;

		/**
		 * Converts Javascript Function into array.
		 *
		 * @param $args
		 *
		 * @return mixed
		 */
		public static function handle_js_function( $args ) {
			foreach ( $args as $i => $ar ) {
				if ( is_array( $ar ) ) {
					$args[ $i ] = self::handle_js_function( $ar );
				} elseif ( is_string( $ar ) ) {
					$re = '/\bfunction[ ]{0,1}(\(((?>[^()]+|(?-2))*)\))(\{((?>[^{}]+|(?-2))*)\})/';
					preg_match_all( $re, $ar, $matches, PREG_SET_ORDER, 0 );
					if ( is_array( $matches ) && ! empty( array_filter( $matches ) ) ) {
						$args[ $i ] = array(
							'js_args'     => false,
							'js_contents' => false,
						);

						if ( isset( $matches[0][2] ) ) {
							$args[ $i ]['js_args'] = ( empty( $matches[0][2] ) ) ? false : $matches[0][2];
						}

						if ( isset( $matches[0][4] ) ) {
							$args[ $i ]['js_contents'] = ( empty( $matches[0][4] ) ) ? false : $matches[0][4];
						}
					}
				}
			}

			return $args;
		}

		/**
		 * @return mixed
		 */
		public function jsonSerialize() {
			return $this->render();
		}

		/**
		 * SweetAlert2 constructor.
		 *
		 * @param string $title
		 * @param string $content
		 * @param string $type
		 * @param array  $args
		 */
		public function __construct( $title = '', $content = '', $type = 'success', $args = array() ) {
			if ( ! is_array( $title ) ) {
				$this->title( $title );
				$this->content( $content );
				$this->type( $type );
				$this->handle_bulk_data( $args );
			} else {
				$this->handle_bulk_data( $title );
				$this->handle_bulk_data( $args );
			}
			return $this;
		}


		/**
		 * @param $args
		 */
		private function handle_bulk_data( $args ) {
			foreach ( $args as $key => $values ) {
				if ( method_exists( $this, $key ) ) {
					call_user_func( array( &$this, $key ), $values );
				} else {
					$this->json_data( $key, $values );
				}
			}
		}

		/**
		 * @param string $key
		 * @param null   $value
		 * @param bool   $force
		 *
		 * @return $this
		 */
		protected function json_data( $key = '', $value = null, $force = false ) {
			if ( ! is_array( $value ) || true === $force ) {
				$this->json_data[ $key ] = $value;
			} elseif ( false === $force ) {
				$this->handle_bulk_data( $value );
			}
			return $this;
		}

		/**
		 * @param $key
		 *
		 * @return bool|mixed
		 */
		protected function data( $key ) {
			return ( isset( $this->json_data[ $key ] ) ) ? $this->json_data[ $key ] : false;
		}

		/**
		 * @return mixed
		 */
		public function __toString() {
			return $this->render();
		}

		/**
		 * @return false|string
		 */
		public function to_json() {
			$this->json_data = $this->handle_js_function( $this->json_data );
			return $this->json_encode_data( $this->json_data );
		}

		public function json_encode_data( $data ) {
			return json_encode( $data );
		}

		/**
		 * @return array
		 */
		public function to_array() {
			return $this->json_data;
		}

		/**
		 * @return string
		 */
		public function render() {
			$json     = $this->to_json();
			$variable = 'swal' . md5( $json );
			$output   = $this->before . ' var ' . $variable . ' = vsp_js_function(' . $json . ');';

			$is_mixing = ( false === $this->swal_type || empty( $this->swal_type ) ) ? '' : '.' . $this->swal_type;
			$output    = $output . ' var ' . $variable . ' = swal' . $is_mixing . '(' . $variable . ')';

			if ( ! empty( $this->then ) ) {
				$output .= implode( '', $this->then );
			}
			return $output . ';' . $this->after;
		}

		/**
		 * @param null $title
		 *
		 * @return \SweetAlert2
		 */
		public function title( $title = null ) {
			return $this->json_data( 'title', $title );
		}

		/**
		 * @param null $title
		 *
		 * @return \SweetAlert2
		 */
		public function titleText( $title = null ) {
			return $this->json_data( 'titleText', $title );
		}

		/**
		 * @param $text
		 *
		 * @return \SweetAlert2
		 */
		public function text( $text ) {
			return $this->json_data( 'text', $text );
		}

		/**
		 * @param null $content
		 *
		 * @return \SweetAlert2
		 */
		public function content( $content = null ) {
			return $this->json_data( 'text', $content );
		}

		/**
		 * @param string $notice_type
		 *
		 * @return \SweetAlert2
		 */
		public function type( $notice_type = 'success' ) {
			return $this->json_data( 'type', $notice_type );
		}

		/**
		 * @param null $content
		 *
		 * @return \SweetAlert2
		 */
		public function footer( $content = null ) {
			return $this->json_data( 'footer', $content );
		}

		/**
		 * @param bool $url
		 * @param null $width
		 * @param null $height
		 * @param null $alt
		 * @param null $class
		 *
		 * @return $this
		 */
		public function image( $url = false, $width = null, $height = null, $alt = null, $class = null ) {
			$this->imageUrl( $url );
			$this->imageWidth( $width );
			$this->imageHeight( $height );
			$this->imageClass( $class );
			$this->imageAlt( $alt );
			return $this;
		}

		/**
		 * @param $imageUrl
		 *
		 * @return \SweetAlert2
		 */
		public function imageUrl( $imageUrl ) {
			return $this->json_data( 'imageUrl', $imageUrl );
		}

		/**
		 * @param $imageWidth
		 *
		 * @return \SweetAlert2
		 */
		public function imageWidth( $imageWidth ) {
			return $this->json_data( 'imageWidth', $imageWidth );
		}

		/**
		 * @param $imageHeight
		 *
		 * @return \SweetAlert2
		 */
		public function imageHeight( $imageHeight ) {
			return $this->json_data( 'imageHeight', $imageHeight );
		}

		/**
		 * @param $imageClass
		 *
		 * @return \SweetAlert2
		 */
		public function imageClass( $imageClass ) {
			return $this->json_data( 'imageClass', $imageClass );
		}

		/**
		 * @param $imageAlt
		 *
		 * @return \SweetAlert2
		 */
		public function imageAlt( $imageAlt ) {
			return $this->json_data( 'imageAlt', $imageAlt );
		}

		/**
		 * @param null $html_content
		 *
		 * @return \SweetAlert2
		 */
		public function html( $html_content = null ) {
			if ( null === $html_content ) {
				$html_content = $this->data( 'text' );
				$this->content( null );
			}
			return $this->json_data( 'html', $html_content );
		}

		/**
		 * @param bool $text
		 * @param null $color
		 * @param null $class
		 * @param null $aria_label
		 *
		 * @return $this
		 */
		public function cancel_button( $text = false, $color = null, $class = null, $aria_label = null ) {
			$is_show = true;
			if ( false === $text ) {
				$text       = null;
				$color      = null;
				$aria_label = null;
				$class      = null;
				$is_show    = false;
			}

			$this->cancelButtonText( $text );
			$this->showCancelButton( $is_show );
			$this->cancelButtonColor( $color );
			$this->cancelButtonClass( $class );
			$this->cancelButtonAriaLabel( $aria_label );
			return $this;
		}

		/**
		 * @param $cancelButtonText
		 *
		 * @return \SweetAlert2
		 */
		public function cancelButtonText( $cancelButtonText ) {
			return $this->json_data( 'cancelButtonText', $cancelButtonText );
		}

		/**
		 * @param $showCancelButton
		 *
		 * @return \SweetAlert2
		 */
		public function showCancelButton( $showCancelButton ) {
			return $this->json_data( 'showCancelButton', $showCancelButton );
		}

		/**
		 * @param $cancelButtonColor
		 *
		 * @return \SweetAlert2
		 */
		public function cancelButtonColor( $cancelButtonColor ) {
			return $this->json_data( 'cancelButtonColor', $cancelButtonColor );
		}

		/**
		 * @param $cancelButtonClass
		 *
		 * @return \SweetAlert2
		 */
		public function cancelButtonClass( $cancelButtonClass ) {
			return $this->json_data( 'cancelButtonClass', $cancelButtonClass );
		}

		/**
		 * @param $cancelButtonAriaLabel
		 *
		 * @return \SweetAlert2
		 */
		public function cancelButtonAriaLabel( $cancelButtonAriaLabel ) {
			return $this->json_data( 'cancelButtonAriaLabel', $cancelButtonAriaLabel );
		}

		/**
		 * @param bool $text
		 * @param null $color
		 * @param null $class
		 * @param null $aria_label
		 *
		 * @return $this
		 */
		public function confirm_button( $text = false, $color = null, $class = null, $aria_label = null ) {
			$is_show = true;
			if ( false === $text ) {
				$text       = null;
				$color      = null;
				$aria_label = null;
				$class      = null;
				$is_show    = false;
			}

			$this->confirmButtonText( $text );
			$this->showConfirmButton( $is_show );
			$this->confirmButtonColor( $color );
			$this->confirmButtonClass( $class );
			$this->confirmButtonAriaLabel( $aria_label );
			return $this;
		}

		/**
		 * @param $confirmButtonText
		 *
		 * @return \SweetAlert2
		 */
		public function confirmButtonText( $confirmButtonText ) {
			return $this->json_data( 'confirmButtonText', $confirmButtonText );
		}

		/**
		 * @param $showConfirmButton
		 *
		 * @return \SweetAlert2
		 */
		public function showConfirmButton( $showConfirmButton ) {
			return $this->json_data( 'showConfirmButton', $showConfirmButton );
		}

		/**
		 * @param $confirmButtonColor
		 *
		 * @return \SweetAlert2
		 */
		public function confirmButtonColor( $confirmButtonColor ) {
			return $this->json_data( 'confirmButtonColor', $confirmButtonColor );
		}

		/**
		 * @param $confirmButtonClass
		 *
		 * @return \SweetAlert2
		 */
		public function confirmButtonClass( $confirmButtonClass ) {
			return $this->json_data( 'confirmButtonClass', $confirmButtonClass );
		}

		/**
		 * @param $confirmButtonAriaLabel
		 *
		 * @return \SweetAlert2
		 */
		public function confirmButtonAriaLabel( $confirmButtonAriaLabel ) {
			return $this->json_data( 'confirmButtonAriaLabel', $confirmButtonAriaLabel );
		}

		/**
		 * @return \SweetAlert2
		 */
		public function hide_cancel() {
			return $this->json_data( 'showCancelButton', false );
		}

		/**
		 * @return \SweetAlert2
		 */
		public function hide_confirm() {
			return $this->json_data( 'showConfirmButton', false );
		}

		/**
		 * @param string $position
		 *
		 * @return \SweetAlert2
		 */
		public function position( $position = 'center' ) {
			return $this->json_data( 'position', $position );
		}

		/**
		 * @param bool $stats
		 *
		 * @return \SweetAlert2
		 */
		public function animation( $stats = true ) {
			return $this->json_data( 'animation', $stats );
		}

		/**
		 * @param null $custom_class
		 *
		 * @return \SweetAlert2
		 */
		public function customClass( $custom_class = null ) {
			return $this->json_data( 'customClass', $custom_class );
		}

		/**
		 * @param $then
		 *
		 * @return $this
		 */
		public function then( $then ) {
			if ( $then instanceof SweetAlert2 ) {
				$then = $then->render();
			}
			$this->then[] = '.then((result) => {' . $then . '})';
			return $this;
		}

		/**
		 * @param $before
		 * @param $after
		 *
		 * @return $this
		 */
		public function wrap( $before, $after ) {
			$this->before = $before;
			$this->after  = $after;
			return $this;
		}

		/**
		 * @param null $timer
		 *
		 * @return \SweetAlert2
		 */
		public function auto_close( $timer = null ) {
			return $this->json_data( 'timer', $timer );
		}

		/**
		 * @param bool $status
		 *
		 * @return \SweetAlert2
		 */
		public function buttonsStyling( $status = true ) {
			return $this->json_data( 'buttonsStyling', $status );
		}

		/**
		 * @param $background
		 *
		 * @return \SweetAlert2
		 */
		public function background( $background ) {
			return $this->json_data( 'background', $background );
		}

		/**
		 * @param $backdrop
		 *
		 * @return \SweetAlert2
		 */
		public function backdrop( $backdrop ) {
			return $this->json_data( 'backdrop', $backdrop );
		}

		/**
		 * @param bool $grow
		 *
		 * @return \SweetAlert2
		 */
		public function grow( $grow = false ) {
			return $this->json_data( 'grow', $grow );
		}

		/**
		 * @param string $key
		 * @param string $value
		 *
		 * @return $this
		 */
		public function extra( $key = '', $value = '' ) {
			if ( is_array( $key ) ) {
				$this->handle_bulk_data( $key );
			} else {
				$this->handle_bulk_data( array( $key => $value ) );
			}
			return $this;
		}

		/**
		 * @return $this
		 */
		public function clear_type() {
			$this->swal_type = '';
			return $this;
		}

		/**
		 * @return $this
		 */
		public function is_mixin() {
			$this->swal_type = 'mixin';
			return $this;
		}

		/**
		 * @param bool $is_toast
		 *
		 * @return $this
		 */
		public function toast( $is_toast = true ) {
			$this->is_mixin();
			if ( is_array( $is_toast ) ) {
				$this->json_data( 'toast', true );
				$this->handle_bulk_data( $is_toast );
				return $this;
			} elseif ( true === $is_toast ) {
				$this->json_data( 'toast', true );
			} else {
				$this->json_data( 'toast', false );
				$this->clear_type();
			}
			return $this;
		}

		/**
		 * @param string $target
		 *
		 * @return \SweetAlert2
		 */
		public function target( $target = 'body' ) {
			return $this->json_data( 'target', $target );
		}

		/**
		 * @param null $input
		 *
		 * @return \SweetAlert2
		 */
		public function input( $input = null ) {
			$this->json_data( 'input', $input );
			return $this;
		}

		/**
		 * @param $inputValue
		 *
		 * @return \SweetAlert2
		 */
		public function inputValue( $inputValue ) {
			return $this->json_data( 'inputValue', $inputValue );
		}

		/**
		 * @param $inputPlaceholder
		 *
		 * @return \SweetAlert2
		 */
		public function inputPlaceholder( $inputPlaceholder ) {
			return $this->json_data( 'inputPlaceholder', $inputPlaceholder );
		}

		/**
		 * @param $inputOptions
		 *
		 * @return \SweetAlert2
		 */
		public function inputOptions( $inputOptions ) {
			return $this->json_data( 'inputOptions', $inputOptions );
		}

		/**
		 * @param $inputClass
		 *
		 * @return \SweetAlert2
		 */
		public function inputClass( $inputClass ) {
			return $this->json_data( 'inputClass', $inputClass );
		}

		/**
		 * @param $inputAutoTrim
		 *
		 * @return \SweetAlert2
		 */
		public function inputAutoTrim( $inputAutoTrim ) {
			return $this->json_data( 'inputAutoTrim', $inputAutoTrim );
		}

		/**
		 * @param $inputValidator
		 *
		 * @return \SweetAlert2
		 */
		public function inputValidator( $inputValidator ) {
			return $this->json_data( 'inputValidator', $inputValidator );
		}

		/**
		 * @param $inputAttributes
		 *
		 * @return \SweetAlert2
		 */
		public function inputAttributes( $inputAttributes ) {
			return $this->json_data( 'inputAttributes', $inputAttributes );
		}

		/**
		 * @param $progressSteps
		 *
		 * @return \SweetAlert2
		 */
		public function progressSteps( $progressSteps ) {
			return $this->json_data( 'progressSteps', $progressSteps, true );
		}

		/**
		 * @param $currentProgressStep
		 *
		 * @return \SweetAlert2
		 */
		public function currentProgressStep( $currentProgressStep ) {
			return $this->json_data( 'currentProgressStep', $currentProgressStep );
		}

		/**
		 * @param $progressStepsDistance
		 *
		 * @return \SweetAlert2
		 */
		public function progressStepsDistance( $progressStepsDistance ) {
			return $this->json_data( 'progressStepsDistance', $progressStepsDistance );
		}

		/**
		 * @param $validationMesage
		 *
		 * @return \SweetAlert2
		 */
		public function validationMesage( $validationMesage ) {
			return $this->json_data( 'validationMesage', $validationMesage );
		}

		/**
		 * @param null $width
		 *
		 * @return \SweetAlert2
		 */
		public function width( $width = null ) {
			return $this->json_data( 'width', $width );
		}

		/**
		 * @param null $padding
		 *
		 * @return \SweetAlert2
		 */
		public function padding( $padding = null ) {
			return $this->json_data( 'padding', $padding );
		}

		/**
		 * @param $height_auto
		 *
		 * @return \SweetAlert2
		 */
		public function heightAuto( $height_auto ) {
			return $this->json_data( 'heightAuto', $height_auto );
		}

		/**
		 * @param $height_auto
		 *
		 * @return \SweetAlert2
		 */
		public function allowOutsideClick( $height_auto ) {
			return $this->json_data( 'allowOutsideClick', $height_auto );
		}

		/**
		 * @param $height_auto
		 *
		 * @return \SweetAlert2
		 */
		public function allowEscapeKey( $height_auto ) {
			return $this->json_data( 'allowEscapeKey', $height_auto );
		}

		/**
		 * @param $height_auto
		 *
		 * @return \SweetAlert2
		 */
		public function allowEnterKey( $height_auto ) {
			return $this->json_data( 'allowEnterKey', $height_auto );
		}

		/**
		 * @param $height_auto
		 *
		 * @return \SweetAlert2
		 */
		public function stopKeydownPropagation( $height_auto ) {
			return $this->json_data( 'stopKeydownPropagation', $height_auto );
		}

		/**
		 * @param $height_auto
		 *
		 * @return \SweetAlert2
		 */
		public function keydownListenerCapture( $height_auto ) {
			return $this->json_data( 'keydownListenerCapture', $height_auto );
		}

		/**
		 * @param $reverseButtons
		 *
		 * @return \SweetAlert2
		 */
		public function reverseButtons( $reverseButtons ) {
			return $this->json_data( 'reverseButtons', $reverseButtons );
		}

		/**
		 * @param $reverseButtons
		 *
		 * @return \SweetAlert2
		 */
		public function focusCancel( $reverseButtons ) {
			return $this->json_data( 'focusCancel', $reverseButtons );
		}

		/**
		 * @param bool $showCloseButton
		 *
		 * @return \SweetAlert2
		 */
		public function showCloseButton( $showCloseButton = true ) {
			return $this->json_data( 'showCloseButton', $showCloseButton );
		}

		/**
		 * @param $showLoaderOnConfirm
		 *
		 * @return \SweetAlert2
		 */
		public function showLoaderOnConfirm( $showLoaderOnConfirm ) {
			return $this->json_data( 'showLoaderOnConfirm', $showLoaderOnConfirm );
		}

		/**
		 * @param $onBeforeOpen
		 *
		 * @return \SweetAlert2
		 */
		public function onBeforeOpen( $onBeforeOpen ) {
			return $this->json_data( 'onBeforeOpen', 'function (){' . $onBeforeOpen . '}' );
		}

		/**
		 * @param $onClose
		 *
		 * @return \SweetAlert2
		 */
		public function onClose( $onClose ) {
			return $this->json_data( 'onClose', 'function (){' . $onClose . '}' );
		}

		/**
		 * @param $onOpen
		 *
		 * @return \SweetAlert2
		 */
		public function onOpen( $onOpen ) {
			return $this->json_data( 'onOpen', 'function (){' . $onOpen . '}' );
		}

		/**
		 * @param $onAfterClose
		 *
		 * @return \SweetAlert2
		 */
		public function onAfterClose( $onAfterClose ) {
			return $this->json_data( 'onAfterClose', 'function (){' . $onAfterClose . '}' );
		}

		/**
		 * @param $models
		 *
		 * @return $this
		 */
		public function queue( $models ) {
			$this->is_mixin();
			$models = ( is_array( $models ) ) ? $models : array( $models );
			$output = array();
			foreach ( $models as $model ) {
				if ( $model instanceof SweetAlert2 ) {
					$output[] = $model->to_array();
				} else {
					$output[] = $model;
				}
			}
			$this->then[] = '.queue(' . $this->json_encode_data( self::handle_js_function( $output ) ) . ')';
			return $this;
		}

		public function preConfirm( $preConfirm ) {
			return $this->json_data( 'preConfirm', 'function (){' . $preConfirm . '}' );
		}
	}
}

if ( ! function_exists( 'swal' ) ) {
	/**
	 * @param string $title
	 * @param string $content
	 * @param string $type
	 * @param array  $args
	 *
	 * @return \SweetAlert2
	 */
	function swal( $title = '', $content = '', $type = 'success', $args = array() ) {
		return new SweetAlert2( $title, $content, $type, $args );
	}
}

if ( ! function_exists( 'swal_success' ) ) {
	/**
	 * @param string $title
	 * @param string $content
	 * @param array  $args
	 *
	 * @return \SweetAlert2
	 */
	function swal_success( $title = '', $content = '', $args = array() ) {
		return swal( $title, $content, 'success', $args );
	}
}

if ( ! function_exists( 'swal_info' ) ) {
	/**
	 * @param string $title
	 * @param string $content
	 * @param array  $args
	 *
	 * @return \SweetAlert2
	 */
	function swal_info( $title = '', $content = '', $args = array() ) {
		return swal( $title, $content, 'info', $args );
	}
}

if ( ! function_exists( 'swal_question' ) ) {
	/**
	 * @param string $title
	 * @param string $content
	 * @param array  $args
	 *
	 * @return \SweetAlert2
	 */
	function swal_question( $title = '', $content = '', $args = array() ) {
		return swal( $title, $content, 'question', $args );
	}
}

if ( ! function_exists( 'swal_warning' ) ) {
	/**
	 * @param string $title
	 * @param string $content
	 * @param array  $args
	 *
	 * @return \SweetAlert2
	 */
	function swal_warning( $title = '', $content = '', $args = array() ) {
		return swal( $title, $content, 'warning', $args );
	}
}

if ( ! function_exists( 'swal_error' ) ) {
	/**
	 * @param string $title
	 * @param string $content
	 * @param array  $args
	 *
	 * @return \SweetAlert2
	 */
	function swal_error( $title = '', $content = '', $args = array() ) {
		return swal( $title, $content, 'error', $args );
	}
}
