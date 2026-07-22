<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template tag for theme developers: prints the author box for a given
 * post (defaults to the current post in the loop).
 */
function abx_the_author_box( $post_id = null ) {
	echo ABX_Frontend::get_box_html( $post_id ? $post_id : get_the_ID(), true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
