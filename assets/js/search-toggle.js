document.addEventListener('DOMContentLoaded', () => {
    // Listen globally for clicks on the search button (using event delegation)
    document.addEventListener('click', (e) => {
        const searchBtn = e.target.closest('.search-btn');
        if (searchBtn) {
            e.preventDefault();
            const floatingSearchContainer = document.querySelector('.floating-search-container');
            if (floatingSearchContainer) {
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
            }
        } else {
            // Close on clicking outside the container and the search button
            const floatingSearchContainer = document.querySelector('.floating-search-container');
            if (floatingSearchContainer && floatingSearchContainer.classList.contains('is-open')) {
                if (!floatingSearchContainer.contains(e.target)) {
                    floatingSearchContainer.classList.remove('is-open');
                    floatingSearchContainer.setAttribute('aria-expanded', 'false');
                }
            }
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const floatingSearchContainer = document.querySelector('.floating-search-container');
            if (floatingSearchContainer && floatingSearchContainer.classList.contains('is-open')) {
                floatingSearchContainer.classList.remove('is-open');
                floatingSearchContainer.setAttribute('aria-expanded', 'false');
                
                const searchBtn = document.querySelector('.search-btn');
                if (searchBtn) {
                    searchBtn.focus();
                }
            }
        }
    });
});
