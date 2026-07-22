<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABX_PLUGIN_DIR . 'includes/lib/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Points Plugin Update Checker at this plugin's GitHub repo so sites
 * running it get native "Update available" notices from tagged GitHub
 * Releases, the same way they would from wordpress.org.
 *
 * To publish an update: bump the `Version` header above, tag/release it
 * on GitHub, and attach the built plugin zip as a release asset (the
 * "release" GitHub Actions job does this automatically on `v*` tags).
 */
class ABX_Updater {

	const REPO_URL = 'https://github.com/DLMCole/author-wp-plugin/';

	public function __construct() {
		$update_checker = PucFactory::buildUpdateChecker(
			self::REPO_URL,
			ABX_PLUGIN_FILE,
			'authorship-box'
		);

		$update_checker->getVcsApi()->enableReleaseAssets( '/\.zip($|[?&#])/i' );

		/**
		 * If this repo is ever made private, uncomment and supply a
		 * GitHub personal access token with repo read access:
		 *
		 * $update_checker->setAuthentication( 'YOUR-GITHUB-TOKEN' );
		 */
	}
}
