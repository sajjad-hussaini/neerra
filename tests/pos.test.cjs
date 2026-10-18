const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function setup(products) {
    const elements = new Map();
    const fields = new Map();
    let submitted;
    function element(id) {
        if (!elements.has(id)) {
            const el = { value: '', querySelectorAll: () => [], listeners: {}, classList: { add() {}, remove() {} },
                addEventListener(type, handler) { this.listeners[type] = handler; } };
            Object.defineProperty(el, 'innerHTML', {
                get() { return this.html; },
                set(html) {
                    this.html = html;
                    if (id !== 'orderItems') return;
                    fields.clear();
                    for (const tag of html.matchAll(/<input\b[^>]*>/g)) {
                        const attr = (name) => tag[0].match(new RegExp(`${name}="([^"]*)"`))?.[1];
                        const act = attr('data-act');
                        fields.set(`${attr('data-id')}:${act}`, { value: attr('value'), tagName: 'INPUT', dataset: { act, id: attr('data-id') } });
                    }
                }
            });
            elements.set(id, el);
        }
        return elements.get(id);
    }
    const window = { posConfig: { products, statsUrl: '/stats', storeUrl: '/sales' }, open() { return null; } };
    vm.runInNewContext(fs.readFileSync('public/js/pos.js', 'utf8'), {
        window, document: { getElementById: element, querySelectorAll: () => [],
            querySelector(selector) {
                if (selector.startsWith('meta')) return { content: 'token' };
                const [, id, act] = selector.match(/data-line="([^"]+)".*data-act="([^"]+)"/);
                return fields.get(`${id}:${act}`);
            } },
        Intl, Date, console, setTimeout() {}, alert(message) { throw Error(message); },
        async fetch(url, options) {
            if (options) submitted = JSON.parse(options.body);
            return { ok: true, async json() { return url === '/sales'
                ? { invoice_number: 'TEST', total: submitted.items.reduce((sum, line) => sum + line.line_total, 0), paid: 100, due: 0 }
                : { today_total: 0, today_orders: 0, month_total: 0, year_total: 0 }; } };
        }
    });
    return { window, element, submitted: () => submitted,
        value(id, act) { return Number(fields.get(`${id}:${act}`).value); },
        edit(id, act, value) {
            const target = fields.get(`${id}:${act}`);
            target.value = String(value);
            element('orderItems').listeners.input({ target });
            element('orderItems').listeners.change({ target });
        },
        click(id, act) { element('orderItems').listeners.click({ target: { dataset: { id: String(id), act } } }); }
    };
}
const product = (id, stock = 5) => ({ id, name: 'Water', size: '1L', category: 'water', price: 200, stock });

test('amount changes quantity at a fixed rate and saves fractional stock correctly', async () => {
    const app = setup([product(1, 1.5)]);
    app.window.posAdd(1);
    app.edit(1, 'total', 100);
    assert.equal(app.value(1, 'qty'), 0.5);
    assert.equal(app.value(1, 'total'), 100);
    await app.element('checkout').onclick();
    assert.equal(app.submitted().items[0].quantity, 0.5);
    assert.equal(app.submitted().items[0].unit_price, 200);
    app.window.posAdd(1);
    assert.equal(app.value(1, 'qty'), 1);
    app.window.posAdd(1);
    assert.equal(app.value(1, 'qty'), 1);
});

test('fractional stock is selectable and amount, quantity, and add buttons respect stock', () => {
    const app = setup([product(1, 0.5), product(2, 1.5), product(3, 0)]);
    assert.match(app.element('products').innerHTML, /posAdd\(1\)" >/);
    app.window.posAdd(1);
    assert.equal(app.value(1, 'qty'), 0.5);
    assert.equal(app.value(1, 'total'), 100);
    app.edit(1, 'total', 500);
    assert.equal(app.value(1, 'qty'), 0.5);
    assert.equal(app.value(1, 'total'), 100);
    app.window.posAdd(2);
    app.window.posAdd(2);
    assert.equal(app.value(2, 'qty'), 1.5);
    app.edit(2, 'qty', 10);
    assert.equal(app.value(2, 'qty'), 1.5);
    app.edit(2, 'qty', 0.2);
    app.click(2, 'inc');
    assert.equal(app.value(2, 'qty'), 1.2);
    app.click(2, 'inc');
    assert.equal(app.value(2, 'qty'), 1.5);
    app.window.posAdd(3);
    assert.doesNotMatch(app.element('orderItems').innerHTML, /data-line="3"/);
});

test('decimal quantities round money consistently and zero amount blocks checkout', () => {
    const app = setup([{ ...product('1'), price: 199 }, product(2)]);
    app.window.posAdd(1);
    app.edit(1, 'qty', 0.1);
    assert.equal(app.value(1, 'total'), 20);
    app.window.posAdd(1);
    assert.equal(app.value(1, 'total'), 219);
    app.edit(1, 'total', 0);
    app.window.posAdd(2);
    assert.equal(app.element('checkout').disabled, true);
    app.edit(1, 'total', 100);
    assert.equal(app.element('checkout').disabled, false);
});

function chooseBiscuitAmount(app, amount) {
    const button = { dataset: { amount: String(amount) } };
    app.element('biscuitPriceOptions').listeners.click({ target: { closest: () => button } });
}
const biscuit = (id, stock = 5) => ({ ...product(id, stock * 6), name: 'Biscuits', category: 'Biscuits', price_stocks: { 10: stock, 20: stock, 30: stock, 40: stock, 50: stock, 100: stock } });

test('one biscuit product supports separate prices with whole quantities and one shared product ID', async () => {
    const app = setup([biscuit(1, 10), product(2)]);
    app.window.posAdd(1);
    assert.equal(app.element('biscuitPrices').hidden, false);
    for (const amount of [10, 20, 30, 40, 50, 100]) {
        assert.ok(app.element('biscuitPriceOptions').innerHTML.includes('data-amount="' + amount + '"'));
    }
    assert.doesNotMatch(app.element('orderItems').innerHTML, /data-line="1-/);
    chooseBiscuitAmount(app, 20);
    chooseBiscuitAmount(app, 20);
    chooseBiscuitAmount(app, 50);
    assert.equal(app.value('1-20', 'qty'), 2);
    assert.equal(app.value('1-20', 'total'), 40);
    assert.equal(app.value('1-50', 'qty'), 1);
    assert.equal(app.value('1-50', 'total'), 50);
    assert.equal(app.element('total').textContent, 'Rs. 90');
    app.window.posAdd(2);
    assert.equal(app.element('biscuitPrices').hidden, true);
    assert.equal(app.element('total').textContent, 'Rs. 290');
    await app.element('checkout').onclick();
    const lines = app.submitted().items.filter((line) => line.product_id === 1);
    assert.equal(lines.length, 2);
    assert.deepEqual(lines.map((line) => [line.quantity, line.unit_price, line.line_total]), [[2, 20, 40], [1, 50, 50]]);
    app.window.posAdd(1);
    // Sales reduced the selected prices only. Rs. 10 stock is still ten.
    for (let i = 0; i < 11; i++) chooseBiscuitAmount(app, 10);
    assert.equal(app.value('1-10', 'qty'), 10);
    assert.match(app.element('biscuitPriceStatus').textContent, /Not enough/);
});

test('each price has independent stock and quantity edits cannot exceed that price stock', () => {
    const app = setup([biscuit(1, 3)]);
    app.window.posAdd(1);
    chooseBiscuitAmount(app, 10);
    chooseBiscuitAmount(app, 50);
    app.edit('1-10', 'qty', 10);
    assert.equal(app.value('1-10', 'qty'), 3);
    assert.equal(app.value('1-10', 'total'), 30);
    assert.equal(app.value('1-50', 'qty'), 1);
    chooseBiscuitAmount(app, 10);
    assert.equal(app.value('1-10', 'qty'), 3);
    assert.match(app.element('biscuitPriceStatus').textContent, /Not enough/);
    chooseBiscuitAmount(app, 30);
    assert.equal(app.value('1-30', 'qty'), 1);
    app.click('1-50', 'inc');
    assert.equal(app.value('1-50', 'qty'), 2);
    app.click('1-10', 'dec');
    assert.equal(app.value('1-10', 'qty'), 2);
    app.edit('1-10', 'total', 100);
    assert.equal(app.value('1-10', 'total'), 20);
});

test('unallocated and empty biscuit price stocks cannot be sold', () => {
    const app = setup([{ ...biscuit(1), price_stocks: null }, { ...biscuit(2), price_stocks: { 20: 1 } }]);
    app.window.posAdd(1);
    assert.match(app.element('biscuitPriceStatus').textContent, /Set stock for each/);
    chooseBiscuitAmount(app, 10);
    assert.doesNotMatch(app.element('orderItems').innerHTML, /data-line="1-/);
    app.window.posAdd(2);
    chooseBiscuitAmount(app, 10);
    assert.doesNotMatch(app.element('orderItems').innerHTML, /data-line="2-10"/);
    chooseBiscuitAmount(app, 20);
    assert.equal(app.value('2-20', 'qty'), 1);
});

test('clearing removes all price options from the order and base price does not affect biscuit pricing', () => {
    const app = setup([{ ...biscuit(1), price: 5000 }]);
    app.window.posAdd(1);
    chooseBiscuitAmount(app, 10);
    assert.equal(app.value('1-10', 'qty'), 1);
    assert.equal(app.value('1-10', 'total'), 10);
    app.element('clear').onclick();
    assert.equal(app.element('biscuitPrices').hidden, true);
    app.window.posAdd(1);
    assert.equal(app.element('biscuitPriceStatus').textContent, 'Biscuit total: Rs. 0');
    chooseBiscuitAmount(app, 50);
    assert.equal(app.value('1-50', 'total'), 50);
    assert.doesNotMatch(app.element('orderItems').innerHTML, /data-line="1-10"/);
});
