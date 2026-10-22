/*
 * BIA Quiz Experience — a presentational enhancement for Tutor LMS 4.1.x.
 * TutorCore owns all form values, validity, scoring, timer and submission.
 * No competing Alpine root or independent submission endpoint is created.
 */
(function () {
  'use strict';

  const thai = {
    intro: 'โปรดตรวจสอบจำนวนข้อ เวลาที่กำหนด และเกณฑ์ผ่านก่อนเริ่มทำแบบทดสอบ',
    reviewTitle: 'ตรวจทานก่อนส่งข้อสอบ',
    reviewDescription: 'คุณยังแก้ไขคำตอบได้ก่อนยืนยันส่งข้อสอบ',
    resultAttempt: 'ผลสอบครั้งนี้',
    answered: (done, total) => `ตอบแล้ว ${done} จาก ${total} ข้อ`,
    unanswered: (count) => `ยังไม่ได้ตอบ ${count} ข้อ (ข้อที่ไม่บังคับสามารถข้ามได้)`,
    answeredLabel: 'ตอบแล้ว',
    unansweredLabel: 'ยังไม่ตอบ',
    reviewUnavailable: 'โปรดตรวจทานคำตอบของคุณก่อนยืนยัน Tutor LMS จะตรวจสอบข้อบังคับอีกครั้ง',
    returnToQuiz: 'กลับไปตรวจคำตอบ',
    confirm: 'ยืนยันส่งข้อสอบ',
    jumpTo: (number) => `กลับไปข้อ ${number}`,
    reviewButton: 'ตรวจทานก่อนส่ง',
  };

  const english = {
    intro: 'Check the question count, time limit and passing score before you begin.',
    reviewTitle: 'Review before submitting',
    reviewDescription: 'You can still edit your answers before you confirm submission.',
    resultAttempt: 'This attempt',
    answered: (done, total) => `Answered ${done} of ${total} questions`,
    unanswered: (count) => `${count} unanswered (optional questions may be skipped)`,
    answeredLabel: 'Answered',
    unansweredLabel: 'Unanswered',
    reviewUnavailable: 'Review your answers before confirming. Tutor LMS will validate required questions.',
    returnToQuiz: 'Return to questions',
    confirm: 'Confirm submission',
    jumpTo: (number) => `Return to question ${number}`,
    reviewButton: 'Review answers',
  };

  function copyForLocale(locale) {
    return String(locale || '').toLowerCase().startsWith('th') ? thai : english;
  }

  function hasAttemptedValue(value) {
    if (value === null || value === undefined) return false;
    if (typeof value === 'string') return value.trim().length > 0;
    if (Array.isArray(value)) return value.some(hasAttemptedValue);
    if (typeof value === 'object') return Object.values(value).some(hasAttemptedValue);
    // A numeric zero is a legitimate choice or score input.
    return true;
  }

  // Mirrors Tutor's question-ID/value lookup; this is display-only and never
  // supplies answers to the Tutor submit handler.
  function reviewFromTutorValues(values) {
    if (!values || typeof values !== 'object') return null;
    const idsEntry = Object.entries(values).find(([key]) => key.includes('[quiz_question_ids]'));
    if (!idsEntry || !Array.isArray(idsEntry[1])) return null;

    const ids = idsEntry[1];
    const questions = ids.map((id) => {
      const needle = `[quiz_question][${id}]`;
      const answered = Object.entries(values).some(
        ([key, value]) => key.includes(needle) && hasAttemptedValue(value)
      );
      return { id: Number(id), answered };
    });
    const answered = questions.filter((question) => question.answered).length;

    return {
      total: questions.length,
      answered,
      unanswered: questions.length - answered,
      questions,
    };
  }

  function shouldInterceptManualSubmit(isTrusted, approved) {
    return Boolean(isTrusted) && !approved;
  }

  // Pure helpers are exported for Node regression tests; the browser runs the
  // same implementation without adding another runtime or Alpine instance.
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
      reviewFromTutorValues,
      copyForLocale,
      shouldInterceptManualSubmit,
    };
  }

  if (typeof document === 'undefined') return;

  const doc = document;
  const copy = copyForLocale(doc.documentElement.getAttribute('lang') || 'en');

  function el(tag, className, text) {
    const node = doc.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
  }

  function enhanceIntro() {
    const intro = doc.querySelector('.tutor-quiz-intro:not([data-bia-intro-ready])');
    if (!intro) return;

    const overview = intro.querySelector('.tutor-quiz-intro-overview');
    if (!overview) return;

    intro.dataset.biaIntroReady = '1';
    overview.appendChild(el('p', 'bia-quiz-intro-help', copy.intro));
  }

  function enhanceResults() {
    const summary = doc.querySelector('.tutor-quiz-summary-page:not([data-bia-results-ready])');
    if (!summary) return;
    const heading = summary.querySelector('.tutor-quiz-summary-header h5');
    if (!heading) return;

    summary.dataset.biaResultsReady = '1';
    heading.insertAdjacentElement('afterend', el('span', 'bia-quiz-attempt-label', copy.resultAttempt));
  }

  function tutorReviewState(form) {
    const tutor = window.TutorCore && window.TutorCore.form;
    if (!tutor || !form.id || typeof tutor.hasForm !== 'function' ||
        typeof tutor.getFormState !== 'function' || !tutor.hasForm(form.id)) {
      return null;
    }
    const state = tutor.getFormState(form.id);
    return reviewFromTutorValues(state && state.values);
  }

  function addReviewItem(list, question, index, form, dialog) {
    const item = el('li', 'bia-quiz-review-item');
    const marker = el('span',
      question.answered ? 'bia-quiz-review-marker is-answered' : 'bia-quiz-review-marker is-unanswered',
      question.answered ? '✓' : '–'
    );
    marker.setAttribute('aria-hidden', 'true');

    const text = el('span', 'bia-quiz-review-text',
      `${index + 1}. ${question.answered ? copy.answeredLabel : copy.unansweredLabel}`
    );

    const nav = form.querySelector(
      `.tutor-quiz-questions-pagination button[data-quiz-question-index="${index + 1}"]`
    );
    const wrapper = form.querySelector(
      `.tutor-quiz-question-wrapper[data-quiz-question-index="${index + 1}"]`
    );

    if (nav || (form.dataset.questionLayoutView === 'question_below_each_other' && wrapper)) {
      const button = el('button', 'bia-quiz-review-jump', '');
      button.type = 'button';
      button.setAttribute('aria-label', copy.jumpTo(index + 1));
      button.append(marker, text);
      button.addEventListener('click', () => {
        dialog.close();
        if (nav) nav.click(); // Use Tutor's own goTo() and question state.
        else if (wrapper) wrapper.scrollIntoView({ behavior: 'smooth', block: 'center' });
      });
      item.appendChild(button);
    } else {
      item.append(marker, text);
    }
    list.appendChild(item);
  }

  function enhanceAttempt() {
    const form = doc.querySelector('form.tutor-quiz-submission:not([data-bia-quiz-ready])');
    if (!form) return;
    form.dataset.biaQuizReady = '1';

    // Informative label for Tutor's existing answer-progress bar; never
    // create a second progress calculation or start another timer.
    const progressWrapper = form.querySelector('.tutor-quiz-progress-bar-wrapper');
    const progressLabel = progressWrapper ? el('span', 'bia-quiz-answered-count') : null;
    if (progressWrapper && progressLabel) {
      progressLabel.setAttribute('aria-live', 'polite');
      progressWrapper.appendChild(progressLabel);
    }

    function updateCount() {
      if (!progressLabel) return;
      const state = tutorReviewState(form);
      if (!state) {
        progressLabel.hidden = true;
        return;
      }
      const next = copy.answered(state.answered, state.total);
      if (progressLabel.textContent !== next) progressLabel.textContent = next;
      progressLabel.hidden = false;
    }

    form.addEventListener('input', updateCount);
    form.addEventListener('change', updateCount);
    window.requestAnimationFrame(updateCount);
    window.setTimeout(updateCount, 350);

    // Native Tutor forms remain fully functional if dialog support is absent.
    const dialogSupported = typeof HTMLDialogElement !== 'undefined' &&
      typeof HTMLDialogElement.prototype.showModal === 'function';
    if (!dialogSupported) return;

    const dialog = el('dialog', 'bia-quiz-review-dialog');
    const card = el('div', 'bia-quiz-review-card');
    const title = el('h2', 'bia-quiz-review-title', copy.reviewTitle);
    const titleId = 'bia-quiz-review-title-' + String(form.id || 'quiz').replace(/[^a-zA-Z0-9-]/g, '');
    title.id = titleId;
    dialog.setAttribute('aria-labelledby', titleId);
    const description = el('p', 'bia-quiz-review-description', copy.reviewDescription);
    const stats = el('p', 'bia-quiz-review-stats');
    const warning = el('p', 'bia-quiz-review-warning');
    warning.setAttribute('role', 'status');
    const items = el('ol', 'bia-quiz-review-list');
    const actions = el('div', 'bia-quiz-review-actions');
    const back = el('button', 'bia-quiz-review-back', copy.returnToQuiz);
    back.type = 'button';
    back.addEventListener('click', () => dialog.close());
    const confirm = el('button', 'bia-quiz-review-confirm', copy.confirm);
    confirm.type = 'button';
    actions.append(back, confirm);
    card.append(title, description, stats, warning, items, actions);
    dialog.appendChild(card);
    doc.body.appendChild(dialog);

    let selectedSubmitter = null;
    let approvedSubmit = false;

    function openReview(submitter) {
      selectedSubmitter = submitter;
      const state = tutorReviewState(form);
      items.replaceChildren();

      if (state) {
        stats.textContent = copy.answered(state.answered, state.total);
        warning.textContent = state.unanswered ? copy.unanswered(state.unanswered) : '';
        state.questions.forEach((question, index) => addReviewItem(items, question, index, form, dialog));
      } else {
        stats.textContent = '';
        warning.textContent = copy.reviewUnavailable;
      }
      updateCount();
      dialog.showModal();
    }

    confirm.addEventListener('click', () => {
      dialog.close();
      const submitter = selectedSubmitter && selectedSubmitter.isConnected && !selectedSubmitter.disabled
        ? selectedSubmitter : form.querySelector('.tutor-quiz-footer button[type="submit"]:not([disabled])');
      selectedSubmitter = null;
      if (!submitter) return;
      // The actual submit/correctness/timeout flow is Tutor's; only the
      // user-triggered first click is intercepted for review.
      approvedSubmit = true;
      try {
        submitter.click();
      } finally {
        approvedSubmit = false;
      }
    });

    form.addEventListener('click', (event) => {
      const target = event.target;
      const submitter = target && typeof target.closest === 'function'
        ? target.closest('.tutor-quiz-footer button[type="submit"], .tutor-quiz-footer input[type="submit"]')
        : null;
      if (!submitter || !shouldInterceptManualSubmit(event.isTrusted, approvedSubmit) || submitter.disabled) {
        return;
      }
      event.preventDefault();
      event.stopImmediatePropagation();
      openReview(submitter);
    }, true);

    // Keyboard/form submissions that did not originate from the submit button
    // should also get a review prompt. Tutor's own timed auto-submit bypasses
    // native submit events and therefore cannot be blocked by this listener.
    form.addEventListener('submit', (event) => {
      if (!shouldInterceptManualSubmit(event.isTrusted, approvedSubmit)) return;
      const submitter = event.submitter || form.querySelector('.tutor-quiz-footer button[type="submit"]');
      if (!submitter || submitter.disabled) return;
      event.preventDefault();
      event.stopImmediatePropagation();
      openReview(submitter);
    }, true);

    // Tutor alone decides timeout/auto-submit. Close our visual layer as
    // soon as its native timer expires or its attempt is completed.
    function closeWhenTutorFinishes(event) {
      const detail = event && event.detail;
      if (detail && detail.formId && detail.formId !== form.id) return;
      if (dialog.open) dialog.close();
    }
    doc.addEventListener('tutor-quiz-time-expired', closeWhenTutorFinishes);
    doc.addEventListener('tutor-quiz-attempt-completed', closeWhenTutorFinishes);

    // Escape and backdrop dismissal must never trigger a submission.
    dialog.addEventListener('click', (event) => {
      if (event.target === dialog) dialog.close();
    });
  }

  function boot() {
    enhanceIntro();
    enhanceAttempt();
    enhanceResults();
  }

  if (doc.readyState === 'loading') {
    doc.addEventListener('DOMContentLoaded', boot, { once: true });
  } else {
    boot();
  }
})();
