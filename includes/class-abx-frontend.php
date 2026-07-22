<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end output: injects the JSON-LD Person schema into wp_head and
 * appends/prepends the visible author box to singular content. Also
 * exposes a shortcode and a template tag for theme developers who want
 * to place the box manually.
 */
class ABX_Frontend {

	public function __construct() {
		add_action( 'wp_head', array( $this, 'output_schema' ), 5 );
		add_filter( 'the_content', array( $this, 'inject_box' ) );
		add_shortcode( 'authorship_box', array( $this, 'shortcode' ) );
	}

	public function output_schema() {
		if ( ! is_singular() ) {
			return;
		}

		$settings = ABX_Resolver::get_settings();
		if ( empty( $settings['output_schema'] ) ) {
			return;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id || ! ABX_Resolver::should_display( $post_id ) ) {
			return;
		}

		$author_id = ABX_Resolver::get_assigned_author_id( $post_id );
		$schema    = ABX_Schema::build_person_schema( $author_id );

		if ( $schema ) {
			ABX_Schema::render_json_ld( $schema );
		}
	}

	public function inject_box( $content ) {
		if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$box = self::get_box_html( get_the_ID() );
		if ( ! $box ) {
			return $content;
		}

		$settings = ABX_Resolver::get_settings();
		return 'before_content' === $settings['box_position'] ? $box . $content : $content . $box;
	}

	public function shortcode( $atts ) {
		$atts = shortcode_atts( array( 'post_id' => get_the_ID() ), $atts );
		return self::get_box_html( (int) $atts['post_id'], true );
	}

	/**
	 * Returns the rendered author box HTML for a given post, or an empty
	 * string if nothing should show. $force bypasses the enabled/disabled
	 * check (used by the shortcode, so authors can be shown deliberately
	 * even on content types that are off by default).
	 */
	public static function get_box_html( $post_id, $force = false ) {
		if ( ! $post_id ) {
			return '';
		}

		if ( ! $force && ! ABX_Resolver::should_display( $post_id ) ) {
			return '';
		}

		$author_id = ABX_Resolver::get_assigned_author_id( $post_id );
		if ( ! $author_id ) {
			return '';
		}

		return self::render_author_box( $author_id );
	}

	public static function render_author_box( $author_id ) {
		$author = get_post( $author_id );
		if ( ! $author ) {
			return '';
		}

		$short_bio = get_post_meta( $author_id, '_abx_short_bio', true );

		$data = array(
			'author_id'  => $author_id,
			'name'       => get_the_title( $author_id ),
			'job_title'  => get_post_meta( $author_id, '_abx_job_title', true ),
			'org_name'   => get_post_meta( $author_id, '_abx_org_name', true ),
			'permalink'  => get_permalink( $author_id ),
			'image_url'  => has_post_thumbnail( $author_id ) ? get_the_post_thumbnail_url( $author_id, 'thumbnail' ) : '',
			'bio'        => $short_bio ? $short_bio : get_the_excerpt( $author_id ),
			'sameas'     => array_values( array_filter( array_map( 'trim', preg_split( '/\r?\n/', (string) get_post_meta( $author_id, '_abx_sameas', true ) ) ) ) ),
			'website'    => get_post_meta( $author_id, '_abx_url', true ),
		);

		ob_start();
		$template = locate_template( 'authorship-box/author-box.php' );
		if ( ! $template ) {
			$template = ABX_PLUGIN_DIR . 'templates/author-box.php';
		}
		include $template;
		return ob_get_clean();
	}
}

/**
 * Template tag for theme developers.
 */
function abx_the_author_box( $post_id = null ) {
	echo ABX_Frontend::get_box_html( $post_id ? $post_id : get_the_ID(), true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
