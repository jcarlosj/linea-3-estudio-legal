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
            modalOverlay.classList.remove('is-visible');
            document.body.style.overflow = '';
            
            setTimeout(() => {
                modalOverlay.classList.remove('is-active');
            }, ANIMATION_DURATION);
        };

        if (modalTriggers.length > 0) {
            modalTriggers.forEach(trigger => {
                trigger.addEventListener('click', (e) => {
                    e.preventDefault();
                    openModal(trigger);
                });
            });
        }

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
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
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
                            }, ANIMATION_DURATION);
                        }, 800);
                        
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
        }
    };

    // 1. Inicializar Modal de Consulta Estratégica (Clásico)
    // Este modal NO tiene clase específica más que overlay general en el HTML viejo,
    // pero para no romper compatibilidad tomamos el primer overlay que NO sea del team-contact
    setupModal('.antigravity-modal-trigger', '.antigravity-modal-overlay:not(.antigravity-team-contact-modal-overlay)');

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

});
