/**
 * Modal Strategic Consultation Script
 * Vanilla JS implementation
 */

document.addEventListener('DOMContentLoaded', () => {
    const modalTriggers = document.querySelectorAll('.antigravity-modal-trigger');
    const modalOverlay = document.querySelector('.antigravity-modal-overlay');
    
    if (!modalOverlay || modalTriggers.length === 0) return;

    const closeBtn = modalOverlay.querySelector('.antigravity-modal-close');
    const ANIMATION_DURATION = 300;

    const openModal = () => {
        modalOverlay.classList.add('is-active');
        document.body.style.overflow = 'hidden';
        
        // Timeout to allow display:block to apply before animation classes
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

    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            openModal();
        });
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

    // Accesibility: Close on Esc key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalOverlay.classList.contains('is-active')) {
            closeModal();
        }
    });

    // Form Handler via AJAX
    const form = modalOverlay.querySelector('.antigravity-modal-form');
    const modalContent = modalOverlay.querySelector('.antigravity-modal-content');
    const submitBtn = form?.querySelector('button[type="submit"]');
    
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'Enviando...';
            }

            const formData = new FormData(form);
            
            // Re-route the action parameter specifically for ajax
            // We use the same /wp-admin/admin-post.php url? Actually wp uses admin-ajax.php
            // Luckily FSE uses standard relative routes so we can parse from WP if needed, 
            // but we can just replace 'admin-post' with 'admin-ajax' in action url
            const actionUrl = form.getAttribute('action').replace('admin-post.php', 'admin-ajax.php');

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Success UI Animation
                    submitBtn.innerText = '¡Enviado!';
                    submitBtn.style.backgroundColor = '#38a169'; // Green success tone
                    
                    // Add success animation class to content
                    modalContent.style.transition = 'all 0.5s ease';
                    modalContent.style.transform = 'scale(0.95)';
                    modalContent.style.opacity = '0';
                    
                    // Wait for local animation then close and reset
                    setTimeout(() => {
                        closeModal();
                        setTimeout(() => {
                            form.reset();
                            submitBtn.disabled = false;
                            submitBtn.innerText = 'Agendar';
                            submitBtn.style.backgroundColor = '';
                            modalContent.style.transform = '';
                            modalContent.style.opacity = '';
                        }, ANIMATION_DURATION);
                    }, 800);
                    
                } else {
                    // It failed - Do NOT close window
                    alert(data.data || 'Ocurrió un error. Verifica tu información.');
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Enviar Solicitud';
                }
            } catch (error) {
                console.error('Submit Error:', error);
                alert('No se pudo establecer la conexión. Intenta nuevamente.');
                submitBtn.disabled = false;
                submitBtn.innerText = 'Enviar Solicitud';
            }
        });
    }
});
