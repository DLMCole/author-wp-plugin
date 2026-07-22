<?php
/**
 * Fallback single-author.php template, used only when the active theme
 * doesn't provide its own single-abx_author.php or
 * authorship-box/single-author.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$author_id  = get_the_ID();
	$job_title  = get_post_meta( $author_id, '_abx_job_title', true );
	$org_name   = get_post_meta( $author_id, '_abx_org_name', true );
	$org_url    = get_post_meta( $author_id, '_abx_org_url', true );
	$website    = get_post_meta( $author_id, '_abx_url', true );
	$email      = get_post_meta( $author_id, '_abx_email', true );
	$knows_about = array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $author_id, '_abx_knows_about', true ) ) ) );
	$sameas     = array_values( array_filter( array_map( 'trim', preg_split( '/\r?\n/', (string) get_post_meta( $author_id, '_abx_sameas', true ) ) ) ) );
	$content_items = ABX_Single_Template::get_authored_content( $author_id );
	?>
	<main id="primary" class="abx-author-profile">
		<article <?php post_class(); ?>>
			<header class="abx-author-profile__header">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="abx-author-profile__avatar"><?php the_post_thumbnail( 'medium' ); ?></div>
				<?php endif; ?>
				<div>
					<h1 class="abx-author-profile__name"><?php the_title(); ?></h1>
					<?php if ( $job_title || $org_name ) : ?>
						<p class="abx-author-profile__title">
							<?php
							echo esc_html( $job_title );
							if ( $job_title && $org_name ) {
								echo ' &middot; ';
							}
							if ( $org_url ) {
								printf( '<a href="%s">%s</a>', esc_url( $org_url ), esc_html( $org_name ) );
							} else {
								echo esc_html( $org_name );
							}
							?>
						</p>
					<?php endif; ?>
				</div>
			</header>

			<div class="abx-author-profile__bio">
				<?php the_content(); ?>
			</div>

			<?php if ( $knows_about ) : ?>
				<p class="abx-author-profile__expertise">
					<strong><?php esc_html_e( 'Expertise:', 'authorship-box' ); ?></strong> <?php echo esc_html( implode( ', ', $knows_about ) ); ?>
				</p>
			<?php endif; ?>

			<?php if ( $website || $email || $sameas ) : ?>
				<p class="abx-author-profile__links">
					<?php if ( $website ) : ?><a href="<?php echo esc_url( $website ); ?>" rel="me" target="_blank"><?php esc_html_e( 'Website', 'authorship-box' ); ?></a><?php endif; ?>
					<?php if ( $email ) : ?><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php esc_html_e( 'Email', 'authorship-box' ); ?></a><?php endif; ?>
					<?php foreach ( $sameas as $url ) : ?>
						<a href="<?php echo esc_url( $url ); ?>" rel="me" target="_blank"><?php echo esc_html( wp_parse_url( $url, PHP_URL_HOST ) ); ?></a>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>

			<?php if ( $content_items ) : ?>
				<section class="abx-author-profile__content">
					<h2><?php esc_html_e( 'Content by this author', 'authorship-box' ); ?></h2>
					<ul>
						<?php foreach ( $content_items as $item ) : ?>
							<li><a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>
		</article>
	</main>
	<?php
endwhile;

get_footer();
