/**
 * BIA Learn — front-end JavaScript
 * Alpine.js powers small interactions (mobile menu, dropdowns, accordion,
 * tabs, count-up stats). Everything else stays server-rendered.
 */
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';

const registeredInstances = new WeakSet();

// Components must belong to the instance that walks the page. Tutor exposes its
// own Alpine before alpine:init; registering on our unused import loses them.
function registerThemeComponents(alpine) {
  if (registeredInstances.has(alpine)) return;
  registeredInstances.add(alpine);
  // External owners (including Tutor) manage their own plugin lifecycle.
  if (alpine === Alpine) {
    alpine.plugin(collapse);
    alpine.plugin(focus);
  }

  /* ---------------------------------------------------------------------------
   * Reusable Alpine data components
   * ------------------------------------------------------------------------- */

  // Count-up animation for the statistics strip. Honors reduced-motion.
  alpine.data('countUp', (target = 0, duration = 1600) => ({
    current: 0,
    started: false,
    init() {
      const prefersReduced = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
      ).matches;

      if (prefersReduced) {
        this.current = target;
        return;
      }

      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting && !this.started) {
              this.started = true;
              this.run(duration);
              observer.disconnect();
            }
          });
        },
        { threshold: 0.4 }
      );
      observer.observe(this.$el);
    },
    run(duration) {
      const start = performance.now();
      const step = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        // easeOutExpo
        const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
        this.current = Math.round(eased * target);
        if (progress < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    },
    get display() {
      return this.current.toLocaleString('th-TH');
    },
  }));

  // Header that gains a shadow / solid background once the user scrolls.
  alpine.data('siteHeader', () => ({
    scrolled: false,
    mobileOpen: false,
    init() {
      this.onScroll();
      window.addEventListener('scroll', () => this.onScroll(), { passive: true });
    },
    onScroll() {
      this.scrolled = window.scrollY > 24;
    },
    toggleMobile() {
      this.mobileOpen = !this.mobileOpen;
      document.body.classList.toggle('overflow-hidden', this.mobileOpen);
    },
    closeMobile() {
      this.mobileOpen = false;
      document.body.classList.remove('overflow-hidden');
    },
  }));
}

// If the theme arrives after an owner's initial walk, only repair theme roots
// that failed to acquire their data. Never restart Alpine or reset Tutor state.
function repairThemeRoots(alpine) {
  document.querySelectorAll('[x-data]').forEach((root) => {
    const expression = root.getAttribute('x-data').trim();
    const key = expression === 'siteHeader' ? 'mobileOpen'
      : expression.startsWith('countUp(') ? 'current' : null;
    const state = root._x_dataStack && root._x_dataStack[0];
    if (!key || !state || key in state) return;
    alpine.destroyTree(root);
    alpine.initTree(root);
  });
}

document.addEventListener('alpine:init', () => {
  registerThemeComponents(window.Alpine || Alpine);
});

const otherAlpineOwnsPage =
  !!window.Alpine ||
  !!document.querySelector('[x-data*="tutor"]') ||
  Array.prototype.some.call(
    document.querySelectorAll('[x-data]'),
    (el) => !!el._x_dataStack
  );

if (window.Alpine) {
  registerThemeComponents(window.Alpine);
  repairThemeRoots(window.Alpine);
} else if (!otherAlpineOwnsPage) {
  window.Alpine = Alpine;
  registerThemeComponents(Alpine);
  Alpine.start();
}

// Lesson completion belongs to Tutor LMS. Navigation and seeking a video must
// never mark a lesson complete; Tutor applies the configured completion rules.

// Tutor 4.1's legacy review handler uses alert() for missing stars/content.
// Validate before that delegated handler; valid reviews still use Tutor's save.
function validateCourseReview(event) {
  const target = event.target;
  const form = target.closest?.('form.tutor-course-review-popup-form');
  if (!form || (event.type === 'click' && !target.closest('.tutor_submit_review_btn'))) return;
  const rating = Number(form.querySelector('[name="tutor_rating_gen_input"]')?.value);
  const review = form.querySelector('[name="review"]');
  const oldError = form.querySelector('[data-bia-review-error]');
  if (oldError) oldError.remove();
  if (rating >= 1 && rating <= 5 && review?.value.trim()) return;
  event.preventDefault();
  event.stopImmediatePropagation();
  const error = document.createElement('p');
  error.dataset.biaReviewError = '1';
  error.setAttribute('role', 'alert');
  error.tabIndex = -1;
  error.textContent = document.documentElement.lang.startsWith('th')
    ? 'กรุณาเลือกดาวและเขียนรีวิวก่อนส่ง แล้วลองอีกครั้ง'
    : 'Please select a rating and write a review before submitting.';
  form.appendChild(error);
  error.focus();
}
document.addEventListener('click', validateCourseReview, true);
document.addEventListener('submit', validateCourseReview, true);

// Tutor owns accordion expansion; reflect its class changes for assistive tech.
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('button.tutor-accordion-item-header').forEach(button => {
    const sync = () => button.setAttribute('aria-expanded', String(button.classList.contains('is-active')));
    sync();
    new MutationObserver(sync).observe(button, { attributes: true, attributeFilter: ['class'] });
  });
});
// Tutor LMS 4.2 completion modal uses a different form from its legacy
// single-course popup. Keep its save path usable even when Tutor's Alpine
// submit handler fails to initialize, and never block closing on cleanup.
function reviewModalMessage(form, message) {
  let note = form.querySelector('[data-bia-completion-review-message]');
  if (!note) {
    note = document.createElement('p');
    note.dataset.biaCompletionReviewMessage = '1';
    note.setAttribute('role', 'alert');
    note.tabIndex = -1;
    form.appendChild(note);
  }
  note.textContent = message;
  note.focus();
}

const completionReviewThai = () => document.documentElement.lang.startsWith('th');

// Synchronize Tutor's visual rating buttons with the actual form field. This
// also works if Alpine didn't initialize its click handlers after a course
// completion redirect.
document.addEventListener('click', (event) => {
  const button = event.target.closest?.(
    '#create-review-modal .tutor-star-rating-emoji-btn, #create-review-modal .tutor-star-rating-icon-btn'
  );
  if (!button) return;
  const form = button.closest('form#create-review-form');
  const ratingField = form?.querySelector('input[name="rating"]');
  if (!ratingField) return;
  const peers = Array.from(button.parentElement.querySelectorAll(
    button.classList.contains('tutor-star-rating-emoji-btn')
      ? '.tutor-star-rating-emoji-btn' : '.tutor-star-rating-icon-btn'
  ));
  const rating = peers.indexOf(button) + 1;
  if (rating < 1 || rating > 5) return;
  ratingField.value = String(rating);
  ratingField.dispatchEvent(new Event('input', { bubbles: true }));
}, true);

document.addEventListener('submit', async (event) => {
  const form = event.target;
  if (!(form instanceof HTMLFormElement) ||
    form.id !== 'create-review-form' || !form.closest('#create-review-modal')) return;

  // Own ONLY the completion-modal form. Native Tutor still owns legacy reviews
  // and inline review edits. The capture listener prevents duplicate POSTs.
  event.preventDefault();
  event.stopImmediatePropagation();
  if (form.dataset.biaReviewSaving === '1') return;

  let state = {};
  try {
    state = window.Alpine?.$data?.(form)?.values || {};
  } catch (error) {
    // The uninitialized form is handled via its server-rendered fields below.
  }
  const rating = Number(form.querySelector('[name="rating"]')?.value || state.rating);
  const review = String(form.querySelector('[name="comment_content"]')?.value ?? state.comment_content ?? '').trim();
  const source = form.getAttribute('x-data') || '';
  const courseId = Number(state.comment_post_ID || source.match(/comment_post_ID\s*:\s*(\d+)/)?.[1]);
  const client = window.TutorCore;

  if (!Number.isInteger(rating) || rating < 1 || rating > 5 || !review) {
    reviewModalMessage(form, completionReviewThai()
      ? 'กรุณาเลือกคะแนน 1–5 ดาวและเขียนรีวิวก่อนบันทึก'
      : 'Please choose 1–5 stars and write a review.');
    return;
  }
  if (!courseId || typeof client?.api?.wpPost !== 'function' || !client?.endpoints?.PLACE_RATING) {
    reviewModalMessage(form, completionReviewThai()
      ? 'ระบบรีวิวไม่พร้อมใช้งาน กรุณารีโหลดหน้าแล้วลองใหม่'
      : 'The review service is not ready. Please reload and try again.');
    return;
  }

  form.querySelector('[data-bia-completion-review-message]')?.remove();
  const submitButton = form.querySelector('button[type="submit"]');
  form.dataset.biaReviewSaving = '1';
  if (submitButton) {
    submitButton.disabled = true;
    submitButton.classList.add('tutor-btn-loading');
  }

  try {
    const result = await client.api.wpPost(client.endpoints.PLACE_RATING, {
      course_id: courseId,
      tutor_rating_gen_input: rating,
      review,
    });
    // An HTTP 200 with body "0" or invalid JSON is not a successful review.
    if (result?.success !== true) {
      throw new Error(result?.data?.message || 'The server did not confirm saving the review.');
    }

    // Close immediately after the save is confirmed. Clearing Tutor's
    // completion-popup flag is best-effort and must not hold the modal open.
    const modal = form.closest('#create-review-modal');
    try {
      client.modal?.closeModal?.('create-review-modal');
    } catch (error) {
      console.warn('Tutor review modal close failed:', error);
    }
    if (modal && !modal.hidden && modal.getAttribute('aria-hidden') !== 'true') {
      modal.hidden = true;
      modal.setAttribute('aria-hidden', 'true');
      document.body.style.removeProperty('overflow');
    }
    try {
      client.toast?.success?.(result.data?.message || (completionReviewThai()
        ? 'บันทึกรีวิวเรียบร้อยแล้ว' : 'Your review has been saved.'));
    } catch (error) {
      console.warn('Tutor review toast failed:', error);
    }

    // Tutor may also clear this flag from the modal-closed event. Duplicate
    // clearing is harmless; a failed clear must not be reported as save failed.
    try {
      if (client.endpoints.CLEAR_REVIEW_POPUP_DATA) {
        await client.api.wpPost(client.endpoints.CLEAR_REVIEW_POPUP_DATA, { course_id: courseId });
      }
      window.location.reload();
    } catch (error) {
      console.warn('Course review saved, but popup cleanup failed:', error);
    }
  } catch (error) {
    const detail = error instanceof Error ? error.message : String(error);
    reviewModalMessage(form, (completionReviewThai()
      ? 'ไม่สามารถบันทึกรีวิวได้: ' : 'Review could not be saved: ') + detail);
  } finally {
    form.dataset.biaReviewSaving = '0';
    if (submitButton) {
      submitButton.disabled = false;
      submitButton.classList.remove('tutor-btn-loading');
    }
  }
}, true);
