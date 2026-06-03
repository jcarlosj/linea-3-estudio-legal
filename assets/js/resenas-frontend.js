/**
 * Linea 3 Estudio Legal - Sistema de Reseñas de Clientes (JS Frontend)
 * Interactividad refinada: transiciones de pasos, estrellas interactivas, drag & drop y AJAX.
 */

jQuery(document).ready(function($) {
	// Verificar si el contenedor del formulario existe antes de actuar
	if ($('#l3-resenas-form-container').length === 0) {
		return;
	}

	// Inyectar URL de OAuth de LinkedIn real en el botón desde el backend
	if (typeof l3_resenas_params !== 'undefined' && l3_resenas_params.linkedin_auth_url) {
		$('#l3-real-linkedin-btn').attr('href', l3_resenas_params.linkedin_auth_url);
	}

	// Mover los modales al final del body para evitar problemas de stacking context en position: fixed
	var $choiceModal = $('#l3-review-choice-modal');
	if ($choiceModal.length) {
		$choiceModal.appendTo('body');
	}
	var $linkedinModal = $('#l3-linkedin-modal');
	if ($linkedinModal.length) {
		$linkedinModal.appendTo('body');
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

	// ── Detectar flujo automático desde URL ──
	var urlParams = new URLSearchParams(window.location.search);
	var urlFlow = urlParams.get('flow');
	if (urlFlow === 'manual') {
		currentFlow = 'manual';
		$viaLinkedinInput.val('0');
		setTimeout(function() {
			resetRating();
			$('#l3-review-choice-modal').addClass('is-visible');
			$('body').addClass('l3-modal-open-lock');
			$step0.removeClass('l3-active-step').hide();
			$stepB.show().addClass('l3-active-step');
		}, 100);
	} else if (urlFlow === 'linkedin') {
		setTimeout(function() {
			$('#l3-review-choice-modal').addClass('is-visible');
			$('body').addClass('l3-modal-open-lock');
			$linkedinModal.addClass('l3-modal-open');
		}, 100);
	}

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
		
		// Ocultar modal principal con elegancia para dar paso al nuevo formulario
		$('#l3-review-choice-modal').removeClass('is-visible');
		
		setTimeout(function() {
			// Preparar los pasos internamente
			$step0.removeClass('l3-active-step').hide();
			$stepB.show().addClass('l3-active-step');
			
			// Restaurar el modal principal revelando el formulario manual
			$('#l3-review-choice-modal').addClass('is-visible');
		}, 350);
	});

	// Acción: Abrir Modal LinkedIn OAuth
	$linkedinTrigger.on('click', function(e) {
		e.preventDefault();
		
		// Ocultar modal principal con elegancia para dar paso al siguiente
		$('#l3-review-choice-modal').removeClass('is-visible');
		
		setTimeout(function() {
			$linkedinModal.addClass('l3-modal-open');
		}, 350); // Esperar a que el modal anterior se difumine
	});

	// Lógica de reseteo al cerrar el modal de selección
	function resetModalForms() {
		// Reset forms
		$('#l3-form-linkedin')[0].reset();
		$('#l3-form-manual')[0].reset();
		
		// Reset rating
		resetRating();
		
		// Reset avatar preview manual
		$('#l3-avatar-preview-wrap').hide().find('img').attr('src', '');
		
		// Reset dynamic alerts
		$('.l3-resenas-alert').remove();
		
		// Reset submit button spinner states
		$('.l3-btn-submit').removeClass('l3-btn--loading').prop('disabled', false);
		
		// Re-enable form fields
		$('#l3-resenas-form-container').find('input, textarea, select, button').prop('disabled', false);
		$('#l3-resenas-form-container').find('.l3-frontend-star').css('pointer-events', 'auto');
		
		// Reset steps: show step0, hide stepA and stepB
		$stepA.removeClass('l3-active-step').hide();
		$stepB.removeClass('l3-active-step').hide();
		$step0.show().addClass('l3-active-step');
		currentFlow = '';
	}

	// Registrar listeners en jQuery para el modal choice
	$('#l3-close-choice-modal, #l3-review-choice-modal').on('click', function(e) {
		if (e.target === this || $(e.target).attr('id') === 'l3-close-choice-modal') {
			resetModalForms();
			$('#l3-review-choice-modal').removeClass('is-visible');
			$('body').removeClass('l3-modal-open-lock');
		}
	});

	// Cerrar Modal LinkedIn
	$('.l3-linkedin-close, #l3-linkedin-modal').on('click', function(e) {
		if (e.target === this || $(e.target).hasClass('l3-linkedin-close')) {
			$linkedinModal.removeClass('l3-modal-open');
			
			// Al cancelar, devolvemos al usuario al modal principal con gracia
			setTimeout(function() {
				$('#l3-review-choice-modal').addClass('is-visible');
			}, 350);
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

			// Cerrar modal LinkedIn (Mock OAuth)
			$linkedinModal.removeClass('l3-modal-open');
			
			// Preparar los pasos en background de forma instantánea
			$step0.removeClass('l3-active-step').hide();
			$stepA.show().addClass('l3-active-step');
			
			// Restaurar el modal principal revelando el siguiente paso
			setTimeout(function() {
				$('#l3-review-choice-modal').addClass('is-visible');
			}, 350);
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
		$ratingInput.val('');
		$('.l3-stars-filled').css('width', '0%');
		$('.l3-rating-range').val(0);
		$('.l3-stars-text').text('Sin calificación');
	}

	function getLabelForValue(val) {
		if (val == 0) return 'Sin calificación';
		if (val >= 1 && val < 2) return 'Muy insatisfecho';
		if (val >= 2 && val < 3) return 'Insatisfecho';
		if (val >= 3 && val < 4) return 'Satisfecho';
		if (val >= 4 && val < 5) return 'Muy satisfecho';
		if (val == 5) return 'Excelente servicio';
		return '';
	}

	$('.l3-rating-range').on('input', function() {
		var val = parseFloat($(this).val());
		if (val < 1.0) {
			val = 1.0;
			$(this).val(1.0);
		}
		val = val.toFixed(1);
		selectedRating = val;
		$ratingInput.val(val);
		
		var percentage = (val / 5) * 100;
		var $starsFilled = $(this).siblings('.l3-stars-filled');
		$starsFilled.css('width', percentage + '%');
		
		if (val == 5.0) {
			$starsFilled.addClass('l3-stars-perfect');
		} else {
			$starsFilled.removeClass('l3-stars-perfect');
		}
		
		var label = getLabelForValue(val);
		$(this).closest('.l3-star-rating-wrapper').find('.l3-stars-text').text(val + ' - ' + label);
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

		if (len > 240) {
			// Forzar truncado a 240
			$(this).val(content.substring(0, 240));
			$counterVal.text(240);
			$counterWrap.addClass('l3-char-counter--exceeded').removeClass('l3-char-counter--warning');
			$(this).addClass('l3-textarea--exceeded');
		} else if (len >= 220) {
			$counterWrap.addClass('l3-char-counter--warning').removeClass('l3-char-counter--exceeded');
			$(this).removeClass('l3-textarea--exceeded');
		} else {
			$counterWrap.removeClass('l3-char-counter--warning l3-char-counter--exceeded');
			$(this).removeClass('l3-textarea--exceeded');
		}
	});

	// ── 5. Actualización de Tarjeta LinkedIn en tiempo real ──
	$('#resena_linkedin_url').on('input', function() {
		var val = $(this).val().trim();
		if (val) {
			$('#l3-card-profile-url').attr('href', val).text(val);
		} else {
			$('#l3-card-profile-url').attr('href', 'https://www.linkedin.com/').text('https://www.linkedin.com/');
		}
	});

	// ── 6. Envío Asíncrono AJAX ──

	// Prevenir validación nativa HTML5 para usar la validación personalizada en vivo
	$('.l3-resenas-form').attr('novalidate', true);

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
		$form.find('.l3-frontend-inline-error').remove();
		$form.find('.l3-input-error').removeClass('l3-input-error');

		var hasErrors = false;

		function showInlineError($el, msg) {
			$el.addClass('l3-input-error');
			$el.after('<div class="l3-frontend-inline-error" style="color: #ce9e50; font-size: 12px; margin-top: 4px;">' + msg + '</div>');
			hasErrors = true;
		}

		// Validación Común (Rating)
		if (parseInt($ratingInput.val()) === 0 || isNaN(parseInt($ratingInput.val()))) {
			var $starContainer = $form.find('.l3-star-rating-wrapper');
			$starContainer.after('<div class="l3-frontend-inline-error" style="color: #ce9e50; font-size: 12px; margin-top: 4px;">Por favor, selecciona una calificación con estrellas.</div>');
			hasErrors = true;
		}

		// Validar campos específicos según el flujo
		if ($form.attr('id') === 'l3-form-linkedin') {
			var $nombreA = $('#resena_linkedin_nombre');
			var $contenidoA = $('#resena_contenido_a');
			var $empresaA = $('#resena_empresa_a');
			var $cargoA = $('#resena_cargo_a');
			var $urlA = $('#resena_linkedin_url');

			if ($urlA.val().trim() === '') {
				showInlineError($urlA, 'Este campo es obligatorio.');
			} else if (!/^https?:\/\/(www\.)?linkedin\.com\/(in|company)\/.+/i.test($urlA.val().trim())) {
				showInlineError($urlA, 'Debe contener /in/nombre-usuario o /company/nombre-empresa después de linkedin.com/');
			}

			if ($nombreA.val().trim() === '') {
				showInlineError($nombreA, 'Este campo es obligatorio.');
			} else if ($nombreA.val().trim().length < 3) {
				showInlineError($nombreA, 'El nombre debe tener al menos 3 caracteres.');
			}

			if ($contenidoA.val().trim() === '') {
				showInlineError($contenidoA, 'Este campo es obligatorio.');
			} else if ($contenidoA.val().trim().length < 10) {
				showInlineError($contenidoA, 'La reseña debe tener al menos 10 caracteres.');
			}
			$cargoA.removeClass('l3-input-error');
			if ($empresaA.val().trim() !== '' && $cargoA.val().trim() === '') {
				showInlineError($cargoA, 'Es obligatorio especificar el cargo en la empresa');
			}
		} else {
			var $nombreB = $('#resena_nombre_b');
			var $contenidoB = $('#resena_contenido_b');
			var $empresaB = $('#resena_empresa_b');
			var $cargoB = $('#resena_cargo_b');
			var $tipoB = $('#resena_red_social_tipo');
			var $urlB = $('#resena_red_social_url');

			if ($nombreB.val().trim() === '') {
				showInlineError($nombreB, 'Este campo es obligatorio.');
			} else if ($nombreB.val().trim().length < 3) {
				showInlineError($nombreB, 'El nombre debe tener al menos 3 caracteres.');
			}

			if ($contenidoB.val().trim() === '') {
				showInlineError($contenidoB, 'Este campo es obligatorio.');
			} else if ($contenidoB.val().trim().length < 10) {
				showInlineError($contenidoB, 'La reseña debe tener al menos 10 caracteres.');
			}
			$cargoB.removeClass('l3-input-error');
			if ($empresaB.val().trim() !== '' && $cargoB.val().trim() === '') {
				showInlineError($cargoB, 'Es obligatorio especificar el cargo en la empresa');
			}

			var tipo = $tipoB.val();
			var url = $urlB.val().trim();
			
			if (tipo !== '' && url !== '') {
				var regex;
				var expectedPrefix = '';
				if (tipo === 'linkedin') {
					regex = /^https?:\/\/(www\.)?linkedin\.com\/(in|company)\/.+/i;
					expectedPrefix = 'https://linkedin.com/in/ o https://linkedin.com/company/';
				} else if (tipo === 'instagram') {
					regex = /^https?:\/\/(www\.)?instagram\.com\/.+/i;
					expectedPrefix = 'https://instagram.com/';
				} else if (tipo === 'facebook') {
					regex = /^https?:\/\/(www\.)?facebook\.com\/.+/i;
					expectedPrefix = 'https://facebook.com/';
				}

				if (regex && !regex.test(url)) {
					if (tipo === 'linkedin') {
						showInlineError($urlB, 'Debe contener /in/nombre-usuario o /company/nombre-empresa después de linkedin.com/');
					} else {
						showInlineError($urlB, 'Debe incluir tu nombre de usuario después de ' + tipo + '.com/');
					}
				}
			} else if (tipo !== '' && url === '') {
				showInlineError($urlB, 'Debe ingresar la URL si selecciona una red social.');
			} else if (tipo === '' && url !== '') {
				showInlineError($tipoB, 'Seleccione el tipo de red social para la URL ingresada.');
			}
		}

		if (hasErrors) {
			var $firstError = $form.find('.l3-input-error').first();
			if ($firstError.length) {
				$('#l3-review-choice-modal').animate({
					scrollTop: $firstError.offset().top - $('#l3-review-choice-modal').offset().top - 40
				}, 300);
			}
			return;
		}

		// Crear objeto FormData para soportar la subida binaria de archivos
		var formData = new FormData();
		
		// Metadatos comunes
		formData.append('action', 'l3_submit_resena');
		formData.append('nonce', l3_resenas_params.nonce);
		formData.append('via_linkedin', $viaLinkedinInput.val());
		formData.append('rating', $ratingInput.val());

		if ($form.attr('id') === 'l3-form-linkedin') {
			// Datos flujo LinkedIn
			formData.append('nombre', $linkedinNameInput.val());
			formData.append('linkedin_url', $linkedinUrlInput.val());
			formData.append('foto_url', $linkedinAvatarInput.val());
			formData.append('empresa', $('#resena_empresa_a').val());
			formData.append('cargo', $('#resena_cargo_a').val());
			formData.append('contenido', $('#resena_contenido_a').val());
			
			var linkedinAuth = $('#resena_linkedin_auth').is(':checked') ? '1' : '0';
			formData.append('linkedin_auth', linkedinAuth);

			// Adjuntar token de usuario si se recuperó en el redireccionamiento
			var userToken = $('#resena_linkedin_user_token').val();
			if (userToken) {
				formData.append('resena_linkedin_user_token', userToken);
			}
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

					// --- Inicio Secuencia de Éxito Premium ---

					// 1. Cambiar estado del botón a enviado
					$btnSubmit.text('¡Enviado!').css('background-color', '#38a169');
					$btnSubmit.prop('disabled', true);

					// 2. Ocultar botón de cerrar modal durante la animación
					var $closeBtn = $('#l3-close-choice-modal');
					$closeBtn.hide();

					// 3. Aplicar blur al header y al formulario activo
					var $modalHeader = $('.l3-custom-modal-header');
					$modalHeader.css({
						'transition': 'filter 0.5s ease, opacity 0.5s ease',
						'filter': 'blur(6px)',
						'opacity': '0.3'
					});
					$form.css({
						'transition': 'filter 0.5s ease, opacity 0.5s ease',
						'filter': 'blur(6px)',
						'opacity': '0.3',
						'pointer-events': 'none'
					});

					// 4. Inyectar overlay del countdown en el modal box
					var $modalBox = $('.l3-custom-modal-box');
					var $countdownOverlay = $('<div class="antigravity-success-countdown-overlay"></div>').css({
						'position': 'absolute',
						'top': '0',
						'left': '0',
						'width': '100%',
						'height': '100%',
						'display': 'flex',
						'flex-direction': 'column',
						'justify-content': 'center',
						'align-items': 'center',
						'z-index': '10',
						'color': '#ffffff',
						'text-align': 'center',
						'padding': '20px',
						'box-sizing': 'border-box'
					});
					$countdownOverlay.html('<div class="l3-countdown-number" style="font-size: 80px; font-weight: bold; color: #ce9e50; transition: transform 0.2s ease; text-shadow: 0 4px 10px rgba(0,0,0,0.5);">5</div>');
					$modalBox.append($countdownOverlay);

					// 5. Contador regresivo 5 → 1
					var count = 5;
					var $countEl = $countdownOverlay.find('.l3-countdown-number');

					var interval = setInterval(function() {
						count--;
						if (count > 0) {
							$countEl.text(count).css('transform', 'scale(1.2)');
							setTimeout(function() { $countEl.css('transform', 'scale(1)'); }, 150);
						} else {
							clearInterval(interval);

							// 6. Mostrar mensaje de éxito (sin mostrar el cero)
							$countdownOverlay.html(
								'<div class="l3-success-message" style="opacity:0; transform:translateY(10px); transition: all 0.5s ease; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">' +
									'<h3 style="color:#ce9e50; font-size:24px; margin:0 0 15px 0; font-family:\'Playfair Display\', serif; font-weight:bold; line-height:1.3;">¡Reseña enviada!</h3>' +
									'<p style="color:#ffffff; font-size:16px; line-height:1.6; max-width:320px; margin:0 auto;">Tu testimonio será revisado por el equipo de Línea 3 Legal antes de ser publicado.</p>' +
								'</div>'
							);
							setTimeout(function() {
								$countdownOverlay.find('.l3-success-message').css({ 'opacity': '1', 'transform': 'translateY(0)' });
							}, 50);

							// 7. Cerrar y resetear el modal tras 3 segundos
							setTimeout(function() {
								$modalBox.css({ 'transition': 'all 0.5s ease', 'transform': 'scale(0.95)', 'opacity': '0' });

								setTimeout(function() {
									// Eliminar overlay
									$countdownOverlay.remove();

									// Cerrar modal principal
									$('#l3-review-choice-modal').removeClass('is-visible');
									$('body').removeClass('l3-modal-open-lock');

									setTimeout(function() {
										// Restaurar estilos del modal box
										$modalBox.css({ 'transform': '', 'opacity': '', 'transition': '' });

										// Restaurar blur del header y formulario
										$modalHeader.css({ 'filter': '', 'opacity': '', 'transition': '' });
										$form.css({ 'filter': '', 'opacity': '', 'transition': '', 'pointer-events': '' });

										// Restaurar botón X y submit
										$closeBtn.show();
										$btnSubmit.text('Enviar Reseña').css('background-color', '').prop('disabled', false);

										// Resetear formulario completo (campos, rating, avatar, pasos)
										resetModalForms();
									}, 300);
								}, 500);
							}, 3000);
						}
					}, 1000);

					// --- Fin Secuencia de Éxito Premium ---

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

	// Limpiar errores en tiempo real al escribir en el frontend
	$('#l3-resenas-form-container').on('input change', 'input, textarea, select', function() {
		var $el = $(this);
		if ($el.hasClass('l3-input-error')) {
			$el.removeClass('l3-input-error');
			$el.next('.l3-frontend-inline-error').remove();
		}
	});

	function displayAlert($targetForm, message, type) {
		if (type !== 'success') {
			return; // Solo mostramos mensajes de éxito en la parte superior.
		}

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
