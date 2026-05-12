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

		// Handle dimensions for fallback
		$width_attr = '';
		$height_attr = '';
		if (is_array($size)) {
			$width_attr = sprintf(' width="%d"', $size[0]);
			$height_attr = sprintf(' height="%d"', $size[1]);
		}

		$html = sprintf(
			'<img src="%s" class="%s" alt="" loading="lazy"%s%s />',
			esc_url($placeholder_url),
			esc_attr($class),
			$width_attr,
			$height_attr
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

		$width_attr = '';
		$height_attr = '';
		$size_class = '';

		if (is_array($size)) {
			$width_attr = sprintf(' width="%d"', $size[0]);
			$height_attr = sprintf(' height="%d"', $size[1]);
			$size_class = sprintf('size-%dx%d', $size[0], $size[1]);
			$attachment_class = 'custom';
		} else {
			$size_class = 'size-' . (string) $size;
			$attachment_class = (string) $size;
		}

		$html = sprintf(
			'<img src="%s" class="attachment-%s %s wp-post-image fallback-image" alt="" loading="lazy"%s%s />',
			esc_url($placeholder_url),
			esc_attr($attachment_class),
			esc_attr($size_class),
			$width_attr,
			$height_attr
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
				<a href='" . esc_url(home_url('/')) . "' target='_blank' style='display: inline-block; text-decoration: none;'>
					<img src='" . esc_url(get_stylesheet_directory_uri() . '/assets/images/logo-horizontal-oscuro.png') . "' alt='Línea 3 Estudio Legal' style='max-width: 200px; height: auto; border: none;'>
				</a>
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
 * Inyecta el contenedor HTML del Modal de Contacto del Equipo en el footer.
 */
function antigravity_render_team_contact_modal()
{
	?>
	<div class="antigravity-modal-overlay antigravity-team-contact-modal-overlay">
		<div class="antigravity-modal-content team-contact-split-content">
			<button class="antigravity-modal-close" aria-label="Cerrar modal">&times;</button>
			<div class="antigravity-modal-body team-contact-split-layout">
				<div class="team-contact-sidebar">
					<img id="team-contact-modal-image" src="" alt="Profesional" class="team-contact-sidebar-bg">
					<div class="team-contact-sidebar-overlay">
						<h3 id="team-contact-modal-title">Contactar Profesional</h3>
						<p>Envíe un mensaje directo y confidencial. Le responderemos a la brevedad.</p>
					</div>
				</div>
				<div class="team-contact-form-area">
					<form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST"
						class="antigravity-modal-form antigravity-team-contact-form" enctype="multipart/form-data">
						<?php wp_nonce_field('antigravity_team_contact_action', 'antigravity_team_contact_nonce'); ?>
						<input type="hidden" name="action" value="antigravity_submit_team_contact">
						<input type="hidden" name="target_author_id" id="target_author_id" value="">

						<div class="antigravity-form-grid">
							<div class="antigravity-form-group"><label for="team-contact-email">De: (Su Correo) *</label><input 
									type="email" id="team-contact-email" name="contact_email"
									placeholder="ejemplo@correo.com" required></div>
							<div class="antigravity-form-group"><label for="team-contact-phone">Teléfono</label><input 
									type="tel" id="team-contact-phone" name="contact_phone"
									placeholder="Su número de teléfono"></div>
							<div class="antigravity-form-group"><label for="team-contact-subject">Asunto: *</label><input 
									type="text" id="team-contact-subject" name="contact_subject"
									placeholder="Motivo de la consulta" required></div>
							<div class="antigravity-form-group"><label for="team-contact-name">Tu Nombre: *</label><input 
									type="text" id="team-contact-name" name="contact_name"
									placeholder="Ej. Juan Pérez" required></div>
							<div class="antigravity-form-group full-width"><label for="team-contact-message">Mensaje: *</label><textarea 
									id="team-contact-message" name="contact_message" rows="5"
									placeholder="Escriba su mensaje aquí..." required></textarea></div>
							<div class="antigravity-form-group full-width">
								<label for="team-contact-attachment" style="display: flex; justify-content: space-between;">
									<span>Archivos Adjuntos (Opcional)</span>
									<span style="font-size: 0.8em; color: #94a3b8; font-weight: normal;">Máx. Total 15MB (PDF, DOC, DOCX, JPG, PNG)</span>
								</label>
								<input type="file" id="team-contact-attachment" name="contact_attachment[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
							</div>
						</div>

						<div class="antigravity-form-group submit-group" style="text-align: right;"><button type="submit"
								class="antigravity-btn-submit">Enviar Mensaje</button></div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<?php
}
add_action('wp_footer', 'antigravity_render_team_contact_modal');

/**
 * Procesa el envío del formulario de Contacto al Equipo
 */
function antigravity_handle_team_contact_form()
{
	if (!isset($_POST['antigravity_team_contact_nonce']) || !wp_verify_nonce($_POST['antigravity_team_contact_nonce'], 'antigravity_team_contact_action')) {
		wp_send_json_error('Fallo de seguridad.');
	}

	$author_id = isset($_POST['target_author_id']) ? intval($_POST['target_author_id']) : 0;
	if ($author_id <= 0) {
		wp_send_json_error('Destinatario no válido.');
	}

	$target_user = get_userdata($author_id);
	if (!$target_user || empty($target_user->user_email)) {
		wp_send_json_error('El profesional seleccionado no está disponible.');
	}

	$email = sanitize_email($_POST['contact_email'] ?? '');
	$phone = sanitize_text_field($_POST['contact_phone'] ?? '');
	$subject_input = sanitize_text_field($_POST['contact_subject'] ?? '');
	$name = sanitize_text_field($_POST['contact_name'] ?? '');
	$message = sanitize_textarea_field($_POST['contact_message'] ?? '');

	if (empty($email) || empty($subject_input) || empty($message) || empty($name) || !is_email($email)) {
		wp_send_json_error('Datos inválidos o incompletos.');
	}

	$to = $target_user->user_email;
	$professional_name = $target_user->display_name;

	$subject = 'Mensaje Directo: ' . $subject_input;
	$body = "
	<div style='background-color: #0f172a; padding: 40px; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif;'>
		<div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);'>
			<div style='background-color: #0a2233; padding: 30px; text-align: center; border-bottom: 4px solid #ce9e50;'>
				<a href='" . esc_url(home_url('/')) . "' target='_blank' style='display: inline-block; text-decoration: none;'>
					<img src='" . esc_url(get_stylesheet_directory_uri() . '/assets/images/logo-horizontal-oscuro.png') . "' alt='Línea 3 Estudio Legal' style='max-width: 200px; height: auto; border: none;'>
				</a>
			</div>
			<div style='padding: 40px; color: #334155; line-height: 1.6;'>
				<h2 style='color: #0a2233; margin-top: 0; font-size: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;'>Nuevo Mensaje Directo</h2>
				<p style='color: #475569;'>Hola <strong>" . esc_html($professional_name) . "</strong>, has recibido un mensaje a través de tu perfil en Linea 3.</p>
				
				<div style='margin-bottom: 25px;'>
					<p style='margin: 5px 0;'><strong style='color: #0a2233;'>De:</strong> " . esc_html($name) . " &lt;<a href='mailto:" . esc_attr($email) . "' style='color: #ce9e50; text-decoration: none;'>" . esc_html($email) . "</a>&gt;</p>
					" . (!empty($phone) ? "<p style='margin: 5px 0;'><strong style='color: #0a2233;'>Teléfono:</strong> " . esc_html($phone) . "</p>" : "") . "
					<p style='margin: 5px 0;'><strong style='color: #0a2233;'>Asunto:</strong> " . esc_html($subject_input) . "</p>
				</div>

				<div style='background-color: #f8fafc; padding: 25px; border-left: 4px solid #ce9e50; border-radius: 4px;'>
					<h3 style='margin-top: 0; margin-bottom: 10px; font-size: 16px; color: #0a2233; text-transform: uppercase; letter-spacing: 0.5px;'>Mensaje</h3>
					<p style='margin: 0; white-space: pre-wrap; color: #475569;'>" . nl2br(esc_html($message)) . "</p>
				</div>
				
				<div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center;'>
					<p style='font-size: 12px; color: #94a3b8; margin: 0;'>Este es un mensaje automático generado desde el portal web de Linea 3 Estudio Legal.</p>
				</div>
			</div>
		</div>
	</div>";
	
	$attachments = array();
	$uploaded_files = array(); // Para limpiar archivos temporales luego del envío

	// Manejo de archivos adjuntos (Múltiples)
	if (isset($_FILES['contact_attachment']) && is_array($_FILES['contact_attachment']['name'])) {
		$files = $_FILES['contact_attachment'];
		$total_size = 0;
		$num_files = count($files['name']);

		// Calcular el tamaño total de todos los archivos
		for ($i = 0; $i < $num_files; $i++) {
			if ($files['error'][$i] === UPLOAD_ERR_OK) {
				$total_size += $files['size'][$i];
			}
		}

		// Validar tamaño global: 15MB (15 * 1024 * 1024)
		if ($total_size > 15728640) {
			wp_send_json_error('El peso total de los archivos adjuntos excede el límite máximo de 15MB.');
		}

		if (!function_exists('wp_handle_upload')) {
			require_once(ABSPATH . 'wp-admin/includes/file.php');
		}

		// Definir mimes permitidos por seguridad
		$mimes = array(
			'pdf' => 'application/pdf',
			'doc' => 'application/msword',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'jpg|jpeg|jpe' => 'image/jpeg',
			'png' => 'image/png'
		);

		$upload_overrides = array('test_form' => false, 'mimes' => $mimes);

		// Subir cada archivo temporalmente
		for ($i = 0; $i < $num_files; $i++) {
			if ($files['error'][$i] === UPLOAD_ERR_OK) {
				$single_file = array(
					'name'     => $files['name'][$i],
					'type'     => $files['type'][$i],
					'tmp_name' => $files['tmp_name'][$i],
					'error'    => $files['error'][$i],
					'size'     => $files['size'][$i]
				);

				$movefile = wp_handle_upload($single_file, $upload_overrides);

				if ($movefile && !isset($movefile['error'])) {
					$attachments[] = $movefile['file'];
					$uploaded_files[] = $movefile['file']; // Guardar ruta absoluta para borrar luego
				} else {
					wp_send_json_error('Error procesando el archivo ' . esc_html($single_file['name']) . ': ' . $movefile['error']);
				}
			}
		}
	}

	$headers = array('Content-Type: text/html; charset=UTF-8', 'From: Linea 3 Web <no-reply@linea3legal.com>');
	$mail_sent = wp_mail($to, $subject, $body, $headers, $attachments);

	// Eliminar archivos temporales para no ocupar espacio en el servidor
	if (!empty($uploaded_files)) {
		foreach ($uploaded_files as $file_path) {
			@unlink($file_path);
		}
	}

	if ($mail_sent) {
		wp_send_json_success('Mensaje enviado exitosamente');
	} else {
		wp_send_json_error('Error enviando el correo al profesional.');
	}
}
add_action('wp_ajax_nopriv_antigravity_submit_team_contact', 'antigravity_handle_team_contact_form');
add_action('wp_ajax_antigravity_submit_team_contact', 'antigravity_handle_team_contact_form');

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
function antigravity_map_shortcode($atts)
{
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
	register_block_pattern('antigravity/nuestro-equipo', array('title' => 'Nuestro Cuerpo Jurídico', 'categories' => array('antigravity-patterns'), 'content' => '<!-- wp:group {"className":"antigravity-team-section","layout":{"type":"constrained"}} --><div class="wp-block-group antigravity-team-section"><!-- wp:shortcode -->[antigravity_team_grid]<!-- /wp:shortcode --></div><!-- /wp:group -->'));
	register_block_pattern('antigravity/publicaciones-destacadas', array('title' => 'Publicaciones Destacadas', 'categories' => array('antigravity-patterns'), 'content' => '<!-- wp:group {"className":"antigravity-featured-posts-section","layout":{"type":"constrained"}} --><div class="wp-block-group antigravity-featured-posts-section"><!-- wp:shortcode -->[antigravity_featured_posts]<!-- /wp:shortcode --></div><!-- /wp:group -->'));
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
            <figure class="wp-block-image size-large location-photo-wrap"><img src="' . get_stylesheet_directory_uri() . '/assets/images/oficina-bogota.jpg" alt="Sede Linea 3 Bogotá"/></figure>
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
                <div class="wp-block-button btn-agendar-visita"><a class="wp-block-button__link wp-element-button antigravity-modal-trigger">Agendar Consulta</a></div>
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
	
	register_block_pattern('antigravity/servicios', array(
		'title' => 'Servicios Legales (Grid Dinámico)',
		'categories' => array('antigravity-patterns'),
		'content' => '<!-- wp:group {"className":"l3-container-standard","layout":{"type":"constrained"}} -->
<div class="wp-block-group l3-container-standard">
    <!-- wp:group {"className":"services-section-header","layout":{"type":"constrained"}} -->
    <div class="wp-block-group services-section-header">
        <!-- wp:group {"className":"section-vertical-line","layout":{"type":"constrained"}} -->
        <div class="wp-block-group section-vertical-line"></div>
        <!-- /wp:group -->

        <!-- wp:group {"className":"services-header-left","layout":{"type":"constrained"}} -->
        <div class="wp-block-group services-header-left">
            <!-- wp:paragraph {"className":"services-eyebrow"} -->
            <p class="services-eyebrow">ESPECIALIZACIÓN Y ESTRATEGIA JURÍDICA</p>
            <!-- /wp:paragraph -->

            <!-- wp:heading {"level":2,"className":"services-title"} -->
            <h2 class="wp-block-heading services-title">Nuestras Áreas de Práctica</h2>
            <!-- /wp:heading -->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:group -->

    <!-- wp:shortcode -->
    [antigravity_services_grid orderby="date" order="ASC"]
    <!-- /wp:shortcode -->
</div>
<!-- /wp:group -->'
	));

    register_block_pattern('antigravity/aliados', array(
        'title' => 'Nuestros Aliados (Logos)',
        'categories' => array('antigravity-patterns'),
        'content' => '<!-- wp:group {"align":"full","className":"l3-allies-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull l3-allies-section">
    <!-- wp:group {"className":"l3-container-standard","layout":{"type":"constrained"}} -->
    <div class="wp-block-group l3-container-standard">
        <!-- wp:group {"className":"l3-allies-header-centered","layout":{"type":"constrained"}} -->
        <div class="wp-block-group l3-allies-header-centered">
            <!-- wp:paragraph {"className":"services-eyebrow"} -->
            <p class="services-eyebrow">RESPALDO Y COOPERACIÓN</p>
            <!-- /wp:paragraph -->

            <!-- wp:heading {"level":2,"className":"services-title"} -->
            <h2 class="wp-block-heading services-title">Nuestros aliados</h2>
            <!-- /wp:heading -->
        </div>
        <!-- /wp:group -->

        <!-- wp:shortcode -->
        [antigravity_allies_grid]
        <!-- /wp:shortcode -->
    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->'
    ));
}
add_action('init', 'antigravity_register_block_patterns');

function antigravity_register_enfoque_pattern(): void {
    $content = <<<'PATTERN_HTML'
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}}},"backgroundColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-background-color has-background l3-enfoque-wrapper">
    <!-- wp:html -->
    <section class="l3-enfoque-section">
        <div class="l3-enfoque-container">
            
        <div class="l3-enfoque-header">
            <span class="l3-enfoque-label">COMPARATIVA DE ENFOQUE</span>
            <h2 class="l3-enfoque-title">Cambiando el enfoque del derecho</h2>
        </div>

            <div class="l3-enfoque-table-wrapper">
                <div class="l3-enfoque-table">
                    <div class="l3-enfoque-row header-row">
                        <div class="l3-enfoque-cell empty-cell"></div>
                        <div class="l3-enfoque-cell label-cell highlight">Tradicional</div>
                        <div class="l3-enfoque-cell label-cell">
                        <img src="/wp-content/themes/linea3-legal-child/assets/images/logo-horizontal-oscuro.png" alt="Línea Tres" class="l3-enfoque-logo">
                    </div>
                    </div>

                    <div class="l3-enfoque-row">
                        <div class="l3-enfoque-cell feature-cell">Visión</div>
                        <div class="l3-enfoque-cell content-cell">Trámite y obstáculo postergable</div>
                        <div class="l3-enfoque-cell content-cell highlight">
                            <span class="l3-check-icon l3-check-delay-1"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                            Herramienta estratégica y accesible
                        </div>
                    </div>

                    <div class="l3-enfoque-row">
                        <div class="l3-enfoque-cell feature-cell">Enfoque</div>
                        <div class="l3-enfoque-cell content-cell">Reactivo y enfocado en apagar incendios</div>
                        <div class="l3-enfoque-cell content-cell highlight">
                            <span class="l3-check-icon l3-check-delay-2"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                            Preventivo, organizativo y protector
                        </div>
                    </div>

                    <div class="l3-enfoque-row">
                        <div class="l3-enfoque-cell feature-cell">Lenguaje</div>
                        <div class="l3-enfoque-cell content-cell">Complejo, teórico y distante</div>
                        <div class="l3-enfoque-cell content-cell highlight">
                            <span class="l3-check-icon l3-check-delay-3"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                            Simple, claro y basado en la experiencia
                        </div>
                    </div>
                </div>
            </div>

            <div class="l3-enfoque-footer">
                <div class="l3-enfoque-footer-divider"></div>
                <p class="l3-enfoque-footer-text">
                    En <strong>Línea Tres</strong> convertimos lo jurídico en tu mayor ventaja:<br>
                    prevenimos errores, organizamos tu negocio y protegemos tu crecimiento.
                </p>
            </div>

        </div>
    </section>
    <!-- /wp:html -->
</div>
<!-- /wp:group -->
PATTERN_HTML;

    register_block_pattern(
        'antigravity/enfoque-derecho',
        array(
            'title'       => __( 'Enfoque del Derecho (Comparativa)', 'linea3-legal-child' ),
            'description' => _x( 'Tabla comparativa entre el enfoque tradicional y Línea Tres.', 'Block pattern description', 'linea3-legal-child' ),
            'content'     => $content,
            'categories'  => array( 'antigravity-patterns' ),
        )
    );
}
add_action( 'init', 'antigravity_register_enfoque_pattern' );

/**
 * Registra el patrón Síntoma de la Desorganización Jurídica.
 */
function antigravity_register_sintoma_pattern(): void {
    $content = <<<'PATTERN_HTML'
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}}},"backgroundColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-background-color has-background l3-sintoma-wrapper">
    <!-- wp:html -->
    <section class="l3-sintoma-section">
        <div class="l3-sintoma-container">
            <div class="l3-sintoma-header">
                <span class="l3-sintoma-label">DIAGNÓSTICO LEGAL</span>
                <h2 class="l3-sintoma-title">El síntoma de la<br>desorganización jurídica</h2>
            </div>
            <div class="l3-sintoma-diagram">
                <div class="l3-sintoma-card l3-card-tl">
                    <h3 class="l3-sintoma-card-title">Empresas</h3>
                    <p class="l3-sintoma-card-text">Estructuras jurídicas incompletas que limitan el crecimiento.</p>
                </div>
                <div class="l3-sintoma-con l3-con-tl" aria-hidden="true"></div>
                <div class="l3-sintoma-hub">
                    <div class="l3-sintoma-circle">
                        <span>Desorganización<br>Jurídica</span>
                    </div>
                </div>
                <div class="l3-sintoma-con l3-con-tr" aria-hidden="true"></div>
                <div class="l3-sintoma-card l3-card-tr">
                    <h3 class="l3-sintoma-card-title">Emprendedores</h3>
                    <p class="l3-sintoma-card-text">Falta de guía y protección desde el inicio del proyecto.</p>
                </div>
                <div class="l3-sintoma-card l3-card-bl">
                    <h3 class="l3-sintoma-card-title">Personas</h3>
                    <p class="l3-sintoma-card-text">Desconocimiento de sus derechos fundamentales en el día a día.</p>
                </div>
                <div class="l3-sintoma-con l3-con-bl" aria-hidden="true"></div>
                <div class="l3-sintoma-con l3-con-br" aria-hidden="true"></div>
                <div class="l3-sintoma-card l3-card-br">
                    <h3 class="l3-sintoma-card-title">Capital</h3>
                    <p class="l3-sintoma-card-text">Negocios perdiendo dinero por falta de planeación preventiva.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- /wp:html -->
</div>
<!-- /wp:group -->
PATTERN_HTML;

    register_block_pattern(
        'antigravity/sintoma-juridico',
        array(
            'title'       => __( 'Síntoma Jurídico (Diagrama)', 'linea3-legal-child' ),
            'description' => _x( 'Diagrama hub-spoke de la desorganización jurídica.', 'Block pattern description', 'linea3-legal-child' ),
            'content'     => $content,
            'categories'  => array( 'antigravity-patterns' ),
        )
    );
}
add_action( 'init', 'antigravity_register_sintoma_pattern' );

/**
 * Registra el patrón Nuestra Metodología.
 */
function antigravity_register_metodologia_pattern(): void {
    $theme_dir = get_stylesheet_directory();
    $svg_path = $theme_dir . '/assets/images/infinito.svg';
    $svg_content = file_exists( $svg_path ) ? file_get_contents( $svg_path ) : '';

    $content = <<<PATTERN_HTML
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"0","bottom":"0","left":"0","right":"0"},"margin":{"top":"0","bottom":"0"}}},"backgroundColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-base-background-color has-background l3-metodologia-wrapper">
    <!-- wp:html -->
    <section class="l3-metodologia-section">
        <div class="l3-metodologia-container">
            <div class="l3-metodologia-header">
                <span class="l3-metodologia-label">NUESTRA METODOLOGÍA</span>
                <h2 class="l3-metodologia-title">No solo asesoramos.</h2>
            </div>
            <div class="l3-metodologia-diagram">
                <div class="l3-metodo-card l3-metodo-tl">
                    <h3 class="l3-metodo-card-title"><span class="l3-metodo-number">1.</span> Escuchamos</h3>
                    <p class="l3-metodo-card-text">Antes de proponer una solución legal, escuchamos y diagnosticamos tu realidad.</p>
                </div>
                <div class="l3-metodo-card l3-metodo-bl">
                    <h3 class="l3-metodo-card-title"><span class="l3-metodo-number">2.</span> Entendemos</h3>
                    <p class="l3-metodo-card-text">Analizamos el negocio completo, no solo el problema jurídico aislado.</p>
                </div>
                <div class="l3-metodo-hub" style="display: flex; align-items: center; justify-content: center;">
                    {$svg_content}
                </div>
                <div class="l3-metodo-card l3-metodo-tr">
                    <h3 class="l3-metodo-card-title"><span class="l3-metodo-number">3.</span> Simplificamos</h3>
                    <p class="l3-metodo-card-text">Traducimos la complejidad del ordenamiento jurídico a decisiones claras y accesibles.</p>
                </div>
                <div class="l3-metodo-card l3-metodo-br">
                    <h3 class="l3-metodo-card-title"><span class="l3-metodo-number">4.</span> Acompañamos</h3>
                    <p class="l3-metodo-card-text">Estamos presentes en la ejecución de cada decisión para garantizar la seguridad del proyecto.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- /wp:html -->
</div>
<!-- /wp:group -->
PATTERN_HTML;

    register_block_pattern(
        'antigravity/metodologia-final',
        array(
            'title'       => __( 'Nuestra Metodología', 'linea3-legal-child' ),
            'description' => _x( 'Diagrama de flujo (infinito) de la metodología.', 'Block pattern description', 'linea3-legal-child' ),
            'content'     => $content,
            'categories'  => array( 'antigravity-patterns' ),
        )
    );
}
add_action( 'init', 'antigravity_register_metodologia_pattern' );

/**
 * Registra el patrón de Modalidades de Servicio (Blindaje Continuo e Intervención).
 * Se registra por separado para mantener el código limpio y evitar conflictos de sintaxis.
 */
function antigravity_register_modalidades_pattern(): void {
	$content = <<<'PATTERN_HTML'
<!-- wp:group {"align":"full","className":"l3-modalidades-section","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"base","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull l3-modalidades-section has-base-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">

    <!-- wp:group {"className":"l3-container-standard","layout":{"type":"constrained"}} -->
    <div class="wp-block-group l3-container-standard">

        <!-- Encabezado centrado -->
        <!-- wp:group {"className":"l3-modalidades-header","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","justifyContent":"center"}} -->
        <div class="wp-block-group l3-modalidades-header" style="margin-bottom:var(--wp--preset--spacing--50)">
            <!-- wp:paragraph {"align":"center","className":"services-eyebrow","textColor":"primary"} -->
            <p class="has-text-align-center services-eyebrow has-primary-color has-text-color">ADAPTABLES A TU ETAPA</p>
            <!-- /wp:paragraph -->
            <!-- wp:heading {"textAlign":"center","level":2,"className":"services-title","fontFamily":"serif"} -->
            <h2 class="wp-block-heading has-text-align-center services-title has-serif-font-family">Adaptables a la etapa de su negocio</h2>
            <!-- /wp:heading -->
        </div>
        <!-- /wp:group -->

        <!-- Grid de tarjetas -->
        <!-- wp:columns {"isStackedOnMobile":true,"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|40","top":"var:preset|spacing|40"}}}} -->
        <div class="wp-block-columns is-layout-flex">

            <!-- ===== TARJETA 1: BLINDAJE CONTINUO ===== -->
            <!-- wp:column {"className":"l3-modalidad-card l3-modalidad-blindaje"} -->
            <div class="wp-block-column l3-modalidad-card l3-modalidad-blindaje">
                <!-- wp:group {"className":"l3-modalidad-inner","layout":{"type":"constrained","justifyContent":"center"}} -->
                <div class="wp-block-group l3-modalidad-inner" style="padding:var(--wp--preset--spacing--50)">
                    <!-- Badge pill -->
                    <!-- wp:paragraph {"align":"center","className":"l3-modalidad-badge"} -->
                    <p class="has-text-align-center l3-modalidad-badge">Retainer<br>(Mensualidad)</p>
                    <!-- /wp:paragraph -->
                    <!-- wp:heading {"textAlign":"center","level":3,"className":"has-serif-font-family"} -->
                    <h3 class="wp-block-heading has-text-align-center has-serif-font-family">Blindaje<br>Continuo.</h3>
                    <!-- /wp:heading -->
                    <!-- wp:separator {"className":"is-style-wide"} -->
                    <hr class="wp-block-separator is-style-wide has-alpha-channel-opacity" />
                    <!-- /wp:separator -->
                    <!-- wp:paragraph {"align":"center"} -->
                    <p class="has-text-align-center">Para empresas que requieren un aliado jurídico constante. Prevención, revisión de contratos del día a día y soporte estratégico permanente para asegurar el crecimiento sin interrupciones.</p>
                    <!-- /wp:paragraph -->
                    
                    <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
                    <div class="wp-block-buttons">
                        <!-- wp:button {"className":"btn-primary-gold"} -->
                        <div class="wp-block-button btn-primary-gold"><a class="wp-block-button__link wp-element-button antigravity-modal-trigger">Iniciar Alianza</a></div>
                        <!-- /wp:button -->
                    </div>
                    <!-- /wp:buttons -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:column -->

            <!-- ===== TARJETA 2: INTERVENCIÓN ===== -->
            <!-- wp:column {"className":"l3-modalidad-card l3-modalidad-intervencion"} -->
            <div class="wp-block-column l3-modalidad-card l3-modalidad-intervencion">
                <!-- wp:group {"className":"l3-modalidad-inner","layout":{"type":"constrained","justifyContent":"center"}} -->
                <div class="wp-block-group l3-modalidad-inner" style="padding:var(--wp--preset--spacing--50)">
                    <!-- Badge pill -->
                    <!-- wp:paragraph {"align":"center","className":"l3-modalidad-badge"} -->
                    <p class="has-text-align-center l3-modalidad-badge">On-Demand<br>(Por Tarea)</p>
                    <!-- /wp:paragraph -->
                    <!-- wp:heading {"textAlign":"center","level":3,"className":"has-serif-font-family"} -->
                    <h3 class="wp-block-heading has-text-align-center has-serif-font-family">Intervención<br>Estratégica.</h3>
                    <!-- /wp:heading -->
                    <!-- wp:separator {"className":"is-style-wide"} -->
                    <hr class="wp-block-separator is-style-wide has-alpha-channel-opacity" />
                    <!-- /wp:separator -->
                    <!-- wp:paragraph {"align":"center"} -->
                    <p class="has-text-align-center">Para proyectos específicos, estructuraciones puntuales o resolución de contingencias. Acompañamiento experto exactamente cuando y donde el negocio lo necesita.</p>
                    <!-- /wp:paragraph -->
                    
                    <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
                    <div class="wp-block-buttons">
                        <!-- wp:button {"className":"l3-btn-amber"} -->
                        <div class="wp-block-button l3-btn-amber"><a class="wp-block-button__link wp-element-button antigravity-modal-trigger">Consultar Caso</a></div>
                        <!-- /wp:button -->
                    </div>
                    <!-- /wp:buttons -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:column -->

            <!-- ===== TARJETA 3: LITIGIO ESTRATÉGICO ===== -->
            <!-- wp:column {"className":"l3-modalidad-card l3-modalidad-litigio"} -->
            <div class="wp-block-column l3-modalidad-card l3-modalidad-litigio">
                <!-- wp:group {"className":"l3-modalidad-inner","layout":{"type":"constrained","justifyContent":"center"}} -->
                <div class="wp-block-group l3-modalidad-inner" style="padding:var(--wp--preset--spacing--50)">
                    <!-- Badge pill -->
                    <!-- wp:paragraph {"align":"center","className":"l3-modalidad-badge"} -->
                    <p class="has-text-align-center l3-modalidad-badge">Litigio<br>(Defensa Activa)</p>
                    <!-- /wp:paragraph -->
                    <!-- wp:heading {"textAlign":"center","level":3,"className":"has-serif-font-family"} -->
                    <h3 class="wp-block-heading has-text-align-center has-serif-font-family">Litigio<br>Estratégico.</h3>
                    <!-- /wp:heading -->
                    <!-- wp:separator {"className":"is-style-wide"} -->
                    <hr class="wp-block-separator is-style-wide has-alpha-channel-opacity" />
                    <!-- /wp:separator -->
                    <!-- wp:paragraph {"align":"center"} -->
                    <p class="has-text-align-center">Defensa activa de sus intereses en cualquier instancia judicial. Presencia firme, estrategia de alto nivel y representación comprometida hasta obtener el resultado que su negocio merece.</p>
                    <!-- /wp:paragraph -->
                    
                    <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
                    <div class="wp-block-buttons">
                        <!-- wp:button {"className":"l3-btn-teal"} -->
                        <div class="wp-block-button l3-btn-teal"><a class="wp-block-button__link wp-element-button antigravity-modal-trigger">Evaluar Mi Caso</a></div>
                        <!-- /wp:button -->
                    </div>
                    <!-- /wp:buttons -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:column -->

        </div>
        <!-- /wp:columns -->

    </div>
    <!-- /wp:group -->

</div>
<!-- /wp:group -->
PATTERN_HTML;

	register_block_pattern( 'antigravity/modalidades-servicio', array(
		'title'       => 'Modalidades de Servicio Legal',
		'description' => 'Tres tarjetas: Blindaje, Intervención y Litigio. Diseño editorial premium.',
		'categories'  => array( 'antigravity-patterns' ),
		'keywords'    => array( 'servicio', 'blindaje', 'intervención', 'modalidades', 'legal' ),
		'content'     => $content,
	) );
}
add_action( 'init', 'antigravity_register_modalidades_pattern' );

/**
 * Registra el patrón "Cobertura Legal Estratégica"
 */
function antigravity_register_cobertura_pattern(): void {
    $content = <<<'PATTERN_HTML'
<!-- wp:group {"tagName":"section","align":"full","className":"l3-cobertura-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull l3-cobertura-section">

    <!-- wp:group {"className":"l3-cobertura-container","layout":{"type":"constrained"}} -->
    <div class="wp-block-group l3-cobertura-container">

        <!-- wp:group {"className":"l3-cobertura-header","layout":{"type":"constrained","justifyContent":"center"}} -->
        <div class="wp-block-group l3-cobertura-header">
            <!-- wp:paragraph {"align":"center","className":"services-eyebrow","textColor":"primary"} -->
            <p class="has-text-align-center services-eyebrow has-primary-color has-text-color">SOLUCIONES INTEGRALES</p>
            <!-- /wp:paragraph -->
            <!-- wp:heading {"textAlign":"center","level":2,"className":"l3-cobertura-title","fontFamily":"serif"} -->
            <h2 class="wp-block-heading has-text-align-center l3-cobertura-title has-serif-font-family">Cobertura Legal Estratégica</h2>
            <!-- /wp:heading -->
        </div>
        <!-- /wp:group -->

        <!-- wp:group {"className":"l3-cobertura-grid","layout":{"type":"flex","flexWrap":"nowrap"}} -->
        <div class="wp-block-group l3-cobertura-grid">

            <!-- Columna 1 -->
            <!-- wp:group {"className":"l3-cobertura-card","layout":{"type":"constrained"}} -->
            <div class="wp-block-group l3-cobertura-card">
                <!-- wp:heading {"level":3,"fontFamily":"serif"} -->
                <h3 class="wp-block-heading has-serif-font-family">Corporativo <br>&amp; Negocios</h3>
                <!-- /wp:heading -->
                <!-- wp:list {"className":"l3-cobertura-list"} -->
                <ul class="wp-block-list l3-cobertura-list">
                    <li>Asuntos corporativos</li>
                    <li>Asuntos civiles y comerciales</li>
                    <li>Laboral</li>
                </ul>
                <!-- /wp:list -->
            </div>
            <!-- /wp:group -->

            <!-- Columna 2 -->
            <!-- wp:group {"className":"l3-cobertura-card","layout":{"type":"constrained"}} -->
            <div class="wp-block-group l3-cobertura-card">
                <!-- wp:heading {"level":3,"fontFamily":"serif"} -->
                <h3 class="wp-block-heading has-serif-font-family">Riesgo, Regulación <br>&amp; Estado</h3>
                <!-- /wp:heading -->
                <!-- wp:list {"className":"l3-cobertura-list"} -->
                <ul class="wp-block-list l3-cobertura-list">
                    <li>Tributario y Aduanero</li>
                    <li>Administrativo / Ambiental</li>
                    <li>Regulaciones / Extinción de Dominio</li>
                    <li>Compliance - SAGRILAFT</li>
                </ul>
                <!-- /wp:list -->
            </div>
            <!-- /wp:group -->

            <!-- Columna 3 -->
            <!-- wp:group {"className":"l3-cobertura-card","layout":{"type":"constrained"}} -->
            <div class="wp-block-group l3-cobertura-card">
                <!-- wp:heading {"level":3,"fontFamily":"serif"} -->
                <h3 class="wp-block-heading has-serif-font-family">Sectores <br>Especializados</h3>
                <!-- /wp:heading -->
                <!-- wp:list {"className":"l3-cobertura-list"} -->
                <ul class="wp-block-list l3-cobertura-list">
                    <li>Transporte</li>
                    <li>Urbanístico</li>
                    <li>Cannabis</li>
                    <li>Zonas francas</li>
                </ul>
                <!-- /wp:list -->
            </div>
            <!-- /wp:group -->

        </div>
        <!-- /wp:group -->

        <!-- wp:paragraph {"align":"center","className":"l3-cobertura-footer-text","fontFamily":"serif"} -->
        <p class="has-text-align-center l3-cobertura-footer-text has-serif-font-family">Un ecosistema diseñado para rodear tu negocio de seguridad jurídica en cada frente.</p>
        <!-- /wp:paragraph -->

    </div>
    <!-- /wp:group -->

</section>
<!-- /wp:group -->
PATTERN_HTML;

    register_block_pattern( 'antigravity/cobertura-legal', array(
        'title'       => 'Cobertura Legal Estratégica',
        'description' => 'Grid de 3 columnas con watermark de logo y diseño editorial.',
        'categories'  => array( 'antigravity-patterns' ),
        'keywords'    => array( 'cobertura', 'legal', 'estratégica', 'grid' ),
        'content'     => $content,
    ) );
}
add_action( 'init', 'antigravity_register_cobertura_pattern' );

/**
 * Registra el patrón "Experiencia Legal (Filosofía)"
 */
function antigravity_register_experiencia_pattern(): void {
    $content = <<<'PATTERN_HTML'
<!-- wp:group {"tagName":"section","align":"full","className":"l3-experiencia-section","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull l3-experiencia-section">

    <!-- Marca de agua (Logo Symbol) -->
    <!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"l3-cobertura-watermark"} -->
    <figure class="wp-block-image size-full l3-cobertura-watermark">
        <img src="/wp-content/themes/linea3-legal-child/assets/images/logo-favicon.png" alt="" />
    </figure>
    <!-- /wp:image -->

    <!-- wp:group {"className":"l3-experiencia-container","layout":{"type":"constrained"}} -->
    <div class="wp-block-group l3-experiencia-container">

        <!-- wp:group {"className":"l3-experiencia-grid","layout":{"type":"flex","flexWrap":"nowrap"}} -->
        <div class="wp-block-group l3-experiencia-grid">

            <!-- Columna Izquierda: Filosofía -->
            <!-- wp:group {"className":"l3-experiencia-col-left","layout":{"type":"constrained"}} -->
            <div class="wp-block-group l3-experiencia-col-left">

                <div class="l3-experiencia-header">
                    <!-- wp:paragraph {"className":"l3-experiencia-eyebrow"} -->
                    <p class="l3-experiencia-eyebrow">NUESTRA FILOSOFÍA</p>
                    <!-- /wp:paragraph -->
                    
                    <!-- wp:heading {"level":2,"fontFamily":"serif"} -->
                    <h2 class="wp-block-heading has-serif-font-family">Hablamos desde la experiencia, no solo desde la teoría.</h2>
                    <!-- /wp:heading -->
                    
                    <!-- wp:paragraph -->
                    <p>Somos una firma creada por mujeres que han vivido de cerca lo que significa construir empresa, trabajar, asumir riesgos y buscar crecimiento todos los días.</p>
                    <!-- /wp:paragraph -->
                    <!-- wp:paragraph -->
                    <p>Entendemos que detrás de cada decisión legal hay un negocio, un esfuerzo y un camino.</p>
                    <!-- /wp:paragraph -->
                </div>
            </div>
            <!-- /wp:group -->

            <!-- Separador Vertical -->
            <div class="l3-experiencia-separator"></div>

            <!-- Columna Derecha: Características -->
            <!-- wp:group {"className":"l3-experiencia-col-right","layout":{"type":"constrained"}} -->
            <div class="wp-block-group l3-experiencia-col-right">

                <!-- Feature 1: Empatía -->
                <!-- wp:group {"className":"l3-experiencia-feature","layout":{"type":"constrained"}} -->
                <div class="wp-block-group l3-experiencia-feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:40px; height:40px; margin-bottom:15px; color:#b89664;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    </div>
                    <!-- wp:heading {"level":3,"fontFamily":"serif"} -->
                    <h3 class="wp-block-heading has-serif-font-family">Empatía Empresarial</h3>
                    <!-- /wp:heading -->
                    <!-- wp:paragraph -->
                    <p>Entendemos tu necesidad porque también la hemos vivido.</p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->

                <!-- Feature 2: Simplificación -->
                <!-- wp:group {"className":"l3-experiencia-feature","layout":{"type":"constrained"}} -->
                <div class="wp-block-group l3-experiencia-feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:40px; height:40px; margin-bottom:15px; color:#b89664;"><circle cx="12" cy="12" r="10"></circle><path d="M12 8l-4 4 4 4M16 12H8"></path></svg>
                    </div>
                    <!-- wp:heading {"level":3,"fontFamily":"serif"} -->
                    <h3 class="wp-block-heading has-serif-font-family">Simplificación Radical</h3>
                    <!-- /wp:heading -->
                    <!-- wp:paragraph -->
                    <p>Traducimos lo complejo para que tú y tu equipo lo manejen de la mejor manera.</p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->

                <!-- Feature 3: Acompañamiento -->
                <!-- wp:group {"className":"l3-experiencia-feature","layout":{"type":"constrained"}} -->
                <div class="wp-block-group l3-experiencia-feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:40px; height:40px; margin-bottom:15px; color:#b89664;"><polyline points="13 17 18 12 13 7"></polyline><polyline points="6 17 11 12 6 7"></polyline></svg>
                    </div>
                    <!-- wp:heading {"level":3,"fontFamily":"serif"} -->
                    <h3 class="wp-block-heading has-serif-font-family">Acompañamiento Integral</h3>
                    <!-- /wp:heading -->
                    <!-- wp:paragraph -->
                    <p>No dejamos que recorras el camino solo; reducimos el riesgo de tu desarrollo.</p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->

            </div>
            <!-- /wp:group -->

        </div>
        <!-- /wp:group -->

    </div>
    <!-- /wp:group -->

</section>
<!-- /wp:group -->
PATTERN_HTML;

    register_block_pattern( 'antigravity/experiencia-legal', array(
        'title'       => 'Experiencia Legal (Filosofía)',
        'description' => 'Patrón de dos columnas con separador vertical y diseño editorial premium.',
        'categories'  => array( 'antigravity-patterns' ),
        'keywords'    => array( 'experiencia', 'filosofía', 'empresa', 'legal' ),
        'content'     => $content,
    ) );
}
add_action( 'init', 'antigravity_register_experiencia_pattern' );


/**
 * Registro de bloques dinámicos.
 */
function antigravity_register_dynamic_blocks(): void
{
	register_block_type('antigravity/author-card', array('render_callback' => 'antigravity_render_author_card', 'uses_context' => array('postId')));
}
add_action('init', 'antigravity_register_dynamic_blocks');

	// La función user_contactmethods fue removida para migrar los campos a Información Adicional con mejor control HTML.

/**
 * Añade campos adicionales al perfil (Extracto Profesional)
 */
function antigravity_show_extra_profile_fields($user)
{
	$prefix = get_the_author_meta('antigravity_user_prefix', $user->ID);
	$specialty = get_the_author_meta('antigravity_user_specialty', $user->ID);
	$job_title = get_the_author_meta('antigravity_user_job_title', $user->ID);
	$quote = get_the_author_meta('antigravity_user_quote', $user->ID);
	$quote_visible = get_the_author_meta('antigravity_user_quote_visible', $user->ID);
	$focus = get_the_author_meta('antigravity_user_focus', $user->ID);
	$focus_visible = get_the_author_meta('antigravity_user_focus_visible', $user->ID);
	$languages = get_the_author_meta('antigravity_user_languages', $user->ID);
	$website = get_the_author_meta('antigravity_user_website', $user->ID);
	$website_visible = get_the_author_meta('antigravity_user_website_visible', $user->ID);
	$linkedin = get_the_author_meta('antigravity_user_linkedin', $user->ID);
	$linkedin_visible = get_the_author_meta('antigravity_user_linkedin_visible', $user->ID);
	$twitter = get_the_author_meta('antigravity_user_twitter', $user->ID);
	$twitter_visible = get_the_author_meta('antigravity_user_twitter_visible', $user->ID);
	$email_visible = get_the_author_meta('antigravity_user_email_visible', $user->ID);
	$share_visible = get_the_author_meta('antigravity_user_share_visible', $user->ID);

	// Por defecto, si está vacío y hay URL, lo consideramos visible por compatibilidad con versiones anteriores
	if ($quote_visible === '' && !empty($quote)) $quote_visible = 'yes';
	if ($focus_visible === '' && !empty($focus)) $focus_visible = 'yes';
	if ($website_visible === '' && !empty($website)) $website_visible = 'yes';
	if ($linkedin_visible === '' && !empty($linkedin)) $linkedin_visible = 'yes';
	if ($twitter_visible === '' && !empty($twitter)) $twitter_visible = 'yes';
	if ($email_visible === '') $email_visible = 'yes';
	if ($share_visible === '') $share_visible = 'yes';

	?>
	<style>
		.l3-switch {
			position: relative;
			display: inline-block;
			width: 40px;
			height: 24px;
			vertical-align: middle;
			margin-left: 10px;
		}
		.l3-switch input {
			opacity: 0;
			width: 0;
			height: 0;
		}
		.l3-slider {
			position: absolute;
			cursor: pointer;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background-color: #ccc;
			transition: .4s;
			border-radius: 24px;
		}
		.l3-slider:before {
			position: absolute;
			content: "";
			height: 16px;
			width: 16px;
			left: 4px;
			bottom: 4px;
			background-color: white;
			transition: .4s;
			border-radius: 50%;
		}
		input:checked + .l3-slider {
			background-color: #2271b1;
		}
		input:checked + .l3-slider:before {
			transform: translateX(16px);
		}
		.l3-toggle-wrap {
			display: inline-flex;
			align-items: center;
			margin-left: 15px;
			font-size: 13px;
			color: #646970;
		}
	</style>
	<h3><?php _e('Información Adicional (Linea 3)', 'linea3-legal-child'); ?></h3>
	<table class="form-table">
		<tr>
			<th><label for="antigravity_user_prefix"><?php _e('Prefijo Profesional', 'linea3-legal-child'); ?></label></th>
			<td>
				<?php 
				$prefixes = array(
					'' => 'Ninguno',
					'Dr.' => 'Dr.',
					'Dra.' => 'Dra.',
					'Sr.' => 'Sr.',
					'Sra.' => 'Sra.',
					'Abg.' => 'Abg.',
					'Abogado' => 'Abogado',
					'Abogada' => 'Abogada',
					'Mgtr.' => 'Mgtr.',
					'Magíster' => 'Magíster',
					'Esp.' => 'Esp.',
					'Ph.D.' => 'Ph.D.'
				);
				?>
				<select name="antigravity_user_prefix" id="antigravity_user_prefix">
					<?php foreach ($prefixes as $val => $label) : ?>
						<option value="<?php echo esc_attr($val); ?>" <?php selected($prefix, $val); ?>><?php echo esc_html($label); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php _e('Aparecerá antes del nombre del profesional.', 'linea3-legal-child'); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="antigravity_user_job_title"><?php _e('Cargo', 'linea3-legal-child'); ?></label></th>
			<td>
				<input type="text" name="antigravity_user_job_title" id="antigravity_user_job_title" value="<?php echo esc_attr($job_title); ?>" class="regular-text" />
			</td>
		</tr>
		<tr>
			<th><label for="antigravity_user_specialty"><?php _e('Especialidad', 'linea3-legal-child'); ?></label></th>
			<td>
				<input type="text" name="antigravity_user_specialty" id="antigravity_user_specialty" value="<?php echo esc_attr($specialty); ?>" class="regular-text" />
			</td>
		</tr>
		<tr>
			<th><label for="antigravity_user_website"><?php _e('Sitio Web URL', 'linea3-legal-child'); ?></label></th>
			<td>
				<div style="display: flex; align-items: center; gap: 20px;">
					<input type="url" name="antigravity_user_website" id="antigravity_user_website" value="<?php echo esc_attr($website); ?>" class="regular-text" />
					<div class="l3-toggle-wrap" style="margin: 0;">
						<span style="font-size: 12px; opacity: 0.7;">Mostrar Icono:</span>
						<label class="l3-switch">
							<input type="checkbox" name="antigravity_user_website_visible" value="yes" <?php checked($website_visible, 'yes'); ?>>
							<span class="l3-slider"></span>
						</label>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<th><label for="antigravity_user_linkedin"><?php _e('LinkedIn URL', 'linea3-legal-child'); ?></label></th>
			<td>
				<div style="display: flex; align-items: center; gap: 20px;">
					<input type="url" name="antigravity_user_linkedin" id="antigravity_user_linkedin" value="<?php echo esc_attr($linkedin); ?>" class="regular-text" />
					<div class="l3-toggle-wrap" style="margin: 0;">
						<span style="font-size: 12px; opacity: 0.7;">Mostrar Icono:</span>
						<label class="l3-switch">
							<input type="checkbox" name="antigravity_user_linkedin_visible" value="yes" <?php checked($linkedin_visible, 'yes'); ?>>
							<span class="l3-slider"></span>
						</label>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<th><label for="antigravity_user_twitter"><?php _e('Twitter/X URL', 'linea3-legal-child'); ?></label></th>
			<td>
				<div style="display: flex; align-items: center; gap: 20px;">
					<input type="url" name="antigravity_user_twitter" id="antigravity_user_twitter" value="<?php echo esc_attr($twitter); ?>" class="regular-text" />
					<div class="l3-toggle-wrap" style="margin: 0;">
						<span style="font-size: 12px; opacity: 0.7;">Mostrar Icono:</span>
						<label class="l3-switch">
							<input type="checkbox" name="antigravity_user_twitter_visible" value="yes" <?php checked($twitter_visible, 'yes'); ?>>
							<span class="l3-slider"></span>
						</label>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<th><label for="antigravity_user_whatsapp"><?php _e('WhatsApp Number', 'linea3-legal-child'); ?></label></th>
			<td>
				<div style="display: flex; align-items: center; gap: 20px;">
					<input type="text" name="antigravity_user_whatsapp" id="antigravity_user_whatsapp" value="<?php echo esc_attr(get_the_author_meta('antigravity_user_whatsapp', $user->ID)); ?>" class="regular-text" placeholder="Ej: +573001234567" />
					<div class="l3-toggle-wrap" style="margin: 0;">
						<span style="font-size: 12px; opacity: 0.7;">Mostrar Icono:</span>
						<label class="l3-switch">
							<input type="checkbox" name="antigravity_user_whatsapp_visible" value="yes" <?php checked(get_the_author_meta('antigravity_user_whatsapp_visible', $user->ID), 'yes'); ?>>
							<span class="l3-slider"></span>
						</label>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<th><label><?php _e('Iconos Base', 'linea3-legal-child'); ?></label></th>
			<td>
				<div style="margin-bottom: 10px;">
					<div class="l3-toggle-wrap" style="margin-left: 0;">
						<span style="display:inline-block; width: 120px;">Icono Correo:</span>
						<label class="l3-switch">
							<input type="checkbox" name="antigravity_user_email_visible" value="yes" <?php checked($email_visible, 'yes'); ?>>
							<span class="l3-slider"></span>
						</label>
					</div>
				</div>
				<div>
					<div class="l3-toggle-wrap" style="margin-left: 0;">
						<span style="display:inline-block; width: 120px;">Icono Compartir:</span>
						<label class="l3-switch">
							<input type="checkbox" name="antigravity_user_share_visible" value="yes" <?php checked($share_visible, 'yes'); ?>>
							<span class="l3-slider"></span>
						</label>
					</div>
				</div>
				<p class="description">
					<?php _e('Permite ocultar el botón del formulario de contacto o el botón de compartir enlace.', 'linea3-legal-child'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th><label for="antigravity_user_excerpt"><?php _e('Extracto Profesional', 'linea3-legal-child'); ?></label>
			</th>
			<td>
				<textarea name="antigravity_user_excerpt" id="antigravity_user_excerpt" rows="5" cols="30" class="large-text" style="width: 25em; max-width: 100%;"><?php echo esc_textarea(get_the_author_meta('antigravity_user_excerpt', $user->ID)); ?></textarea>
				<p class="description">
					<?php _e('Breve descripción que aparece en la tarjeta del profesional.', 'linea3-legal-child'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th><label for="antigravity_user_quote"><?php _e('Cita Destacada', 'linea3-legal-child'); ?></label></th>
			<td>
				<div style="display: flex; align-items: center; gap: 20px;">
					<input type="text" name="antigravity_user_quote" id="antigravity_user_quote" value="<?php echo esc_attr($quote); ?>" class="regular-text" />
					<div class="l3-toggle-wrap" style="margin: 0;">
						<span style="font-size: 12px; opacity: 0.7;">Mostrar Cita:</span>
						<label class="l3-switch">
							<input type="checkbox" name="antigravity_user_quote_visible" value="yes" <?php checked($quote_visible, 'yes'); ?>>
							<span class="l3-slider"></span>
						</label>
					</div>
				</div>
				<p class="description"><?php _e('Ej: "La excelencia jurídica no es un acto..."', 'linea3-legal-child'); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="antigravity_user_focus"><?php _e('Enfoque Principal', 'linea3-legal-child'); ?></label></th>
			<td>
				<textarea name="antigravity_user_focus" id="antigravity_user_focus" rows="3" cols="30" class="large-text" style="width: 25em; max-width: 100%;"><?php echo esc_textarea($focus); ?></textarea>
				<div class="l3-toggle-wrap" style="margin-top: 10px;">
					<span style="font-size: 12px; opacity: 0.7;">Mostrar Enfoque:</span>
					<label class="l3-switch">
						<input type="checkbox" name="antigravity_user_focus_visible" value="yes" <?php checked($focus_visible, 'yes'); ?>>
						<span class="l3-slider"></span>
					</label>
				</div>
				<p class="description"><?php _e('Ej: Litigios de Alta Complejidad', 'linea3-legal-child'); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="antigravity_user_languages"><?php _e('Idiomas', 'linea3-legal-child'); ?></label></th>
			<td>
				<textarea name="antigravity_user_languages" id="antigravity_user_languages" rows="3" cols="30" class="large-text" style="width: 25em; max-width: 100%;"><?php echo esc_textarea($languages); ?></textarea>
				<div class="l3-toggle-wrap" style="margin-top: 10px;">
					<span style="font-size: 12px; opacity: 0.7;">Mostrar Idiomas:</span>
					<label class="l3-switch">
						<input type="checkbox" name="antigravity_user_languages_visible" value="yes" <?php checked(get_the_author_meta('antigravity_user_languages_visible', $user->ID), 'yes'); ?>>
						<span class="l3-slider"></span>
					</label>
				</div>
				<p class="description"><?php _e('Uno por línea. Ej: Español (Nativo)', 'linea3-legal-child'); ?> \n <?php _e('Inglés (Avanzado)', 'linea3-legal-child'); ?></p>
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
	
	$fields = array(
		'antigravity_user_prefix',
		'antigravity_user_excerpt',
		'antigravity_user_job_title',
		'antigravity_user_specialty',
		'antigravity_user_quote',
		'antigravity_user_focus',
		'antigravity_user_languages',
		'antigravity_user_website',
		'antigravity_user_linkedin',
		'antigravity_user_twitter',
		'antigravity_user_whatsapp'
	);
	
	foreach ($fields as $field) {
		if (isset($_POST[$field])) {
			update_user_meta($user_id, $field, sanitize_textarea_field($_POST[$field]));
		}
	}

	// Toggles (checkboxes: si no están en $_POST, significa que fueron desmarcados)
	$quote_visible = isset($_POST['antigravity_user_quote_visible']) ? 'yes' : 'no';
	update_user_meta($user_id, 'antigravity_user_quote_visible', $quote_visible);

	$focus_visible = isset($_POST['antigravity_user_focus_visible']) ? 'yes' : 'no';
	update_user_meta($user_id, 'antigravity_user_focus_visible', $focus_visible);

	$website_visible = isset($_POST['antigravity_user_website_visible']) ? 'yes' : 'no';
	update_user_meta($user_id, 'antigravity_user_website_visible', $website_visible);

	$languages_visible = isset($_POST['antigravity_user_languages_visible']) ? 'yes' : 'no';
	update_user_meta($user_id, 'antigravity_user_languages_visible', $languages_visible);

	$linkedin_visible = isset($_POST['antigravity_user_linkedin_visible']) ? 'yes' : 'no';
	update_user_meta($user_id, 'antigravity_user_linkedin_visible', $linkedin_visible);

	$twitter_visible = isset($_POST['antigravity_user_twitter_visible']) ? 'yes' : 'no';
	update_user_meta($user_id, 'antigravity_user_twitter_visible', $twitter_visible);

	$email_visible = isset($_POST['antigravity_user_email_visible']) ? 'yes' : 'no';
	update_user_meta($user_id, 'antigravity_user_email_visible', $email_visible);

	$share_visible = isset($_POST['antigravity_user_share_visible']) ? 'yes' : 'no';
	update_user_meta($user_id, 'antigravity_user_share_visible', $share_visible);

	$whatsapp_visible = isset($_POST['antigravity_user_whatsapp_visible']) ? 'yes' : 'no';
	update_user_meta($user_id, 'antigravity_user_whatsapp_visible', $whatsapp_visible);
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

	// Si no hay post_id pero estamos en un post individual, intentamos obtenerlo del global
	if (!$post_id && is_singular('post')) {
		$post_id = get_the_ID();
	}

	$avatar_url = get_avatar_url($author_id, array('size' => 120));
	$name = get_the_author_meta('display_name', $author_id);
	$prefix = get_the_author_meta('antigravity_user_prefix', $author_id);

	if (!empty($prefix)) {
		$name = $prefix . ' ' . $name;
	}

	$specialty = get_the_author_meta('antigravity_user_specialty', $author_id);
	$meta_html = '';

	if ($post_id > 0) {
		$date = get_the_date('', $post_id);
		$content = get_post_field('post_content', $post_id);
		$word_count = str_word_count(strip_tags($content));
		$reading_time = max(1, ceil($word_count / 200));
		$meta_html = sprintf('<span class="author-post-meta">%s — %d min de lectura</span>', esc_html($date), $reading_time);
	}

	$author_url = get_author_posts_url($author_id);

	return sprintf(
		'<a href="%s" class="antigravity-author-card">
			<div class="author-avatar-wrapper">
				<img src="%s" alt="%s" class="author-avatar" />
			</div>
			<div class="author-data-wrapper">
				<span class="author-name">%s</span>
				%s
				%s
			</div>
		</a>',
		esc_url($author_url),
		esc_url($avatar_url),
		esc_attr($name),
		esc_html($name),
		(!empty($specialty) ? sprintf('<span class="author-specialty">%s</span>', esc_html($specialty)) : ''),
		$meta_html
	);
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
/**
 * Helper function to render the related posts section.
 */
function antigravity_get_related_posts_html($author_id, $current_post_id = 0) {
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 3,
        'author' => $author_id,
        'orderby' => 'date',
        'order' => 'DESC'
    );
    if ($current_post_id) {
        $args['post__not_in'] = array($current_post_id);
    }
    
    $posts = get_posts($args);
    if (empty($posts)) return '';

    $author_posts_url = get_author_posts_url($author_id);
    $output = '<!-- ANTIGRAVITY_START --><section class="related-posts-section"><div class="related-posts-header"><div class="section-vertical-line"></div><div class="related-header-content"><div class="related-header-top"><span class="related-subtitle">MÁS DEL MISMO AUTOR</span><a href="' . esc_url($author_posts_url) . '" class="view-all-link">Ver todas sus publicaciones</a></div><div class="related-header-main"><h2 class="related-title">Publicaciones Relacionadas</h2></div></div></div><div class="blog-listing-wrapper"><div class="antigravity-grid is-layout-grid columns-3">';
    foreach ($posts as $p) {
        $output .= sprintf('<div class="antigravity-card" onclick="window.location=\'%s\'"><article class="wp-block-group"><div class="wp-block-post-featured-image">%s</div><div class="antigravity-card-content"><div class="wp-block-post-terms">%s</div><h3 class="wp-block-post-title">%s</h3>%s</div></article></div>', get_permalink($p->ID), antigravity_get_post_thumbnail_html($p->ID, 'medium_large', array('class' => 'related-post-img')), get_the_term_list($p->ID, 'category', '', ' ', ''), get_the_title($p->ID), antigravity_get_author_card_html($author_id, $p->ID));
    }
    $output .= '</div></div></section><!-- ANTIGRAVITY_END -->';
    return preg_replace('/>\s+</', '><', $output);
}

/**
 * Renderizado de Publicaciones Relacionadas.
 */
function antigravity_related_posts_shortcode()
{
	if (!is_singular('post'))
		return '';
	$author_id = (int) get_post_field('post_author', get_the_ID());
	return antigravity_get_related_posts_html($author_id, get_the_ID());
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

		$author_url = get_author_posts_url($user_id);
		
		// Imagen (Cuadrada/Rectangular con padding CSS)
		$output .= '<div class="linea3-team-card-image-wrap">';
		$output .= '<a href="' . esc_url($author_url) . '">';
		$output .= sprintf(
			'<img src="%s" alt="%s" class="linea3-team-card-image" />',
			esc_url($avatar_url),
			esc_attr($name)
		);
		$output .= '</a>';
		$output .= '</div>';

		// Contenido interno alineado a la izquierda
		$output .= '<div class="linea3-team-card-content">';

		if (!empty($job_title)) {
			$output .= sprintf('<p class="linea3-team-job-title">%s</p>', esc_html($job_title));
		}

		$prefix = get_the_author_meta('antigravity_user_prefix', $user_id);
		$display_name = !empty($prefix) ? $prefix . ' ' . $name : $name;
		$output .= sprintf('<h3 class="linea3-team-name"><a href="%s" style="color:inherit; text-decoration:none;">%s</a></h3>', esc_url($author_url), esc_html($display_name));

		if (!empty($specialty)) {
			$output .= sprintf('<p class="linea3-team-specialty">%s</p>', esc_html($specialty));
		}

		$website = get_the_author_meta('antigravity_user_website', $user_id);
		$linkedin = get_the_author_meta('antigravity_user_linkedin', $user_id);
		$twitter = get_the_author_meta('antigravity_user_twitter', $user_id);

		$website_visible = get_the_author_meta('antigravity_user_website_visible', $user_id);
		$linkedin_visible = get_the_author_meta('antigravity_user_linkedin_visible', $user_id);
		$twitter_visible = get_the_author_meta('antigravity_user_twitter_visible', $user_id);
		$email_visible = get_the_author_meta('antigravity_user_email_visible', $user_id);
		$share_visible = get_the_author_meta('antigravity_user_share_visible', $user_id);

		// Retrocompatibilidad: Si el valor de visibilidad nunca se guardó, se muestra
		if ($website_visible === '' && !empty($website)) $website_visible = 'yes';
		if ($linkedin_visible === '' && !empty($linkedin)) $linkedin_visible = 'yes';
		if ($twitter_visible === '' && !empty($twitter)) $twitter_visible = 'yes';
		if ($email_visible === '') $email_visible = 'yes';
		if ($share_visible === '') $share_visible = 'yes';

		$output .= '<div class="linea3-team-socials">';
		$output .= '<div class="linea3-team-social-icons">';

		if ($share_visible === 'yes') {
			$output .= '<span class="linea3-team-icon-share"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg></span>';
		}

		if (!empty($user->user_email) && $email_visible === 'yes') {
			// Se reemplaza el enlace mailto para evitar exponer el correo electrónico
			$output .= sprintf('<a href="#" class="linea3-team-icon-email linea3-team-contact-btn" data-author-id="%d" data-author-name="%s" data-author-image="%s" aria-label="Contactar a %s"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></a>', $user_id, esc_attr($name), esc_url($avatar_url), esc_attr($name));
		}

		if (!empty($website) && $website_visible === 'yes') {
			$output .= sprintf('<a href="%s" target="_blank" rel="noopener noreferrer" class="linea3-team-icon-website" aria-label="Sitio Web"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg></a>', esc_url($website));
		}

		if (!empty($linkedin) && $linkedin_visible === 'yes') {
			$output .= sprintf('<a href="%s" target="_blank" rel="noopener noreferrer" class="linea3-team-icon-linkedin" aria-label="LinkedIn"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg></a>', esc_url($linkedin));
		}

		if (!empty($twitter) && $twitter_visible === 'yes') {
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
add_filter('manage_post_posts_columns', 'antigravity_add_posts_columns');
add_filter('manage_servicio_posts_columns', 'antigravity_add_posts_columns');
function antigravity_render_posts_columns($column, $post_id)
{
	if ($column === 'featured_image')
		echo get_the_post_thumbnail($post_id, array(50, 50));
	if ($column === 'has_excerpt')
		echo has_excerpt($post_id) ? '✔' : '✖';
}
add_action('manage_post_posts_custom_column', 'antigravity_render_posts_columns', 10, 2);
add_action('manage_servicio_posts_custom_column', 'antigravity_render_posts_columns', 10, 2);

/**
 * Agrega CSS al panel de administración para corregir el tamaño de las imágenes en las columnas.
 */
function antigravity_admin_columns_css() {
    $screen = get_current_screen();
    if ($screen && $screen->base === 'edit' && ($screen->post_type === 'post' || $screen->post_type === 'servicio')) {
        echo '<style>
            .column-featured_image { width: 70px !important; }
            .column-featured_image img {
                width: 50px !important;
                height: 50px !important;
                object-fit: cover;
                border-radius: 4px;
                display: block;
                margin: 0 auto;
                background-color: #f0f0f0;
            }
        </style>';
    }
}
add_action('admin_head', 'antigravity_admin_columns_css');

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
		$output .= sprintf('<div class="antigravity-card" onclick="window.location=\'%s\'"><div class="featured-card-image-wrap"><img src="%s" alt="%s" class="featured-card-image"></div><div class="featured-card-overlay"></div><div class="featured-card-content"><div class="featured-card-meta"><span class="featured-card-category">%s</span><h3 class="featured-card-title">%s</h3></div><div class="featured-card-author">%s</div><div class="featured-card-accent-line"></div></div></div>', get_permalink($p->ID), esc_url($thumb), esc_attr($p->post_title), esc_html($cat_name), esc_html($p->post_title), antigravity_get_author_card_html($author_id, $p->ID));
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

/**
 * Agrega el favicon del sitio desde los assets del tema.
 */
function linea3_legal_child_favicon() {
	$favicon_url = get_stylesheet_directory_uri() . '/assets/images/logo-favicon.png';
	echo '<link rel="icon" href="' . esc_url($favicon_url) . '" type="image/png" />' . "\n";
	echo '<link rel="apple-touch-icon" href="' . esc_url($favicon_url) . '" />' . "\n";
}
add_action('wp_head', 'linea3_legal_child_favicon');
add_action('admin_head', 'linea3_legal_child_favicon');

/**
 * Registrar Custom Post Type: Servicios
 */
function antigravity_register_servicios_cpt() {
    $labels = array(
        'name'                  => _x('Servicios', 'Post Type General Name', 'linea3-legal-child'),
        'singular_name'         => _x('Servicio', 'Post Type Singular Name', 'linea3-legal-child'),
        'menu_name'             => __('Servicios', 'linea3-legal-child'),
        'name_admin_bar'        => __('Servicio', 'linea3-legal-child'),
        'archives'              => __('Archivo de Servicios', 'linea3-legal-child'),
        'attributes'            => __('Atributos del Servicio', 'linea3-legal-child'),
        'parent_item_colon'     => __('Servicio Padre:', 'linea3-legal-child'),
        'all_items'             => __('Todos los servicios', 'linea3-legal-child'),
        'add_new_item'          => __('Añadir nuevo servicio', 'linea3-legal-child'),
        'add_new'               => __('Añadir nuevo', 'linea3-legal-child'),
        'new_item'              => __('Nuevo servicio', 'linea3-legal-child'),
        'edit_item'             => __('Editar servicio', 'linea3-legal-child'),
        'update_item'           => __('Actualizar servicio', 'linea3-legal-child'),
        'view_item'             => __('Ver servicio', 'linea3-legal-child'),
        'view_items'            => __('Ver servicios', 'linea3-legal-child'),
        'search_items'          => __('Buscar servicio', 'linea3-legal-child'),
        'not_found'             => __('No encontrado', 'linea3-legal-child'),
        'not_found_in_trash'    => __('No encontrado en la papelera', 'linea3-legal-child'),
        'featured_image'        => __('Imagen de fondo (Cover)', 'linea3-legal-child'),
        'set_featured_image'    => __('Asignar imagen de fondo', 'linea3-legal-child'),
        'remove_featured_image' => __('Quitar imagen de fondo', 'linea3-legal-child'),
        'use_featured_image'    => __('Usar como imagen de fondo', 'linea3-legal-child'),
        'insert_into_item'      => __('Insertar en servicio', 'linea3-legal-child'),
        'uploaded_to_this_item' => __('Subido a este servicio', 'linea3-legal-child'),
        'items_list'            => __('Lista de servicios', 'linea3-legal-child'),
        'items_list_navigation' => __('Navegación de lista de servicios', 'linea3-legal-child'),
        'filter_items_list'     => __('Filtrar lista de servicios', 'linea3-legal-child'),
    );
    $args = array(
        'label'                 => __('Servicio', 'linea3-legal-child'),
        'description'           => __('Servicios legales ofrecidos', 'linea3-legal-child'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
        'taxonomies'            => array(),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 21,
        'menu_icon'             => 'dashicons-portfolio',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
        'show_in_rest'          => true, // Habilita Gutenberg
    );
    register_post_type('servicio', $args);
}
add_action('init', 'antigravity_register_servicios_cpt', 0);

/**
 * Añadir Metabox para el Ícono del Servicio
 */
function antigravity_add_servicio_icon_metabox() {
    add_meta_box(
        'antigravity_servicio_icon',
        __('Ícono del Servicio', 'linea3-legal-child'),
        'antigravity_render_servicio_icon_metabox',
        'servicio',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'antigravity_add_servicio_icon_metabox');

function antigravity_render_servicio_icon_metabox($post) {
    wp_nonce_field('antigravity_save_servicio_icon', 'antigravity_servicio_icon_nonce');
    $icon_id = get_post_meta($post->ID, '_servicio_icon_id', true);
    $icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'thumbnail') : '';
    
    echo '<div class="antigravity-icon-wrapper" style="text-align:center;">';
    if ($icon_url) {
        echo '<img id="antigravity-icon-preview" src="' . esc_url($icon_url) . '" style="max-width:100%; height:auto; margin-bottom:15px;" />';
    } else {
        echo '<img id="antigravity-icon-preview" src="" style="max-width:100%; height:auto; margin-bottom:15px; display:none;" />';
    }
    echo '<input type="hidden" id="antigravity_servicio_icon_id" name="antigravity_servicio_icon_id" value="' . esc_attr($icon_id) . '" />';
    echo '<button type="button" class="button" id="antigravity-upload-icon-btn">' . __('Seleccionar / Subir Ícono', 'linea3-legal-child') . '</button>';
    echo '<button type="button" class="button" id="antigravity-remove-icon-btn" style="color:red; margin-top:5px; ' . ($icon_id ? '' : 'display:none;') . '">' . __('Quitar Ícono', 'linea3-legal-child') . '</button>';
    echo '</div>';
    
    // Script nativo de WordPress Media Uploader
    ?>
    <script>
    jQuery(document).ready(function($){
        var frame;
        $('#antigravity-upload-icon-btn').on('click', function(e) {
            e.preventDefault();
            if (frame) {
                frame.open();
                return;
            }
            frame = wp.media({
                title: 'Seleccionar o subir ícono',
                button: { text: 'Usar este ícono' },
                multiple: false
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#antigravity-icon-preview').attr('src', attachment.url).show();
                $('#antigravity_servicio_icon_id').val(attachment.id);
                $('#antigravity-remove-icon-btn').show();
            });
            frame.open();
        });
        
        $('#antigravity-remove-icon-btn').on('click', function(e) {
            e.preventDefault();
            $('#antigravity-icon-preview').attr('src', '').hide();
            $('#antigravity_servicio_icon_id').val('');
            $(this).hide();
        });
    });
    </script>
    <?php
}

function antigravity_save_servicio_icon($post_id) {
    if (!isset($_POST['antigravity_servicio_icon_nonce']) || !wp_verify_nonce($_POST['antigravity_servicio_icon_nonce'], 'antigravity_save_servicio_icon')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (isset($_POST['antigravity_servicio_icon_id'])) {
        update_post_meta($post_id, '_servicio_icon_id', sanitize_text_field($_POST['antigravity_servicio_icon_id']));
    }
}
add_action('save_post', 'antigravity_save_servicio_icon');

// Script para encolar medios en admin si no están encolados (para CPT servicio)
function antigravity_enqueue_media_uploader($hook) {
    global $typenow;
    if ($typenow == 'servicio') {
        wp_enqueue_media();
    }
}
add_action('admin_enqueue_scripts', 'antigravity_enqueue_media_uploader');

/**
 * Shortcode Dinámico para Cuadrícula de Servicios
 */
function antigravity_services_grid_shortcode($atts) {
    $atts = shortcode_atts(array(
        'orderby' => 'date', // Puede ser 'date' o 'title'
        'order'   => 'ASC',  // Si es date es ASC (los primeros creados primero), si title es A-Z
    ), $atts);

    $args = array(
        'post_type'      => 'servicio',
        'posts_per_page' => -1, // Mostrar todos
        'orderby'        => $atts['orderby'],
    );
    
    if ($atts['orderby'] === 'title') {
        $args['order'] = isset($atts['order']) ? $atts['order'] : 'ASC';
    } else {
        $args['order'] = isset($atts['order']) ? $atts['order'] : 'ASC'; // Mostrar en el orden en que se van añadiendo
    }

    $query = new WP_Query($args);

    // Contenedor principal de la cuadrícula
    $output = '<!-- ANTIGRAVITY_START --><div class="wp-block-group l3-services-grid">';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            
            // Recoger datos
            $title = get_the_title();
            $permalink = get_permalink();
            // Para el contenido, usamos the_excerpt si existe, sino el content cortado.
            $excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 25);
            $bg_image_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
            // Imagen por defecto si no hay destacada
            if (!$bg_image_url) {
                $bg_image_url = get_stylesheet_directory_uri() . '/assets/images/placeholder-legal.png'; 
            }
            
            // Si hay un icono guardado
            $icon_id = get_post_meta(get_the_ID(), '_servicio_icon_id', true);
            $icon_html = '';
            if ($icon_id) {
                $icon_url = wp_get_attachment_image_url($icon_id, 'thumbnail');
                if ($icon_url) {
                    $icon_html = '<img src="' . esc_url($icon_url) . '" alt="" class="service-icon" />';
                }
            }

            // HTML de la tarjeta basado en el patrón creado
            $output .= '<div class="wp-block-cover l3-service-card">';
            $output .= '<span aria-hidden="true" class="wp-block-cover__background has-base-background-color has-background-dim-80 has-background-dim"></span>';
            $output .= '<img class="wp-block-cover__image-background" alt="" src="' . esc_url($bg_image_url) . '" data-object-fit="cover"/>';
            
            $output .= '<div class="wp-block-cover__inner-container">';
            $output .= '<div class="wp-block-group">';
            if ($icon_html) {
                $output .= $icon_html;
            }
            $output .= '<h3 class="wp-block-heading">' . esc_html($title) . '</h3>';
            $output .= '<div class="service-excerpt">' . esc_html($excerpt) . '</div>';
            $output .= '</div>'; // /wp-block-group
            
            $output .= '</div>'; // /wp-block-cover__inner-container
            $output .= '</div>'; // /wp-block-cover
        }
        wp_reset_postdata();
    } else {
        $output .= '<p>No se han registrado servicios todavía. <a href="' . esc_url(admin_url('post-new.php?post_type=servicio')) . '">Añadir el primero.</a></p>';
    }

    $output .= '</div><!-- ANTIGRAVITY_END -->';
    return $output;
}
add_shortcode('antigravity_services_grid', 'antigravity_services_grid_shortcode');

/**
 * Habilitar soporte para subir archivos SVG de forma segura
 */
function l3_allow_svg_upload($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'l3_allow_svg_upload');

function l3_fix_svg_mime_type($data, $file, $filename, $mimes) {
    $ext = isset($data['ext']) ? $data['ext'] : '';
    if (strlen($ext) < 1) {
        $exploded = explode('.', $filename);
        $ext = strtolower(end($exploded));
    }
    if ($ext === 'svg') {
        $data['type'] = 'image/svg+xml';
        $data['ext'] = 'svg';
    }
    return $data;
}
add_filter('wp_check_filetype_and_ext', 'l3_fix_svg_mime_type', 10, 4);

/**
 * Opciones de Página: Ocultar Título
 */
add_action('init', 'linea3_legal_register_page_meta');
function linea3_legal_register_page_meta() {
    register_post_meta('page', '_linea3_hide_title', array(
        'show_in_rest' => true,
        'single' => true,
        'type' => 'boolean',
    ));
}

add_action('add_meta_boxes', 'linea3_legal_add_page_options_meta_box');
function linea3_legal_add_page_options_meta_box() {
    add_meta_box(
        'linea3_page_options',
        'Opciones de Visualización',
        'linea3_legal_page_options_html',
        'page',
        'side',
        'low'
    );
}

function linea3_legal_page_options_html($post) {
    $hide_title = get_post_meta($post->ID, '_linea3_hide_title', true);
    wp_nonce_field('linea3_page_options_nonce', 'linea3_page_options_nonce_field');
    ?>
    <p>
        <label>
            <input type="checkbox" name="linea3_hide_title" value="1" <?php checked($hide_title, 1); ?>>
            Ocultar el título de esta página
        </label>
    </p>
    <?php
}

add_action('save_post', 'linea3_legal_save_page_options');
function linea3_legal_save_page_options($post_id) {
    if (!isset($_POST['linea3_page_options_nonce_field']) || !wp_verify_nonce($_POST['linea3_page_options_nonce_field'], 'linea3_page_options_nonce')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_page', $post_id)) return;

    if (isset($_POST['linea3_hide_title'])) {
        update_post_meta($post_id, '_linea3_hide_title', 1);
    } else {
        delete_post_meta($post_id, '_linea3_hide_title');
    }
}

// Inyectar CSS para ocultar el título si la opción está activa
add_action('wp_head', 'linea3_legal_hide_title_css');
function linea3_legal_hide_title_css() {
    if (is_page() && get_post_meta(get_the_ID(), '_linea3_hide_title', true)) {
        echo '<style>.wp-block-post-title { display: none !important; }</style>';
    }
}

/**
 * Registro de Custom Post Type: Aliados.
 */
function l3_register_aliados_cpt(): void
{
	$labels = array(
		'name'                  => 'Aliados',
		'singular_name'         => 'Aliado',
		'menu_name'             => 'Aliados',
		'name_admin_bar'        => 'Aliado',
		'add_new'               => 'Añadir Nuevo',
		'add_new_item'          => 'Añadir Nuevo Aliado',
		'new_item'              => 'Nuevo Aliado',
		'edit_item'             => 'Editar Aliado',
		'view_item'             => 'Ver Aliado',
		'all_items'             => 'Todos los Aliados',
		'search_items'          => 'Buscar Aliados',
		'not_found'             => 'No se encontraron aliados.',
		'not_found_in_trash'    => 'No hay aliados en la papelera.',
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array('slug' => 'aliado'),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 20,
		'menu_icon'          => 'dashicons-groups',
		'supports'           => array('title', 'thumbnail'),
		'show_in_rest'       => true,
	);

	register_post_type('l3_aliado', $args);
}
add_action('init', 'l3_register_aliados_cpt');

/**
 * Meta Box para la URL del Aliado.
 */
function l3_aliados_add_meta_box(): void
{
	add_meta_box(
		'l3_aliado_details',
		'Información del Aliado',
		'l3_aliado_details_callback',
		'l3_aliado',
		'normal',
		'high'
	);
}
add_action('add_meta_boxes', 'l3_aliados_add_meta_box');

function l3_aliado_details_callback($post): void
{
	wp_nonce_field('l3_aliado_save_meta', 'l3_aliado_nonce');
	$url = get_post_meta($post->ID, '_l3_aliado_url', true);
	?>
	<p>
		<label for="l3_aliado_url"><strong>URL del Sitio Web:</strong></label><br>
		<input type="url" id="l3_aliado_url" name="l3_aliado_url" value="<?php echo esc_attr($url); ?>" class="widefat" placeholder="https://ejemplo.com">
	</p>
	<?php
}

function l3_aliado_save_meta($post_id): void
{
	if (!isset($_POST['l3_aliado_nonce']) || !wp_verify_nonce($_POST['l3_aliado_nonce'], 'l3_aliado_save_meta')) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	if (isset($_POST['l3_aliado_url'])) {
		update_post_meta($post_id, '_l3_aliado_url', esc_url_raw($_POST['l3_aliado_url']));
	}
}
add_action('save_post', 'l3_aliado_save_meta');

/**
 * Shortcode para mostrar el Slider de Aliados.
 */
function l3_allies_grid_shortcode($atts): string
{
	$args = array(
		'post_type'      => 'l3_aliado',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	);

	$query = new WP_Query($args);
	if (!$query->have_posts()) {
		return '';
	}

	$slider_id = 'l3-slider-' . uniqid();

	$output = '<div class="l3-allies-slider-container" id="' . $slider_id . '">';
	$output .= '<button class="l3-slider-btn prev" aria-label="Anterior"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg></button>';
	
	$output .= '<div class="l3-allies-slider-viewport">';
	$output .= '<div class="l3-allies-slider-track">';
	
	while ($query->have_posts()) {
		$query->the_post();
		$logo_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
		$site_url = get_post_meta(get_the_ID(), '_l3_aliado_url', true);

		if ($logo_url) {
			$output .= '<div class="ally-card">';
			if ($site_url) {
				$output .= '<a href="' . esc_url($site_url) . '" target="_blank" rel="noopener noreferrer">';
			}
			$output .= '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr(get_the_title()) . '">';
			if ($site_url) {
				$output .= '</a>';
			}
			$output .= '</div>';
		}
	}
	wp_reset_postdata();

	$output .= '</div>'; // End track
	$output .= '</div>'; // End viewport
	
	$output .= '<button class="l3-slider-btn next" aria-label="Siguiente"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg></button>';
	$output .= '</div>'; // End container

	// JavaScript for the slider
	$output .= "
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const container = document.getElementById('" . $slider_id . "');
		if (!container) return;

		const track = container.querySelector('.l3-allies-slider-track');
		const viewport = container.querySelector('.l3-allies-slider-viewport');
		const btnPrev = container.querySelector('.l3-slider-btn.prev');
		const btnNext = container.querySelector('.l3-slider-btn.next');
		
		let index = 0;
		let autoplayInterval;

		function getVisibleCards() {
			const cardWidth = container.querySelector('.ally-card').offsetWidth + 20; // 20 is gap
			return Math.floor(viewport.offsetWidth / cardWidth);
		}

		function updateSlider() {
			const cardWidth = container.querySelector('.ally-card').offsetWidth + 20;
			const maxIndex = track.children.length - getVisibleCards();
			
			if (index > maxIndex) index = 0;
			if (index < 0) index = maxIndex > 0 ? maxIndex : 0;

			const offset = index * cardWidth;
			track.style.transform = 'translateX(-' + offset + 'px)';
		}

		function nextSlide() {
			index++;
			updateSlider();
		}

		function prevSlide() {
			index--;
			updateSlider();
		}

		function startAutoplay() {
			stopAutoplay();
			autoplayInterval = setInterval(nextSlide, 5000);
		}

		function stopAutoplay() {
			if (autoplayInterval) clearInterval(autoplayInterval);
		}

		btnNext.addEventListener('click', () => {
			nextSlide();
			startAutoplay();
		});

		btnPrev.addEventListener('click', () => {
			prevSlide();
			startAutoplay();
		});

		// Touch events for mobile
		let touchStartX = 0;
		viewport.addEventListener('touchstart', (e) => {
			touchStartX = e.touches[0].clientX;
			stopAutoplay();
		});

		viewport.addEventListener('touchend', (e) => {
			const touchEndX = e.changedTouches[0].clientX;
			if (touchStartX - touchEndX > 50) nextSlide();
			if (touchStartX - touchEndX < -50) prevSlide();
			startAutoplay();
		});

		window.addEventListener('resize', updateSlider);
		
		// Initial start
		startAutoplay();
	});
	</script>";

	return $output;
}
add_shortcode('antigravity_allies_grid', 'l3_allies_grid_shortcode');

/**
 * Shortcode [antigravity_author_profile] para renderizar el perfil detallado del usuario.
 */
function antigravity_author_profile_shortcode($atts) {
    if (!is_author()) {
        return '';
    }
    
    $author_id = get_queried_object_id();
    $user = get_userdata($author_id);
    
    if (!$user) {
        return '';
    }

    $prefix = get_the_author_meta('antigravity_user_prefix', $author_id);
    $name = $user->display_name;
    $display_name = !empty($prefix) ? $prefix . ' ' . $name : $name;
    
    $website = get_the_author_meta('antigravity_user_website', $author_id);
    $linkedin = get_the_author_meta('antigravity_user_linkedin', $author_id);
    $twitter = get_the_author_meta('antigravity_user_twitter', $author_id);
    $whatsapp = get_the_author_meta('antigravity_user_whatsapp', $author_id);
    
    $website_visible = get_the_author_meta('antigravity_user_website_visible', $author_id);
    $linkedin_visible = get_the_author_meta('antigravity_user_linkedin_visible', $author_id);
    $twitter_visible = get_the_author_meta('antigravity_user_twitter_visible', $author_id);
    $email_visible = get_the_author_meta('antigravity_user_email_visible', $author_id);
    $whatsapp_visible = get_the_author_meta('antigravity_user_whatsapp_visible', $author_id);
    
    if ($website_visible === '' && !empty($website)) $website_visible = 'yes';
    if ($linkedin_visible === '' && !empty($linkedin)) $linkedin_visible = 'yes';
    if ($twitter_visible === '' && !empty($twitter)) $twitter_visible = 'yes';
    if ($whatsapp_visible === '' && !empty($whatsapp)) $whatsapp_visible = 'yes';
    if ($email_visible === '') $email_visible = 'yes';
    
    $job_title = get_the_author_meta('antigravity_user_job_title', $author_id);
    if (empty($job_title)) {
        // Fallback al rol o algo por defecto si está vacío, pero mejor vacío.
        $job_title = 'Especialista Legal';
    }
    
    $quote = get_the_author_meta('antigravity_user_quote', $author_id);
    $quote_visible = get_the_author_meta('antigravity_user_quote_visible', $author_id);
    if ($quote_visible === '' && !empty($quote)) {
        $quote_visible = 'yes';
    }
    
    // Trayectoria construida desde Información Biográfica (description) y Extracto Profesional
    $bio = $user->description;
    $excerpt = get_the_author_meta('antigravity_user_excerpt', $author_id);
    
    $focus = get_the_author_meta('antigravity_user_focus', $author_id);
    $focus_visible = get_the_author_meta('antigravity_user_focus_visible', $author_id);
    if ($focus_visible === '' && !empty($focus)) {
        $focus_visible = 'yes';
    }
    
    $languages = get_the_author_meta('antigravity_user_languages', $author_id);
    
    $avatar_url = get_avatar_url($author_id, array('size' => 800));
    
    $languages_visible = get_the_author_meta('antigravity_user_languages_visible', $author_id);
    if ($languages_visible === '' && !empty($languages)) {
        $languages_visible = 'yes';
    }
    
    ob_start();
    ?>
    <!-- ANTIGRAVITY_START -->
    <div class="l3-author-profile-wrapper l3-container-standard">
        
        <!-- Header del Perfil -->
        <div class="l3-author-profile-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 60px; gap: 60px;">
            <div class="l3-author-profile-title-col" style="display: flex; gap: 30px; align-items: stretch;">
                <div class="section-vertical-line" style="width: 2px; background: var(--wp--preset--color--primary); flex-shrink: 0;"></div>
                <div class="l3-author-title-content">
                    <p class="l3-author-role-eyebrow" style="margin-bottom: 5px;"><?php echo esc_html($job_title); ?></p>
                    <h1 class="l3-author-name-huge" style="margin-bottom: 15px;"><?php echo esc_html($display_name); ?></h1>
                
                <!-- Iconos de Redes y Contacto -->
                <div class="l3-author-social-header" style="display: flex; gap: 18px; margin-bottom: 25px; align-items: center;">
                    <?php if (!empty($user->user_email) && $email_visible === 'yes'): ?>
                        <a href="#" class="linea3-team-icon-email linea3-team-contact-btn" 
                           data-author-id="<?php echo $author_id; ?>" 
                           data-author-name="<?php echo esc_attr($name); ?>" 
                           data-author-image="<?php echo esc_url($avatar_url); ?>" 
                           title="Contactar"
                           style="color: var(--wp--preset--color--primary); transition: opacity 0.3s;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($whatsapp) && $whatsapp_visible === 'yes'): ?>
                        <?php 
                        $wa_link = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $whatsapp);
                        ?>
                        <a href="<?php echo esc_url($wa_link); ?>" target="_blank" rel="noopener noreferrer" title="WhatsApp"
                           style="color: var(--wp--preset--color--primary); transition: opacity 0.3s;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 1 1-7.6-11.7 8.38 8.38 0 0 1 3.8.9L21 3l-1.4 5.4L21 11.5z"></path></svg>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($linkedin) && $linkedin_visible === 'yes'): ?>
                        <a href="<?php echo esc_url($linkedin); ?>" target="_blank" rel="noopener noreferrer" title="LinkedIn"
                           style="color: var(--wp--preset--color--primary); transition: opacity 0.3s;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($website) && $website_visible === 'yes'): ?>
                        <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener noreferrer" title="Sitio Web"
                           style="color: var(--wp--preset--color--primary); transition: opacity 0.3s;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($twitter) && $twitter_visible === 'yes'): ?>
                        <a href="<?php echo esc_url($twitter); ?>" target="_blank" rel="noopener noreferrer" title="Twitter/X"
                           style="color: var(--wp--preset--color--primary); transition: opacity 0.3s;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path></svg>
                        </a>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($quote) && $quote_visible === 'yes'): ?>
                <div class="l3-author-quote-wrapper" style="margin-top: 25px; display: flex; align-items: flex-start; gap: 20px;">
                    <p class="l3-author-quote-text" style="margin: 0; font-style: italic; color: rgba(255, 255, 255, 0.7); line-height: 1.6; font-size: 1.1rem; flex: 1;"><?php echo esc_html($quote); ?></p>
                    <div class="l3-quote-decoration" style="flex-shrink: 0; opacity: 0.15; color: #fff;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor"><path d="M14.017 21L14.017 18C14.017 16.8954 14.9124 16 16.017 16H19.017C19.5693 16 20.017 15.5523 20.017 15V9C20.017 8.44772 19.5693 8 19.017 8H15.017C14.4647 8 14.017 8.44772 14.017 9V12C14.017 12.5523 13.5693 13 13.017 13H11.017C10.4647 13 10.017 12.5523 10.017 12V9C10.017 6.79086 11.8079 5 14.017 5H19.017C21.2261 5 23.017 6.79086 23.017 9V15C23.017 18.3137 20.3307 21 17.017 21H14.017ZM3.017 21L3.017 18C3.017 16.8954 3.91243 16 5.017 16H8.017C8.56928 16 9.017 15.5523 9.017 15V9C9.017 8.44772 8.56928 8 8.017 8H4.017C3.46472 8 3.017 8.44772 3.017 9V12C3.017 12.5523 2.56928 13 2.017 13H0.017C-0.535282 13 -1.017 12.5523 -1.017 12V9C-1.017 6.79086 0.773858 5 3.017 5H8.017C10.2261 5 12.017 6.79086 12.017 9V15C12.017 18.3137 9.33072 21 6.017 21H3.017Z"/></svg>
                    </div>
                </div>
                <?php endif; ?>
                </div>
            </div>

            <div class="l3-author-profile-image-col">
                <div class="linea3-team-card" style="padding: 25px; height: auto; cursor: default; transition: all 0.4s ease;">
                    <div class="linea3-team-card-image-wrap" style="margin-bottom: 0; aspect-ratio: 1/1.2;">
                        <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($display_name); ?>" class="linea3-team-card-image" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido del Perfil -->
        <div class="l3-author-profile-content">
            <?php 
            $has_sidebar_content = (!empty($focus) && $focus_visible === 'yes') || (!empty($languages) && $languages_visible === 'yes');
            if ($has_sidebar_content): 
            ?>
            <div class="l3-author-sidebar">
                <div class="l3-sidebar-title-wrapper" style="margin-bottom: 30px;">
                    <h2 class="l3-sidebar-title" style="margin: 0; font-size: 1.5rem; color: var(--wp--preset--color--primary); text-transform: uppercase; letter-spacing: 1px;">Trayectoria y Visión</h2>
                </div>
                
                <?php if (!empty($focus) && $focus_visible === 'yes'): ?>
                <div class="l3-sidebar-block l3-focus-block">
                    <h3 class="l3-sidebar-subtitle">ENFOQUE PRINCIPAL</h3>
                    <div class="l3-sidebar-text"><?php echo wpautop(esc_html($focus)); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($languages) && $languages_visible === 'yes'): ?>
                <div class="l3-sidebar-block l3-languages-block">
                    <h3 class="l3-sidebar-subtitle">IDIOMAS</h3>
                    <ul class="l3-languages-list">
                        <?php 
                        $langs = explode("\n", $languages);
                        foreach($langs as $lang) {
                            if (trim($lang) !== '') {
                                echo '<li><span class="l3-lang-bullet"></span>' . esc_html(trim($lang)) . '</li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="l3-author-main-text">
                <div class="l3-trajectory-text">
                    <?php if (!empty($bio)): ?>
                        <p><?php echo nl2br(esc_html($bio)); ?></p>
                    <?php endif; ?>
                    
                    <?php if (!empty($excerpt)): ?>
                        <p class="l3-trajectory-excerpt" style="margin-top: 30px; color: rgba(255, 255, 255, 0.6); font-size: 0.95em;"><?php echo nl2br(esc_html($excerpt)); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </div>
        
    </div>
    <!-- ANTIGRAVITY_END -->
    <?php
    $output = ob_get_clean();
    return preg_replace('/>\s+</', '><', $output);
}
add_shortcode('antigravity_author_profile', 'antigravity_author_profile_shortcode');

/**
 * Shortcode to render the publication section header in author profile.
 */
/**
 * Shortcode to render the entire publication section in author profile.
 * Identical to the single post related posts section.
 */
function antigravity_author_publications_section_shortcode() {
    $author = get_queried_object();
    if (!$author || !isset($author->ID)) {
        return '';
    }
    return antigravity_get_related_posts_html($author->ID);
}
add_shortcode('antigravity_author_publications_section', 'antigravity_author_publications_section_shortcode');
