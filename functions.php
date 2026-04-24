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
 *
 * @return void
 */
function linea3_legal_child_enqueue_styles(): void
{
	// Block themes with theme.json usually don't need to manually enqueue the parent stylesheet
	// because WP loads the parent's block styles automatically.
	// However, if the parent has custom arbitrary CSS in its style.css, it may be needed.
	// In the case of Twenty Twenty-Five it's fully block-based.
	// We'll enqueue the child theme's style.css to ensure utility classes are loaded.
	wp_enqueue_style(
		'linea3-legal-child-style',
		get_stylesheet_uri(),
		array(),
		filemtime(get_stylesheet_directory() . '/style.css') // Cache-busting automático
	);

	// Enqueue search toggle script
	wp_enqueue_script(
		'linea3-legal-child-search',
		get_stylesheet_directory_uri() . '/assets/js/search-toggle.js',
		array(),
		filemtime(get_stylesheet_directory() . '/assets/js/search-toggle.js'),
		true // Load in footer
	);

	// Enqueue modal strategic script
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
 *
 * @return void
 */
function linea3_legal_child_setup(): void
{
	// Add support for editor styles.
	add_theme_support('editor-styles');

	// Enqueue editor styles for Block Editor.
	add_editor_style('style.css');
}
add_action('after_setup_theme', 'linea3_legal_child_setup');

// Espacio reservado para futuros hooks, filtros y registro de variaciones de bloques.
// Mantenlo limpio y modular.

/**
 * Limit search results to specific post types to avoid template parts repetition.
 *
 * @param WP_Query $query The query object.
 * @return void
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
	if (!is_search()) {
		return '';
	}
	global $wp_query;
	$count = (int) $wp_query->found_posts;

	if ($count === 0) {
		return '';
	}

	$label = ($count === 1) ? 'registro encontrado' : 'registros encontrados';

	return sprintf(
		'<p class="search-result-count">Se han visualizado <span class="count-number">%d</span> %s</p>',
		$count,
		$label
	);
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
				<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST"
					class="antigravity-modal-form">
					<?php wp_nonce_field('antigravity_consultation_action', 'antigravity_consultation_nonce'); ?>
					<input type="hidden" name="action" value="antigravity_submit_consultation">

					<div class="antigravity-form-group">
						<label for="consultation-name">Nombre Completo *</label>
						<input type="text" id="consultation-name" name="consultation_name" required>
					</div>

					<div class="antigravity-form-group">
						<label for="consultation-email">Correo Electrónico *</label>
						<input type="email" id="consultation-email" name="consultation_email" required>
					</div>

					<div class="antigravity-form-group">
						<label for="consultation-company">Empresa / Organización</label>
						<input type="text" id="consultation-company" name="consultation_company">
					</div>

					<div class="antigravity-form-group">
						<label for="consultation-message">Detalles de la Consulta *</label>
						<textarea id="consultation-message" name="consultation_message" rows="4" required></textarea>
					</div>

					<div class="antigravity-form-group submit-group" style="text-align: right;">
						<button type="submit" class="antigravity-btn-submit">Agendar</button>
					</div>
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
	// 1. Verificar Nonce por seguridad
	if (!isset($_POST['antigravity_consultation_nonce']) || !wp_verify_nonce($_POST['antigravity_consultation_nonce'], 'antigravity_consultation_action')) {
		wp_send_json_error('Fallo de seguridad: Sesión expirada o token inválido.');
	}

	// 2. Sanitizar datos de entrada
	$name = isset($_POST['consultation_name']) ? sanitize_text_field($_POST['consultation_name']) : '';
	$email = isset($_POST['consultation_email']) ? sanitize_email($_POST['consultation_email']) : '';
	$company = isset($_POST['consultation_company']) ? sanitize_text_field($_POST['consultation_company']) : '';
	$message = isset($_POST['consultation_message']) ? sanitize_textarea_field($_POST['consultation_message']) : '';

	// 3. Validar obligatorios básicos
	if (empty($name) || empty($email) || empty($message)) {
		wp_send_json_error('Por favor, complete los campos obligatorios.');
	}

	if (!is_email($email)) {
		wp_send_json_error('Por favor, ingrese un correo válido.');
	}

	// 4. Configurar Correo Electrónico
	$to = 'jcarlosj.dev@gmail.com'; // Puedes cambiar esto por el correo del cliente
	$subject = 'Consulta Agendada: ' . $company . ' - ' . $name;

	$body = "Has recibido una nueva solicitud de Consulta Agendada desde la web.\n\n";
	$body .= "Datos del Contacto:\n";
	$body .= "------------------------\n";
	$body .= "Nombre Completo: $name\n";
	$body .= "Correo Electrónico: $email\n";
	$body .= "Empresa/Organización: " . ($company ? $company : 'N/A') . "\n\n";
	$body .= "Detalles de la Consulta:\n";
	$body .= "------------------------\n";
	$body .= "$message\n";

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'From: Linea 3 Web <no-reply@linea3legal.com>',
		'Reply-To: ' . $name . ' <' . $email . '>'
	);

	// 5. Enviar el correo
	$sent = wp_mail($to, $subject, $body, $headers);

	// 6. Respuesta AJAX
	if ($sent) {
		wp_send_json_success('Mensaje enviado exitosamente');
	} else {
		wp_send_json_error('Error general enviando el correo.');
	}
}
// Registrar las acciones para AJAX (usuarios logueados y no logueados)
add_action('wp_ajax_nopriv_antigravity_submit_consultation', 'antigravity_handle_consultation_form');
add_action('wp_ajax_antigravity_submit_consultation', 'antigravity_handle_consultation_form');


/**
 * Intercepta los correos salientes EXCLUSIVAMENTE en desarrollo local para enviarlos a Mailpit.
 * Detecta si estamos en localhost con el puerto 8081 para no romper la web en Producción.
 */
function antigravity_mailpit_smtp($phpmailer)
{
	$phpmailer->isSMTP();
	$phpmailer->Host = 'mailpit'; // Nombre del contenedor en docker-compose
	$phpmailer->SMTPAuth = false;
	$phpmailer->Port = 1025; // Puerto de captura SMTP de Mailpit
}

// Candado de Seguridad: Solo activa la trampa de correos en tu equipo local.
// De esta forma en Producción (hosting) se usarán los correos de forma normal.
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
	add_action('phpmailer_init', 'antigravity_mailpit_smtp');
}

/**
 * Registra categorías y patrones de bloques personalizados.
 */
function antigravity_register_block_patterns(): void
{
	// 1. Registrar categoría personalizada
	register_block_pattern_category(
		'antigravity-patterns',
		array('label' => __('Linea 3 Estudio Legal Patterns', 'linea3-legal-child'))
	);

	// 2. Registrar Patrón de Consulta Estratégica
	register_block_pattern(
		'antigravity/cta-strategic-consultation',
		array(
			'title'       => __('Agendar Consulta', 'linea3-legal-child'),
			'description' => __('Sección de llamada a la acción para agendar una consulta estratégica.', 'linea3-legal-child'),
			'categories'  => array('featured', 'antigravity-patterns', 'call-to-action'),
			'keywords'    => array('Agendar', 'Consulta', 'Legal', 'CTA', 'Estratégica'),
			'postTypes'   => array('page'),
			'blockTypes'  => array('core/paragraph', 'core/buttons', 'core/group'),
			'inserter'    => true,
			'content'     => '<!-- wp:group {"layout":{"type":"constrained"}} --><div class="wp-block-group"><!-- wp:template-part {"slug":"cta-strategic-consultation","theme":"' . get_stylesheet() . '"} /--></div><!-- /wp:group -->',
		)
	);

	// 3. Registrar Patrón "Nuestro Equipo"
	register_block_pattern(
		'antigravity/nuestro-equipo',
		array(
			'title'       => __('Nuestro Cuerpo Jurídico', 'linea3-legal-child'),
			'description' => __('Lista dinámica de los usuarios activos del sistema (autores).', 'linea3-legal-child'),
			'categories'  => array('featured', 'antigravity-patterns'),
			'keywords'    => array('Equipo', 'Abogados', 'Profesionales', 'Team', 'Nosotros', 'Cuerpo Jurídico'),
			'postTypes'   => array('page'),
			'blockTypes'  => array('core/group', 'core/shortcode'),
			'inserter'    => true,
			'content'     => '<!-- wp:group {"className":"antigravity-team-section","layout":{"type":"constrained"}} --><div class="wp-block-group antigravity-team-section"><!-- wp:shortcode -->[antigravity_team_grid]<!-- /wp:shortcode --></div><!-- /wp:group -->',
		)
	);
}
add_action('init', 'antigravity_register_block_patterns');

/**
 * -----------------------------------------------------------------------------
 * REGISTRO DE BLOQUES DINÁMICOS
 * -----------------------------------------------------------------------------
 */

/**
 * Registra el bloque dinámico 'antigravity/author-card'.
 */
function antigravity_register_dynamic_blocks(): void
{
	register_block_type('antigravity/author-card', array(
		'render_callback' => 'antigravity_render_author_card',
		'uses_context'    => array('postId'),
	));
}
add_action('init', 'antigravity_register_dynamic_blocks');

/**
 * -----------------------------------------------------------------------------
 * REGISTRO DE SHORTCODES
 * -----------------------------------------------------------------------------
 */
add_shortcode('antigravity_team_grid', 'antigravity_render_team_grid');

/**
 * Añade campos personalizados al perfil de usuario (Especialidad).
 * 
 * @param array $methods Métodos de contacto del usuario.
 * @return array Métodos actualizados.
 */
function antigravity_add_user_meta_fields($methods): array
{
	$methods['antigravity_user_specialty'] = __('Especialidad', 'linea3-legal-child');
	$methods['antigravity_user_job_title']  = __('Cargo', 'linea3-legal-child');
	$methods['antigravity_user_linkedin']  = __('LinkedIn URL', 'linea3-legal-child');
	$methods['antigravity_user_twitter']   = __('Twitter/X URL', 'linea3-legal-child');
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
			<th><label for="antigravity_user_excerpt"><?php _e('Extracto Profesional', 'linea3-legal-child'); ?></label></th>
			<td>
				<textarea name="antigravity_user_excerpt" id="antigravity_user_excerpt" rows="5" cols="30"><?php echo esc_textarea(get_the_author_meta('antigravity_user_excerpt', $user->ID)); ?></textarea>
				<p class="description"><?php _e('Breve descripción que aparece en la tarjeta del profesional.', 'linea3-legal-child'); ?></p>
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
 * 
 * @param int $author_id ID del autor.
 * @return string HTML de la tarjeta.
 */
function antigravity_get_author_card_html(int $author_id, int $post_id = 0): string
{
	if (!$author_id) {
		return '';
	}

	// 1. Extracción de datos
	$avatar_url = get_avatar_url($author_id, array('size' => 120));
	$name       = get_the_author_meta('display_name', $author_id);
	$specialty  = get_the_author_meta('antigravity_user_specialty', $author_id);

	// 2. Metadata (si hay post_id)
	$meta_html = '';
	if ($post_id > 0) {
		$date = get_the_date('', $post_id);
		$content = get_post_field('post_content', $post_id);
		$word_count = str_word_count(strip_tags($content));
		$reading_time = ceil($word_count / 200);
		if ($reading_time < 1) $reading_time = 1;
		$reading_time_text = sprintf(_n('%d min de lectura', '%d min de lectura', $reading_time, 'linea3-legal-child'), $reading_time);
		$meta_html = sprintf('<p class="author-post-meta">%s — %s</p>', esc_html($date), esc_html($reading_time_text));
	}

	// 3. Construcción de la estructura HTML (Unificada con single post)
	$output = '<div class="antigravity-author-card">';

	// Columna Izquierda (Avatar)
	$output .= '<div class="author-avatar-wrapper">';
	$output .= sprintf(
		'<img src="%s" alt="%s" class="author-avatar" />',
		esc_url($avatar_url),
		esc_attr($name)
	);
	$output .= '</div>';

	// Columna Derecha (Datos)
	$output .= '<div class="author-data-wrapper">';
	$output .= sprintf('<h4 class="author-name">%s</h4>', esc_html($name));

	if (!empty($specialty)) {
		$output .= sprintf(
			'<p class="author-specialty">%s</p>',
			esc_html(sanitize_text_field($specialty))
		);
	}

	if (!empty($meta_html)) {
		$output .= $meta_html;
	}

	$output .= '</div>'; // .author-data-wrapper
	$output .= '</div>'; // .antigravity-author-card

	return $output;
}

/**
 * Renderizado del bloque de tarjeta de autor para los resultados de búsqueda/blog.
 * 
 * @param array $attributes Atributos del bloque.
 * @param string $content Contenido del bloque.
 * @param WP_Block $block Objeto del bloque.
 * @return string HTML renderizado.
 */
function antigravity_render_author_card($attributes, $content, $block): string
{
	if (!isset($block->context['postId'])) {
		return '';
	}

	$post_id   = $block->context['postId'];
	$author_id = (int) get_post_field('post_author', $post_id);

	return antigravity_get_author_card_html($author_id, $post_id);
}

/**
 * Renderizado del bloque dinámico 'antigravity/team-grid' (Nuestro Equipo).
 * 
 * @param array $attributes Atributos del bloque.
 * @return string HTML renderizado.
 */
function antigravity_render_team_grid($attributes): string
{
	// Filtrar usuarios activos con rol 'author'.
	$args = array(
		'role__in' => array('author'),
		'orderby'  => 'display_name',
		'order'    => 'ASC',
	);

	$users = get_users($args);

	// Inyectamos el Header directamente en el renderizado dinámico para asegurar su presencia
	$output = '<div class="antigravity-team-section">';
	
	$output .= '<div class="team-section-header">';
	$output .= '<div class="team-header-left">';
	$output .= '<h2 class="team-title">' . esc_html__('Nuestro Cuerpo Jurídico', 'linea3-legal-child') . '</h2>';
	$output .= '<p class="team-subtitle">' . esc_html__('LIDERAZGO Y ESTRATEGIA', 'linea3-legal-child') . '</p>';
	$output .= '</div>';
	$output .= '<div class="team-header-right">';
	$output .= '<p class="team-corporate-phrase">' . esc_html__('“La justicia no es solo una norma, es la arquitectura de una sociedad estable.”', 'linea3-legal-child') . '</p>';
	$output .= '</div>';
	$output .= '</div>'; // .team-section-header

	if (empty($users)) {
		$output .= '<p class="linea3-team-empty">' . esc_html__('No hay profesionales disponibles para mostrar.', 'linea3-legal-child') . '</p>';
		$output .= '</div>';
		return $output;
	}

	$output .= '<div class="linea3-team-grid">';

	foreach ($users as $user) {
		$user_id    = $user->ID;
		$avatar_url = get_avatar_url($user_id, array('size' => 400));
		$name       = $user->display_name;
		$specialty  = get_the_author_meta('antigravity_user_specialty', $user_id);
		$job_title  = get_the_author_meta('antigravity_user_job_title', $user_id);
		$linkedin   = get_the_author_meta('antigravity_user_linkedin', $user_id);
		$twitter    = get_the_author_meta('antigravity_user_twitter', $user_id);

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

/**
 * 4. Shortcode para el Cuadro de Autor en Entradas Individuales
 * Renderiza Avatar + Nombre + Cargo (Dorado)
 */
function antigravity_post_author_box_shortcode() {
	global $post;

	// En el editor de sitio, mostramos un placeholder para evitar errores visuales
	if (is_admin() && !defined('DOING_AJAX')) {
		return '<div class="single-post-author-box placeholder">
			<div class="author-avatar-wrap"><div style="width:64px;height:64px;background:#c19b5a;border-radius:12px"></div></div>
			<div class="author-info-wrap">
				<p class="author-name">Nombre del Autor</p>
				<p class="author-specialty">Especialidad del Autor</p>
				<p class="author-post-meta">21 de Abril, 2026 — 5 minutos de lectura</p>
			</div>
		</div>';
	}

	$author_id = (int) get_post_field('post_author', $post->ID);

	$first_name = get_the_author_meta('first_name', $author_id);
	$last_name  = get_the_author_meta('last_name', $author_id);
	$name       = trim($first_name . ' ' . $last_name);
	
	// Fallback si no hay nombre completo
	if (empty($name)) {
		$name = get_the_author_meta('display_name', $author_id);
	}

	$specialty = get_user_meta($author_id, 'antigravity_user_specialty', true);
	$avatar    = get_avatar($author_id, 80, '', $name, array('class' => 'single-author-avatar'));
	
	// Datos dinámicos: Fecha y Tiempo de Lectura
	$date = get_the_date();
	$content = get_post_field('post_content', $post->ID);
	$word_count = str_word_count(strip_tags($content));
	$reading_time = ceil($word_count / 200); // Promedio de 200 palabras por minuto
	if ($reading_time < 1) $reading_time = 1;
	$reading_time_text = sprintf(_n('%d min de lectura', '%d min de lectura', $reading_time, 'linea3-legal-child'), $reading_time);

	$output  = '<div class="single-post-author-box">';
	$output .= '<div class="author-avatar-wrap">' . $avatar . '</div>';
	$output .= '<div class="author-info-wrap">';
	$output .= '<p class="author-name">' . esc_html($name) . '</p>';
	if (!empty($specialty)) {
		$output .= '<p class="author-specialty">' . esc_html($specialty) . '</p>';
	}
	$output .= '<p class="author-post-meta">' . esc_html($date) . ' — ' . esc_html($reading_time_text) . '</p>';
	$output .= '</div>';
	$output .= '</div>';

	return $output;
}
add_shortcode('antigravity_post_author_box', 'antigravity_post_author_box_shortcode');
/**
 * 5. Shortcode para Publicaciones Relacionadas (Mismo Autor)
 * Despliega las 3 últimas publicaciones del autor actual.
 */
function antigravity_related_posts_shortcode() {
	if (!is_singular('post')) {
		return '';
	}

	$current_post_id = get_the_ID();
	$author_id       = get_post_field('post_author', $current_post_id);

	$args = array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post__not_in'   => array($current_post_id),
		'author'         => $author_id,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	$query = new WP_Query($args);

	if (!$query->have_posts()) {
		return '';
	}

	ob_start();
	?>
	<section class="related-posts-section">
		<div class="related-posts-header">
			<div class="related-header-top">
				<p class="related-subtitle"><?php esc_html_e('MÁS DEL MISMO AUTOR', 'linea3-legal-child'); ?></p>
				<a href="<?php echo esc_url(get_author_posts_url($author_id)); ?>" class="view-all-link">
					<?php esc_html_e('Ver todas sus publicaciones', 'linea3-legal-child'); ?>
				</a>
			</div>
			<div class="related-header-main">
				<h2 class="related-title"><?php esc_html_e('Publicaciones Relacionadas', 'linea3-legal-child'); ?></h2>
			</div>
		</div>

		<div class="blog-listing-wrapper">
			<ul class="wp-block-post-template is-layout-grid columns-3">
				<?php while ($query->have_posts()) : $query->the_post(); ?>
					<li class="wp-block-post">
						<article class="wp-block-group">
							<div class="wp-block-post-featured-image">
								<a href="<?php the_permalink(); ?>">
									<?php echo antigravity_get_post_thumbnail_html(get_the_ID(), 'medium_large', array('class' => 'related-post-img')); ?>
								</a>
							</div>
							
							<div class="antigravity-card-content">
								<div class="wp-block-post-terms">
									<?php the_category(' '); ?>
								</div>
								<h3 class="wp-block-post-title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>
								<?php echo antigravity_get_author_card_html((int) get_the_author_meta('ID'), get_the_ID()); ?>
							</div>
						</article>
					</li>
				<?php endwhile; wp_reset_postdata(); ?>
			</ul>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode('antigravity_related_posts', 'antigravity_related_posts_shortcode');

/**
 * 6. ADICIÓN DE COLUMNAS PERSONALIZADAS AL ADMIN (ENTRADAS)
 */

/**
 * Añade las columnas "Imagen" y "Extracto" a la lista de Entradas.
 */
function antigravity_add_posts_columns($columns) {
	$new_columns = array();
	foreach($columns as $key => $value) {
		if ($key === 'title') {
			$new_columns['title'] = $value;
			$new_columns['featured_image'] = __('Imagen', 'linea3-legal-child');
			$new_columns['has_excerpt'] = __('Extracto', 'linea3-legal-child');
		} else {
			$new_columns[$key] = $value;
		}
	}
	return $new_columns;
}
add_filter('manage_posts_columns', 'antigravity_add_posts_columns');

/**
 * Renderiza el contenido de las columnas personalizadas.
 */
function antigravity_render_posts_columns($column, $post_id) {
	switch ($column) {
		case 'featured_image':
			if (has_post_thumbnail($post_id)) {
				echo get_the_post_thumbnail($post_id, array(50, 50), array('style' => 'border-radius: 6px; border: 1px solid rgba(0,0,0,0.1);'));
			} else {
				echo '<span style="color: #999; font-style: italic;">Sin imagen</span>';
			}
			break;
		case 'has_excerpt':
			if (has_excerpt($post_id)) {
				echo '<span style="color: #2271b1; font-weight: bold; font-size: 1.2rem;">✔</span>';
			} else {
				echo '<span style="color: #d63638; font-weight: bold; font-size: 1.2rem;">✖</span>';
			}
			break;
	}
}
add_action('manage_posts_custom_column', 'antigravity_render_posts_columns', 10, 2);

/**
 * Renderizado de la cuadrícula de publicaciones destacadas premium.
 */
function antigravity_render_featured_posts_grid(): string
{
	$args = array(
		'post_type'      => 'post',
		'posts_per_page' => 5,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	$query = new WP_Query($args);

	if (!$query->have_posts()) {
		return '';
	}

	ob_start();
	?>
	<section class="antigravity-featured-posts-grid">
		<div class="featured-posts-container">
			<div class="featured-posts-header">
				<div class="featured-header-left">
					<h2 class="featured-title">Publicaciones Destacadas</h2>
					<p class="featured-subtitle">Especialización de alto nivel para blindar cada aspecto de tu organización.</p>
				</div>
				<div class="featured-header-right">
					<div class="featured-accent-line"></div>
				</div>
			</div>

			<div class="wp-block-post-template">
				<?php 
				$counter = 0;
				while ($query->have_posts()) : $query->the_post(); 
					$counter++;
					
					$category = get_the_category();
					$cat_name = !empty($category) ? $category[0]->name : 'Estrategia';
					$thumb_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
					$is_fallback = false;

					if (!$thumb_url) {
						$thumb_url = get_stylesheet_directory_uri() . '/assets/images/placeholder-legal.png';
						$is_fallback = true;
					}
					
					$fallback_class = $is_fallback ? 'has-fallback-image' : '';
					$author_name = get_the_author();
				?>
					<a href="<?php the_permalink(); ?>" class="wp-block-post <?php echo $fallback_class; ?>">
						<div class="featured-card-image-wrap">
							<img src="<?php echo esc_url($thumb_url); ?>" alt="<?php the_title_attribute(); ?>" class="featured-card-image">
						</div>
						<div class="featured-card-overlay"></div>
						<div class="featured-card-content">
							<div class="featured-card-meta">
								<span class="featured-card-category"><?php echo esc_html($cat_name); ?></span>
								<h3 class="featured-card-title"><?php the_title(); ?></h3>
							</div>
							<div class="featured-card-author">
								<div class="author-avatar-wrap">
									<?php echo get_avatar(get_the_author_meta('ID'), 32); ?>
								</div>
								<span class="author-name"><?php echo esc_html($author_name); ?></span>
							</div>
							<div class="featured-card-accent-line"></div>
						</div>
					</a>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
add_shortcode('antigravity_featured_posts', 'antigravity_render_featured_posts_grid');

/**
 * Registro del patrón de bloques de Publicaciones Destacadas.
 */
function antigravity_register_featured_pattern(): void
{
	register_block_pattern(
		'antigravity/featured-posts-premium',
		array(
			'title'       => __('Todas las Publicaciones Destacadas', 'linea3-legal-child'),
			'description' => __('Cuadrícula de 5 publicaciones con diseño de alta fidelidad y overlays.', 'linea3-legal-child'),
			'categories'  => array('featured', 'antigravity-patterns'),
			'keywords'    => array('Destacadas', 'Posts', 'Premium', 'Grid'),
			'postTypes'   => array('page'),
			'content'     => '<!-- wp:group {"align":"full","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull"><!-- wp:shortcode -->[antigravity_featured_posts]<!-- /wp:shortcode --></div><!-- /wp:group -->',
		)
	);
}
add_action('init', 'antigravity_register_featured_pattern');
