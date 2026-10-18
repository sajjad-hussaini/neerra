(() => {
    const form = document.getElementById('productForm');
    if (!form) return;
    const fields = document.getElementById('biscuitStockFields');
    const inputs = [...fields.querySelectorAll('input')];
    const stock = form.elements.stock;
    let regularStock = stock.value;
    let wasBiscuit = false;
    function update() {
        const biscuit = /biscuit|biscut|cookie/i.test(form.elements.name.value + ' ' + form.elements.category.value);
        if (biscuit && !wasBiscuit) regularStock = stock.value;
        fields.hidden = !biscuit;
        inputs.forEach((input) => { input.disabled = !biscuit; input.required = biscuit; });
        stock.readOnly = biscuit;
        document.getElementById('stockLabel').textContent = biscuit ? 'Total biscuit stock (all prices)' : 'Available stock';
        if (biscuit) stock.value = inputs.reduce((sum, input) => sum + Math.max(0, Number(input.value) || 0), 0);
        else if (wasBiscuit) stock.value = regularStock;
        wasBiscuit = biscuit;
    }
    form.elements.name.addEventListener('input', update);
    form.elements.category.addEventListener('input', update);
    inputs.forEach((input) => input.addEventListener('input', update));
    update();
})();
