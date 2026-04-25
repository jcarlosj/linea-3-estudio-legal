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

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

/**
 * Enqueue theme scripts and styles.
 */
function linea3_legal_child_enqueue_styles(): void
{
	wp_enqueue_style(
		'linea3-legal-child-style',
		get_stylesheet_uri(),
		array(),
		filemtime(get_stylesheet_directory() . '/style.css')
	);

	wp_enqueue_script(
		'linea3-legal-child-search',
		get_stylesheet_directory_uri() . '/assets/js/search-toggle.js',
		array(),
		filemtime(get_stylesheet_directory() . '/assets/js/search-toggle.js'),
		true
	);

	wp_enqueue_script(
		'antigravity-modal-strategic',
		get_stylesheet_directory_uri() . '/assets/js/modal-strategic.js',
		array(),
		filemtime(get_stylesheet_directory() . '/assets/js/modal-strategic.js'),
		true
	);
}
add_action('wp_enqueue_scripts', 'linea3_legal_child_enqueue_styles');

/**
 * Perform theme setup.
 */
function linea3_legal_child_setup(): void
{
	add_theme_support('editor-styles');
	add_editor_style('style.css');
}
add_action('after_setup_theme', 'linea3_legal_child_setup');

/**
 * Limit search results to specific post types.
 */
function linea3_legal_child_limit_search_results($query): void
{
	if ($query->is_search() && !is_admin() && $query->is_main_query()) {
		$query->set('post_type', array('post', 'page'));
	}
}
add_action('pre_get_posts', 'linea3_legal_child_limit_search_results');

/**
 * Helper para obtener el HTML de la imagen destacada con fallback.
 */
function antigravity_get_post_thumbnail_html($post_id, $size = 'medium_large', $attr = array()) {
	$html = get_the_post_thumbnail($post_id, $size, $attr);
	if (empty($html)) {
		$placeholder_url = get_stylesheet_directory_uri() . '/assets/images/placeholder-legal.png';
		$class = isset($attr['class']) ? $attr['class'] . ' fallback-image' : 'wp-post-image fallback-image';
		$html = sprintf(
			'<img src="%s" class="%s" alt="" loading="lazy" />',
			esc_url($placeholder_url),
			esc_attr($class)
		);
	}
	return $html;
}

/**
 * Provides a fallback featured image if a post doesn't have one (Filter version).
 */
function linea3_legal_child_fallback_featured_image($html, $post_id, $post_thumbnail_id, $size, $attr)
{
	if (empty($html)) {
		$placeholder_url = get_stylesheet_directory_uri() . '/assets/images/placeholder-legal.png';
		$html = sprintf(
			'<img src="%s" class="attachment-%s size-%s wp-post-image fallback-image" alt="" loading="lazy" />',
			esc_url($placeholder_url),
			esc_attr((string) $size),
			esc_attr((string) $size)
		);
	}
	return $html;
}
add_filter('post_thumbnail_html', 'linea3_legal_child_fallback_featured_image', 10, 5);

/**
 * Shortcode into the search template to display the results count.
 */
function linea3_legal_child_search_result_count()
{
	if (!is_search()) return '';
	global $wp_query;
	$count = (int) $wp_query->found_posts;
	if ($count === 0) return '';
	$label = ($count === 1) ? 'registro encontrado' : 'registros encontrados';
	return sprintf('<p class="search-result-count">Se han visualizado <span class="count-number">%d</span> %s</p>', $count, $label);
}
add_shortcode('search_result_count', 'linea3_legal_child_search_result_count');

/**
 * Inyecta el contenedor HTML del Modal en el footer.
 */
function antigravity_render_strategic_modal()
{
	?>
	<div class="antigravity-modal-overlay">
		<div class="antigravity-modal-content">
			<button class="antigravity-modal-close" aria-label="Cerrar modal">&times;</button>
			<div class="antigravity-modal-header">
				<h3>Agendar Consulta Estratégica</h3>
				<p>Complete el siguiente formulario y un especialista de Linea 3 se pondrá en contacto pronto.</p>
			</div>
			<div class="antigravity-modal-body">
				<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" class="antigravity-modal-form">
					<?php wp_nonce_field('antigravity_consultation_action', 'antigravity_consultation_nonce'); ?>
					<input type="hidden" name="action" value="antigravity_submit_consultation">
					<div class="antigravity-form-group"><label for="consultation-name">Nombre Completo *</label><input type="text" id="consultation-name" name="consultation_name" required></div>
					<div class="antigravity-form-group"><label for="consultation-email">Correo Electrónico *</label><input type="email" id="consultation-email" name="consultation_email" required></div>
					<div class="antigravity-form-group"><label for="consultation-company">Empresa / Organización</label><input type="text" id="consultation-company" name="consultation_company"></div>
					<div class="antigravity-form-group"><label for="consultation-message">Detalles de la Consulta *</label><textarea id="consultation-message" name="consultation_message" rows="4" required></textarea></div>
					<div class="antigravity-form-group submit-group" style="text-align: right;"><button type="submit" class="antigravity-btn-submit">Agendar</button></div>
				</form>
			</div>
		</div>
	</div>
	<?php
}
add_action('wp_footer', 'antigravity_render_strategic_modal');

/**
 * Procesa el envío del formulario de Consulta Estratégica
 */
function antigravity_handle_consultation_form()
{
	if (!isset($_POST['antigravity_consultation_nonce']) || !wp_verify_nonce($_POST['antigravity_consultation_nonce'], 'antigravity_consultation_action')) {
		wp_send_json_error('Fallo de seguridad.');
	}
	$name = sanitize_text_field($_POST['consultation_name'] ?? '');
	$email = sanitize_email($_POST['consultation_email'] ?? '');
	$company = sanitize_text_field($_POST['consultation_company'] ?? '');
	$message = sanitize_textarea_field($_POST['consultation_message'] ?? '');
	if (empty($name) || empty($email) || empty($message) || !is_email($email)) {
		wp_send_json_error('Datos inválidos.');
	}
	$to = 'jcarlosj.dev@gmail.com';
	$subject = 'Consulta Agendada: ' . $company . ' - ' . $name;
	$body = "Nombre: $name\nEmail: $email\nEmpresa: $company\n\nMensaje:\n$message";
	$headers = array('Content-Type: text/plain; charset=UTF-8', 'From: Linea 3 Web <no-reply@linea3legal.com>');
	if (wp_mail($to, $subject, $body, $headers)) {
		wp_send_json_success('Mensaje enviado exitosamente');
	} else {
		wp_send_json_error('Error enviando el correo.');
	}
}
add_action('wp_ajax_nopriv_antigravity_submit_consultation', 'antigravity_handle_consultation_form');
add_action('wp_ajax_antigravity_submit_consultation', 'antigravity_handle_consultation_form');

/**
 * Intercepta los correos salientes en desarrollo local.
 */
function antigravity_mailpit_smtp($phpmailer)
{
	$phpmailer->isSMTP();
	$phpmailer->Host = 'mailpit';
	$phpmailer->SMTPAuth = false;
	$phpmailer->Port = 1025;
}
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
	add_action('phpmailer_init', 'antigravity_mailpit_smtp');
}

/**
 * Registra categorías y patrones de bloques.
 */
function antigravity_register_block_patterns(): void
{
	register_block_pattern_category('antigravity-patterns', array('label' => 'Linea 3 Patterns'));
	register_block_pattern('antigravity/cta-strategic-consultation', array('title' => 'Agendar Consulta', 'categories' => array('antigravity-patterns'), 'content' => '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group"><!-- wp:template-part {"slug":"cta-strategic-consultation","theme":"' . get_stylesheet() . '"} /--></div><!-- /wp:group -->'));
	register_block_pattern('antigravity/nuestro-equipo', array('title' => 'Nuestro Cuerpo Jurídico', 'categories' => array('antigravity-patterns'), 'content' => '<!-- wp:group {"className":"antigravity-team-section","layout":{"type":"constrained"}} --><div class="wp-block-group antigravity-team-section"><!-- wp:shortcode -->[antigravity_featured_posts]<!-- /wp:shortcode --></div><!-- /wp:group -->'));
}
add_action('init', 'antigravity_register_block_patterns');

/**
 * Registro de bloques dinámicos.
 */
function antigravity_register_dynamic_blocks(): void
{
	register_block_type('antigravity/author-card', array('render_callback' => 'antigravity_render_author_card', 'uses_context' => array('postId')));
}
add_action('init', 'antigravity_register_dynamic_blocks');

/**
 * Métodos de contacto del usuario.
 */
function antigravity_add_user_meta_fields($methods): array
{
	$methods['antigravity_user_specialty'] = 'Especialidad';
	$methods['antigravity_user_job_title']  = 'Cargo';
	$methods['antigravity_user_linkedin']  = 'LinkedIn URL';
	$methods['antigravity_user_twitter']   = 'Twitter/X URL';
	return $methods;
}
add_filter('user_contactmethods', 'antigravity_add_user_meta_fields');

/**
 * Helper para obtener el HTML de la tarjeta de autor.
 */
function antigravity_get_author_card_html(int $author_id, int $post_id = 0): string
{
	if (!$author_id) return '';
	$avatar_url = get_avatar_url($author_id, array('size' => 120));
	$name = get_the_author_meta('display_name', $author_id);
	$specialty = get_the_author_meta('antigravity_user_specialty', $author_id);
	$meta_html = '';
	if ($post_id > 0) {
		$date = get_the_date('', $post_id);
		$word_count = str_word_count(strip_tags(get_post_field('post_content', $post_id)));
		$reading_time = max(1, ceil($word_count / 200));
		$meta_html = sprintf('<span class="author-post-meta">%s — %d min de lectura</span>', esc_html($date), $reading_time);
	}
	return sprintf('<div class="antigravity-author-card"><div class="author-avatar-wrapper"><img src="%s" alt="%s" class="author-avatar" /></div><div class="author-data-wrapper"><span class="author-name">%s</span>%s%s</div></div>', esc_url($avatar_url), esc_attr($name), esc_html($name), (!empty($specialty) ? sprintf('<span class="author-specialty">%s</span>', esc_html($specialty)) : ''), $meta_html);
}

/**
 * Renderizado de tarjeta de autor para bloques.
 */
function antigravity_render_author_card($attributes, $content, $block): string
{
	if (!isset($block->context['postId'])) return '';
	return antigravity_get_author_card_html((int)get_post_field('post_author', $block->context['postId']), $block->context['postId']);
}

/**
 * Renderizado de Publicaciones Relacionadas.
 */
function antigravity_related_posts_shortcode() {
	if (!is_singular('post')) return '';
	$posts = get_posts(array('post_type' => 'post', 'posts_per_page' => 3, 'post__not_in' => array(get_the_ID()), 'author' => get_post_field('post_author', get_the_ID()), 'orderby' => 'date', 'order' => 'DESC'));
	if (empty($posts)) return '';
	$author_id = (int)get_post_field('post_author', get_the_ID());
	$output = '<!-- ANTIGRAVITY_START --><section class="related-posts-section"><div class="related-posts-header"><div class="related-header-top"><span class="related-subtitle">MÁS DEL MISMO AUTOR</span><a href="'.esc_url(get_author_posts_url($author_id)).'" class="view-all-link">Ver todas sus publicaciones</a></div><div class="related-header-main"><h2 class="related-title">Publicaciones Relacionadas</h2></div></div><div class="blog-listing-wrapper"><div class="antigravity-grid is-layout-grid columns-3">';
	foreach ($posts as $p) {
		$output .= sprintf('<div class="antigravity-card" onclick="window.location=\'%s\'"><article class="wp-block-group"><div class="wp-block-post-featured-image">%s</div><div class="antigravity-card-content"><div class="wp-block-post-terms">%s</div><h3 class="wp-block-post-title">%s</h3>%s</div></article></div>', get_permalink($p->ID), antigravity_get_post_thumbnail_html($p->ID, 'medium_large', array('class' => 'related-post-img')), get_the_term_list($p->ID, 'category', '', ' ', ''), get_the_title($p->ID), antigravity_get_author_card_html($author_id, $p->ID));
	}
	return $output . '</div></div></section><!-- ANTIGRAVITY_END -->';
}
add_shortcode('antigravity_related_posts', 'antigravity_related_posts_shortcode');

/**
 * Columnas personalizadas en el Admin.
 */
function antigravity_add_posts_columns($columns) {
	$columns['featured_image'] = 'Imagen';
	$columns['has_excerpt'] = 'Extracto';
	return $columns;
}
add_filter('manage_posts_columns', 'antigravity_add_posts_columns');
function antigravity_render_posts_columns($column, $post_id) {
	if ($column === 'featured_image') echo get_the_post_thumbnail($post_id, array(50, 50));
	if ($column === 'has_excerpt') echo has_excerpt($post_id) ? '✔' : '✖';
}
add_action('manage_posts_custom_column', 'antigravity_render_posts_columns', 10, 2);

/**
 * Renderizado de Publicaciones Destacadas.
 */
function antigravity_render_featured_posts_grid(): string
{
	$posts = get_posts(array('post_type' => 'post', 'posts_per_page' => 5, 'orderby' => 'date', 'order' => 'DESC'));
	if (empty($posts)) return '';
	$output = '<!-- ANTIGRAVITY_START --><section class="antigravity-featured-posts-grid"><div class="featured-posts-container"><div class="featured-posts-header"><div class="featured-header-left"><h2 class="featured-title">Publicaciones Destacadas</h2><span class="featured-subtitle">Especialización de alto nivel para blindar cada aspecto de tu organización.</span></div><div class="featured-header-right"><div class="featured-accent-line"></div></div></div><div class="antigravity-grid">';
	foreach ($posts as $p) {
		$cat = get_the_category($p->ID);
		$cat_name = !empty($cat) ? $cat[0]->name : 'Estrategia';
		$thumb = get_the_post_thumbnail_url($p->ID, 'large') ?: get_stylesheet_directory_uri() . '/assets/images/placeholder-legal.png';
		$author_id = (int) $p->post_author;
		$output .= sprintf('<div class="antigravity-card" onclick="window.location=\'%s\'"><div class="featured-card-image-wrap"><img src="%s" alt="%s" class="featured-card-image"></div><div class="featured-card-overlay"></div><div class="featured-card-content"><div class="featured-card-meta"><span class="featured-card-category">%s</span><h3 class="featured-card-title">%s</h3></div><div class="featured-card-author"><div class="author-avatar-wrap">%s</div><span class="author-name">%s</span></div><div class="featured-card-accent-line"></div></div></div>', get_permalink($p->ID), esc_url($thumb), esc_attr($p->post_title), esc_html($cat_name), esc_html($p->post_title), get_avatar($author_id, 32), get_the_author_meta('display_name', $author_id));
	}
	return $output . '</div></div></section><!-- ANTIGRAVITY_END -->';
}
add_shortcode('antigravity_featured_posts', 'antigravity_render_featured_posts_grid');

/**
 * MASTER CLEANER: Elimina párrafos y saltos de línea inyectados.
 */
add_filter('the_content', function($content) {
	$content = preg_replace_callback('/<!-- ANTIGRAVITY_START -->(.*?)<!-- ANTIGRAVITY_END -->/is', function($m) {
		return preg_replace('/<\/?p[^>]*>|<br\s*\/?>/i', '', $m[1]);
	}, $content);
	$content = preg_replace('/<p[^>]*>\s*<!-- ANTIGRAVITY_START -->/i', '<!-- ANTIGRAVITY_START -->', $content);
	$content = preg_replace('/<!-- ANTIGRAVITY_END -->\s*<\/p>/i', '<!-- ANTIGRAVITY_END -->', $content);
	return $content;
}, 9999);

/**
 * Desactivar wpautop en la página frontal.
 */
add_action('wp', function() {
	if (is_front_page()) remove_filter('the_content', 'wpautop');
});
