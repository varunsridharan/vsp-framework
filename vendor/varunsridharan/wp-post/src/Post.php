<?php
/**
 * Simple WP Post Class With Advanced Options.
 *
 * @author    Varun Sridharan <varunsridharan23@gmail.com>
 * @copyright 2018 Varun Sridharan
 * @license   GPLV3 Or Greater
 */

namespace Varunsridharan\WordPress;

if ( ! class_exists( '\Varunsridharan\WordPress\Post' ) ) {
	/**
	 * Class Post
	 *
	 * @package Varunsridharan\WordPress
	 * @author Varun Sridharan <varunsridharan23@gmail.com>
	 * @since 1.0
	 */
	class Post {
		/**
		 * Post Version
		 *
		 * @var string
		 */
		public $version = '1.0';

		/**
		 * Post id
		 *
		 * @var null
		 */
		protected $id = null;

		/**
		 * Post Object.
		 *
		 * @var null|\WP_Post
		 */
		protected $post = null;

		/**
		 * Returns All Taxonomies.
		 *
		 * @var array
		 */
		protected $taxes = array();

		/**
		 * All Post Data.
		 *
		 * @var array
		 */
		protected $data = array();


		/**
		 * VS_WP_Post constructor.
		 *
		 * @param int|string $post
		 */
		public function __construct( $post = '' ) {
			$this->set_post( \get_post( $post ) );
		}

		/**
		 * Sets Post Data.
		 *
		 * @param $post
		 */
		protected function set_post( $post ) {
			$this->post = $post;
			$this->id   = ( isset( $post->ID ) ) ? $post->ID : false;

			if ( ! empty( $this->taxonomies() ) ) {
				foreach ( $this->taxonomies() as $taxonomy ) {
					$this->{$taxonomy} = $this->get_terms( $taxonomy, array( 'fields' => 'all' ) );
				}
			}
		}

		/**
		 * @param $name
		 *
		 * @return bool|mixed
		 */
		public function __get( $name ) {
			if ( isset( $this->data[ $name ] ) ) {
				return $this->data[ $name ];
			}
			return false;
		}

		/**
		 * Sets A Value to $data array
		 *
		 * @param $name
		 * @param $value
		 *
		 * @uses \VS_WP_Post::$data
		 *
		 */
		public function __set( $name, $value ) {
			$this->data[ $name ] = $value;
		}

		/**
		 * Returns Post ID.
		 *
		 * @return null
		 */
		public function id() {
			return $this->id;
		}

		/**
		 * Returns Post Object.
		 *
		 * @return null
		 */
		public function post() {
			return $this->post;
		}

		/**
		 * Checks If Post Has Featured Image.
		 *
		 * @return bool
		 */
		public function has_featured_image() {
			return ( \get_the_post_thumbnail_url( $this->id() ) !== false ) ? true : false;
		}

		/**
		 * Returns Featured Image ID.
		 *
		 * @return bool|int|mixed|string
		 */
		public function featured_image_id() {
			if ( isset( $this->featured_image_id ) ) {
				return $this->featured_image_id;
			}
			$this->featured_image_id = \get_post_thumbnail_id( $this->post );
			return $this->featured_image_id;
		}

		/**
		 * Returns Featured Image URL.
		 *
		 * @param string $size
		 * @param string $default
		 *
		 * @return false|string
		 */
		public function featured_image_url( $size = 'thumbnail', $default = '' ) {
			$featured_image = \get_the_post_thumbnail_url( $this->id(), $size );
			return ( false !== $featured_image ) ? $featured_image : $default;
		}

		/**
		 * Returns Post Permalink.
		 *
		 * @return false|string
		 */
		public function permalink() {
			return \get_permalink( $this->id() );
		}

		/**
		 * Returns Post Author.
		 *
		 * @param bool|boolean $only_id
		 *
		 * @return bool
		 */
		public function author( $only_id = true ) {
			if ( true === $only_id ) {
				return $this->post->post_author;
			}
			return false;
		}

		/**
		 * Checks if post has content.
		 *
		 * @return bool
		 */
		public function has_content() {
			return ( empty( $this->post->post_content ) ) ? false : true;
		}

		/**
		 * Checks if post has post_excerpt.
		 *
		 * @return bool
		 */
		public function has_excerpt() {
			return ( empty( $this->post->has_excerpt ) ) ? false : true;
		}

		/**
		 * Returns Post Title.
		 *
		 * @return mixed
		 */
		public function title() {
			return $this->post->post_title;
		}

		/**
		 * Returns Post Slug.
		 *
		 * @return string
		 */
		public function slug() {
			return $this->post->post_name;
		}

		/**
		 * Returns Post Parent.
		 *
		 * @param boolean $only_id
		 *
		 * @return int
		 */
		public function parent( $only_id = true ) {
			if ( $only_id ) {
				return $this->post->post_parent;
			}
			$class = get_called_class();
			return new $class( $this->post->post_parent );
		}

		/**
		 * Returns Current Post Type.
		 *
		 * @return string
		 */
		public function type() {
			return $this->post->post_type;
		}

		/**
		 * Returns Page Template.
		 *
		 * @return string
		 */
		public function page_template() {
			return $this->post->page_template;
		}

		/**
		 * Returns Post post_excerpt.
		 *
		 * @return mixed
		 */
		public function excerpt() {
			return $this->post->post_excerpt;
		}

		/**
		 * Returns Post Content.
		 *
		 * @return string
		 */
		public function content() {
			return $this->post->post_content;
		}

		/**
		 * Returns Post Status.
		 *
		 * @return mixed
		 */
		public function status() {
			return $this->post->post_status;
		}

		/**
		 * Checks if given status is post status.
		 *
		 * @param string $status
		 *
		 * @return bool
		 */
		public function is_status( $status = 'publish' ) {
			return ( $status === $this->post->post_status ) ? true : false;
		}

		/*====================================================================================
		 * Post Meta Handler.																 *
		 ====================================================================================*/

		/**
		 * Gets Post Meta For the given meta key.
		 *
		 * @param string $meta_key
		 * @param string $default
		 *
		 * @return mixed
		 */
		public function get_meta( $meta_key = '', $default = '' ) {
			return \get_post_meta( $this->id(), $meta_key, $default );
		}

		/**
		 * Updates Post Meta.
		 *
		 * @param string $meta_key
		 * @param string $values
		 * @param string $prev_values
		 *
		 * @return bool|int
		 */
		public function update_meta( $meta_key = '', $values = '', $prev_values = '' ) {
			return \update_post_meta( $this->id(), $meta_key, $values, $prev_values );
		}

		/**
		 * Adds Post Meta.
		 *
		 * @param string       $meta_key
		 * @param string       $values
		 * @param boolean|bool $unique
		 *
		 * @return bool|int
		 */
		public function add_meta( $meta_key = '', $values = '', $unique = false ) {
			return \add_post_meta( $this->id(), $meta_key, $values, $unique );
		}

		/**
		 * Deletes A Post Meta.
		 *
		 * @param string $meta_key
		 * @param mixed  $values
		 *
		 * @return bool
		 */
		public function delete_meta( $meta_key = '', $values = '' ) {
			return \delete_post_meta( $this->id(), $meta_key, $values );
		}

		/*====================================================================================
		 * Taxonomy Handler.																 *
		 ====================================================================================*/

		/**
		 * Returns All Post Taxonomies.
		 *
		 * @return array
		 */
		public function taxonomies() {
			if ( ! empty( $this->taxes ) ) {
				return $this->taxes;
			}
			$taxes       = \get_post_taxonomies( $this->post );
			$this->taxes = $taxes;
			return $this->taxes;
		}

		/**
		 * Returns All Terms From Post.
		 *
		 * @param string $taxonomy
		 * @param array  $args
		 *
		 * @return array|\WP_Error
		 */
		public function get_terms( $taxonomy = '', $args = array() ) {
			if ( isset( $this->{$taxonomy} ) && empty( $args ) ) {
				return $this->{$taxonomy};
			} else {
				$name = $taxonomy . '_' . md5( json_encode( $args ) );
				if ( isset( $this->{$name} ) ) {
					return $this->{$name};
				}
				$data          = \wp_get_post_terms( $this->id(), $taxonomy, $args );
				$this->{$name} = $data;
				return $data;
			}
		}

		/**
		 * Sets Given Terms With Tax To The Current Post.
		 *
		 * @param array  $terms
		 * @param string $taxonomy
		 * @param bool   $append
		 *
		 * @return array|false|\WP_Error
		 */
		public function set_terms( $terms = array(), $taxonomy = '', $append = false ) {
			return \wp_set_post_terms( $this->id(), $terms, $taxonomy, $append );
		}


		/*====================================================================================
		 * Class Helpers.
		 ====================================================================================*/

		/**
		 * Checks if given key is in array
		 *
		 * @param string $key
		 * @param string $data
		 * @param bool   $default
		 *
		 * @return bool|string
		 * @uses \in_array()
		 * @uses \VS_WP_Post
		 *
		 * @example in('somekey','post_category','no') post_category will be taken from post data.
		 * @example in('somekey',array('somekey' => 'somekey'), will be used the given array.
		 *
		 */
		public function in( $key = '', $data = '', $default = false ) {
			if ( is_array( $data ) ) {
				return ( in_array( $key, $data ) ) ? $key : $default;
			}
			if ( isset( $this->{$data} ) ) {
				return ( in_array( $key, $this->{$data} ) ) ? $key : $default;
			}
			return $default;
		}
	}
}