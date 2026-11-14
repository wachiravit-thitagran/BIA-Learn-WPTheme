const { test } = require('node:test');
const assert = require('node:assert/strict');
const { JSDOM } = require('jsdom');
const { buildSync } = require('esbuild');
const main = buildSync({ entryPoints: ['src/js/main.js'], bundle: true, write: false }).outputFiles[0].text;

test('Next is navigation and never records lesson completion', async () => {
  const dom = new JSDOM('<form><input name="tutor_action" value="tutor_complete_lesson"></form><a href="#next">Next</a>', { url: 'https://example.test/lesson', runScripts: 'outside-only' });
  let saves = 0;
  dom.window.fetch = async () => { saves++; return { ok: true, json: async () => ({ success: true, data: { lesson_completed: true } }) }; };
  dom.window.eval(main);
  await new Promise(resolve => setTimeout(resolve, 25));
  dom.window.document.querySelector('a').click();
  await new Promise(resolve => setTimeout(resolve, 25));
  assert.equal(saves, 0);
  assert.equal(dom.window.location.hash, '#next');
  dom.window.close();
});

test('empty course review is blocked inline before Tutor can show a blocking alert', async () => {
  const dom = new JSDOM('<form class="tutor-course-review-popup-form"><input name="tutor_rating_gen_input" value="0"><textarea name="review"></textarea><button class="tutor_submit_review_btn" type="submit">Update Review</button></form>', { runScripts: 'outside-only' });
  dom.window.eval(main);
  await new Promise(resolve => setTimeout(resolve, 25));
  let tutorHandler = 0;
  dom.window.document.addEventListener('click', () => { tutorHandler++; });
  dom.window.document.querySelector('button').click();
  assert.equal(tutorHandler, 0);
  assert.match(dom.window.document.querySelector('[role="alert"]')?.textContent || '', /rating|ดาว/i);
  dom.window.close();
});

test('valid course review reaches Tutor unchanged', async () => {
  const dom = new JSDOM('<form class="tutor-course-review-popup-form"><input name="tutor_rating_gen_input" value="5"><textarea name="review">Useful course</textarea><button class="tutor_submit_review_btn" type="button">Update Review</button></form>', { runScripts: 'outside-only' });
  dom.window.eval(main);
  await new Promise(resolve => setTimeout(resolve, 25));
  let tutorHandler = 0;
  dom.window.document.addEventListener('click', () => { tutorHandler++; });
  dom.window.document.querySelector('button').click();
  assert.equal(tutorHandler, 1);
  assert.equal(dom.window.document.querySelector('[role="alert"]'), null);
  dom.window.close();
});
