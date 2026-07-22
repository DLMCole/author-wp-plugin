<?php
/**
 * Plugin Name:       Authorship Box
 * Plugin URI:        https://github.com/DLMCole/author-wp-plugin
 * Description:       Create reusable Author profiles with full schema.org markup, and attach them to posts, pages, and any custom post type. Toggle the author box on globally by content type, with a per-item override.
 * Version:           1.0.3
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Cole
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       authorship-box
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ABX_VERSION', '1.0.3' );
define( 'ABX_PLUGIN_FILE', __FILE__ );
define( 'ABX_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ABX_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ABX_AUTHOR_CPT', 'abx_author' );
define( 'ABX_SETTINGS_OPTION', 'abx_settings' );

require_once ABX_PLUGIN_DIR . 'includes/class-abx-post-type.php';
require_once ABX_PLUGIN_DIR . 'includes/class-abx-author-metabox.php';
require_once ABX_PLUGIN_DIR . 'includes/class-abx-schema.php';
require_once ABX_PLUGIN_DIR . 'includes/class-abx-settings.php';
require_once ABX_PLUGIN_DIR . 'includes/class-abx-resolver.php';
require_once ABX_PLUGIN_DIR . 'includes/class-abx-post-metabox.php';
require_once ABX_PLUGIN_DIR . 'includes/class-abx-frontend.php';
require_once ABX_PLUGIN_DIR . 'includes/template-tags.php';
require_once ABX_PLUGIN_DIR . 'includes/class-abx-single-template.php';
require_once ABX_PLUGIN_DIR . 'includes/class-abx-assets.php';
require_once ABX_PLUGIN_DIR . 'includes/class-abx-updater.php';

/**
 * Central bootstrap. Every feature area lives in its own class and wires
 * itself up in init(); this just decides when each one gets constructed.
 */
final class Authorship_Box {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		register_activation_hook( ABX_PLUGIN_FILE, array( __CLASS__, 'activate' ) );
		register_deactivation_hook( ABX_PLUGIN_FILE, array( __CLASS__, 'deactivate' ) );
	}

	public function init() {
		load_plugin_textdomain( 'authorship-box', false, dirname( plugin_basename( ABX_PLUGIN_FILE ) ) . '/languages' );

		new ABX_Post_Type();
		new ABX_Author_Metabox();
		new ABX_Settings();
		new ABX_Post_Metabox();
		new ABX_Frontend();
		new ABX_Single_Template();
		new ABX_Assets();
		new ABX_Updater();
	}

	public static function activate() {
		require_once ABX_PLUGIN_DIR . 'includes/class-abx-post-type.php';
		ABX_Post_Type::register();

		if ( false === get_option( ABX_SETTINGS_OPTION ) ) {
			update_option(
				ABX_SETTINGS_OPTION,
				array(
					'post_types'    => array( 'post' ),
					'output_schema' => 1,
					'box_position'  => 'after_content',
				)
			);
		}

		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}
}

Authorship_Box::instance();
