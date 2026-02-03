// Mobile menu toggle functionality
document.addEventListener('DOMContentLoaded', function () {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('mobileMenu');

    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function () {
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

    // Currency selector functionality
    const currencyButton = document.getElementById('currencyButton');
    const currencyDropdown = document.getElementById('currencyDropdown');
    const currentCurrency = document.getElementById('currentCurrency');
    const currentCurrencySymbol = document.getElementById('currentCurrencySymbol');
    const currencyOptions = document.querySelectorAll('.currency-option');
    const chevron = currencyButton ? currencyButton.querySelector('svg') : null;

    // Currency data
    const currencyData = {
        'GBP': { symbol: '£', name: 'Pounds' },
        'USD': { symbol: '$', name: 'Dollars' },
        'EUR': { symbol: '€', name: 'Euros' }
    };

    if (currencyButton && currencyDropdown) {
        // Toggle dropdown
        currencyButton.addEventListener('click', (e) => {
            e.stopPropagation();
            const isActive = currencyDropdown.classList.contains('opacity-100');

            if (isActive) {
                currencyDropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
                currencyDropdown.classList.add('opacity-0', 'invisible', '-translate-y-2');
                if (chevron) chevron.classList.remove('rotate-180');
            } else {
                currencyDropdown.classList.remove('opacity-0', 'invisible', '-translate-y-2');
                currencyDropdown.classList.add('opacity-100', 'visible', 'translate-y-0');
                if (chevron) chevron.classList.add('rotate-180');
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.currency-selector')) {
                currencyDropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
                currencyDropdown.classList.add('opacity-0', 'invisible', '-translate-y-2');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        });

        // Keyboard accessibility
        currencyButton.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                currencyButton.click();
            }
            if (e.key === 'Escape') {
                currencyDropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
                currencyDropdown.classList.add('opacity-0', 'invisible', '-translate-y-2');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        });

        // Handle arrow key navigation in dropdown
        let currentFocusIndex = -1;
        const optionElements = Array.from(currencyOptions);

        currencyDropdown.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                currentFocusIndex = (currentFocusIndex + 1) % optionElements.length;
                optionElements[currentFocusIndex].focus();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                currentFocusIndex = currentFocusIndex <= 0 ? optionElements.length - 1 : currentFocusIndex - 1;
                optionElements[currentFocusIndex].focus();
            } else if (e.key === 'Escape') {
                currencyDropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
                currencyDropdown.classList.add('opacity-0', 'invisible', '-translate-y-2');
                if (chevron) chevron.classList.remove('rotate-180');
                currencyButton.focus();
            }
        });
    }

    // Currency change function
    async function changeCurrency(currency) {
        try {
            // Update session via API
            const response = await fetch('api/currency.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ currency: currency })
            });

            const data = await response.json();

            if (data.success) {
                // Update UI
                if (currentCurrency) currentCurrency.textContent = currency;
                if (currentCurrencySymbol) currentCurrencySymbol.textContent = data.symbol;

                // Close dropdown
                if (currencyDropdown) {
                    currencyDropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
                    currencyDropdown.classList.add('opacity-0', 'invisible', '-translate-y-2');
                    if (chevron) chevron.classList.remove('rotate-180');
                }

                // Set cookie for persistence
                const d = new Date();
                d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000));
                document.cookie = `currency=${currency};expires=${d.toUTCString()};path=/`;

                // Reload page to update all prices
                window.location.reload();
            } else {
                console.error('Failed to update currency:', data.error);
            }
        } catch (error) {
            console.error('Error changing currency:', error);
        }
    }

    // Make changeCurrency available globally
    window.changeCurrency = changeCurrency;

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
        searchInput.addEventListener('input', function () {
            if (this.value.trim() !== '') {
                clearButton.classList.remove('hidden');
                searchResults.classList.remove('hidden');
            } else {
                clearButton.classList.add('hidden');
                searchResults.classList.add('hidden');
            }
        });

        // Clear search input
        clearButton.addEventListener('click', function () {
            searchInput.value = '';
            searchInput.focus();
            this.classList.add('hidden');
            searchResults.classList.add('hidden');
        });

        // Close search results when clicking outside
        document.addEventListener('click', function (event) {
            if (!event.target.closest('.relative') && !event.target.closest('.absolute.mt-2')) {
                searchResults.classList.add('hidden');
            }
        });

        // Show search results on focus
        searchInput.addEventListener('focus', function () {
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
    document.addEventListener('click', function (e) {
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
}); // END OF DOMContentLoaded