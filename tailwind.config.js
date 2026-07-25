import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },

            // Szemantikus színtokenek — lásd docs/szinvilag-terv.md.
            // A tényleges értékek CSS egyéni tulajdonságként élnek (resources/css/app.css),
            // hogy a téma (sötét alap / világos váltás) egyetlen helyen módosítható legyen.
            colors: {
                page: 'var(--bg-page)',
                sunken: 'var(--bg-sunken)',
                surface: 'var(--bg-surface)',
                'surface-hover': 'var(--bg-surface-hover)',
                ink: 'var(--text-primary)',
                'ink-soft': 'var(--text-secondary)',
                'ink-muted': 'var(--text-muted)',
                line: 'var(--border-subtle)',
                'line-strong': 'var(--border-strong)',
                accent: 'var(--accent-primary)',
                'accent-ink': 'var(--accent-ink)',
                success: 'var(--status-success)',
                danger: 'var(--status-danger)',
                warning: 'var(--status-warning)',
                info: 'var(--status-info)',
            },

            // Fluid tipográfia/spacing — lásd docs/tipografia-layout-terv.md.
            fontSize: {
                'fluid-xs': 'var(--step--1)',
                'fluid-base': 'var(--step-0)',
                'fluid-lg': 'var(--step-1)',
                'fluid-xl': 'var(--step-2)',
                'fluid-2xl': 'var(--step-3)',
                'fluid-3xl': 'var(--step-4)',
            },
            spacing: {
                'fluid-xs': 'var(--space-xs)',
                'fluid-sm': 'var(--space-sm)',
                'fluid-md': 'var(--space-md)',
                'fluid-lg': 'var(--space-lg)',
                'fluid-xl': 'var(--space-xl)',
            },
        },
    },

    plugins: [forms],
};
