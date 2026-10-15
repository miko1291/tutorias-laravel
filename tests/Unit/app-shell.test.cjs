const {test} = require('node:test');
const assert = require('node:assert/strict');
const {readFileSync} = require('node:fs');
const {runInNewContext} = require('node:vm');
const source = readFileSync('public/js/app-shell.js', 'utf8');

function setup() {
    const events = {};
    const timers = [];
    const button = {tagName: 'BUTTON', innerHTML: 'Guardar', disabled: false, dataset: {}, classList: {add() {}, remove() {}}};
    const form = {method: 'post', dataset: {}, matches: () => false, querySelectorAll: () => [button]};
    let now = 0;
    let logouts = 0;
    let tick;
    const document = {
        getElementById: () => ({requestSubmit() { logouts++; }}),
        addEventListener: (type, handler) => { events[type] = handler; },
        querySelector: () => null,
        querySelectorAll: () => form.dataset.submitting ? [form] : [],
    };
    const window = {
        setInterval: handler => { tick = handler; },
        setTimeout: handler => timers.push(handler),
        addEventListener: (type, handler) => { events[type] = handler; },
    };
    runInNewContext(source, {document, window, Date: {now: () => now}});
    const submit = (cancelled = false) => {
        const event = {target: form, defaultPrevented: cancelled, preventDefault() { this.defaultPrevented = true; }};
        events.submit(event);
        return event;
    };
    return {events, timers, form, button, submit, advance: milliseconds => { now += milliseconds; tick(); }, logouts: () => logouts};
}

test('cancelled and GET submissions keep buttons usable', () => {
    const ui = setup();
    ui.submit(true);
    ui.form.method = 'get';
    ui.submit();
    assert.equal(ui.button.disabled, false);
    assert.equal(ui.form.dataset.submitting, undefined);
});

test('valid submission preserves button data until serialization and prevents duplicates', () => {
    const ui = setup();
    ui.submit();
    assert.equal(ui.button.disabled, false);
    assert.equal(ui.submit().defaultPrevented, true);
    ui.timers.forEach(callback => callback());
    assert.equal(ui.button.disabled, true);
    assert.match(ui.button.innerHTML, /Procesando/);
});

test('returning through browser history restores submit controls', () => {
    const ui = setup();
    ui.submit();
    ui.timers.forEach(callback => callback());
    ui.events.pageshow();
    assert.equal(ui.button.disabled, false);
    assert.equal(ui.button.innerHTML, 'Guardar');
    assert.equal(ui.form.dataset.submitting, undefined);
});

test('a later validation handler can cancel submission without locking the form', () => {
    const ui = setup();
    const event = ui.submit();
    event.preventDefault();
    ui.timers.forEach(callback => callback());
    assert.equal(ui.button.disabled, false);
    assert.equal(ui.form.dataset.submitting, undefined);
});

test('activity resets the inactivity timeout before logout', () => {
    const ui = setup();
    ui.advance(9 * 60 * 1000);
    assert.equal(ui.logouts(), 0);
    ui.events.keydown();
    ui.advance(9 * 60 * 1000);
    assert.equal(ui.logouts(), 0);
    ui.advance(60 * 1000);
    assert.equal(ui.logouts(), 1);
});
