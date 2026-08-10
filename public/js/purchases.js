(() => {
    const products = window.purchaseProducts || [];
    const rows = document.getElementById("purchaseRows");
    const total = document.getElementById("purchaseTotal");
    let nextIndex = 0;
    const money = (n) => "Rs. " + new Intl.NumberFormat("en-PK").format(n);

    function calculate() {
        total.textContent = money(
            [...rows.querySelectorAll(".purchase-row")].reduce(
                (sum, row) =>
                    sum +
                    (+row.querySelector(".qty-input").value || 0) *
                        (+row.querySelector(".cost-input").value || 0),
                0,
            ),
        );
    }

    function addRow() {
        const index = nextIndex++;
        const row = document.createElement("div");
        row.className = "purchase-row";
        row.innerHTML = `<label>Product<select name="items[${index}][product_id]" required><option value="">Choose product</option>${products.map((p) => `<option value="${p.id}">${p.name} (${p.size}) - stock: ${p.stock}</option>`).join("")}</select></label><label>Quantity<input class="qty-input" name="items[${index}][quantity]" type="number" min="1" value="1" required></label><label>Unit cost (Rs.)<input class="cost-input" name="items[${index}][unit_cost]" type="number" min="0" value="0" required></label><button class="remove-row" type="button">Remove</button>`;
        rows.append(row);
        row.querySelector(".remove-row").onclick = () => {
            row.remove();
            calculate();
        };
        row.querySelectorAll("input").forEach(
            (input) => (input.oninput = calculate),
        );
    }

    document.getElementById("addPurchaseRow").onclick = addRow;
    addRow();
})();
