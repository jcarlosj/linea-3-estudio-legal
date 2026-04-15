document.addEventListener('DOMContentLoaded', () => {
    const searchBtn = document.querySelector('.search-btn');
    const floatingSearchContainer = document.querySelector('.floating-search-container');

    if (searchBtn && floatingSearchContainer) {
        // Toggle the open class
        searchBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const isOpen = floatingSearchContainer.classList.toggle('is-open');
            floatingSearchContainer.setAttribute('aria-expanded', isOpen);
            
            // Allow time for display to apply, then focus the input
            if (isOpen) {
                setTimeout(() => {
                    const input = floatingSearchContainer.querySelector('.wp-block-search__input');
                    if (input) {
                        input.focus();
                    }
                }, 50);
            }
        });

        // Close on clicking outside or ESC key
        document.addEventListener('click', (e) => {
            if (floatingSearchContainer.classList.contains('is-open') && 
                !floatingSearchContainer.contains(e.target) && 
                !searchBtn.contains(e.target)) {
                floatingSearchContainer.classList.remove('is-open');
                floatingSearchContainer.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && floatingSearchContainer.classList.contains('is-open')) {
                floatingSearchContainer.classList.remove('is-open');
                floatingSearchContainer.setAttribute('aria-expanded', 'false');
                searchBtn.focus();
            }
        });
    }
});
