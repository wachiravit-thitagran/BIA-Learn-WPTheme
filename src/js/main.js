/**
 * BIA Learn — front-end JavaScript
 * Alpine.js powers small interactions (mobile menu, dropdowns, accordion,
 * tabs, count-up stats). Everything else stays server-rendered.
 */
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

Alpine.plugin(collapse);

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

window.Alpine = Alpine;
Alpine.start();
