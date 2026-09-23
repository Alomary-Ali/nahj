import { computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

export function useTranslation() {
    const page = usePage<PageProps>();

    const locale = computed(() => page.props.locale || 'ar');
    const direction = computed<'rtl' | 'ltr'>(() => (page.props.direction as 'rtl' | 'ltr') || 'rtl');
    const isRtl = computed(() => direction.value === 'rtl');
    const translations = computed(() => (page.props.translations as Record<string, string>) || {});

    function t(key: string, replacements?: Record<string, string | number>): string {
        let text = translations.value[key] || key;

        if (replacements) {
            Object.keys(replacements).forEach((k) => {
                text = text.replace(new RegExp(`:${k}`, 'g'), String(replacements[k]));
            });
        }

        return text;
    }

    function setLocale(newLocale: 'ar' | 'en') {
        if (newLocale === locale.value) return;

        router.post(
            route('locale.update'),
            { locale: newLocale },
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Update document attributes immediately
                    document.documentElement.lang = newLocale;
                    document.documentElement.dir = newLocale === 'ar' ? 'rtl' : 'ltr';
                },
            }
        );
    }

    return {
        t,
        locale,
        direction,
        isRtl,
        setLocale,
    };
}
