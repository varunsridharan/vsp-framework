<?php
/**
 * Name: WP Review Me
 * Version:1.1
 * Created by PhpStorm.
 * User: varun
 * Date: 02-03-2018
 * Time: 03:15 PM
 *
 * @copyright GPLV3
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 */

if ( ! defined( "WPINC" ) ) {
	die;
}

if ( class_exists( "VS_WP_Review_Me" ) ) {
	return;
}

/**
 * Class VS_WP_Review_Me
 */
class VS_WP_Review_Me {
	/**
	 * Library Version
	 *
	 * @var string
	 */
	public    $version = '1.0';
	public    $linkid;
	protected $key;

	/**
	 * VS_WP_Review_Me constructor.
	 *
	 * @param array $options
	 */
	public function __construct( $options = array() ) {
		if ( is_admin() || defined( "DOING_AJAX" ) ) {
			$this->op      = wp_parse_args( $options, $this->get_defaults() );
			$this->key     = 'vswprm_' . substr( md5( $this->op['slug'] ), 0, 20 );
			$this->link_id = 'vswprm-review-link-' . $this->key;

			if ( get_option( $this->key . 'isdone', false ) === false ) {
				$this->maybe_prompt();
			}

			add_action( 'wp_ajax_vswprm_clicked_review', array( $this, 'dismiss_notice' ) );
		}
	}

	/**
	 * returns defaults args
	 *
	 * @return array
	 */
	public function get_defaults() {
		return array(
			'days_after'      => 10,
			'notice_callback' => false,
			'type'            => '',
			'slug'            => '',
			'rating'          => 5,
			'message'         => sprintf( esc_html__( 'Hey! It&#039;s been a little while that you&#039;ve been using this product. You might not realize it, but user reviews are such a great help to us. We would be so grateful if you could take a minute to leave a review on WordPress.org. Many thanks in advance :)', 'wp-review-me' ) ),
			'link_label'      => esc_html__( 'Click here to leave your review', 'wp-review-me' ),
			'review_link'     => false,
			'item_id'         => false,
			'site'            => 'wordpress',
		);
	}

	/**
	 * Checks And Shows Review Message
	 */
	protected function maybe_prompt() {
		if ( ! $this->is_time() ) {
			return;
		}

		add_action( 'admin_footer', array( $this, 'script' ) );
		if ( $this->op['notice_callback'] !== false ) {
			call_user_func_array( $this->op['notice_callback'], array( &$this ) );
		} else {
			add_action( 'admin_notices', array( $this, 'add_notice' ) );
		}
	}

	/**
	 * Check if it is time to ask for a review
	 *
	 * @since 1.0
	 * @return bool
	 */
	protected function is_time() {
		$installed = get_option( $this->key, false );

		if ( false === $installed || $installed === 0 ) {
			$this->setup_date();
			$installed = time();
		}

		if ( $installed + ( $this->op['days_after'] * 86400 ) > time() ) {
			return false;
		}
		return true;
	}

	/**
	 * Save the current date as the installation date
	 *
	 * @since 1.0
	 * @return void
	 */
	protected function setup_date() {
		update_option( $this->key, time() );
	}

	/**
	 * Display Admin Notice
	 */
	public function add_notice() {
		echo '<div class="updated success is-dismissible"><p>' . $this->get_message() . '</p></div>';
	}

	/**
	 * Get the review prompt message
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_message() {
		$message = $this->op['message'];
		$link    = $this->get_review_link_tag();
		$message = $message . ' ' . $link;
		return wp_kses_post( $message );
	}

	/**
	 * Get the complete link tag
	 *
	 * @since 1.0
	 * @return string
	 */
	protected function get_review_link_tag() {
		$link  = $this->get_review_link();
		$label = $this->op['link_label'];
		return "<a href='$link' target='_blank' id='$this->link_id'>$label</a>";
	}

	/**
	 * Get the review link
	 *
	 * @since 1.0
	 * @return string
	 */
	protected function get_review_link() {
		if ( $this->op['review_link'] === false ) {
			$link = '';
			if ( $this->op['site'] === 'wordpress' ) {
				$link = 'https://wordpress.org/support/';

				switch ( $this->op['type'] ) {
					case 'theme':
						$link .= 'theme/';
						break;
					case 'plugin':
						$link .= 'plugin/';
						break;
				}

				$link .= $this->op['type'] . '/reviews';
				$link = add_query_arg( 'rate', $this->op['rating'], $link );
				$link = esc_url( $link . '#new-post' );
			} elseif ( $this->op['site'] === 'codecanyon' ) {
				$link = 'https://codecanyon.net/item/x/reviews/';
				$link .= $this->op['item_id'] . '#rating-' . $this->op['item_id'];
			} elseif ( $this->op['site'] === 'themeforest' ) {
				$link = 'https://themeforest.net/item/x/reviews/';
				$link .= $this->op['item_id'] . '#rating-' . $this->op['item_id'];
			}

			return $link;
		}


		return $this->op['review_link'];
	}

	/**
	 * Echo the JS script in the admin footer
	 *
	 * @since 1.0
	 * @return void
	 */
	public function script() { ?>

        <script>
            jQuery(document).ready(function ($) {
                $('#<?php echo $this->link_id; ?>').on('click', wrmDismiss);

                function wrmDismiss() {
                    var data = {
                        action: 'vswprm_clicked_review',
                        id: '<?php echo $this->link_id; ?>'
                    };
                    jQuery.ajax({
                        type: 'POST',
                        url: ajaxurl,
                        data: data,
                    });
                }
            });
        </script>

	<?php }

	/**
	 * Dismiss the notice when the review link is clicked
	 *
	 * @since 1.0
	 * @return void
	 */
	public function dismiss_notice() {
		if ( empty( $_POST ) ) {
			die();
		}
		if ( ! isset( $_POST['id'] ) ) {
			die();
		}
		$id = sanitize_text_field( $_POST['id'] );
		if ( $id !== $this->link_id ) {
			die();
		}

		update_option( $this->key . 'isdone', 'yes' );
		die();
	}
}