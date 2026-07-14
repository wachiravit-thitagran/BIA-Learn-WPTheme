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
const alpineAlreadyRunning =
  !!window.Alpine ||
  Array.prototype.some.call(
    document.querySelectorAll('[x-data]'),
    (el) => !!el._x_dataStack
  );

if (!alpineAlreadyRunning) {
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

  const nextBtn = document.querySelector('.tutor-next-link');

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

  // 2. Video Lessons: Listen to Video Ended Events
  const checkVideo = setInterval(() => {
    let videoFound = false;

    // Check Plyr instances from Tutor LMS
    if (window.tutor_plyr && Array.isArray(window.tutor_plyr) && window.tutor_plyr.length > 0) {
      window.tutor_plyr.forEach(player => {
        player.on('ended', () => {
          completeForm.submit(); // Normal submit will reload and show checkmark
        });
      });
      videoFound = true;
    }

    // Check native video tags
    const videoEls = document.querySelectorAll('video');
    if (videoEls.length > 0) {
      videoEls.forEach(vid => {
        vid.addEventListener('ended', () => {
          completeForm.submit();
        });
      });
      videoFound = true;
    }

    if (videoFound) {
      clearInterval(checkVideo);
    }
  }, 1000);
  
  // Stop checking for video after 10 seconds
  setTimeout(() => clearInterval(checkVideo), 10000);
});
