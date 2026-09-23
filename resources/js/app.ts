import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'نهج';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Global translation helper accessible in all templates via $t('key')
        app.config.globalProperties.$t = (key: string, replacements?: Record<string, string | number>): string => {
            const pageProps = props.initialPage.props as any;
            const translations = pageProps?.translations || {};
            let text = translations[key] || key;
            if (replacements) {
                Object.keys(replacements).forEach((k) => {
                    text = text.replace(new RegExp(`:${k}`, 'g'), String(replacements[k]));
                });
            }
            return text;
        };

        // Sync initial direction and language attributes
        const initialLocale = (props.initialPage.props as any)?.locale || 'ar';
        const initialDir = (props.initialPage.props as any)?.direction || (initialLocale === 'ar' ? 'rtl' : 'ltr');
        if (typeof document !== 'undefined') {
            document.documentElement.lang = initialLocale;
            document.documentElement.dir = initialDir;

            // Sync on every Inertia navigation
            router.on('navigate', (event) => {
                const pageProps = event.detail.page.props as any;
                if (pageProps?.locale) {
                    document.documentElement.lang = pageProps.locale;
                    document.documentElement.dir = pageProps.direction || (pageProps.locale === 'ar' ? 'rtl' : 'ltr');
                }
            });
        }

        app.use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#0F766E',
    },
});
