<?php
/**
 * Default author box template.
 * Copy this file to yourtheme/authorship-box/author-box.php to override it.
 *
 * Available: $data (see ABX_Frontend::render_author_box)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon_labels = array(
	'twitter.com'   => 'X (Twitter)',
	'x.com'         => 'X (Twitter)',
	'linkedin.com'  => 'LinkedIn',
	'facebook.com'  => 'Facebook',
	'instagram.com' => 'Instagram',
	'youtube.com'   => 'YouTube',
	'wikipedia.org' => 'Wikipedia',
);

$get_label = function ( $url ) use ( $icon_labels ) {
	$host = wp_parse_url( $url, PHP_URL_HOST );
	$host = $host ? preg_replace( '/^www\./', '', $host ) : '';
	foreach ( $icon_labels as $needle => $label ) {
		if ( false !== strpos( $host, $needle ) ) {
			return $label;
		}
	}
	return $host ? $host : $url;
};
?>
<div class="<?php echo esc_attr( $data['wrapper_class'] ); ?>" id="abx-author-<?php echo esc_attr( $data['author_id'] ); ?>" style="<?php echo esc_attr( $data['wrapper_style'] ); ?>">
	<?php if ( $data['image_url'] ) : ?>
		<div class="abx-author-box__avatar">
			<a href="<?php echo esc_url( $data['permalink'] ); ?>">
				<img src="<?php echo esc_url( $data['image_url'] ); ?>" alt="<?php echo esc_attr( $data['name'] ); ?>" loading="lazy" />
			</a>
		</div>
	<?php endif; ?>

	<div class="abx-author-box__body">
		<p class="abx-author-box__label"><?php esc_html_e( 'Written by', 'authorship-box' ); ?></p>
		<h4 class="abx-author-box__name">
			<a href="<?php echo esc_url( $data['permalink'] ); ?>"><?php echo esc_html( $data['name'] ); ?></a>
		</h4>

		<?php if ( $data['job_title'] || $data['org_name'] ) : ?>
			<p class="abx-author-box__title">
				<?php
				echo esc_html( $data['job_title'] );
				if ( $data['job_title'] && $data['org_name'] ) {
					echo ' &middot; ';
				}
				echo esc_html( $data['org_name'] );
				?>
			</p>
		<?php endif; ?>

		<?php if ( $data['bio'] ) : ?>
			<p class="abx-author-box__bio"><?php echo esc_html( $data['bio'] ); ?></p>
		<?php endif; ?>

		<?php if ( $data['sameas'] || $data['website'] ) : ?>
			<p class="abx-author-box__links">
				<?php if ( $data['website'] ) : ?>
					<a href="<?php echo esc_url( $data['website'] ); ?>" rel="me" target="_blank"><?php echo esc_html( $get_label( $data['website'] ) ); ?></a>
				<?php endif; ?>
				<?php foreach ( $data['sameas'] as $url ) : ?>
					<a href="<?php echo esc_url( $url ); ?>" rel="me" target="_blank"><?php echo esc_html( $get_label( $url ) ); ?></a>
				<?php endforeach; ?>
			</p>
		<?php endif; ?>
	</div>
</div>
