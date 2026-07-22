<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues the plugin's admin and front-end CSS/JS.
 */
class ABX_Assets {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_assets' ) );
	}

	public function admin_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$is_author_screen   = ABX_AUTHOR_CPT === $screen->post_type;
		$is_settings_screen = ABX_AUTHOR_CPT . '_page_' . ABX_Settings::PAGE_SLUG === $screen->id;
		$is_content_screen  = in_array( $screen->post_type, array_keys( ABX_Settings::get_supported_post_types() ), true ) && 'post' === $screen->base;

		if ( ! $is_author_screen && ! $is_settings_screen && ! $is_content_screen ) {
			return;
		}

		wp_enqueue_style( 'abx-admin', ABX_PLUGIN_URL . 'assets/css/admin.css', array(), ABX_VERSION );
		wp_enqueue_script( 'abx-admin', ABX_PLUGIN_URL . 'assets/js/admin.js', array(), ABX_VERSION, true );
	}

	public function frontend_assets() {
		wp_register_style( 'abx-frontend', ABX_PLUGIN_URL . 'assets/css/frontend.css', array(), ABX_VERSION );

		if ( is_singular() ) {
			wp_enqueue_style( 'abx-frontend' );
		}
	}
}
