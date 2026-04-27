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
function antigravity_get_post_thumbnail_html($post_id, $size = 'medium_large', $attr = array())
{
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
	if (!is_search())
		return '';
	global $wp_query;
	$count = (int) $wp_query->found_posts;
	if ($count === 0)
		return '';
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
				<h3>Agendar Consulta</h3>
				<p>Complete el siguiente formulario y un especialista de Linea 3 se pondrá en contacto pronto.</p>
			</div>
			<div class="antigravity-modal-body">
				<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST"
					class="antigravity-modal-form">
					<?php wp_nonce_field('antigravity_consultation_action', 'antigravity_consultation_nonce'); ?>
					<input type="hidden" name="action" value="antigravity_submit_consultation">

					<div class="antigravity-form-grid">
						<div class="antigravity-form-group"><label for="consultation-name">Nombre Completo *</label><input
								type="text" id="consultation-name" name="consultation_name" placeholder="Ej: Juan Pérez"
								required></div>
						<div class="antigravity-form-group"><label for="consultation-email">Correo Electrónico
								*</label><input type="email" id="consultation-email" name="consultation_email"
								placeholder="ejemplo@correo.com" required></div>
						<div class="antigravity-form-group"><label for="consultation-phone">Número de Teléfono
								*</label><input type="tel" id="consultation-phone" name="consultation_phone"
								placeholder="+57 300 000 0000" required></div>
						<div class="antigravity-form-group"><label for="consultation-company">Empresa /
								Organización</label><input type="text" id="consultation-company" name="consultation_company"
								placeholder="Nombre de su empresa"></div>
						<div class="antigravity-form-group full-width"><label for="consultation-message">Detalles de la
								Consulta *</label><textarea id="consultation-message" name="consultation_message" rows="4"
								placeholder="¿En qué podemos ayudarle?" required></textarea></div>
					</div>

					<div class="antigravity-form-group submit-group" style="text-align: right;"><button type="submit"
							class="antigravity-btn-submit">Agendar</button></div>
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
	$phone = sanitize_text_field($_POST['consultation_phone'] ?? '');
	$company = sanitize_text_field($_POST['consultation_company'] ?? '');
	$message = sanitize_textarea_field($_POST['consultation_message'] ?? '');
	if (empty($name) || empty($email) || empty($phone) || empty($message) || !is_email($email)) {
		wp_send_json_error('Datos inválidos o incompletos.');
	}
	$to = 'jcarlosj.dev@gmail.com';
	$subject = 'Consulta Agendada: ' . $company . ' - ' . $name;
	$body = "
	<div style='background-color: #0f172a; padding: 40px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
		<div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);'>
			<div style='background-color: #0a2233; padding: 30px; text-align: center; border-bottom: 4px solid #ce9e50;'>
				<img src='" . esc_url(home_url('/wp-content/uploads/logo-horizontal-oscuro.png')) . "' alt='Línea 3 Estudio Legal' style='max-width: 200px; height: auto;'>
			</div>
			<div style='padding: 40px; color: #334155; line-height: 1.6;'>
				<h2 style='color: #0a2233; margin-top: 0; font-size: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;'>Nueva Consulta</h2>
				
				<div style='margin-bottom: 25px;'>
					<p style='margin: 5px 0;'><strong style='color: #0a2233;'>Nombre:</strong> " . esc_html($name) . "</p>
					<p style='margin: 5px 0;'><strong style='color: #0a2233;'>Email:</strong> <a href='mailto:" . esc_attr($email) . "' style='color: #ce9e50; text-decoration: none;'>" . esc_html($email) . "</a></p>
					<p style='margin: 5px 0;'><strong style='color: #0a2233;'>Teléfono:</strong> " . esc_html($phone) . "</p>
					<p style='margin: 5px 0;'><strong style='color: #0a2233;'>Empresa:</strong> " . esc_html($company) . "</p>
				</div>

				<div style='background-color: #f8fafc; padding: 25px; border-left: 4px solid #ce9e50; border-radius: 4px;'>
					<h3 style='margin-top: 0; margin-bottom: 10px; font-size: 16px; color: #0a2233; text-transform: uppercase; letter-spacing: 0.5px;'>Detalles de la Consulta</h3>
					<p style='margin: 0; white-space: pre-wrap; color: #475569;'>" . nl2br(esc_html($message)) . "</p>
				</div>
				
				<div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center;'>
					<p style='font-size: 12px; color: #94a3b8; margin: 0;'>Este es un mensaje automático generado desde el portal web de Linea 3 Estudio Legal.</p>
				</div>
			</div>
		</div>
	</div>";
	$headers = array('Content-Type: text/html; charset=UTF-8', 'From: Linea 3 Web <no-reply@linea3legal.com>');
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
 * Shortcode para mapa dinámico y fácil de editar.
 * Uso: [antigravity_map address="Calle 93 #11-13, Bogota"]
 */
function antigravity_map_shortcode($atts) {
	$atts = shortcode_atts(array(
		'address' => 'Calle 93 #11-13, Bogota',
		'zoom' => '15'
	), $atts);
	$address = urlencode($atts['address']);
	return '<div class="antigravity-location-map-container">
		<iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
		src="https://maps.google.com/maps?q=' . $address . '&t=&z=' . $atts['zoom'] . '&ie=UTF8&iwloc=&output=embed"></iframe>
	</div>';
}
add_shortcode('antigravity_map', 'antigravity_map_shortcode');

/**
 * Registra categorías y patrones de bloques.
 */
function antigravity_register_block_patterns(): void
{
	register_block_pattern_category('antigravity-patterns', array('label' => 'Linea 3 Patterns'));
	register_block_pattern('antigravity/cta-strategic-consultation', array('title' => 'Agendar Consulta', 'categories' => array('antigravity-patterns'), 'content' => '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group"><!-- wp:template-part {"slug":"cta-strategic-consultation","theme":"' . get_stylesheet() . '"} /--></div><!-- /wp:group -->'));
	register_block_pattern('antigravity/nuestro-equipo', array('title' => 'Nuestro Cuerpo Jurídico', 'categories' => array('antigravity-patterns'), 'content' => '<!-- wp:group {"className":"antigravity-team-section","layout":{"type":"constrained"}} --><div class="wp-block-group antigravity-team-section"><!-- wp:shortcode -->[antigravity_featured_posts]<!-- /wp:shortcode --></div><!-- /wp:group -->'));
	register_block_pattern('antigravity/hero-editorial', array(
		'title' => 'Hero Editorial',
		'categories' => array('antigravity-patterns'),
		'content' => '<!-- wp:cover {"dimRatio":80,"overlayColor":"base","minHeight":85,"minHeightUnit":"vh","align":"full","className":"antigravity-hero-editorial","layout":{"type":"constrained"}} -->
<div class="wp-block-cover alignfull antigravity-hero-editorial" style="min-height:85vh"><span aria-hidden="true" class="wp-block-cover__background has-base-background-color has-background-dim-80 has-background-dim"></span><div class="wp-block-cover__inner-container">
    <div class="wp-block-group l3-container-standard">
        <!-- wp:group {"className":"hero-content-wrapper","layout":{"type":"constrained","justifyContent":"left"}} -->
        <div class="wp-block-group hero-content-wrapper">
        <!-- wp:separator {"className":"hero-vertical-line"} -->
        <hr class="wp-block-separator has-alpha-channel-opacity hero-vertical-line"/>
        <!-- /wp:separator -->
        <!-- wp:heading {"level":1,"className":"hero-title","style":{"typography":{"lineHeight":"1.1"}},"fontFamily":"serif"} -->
        <h1 class="wp-block-heading hero-title has-serif-font-family" style="line-height:1.1">Nuestra Misión<br>y <span class="accent">Excelencia</span></h1>
        <!-- /wp:heading -->
        <!-- wp:paragraph {"className":"hero-description"} -->
        <p class="hero-description">En Línea 3, concebimos el ejercicio del derecho como un Soberano Archivo de conocimiento. Somos los guardianes de la estructura legal de nuestros representados.</p>
        <!-- /wp:paragraph -->
        <!-- wp:buttons {"className":"hero-buttons"} -->
        <div class="wp-block-buttons hero-buttons">
            <!-- wp:button {"className":"btn-primary-gold"} -->
            <div class="wp-block-button btn-primary-gold"><a class="wp-block-button__link wp-element-button">INICIAR ALIANZA</a></div>
            <!-- /wp:button -->
            <!-- wp:button {"className":"btn-outline-white"} -->
            <div class="wp-block-button btn-outline-white"><a class="wp-block-button__link wp-element-button">NUESTRA FIRMA</a></div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:buttons -->
    </div>
    <!-- /wp:group -->
    </div>
</div></div>
<!-- /wp:cover -->'
	));
	register_block_pattern('antigravity/mapa-sede', array(
		'title' => 'Mapa - Nuestra Sede (Fácil de Editar)',
		'categories' => array('antigravity-patterns'),
		'keywords' => array('mapa', 'sede', 'bogota', 'ubicacion', 'contacto', 'foto'),
		'content' => '<!-- wp:group {"align":"full","className":"antigravity-location-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull antigravity-location-section">
    <!-- wp:columns {"align":"wide","className":"l3-container-standard","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|50"}}}} -->
    <div class="wp-block-columns alignwide l3-container-standard">
        <!-- wp:column {"width":"40%","className":"location-content-col"} -->
        <div class="wp-block-column location-content-col" style="flex-basis:40%">
            <!-- wp:group {"className":"location-header","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left"}} -->
            <div class="wp-block-group location-header">
                <!-- wp:group {"className":"section-vertical-line"} -->
                <div class="wp-block-group section-vertical-line"></div>
                <!-- /wp:group -->

                <!-- wp:group {"className":"location-header-content"} -->
                <div class="wp-block-group location-header-content">
                    <!-- wp:paragraph {"className":"location-eyebrow"} -->
                    <p class="location-eyebrow">UBICACIÓN</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:heading {"level":2,"className":"location-title"} -->
                    <h2 class="wp-block-heading location-title">Nuestra Sede en Bogotá</h2>
                    <!-- /wp:heading -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->
            
            <!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"location-photo-wrap"} -->
            <figure class="wp-block-image size-large location-photo-wrap"><img src="' . get_stylesheet_directory_uri() . '/assets/images/oficina-bogota.png" alt="Sede Linea 3 Bogotá"/></figure>
            <!-- /wp:image -->

            <!-- wp:group {"className":"location-info-row","layout":{"type":"constrained"}} -->
            <div class="wp-block-group location-info-row">
                <!-- wp:group {"className":"info-block info-main"} -->
                <div class="wp-block-group info-block info-main">
                    <!-- wp:heading {"level":3,"className":"info-label"} -->
                    <h3 class="wp-block-heading info-label">Dirección Principal</h3>
                    <!-- /wp:heading -->
                    <!-- wp:paragraph {"className":"info-text"} -->
                    <p class="info-text">Calle 93 #11-13, Edificio Nou<br>Piso 5, Oficina 502<br>Bogotá D.C., Colombia</p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->

                <!-- wp:group {"className":"info-block info-contact"} -->
                <div class="wp-block-group info-block info-contact">
                    <!-- wp:heading {"level":3,"className":"info-label"} -->
                    <h3 class="wp-block-heading info-label">Contacto</h3>
                    <!-- /wp:heading -->
                    <!-- wp:paragraph {"className":"info-text"} -->
                    <p class="info-text">bogota@linea3legal.com<br>+57 601 745 8900</p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->

                <!-- wp:group {"className":"info-block info-hours"} -->
                <div class="wp-block-group info-block info-hours">
                    <!-- wp:heading {"level":3,"className":"info-label"} -->
                    <h3 class="wp-block-heading info-label">Horario</h3>
                    <!-- /wp:heading -->
                    <!-- wp:paragraph {"className":"info-text"} -->
                    <p class="info-text">Lunes a Viernes<br>8:00 AM — 6:00 PM</p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->
            <!-- wp:buttons -->
            <div class="wp-block-buttons">
                <!-- wp:button {"className":"btn-agendar-visita"} -->
                <div class="wp-block-button btn-agendar-visita"><a class="wp-block-button__link wp-element-button antigravity-modal-trigger">AGENDAR VISITA</a></div>
                <!-- /wp:button -->
            </div>
            <!-- /wp:buttons -->
        </div>
        <!-- /wp:column -->
        <!-- wp:column {"width":"60%"} -->
        <div class="wp-block-column" style="flex-basis:60%">
            <!-- wp:shortcode -->
            [antigravity_map address="Calle 93 #11-13, Bogota"]
            <!-- /wp:shortcode -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->'
	));
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
	$methods['antigravity_user_job_title'] = 'Cargo';
	$methods['antigravity_user_linkedin'] = 'LinkedIn URL';
	$methods['antigravity_user_twitter'] = 'Twitter/X URL';
	return $methods;
}
add_filter('user_contactmethods', 'antigravity_add_user_meta_fields');

/**
 * Añade campos adicionales al perfil (Extracto Profesional)
 */
function antigravity_show_extra_profile_fields($user)
{
	?>
	<h3><?php _e('Información Adicional (Linea 3)', 'linea3-legal-child'); ?></h3>
	<table class="form-table">
		<tr>
			<th><label for="antigravity_user_excerpt"><?php _e('Extracto Profesional', 'linea3-legal-child'); ?></label>
			</th>
			<td>
				<textarea name="antigravity_user_excerpt" id="antigravity_user_excerpt" rows="5"
					cols="30"><?php echo esc_textarea(get_the_author_meta('antigravity_user_excerpt', $user->ID)); ?></textarea>
				<p class="description">
					<?php _e('Breve descripción que aparece en la tarjeta del profesional.', 'linea3-legal-child'); ?></p>
			</td>
		</tr>
	</table>
	<?php
}
add_action('show_user_profile', 'antigravity_show_extra_profile_fields');
add_action('edit_user_profile', 'antigravity_show_extra_profile_fields');

/**
 * Guarda los campos adicionales del perfil
 */
function antigravity_save_extra_profile_fields($user_id)
{
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	}
	if (isset($_POST['antigravity_user_excerpt'])) {
		update_user_meta($user_id, 'antigravity_user_excerpt', sanitize_textarea_field($_POST['antigravity_user_excerpt']));
	}
}
add_action('personal_options_update', 'antigravity_save_extra_profile_fields');
add_action('edit_user_profile_update', 'antigravity_save_extra_profile_fields');

/**
 * Helper para obtener el HTML de la tarjeta de autor.
 */
function antigravity_get_author_card_html(int $author_id, int $post_id = 0): string
{
	if (!$author_id)
		return '';
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
	if (!isset($block->context['postId']))
		return '';
	return antigravity_get_author_card_html((int) get_post_field('post_author', $block->context['postId']), $block->context['postId']);
}

/**
 * Shortcode para el Cuadro de Autor en Entradas Individuales (Retrocompatibilidad).
 */
function antigravity_post_author_box_shortcode()
{
	global $post;
	$author_id = (int) get_the_author_meta('ID');
	if (!$author_id && $post) {
		$author_id = (int) $post->post_author;
	}
	if (!$author_id)
		return '';

	$html = antigravity_get_author_card_html($author_id, $post ? $post->ID : 0);
	// Añadimos la clase legacy para asegurar compatibilidad con estilos específicos de single post
	return str_replace('antigravity-author-card', 'antigravity-author-card single-post-author-box', $html);
}
add_shortcode('antigravity_post_author_box', 'antigravity_post_author_box_shortcode');

/**
 * Renderizado de Publicaciones Relacionadas.
 */
function antigravity_related_posts_shortcode()
{
	if (!is_singular('post'))
		return '';
	$posts = get_posts(array('post_type' => 'post', 'posts_per_page' => 3, 'post__not_in' => array(get_the_ID()), 'author' => get_post_field('post_author', get_the_ID()), 'orderby' => 'date', 'order' => 'DESC'));
	if (empty($posts))
		return '';
	$author_id = (int) get_post_field('post_author', get_the_ID());
	$output = '<!-- ANTIGRAVITY_START --><section class="related-posts-section"><div class="related-posts-header"><div class="section-vertical-line"></div><div class="related-header-content"><div class="related-header-top"><span class="related-subtitle">MÁS DEL MISMO AUTOR</span><a href="' . esc_url(get_author_posts_url($author_id)) . '" class="view-all-link">Ver todas sus publicaciones</a></div><div class="related-header-main"><h2 class="related-title">Publicaciones Relacionadas</h2></div></div></div><div class="blog-listing-wrapper"><div class="antigravity-grid is-layout-grid columns-3">';
	foreach ($posts as $p) {
		$output .= sprintf('<div class="antigravity-card" onclick="window.location=\'%s\'"><article class="wp-block-group"><div class="wp-block-post-featured-image">%s</div><div class="antigravity-card-content"><div class="wp-block-post-terms">%s</div><h3 class="wp-block-post-title">%s</h3>%s</div></article></div>', get_permalink($p->ID), antigravity_get_post_thumbnail_html($p->ID, 'medium_large', array('class' => 'related-post-img')), get_the_term_list($p->ID, 'category', '', ' ', ''), get_the_title($p->ID), antigravity_get_author_card_html($author_id, $p->ID));
	}
	return $output . '</div></div></section><!-- ANTIGRAVITY_END -->';
}
add_shortcode('antigravity_related_posts', 'antigravity_related_posts_shortcode');

/**
 * Renderizado de la cuadrícula de equipo (Nuestro Cuerpo Jurídico).
 */
function antigravity_render_team_grid($attributes): string
{
	// Filtrar usuarios activos con rol 'author'.
	$args = array(
		'role__in' => array('author'),
		'orderby' => 'display_name',
		'order' => 'ASC',
	);

	$users = get_users($args);

	// Inyectamos el Header directamente en el renderizado dinámico para asegurar su presencia
	$output = '<div class="antigravity-team-section">';

	$output .= '<div class="team-section-header">';
	$output .= '<div class="section-vertical-line"></div>';
	$output .= '<div class="team-header-content">';
	$output .= '<div class="team-header-left">';
	$output .= '<h2 class="team-title">' . esc_html__('Nuestro Cuerpo Jurídico', 'linea3-legal-child') . '</h2>';
	$output .= '<p class="team-subtitle">' . esc_html__('LIDERAZGO Y ESTRATEGIA', 'linea3-legal-child') . '</p>';
	$output .= '</div>';
	$output .= '<div class="team-header-right">';
	$output .= '<p class="team-corporate-phrase">' . esc_html__('“La justicia no es solo una norma, es la arquitectura de una sociedad estable.”', 'linea3-legal-child') . '</p>';
	$output .= '</div>';
	$output .= '</div>'; // .team-header-content
	$output .= '</div>'; // .team-section-header

	if (empty($users)) {
		$output .= '<p class="linea3-team-empty">' . esc_html__('No hay profesionales disponibles para mostrar.', 'linea3-legal-child') . '</p>';
		$output .= '</div>';
		return $output;
	}

	$output .= '<div class="linea3-team-grid">';

	foreach ($users as $user) {
		$user_id = $user->ID;
		$avatar_url = get_avatar_url($user_id, array('size' => 400));
		$name = $user->display_name;
		$specialty = get_the_author_meta('antigravity_user_specialty', $user_id);
		$job_title = get_the_author_meta('antigravity_user_job_title', $user_id);
		$linkedin = get_the_author_meta('antigravity_user_linkedin', $user_id);
		$twitter = get_the_author_meta('antigravity_user_twitter', $user_id);

		$output .= '<div class="linea3-team-card">';

		// Imagen (Cuadrada/Rectangular con padding CSS)
		$output .= '<div class="linea3-team-card-image-wrap">';
		$output .= sprintf(
			'<img src="%s" alt="%s" class="linea3-team-card-image" />',
			esc_url($avatar_url),
			esc_attr($name)
		);
		$output .= '</div>';

		// Contenido interno alineado a la izquierda
		$output .= '<div class="linea3-team-card-content">';

		if (!empty($job_title)) {
			$output .= sprintf('<p class="linea3-team-job-title">%s</p>', esc_html($job_title));
		}

		$output .= sprintf('<h3 class="linea3-team-name">%s</h3>', esc_html($name));

		if (!empty($specialty)) {
			$output .= sprintf('<p class="linea3-team-specialty">%s</p>', esc_html($specialty));
		}

		// Redes sociales alineadas a la izquierda
		$output .= '<div class="linea3-team-socials">';
		$output .= '<div class="linea3-team-social-icons">';

		$output .= '<span class="linea3-team-icon-share"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg></span>';

		if (!empty($user->user_email)) {
			$output .= sprintf('<a href="mailto:%s" class="linea3-team-icon-email" aria-label="Email"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></a>', esc_attr($user->user_email));
		}

		if (!empty($linkedin)) {
			$output .= sprintf('<a href="%s" target="_blank" rel="noopener noreferrer" class="linea3-team-icon-linkedin" aria-label="LinkedIn"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg></a>', esc_url($linkedin));
		}

		if (!empty($twitter)) {
			$output .= sprintf('<a href="%s" target="_blank" rel="noopener noreferrer" class="linea3-team-icon-twitter" aria-label="Twitter"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path></svg></a>', esc_url($twitter));
		}

		$output .= '</div>';
		$output .= '</div>';

		$output .= '</div>';
		$output .= '</div>';
	}

	$output .= '</div>'; // .linea3-team-grid
	$output .= '</div>'; // .antigravity-team-section

	return $output;
}
add_shortcode('antigravity_team_grid', 'antigravity_render_team_grid');

/**
 * Columnas personalizadas en el Admin.
 */
function antigravity_add_posts_columns($columns)
{
	$columns['featured_image'] = 'Imagen';
	$columns['has_excerpt'] = 'Extracto';
	return $columns;
}
add_filter('manage_posts_columns', 'antigravity_add_posts_columns');
function antigravity_render_posts_columns($column, $post_id)
{
	if ($column === 'featured_image')
		echo get_the_post_thumbnail($post_id, array(50, 50));
	if ($column === 'has_excerpt')
		echo has_excerpt($post_id) ? '✔' : '✖';
}
add_action('manage_posts_custom_column', 'antigravity_render_posts_columns', 10, 2);

/**
 * Renderizado de Publicaciones Destacadas.
 */
function antigravity_render_featured_posts_grid(): string
{
	$posts = get_posts(array('post_type' => 'post', 'posts_per_page' => 5, 'orderby' => 'date', 'order' => 'DESC'));
	if (empty($posts))
		return '';
	$output = '<!-- ANTIGRAVITY_START --><section class="antigravity-featured-posts-grid"><div class="featured-posts-container l3-container-standard"><div class="featured-posts-header"><div class="section-vertical-line"></div><div class="featured-header-left"><span class="featured-eyebrow">Publicaciones de los expertos de nuestro equipo</span><h2 class="featured-title">Publicaciones Destacadas</h2><p class="featured-description">Especialización de alto nivel para blindar cada aspecto de tu organización.</p></div><div class="featured-header-right"><a href="' . esc_url(get_permalink(get_option('page_for_posts'))) . '" class="view-all-link">Ver todas</a></div></div><div class="antigravity-grid">';
	foreach ($posts as $p) {
		$cat = get_the_category($p->ID);
		$cat_name = !empty($cat) ? $cat[0]->name : 'Estrategia';
		$thumb = get_the_post_thumbnail_url($p->ID, 'large') ?: get_stylesheet_directory_uri() . '/assets/images/placeholder-legal.png';
		$author_id = (int) $p->post_author;
		$output .= sprintf('<div class="antigravity-card" onclick="window.location=\'%s\'"><div class="featured-card-image-wrap"><img src="%s" alt="%s" class="featured-card-image"></div><div class="featured-card-overlay"></div><div class="featured-card-content"><div class="featured-card-meta"><span class="featured-card-category">%s</span><h3 class="featured-card-title">%s</h3></div><div class="featured-card-author">%s</div><div class="featured-card-accent-line"></div></div></div>', get_permalink($p->ID), esc_url($thumb), esc_attr($p->post_title), esc_html($cat_name), esc_html($p->post_title), antigravity_get_author_card_html($author_id));
	}
	return $output . '</div></div></section><!-- ANTIGRAVITY_END -->';
}
add_shortcode('antigravity_featured_posts', 'antigravity_render_featured_posts_grid');

/**
 * MASTER CLEANER: Elimina párrafos y saltos de línea inyectados.
 */
add_filter('the_content', function ($content) {
	$content = preg_replace_callback('/<!-- ANTIGRAVITY_START -->(.*?)<!-- ANTIGRAVITY_END -->/is', function ($m) {
		return preg_replace('/<\/?p[^>]*>|<br\s*\/?>/i', '', $m[1]);
	}, $content);
	$content = preg_replace('/<p[^>]*>\s*<!-- ANTIGRAVITY_START -->/i', '<!-- ANTIGRAVITY_START -->', $content);
	$content = preg_replace('/<!-- ANTIGRAVITY_END -->\s*<\/p>/i', '<!-- ANTIGRAVITY_END -->', $content);
	return $content;
}, 9999);

/**
 * Desactivar wpautop en la página frontal.
 */
add_action('wp', function () {
	if (is_front_page())
		remove_filter('the_content', 'wpautop');
});
