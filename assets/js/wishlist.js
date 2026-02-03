/**
 * Wishlist Utility - Handles wishlist interactions across the site
 */
const Wishlist = {
    ids: [],

    async init() {
        await this.fetchIds();
        this.updateUI();
        this.setupListeners();
    },

    async fetchIds() {
        try {
            const response = await fetch('api/wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_ids' })
            });
            const data = await response.json();
            if (data.success) {
                this.ids = data.wishlist_ids;
                this.updateCounter(data.wishlist_count);
            }
        } catch (error) {
            console.error('Error fetching wishlist:', error);
        }
    },

    async toggle(productId, buttonElem) {
        if (!productId) return;
        
        const isAdding = !this.ids.includes(parseInt(productId));
        const action = isAdding ? 'add' : 'remove';
        
        try {
            // Optimistic UI update
            if (isAdding) {
                this.ids.push(parseInt(productId));
            } else {
                this.ids = this.ids.filter(id => id !== parseInt(productId));
            }
            this.updateHeartIcon(productId, isAdding);

            const response = await fetch('api/wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, product_id: productId })
            });
            
            const data = await response.json();
            if (data.success) {
                this.ids = data.wishlist_ids || this.ids;
                this.updateCounter(data.wishlist_count);
            } else {
                // Revert on failure
                await this.fetchIds();
                this.updateUI();
            }
        } catch (error) {
            console.error('Error toggling wishlist:', error);
            await this.fetchIds();
            this.updateUI();
        }
    },

    updateUI() {
        // Find all wishlist buttons and update their state
        document.querySelectorAll('[data-wishlist-id]').forEach(btn => {
            const id = parseInt(btn.getAttribute('data-wishlist-id'));
            this.updateHeartIcon(id, this.ids.includes(id));
        });
    },

    updateHeartIcon(productId, isActive) {
        const buttons = document.querySelectorAll(`[data-wishlist-id="${productId}"]`);
        buttons.forEach(btn => {
            const icon = btn.querySelector('i');
            if (icon) {
                if (isActive) {
                    icon.classList.remove('far'); // regular
                    icon.classList.add('fas', 'text-red-500'); // solid
                } else {
                    icon.classList.remove('fas', 'text-red-500');
                    icon.classList.add('far');
                }
            }
        });
    },

    updateCounter(count) {
        const counters = document.querySelectorAll('.wishlist-count-badge');
        counters.forEach(counter => {
            counter.textContent = count;
            if (count > 0) {
                counter.classList.remove('hidden');
                // If it's the header span that might be hidden
                counter.parentElement.classList.remove('hidden');
            } else {
                // Optional: hide if 0? Commerial Pool Equipment header seems to hide it
                // counter.classList.add('hidden');
            }
        });
    },

    setupListeners() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-wishlist-btn]');
            if (btn) {
                e.preventDefault();
                const productId = btn.getAttribute('data-wishlist-id');
                this.toggle(productId, btn);
            }
        });
    }
};

// Auto-init
document.addEventListener('DOMContentLoaded', () => Wishlist.init());
