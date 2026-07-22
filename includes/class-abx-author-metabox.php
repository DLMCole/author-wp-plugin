<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The "Author Details" meta box shown when editing an Author profile
 * (the abx_author CPT). Field definitions are data-driven so render()
 * and save() stay in sync from a single source of truth — see
 * ABX_Schema::get_field_groups(), which this class shares with the
 * schema generator.
 */
class ABX_Author_Metabox {

	const NONCE_ACTION = 'abx_save_author_meta';
	const NONCE_NAME   = 'abx_author_meta_nonce';

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . ABX_AUTHOR_CPT, array( $this, 'save' ), 10, 2 );
	}

	public function add_boxes() {
		add_meta_box(
			'abx-author-details',
			__( 'Author Schema Details', 'authorship-box' ),
			array( $this, 'render' ),
			ABX_AUTHOR_CPT,
			'normal',
			'high'
		);

		add_meta_box(
			'abx-author-preview',
			__( 'Generated Schema Preview', 'authorship-box' ),
			array( $this, 'render_preview' ),
			ABX_AUTHOR_CPT,
			'side',
			'default'
		);
	}

	public function render( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		echo '<p class="description">' . esc_html__( 'The Display Name (post title), Bio (main editor), and Featured Image above are also used in this author\'s schema.org markup.', 'authorship-box' ) . '</p>';

		foreach ( ABX_Schema::get_field_groups() as $group_key => $group ) {
			echo '<div class="abx-field-group">';
			echo '<h3>' . esc_html( $group['title'] ) . '</h3>';
			echo '<table class="form-table abx-form-table"><tbody>';

			foreach ( $group['fields'] as $key => $field ) {
				$value = get_post_meta( $post->ID, $key, true );
				$this->render_field( $key, $field, $value );
			}

			echo '</tbody></table></div>';
		}
	}

	private function render_field( $key, $field, $value ) {
		printf( '<tr><th scope="row"><label for="%1$s">%2$s</label></th><td>', esc_attr( $key ), esc_html( $field['label'] ) );

		switch ( $field['type'] ) {
			case 'select':
				echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
				foreach ( $field['options'] as $option_value => $option_label ) {
					printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $option_value ), selected( $value, $option_value, false ), esc_html( $option_label ) );
				}
				echo '</select>';
				break;

			case 'textarea':
			case 'textarea_lines':
			case 'textarea_lines_plain':
				printf(
					'<textarea id="%1$s" name="%1$s" rows="%2$d" class="large-text">%3$s</textarea>',
					esc_attr( $key ),
					isset( $field['rows'] ) ? (int) $field['rows'] : 4,
					esc_textarea( $value )
				);
				break;

			default:
				printf(
					'<input type="%1$s" id="%2$s" name="%2$s" value="%3$s" class="regular-text"%4$s />',
					esc_attr( $field['type'] ),
					esc_attr( $key ),
					esc_attr( $value ),
					isset( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : ''
				);
		}

		if ( ! empty( $field['desc'] ) ) {
			echo '<p class="description">' . esc_html( $field['desc'] ) . '</p>';
		}

		echo '</td></tr>';
	}

	public function render_preview( $post ) {
		$schema = ABX_Schema::build_person_schema( $post->ID );
		echo '<p class="description">' . esc_html__( 'This JSON-LD is generated automatically from the fields on the left and is output on any content this author is assigned to.', 'authorship-box' ) . '</p>';
		echo '<textarea readonly rows="16" class="widefat" style="font-family:monospace;font-size:11px;">' . esc_textarea( wp_json_encode( $schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ) . '</textarea>';
	}

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( wp_unslash( $_POST[ self::NONCE_NAME ] ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( ABX_Schema::get_field_groups() as $group ) {
			foreach ( $group['fields'] as $key => $field ) {
				if ( ! isset( $_POST[ $key ] ) ) {
					continue;
				}

				$raw = wp_unslash( $_POST[ $key ] );
				update_post_meta( $post_id, $key, $this->sanitize_field( $raw, $field ) );
			}
		}
	}

	private function sanitize_field( $raw, $field ) {
		switch ( $field['type'] ) {
			case 'email':
				return sanitize_email( $raw );

			case 'url':
				return esc_url_raw( trim( $raw ) );

			case 'select':
				return array_key_exists( $raw, $field['options'] ) ? $raw : '';

			case 'textarea':
				return sanitize_textarea_field( $raw );

			case 'textarea_lines':
			case 'textarea_lines_plain':
				$lines = array_filter( array_map( 'trim', explode( "\n", $raw ) ) );
				$lines = array_map( 'sanitize_text_field', $lines );
				return implode( "\n", $lines );

			case 'date':
				return preg_match( '/^\d{4}-\d{2}-\d{2}$/', trim( $raw ) ) ? trim( $raw ) : '';

			default:
				return sanitize_text_field( $raw );
		}
	}
}
