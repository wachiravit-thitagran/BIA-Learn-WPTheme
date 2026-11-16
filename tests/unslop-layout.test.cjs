const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const read = (file) => fs.readFileSync(path.join(__dirname, '..', file), 'utf8');

test('homepage keeps functional course search, customizer copy and real metrics', () => {
  const hero = read('template-parts/home/hero.php');
  assert.match(hero, /bia_hero_title/);
  assert.match(hero, /bia_hero_cta_url/);
  assert.match(hero, /name="post_type" value="courses"/);
  assert.match(hero, /name="s" required/);
  assert.match(hero, /bia_learn_get_stats/);
  assert.match(hero, /bg-plum-900/);
  assert.doesNotMatch(hero, /blur-3xl|backdrop-blur|bg-plum-wash|bg-crimson\/20/);
});

test('category and learning steps show real content without decorative icon tiles', () => {
  const categories = read('template-parts/home/categories.php');
  const steps = read('template-parts/home/how-it-works.php');
  assert.match(categories, /get_terms/);
  assert.match(categories, /get_term_link/);
  assert.match(categories, /aria-label=.*หมวดหมู่คอร์สเรียน/);
  assert.doesNotMatch(categories, /bg-grain|bg-crimson-50|group-hover:scale/);
  assert.match(steps, /<ol /);
  assert.match(steps, /<li>/);
  assert.doesNotMatch(steps, /bg-gradient|bg-crimson-wash|border-dashed/);
});

test('metrics are semantic and use WordPress data rather than colorful stat cards', () => {
  const stats = read('template-parts/home/stats.php');
  assert.match(stats, /<dl /);
  assert.match(stats, /<dt /);
  assert.match(stats, /<dd /);
  assert.match(stats, /bia_learn_get_stats/);
  assert.doesNotMatch(stats, /icon-chip-(?:success|warning|info)|stat-card/);
});

test('course cards keep prerequisite locks, pricing, progress and course links', () => {
  const card = read('template-parts/cards/course-card.php');
  for (const marker of ['bia-episode-course-card', 'bia-course-top-badges', 'bia-episode-lock', '$price_html', '$progress', 'get_permalink']) {
    assert.ok(card.includes(marker), 'Missing course-card behavior marker: ' + marker);
  }
  assert.doesNotMatch(card, /group-hover:scale-105|backdrop-blur/);
});

test('shared component transitions avoid blanket motion and hover lifts', () => {
  const css = read('src/css/main.css');
  const block = css.slice(css.indexOf('/* Eyebrow / kicker'), css.indexOf('/* --- Dashboard kit'));
  assert.ok(block.length > 100);
  assert.doesNotMatch(block, /transition-all|hover:-translate-y-1|uppercase tracking-/);
  assert.match(block, /transition-colors/);
});
