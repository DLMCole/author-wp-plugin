<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the Author's own public profile page: falls back to a bundled
 * template when the active theme doesn't provide one, and outputs that
 * author's Person schema in wp_head (independent of the per-content
 * enable/disable settings, since this page *is* the author).
 */
class ABX_Single_Template {

	public function __construct() {
		add_filter( 'template_include', array( $this, 'template_include' ) );
		add_action( 'wp_head', array( $this, 'output_schema' ), 5 );
	}

	public function template_include( $template ) {
		if ( ! is_singular( ABX_AUTHOR_CPT ) ) {
			return $template;
		}

		$theme_template = locate_template( array( 'single-' . ABX_AUTHOR_CPT . '.php', 'authorship-box/single-author.php' ) );
		if ( $theme_template ) {
			return $theme_template;
		}

		return ABX_PLUGIN_DIR . 'templates/author-profile.php';
	}

	public function output_schema() {
		if ( ! is_singular( ABX_AUTHOR_CPT ) ) {
			return;
		}

		$settings = ABX_Resolver::get_settings();
		if ( empty( $settings['output_schema'] ) ) {
			return;
		}

		$schema = ABX_Schema::build_person_schema( get_queried_object_id() );
		if ( $schema ) {
			ABX_Schema::render_json_ld( $schema );
		}
	}

	/**
	 * Content this author is assigned to, across all supported post types,
	 * limited to items currently resolving as "enabled".
	 */
	public static function get_authored_content( $author_id, $limit = 20 ) {
		$post_types = array_keys( ABX_Settings::get_supported_post_types() );

		$query = new WP_Query(
			array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'meta_query'     => array(
					array(
						'key'   => ABX_Resolver::AUTHOR_META_KEY,
						'value' => $author_id,
					),
				),
			)
		);

		return array_values(
			array_filter(
				$query->posts,
				function ( $post ) {
					return ABX_Resolver::should_display( $post->ID );
				}
			)
		);
	}
}
