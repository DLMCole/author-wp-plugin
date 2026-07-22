<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Answers "should this post show the author box / schema?" by combining
 * the global per-post-type setting with a per-post override. Kept as
 * static helpers so both the admin metabox and frontend can share it.
 */
class ABX_Resolver {

	const OVERRIDE_META_KEY = '_abx_override_mode';
	const AUTHOR_META_KEY   = '_abx_author_id';

	private static $settings = null;

	public static function get_settings() {
		if ( null === self::$settings ) {
			$defaults       = array(
				'post_types'    => array( 'post' ),
				'output_schema' => 1,
				'box_position'  => 'after_content',
				'appearance'    => self::default_appearance(),
			);
			self::$settings = wp_parse_args( get_option( ABX_SETTINGS_OPTION, array() ), $defaults );
			if ( ! is_array( self::$settings['post_types'] ) ) {
				self::$settings['post_types'] = array();
			}
			if ( ! is_array( self::$settings['appearance'] ) ) {
				self::$settings['appearance'] = self::default_appearance();
			}
		}

		return self::$settings;
	}

	public static function default_appearance() {
		return array(
			'layout'           => 'boxed',
			'accent_color'     => '#2271b1',
			'background_color' => '#fafafa',
			'avatar_shape'     => 'circle',
			'avatar_size'      => 'medium',
		);
	}

	public static function get_appearance() {
		return self::get_settings()['appearance'];
	}

	public static function is_post_type_globally_enabled( $post_type ) {
		$settings = self::get_settings();
		return in_array( $post_type, $settings['post_types'], true );
	}

	public static function get_override( $post_id ) {
		$value = get_post_meta( $post_id, self::OVERRIDE_META_KEY, true );
		return in_array( $value, array( 'enable', 'disable' ), true ) ? $value : 'default';
	}

	public static function get_assigned_author_id( $post_id ) {
		$author_id = (int) get_post_meta( $post_id, self::AUTHOR_META_KEY, true );
		return ( $author_id && ABX_AUTHOR_CPT === get_post_type( $author_id ) ) ? $author_id : 0;
	}

	/**
	 * Whether the box/schema should be considered "on" for this post,
	 * independent of whether an author is actually assigned.
	 */
	public static function is_enabled_for_post( $post_id ) {
		$override = self::get_override( $post_id );

		if ( 'enable' === $override ) {
			$enabled = true;
		} elseif ( 'disable' === $override ) {
			$enabled = false;
		} else {
			$enabled = self::is_post_type_globally_enabled( get_post_type( $post_id ) );
		}

		/**
		 * Filter final enabled/disabled state for a piece of content.
		 *
		 * @param bool $enabled
		 * @param int  $post_id
		 */
		return apply_filters( 'abx_is_enabled_for_post', $enabled, $post_id );
	}

	/**
	 * Convenience: enabled AND has a valid author assigned.
	 */
	public static function should_display( $post_id ) {
		return self::is_enabled_for_post( $post_id ) && self::get_assigned_author_id( $post_id ) > 0;
	}
}
