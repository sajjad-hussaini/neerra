(() => {
    const config = window.storeConfig;
    if (!config) return;

    const $ = (id) => document.getElementById(id);
    const money = (value) => 'Rs. ' + new Intl.NumberFormat('en-PK', { maximumFractionDigits: 2 }).format(value);
    const escape = (value) => String(value).replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char]);
    const icon = (name) => `<svg class="icon" aria-hidden="true"><use href="#i-${name}"/></svg>`;
    const products = new Map(config.products.map((product, index) => [String(product.id), { ...product, price: Number(product.price), max: Number(product.max), index }]));
    const cards = new Map([...document.querySelectorAll('[data-product-id]')].map((card) => [card.dataset.productId, card]));
    const cart = new Map();
    const storageKey = 'neerra.store.basket.v2';
    let category = '', notificationTimer, submitting = false, activeOverlay = null, returnFocus = null, previousOverflow = '';

    [$('cart'), $('checkout')].forEach((overlay) => {
        overlay.hidden = true;
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-hidden', 'true');
    });

    function openOverlay(overlay) {
        clearTimeout(notificationTimer);
        $('storeNotification').classList.remove('visible');
        if (!activeOverlay) {
            returnFocus = document.activeElement;
            previousOverflow = document.body.style.overflow;
        } else {
            activeOverlay.hidden = true;
            activeOverlay.classList.remove('is-open');
            activeOverlay.setAttribute('aria-hidden', 'true');
        }
        activeOverlay = overlay;
        overlay.classList.add('is-open');
        overlay.hidden = false;
        overlay.setAttribute('aria-hidden', 'false');
        $('storeBackdrop').hidden = false;
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => (overlay.id === 'checkout' ? $('customerName') : overlay.querySelector('button')).focus());
    }

    function closeOverlay() {
        if (!activeOverlay || submitting) return;
        activeOverlay.hidden = true;
        activeOverlay.classList.remove('is-open');
        activeOverlay.setAttribute('aria-hidden', 'true');
        activeOverlay = null;
        $('storeBackdrop').hidden = true;
        document.body.style.overflow = previousOverflow;
        if (returnFocus?.isConnected) returnFocus.focus();
    }

    $('storeBackdrop').addEventListener('click', closeOverlay);
    $('checkout').addEventListener('click', (event) => { if (event.target === $('checkout')) closeOverlay(); });
    document.addEventListener('keydown', (event) => {
        if (!activeOverlay) return;
        if (event.key === 'Escape') { event.preventDefault(); closeOverlay(); }
        if (event.key !== 'Tab') return;
        const focusable = [...activeOverlay.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex="0"]')].filter((element) => element.getClientRects().length > 0);
        const first = focusable[0], last = focusable[focusable.length - 1];
        if (event.shiftKey && (document.activeElement === first || !activeOverlay.contains(document.activeElement))) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    });

    try {
        const saved = JSON.parse(localStorage.getItem(storageKey) || '[]');
        if (Array.isArray(saved)) saved.forEach((item) => {
            const product = products.get(String(item.id));
            const quantity = Math.min(product?.max || 0, Math.max(0, Math.floor(Number(item.quantity) || 0)));
            if (product && quantity > 0) cart.set(String(product.id), quantity);
        });
    } catch { /* Shopping remains available when browser storage is disabled. */ }

    const entries = () => [...cart].map(([id, quantity]) => ({ ...products.get(id), quantity }));
    const total = () => entries().reduce((sum, item) => sum + item.quantity * item.price, 0);
    const count = () => [...cart.values()].reduce((sum, quantity) => sum + quantity, 0);

    function notify(message) {
        clearTimeout(notificationTimer);
        $('storeNotification').textContent = message;
        $('storeNotification').classList.add('visible');
        notificationTimer = setTimeout(() => $('storeNotification').classList.remove('visible'), 2600);
    }

    function stepper(product, quantity) {
        return `<div class="quantity-stepper" role="group" aria-label="Quantity for ${escape(product.name)}"><button type="button" data-cart-action="decrease" data-product="${product.id}" aria-label="Remove one ${escape(product.name)}">${icon('minus')}</button><span aria-live="polite">${quantity}</span><button type="button" data-cart-action="increase" data-product="${product.id}" aria-label="Add one ${escape(product.name)}" ${quantity >= product.max ? 'disabled' : ''}>${icon('plus')}</button></div>`;
    }

    function render() {
        const items = entries(), quantity = count(), subtotal = total();
        $('cartCount').textContent = quantity;
        $('basketItemCount').textContent = quantity;
        $('headerCartTotal').textContent = money(subtotal);
        $('cartTotal').textContent = money(subtotal);
        $('checkoutTotal').textContent = money(subtotal);
        $('mobileCartTotal').textContent = money(subtotal);
        $('mobileCartCount').textContent = `${quantity} item${quantity === 1 ? '' : 's'}`;
        $('mobileBasket').hidden = !quantity;
        document.body.classList.toggle('has-basket', quantity > 0);
        $('checkoutButton').disabled = !quantity;

        $('cartItems').innerHTML = items.length ? items.map((item) => `
            <div class="cart-item">
                <img src="${escape(item.image)}" alt="${escape(item.category)} category image" width="67" height="76">
                <div><h3>${escape(item.name)}</h3><small>${escape(item.size)} · ${money(item.price)} each</small>${stepper(item, item.quantity)}</div>
                <div class="cart-item-price"><strong>${money(item.price * item.quantity)}</strong><button type="button" class="remove-item" data-cart-action="remove" data-product="${item.id}" aria-label="Remove ${escape(item.name)} from basket">Remove</button></div>
            </div>`).join('') : `<div class="cart-empty">${icon('bag')}<h3>A little room for goodness.</h3><p>Your basket is empty. Find your everyday favourites on our shelf.</p><button type="button" class="store-button" data-close-overlay>Explore the essentials ${icon('arrow')}</button></div>`;

        $('checkoutItems').innerHTML = items.map((item) => `<div class="checkout-review-item"><span>${escape(item.name)}<small>${escape(item.size)} × ${item.quantity}</small></span><strong>${money(item.price * item.quantity)}</strong></div>`).join('');

        document.querySelectorAll('[data-product-controls]').forEach((control) => {
            const product = products.get(control.dataset.productControls);
            const quantity = cart.get(String(product.id)) || 0;
            control.innerHTML = quantity ? stepper(product, quantity) : `<button class="add-product" type="button" data-cart-action="add" data-product="${product.id}" aria-label="Add ${escape(product.name)} to basket" ${product.max < 1 ? 'disabled' : ''}>${product.max < 1 ? 'Unavailable' : 'Add <span aria-hidden="true">+</span>'}</button>`;
        });
        document.querySelectorAll('[data-availability]').forEach((badge) => {
            badge.textContent = products.get(badge.dataset.availability).max > 0 ? 'In stock' : 'Unavailable';
        });
        try { localStorage.setItem(storageKey, JSON.stringify([...cart].map(([id, quantity]) => ({ id, quantity })))); } catch { /* Optional persistence. */ }
    }

    function changeQuantity(id, action) {
        const product = products.get(String(id));
        if (!product) return;
        const previous = cart.get(String(id)) || 0;
        let next = action === 'remove' ? 0 : previous + (action === 'decrease' ? -1 : 1);
        if (next > product.max) {
            notify(`You can add up to ${product.max} of this item.`);
            return;
        }
        if (next > 0) cart.set(String(id), next);
        else cart.delete(String(id));
        render();
        if (action === 'add') notify(`${product.name} added to your basket.`);
        if (action === 'remove') notify(`${product.name} removed from your basket.`);
    }

    function filterProducts() {
        const query = $('productSearch').value.trim().toLocaleLowerCase();
        const sort = $('productSort').value;
        const ordered = [...products.values()].sort((a, b) => {
            if (sort === 'price-low') return a.price - b.price || a.index - b.index;
            if (sort === 'price-high') return b.price - a.price || a.index - b.index;
            if (sort === 'name') return a.name.localeCompare(b.name);
            return a.index - b.index;
        });
        let visible = 0;
        ordered.forEach((product) => {
            const card = cards.get(String(product.id));
            const match = (!category || product.category === category) && `${product.name} ${product.size} ${product.category}`.toLocaleLowerCase().includes(query);
            card.hidden = !match;
            if (match) visible++;
            $('productGrid').appendChild(card);
        });
        $('productCount').textContent = `${visible} product${visible === 1 ? '' : 's'}`;
        $('catalogEmpty').hidden = visible > 0;
        document.querySelectorAll('[data-category]').forEach((button) => {
            const selected = button.dataset.category === category;
            button.classList.toggle('selected', selected);
            button.setAttribute('aria-pressed', String(selected));
        });
    }

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-open-basket]')) openOverlay($('cart'));
        if (event.target.closest('[data-close-overlay]')) closeOverlay();
        const action = event.target.closest('[data-cart-action]');
        if (action) changeQuantity(action.dataset.product, action.dataset.cartAction);
        const categoryButton = event.target.closest('[data-category]');
        if (categoryButton) { category = categoryButton.dataset.category; filterProducts(); }
        const categoryLink = event.target.closest('[data-category-link]');
        if (categoryLink) {
            category = categoryLink.dataset.categoryLink;
            $('productSearch').value = '';
            filterProducts();
            $('products').scrollIntoView();
        }
    });
    $('productSearch').addEventListener('input', filterProducts);
    $('productSort').addEventListener('change', filterProducts);
    $('searchForm').addEventListener('submit', (event) => {
        event.preventDefault(); filterProducts(); $('products').scrollIntoView(); $('productSearch').blur();
    });
    $('resetFilters').addEventListener('click', () => {
        category = ''; $('productSearch').value = ''; $('productSort').value = 'featured'; filterProducts();
    });

    $('checkoutButton').addEventListener('click', () => {
        if (!count()) return;
        $('checkoutFields').hidden = false;
        $('orderSuccess').hidden = true;
        $('orderError').hidden = true;
        openOverlay($('checkout'));
    });

    $('orderForm').addEventListener('submit', async (event) => {
        event.preventDefault();
        if (submitting) return;
        if (!count()) { $('orderError').textContent = 'Add an item to your basket before placing an order.'; $('orderError').hidden = false; return; }
        submitting = true;
        $('placeOrder').disabled = true;
        $('placeOrder').textContent = 'Placing your order…';
        $('orderError').hidden = true;
        const payload = Object.fromEntries(new FormData($('orderForm')));
        const orderedItems = entries();
        payload.items = orderedItems.map((item) => ({ product_id: item.id, quantity: item.quantity }));
        try {
            const response = await fetch(config.orderUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(payload),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Your order could not be placed. Please try again.');
            orderedItems.forEach((item) => {
                const product = products.get(String(item.id));
                product.availableStock = Math.max(0, product.availableStock - item.quantity);
                product.max = Math.min(100, product.availableStock);
            });
            cart.clear();
            render();
            $('orderReference').textContent = data.order_number;
            $('checkoutFields').hidden = true;
            $('orderSuccess').hidden = false;
            $('orderForm').reset();
            $('orderSuccess').querySelector('button').focus();
        } catch (error) {
            $('orderError').textContent = error instanceof TypeError || error instanceof SyntaxError ? 'We could not reach the store. Please check your connection and try again.' : error.message;
            $('orderError').hidden = false;
            $('orderError').scrollIntoView({ block: 'nearest' });
        } finally {
            submitting = false;
            $('placeOrder').disabled = false;
            $('placeOrder').innerHTML = `Place order ${icon('arrow')}`;
        }
    });

    render();
    filterProducts();
})();
