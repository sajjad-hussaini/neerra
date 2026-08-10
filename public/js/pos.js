(() => {
    const config = window.posConfig;
    if (!config) return;
    const products = config.products.map((p) => ({
        ...p,
        price: Number(p.price),
        stock: Number(p.stock),
    }));
    let cart = {},
        payment = "Cash";
    const $ = (id) => document.getElementById(id),
        money = (n) => "Rs. " + new Intl.NumberFormat("en-PK").format(n);
    const icon = (p) =>
        p.category.toLowerCase() === "water" ? "&#128167;" : "&#129371;";
    const escape = (value) =>
        String(value).replace(
            /[&<>"']/g,
            (char) =>
                ({
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': "&quot;",
                    "'": "&#039;",
                })[char],
        );
    function renderProducts(query = "") {
        const q = query.toLowerCase();
        $("products").innerHTML =
            products
                .filter((p) =>
                    (p.name + p.size + p.category).toLowerCase().includes(q),
                )
                .map(
                    (p) =>
                        `<button class="product" onclick="posAdd(${p.id})" ${p.stock < 1 ? "disabled" : ""}><div class="product-icon">${icon(p)}</div><h3>${escape(p.name)}</h3><small>${escape(p.size)} / ${p.stock} in stock</small><div class="product-price"><span>${money(p.price)}</span><span class="add">+</span></div></button>`,
                )
                .join("") || '<p class="empty">No product found.</p>';
    }
    function renderCart() {
        const entries = products.filter((p) => cart[p.id]),
            count = entries.reduce((a, p) => a + cart[p.id], 0),
            total = entries.reduce((a, p) => a + p.price * cart[p.id], 0);
        $("cartCount").textContent = `${count} item${count === 1 ? "" : "s"}`;
        $("subtotal").textContent = money(total);
        $("total").textContent = money(total);
        $("checkout").disabled = !count;
        $("orderItems").innerHTML = entries.length
            ? entries
                  .map(
                      (p) =>
                          `<div class="item"><div class="item-icon">${icon(p)}</div><div><div class="item-name">${escape(p.name)}</div><small>${escape(p.size)} / ${money(p.price)}</small></div><div><div class="item-total">${money(p.price * cart[p.id])}</div><div class="qty"><button onclick="posChange(${p.id},-1)">-</button><span>${cart[p.id]}</span><button onclick="posChange(${p.id},1)">+</button></div></div></div>`,
                  )
                  .join("")
            : '<div class="empty">Your cart is empty.<br>Add products to start a sale.</div>';
    }
    window.posAdd = (id) => {
        const p = products.find((p) => p.id === id);
        if ((cart[id] || 0) < p.stock) {
            cart[id] = (cart[id] || 0) + 1;
            renderCart();
        }
    };
    window.posChange = (id, amount) => {
        cart[id] += amount;
        if (cart[id] <= 0) delete cart[id];
        renderCart();
    };
    function printReceipt(invoice, lines, total) {
        const content = lines
            .map(
                ({ product, quantity }) =>
                    `<tr><td>${escape(product.name)}<br><small>${escape(product.size)}</small></td><td>${money(product.price)}</td><td>${quantity}</td><td>${money(product.price * quantity)}</td></tr>`,
            )
            .join("");
        const popup = window.open("", "_blank", "width=520,height=650");
        if (!popup) return;
        popup.document.write(
            `<!doctype html><html><head><title>${escape(invoice)}</title><style>body{font:14px Arial;padding:22px;color:#182d3a}h1{text-align:center;margin:0}p{text-align:center;color:#667;margin:7px}table{width:100%;border-collapse:collapse;margin-top:20px;font-size:12px}th{padding:9px 4px;border-bottom:2px solid #182d3a;text-align:left}td{padding:9px 4px;border-bottom:1px dashed #bbb}th:not(:first-child),td:not(:first-child){text-align:right}strong{font-size:18px}@media print{body{padding:0}}</style></head><body><h1>Neerra</h1><p>Sales Receipt</p><p>${escape(invoice)}<br>${new Date().toLocaleString("en-PK")}</p><table><thead><tr><th>Item</th><th>Unit Price</th><th>Qty</th><th>Total Price</th></tr></thead><tbody>${content}</tbody></table><p style="text-align:right;margin-top:20px"><strong>Total: ${money(total)}</strong></p><p>Payment: ${escape(payment)}<br>Thank you!</p><script>window.onafterprint=()=>window.close();window.onload=()=>window.print()<\/script></body></html>`,
        );
        popup.document.close();
    }
    $("search").oninput = (e) => renderProducts(e.target.value);
    document.querySelectorAll(".method").forEach(
        (b) =>
            (b.onclick = () => {
                payment = b.dataset.method;
                document
                    .querySelectorAll(".method")
                    .forEach((x) => x.classList.toggle("selected", x === b));
            }),
    );
    $("clear").onclick = () => {
        cart = {};
        renderCart();
    };
    $("checkout").onclick = async () => {
        const btn = $("checkout"),
            receiptLines = products
                .filter((p) => cart[p.id])
                .map((product) => ({ product, quantity: cart[product.id] }));
        btn.disabled = true;
        try {
            const r = await fetch(config.storeUrl, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            "meta[name=csrf-token]",
                        ).content,
                    },
                    body: JSON.stringify({
                        payment_method: payment,
                        items: Object.entries(cart).map(
                            ([product_id, quantity]) => ({
                                product_id: +product_id,
                                quantity,
                            }),
                        ),
                    }),
                }),
                data = await r.json();
            if (!r.ok) throw Error(data.message || "Sale could not be saved.");
            cart = {};
            renderCart();
            stats();
            printReceipt(data.invoice_number, receiptLines, data.total);
            const t = $("toast");
            t.textContent = `Sale saved - ${data.invoice_number} - ${money(data.total)}`;
            t.classList.add("show");
            setTimeout(() => t.classList.remove("show"), 3000);
        } catch (e) {
            alert(e.message);
            renderCart();
        }
    };
    async function stats() {
        try {
            const s = await fetch(config.statsUrl).then((r) => r.json());
            $("dailySales").textContent = money(s.today_total);
            $("orderCount").textContent = s.today_orders;
            $("monthSales").textContent = money(s.month_total);
            $("yearSales").textContent = money(s.year_total);
        } catch (e) {
            console.error(e);
        }
    }
    $("today").textContent = new Intl.DateTimeFormat("en-PK", {
        weekday: "short",
        day: "numeric",
        month: "short",
        year: "numeric",
    }).format(new Date());
    renderProducts();
    renderCart();
    stats();
})();
