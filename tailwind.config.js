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
        // Primary — deep crimson / "ครั่ง" (drawn from bia.psu.ac.th + digital.bia.or.th)
        crimson: {
          50: '#fbf2f3',
          100: '#f6e0e2',
          200: '#eec1c6',
          300: '#e09aa2',
          400: '#cf5d6a',
          500: '#b73544',
          DEFAULT: '#9d1c2b',
          600: '#8b1a1a',
          700: '#6b1414',
          800: '#821623',
          900: '#5a0f17',
        },
        // Dark plum — footer / hero overlay
        plum: {
          DEFAULT: '#3a0a24',
          800: '#2a0719',
          900: '#1a0612',
        },
        // Warm "กระดาษสา" paper neutrals
        paper: {
          50: '#faf8f4',
          100: '#f0ece4',
          200: '#e6e0d4',
          300: '#c8c4bc',
          400: '#a8a399',
          500: '#888480',
          600: '#6b6862',
        },
        // Body text ink
        ink: {
          DEFAULT: '#222222',
          soft: '#3f3a36',
          light: '#6b6862',
        },
        // Accent — manuscript gold
        gold: {
          DEFAULT: '#b8862b',
          light: '#d4a94e',
          dark: '#8a6420',
        },
      },
      fontFamily: {
        // Body — Sarabun; Headings — Noto Serif Thai
        sans: ['Sarabun', 'system-ui', 'sans-serif'],
        serif: ['"Noto Serif Thai"', 'Georgia', 'serif'],
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
        soft: '0 2px 8px -2px rgba(58, 10, 36, 0.08), 0 8px 24px -8px rgba(58, 10, 36, 0.10)',
        card: '0 1px 2px rgba(58, 10, 36, 0.06), 0 10px 30px -12px rgba(58, 10, 36, 0.18)',
        'card-hover': '0 6px 14px -4px rgba(58, 10, 36, 0.14), 0 20px 48px -16px rgba(58, 10, 36, 0.30)',
        'inner-line': 'inset 0 -1px 0 rgba(184, 134, 43, 0.4)',
      },
      backgroundImage: {
        // Subtle paper grain + dharma-wheel watermark placeholders
        'paper-texture':
          "radial-gradient(rgba(157,28,43,0.04) 1px, transparent 1px)",
        'crimson-wash':
          'linear-gradient(135deg, #6b1414 0%, #9d1c2b 55%, #821623 100%)',
        'plum-wash':
          'linear-gradient(160deg, #3a0a24 0%, #1a0612 100%)',
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
