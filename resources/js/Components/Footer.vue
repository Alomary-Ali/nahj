<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useTranslation } from '@/Composables/useTranslation';

const { t, locale, setLocale } = useTranslation();
</script>

<template>
    <footer class="border-t border-slate-200 bg-white pt-12 pb-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-4 lg:grid-cols-5">
                <div class="col-span-2">
                    <Link :href="route('home')" class="flex items-center gap-2.5">
                        <img src="/images/nahj-icon.png" alt="شعار نهج" class="h-9 w-9 object-contain" />
                        <div class="flex flex-col -space-y-1">
                            <span class="text-xl font-black text-[#042F2E] font-arabic">
                                نهج
                            </span>
                            <span class="text-[9px] font-bold tracking-wider text-primary uppercase">
                                nahj
                            </span>
                        </div>
                    </Link>
                    <p class="mt-3 max-w-sm text-xs leading-relaxed text-slate-500">
                        {{ t('platform_tagline') }}
                    </p>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">{{ t('courses_and_specializations') }}</h4>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600">
                        <li><a href="#" class="hover:text-primary">{{ locale === 'ar' ? 'الذكاء الاصطناعي' : 'Artificial Intelligence' }}</a></li>
                        <li><a href="#" class="hover:text-primary">{{ locale === 'ar' ? 'علم البيانات' : 'Data Science' }}</a></li>
                        <li><a href="#" class="hover:text-primary">{{ locale === 'ar' ? 'علوم الحاسوب' : 'Computer Science' }}</a></li>
                        <li><a href="#" class="hover:text-primary">{{ locale === 'ar' ? 'تصميم تجربة المستخدم' : 'UI/UX Design' }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">{{ t('community_and_partners') }}</h4>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600">
                        <li><a href="#" class="hover:text-primary">{{ t('become_instructor') }}</a></li>
                        <li><a href="#" class="hover:text-primary">{{ t('nahj_for_business') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">{{ locale === 'ar' ? 'الدعم والاعتماد' : 'Support & Verification' }}</h4>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600">
                        <li><Link :href="route('certificates.verify', 'demo')" class="hover:text-primary">{{ t('verify_certificate') }}</Link></li>
                        <li><a href="#" class="hover:text-primary">{{ locale === 'ar' ? 'الشروط والأحكام' : 'Terms & Conditions' }}</a></li>
                        <li><a href="#" class="hover:text-primary">{{ locale === 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy' }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 flex flex-col items-center justify-between gap-4 border-t border-slate-100 pt-6 sm:flex-row text-xs text-slate-400">
                <p>
                    {{ t('copyright', { year: new Date().getFullYear() }) }}
                </p>

                <!-- Language Selector in Footer -->
                <div class="flex items-center gap-2">
                    <button
                        @click="setLocale('ar')"
                        class="rounded px-2 py-1 font-semibold transition"
                        :class="locale === 'ar' ? 'bg-primary-50 text-primary font-bold' : 'hover:text-slate-600'"
                    >
                        العربية
                    </button>
                    <span>|</span>
                    <button
                        @click="setLocale('en')"
                        class="rounded px-2 py-1 font-semibold transition"
                        :class="locale === 'en' ? 'bg-primary-50 text-primary font-bold' : 'hover:text-slate-600'"
                    >
                        English
                    </button>
                </div>
            </div>
        </div>
    </footer>
</template>
