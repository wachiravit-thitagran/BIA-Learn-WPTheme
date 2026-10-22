'use strict';

const { test } = require('node:test');
const assert = require('node:assert/strict');

const {
  reviewFromTutorValues,
  copyForLocale,
  shouldInterceptManualSubmit,
} = require('../assets/js/bia-quiz-ux.js');

test('review counts Tutor answer values without mistaking question IDs', () => {
  const values = {
    'attempt[7][quiz_question_ids][]': [10, 20, 30],
    'attempt[7][quiz_question][10]': 'yes',
    'attempt[7][quiz_question][20][]': [],
    'attempt[7][quiz_question][300]': 'unrelated',
  };
  assert.deepEqual(reviewFromTutorValues(values), {
    total: 3,
    answered: 1,
    unanswered: 2,
    questions: [
      { id: 10, answered: true },
      { id: 20, answered: false },
      { id: 30, answered: false },
    ],
  });
});

test('multiple choices and textual answers count only actual values', () => {
  const values = {
    'attempt[1][quiz_question_ids][]': [1, 2, 3, 4],
    'attempt[1][quiz_question][1][]': ['a', 'b'],
    'attempt[1][quiz_question][2]': '  ',
    'attempt[1][quiz_question][3]': 0,
    'attempt[1][quiz_question][4]': null,
  };
  const review = reviewFromTutorValues(values);
  assert.equal(review.total, 4);
  assert.equal(review.answered, 2);
  assert.deepEqual(review.questions.map(q => q.answered), [true, false, true, false]);
});

test('missing Tutor state does not claim every question unanswered', () => {
  assert.equal(reviewFromTutorValues(null), null);
  assert.equal(reviewFromTutorValues({}), null);
  assert.equal(reviewFromTutorValues({ other: 'value' }), null);
});

test('review localized copy uses Thai for Thai and English for other locales', () => {
  const th = copyForLocale('th-TH');
  assert.equal(th.reviewTitle, 'ตรวจทานก่อนส่งข้อสอบ');
  assert.equal(th.resultAttempt, 'ผลสอบครั้งนี้');
  assert.equal(th.answered(2, 3), 'ตอบแล้ว 2 จาก 3 ข้อ');
  const en = copyForLocale('en-US');
  assert.equal(en.reviewTitle, 'Review before submitting');
  assert.equal(en.answered(2, 3), 'Answered 2 of 3 questions');
  assert.equal(copyForLocale('fr').reviewTitle, en.reviewTitle);
});

test('only real manual submission prompts; automatic Tutor actions bypass', () => {
  assert.equal(shouldInterceptManualSubmit(true, false), true);
  assert.equal(shouldInterceptManualSubmit(false, false), false);
  assert.equal(shouldInterceptManualSubmit(true, true), false);
});
