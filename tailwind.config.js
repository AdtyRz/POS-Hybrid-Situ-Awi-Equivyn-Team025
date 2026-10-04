import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
// Situ Awi — Design System "Warm Forest Hospitality"
// Sumber: 02_Design_System & Palet Warna.docx + DesignV1_3.md
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/**/*.js',
  ],
  theme: {
    // Breakpoint acuan: 360–414 mobile (basis), 768 tablet, 1366 desktop
    screens: {
      sm: '640px',
      md: '768px',
      lg: '1024px',
      xl: '1280px',
      desktop: '1366px',
      '2xl': '1536px',
    },
    extend: {
      colors: {
        forest: {
          900: '#052E1B',
          800: '#06381F',
          700: '#0A4A28',
          600: '#0B4D2B', // brand primary
          400: '#12703F',
          100: '#D9EBE0',
        },
        gold: {
          600: '#C9982F',
          500: '#E0B24E', // aksen utama
          300: '#F3E27A',
          50: '#FBF3DD',
        },
        leaf: {
          600: '#5C7A22',
          400: '#8BAA3F',
          100: '#EEF3DE',
        },
        cream: { 50: '#FBF8F1', 100: '#F3EEDF' },
        charcoal: { 800: '#2B2A26', 500: '#6B6A63' },
        danger: '#C1442C',
        warning: '#E0B24E',
        success: '#2E8B57',
      },
      fontFamily: {
        display: ['"Playfair Display"', 'Georgia', ...defaultTheme.fontFamily.serif],
        sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
      },
      // Skala mobile 12/14/16/20/24/32/40 + skala KDS 20/28/40/56
      fontSize: {
        xs: ['0.75rem', { lineHeight: '1.5' }],   // 12
        sm: ['0.875rem', { lineHeight: '1.5' }],  // 14
        base: ['1rem', { lineHeight: '1.55' }],   // 16
        xl: ['1.25rem', { lineHeight: '1.4' }],   // 20
        '2xl': ['1.5rem', { lineHeight: '1.3' }], // 24
        '3xl': ['2rem', { lineHeight: '1.2' }],   // 32
        '4xl': ['2.5rem', { lineHeight: '1.15' }],// 40
        '5xl': ['3rem', { lineHeight: '1.1' }],   // 48 (hero desktop)
        'kds-md': ['1.75rem', { lineHeight: '1.2' }], // 28
        'kds-xl': ['3.5rem', { lineHeight: '1.1' }],  // 56
      },
      borderRadius: {
        card: '16px',
        btn: '12px',
        toast: '14px',
      },
      boxShadow: {
        card: '0 2px 8px rgba(6, 77, 43, 0.08)',
        'card-hover': '0 8px 24px rgba(6, 77, 43, 0.08)',
        fab: '0 4px 14px rgba(6, 77, 43, 0.25)',
        sidebar: '2px 0 12px rgba(0, 0, 0, 0.05)',
        toast: '0 4px 16px rgba(0, 0, 0, 0.12)',
      },
      // Calm motion: ease-out-expo lembut, tanpa bounce
      transitionTimingFunction: {
        calm: 'cubic-bezier(0.22, 1, 0.36, 1)',
      },
      transitionDuration: {
        micro: '240ms',
        page: '400ms',
        reveal: '800ms',
      },
      spacing: {
        touch: '44px', // touch target minimal
        sidebar: '240px',
      },
      maxWidth: {
        desktop: '1280px',
        waiter: '480px',
      },
      minHeight: { touch: '44px' },
      minWidth: { touch: '44px' },
    },
  },
  plugins: [forms],
};