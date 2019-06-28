<?php
/**
 *
 * @author Varun Sridharan <varunsridharan23@gmail.com>
 * @version 1.0
 * @since 1.0
 * @link https://github.com/varunsridharan/sweetalert2-php
 * @copyright 2019 Varun Sridharan
 * @license GPLV3 Or Greater (https://www.gnu.org/licenses/gpl-3.0.txt)
 */

if ( ! class_exists( 'SweetAlert2' ) ) {
	/**
	 * Class SweetAlert2
	 *
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 *
	 * @method \SweetAlert2 title( $title = null )
	 * @method \SweetAlert2 titleText( $titleText = null )
	 * @method \SweetAlert2 html( $html = null )
	 * @method \SweetAlert2 text( $text = null )
	 * @method \SweetAlert2 type( $type = null )
	 * @method \SweetAlert2 footer( $footer = null )
	 * @method \SweetAlert2 backdrop( $backdrop = true )
	 * @method \SweetAlert2 toast( $toast = false )
	 * @method \SweetAlert2 target( $target = 'body' )
	 * @method \SweetAlert2 input( $input = null )
	 * @method \SweetAlert2 width( $width = null )
	 * @method \SweetAlert2 padding( $padding = null )
	 * @method \SweetAlert2 background( $background = null )
	 * @method \SweetAlert2 position( $position = 'center' )
	 * @method \SweetAlert2 grow( $grow = false )
	 * @method \SweetAlert2 customClass( $customClass = array() )
	 * @method \SweetAlert2 timer( $timer = null )
	 * @method \SweetAlert2 animation( $animation = true )
	 * @method \SweetAlert2 heightAuto( $heightAuto = true )
	 * @method \SweetAlert2 allowOutsideClick( $allowOutsideClick = true )
	 * @method \SweetAlert2 allowEscapeKey( $allowEscapeKey = true )
	 * @method \SweetAlert2 allowEnterKey( $allowEnterKey = true )
	 * @method \SweetAlert2 stopKeydownPropagation( $stopKeydownPropagation = true )
	 * @method \SweetAlert2 keydownListenerCapture( $keydownListenerCapture = true )
	 * @method \SweetAlert2 showConfirmButton( $showConfirmButton = true )
	 * @method \SweetAlert2 showCancelButton( $showCancelButton = false )
	 * @method \SweetAlert2 confirmButtonText( $confirmButtonText = 'OK' )
	 * @method \SweetAlert2 cancelButtonText( $cancelButtonText = 'Cancel' )
	 * @method \SweetAlert2 confirmButtonColor( $confirmButtonColor = null )
	 * @method \SweetAlert2 cancelButtonColor( $cancelButtonColor = null )
	 * @method \SweetAlert2 confirmButtonAriaLabel( $confirmButtonAriaLabel = '' )
	 * @method \SweetAlert2 cancelButtonAriaLabel( $cancelButtonAriaLabel = '' )
	 * @method \SweetAlert2 buttonsStyling( $buttonsStyling = true )
	 * @method \SweetAlert2 reverseButtons( $reverseButtons = false )
	 * @method \SweetAlert2 focusConfirm( $focusConfirm = true )
	 * @method \SweetAlert2 focusCancel( $focusCancel = false )
	 * @method \SweetAlert2 showCloseButton( $showCloseButton = false )
	 * @method \SweetAlert2 closeButtonAriaLabel( $closeButtonAriaLabel = 'Close this dialog' )
	 * @method \SweetAlert2 showLoaderOnConfirm( $showLoaderOnConfirm = false )
	 * @method \SweetAlert2 scrollbarPadding( $scrollbarPadding = true )
	 * @method \SweetAlert2 preConfirm( $preConfirm = null )
	 * @method \SweetAlert2 imageUrl( $imageUrl = null )
	 * @method \SweetAlert2 imageWidth( $imageWidth = null )
	 * @method \SweetAlert2 imageHeight( $imageHeight = null )
	 * @method \SweetAlert2 imageAlt( $imageAlt = '' )
	 * @method \SweetAlert2 inputPlaceholder( $inputPlaceholder = '' )
	 * @method \SweetAlert2 inputValue( $inputValue = '' )
	 * @method \SweetAlert2 inputOptions( $inputOptions = array() )
	 * @method \SweetAlert2 inputAutoTrim( $inputAutoTrim = true )
	 * @method \SweetAlert2 inputAttributes( $inputAttributes = array() )
	 * @method \SweetAlert2 inputValidator( $inputValidator = null )
	 * @method \SweetAlert2 validationMesage( $validationMesage = null )
	 * @method \SweetAlert2 progressSteps( $progressSteps = array() )
	 * @method \SweetAlert2 currentProgressStep( $currentProgressStep = null )
	 * @method \SweetAlert2 progressStepsDistance( $progressStepsDistance = '40px' )
	 * @method \SweetAlert2 onBeforeOpen( $onBeforeOpen = null )
	 * @method \SweetAlert2 onOpen( $onOpen = null )
	 * @method \SweetAlert2 onClose( $onClose = null )
	 * @method \SweetAlert2 onAfterClose( $onAfterClose = null )
	 * Custom Methods
	 * @method \SweetAlert2 warning( $warning = true )
	 * @method \SweetAlert2 success( $success = true )
	 * @method \SweetAlert2 error( $error = true )
	 * @method \SweetAlert2 question( $question = true )
	 * @method \SweetAlert2 info( $info = true )
	 */
	class SweetAlert2 implements \JsonSerializable {
		/**
		 * Stores Thens.
		 *
		 * @var array
		 * @access
		 */
		private $then = array();

		/**
		 * Stores Current Instance Config.
		 *
		 * @var array
		 * @access
		 */
		private $config = array();

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
		 * Stores Encoded Values.
		 *
		 * @var bool
		 * @access
		 */
		private $encoded = false;

		/**
		 * SweetAlert2 constructor.
		 *
		 * @param string $title
		 * @param string $text
		 * @param string $type
		 */
		public function __construct( $title = '', $text = '', $type = 'success' ) {
			$this->data( 'title', $title );
			$this->data( 'text', $text );
			$this->data( 'type', $type );
		}

		/**
		 * @param string $title
		 * @param string $text
		 * @param string $type
		 *
		 * @return \SweetAlert2
		 */
		public function swal( $title = '', $text = '', $type = 'success' ) {
			return new self( $title, $text, $type );
		}

		/**
		 * @return mixed|string
		 */
		public function jsonSerialize() {
			return $this->render();
		}

		/**
		 * @return mixed
		 */
		public function __toString() {
			return $this->render();
		}

		/**
		 * Returns Encoded Values.
		 *
		 * @return false|string
		 */
		public function to_json() {
			if ( false === $this->encoded ) {
				$this->encoded = json_encode( $this->config );
			}
			return $this->encoded;
		}

		/**
		 * Stores Value.
		 *
		 * @param string $key
		 * @param mixed  $value
		 *
		 * @return $this
		 */
		protected function data( $key = '', $value = '' ) {
			$this->config[ $key ] = $value;
			return $this;
		}

		/**
		 * Returns Javascript Variable Name.
		 *
		 * @return string
		 */
		protected function var_name() {
			return 'swal2_' . md5( $this->to_json() . '-' . microtime() );
		}

		/**
		 * Renders Output.
		 *
		 * @return string
		 */
		public function render() {
			$output = $this->before . ' var ' . $this->var_name() . ' = Swal.fire(' . $this->to_json() . ') ';

			if ( ! empty( $this->then ) ) {
				foreach ( $this->then as $data ) {
					$data   = ( $data instanceof SweetAlert2 ) ? $data->render() : $data;
					$output .= '.then((result) => {' . $data . '})';
				}
			}

			return $output . ';' . $this->after;
		}

		/**
		 * @param $name
		 * @param $arguments
		 *
		 * @return $this
		 */
		public function __call( $name, $arguments ) {
			return $this->data( $name, $arguments[0] );
		}

		/**
		 * @param $name
		 *
		 * @return mixed
		 */
		public function __get( $name ) {
			return ( isset( $this->config[ $name ] ) ) ? $this->config[ $name ] : null;
		}

		/**
		 * @param $then
		 *
		 * @return $this
		 */
		public function then( $then ) {
			$this->then[] = $then;
			return $this;
		}

		/**
		 * @param $before
		 *
		 * @return $this
		 */
		public function before( $before ) {
			$this->before = $before;
			return $this;
		}

		/**
		 * @param $after
		 *
		 * @return $this
		 */
		public function after( $after ) {
			$this->after = $after;
			return $this;
		}

		/**
		 * @param bool $url
		 * @param null $height
		 * @param null $width
		 * @param null $alt
		 *
		 * @return $this
		 */
		public function image( $url = false, $height = null, $width = null, $alt = null ) {
			if ( false === $url && null === $height && null === $alt && null === $width ) {
				$url    = $this->title;
				$height = $this->text;
				$alt    = $this->type;
				$this->title( false );
				$this->text( false );
				$this->type( false );
			}
			$this->data( 'imageUrl', $url );
			$this->data( 'imageHeight', $height );
			$this->data( 'imageWidth', $width );
			$this->data( 'imageAlt', $alt );
			return $this;
		}

		/**
		 * @param bool $text
		 * @param null $color
		 * @param null $aria_label
		 *
		 * @return $this
		 */
		public function cancelButton( $text = false, $color = null, $aria_label = null ) {
			$is_show = true;
			if ( false === $text ) {
				$text       = null;
				$color      = null;
				$aria_label = null;
				$is_show    = false;
			}
			$this->data( 'showCancelButton', $is_show );
			$this->data( 'cancelButtonText', $text );
			$this->data( 'cancelButtonColor', $color );
			$this->data( 'cancelButtonAriaLabel', $aria_label );
			return $this;
		}

		/**
		 * @param bool $text
		 * @param null $color
		 * @param null $aria_label
		 *
		 * @return $this
		 */
		public function confirmButton( $text = false, $color = null, $aria_label = null ) {
			$is_show = true;
			if ( false === $text ) {
				$text       = null;
				$color      = null;
				$aria_label = null;
				$is_show    = false;
			}
			$this->data( 'confirmButtonText', $text );
			$this->data( 'showConfirmButton', $is_show );
			$this->data( 'confirmButtonColor', $color );
			$this->data( 'confirmButtonAriaLabel', $aria_label );
			return $this;
		}
	}

	/**
	 * @param string $title
	 * @param string $content
	 * @param string $type
	 *
	 * @return \SweetAlert2
	 */
	function swal2( $title = '', $content = '', $type = 'success' ) {
		return new \SweetAlert2( $title, $content, $type );
	}

	/**
	 * @param string $title
	 * @param string $content
	 *
	 * @return \SweetAlert2
	 */
	function swal2_success( $title = '', $content = '' ) {
		return swal2( $title, $content, 'success' );
	}

	/**
	 * @param string $title
	 * @param string $content
	 *
	 * @return \SweetAlert2
	 */
	function swal2_info( $title = '', $content = '' ) {
		return swal2( $title, $content, 'info' );
	}

	/**
	 * @param string $title
	 * @param string $content
	 *
	 * @return \SweetAlert2
	 */
	function swal2_question( $title = '', $content = '' ) {
		return swal2( $title, $content, 'question' );
	}

	/**
	 * @param string $title
	 * @param string $content
	 *
	 * @return \SweetAlert2
	 */
	function swal2_warning( $title = '', $content = '' ) {
		return swal2( $title, $content, 'warning' );
	}

	/**
	 * @param string $title
	 * @param string $content
	 *
	 * @return \SweetAlert2
	 */
	function swal2_error( $title = '', $content = '' ) {
		return swal2( $title, $content, 'error' );
	}
}
