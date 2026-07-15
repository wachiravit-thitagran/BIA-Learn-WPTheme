/**
 * BIA Learn — front-end JavaScript
 * Alpine.js powers small interactions (mobile menu, dropdowns, accordion,
 * tabs, count-up stats). Everything else stays server-rendered.
 */
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';

Alpine.plugin(collapse);
Alpine.plugin(focus);

/* ---------------------------------------------------------------------------
 * Reusable Alpine data components
 * ------------------------------------------------------------------------- */

// Count-up animation for the statistics strip. Honors reduced-motion.
Alpine.data('countUp', (target = 0, duration = 1600) => ({
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
Alpine.data('siteHeader', () => ({
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

// Tutor LMS 4.x bundles and starts its OWN Alpine instance on its pages
// (dashboard, courses, lessons, auth). Running a second Alpine instance on the
// same page breaks reactivity across the whole document — Tutor's dashboard
// popovers stop working and the theme's own x-show/x-data (e.g. the auth tabs)
// misbehave, and the console fills with "slides/currentSlide/handleClickOutside
// is not defined" as our Alpine tries to evaluate Tutor's component expressions.
// So only bootstrap our Alpine when the page has none; otherwise leave Tutor's
// instance to run cleanly.
// Tutor bundles its Alpine and defers the DOM walk, and it does not set
// window.Alpine, so we can't reliably detect it at this instant via window.Alpine
// or _x_dataStack. Instead detect it synchronously from the server-rendered DOM:
// every Tutor Alpine root uses an x-data that references a "tutor…" component
// (tutorHeader, tutorPopover, tutorTour, tutorModal, tutorCourseCompletionChart…).
// If any are present, Tutor owns Alpine on this page — do not start a second one.
const otherAlpineOwnsPage =
  !!window.Alpine ||
  !!document.querySelector('[x-data*="tutor"]') ||
  Array.prototype.some.call(
    document.querySelectorAll('[x-data]'),
    (el) => !!el._x_dataStack
  );

if (!otherAlpineOwnsPage) {
  window.Alpine = Alpine;
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

  const completeAndNavigate = (href) => {
    const formData = new FormData(completeForm);
    fetch(window.location.href, {
      method: 'POST',
      body: formData,
      keepalive: true
    }).finally(() => {
      if (href) {
        window.location.href = href;
      } else {
        completeForm.submit();
      }
    });
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
