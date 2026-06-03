/**
 * Modal Strategic Consultation Script
 * Vanilla JS implementation
 */

document.addEventListener('DOMContentLoaded', () => {
    const ANIMATION_DURATION = 300;

    const setupModal = (triggersSelector, overlaySelector, onOpenCallback = null) => {
        const modalTriggers = document.querySelectorAll(triggersSelector);
        const modalOverlay = document.querySelector(overlaySelector);
        
        if (!modalOverlay) return;

        const closeBtn = modalOverlay.querySelector('.antigravity-modal-close');

        const openModal = (triggerElement) => {
            if (onOpenCallback) {
                onOpenCallback(triggerElement, modalOverlay);
            }
            modalOverlay.classList.add('is-active');
            document.body.style.overflow = 'hidden';
            
            setTimeout(() => {
                modalOverlay.classList.add('is-visible');
            }, 10);
        };

        const closeModal = () => {
            // Prevent closing if a success countdown is running
            if (modalOverlay.querySelector('.antigravity-success-countdown-overlay')) {
                return;
            }
            modalOverlay.classList.remove('is-visible');
            document.body.style.overflow = '';
            
            setTimeout(() => {
                modalOverlay.classList.remove('is-active');
            }, ANIMATION_DURATION);
        };

        // Use event delegation for trigger clicks to make them extremely resilient to DOM changes and caching
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest(triggersSelector);
            if (trigger) {
                e.preventDefault();
                openModal(trigger);
            }
        });

        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                closeModal();
            });
        }

        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modalOverlay.classList.contains('is-active')) {
                closeModal();
            }
        });

        // Form Handler via AJAX
        const form = modalOverlay.querySelector('.antigravity-modal-form');
        const modalContent = modalOverlay.querySelector('.antigravity-modal-content');
        const submitBtn = form?.querySelector('button[type="submit"]');
        const defaultSubmitText = submitBtn ? submitBtn.innerText : 'Enviar';
        
        if (form) {
            // Deshabilitar validación HTML5 nativa
            form.setAttribute('novalidate', true);

            // Limpiar errores en tiempo real
            form.addEventListener('input', (e) => {
                const target = e.target;
                if (target.classList.contains('l3-input-error')) {
                    target.classList.remove('l3-input-error');
                    const nextEl = target.nextElementSibling;
                    if (nextEl && nextEl.classList.contains('l3-frontend-inline-error')) {
                        nextEl.remove();
                    }
                }
            });

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                // --- Inicio de Validación ---
                let hasErrors = false;

                // Limpiar errores previos
                form.querySelectorAll('.l3-frontend-inline-error').forEach(el => el.remove());
                form.querySelectorAll('.l3-input-error').forEach(el => el.classList.remove('l3-input-error'));

                const showInlineError = (element, msg) => {
                    element.classList.add('l3-input-error');
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'l3-frontend-inline-error';
                    errorDiv.style.color = '#ce9e50';
                    errorDiv.style.fontSize = '12px';
                    errorDiv.style.marginTop = '4px';
                    errorDiv.innerText = msg;
                    element.parentNode.insertBefore(errorDiv, element.nextSibling);
                    hasErrors = true;
                };

                if (form.classList.contains('antigravity-team-contact-form')) {
                    // Validar Formulario Contactar Equipo
                    const inputEmail = form.querySelector('input[name="contact_email"]');
                    const inputPhone = form.querySelector('input[name="contact_phone"]');
                    const inputSubject = form.querySelector('input[name="contact_subject"]');
                    const inputName = form.querySelector('input[name="contact_name"]');
                    const inputMessage = form.querySelector('textarea[name="contact_message"]');
                    const inputCompany = form.querySelector('input[name="contact_company"]');
                    const inputRole = form.querySelector('input[name="contact_role"]');

                    if (inputEmail) {
                        const val = inputEmail.value.trim();
                        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (val === '') showInlineError(inputEmail, 'Este campo es obligatorio.');
                        else if (!regex.test(val)) showInlineError(inputEmail, 'Por favor, ingresa un correo electrónico válido.');
                    }
                    if (inputPhone) {
                        const val = inputPhone.value.trim();
                        // Optional field, but if filled must be numeric (can include +, -, spaces, parenthesis)
                        const phoneRegex = /^[\d\s\+\-\(\)]+$/;
                        if (val !== '') {
                            if (!phoneRegex.test(val)) {
                                showInlineError(inputPhone, 'Por favor, ingresa solo números para el teléfono.');
                            } else if (val.replace(/[\D]/g, '').length < 7) {
                                showInlineError(inputPhone, 'Ingresa un número de teléfono válido.');
                            }
                        }
                    }
                    if (inputSubject) {
                        const val = inputSubject.value.trim();
                        if (val === '') showInlineError(inputSubject, 'Este campo es obligatorio.');
                    }
                    if (inputName) {
                        const val = inputName.value.trim();
                        if (val === '') showInlineError(inputName, 'Este campo es obligatorio.');
                        else if (val.length < 3) showInlineError(inputName, 'El nombre debe tener al menos 3 caracteres.');
                    }
                    if (inputMessage) {
                        const val = inputMessage.value.trim();
                        if (val === '') showInlineError(inputMessage, 'Este campo es obligatorio.');
                        else if (val.length < 10) showInlineError(inputMessage, 'El mensaje debe tener al menos 10 caracteres.');
                    }

                    if (inputCompany && inputRole) {
                        const companyVal = inputCompany.value.trim();
                        const roleVal = inputRole.value.trim();
                        if (companyVal !== '' && roleVal === '') {
                            showInlineError(inputRole, 'Es obligatorio especificar el cargo en la empresa.');
                        }
                        if (roleVal !== '' && companyVal === '') {
                            showInlineError(inputCompany, 'Es obligatorio especificar la empresa.');
                        }
                    }
                } else {
                    // Validar Formulario Agendar Consulta
                    const inputName = form.querySelector('input[name="consultation_name"]');
                    if (inputName) {
                        const nameVal = inputName.value.trim();
                        if (nameVal === '') showInlineError(inputName, 'Este campo es obligatorio.');
                        else if (nameVal.length < 3) showInlineError(inputName, 'El nombre debe tener al menos 3 caracteres.');
                    }

                    const inputEmail = form.querySelector('input[name="consultation_email"]');
                    if (inputEmail) {
                        const emailVal = inputEmail.value.trim();
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (emailVal === '') showInlineError(inputEmail, 'Este campo es obligatorio.');
                        else if (!emailRegex.test(emailVal)) showInlineError(inputEmail, 'Por favor, ingresa un correo electrónico válido.');
                    }

                    const inputPhone = form.querySelector('input[name="consultation_phone"]');
                    if (inputPhone) {
                        const phoneVal = inputPhone.value.trim();
                        const phoneRegex = /^[\d\s\+\-\(\)]+$/;
                        if (phoneVal === '') {
                            showInlineError(inputPhone, 'Este campo es obligatorio.');
                        } else if (!phoneRegex.test(phoneVal)) {
                            showInlineError(inputPhone, 'Por favor, ingresa solo números para el teléfono.');
                        } else if (phoneVal.replace(/[\D]/g, '').length < 7) {
                            showInlineError(inputPhone, 'Ingresa un número de teléfono válido.');
                        }
                    }

                    const inputMessage = form.querySelector('textarea[name="consultation_message"]');
                    if (inputMessage) {
                        const msgVal = inputMessage.value.trim();
                        if (msgVal === '') showInlineError(inputMessage, 'Este campo es obligatorio.');
                        else if (msgVal.length < 10) showInlineError(inputMessage, 'El mensaje debe tener al menos 10 caracteres.');
                    }
                }

                // Si hay errores, detener el envío
                if (hasErrors) {
                    return;
                }
                // --- Fin de Validación ---

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerText = 'Enviando...';
                }

                const formData = new FormData(form);
                const actionUrl = form.getAttribute('action').replace('admin-post.php', 'admin-ajax.php');

                try {
                    const response = await fetch(actionUrl, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        if (form.classList.contains('antigravity-team-contact-form')) {
                            // Comportamiento por defecto del formulario de contacto del equipo
                            submitBtn.innerText = '¡Enviado!';
                            submitBtn.style.backgroundColor = '#38a169'; 
                            
                            modalContent.style.transition = 'all 0.5s ease';
                            modalContent.style.transform = 'scale(0.95)';
                            modalContent.style.opacity = '0';
                            
                            setTimeout(() => {
                                closeModal();
                                setTimeout(() => {
                                    form.reset();
                                    submitBtn.disabled = false;
                                    submitBtn.innerText = defaultSubmitText;
                                    submitBtn.style.backgroundColor = '';
                                    modalContent.style.transform = '';
                                    modalContent.style.opacity = '';
                                    
                                    // Reset custom file text
                                    const fileTextReset = modalOverlay.querySelector('.antigravity-file-text');
                                    if (fileTextReset) fileTextReset.textContent = 'Ningún archivo seleccionado.';
                                }, ANIMATION_DURATION);
                            }, 800);
                        } else {
                            // Formulario general "Agendar Consulta": Efecto de Blur y Conteo Regresivo
                            submitBtn.innerText = '¡Enviado!';
                            submitBtn.style.backgroundColor = '#38a169';

                            // Ocultar temporalmente el botón de cerrar del modal
                            const modalCloseBtn = modalOverlay.querySelector('.antigravity-modal-close');
                            if (modalCloseBtn) {
                                modalCloseBtn.style.display = 'none';
                            }

                            // Aplicar el efecto de blur al header del modal
                            const modalHeader = modalOverlay.querySelector('.antigravity-modal-header');
                            if (modalHeader) {
                                modalHeader.style.transition = 'filter 0.5s ease, opacity 0.5s ease';
                                modalHeader.style.filter = 'blur(6px)';
                                modalHeader.style.opacity = '0.3';
                            }

                            // Aplicar el efecto de blur y desactivar eventos en el formulario
                            form.style.transition = 'filter 0.5s ease, opacity 0.5s ease';
                            form.style.filter = 'blur(6px)';
                            form.style.opacity = '0.3';
                            form.style.pointerEvents = 'none';

                            // Crear y añadir la superposición del contador
                            const countdownOverlay = document.createElement('div');
                            countdownOverlay.className = 'antigravity-success-countdown-overlay';
                            
                            // Estilos inline de la superposición para no depender de CSS
                            countdownOverlay.style.position = 'absolute';
                            countdownOverlay.style.top = '0';
                            countdownOverlay.style.left = '0';
                            countdownOverlay.style.width = '100%';
                            countdownOverlay.style.height = '100%';
                            countdownOverlay.style.display = 'flex';
                            countdownOverlay.style.flexDirection = 'column';
                            countdownOverlay.style.justifyContent = 'center';
                            countdownOverlay.style.alignItems = 'center';
                            countdownOverlay.style.zIndex = '10';
                            countdownOverlay.style.color = '#ffffff';
                            countdownOverlay.style.textAlign = 'center';
                            countdownOverlay.style.padding = '20px';
                            countdownOverlay.style.boxSizing = 'border-box';

                            countdownOverlay.innerHTML = `
                                <div class="countdown-number" style="font-size: 80px; font-weight: bold; color: #ce9e50; transition: transform 0.2s ease; text-shadow: 0 4px 10px rgba(0,0,0,0.5);">5</div>
                            `;
                            modalContent.appendChild(countdownOverlay);

                            let count = 5;
                            const countdownEl = countdownOverlay.querySelector('.countdown-number');

                            const interval = setInterval(() => {
                                count--;
                                if (count > 0) {
                                    countdownEl.textContent = count;
                                    countdownEl.style.transform = 'scale(1.2)';
                                    setTimeout(() => {
                                        countdownEl.style.transform = 'scale(1)';
                                    }, 150);
                                } else {
                                    clearInterval(interval);

                                    // Mostrar mensaje de éxito final al llegar a cero (sin mostrar el cero)
                                    countdownOverlay.innerHTML = `
                                        <div class="countdown-success-message" style="opacity: 0; transform: translateY(10px); transition: all 0.5s ease; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                                            <h3 style="color: #ce9e50; font-size: 24px; margin: 0 0 15px 0; font-family: 'Playfair Display', serif; font-weight: bold; line-height: 1.3;">¡Has sido Agendado!</h3>
                                            <p style="color: #ffffff; font-size: 16px; line-height: 1.6; max-width: 320px; margin: 0 auto;">Debes esperar a ser contactado por algún miembro del equipo de Línea 3 Legal</p>
                                        </div>
                                    `;

                                    // Fade in del mensaje
                                    setTimeout(() => {
                                        const msgEl = countdownOverlay.querySelector('.countdown-success-message');
                                        if (msgEl) {
                                            msgEl.style.opacity = '1';
                                            msgEl.style.transform = 'translateY(0)';
                                        }
                                    }, 50);

                                    // Cerrar modal automáticamente después de 3 segundos
                                    setTimeout(() => {
                                        modalContent.style.transition = 'all 0.5s ease';
                                        modalContent.style.transform = 'scale(0.95)';
                                        modalContent.style.opacity = '0';

                                        setTimeout(() => {
                                            // Remover el overlay para permitir el cierre en closeModal()
                                            countdownOverlay.remove();
                                            closeModal();

                                            setTimeout(() => {
                                                // Reestablecer todo a su estado original
                                                form.reset();
                                                form.style.filter = '';
                                                form.style.opacity = '';
                                                form.style.pointerEvents = '';
                                                if (modalHeader) {
                                                    modalHeader.style.filter = '';
                                                    modalHeader.style.opacity = '';
                                                }
                                                if (modalCloseBtn) {
                                                    modalCloseBtn.style.display = '';
                                                }

                                                submitBtn.disabled = false;
                                                submitBtn.innerText = defaultSubmitText;
                                                submitBtn.style.backgroundColor = '';
                                                modalContent.style.transform = '';
                                                modalContent.style.opacity = '';
                                            }, ANIMATION_DURATION);
                                        }, 500);
                                    }, 3000);
                                }
                            }, 1000);
                        }
                        
                    } else {
                        alert(data.data || 'Ocurrió un error. Verifica tu información.');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerText = defaultSubmitText;
                        }
                    }
                } catch (error) {
                    console.error('Submit Error:', error);
                    alert('No se pudo establecer la conexión. Intenta nuevamente.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerText = defaultSubmitText;
                    }
                }
            });

            // Handlers para input de archivos personalizados
            const fileInput = modalOverlay.querySelector('.antigravity-file-input-hidden');
            const fileText = modalOverlay.querySelector('.antigravity-file-text');
            if (fileInput && fileText) {
                fileInput.addEventListener('change', (e) => {
                    if (e.target.files && e.target.files.length > 0) {
                        if (e.target.files.length === 1) {
                            fileText.textContent = e.target.files[0].name;
                        } else {
                            fileText.textContent = e.target.files.length + ' archivos seleccionados';
                        }
                    } else {
                        fileText.textContent = 'Ningún archivo seleccionado.';
                    }
                });
            }
        }
    };

    // 1. Inicializar Modal de Consulta Estratégica (Clásico)
    // Este modal NO tiene clase específica más que overlay general en el HTML viejo,
    // pero para no romper compatibilidad tomamos el primer overlay que NO sea del team-contact
    setupModal('.antigravity-modal-trigger, .cta-legal-btn', '.antigravity-modal-overlay:not(.antigravity-team-contact-modal-overlay)');

    // 2. Inicializar Nuevo Modal de Contacto de Equipo
    setupModal('.linea3-team-contact-btn', '.antigravity-team-contact-modal-overlay', (trigger, overlay) => {
        const authorId = trigger.getAttribute('data-author-id');
        const authorName = trigger.getAttribute('data-author-name');
        const authorImage = trigger.getAttribute('data-author-image');
        
        // Inyectar datos en el formulario
        const inputId = overlay.querySelector('#target_author_id');
        if (inputId) inputId.value = authorId;

        // Modificar título del modal
        const titleEl = overlay.querySelector('#team-contact-modal-title');
        if (titleEl && authorName) {
            titleEl.innerText = 'Contactar a ' + authorName;
        }

        // Modificar imagen del modal
        const imageEl = overlay.querySelector('#team-contact-modal-image');
        if (imageEl && authorImage) {
            imageEl.src = authorImage;
            imageEl.alt = authorName;
        }
    });

    // 3. Funcionalidad Premium de Compartir Perfil
    const showShareToast = (message) => {
        let toast = document.querySelector('.l3-share-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.className = 'l3-share-toast';
            toast.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span class="l3-share-toast-text"></span>
            `;
            document.body.appendChild(toast);
        }
        toast.querySelector('.l3-share-toast-text').innerText = message;
        toast.classList.add('is-visible');
        
        setTimeout(() => {
            toast.classList.remove('is-visible');
        }, 3000);
    };

    document.addEventListener('click', async (e) => {
        const shareBtn = e.target.closest('.l3-share-profile-btn');
        if (!shareBtn) return;
        
        e.preventDefault();
        
        const shareUrl = shareBtn.getAttribute('data-share-url') || window.location.href;
        const shareTitle = shareBtn.getAttribute('data-share-title') || document.title;
        
        if (navigator.share) {
            try {
                await navigator.share({
                    title: shareTitle,
                    url: shareUrl
                });
            } catch (err) {
                if (err.name !== 'AbortError') {
                    copyToClipboardFallback(shareUrl);
                }
            }
        } else {
            copyToClipboardFallback(shareUrl);
        }
    });

    const copyToClipboardFallback = (text) => {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text)
                .then(() => showShareToast('Enlace copiado al portapapeles'))
                .catch(() => legacyCopyFallback(text));
        } else {
            legacyCopyFallback(text);
        }
    };

    const legacyCopyFallback = (text) => {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showShareToast('Enlace copiado al portapapeles');
        } catch (err) {
            console.error('Fallback Copy Error:', err);
        }
        document.body.removeChild(textarea);
    };

});
