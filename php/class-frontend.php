<?php

// dependencies
require_once( FVP_DIR . 'php/class-main.php' );

/**
 * Class containing frontend functionality.
 *
 * Enqueue scripts/styles, replace featured images by featured videos or
 * insert the ajax request handlers, add 'has-post-video' class and
 * register the [featured-video-plus] shortcode.
 *
 * @since 1.0.0
 */
class FVP_Frontend extends Featured_Video_Plus {

	/**
	 * Creates a new instace of this class, saves the featured_video_instance.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

		add_filter( 'post_thumbnail_html', array( $this, 'filter_post_thumbnail' ), 99, 5 );
		add_filter( 'post_class', array( $this, 'has_post_video_class' ) );

		add_shortcode( 'featured-video-plus', array( $this, 'shortcode' ) );
	}


	/**
	 * Enqueue all scripts and styles needed when viewing the frontend.
	 *
	 * @since 1.0.0
	 */
	public function enqueue() {
		$min = SCRIPT_DEBUG ? '' : '.min';

		$options = get_option( 'fvp-settings' );

		wp_register_script(
			'jquery.fitvids',
			FVP_URL . "js/jquery.fitvids$min.js",
			array( 'jquery' ),
			'1.1',
			false
		);

		wp_register_script(
			'jquery.domwindow',
			FVP_URL . "js/jquery.domwindow$min.js",
			array( 'jquery' ),
			FVP_VERSION
		);

		// Basic dependencies. Is extended in the following.
		$deps = array( 'jquery' );

		// Is responsive video functionality required? Only when width is set to
		// 'auto' and display mode is not set to overlay.
		if (
			! empty($options['sizing']['responsive']) &&
			$options['sizing']['responsive']
		) {
			$deps[] = 'jquery.fitvids';
		}

		// Is modal functionality required?
		if ( 'overlay' === $options['mode'] ) {
			$deps[] = 'jquery.domwindow';
		}

		// general frontend script
		wp_enqueue_script(
			'fvp-frontend',
			FVP_URL . "js/frontend$min.js",
			$deps,
			FVP_VERSION
		);

		$mode = ! empty( $options['mode'] ) ? $options['mode'] : null;

		// some context for JS
		wp_localize_script( 'fvp-frontend', 'fvpdata', array(
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'featured-video-plus-nonce' ),
			'fitvids'  => ! empty( $options['sizing']['responsive'] ) && $options['sizing']['responsive'],
			'dynamic'  => 'dynamic' === $options['mode'],
			'overlay'  => 'overlay' === $options['mode'],
			'opacity'  => 0.75,
			'loadicon' => 'overlay' === $options['mode'] ? FVP_URL . 'img/loadicon_w.gif' : FVP_URL . 'img/loadicon_b.gif',
			'playicon' => FVP_URL . 'img/playicon.png',
		));

		// general frontend styles
		wp_enqueue_style(
			'fvp-frontend',
			FVP_URL . 'styles/frontend.css',
			array(),
			FVP_VERSION
		);
	}


	/**
	 * Display featured videos in place of featured images if a featured video is available and only if so desired by user.
	 *
	 * @see http://wordpress.stackexchange.com/a/41858
	 * @since 1.0.0
	 *
	 * @param string $html featured image html, ready to echo
	 * @param int $post_id id of target post
	 * @param int $post_thumbnail_id id of featured image
	 * @param string|array $size desired size of featured image / video
	 * @param array $attr
	 */
	public function filter_post_thumbnail(
		$html,
		$post_id,
		$post_thumbnail_id,
		$size,
		$attr
	) {
		$options = get_option( 'fvp-settings' );
		$mode = ! empty( $options['mode'] ) ? $options['mode'] : null;
		$size = $this->get_size();

		if (
			( isset( $options['issingle'] ) && $options['issingle'] && ! is_single() ) ||
			( 'manual' === $options['mode'] || ! has_post_video( $post_id ) ) ) {
			return $html;

		} elseif ( 'dynamic' === $options['mode'] && ! is_single() ) {
			return sprintf(
				'<a href="#fvp-%1$s" id="fvp-%1$s" class="fvp-dynamic" data-id="%1$s">%2$s</a>',
				$post_id,
				$html
			);

		} elseif ( 'overlay' === $options['mode'] && ! is_single() ) {
			return sprintf(
				'<a href="#fvp-%1$s" class="fvp-overlay" data-id="%1$s">%2$s</a>' .
				'<div id="fvp-%1$s" style="display: none;"></div>',
				$post_id,
				$html
			);
		}

		return get_the_post_video( $post_id, $size );
	}


	/**
	 * Add a 'has-post-video' class to posts if appropriate.
	 *
	 * @since 2.0.0
	 *
	 * @param  {array} $classes Existing classes
	 * @return {array}          Updated classes
	 */
	public function has_post_video_class( $classes ) {
		global $post;

		if ( has_post_video( $post->ID ) ) {
			$classes[] = 'has-post-video';
		}
		return $classes;
	}


	/**
	 * Shortcode for usage in post or page entries. Echos the post's featured video.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts can contain the width and/or height how the featured video should be displayed in px, optional
	 */
	public function shortcode($atts){
		$w = isset($atts['width'])  ? $atts['width'] : '';
		$h = isset($atts['height']) ? $atts['height'] : '';

		if ( has_post_video() ) {
			return get_the_post_video( null, array( $w, $h ) );
		}
	}
}
