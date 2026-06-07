/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './**/*.php',
    './src/js/**/*.js',
    './tutor/**/*.php',
    '!./node_modules/**',
    '!./vendor/**',
  ],
  // Safelist classes that may be injected dynamically by Tutor LMS / WP and
  // therefore not present in the static .php source scanned above.
  safelist: [
    'is-active',
    'screen-reader-text',
    { pattern: /^(bg|text|border)-(crimson|plum|paper|gold)(-(50|100|200|300|400|500|600|700|800|900))?$/ },
    { pattern: /^(bg|text|border)-(success|warning|danger|info)(-(light|dark))?$/ },
    // Dashboard kit component classes — kept available even before they are
    // referenced in templates (see "Dashboard kit" in src/css/main.css).
    'pill', 'pill-success', 'pill-warning', 'pill-danger', 'pill-info', 'pill-crimson',
    'stat-card', 'stat-card__num', 'stat-card__label',
    'icon-chip', 'icon-chip-crimson', 'icon-chip-success', 'icon-chip-warning', 'icon-chip-info',
    'progress', 'progress__bar', 'progress__bar--success', 'progress__bar--warning', 'progress__bar--info',
    'dashboard-hero', 'dashboard-hero__title', 'dashboard-hero__subtitle',
  ],
  theme: {
    container: {
      center: true,
      padding: {
        DEFAULT: '1.25rem',
        sm: '1.5rem',
        lg: '2rem',
      },
      screens: {
        '2xl': '1200px',
      },
    },
    extend: {
      colors: {
        // Primary — crimson / "ครั่ง" (sampled live from bia.psu.ac.th:
        // #9d1c2b base, #7c2021 hover/active, soft pink wash #f2e0e0)
        crimson: {
          50: '#fbf2f3',
          100: '#f9e7e7',
          200: '#f2e0e0',
          300: '#e3a9ae',
          400: '#cf5d6a',
          500: '#b73544',
          DEFAULT: '#9d1c2b',
          600: '#7c2021',
          700: '#6b1414',
          800: '#5a1118',
          900: '#430d12',
        },
        // Dark slate-plum — header overlay / footer (bia: #2f2b3d, #444050)
        plum: {
          DEFAULT: '#2f2b3d',
          700: '#444050',
          800: '#27232f',
          900: '#1c1925',
        },
        // Cool "paper" neutrals — sampled from bia.psu.ac.th
        // (bg #f8f7fa, borders #dfdfe3, muted text #6d6b77)
        paper: {
          50: '#f8f7fa',
          100: '#f0f0f4',
          200: '#dfdfe3',
          300: '#c9c8d0',
          400: '#a8a6b3',
          500: '#6d6b77',
          600: '#56545f',
        },
        // Body text ink (cool slate)
        ink: {
          DEFAULT: '#2f2b3d',
          soft: '#444050',
          light: '#6d6b77',
        },
        // Accent — manuscript gold (retained, used sparingly)
        gold: {
          DEFAULT: '#b8862b',
          light: '#d4a94e',
          dark: '#8a6420',
        },
        // Semantic status colors — sampled from the bia.psu.ac.th user
        // dashboard (/user/). Used for stat cards, status pills, progress
        // bars, alerts and Tutor LMS quiz/result states.
        success: { DEFAULT: '#28c76f', light: '#e3fcef', dark: '#00875a' },
        warning: { DEFAULT: '#ff9f43', light: '#fff0e1', dark: '#b3701e' },
        danger:  { DEFAULT: '#ff4c51', light: '#ffe2e3', dark: '#b3353a' },
        info:    { DEFAULT: '#00bad1', light: '#e0f7fa', dark: '#00859a' },
      },
      fontFamily: {
        // Anuphan everywhere — the live bia.psu.ac.th display + UI face.
        // Sarabun retained as a self-hosted fallback while Anuphan loads.
        sans: ['Anuphan', 'Sarabun', 'system-ui', 'sans-serif'],
        serif: ['Anuphan', 'Sarabun', 'system-ui', 'sans-serif'],
      },
      fontSize: {
        '2xs': ['0.6875rem', { lineHeight: '1rem' }],
      },
      spacing: {
        18: '4.5rem',
      },
      maxWidth: {
        prose: '68ch',
        'screen-xl': '1200px',
      },
      borderRadius: {
        '4xl': '2rem',
      },
      boxShadow: {
        // Soft, cool slate elevation — matches bia.psu.ac.th card shadows
        // (0 10px 30px rgba(0,0,0,0.1)).
        soft: '0 4px 18px 0 rgba(47, 43, 61, 0.08)',
        // Light dashboard stat-card elevation (bia /user/ dashboard).
        stat: '0 4px 6px 0 rgba(0, 0, 0, 0.07)',
        card: '0 1px 2px rgba(47, 43, 61, 0.06), 0 10px 30px -8px rgba(0, 0, 0, 0.10)',
        'card-hover': '0 6px 16px -4px rgba(47, 43, 61, 0.12), 0 20px 44px -12px rgba(0, 0, 0, 0.18)',
        'inner-line': 'inset 0 -1px 0 rgba(184, 134, 43, 0.4)',
      },
      backgroundImage: {
        // Flat cool surface — bia.psu.ac.th uses no warm grain; keep an
        // extremely subtle neutral dot for optional textured panels only.
        'paper-texture':
          'radial-gradient(rgba(47,43,61,0.03) 1px, transparent 1px)',
        'crimson-wash':
          'linear-gradient(135deg, #6b1414 0%, #9d1c2b 55%, #7c2021 100%)',
        'plum-wash':
          'linear-gradient(160deg, #2f2b3d 0%, #1c1925 100%)',
      },
      backgroundSize: {
        grain: '18px 18px',
      },
      keyframes: {
        'fade-up': {
          '0%': { opacity: '0', transform: 'translateY(16px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        'fade-in': {
          '0%': { opacity: '0' },
          '100%': { opacity: '1' },
        },
      },
      animation: {
        'fade-up': 'fade-up 0.6s cubic-bezier(0.22, 1, 0.36, 1) both',
        'fade-in': 'fade-in 0.8s ease both',
      },
      transitionTimingFunction: {
        'out-expo': 'cubic-bezier(0.16, 1, 0.3, 1)',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
};
