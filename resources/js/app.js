import './bootstrap';
import '../css/app.css';
import 'vue3-toastify/dist/index.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { createI18n } from 'vue-i18n';
import Vue3Toastify, { toast } from 'vue3-toastify';
import en from './lang/en';
import ar from './lang/ar';
import NProgress from 'nprogress';
import 'nprogress/nprogress.css';

// Configure NProgress
NProgress.configure({
    showSpinner: false,
    minimum: 0.1,
    trickleSpeed: 200,
    easing: 'ease',
    speed: 500
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Create i18n instance
const i18n = createI18n({
    legacy: false, // Set to false to use Composition API
    locale: 'en', // set locale
    fallbackLocale: 'en', // set fallback locale
    messages: {
        en,
        ar
    },
    missing: (locale, key) => {
        // Return the key itself as the fallback text
        return key;
    }
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18n) // Add i18n to your app
            .use(Vue3Toastify, {
                autoClose: 3000,
                position: 'top-right',
            })
            .mount(el);
    },
    progress: {
        color: 'blue',
    },
});

// Add these event listeners
router.on('start', () => NProgress.start());
router.on('finish', () => NProgress.done());
router.on('error', () => NProgress.done());













