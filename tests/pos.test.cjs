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
            const el = { value: '', listeners: {}, classList: { add() {}, remove() {} },
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
                const [, id, act] = selector.match(/data-line="(\d+)".*data-act="([^"]+)"/);
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
