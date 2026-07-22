<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$author_ids = get_posts(
	array(
		'post_type'      => 'abx_author',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

foreach ( $author_ids as $author_id ) {
	wp_delete_post( $author_id, true );
}

delete_option( 'abx_settings' );

$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->postmeta} WHERE meta_key IN (%s, %s)",
		'_abx_author_id',
		'_abx_override_mode'
	)
);
