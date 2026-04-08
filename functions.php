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
		wp_get_theme()->get( 'Version' )
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
