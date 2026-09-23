<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useTranslation } from '@/Composables/useTranslation';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
const { t, locale, setLocale } = useTranslation();

const searchQuery = ref('');
const isLangDropdownOpen = ref(false);

function performSearch() {
    if (searchQuery.value.trim()) {
        router.get(route('home'), { search: searchQuery.value }, { preserveState: true });
    } else {
        router.get(route('home'));
    }
}

function toggleLangDropdown() {
    isLangDropdownOpen.value = !isLangDropdownOpen.value;
}

function switchLanguage(lang: 'ar' | 'en') {
    isLangDropdownOpen.value = false;
    setLocale(lang);
}

function closeDropdown(e: MouseEvent) {
    const target = e.target as HTMLElement;
    if (!target.closest('.lang-dropdown-container')) {
        isLangDropdownOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown);
});
</script>

<template>
    <nav class="sticky top-0 z-40 border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <!-- Brand Logo & Explore -->
            <div class="flex items-center gap-6">
                <Link :href="route('home')" class="flex items-center gap-2.5 group">
                    <img
                        src="/images/nahj-icon.png"
                        alt="شعار نهج"
                        class="h-10 w-10 object-contain transition-transform group-hover:scale-105"
                    />
                    <div class="flex flex-col -space-y-1">
                        <span class="text-2xl font-black tracking-tight text-[#042F2E] font-arabic">
                            نهج
                        </span>
                        <span class="text-[10px] font-bold tracking-wider text-primary uppercase">
                            nahj
                        </span>
                    </div>
                </Link>

                <div class="hidden md:flex">
                    <Link
                        :href="route('home')"
                        class="flex items-center gap-1.5 rounded-lg border border-primary/20 bg-primary-50 px-3.5 py-1.5 text-sm font-semibold text-primary transition hover:bg-primary-100"
                    >
                        <span>{{ t('explore_courses') }}</span>
                        <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </Link>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mx-4 hidden max-w-md flex-1 md:block">
                <form @submit.prevent="performSearch" class="relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        :placeholder="t('search_placeholder')"
                        class="w-full rounded-full border border-slate-300 bg-slate-50 py-2 ps-10 pe-4 text-sm text-slate-800 placeholder-slate-400 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20"
                    />
                    <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </form>
            </div>

            <!-- User Auth & Language Switcher -->
            <div class="flex items-center gap-2.5 sm:gap-3">
                <!-- Language Switcher Dropdown -->
                <div class="relative lang-dropdown-container">
                    <button
                        @click.stop="toggleLangDropdown"
                        type="button"
                        class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-bold text-slate-700 transition hover:border-primary/40 hover:text-primary hover:bg-white"
                        :title="t('language')"
                    >
                        <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <span>{{ locale === 'ar' ? 'العربية' : 'English' }}</span>
                        <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div
                        v-if="isLangDropdownOpen"
                        class="absolute end-0 mt-2 w-32 rounded-xl border border-slate-200 bg-white p-1 shadow-lg z-50"
                    >
                        <button
                            @click="switchLanguage('ar')"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-xs font-bold transition hover:bg-primary-50 hover:text-primary"
                            :class="locale === 'ar' ? 'bg-primary-50 text-primary' : 'text-slate-700'"
                        >
                            <span>العربية</span>
                            <span v-if="locale === 'ar'" class="text-primary font-black">✓</span>
                        </button>
                        <button
                            @click="switchLanguage('en')"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-xs font-bold transition hover:bg-primary-50 hover:text-primary"
                            :class="locale === 'en' ? 'bg-primary-50 text-primary' : 'text-slate-700'"
                        >
                            <span>English</span>
                            <span v-if="locale === 'en'" class="text-primary font-black">✓</span>
                        </button>
                    </div>
                </div>

                <!-- User Auth Area -->
                <template v-if="page.props.auth && page.props.auth.user">
                    <a
                        v-if="page.props.auth.can_access_admin"
                        href="/admin"
                        class="hidden sm:inline-flex items-center gap-1 rounded-lg border border-primary/30 bg-primary-50 px-2.5 py-1.5 text-xs font-bold text-primary transition hover:bg-primary-100"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ t('admin_panel') }}</span>
                    </a>

                    <Link
                        :href="route('dashboard')"
                        class="rounded-lg px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:text-primary"
                    >
                        {{ t('my_learning') }}
                    </Link>
                    <Link
                        :href="route('profile.edit')"
                        class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm font-medium text-slate-700 hover:border-primary/40"
                    >
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-100 font-bold text-xs text-primary">
                            {{ page.props.auth.user.name.charAt(0) }}
                        </span>
                        <span class="hidden sm:inline">{{ page.props.auth.user.name }}</span>
                    </Link>
                </template>
                <template v-else>
                    <Link
                        :href="route('login')"
                        class="rounded-lg px-3 sm:px-4 py-2 text-sm font-bold text-primary transition hover:bg-primary-50"
                    >
                        {{ t('login') }}
                    </Link>
                    <Link
                        :href="route('register')"
                        class="rounded-lg bg-primary px-3 sm:px-4 py-2 text-sm font-bold text-white shadow transition hover:bg-primary-hover"
                    >
                        {{ t('register') }}
                    </Link>
                </template>
            </div>
        </div>
    </nav>
</template>
