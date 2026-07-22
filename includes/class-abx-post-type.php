<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the "Author" custom post type used to store reusable author
 * profiles, plus a couple of admin list-table niceties.
 */
class ABX_Post_Type {

	public function __construct() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'manage_' . ABX_AUTHOR_CPT . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . ABX_AUTHOR_CPT . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
	}

	public static function register() {
		$labels = array(
			'name'               => __( 'Authors', 'authorship-box' ),
			'singular_name'      => __( 'Author', 'authorship-box' ),
			'add_new'            => __( 'Add New Author', 'authorship-box' ),
			'add_new_item'       => __( 'Add New Author', 'authorship-box' ),
			'edit_item'          => __( 'Edit Author', 'authorship-box' ),
			'new_item'           => __( 'New Author', 'authorship-box' ),
			'view_item'          => __( 'View Author', 'authorship-box' ),
			'search_items'       => __( 'Search Authors', 'authorship-box' ),
			'not_found'          => __( 'No authors found', 'authorship-box' ),
			'not_found_in_trash' => __( 'No authors found in Trash', 'authorship-box' ),
			'all_items'          => __( 'All Authors', 'authorship-box' ),
			'menu_name'          => __( 'Authorship Box', 'authorship-box' ),
			'name_admin_bar'     => __( 'Author', 'authorship-box' ),
		);

		register_post_type(
			ABX_AUTHOR_CPT,
			array(
				'labels'        => $labels,
				'public'        => true,
				'has_archive'   => 'authors',
				'rewrite'       => array(
					'slug'       => 'author-profile',
					'with_front' => false,
				),
				'menu_icon'     => 'dashicons-businessperson',
				'menu_position' => 26,
				'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions' ),
				'show_in_rest'  => true,
				'capability_type' => 'post',
			)
		);
	}

	public function columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['abx_photo']    = __( 'Photo', 'authorship-box' );
			}
		}
		$new['abx_job_title'] = __( 'Job Title', 'authorship-box' );
		return $new;
	}

	public function column_content( $column, $post_id ) {
		if ( 'abx_photo' === $column ) {
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, array( 40, 40 ), array( 'style' => 'border-radius:50%;object-fit:cover;' ) );
			} else {
				echo '&#8212;';
			}
		} elseif ( 'abx_job_title' === $column ) {
			$job_title = get_post_meta( $post_id, '_abx_job_title', true );
			echo esc_html( $job_title ? $job_title : '—' );
		}
	}
}
