/**
 * Linea 3 Estudio Legal - Sistema de Reseñas de Clientes (JS Frontend)
 * Interactividad refinada: transiciones de pasos, estrellas interactivas, drag & drop y AJAX.
 */

jQuery(document).ready(function($) {
	// Verificar si el contenedor del formulario existe antes de actuar
	if ($('#l3-resenas-form-container').length === 0) {
		return;
	}

	// ── Variables de Estado ──
	var currentFlow = ''; // 'linkedin' o 'manual'
	var selectedRating = 0;
	var mockProfiles = {
		profile1: {
			name: 'Juan Pérez Alarcón',
			url: 'https://www.linkedin.com/in/juan-perez-legal',
			cargo: 'Director Jurídico',
			empresa: 'Corporación Alpha',
			avatar: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=faces'
		},
		profile2: {
			name: 'Laura Sofía Restrepo',
			url: 'https://www.linkedin.com/in/laura-sofia-restrepo',
			cargo: 'Coordinadora de Recursos Humanos',
			empresa: 'Talent Group',
			avatar: 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&h=150&fit=crop&crop=faces'
		}
	};

	// ── Selectores del DOM ──
	var $container = $('#l3-resenas-form-container');
	var $step0 = $('#l3-step-welcome');
	var $stepA = $('#l3-step-linkedin-flow');
	var $stepB = $('#l3-step-manual-flow');
	
	// Modales y botones
	var $linkedinModal = $('#l3-linkedin-modal');
	var $linkedinTrigger = $('#l3-trigger-linkedin');
	var $manualTrigger = $('#l3-trigger-manual');
	var $btnBackList = $('.l3-btn-back');
	var $linkedinProfileOption = $('.l3-linkedin-profile-option');

	// Inputs ocultos y dinámicos
	var $ratingInput = $('input[name="resena_rating"]');
	var $viaLinkedinInput = $('input[name="resena_via_linkedin"]');
	var $linkedinUrlInput = $('#resena_linkedin_url');
	var $linkedinNameInput = $('#resena_linkedin_nombre');
	var $linkedinAvatarInput = $('#resena_linkedin_avatar');

	// Textareas y contadores
	var $textareas = $('.l3-resena-textarea');

	// ── 1. Manejo de Flujos y Pasos ──

	function switchStep($fromStep, $toStep) {
		$fromStep.removeClass('l3-active-step');
		setTimeout(function() {
			$fromStep.hide();
			$toStep.show().addClass('l3-active-step');
		}, 350);
	}

	// Acción: Iniciar Flujo Manual (Flujo B)
	$manualTrigger.on('click', function(e) {
		e.preventDefault();
		currentFlow = 'manual';
		$viaLinkedinInput.val('0');
		resetRating();
		switchStep($step0, $stepB);
	});

	// Acción: Abrir Modal LinkedIn OAuth
	$linkedinTrigger.on('click', function(e) {
		e.preventDefault();
		$linkedinModal.addClass('l3-modal-open');
	});

	// Cerrar Modal LinkedIn
	$('.l3-linkedin-close, #l3-linkedin-modal').on('click', function(e) {
		if (e.target === this || $(e.target).hasClass('l3-linkedin-close')) {
			$linkedinModal.removeClass('l3-modal-open');
		}
	});

	// Selección de Perfil en Modal LinkedIn (Mock OAuth)
	$linkedinProfileOption.on('click', function() {
		var profileId = $(this).data('profile');
		var profileData = mockProfiles[profileId];

		if (profileData) {
			// Inyectar datos recuperados en el formulario
			$linkedinUrlInput.val(profileData.url);
			$linkedinNameInput.val(profileData.name);
			$linkedinAvatarInput.val(profileData.avatar);

			// Previsualizar datos de la tarjeta LinkedIn
			$('#l3-card-avatar').attr('src', profileData.avatar);
			$('#l3-card-name').text(profileData.name);
			$('#l3-card-profile-url').attr('href', profileData.url).text(profileData.url);

			// Rellenar automáticamente empresa y cargo del perfil
			$('#resena_empresa_a').val(profileData.empresa);
			$('#resena_cargo_a').val(profileData.cargo);

			currentFlow = 'linkedin';
			$viaLinkedinInput.val('1');
			resetRating();

			// Cerrar modal y pasar al Flujo A
			$linkedinModal.removeClass('l3-modal-open');
			setTimeout(function() {
				switchStep($step0, $stepA);
			}, 300);
		}
	});

	// Botón Volver (Regresar a Paso 0)
	$btnBackList.on('click', function(e) {
		e.preventDefault();
		var $currentStep = $(this).closest('.l3-resenas-step');
		switchStep($currentStep, $step0);
		currentFlow = '';
	});

	// ── 2. Selector de Calificación (Estrellas Interactivas) ──

	var ratingLabels = {
		0: 'Sin calificación',
		1: 'Muy insatisfecho',
		2: 'Insatisfecho',
		3: 'Satisfecho',
		4: 'Muy satisfecho',
		5: 'Excelente servicio'
	};

	function resetRating() {
		selectedRating = 0;
		$ratingInput.val(0);
		$('.l3-frontend-star').removeClass('l3-frontend-star--active l3-frontend-star--hover');
		$('.l3-stars-text').text(ratingLabels[0]);
	}

	$('.l3-frontend-star').on('mouseenter', function() {
		var starVal = $(this).data('value');
		var $starsContainer = $(this).closest('.l3-stars-selector');
		
		$starsContainer.find('.l3-frontend-star').each(function() {
			if ($(this).data('value') <= starVal) {
				$(this).addClass('l3-frontend-star--hover');
			} else {
				$(this).removeClass('l3-frontend-star--hover');
			}
		});
		$starsContainer.find('.l3-stars-text').text(ratingLabels[starVal]);
	}).on('mouseleave', function() {
		var $starsContainer = $(this).closest('.l3-stars-selector');
		$starsContainer.find('.l3-frontend-star').removeClass('l3-frontend-star--hover');
		
		// Restaurar al valor seleccionado
		$starsContainer.find('.l3-frontend-star').each(function() {
			if ($(this).data('value') <= selectedRating) {
				$(this).addClass('l3-frontend-star--active');
			} else {
				$(this).removeClass('l3-frontend-star--active');
			}
		});
		$starsContainer.find('.l3-stars-text').text(ratingLabels[selectedRating]);
	}).on('click', function() {
		selectedRating = $(this).data('value');
		$ratingInput.val(selectedRating);
		
		var $starsContainer = $(this).closest('.l3-stars-selector');
		$starsContainer.find('.l3-frontend-star').each(function() {
			if ($(this).data('value') <= selectedRating) {
				$(this).addClass('l3-frontend-star--active');
			} else {
				$(this).removeClass('l3-frontend-star--active');
			}
		});
	});

	// ── 3. Drag & Drop y Visualizador de Archivos (Flujo Manual) ──

	var $fileInput = $('#resena_foto_file');
	var $uploadZone = $('#l3-upload-zone');
	var $previewWrapper = $('#l3-avatar-preview-wrap');

	// Arrastrar sobre la zona
	$uploadZone.on('dragover', function(e) {
		e.preventDefault();
		$(this).addClass('l3-dragover');
	}).on('dragleave', function() {
		$(this).removeClass('l3-dragover');
	}).on('drop', function(e) {
		e.preventDefault();
		$(this).removeClass('l3-dragover');
		
		var files = e.originalEvent.dataTransfer.files;
		if (files.length > 0) {
			$fileInput[0].files = files;
			handleFileSelect(files[0]);
		}
	});

	// Clic en la zona de subida (dispara el input file)
	$fileInput.on('change', function() {
		if (this.files.length > 0) {
			handleFileSelect(this.files[0]);
		}
	});

	function handleFileSelect(file) {
		// Validar tamaño máximo (4MB)
		var maxSize = 4 * 1024 * 1024;
		if (file.size > maxSize) {
			alert('El archivo supera el tamaño máximo permitido de 4MB.');
			$fileInput.val('');
			$previewWrapper.hide();
			return;
		}

		// Validar formato de imagen
		var allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
		if (allowedTypes.indexOf(file.type) === -1) {
			alert('Formato de archivo no permitido. Solo se aceptan imágenes JPG, PNG o WEBP.');
			$fileInput.val('');
			$previewWrapper.hide();
			return;
		}

		// Leer imagen y mostrar preview circular
		var reader = new FileReader();
		reader.onload = function(e) {
			$previewWrapper.find('img').attr('src', e.target.result);
			$previewWrapper.find('span').text(file.name);
			$previewWrapper.fadeIn(300);
		};
		reader.readAsDataURL(file);
	}

	// ── 4. Contador de Caracteres en tiempo real ──

	$textareas.on('input', function() {
		var content = $(this).val();
		var len = content.length;
		var $counterWrap = $(this).closest('.l3-form-group').find('.l3-char-counter');
		var $counterVal = $counterWrap.find('span');

		$counterVal.text(len);

		if (len > 140) {
			// Forzar truncado a 140
			$(this).val(content.substring(0, 140));
			$counterVal.text(140);
			$counterWrap.addClass('l3-char-counter--exceeded').removeClass('l3-char-counter--warning');
			$(this).addClass('l3-textarea--exceeded');
		} else if (len >= 120) {
			$counterWrap.addClass('l3-char-counter--warning').removeClass('l3-char-counter--exceeded');
			$(this).removeClass('l3-textarea--exceeded');
		} else {
			$counterWrap.removeClass('l3-char-counter--warning l3-char-counter--exceeded');
			$(this).removeClass('l3-textarea--exceeded');
		}
	});

	// ── 5. Envío Asíncrono AJAX ──

	$('.l3-resenas-form').on('submit', function(e) {
		e.preventDefault();

		var $form = $(this);
		var $btnSubmit = $form.find('.l3-btn-submit');
		
		// Evitar envíos múltiples
		if ($btnSubmit.hasClass('l3-btn--loading')) {
			return;
		}

		// Limpiar alertas previas
		$('.l3-resenas-alert').remove();

		// Crear objeto FormData para soportar la subida binaria de archivos
		var formData = new FormData();
		
		// Metadatos comunes
		formData.append('action', 'l3_submit_resena');
		formData.append('nonce', l3_resenas_params.nonce);
		formData.append('via_linkedin', $viaLinkedinInput.val());
		formData.append('rating', $ratingInput.val());

		// Calificación mínima requerida
		if (parseInt($ratingInput.val()) === 0) {
			displayAlert($form, 'Por favor, selecciona una calificación con estrellas.', 'error');
			return;
		}

		if (currentFlow === 'linkedin') {
			// Datos flujo LinkedIn
			formData.append('nombre', $linkedinNameInput.val());
			formData.append('linkedin_url', $linkedinUrlInput.val());
			formData.append('foto_url', $linkedinAvatarInput.val());
			formData.append('empresa', $('#resena_empresa_a').val());
			formData.append('cargo', $('#resena_cargo_a').val());
			formData.append('contenido', $('#resena_contenido_a').val());
			
			var linkedinAuth = $('#resena_linkedin_auth').is(':checked') ? '1' : '0';
			formData.append('linkedin_auth', linkedinAuth);
		} else {
			// Datos flujo manual
			formData.append('nombre', $('#resena_nombre_b').val());
			formData.append('empresa', $('#resena_empresa_b').val());
			formData.append('cargo', $('#resena_cargo_b').val());
			formData.append('red_social_tipo', $('#resena_red_social_tipo').val());
			formData.append('red_social_url', $('#resena_red_social_url').val());
			formData.append('contenido', $('#resena_contenido_b').val());

			// Foto manual
			if ($fileInput[0].files.length > 0) {
				formData.append('foto_file', $fileInput[0].files[0]);
			}
		}

		// Visual Loading State
		$btnSubmit.addClass('l3-btn--loading');

		// Ejecución Fetch / AJAX
		$.ajax({
			url: l3_resenas_params.ajax_url,
			type: 'POST',
			data: formData,
			contentType: false,
			processData: false,
			success: function(response) {
				$btnSubmit.removeClass('l3-btn--loading');
				if (response.success) {
					displayAlert($form, response.data.message, 'success');
					
					// Deshabilitar y limpiar formulario
					$form.find('input, textarea, select, button').prop('disabled', true);
					$form.find('.l3-frontend-star').css('pointer-events', 'none');
					
					// Animación de regreso al inicio tras éxito (4 segundos)
					setTimeout(function() {
						location.reload();
					}, 4000);
				} else {
					displayAlert($form, response.data.message, 'error');
				}
			},
			error: function() {
				$btnSubmit.removeClass('l3-btn--loading');
				displayAlert($form, 'Ocurrió un error inesperado al procesar la solicitud. Por favor, intenta de nuevo.', 'error');
			}
		});
	});

	function displayAlert($targetForm, message, type) {
		var alertClass = type === 'success' ? 'l3-resenas-alert--success' : 'l3-resenas-alert--error';
		var icon = type === 'success' ? '✓' : '✗';
		
		var alertHtml = '<div class="l3-resenas-alert ' + alertClass + '">' +
							'<strong>' + icon + '</strong> ' +
							'<span>' + message + '</span>' +
						'</div>';

		$targetForm.prepend(alertHtml);

		// Smooth Scroll al encabezado del formulario para visibilidad de alerta
		$('html, body').animate({
			scrollTop: $container.offset().top - 40
		}, 300);
	}
});
