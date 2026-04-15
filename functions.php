<?php
/**
 * Linea 3 Estudio Legal Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package linea3-legal-child
 * @since 1.0.0
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Enqueue theme scripts and styles.
 *
 * @return void
 */
function linea3_legal_child_enqueue_styles(): void {
	// Block themes with theme.json usually don't need to manually enqueue the parent stylesheet
	// because WP loads the parent's block styles automatically.
	// However, if the parent has custom arbitrary CSS in its style.css, it may be needed.
	// In the case of Twenty Twenty-Five it's fully block-based.
	// We'll enqueue the child theme's style.css to ensure utility classes are loaded.
	wp_enqueue_style(
		'linea3-legal-child-style',
		get_stylesheet_uri(),
		array(),
		filemtime( get_stylesheet_directory() . '/style.css' ) // Cache-busting automático
	);

	// Enqueue search toggle script
	wp_enqueue_script(
		'linea3-legal-child-search',
		get_stylesheet_directory_uri() . '/assets/js/search-toggle.js',
		array(),
		filemtime( get_stylesheet_directory() . '/assets/js/search-toggle.js' ),
		true // Load in footer
	);
}
add_action( 'wp_enqueue_scripts', 'linea3_legal_child_enqueue_styles' );

/**
 * Perform theme setup.
 *
 * @return void
 */
function linea3_legal_child_setup(): void {
	// Add support for editor styles.
	add_theme_support( 'editor-styles' );
	
	// Enqueue editor styles for Block Editor.
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'linea3_legal_child_setup' );

// Espacio reservado para futuros hooks, filtros y registro de variaciones de bloques.
// Mantenlo limpio y modular.

/**
 * Limit search results to specific post types to avoid template parts repetition.
 *
 * @param WP_Query $query The query object.
 * @return void
 */
function linea3_legal_child_limit_search_results( $query ): void {
	if ( $query->is_search() && ! is_admin() && $query->is_main_query() ) {
		$query->set( 'post_type', array( 'post', 'page' ) );
	}
}
add_action( 'pre_get_posts', 'linea3_legal_child_limit_search_results' );

/**
 * Provides a fallback featured image if a post doesn't have one.
 *
 * @param string       $html              The post thumbnail HTML.
 * @param int          $post_id           The post ID.
 * @param int|string   $post_thumbnail_id The post thumbnail ID.
 * @param string|array $size              The post thumbnail size.
 * @param string       $attr              Query string of attributes.
 * @return string The modified post thumbnail HTML.
 */
function linea3_legal_child_fallback_featured_image( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
	if ( empty( $html ) ) {
		$placeholder_url = get_stylesheet_directory_uri() . '/assets/images/placeholder-legal.png';
		$html = sprintf(
			'<img src="%s" class="attachment-%s size-%s wp-post-image fallback-image" alt="" loading="lazy" />',
			esc_url( $placeholder_url ),
			esc_attr( (string) $size ),
			esc_attr( (string) $size )
		);
	}
	return $html;
}
add_filter( 'post_thumbnail_html', 'linea3_legal_child_fallback_featured_image', 10, 5 );

/**
 * Shortcode into the search template to display the results count.
 */
function linea3_legal_child_search_result_count() {
	if ( ! is_search() ) {
		return '';
	}
	global $wp_query;
	$count = (int) $wp_query->found_posts;
	
	if ( $count === 0 ) {
		return '';
	}

	$label = ( $count === 1 ) ? 'registro encontrado' : 'registros encontrados';
	
	return sprintf(
		'<p class="search-result-count">Se han visualizado <span class="count-number">%d</span> %s</p>',
		$count,
		$label
	);
}
add_shortcode( 'search_result_count', 'linea3_legal_child_search_result_count' );
