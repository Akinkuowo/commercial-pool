<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            mobileMenu.classList.toggle('block');
        });
    }
    
    // Mobile menu dropdown functionality
    const mobileMenuItems = document.querySelectorAll('.mobile-menu-item');
    
    mobileMenuItems.forEach(item => {
        const toggle = item.querySelector('.mobile-menu-toggle');
        const submenu = item.querySelector('.mobile-submenu');
        
        if (toggle && submenu) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Close other open menus
                mobileMenuItems.forEach(otherItem => {
                    if (otherItem !== item) {
                        const otherSubmenu = otherItem.querySelector('.mobile-submenu');
                        const otherToggle = otherItem.querySelector('.mobile-menu-toggle svg');
                        if (otherSubmenu && otherSubmenu.classList.contains('block')) {
                            otherSubmenu.classList.remove('block');
                            otherSubmenu.classList.add('hidden');
                            if (otherToggle) {
                                otherToggle.classList.remove('rotate-180');
                            }
                            otherItem.classList.remove('active');
                        }
                    }
                });
                
                // Toggle current menu
                const isHidden = submenu.classList.contains('hidden');
                const icon = toggle.querySelector('svg');
                
                if (isHidden) {
                    submenu.classList.remove('hidden');
                    submenu.classList.add('block');
                    if (icon) {
                        icon.classList.add('rotate-180');
                    }
                    item.classList.add('active');
                } else {
                    submenu.classList.remove('block');
                    submenu.classList.add('hidden');
                    if (icon) {
                        icon.classList.remove('rotate-180');
                    }
                    item.classList.remove('active');
                }
            });
        }
        
        // Handle sub-submenu toggles
        const submenuItems = item.querySelectorAll('.mobile-submenu-item');
        submenuItems.forEach(subItem => {
            const subToggle = subItem.querySelector('.mobile-submenu-toggle');
            const subSubmenu = subItem.querySelector('.mobile-submenu-2');
            
            if (subToggle && subSubmenu) {
                subToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const isHidden = subSubmenu.classList.contains('hidden');
                    const icon = subToggle.querySelector('svg');
                    
                    if (isHidden) {
                        subSubmenu.classList.remove('hidden');
                        subSubmenu.classList.add('block');
                        if (icon) {
                            icon.classList.add('rotate-180');
                        }
                    } else {
                        subSubmenu.classList.remove('block');
                        subSubmenu.classList.add('hidden');
                        if (icon) {
                            icon.classList.remove('rotate-180');
                        }
                    }
                });
            }
        });
    });
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!mobileMenu.classList.contains('hidden')) {
            const isClickInside = mobileMenu.contains(e.target) || 
                                 (mobileMenuToggle && mobileMenuToggle.contains(e.target));
            
            if (!isClickInside && mobileMenuToggle && mobileMenu) {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('block');
                
                // Also close all submenus
                mobileMenuItems.forEach(item => {
                    const submenu = item.querySelector('.mobile-submenu');
                    const icon = item.querySelector('.mobile-menu-toggle svg');
                    if (submenu && submenu.classList.contains('block')) {
                        submenu.classList.remove('block');
                        submenu.classList.add('hidden');
                        if (icon) {
                            icon.classList.remove('rotate-180');
                        }
                        item.classList.remove('active');
                    }
                    
                    // Close sub-submenus
                    const subSubmenus = item.querySelectorAll('.mobile-submenu-2');
                    subSubmenus.forEach(subSubmenu => {
                        if (subSubmenu.classList.contains('block')) {
                            subSubmenu.classList.remove('block');
                            subSubmenu.classList.add('hidden');
                        }
                    });
                });
            }
        }
    });
});
</script>


<script>
        const track = document.getElementById('categoryTrack');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const cards = document.querySelectorAll('.category-card');
        
        let currentPosition = 0;
        let itemsToShow = 4;
        
        function updateItemsToShow() {
            const width = window.innerWidth;
            if (width < 640) {
                itemsToShow = 1;
            } else if (width < 1024) {
                itemsToShow = 2;
            } else if (width < 1280) {
                itemsToShow = 3;
            } else {
                itemsToShow = 4;
            }
            updateSlider();
        }
        
        function updateSlider() {
            const cardWidth = cards[0].offsetWidth;
            const gap = 16;
            const moveAmount = (cardWidth + gap) * itemsToShow;
            
            track.style.transform = `translateX(-${currentPosition * moveAmount}px)`;
            
            prevBtn.classList.toggle('disabled', currentPosition === 0);
            
            const maxPosition = Math.ceil((cards.length - itemsToShow) / itemsToShow);
            nextBtn.classList.toggle('disabled', currentPosition >= maxPosition);
        }
        
        prevBtn.addEventListener('click', () => {
            if (currentPosition > 0) {
                currentPosition--;
                updateSlider();
            }
        });
        
        nextBtn.addEventListener('click', () => {
            const maxPosition = Math.ceil((cards.length - itemsToShow) / itemsToShow);
            if (currentPosition < maxPosition) {
                currentPosition++;
                updateSlider();
            }
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                prevBtn.click();
            } else if (e.key === 'ArrowRight') {
                nextBtn.click();
            }
        });
        
        window.addEventListener('resize', () => {
            currentPosition = 0;
            updateItemsToShow();
        });
        
        updateItemsToShow();
        
        let touchStartX = 0;
        let touchEndX = 0;
        
        track.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        track.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
        
        function handleSwipe() {
            if (touchEndX < touchStartX - 50) {
                nextBtn.click();
            }
            if (touchEndX > touchStartX + 50) {
                prevBtn.click();
            }
        }
    </script>

<script>
    // Add click handlers to Add to Cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Add your cart logic here
            this.textContent = 'Added!';
            this.style.background = '#022658';
            
            setTimeout(() => {
                this.textContent = 'Add to Cart';
                this.style.background = '#CC4514';
            }, 2000);
        });
    });
</script>

<script>
    // Optional: Add click handlers to brand logos
    document.querySelectorAll('.brand-logo').forEach(logo => {
        logo.addEventListener('click', function() {
            const brandName = this.querySelector('.brand-name').textContent.trim().replace(/\n/g, ' ');
            console.log('Brand clicked:', brandName);
            // Add your brand page navigation logic here
            // window.location.href = '/brands/' + brandName.toLowerCase().replace(/\s+/g, '-');
        });
    });
</script>

<script>
        // Form submission handling
        document.getElementById('newsletterForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitBtn = document.getElementById('subscribeBtn');
            const successMessage = document.getElementById('successMessage');
            const originalBtnText = submitBtn.innerHTML;
            
            // Get form data
            const formData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                consent: document.getElementById('consent').checked
            };
            
            // Simple validation
            if (!formData.consent) {
                alert('Please agree to our privacy policy to continue.');
                return;
            }
            
            // Show loading state
            submitBtn.innerHTML = `
                <span class="flex items-center justify-center space-x-3">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Subscribing...</span>
                </span>
            `;
            submitBtn.disabled = true;
            
            try {
                // Simulate API call with delay
                await new Promise(resolve => setTimeout(resolve, 1500));
                
                // Here you would typically send the data to your backend
                // For example:
                // const response = await fetch('/api/newsletter/subscribe', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify(formData)
                // });
                
                // Simulate successful subscription
                console.log('Subscribing:', formData);
                
                // Show success message
                successMessage.classList.remove('hidden');
                successMessage.classList.add('fade-in');
                
                // Send welcome email (in a real app, this would be handled by your backend)
                simulateWelcomeEmail(formData.email, formData.name);
                
                // Reset form
                form.reset();
                
                // Scroll to success message
                successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Hide success message after 8 seconds
                setTimeout(() => {
                    successMessage.classList.add('hidden');
                }, 8000);
                
            } catch (error) {
                console.error('Subscription error:', error);
                alert('There was an error subscribing. Please try again.');
            } finally {
                // Reset button
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }
        });
        
        // Simulate sending welcome email
        function simulateWelcomeEmail(email, name) {
            console.log(`Sending welcome email to: ${email}`);
            console.log(`Dear ${name}, welcome to Outwell! Check your inbox for your 10% discount code.`);
            
            // In a real implementation, this would be an API call to your email service
            // Example using a hypothetical email service:
            // fetch('/api/send-welcome-email', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify({ email, name })
            // });
        }
        
        // Form field animations on focus
        const inputs = document.querySelectorAll('input[type="text"], input[type="email"]');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('ring-2', 'ring-orange-200', 'rounded-xl');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('ring-2', 'ring-orange-200', 'rounded-xl');
            });
        });
    </script>

<script>
        // Simple hover effects enhancement
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth scrolling to footer links
            const footerLinks = document.querySelectorAll('.footer-link');
            footerLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px)';
                });
                
                link.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });
            
            // Social media icon animations
            const socialIcons = document.querySelectorAll('.social-icon');
            socialIcons.forEach(icon => {
                icon.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.1)';
                });
                
                icon.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Newsletter form submission
            const newsletterForm = document.querySelector('form');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const emailInput = this.querySelector('input[type="email"]');
                    if (emailInput.value) {
                        // alert('Thank you for subscribing to our newsletter!');
                        emailInput.value = '';
                    }
                });
            }
            
            // Current year for copyright
            const yearSpan = document.querySelector('footer p:first-of-type');
            if (yearSpan) {
                yearSpan.innerHTML = yearSpan.innerHTML.replace('2024', new Date().getFullYear());
            }
        });
</script>

<script>
    // Smart Search Implementation
    (function() {
        const searchInput = document.getElementById('headerSearchInput');
        const clearBtn = document.getElementById('clearSearchBtn');
        const suggestionsBox = document.getElementById('searchSuggestions');
        const suggestionsList = document.getElementById('suggestionsList');
        const loadingState = document.getElementById('searchLoading');
        const noResults = document.getElementById('noResults');
        const viewAllResults = document.getElementById('viewAllResults');
        const viewAllLink = document.getElementById('viewAllLink');
        const autoCorrectDiv = document.getElementById('autoCorrectSuggestion');
        const correctedTermBtn = document.getElementById('correctedTerm');
        
        let searchTimeout;
        let currentSearch = '';
        
        // Common spelling corrections dictionary
        const spellingCorrections = {
            'tnet': 'tent',
            'tnets': 'tents',
            'sleping': 'sleeping',
            'sleepng': 'sleeping',
            'awing': 'awning',
            'awnings': 'awnings',
            'fridg': 'fridge',
            'fridges': 'fridges',
            'coker': 'cooker',
            'elecrical': 'electrical',
            'electical': 'electrical',
            'bahtroom': 'bathroom',
            'bathrom': 'bathroom',
            'kichen': 'kitchen',
            'kitchn': 'kitchen',
            'campin': 'camping',
            'campng': 'camping',
            'carvan': 'caravan',
            'caravans': 'caravans',
            'motorhome': 'motorhome',
            'motohhome': 'motorhome',
            'furntiure': 'furniture',
            'furnitue': 'furniture',
            'acessories': 'accessories',
            'accesories': 'accessories'
        };
        
        // Levenshtein distance for fuzzy matching
        function levenshteinDistance(str1, str2) {
            const matrix = [];
            
            for (let i = 0; i <= str2.length; i++) {
                matrix[i] = [i];
            }
            
            for (let j = 0; j <= str1.length; j++) {
                matrix[0][j] = j;
            }
            
            for (let i = 1; i <= str2.length; i++) {
                for (let j = 1; j <= str1.length; j++) {
                    if (str2.charAt(i - 1) === str1.charAt(j - 1)) {
                        matrix[i][j] = matrix[i - 1][j - 1];
                    } else {
                        matrix[i][j] = Math.min(
                            matrix[i - 1][j - 1] + 1,
                            matrix[i][j - 1] + 1,
                            matrix[i - 1][j] + 1
                        );
                    }
                }
            }
            
            return matrix[str2.length][str1.length];
        }
        
        // Auto-correct function
        function autoCorrect(query) {
            const words = query.toLowerCase().split(' ');
            let corrected = false;
            let correctedQuery = words.map(word => {
                // Check exact match in corrections dictionary
                if (spellingCorrections[word]) {
                    corrected = true;
                    return spellingCorrections[word];
                }
                return word;
            }).join(' ');
            
            return { corrected: correctedQuery, wasCorrect: corrected };
        }
        
        // Search products
        async function searchProducts(query) {
            try {
                const response = await fetch(`api/products.php?search=${encodeURIComponent(query)}&limit=10`);
                const data = await response.json();
                
                if (data.success) {
                    return data.products;
                }
                return [];
            } catch (error) {
                console.error('Search error:', error);
                return [];
            }
        }
        
        // Render suggestions
        function renderSuggestions(products, originalQuery, correctedQuery) {
            suggestionsList.innerHTML = '';
            
            if (products.length === 0) {
                noResults.classList.remove('hidden');
                viewAllResults.classList.add('hidden');
                return;
            }
            
            noResults.classList.add('hidden');
            
            // Show auto-correct suggestion if needed
            if (correctedQuery && correctedQuery !== originalQuery) {
                autoCorrectDiv.classList.remove('hidden');
                correctedTermBtn.textContent = correctedQuery;
                correctedTermBtn.onclick = () => {
                    searchInput.value = correctedQuery;
                    performSearch(correctedQuery);
                };
            } else {
                autoCorrectDiv.classList.add('hidden');
            }
            
            // Render product suggestions
            products.forEach(product => {
                const item = document.createElement('a');
                item.href = `product-detail.php?id=${product.id}`;
                item.className = 'flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition cursor-pointer border-b border-gray-100 last:border-0';
                
                const inStock = product.stock === 'In Stock' || product.quantity > 0;
                
                item.innerHTML = `
                    <img src="${product.image}" alt="${product.name}" class="w-12 h-12 object-cover rounded">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">${highlightMatch(product.name, originalQuery)}</p>
                        <p class="text-xs text-gray-500 truncate">${product.brand || 'Generic'}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">£${parseFloat(product.price).toFixed(2)}</p>
                        ${inStock ? '<p class="text-xs text-[#022658]">In Stock</p>' : '<p class="text-xs text-red-600">Out of Stock</p>'}
                    </div>
                `;
                
                suggestionsList.appendChild(item);
            });
            
            // Show "View all results" link
            viewAllResults.classList.remove('hidden');
            viewAllLink.href = `product.php?search=${encodeURIComponent(originalQuery)}`;
            viewAllLink.textContent = `View all results for "${originalQuery}" →`;
        }
        
        // Highlight matching text
        function highlightMatch(text, query) {
            if (!query) return text;
            
            const regex = new RegExp(`(${query})`, 'gi');
            return text.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
        }
        
        // Perform search
        async function performSearch(query) {
            if (!query || query.length < 2) {
                suggestionsBox.classList.add('hidden');
                return;
            }
            
            currentSearch = query;
            
            // Show loading state
            suggestionsBox.classList.remove('hidden');
            loadingState.classList.remove('hidden');
            suggestionsList.innerHTML = '';
            noResults.classList.add('hidden');
            viewAllResults.classList.add('hidden');
            
            // Auto-correct
            const correction = autoCorrect(query);
            const searchQuery = correction.wasCorrect ? correction.corrected : query;
            
            // Search products
            const products = await searchProducts(searchQuery);
            
            // Only update if this is still the current search
            if (currentSearch === query) {
                loadingState.classList.add('hidden');
                renderSuggestions(products, query, correction.wasCorrect ? correction.corrected : null);
            }
        }
        
        // Debounce function
        function debounce(func, wait) {
            return function executedFunction(...args) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => func(...args), wait);
            };
        }
        
        const debouncedSearch = debounce(performSearch, 300);
        
        // Event listeners
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            
            if (query) {
                clearBtn.classList.remove('hidden');
                debouncedSearch(query);
            } else {
                clearBtn.classList.add('hidden');
                suggestionsBox.classList.add('hidden');
            }
        });
        
        searchInput.addEventListener('focus', (e) => {
            const query = e.target.value.trim();
            if (query && query.length >= 2) {
                suggestionsBox.classList.remove('hidden');
            }
        });
        
        // Clear button
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            clearBtn.classList.add('hidden');
            suggestionsBox.classList.add('hidden');
            searchInput.focus();
        });
        
        // Handle Enter key
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = searchInput.value.trim();
                if (query) {
                    window.location.href = `product.php?search=${encodeURIComponent(query)}`;
                }
            }
        });
        
        // Close suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.classList.add('hidden');
            }
        });
        
        // Handle ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                suggestionsBox.classList.add('hidden');
            }
        });
    })();
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile dropdown delay functionality
    const profileDropdown = document.querySelector('.nav-item.relative .group');
    
    if (profileDropdown) {
        let hideTimeout;
        const dropdown = profileDropdown.querySelector('.absolute');
        
        if (dropdown) {
            // Show dropdown on mouse enter
            profileDropdown.addEventListener('mouseenter', function() {
                clearTimeout(hideTimeout);
                dropdown.classList.remove('hidden');
                dropdown.classList.add('group-hover:block');
            });
            
            // Hide dropdown with delay on mouse leave
            profileDropdown.addEventListener('mouseleave', function() {
                hideTimeout = setTimeout(function() {
                    dropdown.classList.add('hidden');
                    dropdown.classList.remove('group-hover:block');
                }, 2000); // 2 second delay
            });
            
            // Also handle mouse enter/leave on the dropdown itself
            dropdown.addEventListener('mouseenter', function() {
                clearTimeout(hideTimeout);
            });
            
            dropdown.addEventListener('mouseleave', function() {
                hideTimeout = setTimeout(function() {
                    dropdown.classList.add('hidden');
                    dropdown.classList.remove('group-hover:block');
                }, 2000); // 2 second delay
            });
        }
    }
});
</script>
