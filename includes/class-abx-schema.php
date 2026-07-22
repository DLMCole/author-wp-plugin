<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Owns the Author field definitions (single source of truth, used by the
 * metabox for rendering/saving) and turns an Author post into a
 * schema.org Person graph.
 */
class ABX_Schema {

	private static $field_groups = null;

	public static function get_field_groups() {
		if ( null !== self::$field_groups ) {
			return self::$field_groups;
		}

		self::$field_groups = array(
			'identity'     => array(
				'title'  => __( 'Name & Titles', 'authorship-box' ),
				'fields' => array(
					'_abx_honorific_prefix' => array(
						'label'       => __( 'Honorific Prefix', 'authorship-box' ),
						'type'        => 'text',
						'placeholder' => __( 'Dr., Prof., Rev.', 'authorship-box' ),
						'desc'        => __( 'schema.org: honorificPrefix', 'authorship-box' ),
					),
					'_abx_first_name'       => array(
						'label' => __( 'First Name', 'authorship-box' ),
						'type'  => 'text',
						'desc'  => __( 'schema.org: givenName', 'authorship-box' ),
					),
					'_abx_last_name'        => array(
						'label' => __( 'Last Name', 'authorship-box' ),
						'type'  => 'text',
						'desc'  => __( 'schema.org: familyName', 'authorship-box' ),
					),
					'_abx_honorific_suffix' => array(
						'label'       => __( 'Honorific Suffix', 'authorship-box' ),
						'type'        => 'text',
						'placeholder' => __( 'Ph.D., Jr., Esq.', 'authorship-box' ),
						'desc'        => __( 'schema.org: honorificSuffix', 'authorship-box' ),
					),
				),
			),
			'professional' => array(
				'title'  => __( 'Professional Details', 'authorship-box' ),
				'fields' => array(
					'_abx_job_title'   => array(
						'label' => __( 'Job Title', 'authorship-box' ),
						'type'  => 'text',
						'desc'  => __( 'schema.org: jobTitle', 'authorship-box' ),
					),
					'_abx_org_name'    => array(
						'label' => __( 'Organization / Employer', 'authorship-box' ),
						'type'  => 'text',
						'desc'  => __( 'schema.org: worksFor', 'authorship-box' ),
					),
					'_abx_org_url'     => array(
						'label' => __( 'Organization URL', 'authorship-box' ),
						'type'  => 'url',
					),
					'_abx_knows_about' => array(
						'label' => __( 'Areas of Expertise', 'authorship-box' ),
						'type'  => 'text',
						'desc'  => __( 'Comma-separated. schema.org: knowsAbout', 'authorship-box' ),
					),
					'_abx_alumni_of'   => array(
						'label' => __( 'Alma Mater', 'authorship-box' ),
						'type'  => 'textarea_lines',
						'desc'  => __( 'One per line: School Name | https://school-url.com (URL optional). schema.org: alumniOf', 'authorship-box' ),
					),
					'_abx_member_of'   => array(
						'label' => __( 'Memberships', 'authorship-box' ),
						'type'  => 'textarea_lines',
						'desc'  => __( 'One per line: Organization Name | https://org-url.com (URL optional). schema.org: memberOf', 'authorship-box' ),
					),
					'_abx_awards'      => array(
						'label' => __( 'Awards & Honors', 'authorship-box' ),
						'type'  => 'textarea_lines_plain',
						'desc'  => __( 'One award per line. schema.org: award', 'authorship-box' ),
					),
				),
			),
			'contact'      => array(
				'title'  => __( 'Contact & Social', 'authorship-box' ),
				'fields' => array(
					'_abx_email'   => array(
						'label' => __( 'Email', 'authorship-box' ),
						'type'  => 'email',
						'desc'  => __( 'schema.org: email', 'authorship-box' ),
					),
					'_abx_phone'   => array(
						'label' => __( 'Phone', 'authorship-box' ),
						'type'  => 'tel',
						'desc'  => __( 'schema.org: telephone', 'authorship-box' ),
					),
					'_abx_url'     => array(
						'label' => __( 'Personal / External Website', 'authorship-box' ),
						'type'  => 'url',
						'desc'  => __( 'Included in schema.org sameAs automatically', 'authorship-box' ),
					),
					'_abx_sameas'  => array(
						'label' => __( 'Social Profile URLs', 'authorship-box' ),
						'type'  => 'textarea_lines_plain',
						'desc'  => __( 'One URL per line (X/Twitter, LinkedIn, Facebook, Instagram, YouTube, Wikipedia, etc). schema.org: sameAs', 'authorship-box' ),
					),
				),
			),
			'address'      => array(
				'title'  => __( 'Address', 'authorship-box' ),
				'fields' => array(
					'_abx_address_street'  => array(
						'label' => __( 'Street Address', 'authorship-box' ),
						'type'  => 'text',
					),
					'_abx_address_city'    => array(
						'label' => __( 'City', 'authorship-box' ),
						'type'  => 'text',
					),
					'_abx_address_region'  => array(
						'label' => __( 'State / Region', 'authorship-box' ),
						'type'  => 'text',
					),
					'_abx_address_postal'  => array(
						'label' => __( 'Postal Code', 'authorship-box' ),
						'type'  => 'text',
					),
					'_abx_address_country' => array(
						'label' => __( 'Country', 'authorship-box' ),
						'type'  => 'text',
					),
				),
			),
			'personal'     => array(
				'title'  => __( 'Additional Details (optional)', 'authorship-box' ),
				'fields' => array(
					'_abx_gender'      => array(
						'label'   => __( 'Gender', 'authorship-box' ),
						'type'    => 'select',
						'options' => array(
							''       => __( '— none —', 'authorship-box' ),
							'Male'   => __( 'Male', 'authorship-box' ),
							'Female' => __( 'Female', 'authorship-box' ),
							'Other'  => __( 'Other', 'authorship-box' ),
						),
					),
					'_abx_nationality' => array(
						'label' => __( 'Nationality', 'authorship-box' ),
						'type'  => 'text',
					),
					'_abx_birth_date'  => array(
						'label' => __( 'Birth Date', 'authorship-box' ),
						'type'  => 'date',
						'desc'  => __( 'schema.org: birthDate', 'authorship-box' ),
					),
				),
			),
			'bio'          => array(
				'title'  => __( 'Bio', 'authorship-box' ),
				'fields' => array(
					'_abx_short_bio' => array(
						'label' => __( 'Short Bio (used for schema description & box teaser)', 'authorship-box' ),
						'type'  => 'textarea',
						'rows'  => 4,
						'desc'  => __( 'If left blank, the excerpt (or a trimmed version of the full bio below) will be used.', 'authorship-box' ),
					),
				),
			),
		);

		return self::$field_groups;
	}

	/**
	 * Parses "Name | URL" lines into structured items. The URL half is optional.
	 */
	private static function parse_named_lines( $raw ) {
		$items = array();
		foreach ( preg_split( '/\r?\n/', (string) $raw ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$parts = array_map( 'trim', explode( '|', $line, 2 ) );
			$entry = array( 'name' => $parts[0] );
			if ( ! empty( $parts[1] ) ) {
				$entry['url'] = esc_url_raw( $parts[1] );
			}
			$items[] = $entry;
		}
		return $items;
	}

	private static function parse_plain_lines( $raw ) {
		return array_values( array_filter( array_map( 'trim', preg_split( '/\r?\n/', (string) $raw ) ) ) );
	}

	/**
	 * Deliberately avoids get_the_excerpt(): when a post has no manual
	 * excerpt, WordPress builds one by re-running the `the_content`
	 * filter (see wp_trim_excerpt()) — and this plugin hooks that same
	 * filter to inject the author box, which would recurse infinitely.
	 */
	public static function get_short_description( $author_id ) {
		$short = get_post_meta( $author_id, '_abx_short_bio', true );
		if ( $short ) {
			return $short;
		}

		$post = get_post( $author_id );
		if ( ! $post ) {
			return '';
		}

		if ( $post->post_excerpt ) {
			return wp_strip_all_tags( $post->post_excerpt );
		}

		if ( $post->post_content ) {
			return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 55 );
		}

		return '';
	}

	/**
	 * Builds a schema.org/Person array for the given Author post ID.
	 * Empty values are dropped so the output stays clean.
	 */
	public static function build_person_schema( $author_id ) {
		$author = get_post( $author_id );

		if ( ! $author || ABX_AUTHOR_CPT !== $author->post_type ) {
			return array();
		}

		$get = function ( $key ) use ( $author_id ) {
			return get_post_meta( $author_id, $key, true );
		};

		$schema = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'Person',
			'@id'              => get_permalink( $author_id ) . '#person',
			'name'             => get_the_title( $author_id ),
			'url'              => get_permalink( $author_id ),
			'honorificPrefix'  => $get( '_abx_honorific_prefix' ),
			'givenName'        => $get( '_abx_first_name' ),
			'familyName'       => $get( '_abx_last_name' ),
			'honorificSuffix'  => $get( '_abx_honorific_suffix' ),
			'jobTitle'         => $get( '_abx_job_title' ),
			'description'      => self::get_short_description( $author_id ),
			'email'            => $get( '_abx_email' ),
			'telephone'        => $get( '_abx_phone' ),
			'gender'           => $get( '_abx_gender' ),
			'nationality'      => $get( '_abx_nationality' ) ? array(
				'@type' => 'Country',
				'name'  => $get( '_abx_nationality' ),
			) : '',
			'birthDate'        => $get( '_abx_birth_date' ),
		);

		if ( has_post_thumbnail( $author_id ) ) {
			$schema['image'] = wp_get_attachment_image_url( get_post_thumbnail_id( $author_id ), 'full' );
		}

		$knows_about = array_filter( array_map( 'trim', explode( ',', (string) $get( '_abx_knows_about' ) ) ) );
		if ( $knows_about ) {
			$schema['knowsAbout'] = array_values( $knows_about );
		}

		if ( $get( '_abx_org_name' ) ) {
			$org = array(
				'@type' => 'Organization',
				'name'  => $get( '_abx_org_name' ),
			);
			if ( $get( '_abx_org_url' ) ) {
				$org['url'] = $get( '_abx_org_url' );
			}
			$schema['worksFor'] = $org;
		}

		$alumni = array_map(
			function ( $item ) {
				$entry = array(
					'@type' => 'EducationalOrganization',
					'name'  => $item['name'],
				);
				if ( ! empty( $item['url'] ) ) {
					$entry['url'] = $item['url'];
				}
				return $entry;
			},
			self::parse_named_lines( $get( '_abx_alumni_of' ) )
		);
		if ( $alumni ) {
			$schema['alumniOf'] = count( $alumni ) === 1 ? $alumni[0] : $alumni;
		}

		$member_of = array_map(
			function ( $item ) {
				$entry = array(
					'@type' => 'Organization',
					'name'  => $item['name'],
				);
				if ( ! empty( $item['url'] ) ) {
					$entry['url'] = $item['url'];
				}
				return $entry;
			},
			self::parse_named_lines( $get( '_abx_member_of' ) )
		);
		if ( $member_of ) {
			$schema['memberOf'] = count( $member_of ) === 1 ? $member_of[0] : $member_of;
		}

		$awards = self::parse_plain_lines( $get( '_abx_awards' ) );
		if ( $awards ) {
			$schema['award'] = count( $awards ) === 1 ? $awards[0] : $awards;
		}

		$address_fields = array(
			'streetAddress'   => $get( '_abx_address_street' ),
			'addressLocality' => $get( '_abx_address_city' ),
			'addressRegion'   => $get( '_abx_address_region' ),
			'postalCode'      => $get( '_abx_address_postal' ),
			'addressCountry'  => $get( '_abx_address_country' ),
		);
		$address_fields = array_filter( $address_fields );
		if ( $address_fields ) {
			$schema['address'] = array_merge( array( '@type' => 'PostalAddress' ), $address_fields );
		}

		$sameas = self::parse_plain_lines( $get( '_abx_sameas' ) );
		if ( $get( '_abx_url' ) ) {
			array_unshift( $sameas, $get( '_abx_url' ) );
		}
		$sameas = array_values( array_unique( array_filter( $sameas ) ) );
		if ( $sameas ) {
			$schema['sameAs'] = $sameas;
		}

		if ( $get( '_abx_url' ) ) {
			$schema['mainEntityOfPage'] = $get( '_abx_url' );
		}

		/**
		 * Filter the generated Person schema for an author before it's cached/output.
		 *
		 * @param array $schema    The schema.org Person array.
		 * @param int   $author_id Author post ID.
		 */
		return apply_filters( 'abx_person_schema', array_filter( $schema, array( __CLASS__, 'is_not_empty' ) ), $author_id );
	}

	private static function is_not_empty( $value ) {
		return '' !== $value && array() !== $value;
	}

	/**
	 * Renders a <script type="application/ld+json"> tag for the given author.
	 * $schema_type lets callers embed the Person as part of a bigger graph
	 * (e.g. Article.author) by passing an already-built array instead.
	 */
	public static function render_json_ld( $schema ) {
		if ( empty( $schema ) ) {
			return;
		}

		$json = wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		$json = str_replace( '</script>', '<\/script>', $json );

		echo '<script type="application/ld+json" class="abx-schema">' . $json . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
