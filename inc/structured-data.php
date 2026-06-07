<?php
/**
 * Lightweight JSON-LD structured data: Organization, BreadcrumbList, Article.
 *
 * Course schema is intentionally left to Tutor LMS (it emits its own Course
 * markup on course pages), and the whole module yields to a dedicated SEO
 * plugin — Yoast / Rank Math / SEOPress / AIOSEO — when one is active, so we
 * never duplicate their graph.
 *
 * @package BIA_Learn
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a dedicated SEO plugin is already producing structured data.
 *
 * @return bool
 */
function bia_learn_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' )
		|| class_exists( 'RankMath' )
		|| defined( 'SEOPRESS_VERSION' )
		|| function_exists( 'aioseo' );
}

/**
 * Build the Organization node (site-wide publisher identity).
 *
 * @return array
 */
function bia_learn_schema_organization() {
	$org = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);

	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$src = wp_get_attachment_image_src( $logo_id, 'full' );
		if ( $src ) {
			$org['logo'] = array(
				'@type'  => 'ImageObject',
				'url'    => $src[0],
				'width'  => $src[1],
				'height' => $src[2],
			);
		}
	}

	$socials = array_filter(
		array(
			bia_learn_option( 'bia_social_facebook' ),
			bia_learn_option( 'bia_social_youtube' ),
			bia_learn_option( 'bia_social_line' ),
		)
	);
	if ( $socials ) {
		$org['sameAs'] = array_values( $socials );
	}

	return $org;
}

/**
 * Build the BreadcrumbList node for the current view (mirrors the visual
 * breadcrumb in bia_learn_breadcrumb()). Returns null on the front page.
 *
 * @return array|null
 */
function bia_learn_schema_breadcrumb() {
	if ( is_front_page() ) {
		return null;
	}

	$items   = array();
	$items[] = array(
		'name' => __( 'หน้าแรก', 'bia-learn' ),
		'url'  => home_url( '/' ),
	);

	if ( is_singular( 'post' ) ) {
		$blog_id = (int) get_option( 'page_for_posts' );
		if ( $blog_id ) {
			$items[] = array(
				'name' => get_the_title( $blog_id ),
				'url'  => get_permalink( $blog_id ),
			);
		}
		$items[] = array(
			'name' => get_the_title(),
			'url'  => get_permalink(),
		);
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
			$items[] = array(
				'name' => get_the_title( $ancestor ),
				'url'  => get_permalink( $ancestor ),
			);
		}
		$items[] = array(
			'name' => get_the_title(),
			'url'  => get_permalink(),
		);
	} elseif ( is_singular() ) {
		$items[] = array(
			'name' => get_the_title(),
			'url'  => get_permalink(),
		);
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$items[] = array(
			'name' => single_term_title( '', false ),
			'url'  => '',
		);
	} elseif ( is_post_type_archive() ) {
		$items[] = array(
			'name' => post_type_archive_title( '', false ),
			'url'  => '',
		);
	} elseif ( is_archive() ) {
		$items[] = array(
			'name' => wp_strip_all_tags( get_the_archive_title() ),
			'url'  => '',
		);
	} elseif ( is_search() ) {
		/* translators: %s: search term */
		$items[] = array(
			'name' => sprintf( __( 'ผลการค้นหา: %s', 'bia-learn' ), get_search_query() ),
			'url'  => '',
		);
	} else {
		return null;
	}

	$elements = array();
	foreach ( $items as $pos => $item ) {
		$entry = array(
			'@type'    => 'ListItem',
			'position' => $pos + 1,
			'name'     => $item['name'],
		);
		if ( ! empty( $item['url'] ) ) {
			$entry['item'] = $item['url'];
		}
		$elements[] = $entry;
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $elements,
	);
}

/**
 * Build the Article node for a single post.
 *
 * @return array|null
 */
function bia_learn_schema_article() {
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return null;
	}

	$article = array(
		'@type'            => 'Article',
		'@id'              => get_permalink() . '#article',
		'headline'         => wp_strip_all_tags( get_the_title() ),
		'datePublished'    => get_the_date( 'c' ),
		'dateModified'     => get_the_modified_date( 'c' ),
		'url'              => get_permalink(),
		'mainEntityOfPage' => get_permalink(),
		'author'           => array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ),
		),
		'publisher'        => array( '@id' => home_url( '/#organization' ) ),
	);

	if ( has_post_thumbnail() ) {
		$src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'bia-hero' );
		if ( $src ) {
			$article['image'] = array(
				'@type'  => 'ImageObject',
				'url'    => $src[0],
				'width'  => $src[1],
				'height' => $src[2],
			);
		}
	}

	$excerpt = wp_strip_all_tags( get_the_excerpt() );
	if ( $excerpt ) {
		$article['description'] = $excerpt;
	}

	return $article;
}

/**
 * Print the assembled JSON-LD graph in the document head.
 */
function bia_learn_print_schema() {
	if ( bia_learn_seo_plugin_active() ) {
		return;
	}

	$graph = array( bia_learn_schema_organization() );

	$breadcrumb = bia_learn_schema_breadcrumb();
	if ( $breadcrumb ) {
		$graph[] = $breadcrumb;
	}

	if ( is_singular( 'post' ) ) {
		$article = bia_learn_schema_article();
		if ( $article ) {
			$graph[] = $article;
		}
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	// JSON_HEX_TAG neutralises any stray </script> inside titles/excerpts.
	echo "\n" . '<script type="application/ld+json">'
		. wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG )
		. '</script>' . "\n";
}
add_action( 'wp_head', 'bia_learn_print_schema', 20 );
