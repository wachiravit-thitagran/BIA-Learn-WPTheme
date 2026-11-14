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
