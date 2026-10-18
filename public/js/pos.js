(() => {
    const config = window.posConfig;
    if (!config) return;
    const products = config.products.map((p) => ({
        ...p,
        id: Number(p.id),
        price: Number(p.price),
        stock: Number(p.stock),
    }));
    const customers = (config.customers || []).map((c) => ({
        id: Number(c.id),
        name: c.name,
        phone: c.phone,
    }));
    // Cart keys separate price options for the same product.
    let cart = {},
        payment = "Cash",
        customerId = "",
        paidTouched = false,
        selectedBiscuitId = null;
    const $ = (id) => document.getElementById(id),
        money = (n) => "Rs. " + new Intl.NumberFormat("en-PK").format(n),
        int = (v) => {
            const n = Math.round(Number(v));
            return Number.isFinite(n) && n > 0 ? n : 0;
        },
        csrf = () => document.querySelector("meta[name=csrf-token]").content;
    const qty2dp = (v) => {
            const n = Number(v);
            return Number.isFinite(n) && n > 0 ? Math.round(n * 100) / 100 : 0;
        };
    const icon = (p) => {
        const category = String(p.category || "").toLowerCase();
        if (/biscuit|cookie/.test(category)) return "&#127850;";
        if (/cake|bakery/.test(category)) return "&#127856;";
        if (/bread/.test(category)) return "&#127838;";
        if (/snack|chips/.test(category)) return "&#127871;";
        if (/drink|juice|beverage/.test(category)) return "&#129475;";
        if (/water/.test(category)) return "&#128167;";
        if (/milk|dairy/.test(category)) return "&#129371;";
        return "&#128230;";
    };
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

    const biscuitAmounts = [10, 20, 30, 40, 50, 100];
    const isBiscuit = (p) => /biscuit|biscut|cookie/i.test(p.category + " " + p.name);

    function closeBiscuitPrices() {
        selectedBiscuitId = null;
        $("biscuitPrices").hidden = true;
    }

    function updateBiscuitPriceTotal() {
        if (selectedBiscuitId === null) return;
        const selected = products.find((p) => p.id === selectedBiscuitId);
        if (!selected.price_stocks) {
            $("biscuitPriceStatus").textContent = "Set stock for each biscuit price in Manage products > Edit.";
            return;
        }
        const total = Object.values(cart).filter((line) => line.productId === selectedBiscuitId)
            .reduce((sum, line) => sum + line.lineTotal, 0);
        $("biscuitPriceStatus").textContent = "Biscuit total: " + money(total);
        $("biscuitPriceOptions").querySelectorAll('[data-amount]').forEach((button) => {
            const remaining = availableStock(selectedBiscuitId, null, Number(button.dataset.amount));
            button.disabled = remaining < 1;
            button.querySelector('small').textContent = remaining + " left";
        });
    }

    function openBiscuitPrices(product) {
        selectedBiscuitId = product.id;
        $("biscuitPriceName").textContent = product.name + " (" + product.size + ")";
        $("biscuitPriceOptions").innerHTML = biscuitAmounts.map((amount) => {
            const remaining = availableStock(product.id, null, amount);
            return `<button type="button" class="biscuit-price-button" data-amount="${amount}" ${remaining < 1 ? 'disabled' : ''}>${money(amount)}<small>${remaining} left</small></button>`;
        }).join("");
        $("biscuitPrices").hidden = false;
        updateBiscuitPriceTotal();
    }

    $("closeBiscuitPrices").onclick = closeBiscuitPrices;
    $("biscuitPriceOptions").addEventListener("click", (event) => {
        const button = event.target.closest("[data-amount]");
        if (!button || selectedBiscuitId === null) return;
        const amount = Number(button.dataset.amount);
        const product = products.find((p) => p.id === selectedBiscuitId);
        if (!product || !isBiscuit(product) || !biscuitAmounts.includes(amount)) return;
        if (availableStock(product.id, null, amount) < 1) {
            $("biscuitPriceStatus").textContent = "Not enough biscuit stock.";
            return;
        }
        const key = product.id + "-" + amount;
        const qty = (cart[key]?.qty || 0) + 1;
        cart[key] = { productId: product.id, qty, unitPrice: amount, lineTotal: qty * amount };
        renderCart();
    });

    const cartEntries = () => Object.entries(cart).map(([key, line]) => ({
        ...products.find((p) => p.id === line.productId),
        productId: line.productId,
        id: key,
    }));
    function availableStock(productId, excludeKey = null, price = null) {
        const product = products.find((p) => p.id === productId);
        if (isBiscuit(product)) {
            const selectedPrice = price ?? cart[excludeKey]?.unitPrice;
            const key = productId + "-" + selectedPrice;
            return Math.max(0, Number(product.price_stocks?.[selectedPrice] || 0) - (String(excludeKey) === key ? 0 : cart[key]?.qty || 0));
        }
        const used = Object.entries(cart).reduce((sum, [key, line]) =>
            key !== String(excludeKey) && line.productId === productId ? sum + line.qty : sum, 0);
        return qty2dp(product.stock - used);
    }
    const sumQty = () => qty2dp(cartEntries().reduce((a, p) => a + cart[p.id].qty, 0));
    const sumTotal = () =>
        cartEntries().reduce((a, p) => a + cart[p.id].lineTotal, 0);

    function renderProducts(query = "") {
        const q = query.toLowerCase();
        $("products").innerHTML =
            products
                .filter((p) =>
                    (p.name + p.size + p.category).toLowerCase().includes(q),
                )
                .map(
                    (p) =>
                        `<button class="product" onclick="posAdd(${p.id})" ${p.stock <= 0 ? "disabled" : ""}><div class="product-icon">${icon(p)}</div><h3>${escape(p.name)}</h3><small>${escape(p.size)} / ${p.stock} in stock</small><div class="product-price"><span>${isBiscuit(p) ? "Choose price" : money(p.price)}</span><span class="add">+</span></div></button>`,
                )
                .join("") || '<p class="empty">No product found.</p>';
    }

    function lineHTML(p) {
        const line = cart[p.id];
        return `<div class="item" data-line="${p.id}"><div class="item-icon">${icon(p)}</div><div class="item-main"><div class="item-name">${escape(p.name)}</div><small>${escape(p.size)}</small><div class="item-controls"><span class="unit-price">Rate: ${money(line.unitPrice)}</span><div class="qty"><button type="button" data-act="dec" data-id="${p.id}">-</button><input class="qty-input" type="number" min="${isBiscuit(p) ? 1 : 0.01}" max="${isBiscuit(p) ? Math.floor(availableStock(p.productId, p.id)) : p.stock}" step="${isBiscuit(p) ? 1 : 0.01}" inputmode="decimal" aria-label="Quantity" value="${line.qty}" data-act="qty" data-id="${p.id}"><button type="button" data-act="inc" data-id="${p.id}">+</button></div></div></div><div class="item-amount">Price Rs.<input class="amount-input" aria-label="Total price" title="Enter amount to calculate quantity at the product rate" type="number" min="0" step="1" inputmode="numeric" value="${line.lineTotal}" data-act="total" data-id="${p.id}" ${isBiscuit(p) || line.unitPrice <= 0 ? "readonly" : ""}></div></div>`;
    }

    function renderCart() {
        const entries = cartEntries();
        $("orderItems").innerHTML = entries.length
            ? entries.map(lineHTML).join("")
            : '<div class="empty">Your cart is empty.<br>Add products to start a sale.</div>';
        patchTotals();
    }

    function patchTotals() {
        updateBiscuitPriceTotal();
        const count = sumQty(),
            total = sumTotal();
        $("cartCount").textContent = `${count} item${count === 1 ? "" : "s"}`;
        $("subtotal").textContent = money(total);
        $("total").textContent = money(total);
        $("checkout").disabled = !count || cartEntries().some((p) => cart[p.id].qty <= 0);
        const hasCustomer = !!customerId;
        $("paidRow").hidden = !hasCustomer;
        $("dueRow").hidden = !hasCustomer;
        if (hasCustomer) {
            if (!paidTouched) $("paidAmount").value = '';
            const paid = Math.max(0, Math.min(int($("paidAmount").value), total));
            $("dueAmount").textContent = money(total - paid);
        }
    }

    function setInput(id, act, value) {
        const el = document.querySelector(
            `[data-line="${id}"] [data-act="${act}"]`,
        );
        if (el) el.value = value;
    }

    function patchLine(id) {
        const line = cart[id];
        if (!line) return;
        setInput(id, "price", line.unitPrice);
        setInput(id, "qty", line.qty);
        setInput(id, "total", line.lineTotal);
    }

    // Live math on edit; only touches sibling fields (never the one being typed in).
    function posEdit(id, act, raw) {
        const line = cart[id];
        if (!line) return;
        const p = products.find((x) => x.id === line.productId);
        const maxQty = isBiscuit(p) ? Math.floor(availableStock(p.id, id)) : p.stock;
        if (act === "total" && !isBiscuit(p) && line.unitPrice > 0) {
            line.lineTotal = int(raw);
            line.qty = qty2dp(line.lineTotal / line.unitPrice);
            if (line.qty > p.stock || line.lineTotal > Math.round(p.stock * line.unitPrice)) {
                line.qty = p.stock;
                line.lineTotal = Math.round(line.qty * line.unitPrice);
                setInput(id, "total", line.lineTotal);
            }
            setInput(id, "qty", line.qty);
        } else if (act === "qty") {
            let q = isBiscuit(p) ? Math.max(1, int(raw)) : qty2dp(raw) || 0.01;
            if (q > maxQty) q = maxQty;
            line.qty = q;
            line.lineTotal = Math.round(line.qty * line.unitPrice);
            setInput(id, "total", line.lineTotal);
        }
        patchTotals();
    }

    function changeQty(id, delta) {
        const line = cart[id];
        if (!line) return;
        const p = products.find((x) => x.id === line.productId);
        const maxQty = isBiscuit(p) ? Math.floor(availableStock(p.id, id)) : p.stock;
        const q = qty2dp(Math.min(maxQty, line.qty + delta));
        if (q <= 0) {
            delete cart[id];
            renderCart();
            return;
        }
        if (q > p.stock) return;
        line.qty = q;
        line.lineTotal = Math.round(line.qty * line.unitPrice);
        renderCart();
    }

    window.posAdd = (id) => {
        const p = products.find((p) => p.id === id);
        if (!p || p.stock <= 0) return;
        if (isBiscuit(p)) {
            openBiscuitPrices(p);
            return;
        }
        closeBiscuitPrices();
        const line = cart[id];
        if (line) {
            if (line.qty >= p.stock) return;
            line.qty = qty2dp(Math.min(p.stock, line.qty + 1));
            line.lineTotal = Math.round(line.qty * line.unitPrice);
        } else {
            const qty = Math.min(1, p.stock);
            cart[id] = { productId: p.id, qty, unitPrice: p.price, lineTotal: Math.round(qty * p.price) };
        }
        renderCart();
    };

    // Cart line events (delegated so dynamic inputs stay wired).
    $("orderItems").addEventListener("input", (e) => {
        const t = e.target,
            act = t.dataset.act;
        if (!act || t.tagName !== "INPUT") return;
        posEdit(t.dataset.id, act, t.value);
    });
    $("orderItems").addEventListener("change", (e) => {
        const t = e.target,
            act = t.dataset.act;
        if (!act || t.tagName !== "INPUT") return;
        posEdit(t.dataset.id, act, t.value);
        patchLine(t.dataset.id); // normalise (clamp qty, blank -> 0)
    });
    $("orderItems").addEventListener("click", (e) => {
        const t = e.target,
            act = t.dataset.act;
        if (act === "inc") changeQty(t.dataset.id, 1);
        else if (act === "dec") changeQty(t.dataset.id, -1);
    });

    function printReceipt(invoice, lines, total, paid, due, customerName) {
        const content = lines
            .map(
                ({ product, qty, unitPrice, lineTotal }) =>
                    `<tr><td>${escape(product.name)}<br><small>${escape(product.size)}</small></td><td>${money(unitPrice)}</td><td>${qty}</td><td>${money(lineTotal)}</td></tr>`,
            )
            .join("");
        const custRow = customerName
            ? `<p>Customer: ${escape(customerName)}</p>`
            : "";
        const payRows =
            Number(due) > 0
                ? `<p style="text-align:right;margin-top:20px"><strong>Total: ${money(total)}</strong></p><p style="text-align:right">Paid: ${money(paid)}</p><p style="text-align:right;color:#b00000"><strong>Udhaar / Balance: ${money(due)}</strong></p>`
                : `<p style="text-align:right;margin-top:20px"><strong>Total: ${money(total)}</strong></p>`;
        const popup = window.open("", "_blank", "width=520,height=650");
        if (!popup) return;
        popup.document.write(
            `<!doctype html><html><head><title>${escape(invoice)}</title><style>body{font:14px Arial;padding:22px;color:#182d3a}h1{text-align:center;margin:0}p{text-align:center;color:#667;margin:7px}table{width:100%;border-collapse:collapse;margin-top:20px;font-size:12px}th{padding:9px 4px;border-bottom:2px solid #182d3a;text-align:left}td{padding:9px 4px;border-bottom:1px dashed #bbb}th:not(:first-child),td:not(:first-child){text-align:right}strong{font-size:16px}@media print{body{padding:0}}</style></head><body><h1>Neerra</h1><p>Sales Receipt</p><p>${escape(invoice)}<br>${new Date().toLocaleString("en-PK")}</p>${custRow}<table><thead><tr><th>Item</th><th>Unit Price</th><th>Qty</th><th>Total Price</th></tr></thead><tbody>${content}</tbody></table>${payRows}<p>Payment: ${escape(payment)}<br>Thank you!</p><script>window.onafterprint=()=>window.close();window.onload=()=>window.print()<\/script></body></html>`,
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

    $("customerSelect").addEventListener("change", (e) => {
        customerId = e.target.value;
        paidTouched = false;
        patchTotals();
    });
    $("paidAmount").addEventListener("input", () => {
        paidTouched = true;
        const total = sumTotal();
        const paid = Math.max(0, Math.min(int($("paidAmount").value), total));
        $("dueAmount").textContent = money(total - paid);
    });

    // Inline "new customer" form
    const clearNewCustomer = () => {
        $("newCustomerName").value = "";
        $("newCustomerPhone").value = "";
    };
    $("newCustomerBtn").onclick = () => {
        $("newCustomerForm").hidden = false;
        $("newCustomerName").focus();
    };
    $("cancelCustomerBtn").onclick = () => {
        $("newCustomerForm").hidden = true;
        clearNewCustomer();
    };
    $("saveCustomerBtn").onclick = async () => {
        const name = $("newCustomerName").value.trim(),
            phone = $("newCustomerPhone").value.trim();
        if (!name) {
            $("newCustomerName").focus();
            return;
        }
        const btn = $("saveCustomerBtn");
        btn.disabled = true;
        try {
            const r = await fetch(config.customerStoreUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrf(),
                },
                body: JSON.stringify({ name, phone }),
            }),
                data = await r.json();
            if (!r.ok)
                throw Error(data.message || "Customer could not be saved.");
            const opt = document.createElement("option");
            opt.value = data.id;
            opt.textContent = data.name + (data.phone ? " · " + data.phone : "");
            $("customerSelect").appendChild(opt);
            $("customerSelect").value = data.id;
            customerId = String(data.id);
            paidTouched = false;
            customers.push({ id: data.id, name: data.name, phone: data.phone });
            $("newCustomerForm").hidden = true;
            clearNewCustomer();
            patchTotals();
        } catch (e) {
            alert(e.message);
        } finally {
            btn.disabled = false;
        }
    };

    $("clear").onclick = () => {
        closeBiscuitPrices();
        cart = {};
        renderCart();
    };

    function resetAfterSale() {
        customerId = "";
        $("customerSelect").value = "";
        paidTouched = false;
        $("paidAmount").value = "";
    }

    $("checkout").onclick = async () => {
        const btn = $("checkout"),
            entries = cartEntries();
        if (!entries.length) return;
        const total = sumTotal(),
            hasCustomer = !!customerId,
            paid = hasCustomer
                ? Math.max(0, Math.min(int($("paidAmount").value), total))
                : total,
            due = total - paid,
            receiptLines = entries.map((product) => ({
                product,
                qty: cart[product.id].qty,
                unitPrice: cart[product.id].unitPrice,
                lineTotal: cart[product.id].lineTotal,
            })),
            customerName = hasCustomer
                ? $("customerSelect").selectedOptions[0]?.textContent || ""
                : "";
        btn.disabled = true;
        try {
            const r = await fetch(config.storeUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrf(),
                },
                body: JSON.stringify({
                    payment_method: payment,
                    customer_id: hasCustomer ? Number(customerId) : null,
                    paid_amount: paid,
                    items: entries.map((product) => ({
                        product_id: product.productId,
                        quantity: cart[product.id].qty,
                        unit_price: cart[product.id].unitPrice,
                        line_total: cart[product.id].lineTotal,
                    })),
                }),
            }),
                data = await r.json();
            if (!r.ok) throw Error(data.message || "Sale could not be saved.");
            receiptLines.forEach(({ product, qty, unitPrice }) => {
                const original = products.find((p) => p.id === product.productId);
                original.stock = qty2dp(original.stock - qty);
                if (isBiscuit(original)) {
                    original.price_stocks[unitPrice] = Math.max(0, Number(original.price_stocks[unitPrice] || 0) - qty);
                }
            });
            closeBiscuitPrices();
            cart = {};
            resetAfterSale();
            renderProducts($("search").value);
            renderCart();
            stats();
            printReceipt(
                data.invoice_number,
                receiptLines,
                data.total,
                data.paid,
                data.due,
                customerName,
            );
            const t = $("toast");
            t.textContent =
                Number(data.due) > 0
                    ? `Sale saved - ${data.invoice_number} - Udhaar ${money(data.due)}`
                    : `Sale saved - ${data.invoice_number} - ${money(data.total)}`;
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
