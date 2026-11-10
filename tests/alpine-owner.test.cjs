'use strict';

const { test } = require('node:test');
const assert = require('node:assert/strict');
const path = require('node:path');
const { buildSync } = require('esbuild');
const { JSDOM } = require('jsdom');

const root = path.resolve(__dirname, '..');
const theme = buildSync({
  absWorkingDir: root,
  entryPoints: ['src/js/main.js'],
  bundle: true,
  write: false,
}).outputFiles[0].text;
const tutor = buildSync({
  absWorkingDir: root,
  stdin: {
    contents: `import Alpine from 'alpinejs';
      import collapse from '@alpinejs/collapse';
      import focus from '@alpinejs/focus';
      window.pluginRegistrations = [];
      const directive = Alpine.directive;
      Alpine.directive = (...args) => { window.pluginRegistrations.push(args[0]); return directive(...args); };
      Alpine.plugin(collapse);
      Alpine.plugin(focus);
      window.Alpine = Alpine;
      Alpine.data('tutorFixture', () => ({ clicks: 0 }));
      window.startTutor = () => Alpine.start();`,
    resolveDir: root,
  },
  bundle: true,
  write: false,
}).outputFiles[0].text;

const settle = () => new Promise(resolve => setTimeout(resolve, 25));

function page(hasTutor) {
  const dom = new JSDOM(`<!doctype html><html><body>
    <header x-data="siteHeader">
      <button id="menu" @click="toggleMobile()">Open menu</button>
      <button id="close" @click="closeMobile()">Close menu</button>
      <aside x-show="mobileOpen">Navigation</aside>
    </header>
    <div id="stats" x-data="countUp(37)"><span x-text="display"></span></div>
    ${hasTutor ? '<div id="tutor" x-data="tutorFixture"><button @click="clicks++">Tutor</button></div>' : ''}
    </body></html>`, { runScripts: 'outside-only', pretendToBeVisual: true, url: 'https://example.test/' });
  const w = dom.window;
  const errors = [];
  let starts = 0;
  w.matchMedia = () => ({ matches: true });
  w.console.warn = (...args) => errors.push(args.map(String).join(' '));
  w.addEventListener('error', event => { errors.push(event.message); event.preventDefault(); });
  w.document.addEventListener('alpine:initialized', () => { starts++; });
  return { dom, w, errors, starts: () => starts };
}

async function assertControls(p) {
  const { w } = p;
  await settle();
  const header = w.document.querySelector('header');
  assert.equal(w.Alpine.$data(header).mobileOpen, false);
  w.document.getElementById('menu').click();
  await settle();
  assert.equal(w.Alpine.$data(header).mobileOpen, true);
  assert.equal(w.document.body.classList.contains('overflow-hidden'), true);
  w.document.getElementById('close').click();
  await settle();
  assert.equal(w.Alpine.$data(header).mobileOpen, false);
  assert.equal(w.document.body.classList.contains('overflow-hidden'), false);
  assert.equal(w.Alpine.$data(w.document.getElementById('stats')).current, 37);
}

test('standalone theme starts Alpine once and its controls work', async () => {
  const p = page(false);
  try {
    p.w.eval(theme);
    await assertControls(p);
    assert.equal(p.starts(), 1);
    assert.deepEqual(p.errors, []);
  } finally { p.dom.window.close(); }
});

test('theme loaded before Tutor registers on the Tutor owner before its DOM walk', async () => {
  const p = page(true);
  try {
    p.w.eval(theme);
    assert.equal(p.starts(), 0);
    p.w.eval(tutor);
    p.w.startTutor();
    await assertControls(p);
    p.w.document.querySelector('#tutor button').click();
    await settle();
    assert.equal(p.w.Alpine.$data(p.w.document.getElementById('tutor')).clicks, 1);
    assert.equal(p.starts(), 1);
    assert.deepEqual(p.errors, []);
  } finally { p.dom.window.close(); }
});

test('Tutor downloaded before theme keeps its owner and starts only once', async () => {
  const p = page(true);
  try {
    p.w.eval(tutor);
    const owner = p.w.Alpine;
    p.w.eval(theme);
    p.w.startTutor();
    await assertControls(p);
    assert.equal(p.w.Alpine, owner);
    assert.equal(p.starts(), 1);
    assert.deepEqual(p.errors, []);
  } finally { p.dom.window.close(); }
});

test('late theme repairs only its missing roots without restarting or resetting Tutor', async () => {
  const p = page(true);
  try {
    p.w.eval(tutor);
    p.w.startTutor();
    await settle();
    p.w.Alpine.$data(p.w.document.getElementById('tutor')).clicks = 4;
    p.errors.length = 0;
    p.w.eval(theme);
    await assertControls(p);
    assert.equal(p.w.Alpine.$data(p.w.document.getElementById('tutor')).clicks, 4);
    assert.equal(p.starts(), 1);
    assert.deepEqual(p.errors, []);
  } finally { p.dom.window.close(); }
});

 test('theme preserves plugins already installed by the Tutor owner', async () => {
  const p = page(true);
  try {
    p.w.eval(tutor);
    const before = [...p.w.pluginRegistrations];
    p.w.eval(theme);
    assert.deepEqual([...p.w.pluginRegistrations], before);
  } finally { p.dom.window.close(); }
});
