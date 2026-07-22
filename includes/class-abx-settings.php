<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Global "Authorship Box" settings screen: which post types get the
 * author box by default, plus a couple of site-wide toggles. Individual
 * posts can still override this via ABX_Post_Metabox.
 */
class ABX_Settings {

	const PAGE_SLUG = 'abx-settings';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_setting' ) );
	}

	public function add_menu() {
		add_submenu_page(
			'edit.php?post_type=' . ABX_AUTHOR_CPT,
			__( 'Authorship Box Settings', 'authorship-box' ),
			__( 'Settings', 'authorship-box' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	public static function get_supported_post_types() {
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		unset( $post_types[ ABX_AUTHOR_CPT ], $post_types['attachment'] );

		/**
		 * Filter which post types Authorship Box can be enabled for.
		 *
		 * @param WP_Post_Type[] $post_types
		 */
		return apply_filters( 'abx_supported_post_types', $post_types );
	}

	public function register_setting() {
		register_setting(
			'abx_settings_group',
			ABX_SETTINGS_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => array(
					'post_types'    => array( 'post' ),
					'output_schema' => 1,
					'box_position'  => 'after_content',
					'appearance'    => ABX_Resolver::default_appearance(),
				),
			)
		);
	}

	public function sanitize( $input ) {
		$valid_post_types = array_keys( self::get_supported_post_types() );
		$post_types        = isset( $input['post_types'] ) && is_array( $input['post_types'] ) ? $input['post_types'] : array();

		return array(
			'post_types'    => array_values( array_intersect( $valid_post_types, $post_types ) ),
			'output_schema' => empty( $input['output_schema'] ) ? 0 : 1,
			'box_position'  => in_array( $input['box_position'] ?? '', ABX_Resolver::POSITIONS, true ) ? $input['box_position'] : 'after_content',
			'appearance'    => $this->sanitize_appearance( isset( $input['appearance'] ) && is_array( $input['appearance'] ) ? $input['appearance'] : array() ),
		);
	}

	private function sanitize_appearance( $input ) {
		$defaults = ABX_Resolver::default_appearance();

		$accent_color     = ! empty( $input['accent_color'] ) ? sanitize_hex_color( $input['accent_color'] ) : '';
		$background_color = ! empty( $input['background_color'] ) ? sanitize_hex_color( $input['background_color'] ) : '';

		return array(
			'layout'           => in_array( $input['layout'] ?? '', array( 'boxed', 'minimal', 'bordered' ), true ) ? $input['layout'] : $defaults['layout'],
			'accent_color'     => $accent_color ? $accent_color : $defaults['accent_color'],
			'background_color' => $background_color ? $background_color : $defaults['background_color'],
			'avatar_shape'     => in_array( $input['avatar_shape'] ?? '', array( 'circle', 'square' ), true ) ? $input['avatar_shape'] : $defaults['avatar_shape'],
			'avatar_size'      => in_array( $input['avatar_size'] ?? '', array( 'small', 'medium', 'large' ), true ) ? $input['avatar_size'] : $defaults['avatar_size'],
		);
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings   = ABX_Resolver::get_settings();
		$post_types = self::get_supported_post_types();
		?>
		<div class="wrap abx-settings-wrap">
			<h1><?php esc_html_e( 'Authorship Box Settings', 'authorship-box' ); ?></h1>
			<p><?php esc_html_e( 'Choose which content types show the author box (and schema.org markup) by default. Each individual post, page, or item can still override this from its own edit screen.', 'authorship-box' ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( 'abx_settings_group' ); ?>

				<h2><?php esc_html_e( 'Enabled Content Types', 'authorship-box' ); ?></h2>
				<p>
					<a href="#" class="abx-select-all"><?php esc_html_e( 'Select all', 'authorship-box' ); ?></a> |
					<a href="#" class="abx-select-none"><?php esc_html_e( 'Select none', 'authorship-box' ); ?></a>
				</p>
				<table class="form-table" role="presentation">
					<tbody>
					<?php foreach ( $post_types as $slug => $post_type ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( $post_type->labels->name ); ?></th>
							<td>
								<label>
									<input type="checkbox" class="abx-post-type-checkbox" name="<?php echo esc_attr( ABX_SETTINGS_OPTION ); ?>[post_types][]" value="<?php echo esc_attr( $slug ); ?>" <?php checked( in_array( $slug, $settings['post_types'], true ) ); ?> />
									<?php
									printf(
										/* translators: %s: post type name */
										esc_html__( 'Turn on the author box for all %s by default', 'authorship-box' ),
										esc_html( strtolower( $post_type->labels->name ) )
									);
									?>
								</label>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<h2><?php esc_html_e( 'Display Options', 'authorship-box' ); ?></h2>
				<table class="form-table" role="presentation">
					<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Box Position', 'authorship-box' ); ?></th>
						<td>
							<select name="<?php echo esc_attr( ABX_SETTINGS_OPTION ); ?>[box_position]">
								<option value="after_content" <?php selected( $settings['box_position'], 'after_content' ); ?>><?php esc_html_e( 'After the content', 'authorship-box' ); ?></option>
								<option value="before_content" <?php selected( $settings['box_position'], 'before_content' ); ?>><?php esc_html_e( 'Before the content', 'authorship-box' ); ?></option>
								<option value="shortcode_only" <?php selected( $settings['box_position'], 'shortcode_only' ); ?>><?php esc_html_e( 'Shortcode only (no automatic placement)', 'authorship-box' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'With "Shortcode only", the box never appears automatically — add it wherever you want with the [authorship_box] shortcode or abx_the_author_box() in a template. Each item can override this placement individually.', 'authorship-box' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Schema Markup', 'authorship-box' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( ABX_SETTINGS_OPTION ); ?>[output_schema]" value="1" <?php checked( ! empty( $settings['output_schema'] ) ); ?> />
								<?php esc_html_e( 'Output schema.org JSON-LD markup for assigned authors', 'authorship-box' ); ?>
							</label>
						</td>
					</tr>
					</tbody>
				</table>

				<h2><?php esc_html_e( 'Appearance', 'authorship-box' ); ?></h2>
				<table class="form-table" role="presentation">
					<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Layout Style', 'authorship-box' ); ?></th>
						<td>
							<select name="<?php echo esc_attr( ABX_SETTINGS_OPTION ); ?>[appearance][layout]">
								<option value="boxed" <?php selected( $settings['appearance']['layout'], 'boxed' ); ?>><?php esc_html_e( 'Boxed (background + border)', 'authorship-box' ); ?></option>
								<option value="bordered" <?php selected( $settings['appearance']['layout'], 'bordered' ); ?>><?php esc_html_e( 'Bordered (outline only)', 'authorship-box' ); ?></option>
								<option value="minimal" <?php selected( $settings['appearance']['layout'], 'minimal' ); ?>><?php esc_html_e( 'Minimal (top divider only)', 'authorship-box' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Accent Color', 'authorship-box' ); ?></th>
						<td>
							<input type="text" class="abx-color-field" name="<?php echo esc_attr( ABX_SETTINGS_OPTION ); ?>[appearance][accent_color]" value="<?php echo esc_attr( $settings['appearance']['accent_color'] ); ?>" data-default-color="<?php echo esc_attr( ABX_Resolver::default_appearance()['accent_color'] ); ?>" />
							<p class="description"><?php esc_html_e( 'Used for the author name link, the bordered layout\'s outline, and social links.', 'authorship-box' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Background Color', 'authorship-box' ); ?></th>
						<td>
							<input type="text" class="abx-color-field" name="<?php echo esc_attr( ABX_SETTINGS_OPTION ); ?>[appearance][background_color]" value="<?php echo esc_attr( $settings['appearance']['background_color'] ); ?>" data-default-color="<?php echo esc_attr( ABX_Resolver::default_appearance()['background_color'] ); ?>" />
							<p class="description"><?php esc_html_e( 'Only visible with the Boxed layout style.', 'authorship-box' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Avatar Shape', 'authorship-box' ); ?></th>
						<td>
							<select name="<?php echo esc_attr( ABX_SETTINGS_OPTION ); ?>[appearance][avatar_shape]">
								<option value="circle" <?php selected( $settings['appearance']['avatar_shape'], 'circle' ); ?>><?php esc_html_e( 'Circle', 'authorship-box' ); ?></option>
								<option value="square" <?php selected( $settings['appearance']['avatar_shape'], 'square' ); ?>><?php esc_html_e( 'Rounded square', 'authorship-box' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Avatar Size', 'authorship-box' ); ?></th>
						<td>
							<select name="<?php echo esc_attr( ABX_SETTINGS_OPTION ); ?>[appearance][avatar_size]">
								<option value="small" <?php selected( $settings['appearance']['avatar_size'], 'small' ); ?>><?php esc_html_e( 'Small', 'authorship-box' ); ?></option>
								<option value="medium" <?php selected( $settings['appearance']['avatar_size'], 'medium' ); ?>><?php esc_html_e( 'Medium', 'authorship-box' ); ?></option>
								<option value="large" <?php selected( $settings['appearance']['avatar_size'], 'large' ); ?>><?php esc_html_e( 'Large', 'authorship-box' ); ?></option>
							</select>
						</td>
					</tr>
					</tbody>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
