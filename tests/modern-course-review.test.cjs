'use strict';
const { test } = require('node:test');
const assert = require('node:assert/strict');
const { JSDOM } = require('jsdom');
const { buildSync } = require('esbuild');
const script = buildSync({ entryPoints: ['src/js/main.js'], bundle: true, write: false }).outputFiles[0].text;
const settle = () => new Promise(resolve => setTimeout(resolve, 40));
function fixture(rating = 4, review = 'Useful course') {
  const html = '<!doctype html><html lang="th"><body><div id="create-review-modal">' +
    '<form id="create-review-form" x-data="tutorForm({defaultValues:{comment_post_ID:249}})">' +
    '<input name="rating" value="' + rating + '"><textarea name="comment_content"></textarea>' +
    '<button type="submit">Save</button></form></div></body></html>';
  const dom = new JSDOM(html, { runScripts: 'outside-only', url: 'https://example.test/course/' });
  const form = dom.window.document.querySelector('form');
  form.querySelector('textarea').value = review;
  let closeCount = 0, clearFails = false, saveFails = false;
  const calls = [];
  dom.window.TutorCore = {
    endpoints: { PLACE_RATING: 'tutor_place_rating', CLEAR_REVIEW_POPUP_DATA: 'tutor_clear_review_popup_data' },
    api: { wpPost: async (action, payload) => {
      calls.push({ action, payload });
      if (action === 'tutor_place_rating' && saveFails) throw new Error('HTTP 403');
      if (action === 'tutor_clear_review_popup_data' && clearFails) throw new Error('HTTP 503');
      return { success: true, data: { message: 'Saved' } };
    } },
    modal: { closeModal: () => { closeCount++; } }, toast: { success: () => {} },
  };
  dom.window.console.warn = () => {};
  dom.window.eval(script);
  return {
    dom, form, calls, closeCount: () => closeCount,
    setSaveFails: v => { saveFails = v; }, setClearFails: v => { clearFails = v; },
    submit: () => form.dispatchEvent(new dom.window.Event('submit', { bubbles: true, cancelable: true })),
  };
}
test('completion review saves exactly once and closes even when cleanup fails', async () => {
  const p = fixture();
  try {
    p.setClearFails(true);
    p.submit();
    p.submit();
    await settle();
    assert.equal(p.calls.filter(c => c.action === 'tutor_place_rating').length, 1);
    assert.equal(p.calls[0].payload.course_id, 249);
    assert.equal(p.calls[0].payload.tutor_rating_gen_input, 4);
    assert.equal(p.calls[0].payload.review, 'Useful course');
    assert.equal(p.closeCount(), 1);
    assert.equal(p.dom.window.document.querySelector('#create-review-modal').hidden, true);
  } finally { p.dom.window.close(); }
});
test('failed save leaves modal open and shows server error', async () => {
  const p = fixture();
  try {
    p.setSaveFails(true);
    p.submit();
    await settle();
    assert.equal(p.closeCount(), 0);
    assert.match(p.form.querySelector('[role="alert"]').textContent, /403/);
    assert.equal(p.form.querySelector('textarea').value, 'Useful course');
    assert.equal(p.form.querySelector('button').disabled, false);
  } finally { p.dom.window.close(); }
});
test('invalid rating or review never sends request', async () => {
  const p = fixture(0, ' ');
  try {
    p.submit();
    await settle();
    assert.equal(p.calls.length, 0);
    assert.ok(p.form.querySelector('[role="alert"]'));
  } finally { p.dom.window.close(); }
});
test('legacy review form remains controlled by Tutor', async () => {
  const p = fixture();
  try {
    p.form.id = 'legacy';
    p.submit();
    await settle();
    assert.equal(p.calls.length, 0);
  } finally { p.dom.window.close(); }
});
