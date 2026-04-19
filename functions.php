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
 * Provides a fallback featured image if a post doesn't have one.
 *
 * @param string       $html              The post thumbnail HTML.
 * @param int          $post_id           The post ID.
 * @param int|string   $post_thumbnail_id The post thumbnail ID.
 * @param string|array $size              The post thumbnail size.
 * @param string       $attr              Query string of attributes.
 * @return string The modified post thumbnail HTML.
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
