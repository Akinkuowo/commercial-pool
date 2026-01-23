// Mobile menu toggle functionality
// document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('open');
            
            // Change icon based on menu state
            const icon = mobileMenuToggle.querySelector('i');
            if (mobileMenu.classList.contains('open')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
    
    // Language selector functionality
    const languageButton = document.getElementById('languageButton');
    const languageDropdown = document.getElementById('languageDropdown');
    const currentLanguage = document.getElementById('currentLanguage');
    const currentFlag = document.getElementById('currentFlag');
    const languageOptions = document.querySelectorAll('.language-option');
    const chevron = languageButton ? languageButton.querySelector('svg') : null;
    
    if (languageButton && languageDropdown) {
        // Toggle dropdown
        languageButton.addEventListener('click', (e) => {
            e.stopPropagation();
            const isActive = languageDropdown.classList.contains('opacity-100');
            
            if (isActive) {
                languageDropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
                languageDropdown.classList.add('opacity-0', 'invisible', '-translate-y-2');
                if (chevron) chevron.classList.remove('rotate-180');
            } else {
                languageDropdown.classList.remove('opacity-0', 'invisible', '-translate-y-2');
                languageDropdown.classList.add('opacity-100', 'visible', 'translate-y-0');
                if (chevron) chevron.classList.add('rotate-180');
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.language-selector')) {
                languageDropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
                languageDropdown.classList.add('opacity-0', 'invisible', '-translate-y-2');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        });
        
        // Handle language option clicks
        languageOptions.forEach(option => {
            option.addEventListener('click', (e) => {
                e.preventDefault();
                
                const lang = option.querySelector('span:not(.text-xs)').textContent;
                const flagSvg = option.querySelector('.flag-icon').innerHTML;
                
                // Update current language display
                if (currentLanguage) currentLanguage.textContent = lang;
                if (currentFlag) currentFlag.innerHTML = flagSvg;
                
                // Close dropdown
                languageDropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
                languageDropdown.classList.add('opacity-0', 'invisible', '-translate-y-2');
                if (chevron) chevron.classList.remove('rotate-180');
                
                // In production, you would navigate to the href
                console.log('Would navigate to:', option.href);
                
                // Update cookie for language preference
                updateCountryCookie(option.href.split('/')[1] || 'en-gb');
            });
        });
        
        // Keyboard accessibility
        languageButton.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                languageButton.click();
            }
            if (e.key === 'Escape') {
                languageDropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
                languageDropdown.classList.add('opacity-0', 'invisible', '-translate-y-2');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        });
        
        // Handle arrow key navigation in dropdown
        let currentFocusIndex = -1;
        const optionElements = Array.from(languageOptions);
        
        languageDropdown.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                currentFocusIndex = (currentFocusIndex + 1) % optionElements.length;
                optionElements[currentFocusIndex].focus();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                currentFocusIndex = currentFocusIndex <= 0 ? optionElements.length - 1 : currentFocusIndex - 1;
                optionElements[currentFocusIndex].focus();
            } else if (e.key === 'Escape') {
                languageDropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
                languageDropdown.classList.add('opacity-0', 'invisible', '-translate-y-2');
                if (chevron) chevron.classList.remove('rotate-180');
                languageButton.focus();
            }
        });
    }
    
    // Cookie functionality for language preference
    function updateCountryCookie(value) {
        const d = new Date();
        d.setTime(d.getTime() + (365*24*60*60*1000));
        let expires = "expires="+ d.toUTCString();
        document.cookie = "CountryRedirect="+value+";" + expires + ";path=/";
    }
    
    // Pause animation on hover for better readability
    const announcementTrack = document.querySelector('.announcement-track');
    const announcementContainer = document.querySelector('.announcement-container');
    
    if (announcementTrack && announcementContainer) {
        announcementContainer.addEventListener('mouseenter', () => {
            announcementTrack.classList.add('paused');
        });
        
        announcementContainer.addEventListener('mouseleave', () => {
            announcementTrack.classList.remove('paused');
        });
        
        // Add keyboard control for announcement slider
        announcementContainer.addEventListener('keydown', (e) => {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                announcementTrack.classList.toggle('paused');
            }
        });
    }
    
    // Add interactivity for search field
    const searchInput = document.querySelector('input[type="text"]');
    const clearButton = document.querySelector('button[aria-label="Clear search"]');
    const searchResults = document.querySelector('.absolute.mt-2');
    
    // Show clear button when typing
    if (searchInput && clearButton && searchResults) {
        searchInput.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                clearButton.classList.remove('hidden');
                searchResults.classList.remove('hidden');
            } else {
                clearButton.classList.add('hidden');
                searchResults.classList.add('hidden');
            }
        });
        
        // Clear search input
        clearButton.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.focus();
            this.classList.add('hidden');
            searchResults.classList.add('hidden');
        });
        
        // Close search results when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.relative') && !event.target.closest('.absolute.mt-2')) {
                searchResults.classList.add('hidden');
            }
        });
        
        // Show search results on focus
        searchInput.addEventListener('focus', function() {
            if (this.value.trim() !== '') {
                searchResults.classList.remove('hidden');
            }
        });
    }
    
    // Update cart quantity (example)
    function updateCartQuantity(quantity) {
        const cartBadge = document.querySelector('.mini-cart-quantity');
        if (cartBadge) {
            cartBadge.textContent = quantity;
            cartBadge.style.display = quantity > 0 ? 'block' : 'none';
        }
    }
    
    // Update favorites quantity (example)
    function updateFavoritesQuantity(quantity) {
        const favBadge = document.querySelector('.favorite-quantity');
        if (favBadge) {
            favBadge.textContent = quantity;
            favBadge.style.display = quantity > 0 ? 'block' : 'none';
        }
    }
    
    // Initialize with example data
    updateCartQuantity(3); // cart 
    updateFavoritesQuantity(2); // favorite
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (mobileMenu && mobileMenu.classList.contains('open')) {
            if (!mobileMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                mobileMenu.classList.remove('open');
                const icon = mobileMenuToggle.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            }
        }
    });
    
    // Add CSS rotation class for chevron
    if (!document.querySelector('#rotation-style')) {
        const style = document.createElement('style');
        style.id = 'rotation-style';
        style.textContent = `
            .rotate-180 {
                transform: rotate(180deg);
                transition: transform 0.3s ease;
            }
        `;
        document.head.appendChild(style);
    }
// }); // END OF DOMContentLoaded