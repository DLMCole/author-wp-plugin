<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The "Authorship Box" meta box shown on posts, pages, and any other
 * public post type — lets an editor assign an Author and override the
 * global on/off setting for that one item.
 */
class ABX_Post_Metabox {

	const NONCE_ACTION = 'abx_save_post_meta';
	const NONCE_NAME   = 'abx_post_meta_nonce';

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
	}

	private function get_target_post_types() {
		return array_keys( ABX_Settings::get_supported_post_types() );
	}

	public function add_boxes() {
		foreach ( $this->get_target_post_types() as $post_type ) {
			add_meta_box(
				'abx-post-authorship',
				__( 'Authorship Box', 'authorship-box' ),
				array( $this, 'render' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	public function render( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		$assigned_author  = ABX_Resolver::get_assigned_author_id( $post->ID );
		$override         = ABX_Resolver::get_override( $post->ID );
		$global_on        = ABX_Resolver::is_post_type_globally_enabled( $post->post_type );
		$position_override = ABX_Resolver::get_position_override( $post->ID );
		$global_position  = ABX_Resolver::get_settings()['box_position'];

		$position_labels = array(
			'before_content' => __( 'before the content', 'authorship-box' ),
			'after_content'  => __( 'after the content', 'authorship-box' ),
			'shortcode_only' => __( 'shortcode only', 'authorship-box' ),
		);

		$authors = get_posts(
			array(
				'post_type'      => ABX_AUTHOR_CPT,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);
		?>
		<p>
			<label for="abx_author_id"><strong><?php esc_html_e( 'Author', 'authorship-box' ); ?></strong></label><br />
			<select name="abx_author_id" id="abx_author_id" class="widefat">
				<option value="0"><?php esc_html_e( '— None —', 'authorship-box' ); ?></option>
				<?php foreach ( $authors as $author ) : ?>
					<option value="<?php echo esc_attr( $author->ID ); ?>" <?php selected( $assigned_author, $author->ID ); ?>><?php echo esc_html( get_the_title( $author ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php if ( empty( $authors ) ) : ?>
			<p class="description">
				<?php
				printf(
					wp_kses(
						/* translators: %s: URL to create a new author */
						__( 'No authors yet. <a href="%s">Create one</a>.', 'authorship-box' ),
						array( 'a' => array( 'href' => array() ) )
					),
					esc_url( admin_url( 'post-new.php?post_type=' . ABX_AUTHOR_CPT ) )
				);
				?>
			</p>
		<?php endif; ?>

		<p>
			<label for="abx_override_mode"><strong><?php esc_html_e( 'Author Box Display', 'authorship-box' ); ?></strong></label><br />
			<select name="abx_override_mode" id="abx_override_mode" class="widefat">
				<option value="default" <?php selected( $override, 'default' ); ?>>
					<?php
					printf(
						/* translators: %s: "shown" or "hidden" */
						esc_html__( 'Default (currently %s site-wide)', 'authorship-box' ),
						$global_on ? esc_html__( 'shown', 'authorship-box' ) : esc_html__( 'hidden', 'authorship-box' )
					);
					?>
				</option>
				<option value="enable" <?php selected( $override, 'enable' ); ?>><?php esc_html_e( 'Always show on this item', 'authorship-box' ); ?></option>
				<option value="disable" <?php selected( $override, 'disable' ); ?>><?php esc_html_e( 'Always hide on this item', 'authorship-box' ); ?></option>
			</select>
			<span class="description">
				<?php esc_html_e( 'Overrides the global setting under Authors → Settings just for this item.', 'authorship-box' ); ?>
			</span>
		</p>

		<p>
			<label for="abx_position_override"><strong><?php esc_html_e( 'Box Placement', 'authorship-box' ); ?></strong></label><br />
			<select name="abx_position_override" id="abx_position_override" class="widefat">
				<option value="default" <?php selected( $position_override, 'default' ); ?>>
					<?php
					printf(
						/* translators: %s: current global placement, e.g. "after the content" */
						esc_html__( 'Default (currently %s site-wide)', 'authorship-box' ),
						esc_html( $position_labels[ $global_position ] ?? $global_position )
					);
					?>
				</option>
				<option value="before_content" <?php selected( $position_override, 'before_content' ); ?>><?php esc_html_e( 'Before the content', 'authorship-box' ); ?></option>
				<option value="after_content" <?php selected( $position_override, 'after_content' ); ?>><?php esc_html_e( 'After the content', 'authorship-box' ); ?></option>
				<option value="shortcode_only" <?php selected( $position_override, 'shortcode_only' ); ?>><?php esc_html_e( 'Shortcode only (place [authorship_box] manually)', 'authorship-box' ); ?></option>
			</select>
			<span class="description">
				<?php esc_html_e( 'Only affects where the box auto-appears; the "Author Box Display" setting above still controls whether it shows at all.', 'authorship-box' ); ?>
			</span>
		</p>
		<?php
	}

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( wp_unslash( $_POST[ self::NONCE_NAME ] ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! in_array( $post->post_type, $this->get_target_post_types(), true ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['abx_author_id'] ) ) {
			$author_id = (int) $_POST['abx_author_id'];
			if ( $author_id > 0 && ABX_AUTHOR_CPT === get_post_type( $author_id ) ) {
				update_post_meta( $post_id, ABX_Resolver::AUTHOR_META_KEY, $author_id );
			} else {
				delete_post_meta( $post_id, ABX_Resolver::AUTHOR_META_KEY );
			}
		}

		if ( isset( $_POST['abx_override_mode'] ) ) {
			$mode = sanitize_text_field( wp_unslash( $_POST['abx_override_mode'] ) );
			if ( in_array( $mode, array( 'enable', 'disable' ), true ) ) {
				update_post_meta( $post_id, ABX_Resolver::OVERRIDE_META_KEY, $mode );
			} else {
				delete_post_meta( $post_id, ABX_Resolver::OVERRIDE_META_KEY );
			}
		}

		if ( isset( $_POST['abx_position_override'] ) ) {
			$position = sanitize_text_field( wp_unslash( $_POST['abx_position_override'] ) );
			if ( in_array( $position, ABX_Resolver::POSITIONS, true ) ) {
				update_post_meta( $post_id, ABX_Resolver::POSITION_META_KEY, $position );
			} else {
				delete_post_meta( $post_id, ABX_Resolver::POSITION_META_KEY );
			}
		}
	}
}
