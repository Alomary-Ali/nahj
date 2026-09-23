<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-slate-50 font-sans antialiased text-slate-800 flex flex-col justify-between">
        <!-- Top bar with brand and back link -->
        <header class="border-b border-slate-200/80 bg-white">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
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

                <Link
                    :href="route('home')"
                    class="flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-primary transition"
                >
                    <span>العودة للرئيسية</span>
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
            </div>
        </header>

        <!-- Main Card Container -->
        <main class="flex-1 flex items-center justify-center py-10 px-4 sm:px-6">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <slot />
            </div>
        </main>

        <!-- Minimal Footer -->
        <footer class="border-t border-slate-200/80 bg-white py-4 text-center text-xs text-slate-400">
            &copy; {{ new Date().getFullYear() }} نهج - Nahj. جميع الحقوق محفوظة.
        </footer>
    </div>
</template>
