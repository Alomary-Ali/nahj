<script setup lang="ts">
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const searchQuery = ref('');

function performSearch() {
    if (searchQuery.value.trim()) {
        router.get(route('home'), { search: searchQuery.value }, { preserveState: true });
    } else {
        router.get(route('home'));
    }
}
</script>

<template>
    <nav class="sticky top-0 z-40 border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <!-- Brand Logo & Explore -->
            <div class="flex items-center gap-6">
                <Link :href="route('home')" class="flex items-center gap-2.5 group">
                    <img
                        src="/images/nahj-icon.png"
                        alt="منصة نهج"
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
                        <span>استكشف المقررات</span>
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
                        placeholder="ما المهارة أو التخصص الذي ترغب بتعلمه اليوم؟"
                        class="w-full rounded-full border border-slate-300 bg-slate-50 py-2 ps-10 pe-4 text-sm text-slate-800 placeholder-slate-400 focus:border-primary focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary/20"
                    />
                    <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </form>
            </div>

            <!-- User Auth / Action Buttons -->
            <div class="flex items-center gap-3">
                <template v-if="page.props.auth && page.props.auth.user">
                    <Link
                        :href="route('dashboard')"
                        class="rounded-lg px-3 py-1.5 text-sm font-semibold text-slate-700 transition hover:text-primary"
                    >
                        لوحة تعلّمي
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
                        class="rounded-lg px-4 py-2 text-sm font-bold text-primary transition hover:bg-primary-50"
                    >
                        تسجيل الدخول
                    </Link>
                    <Link
                        :href="route('register')"
                        class="rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white shadow transition hover:bg-primary-hover"
                    >
                        انضم مجاناً
                    </Link>
                </template>
            </div>
        </div>
    </nav>
</template>
