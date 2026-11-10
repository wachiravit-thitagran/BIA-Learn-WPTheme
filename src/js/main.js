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

/* ---------------------------------------------------------------------------
 * Tutor LMS Auto-complete Lessons
 * ------------------------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
  // Find the complete lesson form
  const completeForm = document.querySelector('form input[value="tutor_complete_lesson"]')?.closest('form');
  if (!completeForm) return;

  // Tutor 4.0 renders the lesson footer nav as:
  //   [Previous <a>] [complete <form>] [Next <a>]
  // The "Next" control is the anchor that follows the complete form. Older Tutor
  // used a `.tutor-next-link` class that no longer exists in 4.0, which is why
  // clicking "Next" stopped auto-completing the lesson. Find it structurally,
  // with fallbacks for the legacy class and other layouts.
  let nextBtn = null;
  for (let sib = completeForm.nextElementSibling; sib; sib = sib.nextElementSibling) {
    if (sib.tagName === 'A' && sib.getAttribute('href')) {
      nextBtn = sib;
      break;
    }
  }
  if (!nextBtn) {
    nextBtn = document.querySelector('.tutor-next-link');
  }
  if (!nextBtn) {
    const footer = completeForm.closest('.tutor-learning-area-footer');
    const anchors = footer ? footer.querySelectorAll('a[href]') : [];
    if (anchors.length) {
      nextBtn = anchors[anchors.length - 1];
    }
  }

  let completing = false;
  const completeAndNavigate = async (href) => {
    if (completing) return;
    completing = true;
    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), 15000);
    let errorBox = completeForm.querySelector('[data-bia-completion-error]');
    if (errorBox) errorBox.remove();
    completeForm.setAttribute('aria-busy', 'true');
    const formData = new FormData(completeForm);
    formData.set('bia_complete_and_next', '1');
    try {
      const response = await fetch(window.location.href, {
        method: 'POST', body: formData, credentials: 'same-origin', signal: controller.signal
      });
      if (!response.ok) throw new Error('Completion rejected');
      const result = await response.json();
      if (result.success !== true || result.data?.lesson_completed !== true) {
        throw new Error('Completion not confirmed');
      }
      if (href) window.location.assign(href);
    } catch (error) {
      errorBox = document.createElement('p');
      errorBox.dataset.biaCompletionError = '1';
      errorBox.setAttribute('role', 'alert');
      errorBox.textContent = document.documentElement.lang.startsWith('th')
        ? 'บันทึกการเรียนไม่สำเร็จ กรุณาลองอีกครั้ง' : 'Could not save lesson completion. Please try again.';
      completeForm.appendChild(errorBox);
    } finally {
      window.clearTimeout(timeout);
      completing = false;
      completeForm.removeAttribute('aria-busy');
    }
  };

  // 1. Text Lessons: Intercept "Next" button click
  if (nextBtn) {
    nextBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const href = nextBtn.getAttribute('href');
      completeAndNavigate(href);
    });
  }

  // 2. Video lessons: auto-complete when the video finishes.
  // Tutor 4.0 wraps videos in a Plyr player (YouTube/Vimeo/HTML5) and no longer
  // exposes window.tutor_plyr. Instead of relying on Plyr instance internals, we
  // watch the Plyr seek control, which reports current time (aria-valuenow) and
  // duration (aria-valuemax) as the video plays; when playback reaches the end we
  // submit the lesson's complete form once. Native <video> elements are handled
  // via their "ended" event. This also completes the final lesson, whose "Next"
  // button is hidden.
  const submitComplete = () => {
    const btn = completeForm.querySelector('[name="complete_lesson_btn"]');
    if (typeof completeForm.requestSubmit === 'function') {
      completeForm.requestSubmit(btn || undefined);
    } else {
      completeForm.submit();
    }
  };

  // Skip if this lesson is already completed.
  let alreadyCompleted = false;
  try {
    const trackingEl = document.getElementById('tutor_video_tracking_information');
    if (trackingEl) {
      alreadyCompleted = !!JSON.parse(trackingEl.value || '{}').lesson_completed;
    }
  } catch (e) {}

  if (!alreadyCompleted) {
    let videoDone = false;
    const finish = () => {
      if (videoDone) return;
      videoDone = true;
      submitComplete();
    };

    // Plyr player (YouTube/Vimeo/HTML5): poll the seek control for end-of-video.
    const seek = document.querySelector(
      '.tutor-lesson-video-wrapper input[data-plyr="seek"], .tutor-video-player input[data-plyr="seek"]'
    );
    if (seek) {
      const poll = setInterval(() => {
        if (videoDone) {
          clearInterval(poll);
          return;
        }
        const now = parseFloat(seek.getAttribute('aria-valuenow'));
        const max = parseFloat(seek.getAttribute('aria-valuemax'));
        if (max > 5 && now >= 0 && now / max >= 0.97) {
          clearInterval(poll);
          finish();
        }
      }, 2000);
    }

    // Native HTML5 video fallback.
    document
      .querySelectorAll('.tutor-lesson-video-wrapper video, .tutor-video-player video')
      .forEach((vid) => vid.addEventListener('ended', finish));
  }
});

// Tutor owns accordion expansion; reflect its class changes for assistive tech.
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('button.tutor-accordion-item-header').forEach(button => {
    const sync = () => button.setAttribute('aria-expanded', String(button.classList.contains('is-active')));
    sync();
    new MutationObserver(sync).observe(button, { attributes: true, attributeFilter: ['class'] });
  });
});
