(() => {
    const config = window.storeConfig,
        cart = {};
    const $ = (id) => document.getElementById(id);
    const money = (n) => "Rs. " + new Intl.NumberFormat("en-PK").format(n);
    function render() {
        const items = Object.values(cart),
            count = items.reduce((n, item) => n + item.quantity, 0),
            total = items.reduce(
                (n, item) => n + item.price * item.quantity,
                0,
            );
        $("cartCount").textContent = count;
        $("cartTotal").textContent = money(total);
        $("checkoutButton").disabled = !count;
        $("cartItems").innerHTML = items.length
            ? items
                  .map(
                      (item) =>
                          `<div class="cart-item"><div><h6>${item.name}</h6><small>${item.size} x ${item.quantity}</small></div><div class="text-end"><strong>${money(item.price * item.quantity)}</strong><br><button data-remove="${item.id}">Remove</button></div></div>`,
                  )
                  .join("")
            : '<p class="empty-cart">Your cart is empty.</p>';
        document.querySelectorAll("[data-remove]").forEach(
            (button) =>
                (button.onclick = () => {
                    delete cart[button.dataset.remove];
                    render();
                }),
        );
    }
    document.querySelectorAll(".add-cart").forEach(
        (button) =>
            (button.onclick = () => {
                const product = {
                    id: Number(button.dataset.id),
                    name: button.dataset.name,
                    size: button.dataset.size,
                    price: Number(button.dataset.price),
                };
                cart[product.id] = cart[product.id] || {
                    ...product,
                    quantity: 0,
                };
                cart[product.id].quantity++;
                render();
            }),
    );
    $("orderForm").onsubmit = async (event) => {
        event.preventDefault();
        const button = $("placeOrder"),
            error = $("orderError");
        button.disabled = true;
        error.classList.add("d-none");
        const form = Object.fromEntries(new FormData(event.target));
        form.items = Object.values(cart).map((item) => ({
            product_id: item.id,
            quantity: item.quantity,
        }));
        try {
            const response = await fetch(config.orderUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            "meta[name=csrf-token]",
                        ).content,
                    },
                    body: JSON.stringify(form),
                }),
                data = await response.json();
            if (!response.ok)
                throw Error(data.message || "Order could not be placed.");
            Object.keys(cart).forEach((key) => delete cart[key]);
            render();
            bootstrap.Modal.getInstance($("checkout")).hide();
            bootstrap.Offcanvas.getInstance($("cart")).hide();
            const toast = $("orderToast");
            toast.querySelector(".toast-body").textContent =
                `Thank you! Your order ${data.order_number} has been placed.`;
            new bootstrap.Toast(toast).show();
            event.target.reset();
        } catch (e) {
            error.textContent = e.message;
            error.classList.remove("d-none");
        } finally {
            button.disabled = false;
        }
    };
    render();
})();
