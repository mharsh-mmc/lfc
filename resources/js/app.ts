import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';

// Import vue-plyr and its CSS
import VuePlyr from 'vue-plyr'
import 'vue-plyr/dist/vue-plyr.css'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(VuePlyr, {
                plyr: {
                    controls: [
                        'play-large',  // Big play button
                        'play',        // Play/pause toggle
                        'rewind',      // Rewind 10s
                        'fast-forward',// Forward 10s
                        'progress',    // Progress bar
                        'current-time',
                        'duration',
                        'mute',        // Mute toggle
                        'volume',
                        'fullscreen'   // Fullscreen toggle
                    ],
                    seekTime: 10,     // Skip time for forward/rewind
                    keyboard: { focused: true, global: true }, // Keyboard shortcuts
                    autoplay: false,
                    hideControls: true // Auto-hide when inactive
                }
            })
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
