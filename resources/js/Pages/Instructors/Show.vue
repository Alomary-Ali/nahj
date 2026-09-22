<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import CourseCard from '@/Components/CourseCard.vue';

interface InstructorStats {
    courses_count: number;
    students_count: number;
    reviews_count: number;
    average_rating: number;
}

interface Instructor {
    id: number;
    name: string;
    email: string;
    joined_at: string;
    headline: string;
    bio: string;
    stats: InstructorStats;
}

defineProps<{
    instructor: Instructor;
    courses: any[];
}>();
</script>

<template>
    <Head :title="`${instructor.name} - الملف التعريفي للمحاضر`" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased flex flex-col">
        <Navbar />

        <!-- Instructor Hero Profile Header -->
        <header class="border-b border-slate-800 bg-slate-900 text-white py-12 lg:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-xs text-slate-400 mb-6">
                    <Link :href="route('home')" class="hover:text-white">الرئيسية</Link>
                    <span>/</span>
                    <span>المحاضرون والأساتذة</span>
                    <span>/</span>
                    <span class="text-slate-200">{{ instructor.name }}</span>
                </div>

                <div class="flex flex-col md:flex-row items-start md:items-center gap-8">
                    <!-- Instructor Avatar -->
                    <div class="relative flex h-28 w-28 shrink-0 items-center justify-center rounded-3xl bg-gradient-to-tr from-primary to-blue-400 text-4xl font-black text-white shadow-2xl border-4 border-slate-800">
                        {{ instructor.name.charAt(0) }}
                        <div class="absolute -bottom-2 -end-2 flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500 text-white shadow" title="محاضر معتمد">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Instructor Info -->
                    <div class="space-y-2 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-primary/20 border border-primary/40 px-3 py-1 text-xs font-bold text-primary-200">
                                محاضر معتمد (Verified Instructor)
                            </span>
                            <span class="text-xs text-slate-400">عضو منذ {{ instructor.joined_at }}</span>
                        </div>

                        <h1 class="text-2xl sm:text-4xl font-black text-white">
                            {{ instructor.name }}
                        </h1>

                        <p class="text-sm sm:text-base text-slate-300 max-w-3xl leading-relaxed">
                            {{ instructor.headline }}
                        </p>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-2 gap-3 shrink-0 w-full md:w-auto">
                        <div class="rounded-2xl border border-slate-800 bg-slate-800/60 p-4 text-center min-w-[120px]">
                            <span class="block text-2xl font-black text-primary-300">{{ instructor.stats.courses_count }}</span>
                            <span class="block text-xs font-medium text-slate-400 mt-1">مقررات منشورة</span>
                        </div>
                        <div class="rounded-2xl border border-slate-800 bg-slate-800/60 p-4 text-center min-w-[120px]">
                            <span class="block text-2xl font-black text-emerald-400">{{ instructor.stats.students_count }}</span>
                            <span class="block text-xs font-medium text-slate-400 mt-1">طالباً ملتحقاً</span>
                        </div>
                        <div class="rounded-2xl border border-slate-800 bg-slate-800/60 p-4 text-center min-w-[120px]">
                            <div class="flex items-center justify-center gap-1 text-2xl font-black text-amber-400">
                                <span>★</span>
                                <span>{{ instructor.stats.average_rating }}</span>
                            </div>
                            <span class="block text-xs font-medium text-slate-400 mt-1">متوسط التقييم</span>
                        </div>
                        <div class="rounded-2xl border border-slate-800 bg-slate-800/60 p-4 text-center min-w-[120px]">
                            <span class="block text-2xl font-black text-slate-200">{{ instructor.stats.reviews_count }}</span>
                            <span class="block text-xs font-medium text-slate-400 mt-1">مراجعة طلابية</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-12">
                <!-- Bio & Credentials Section -->
                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-xl font-black text-slate-900 flex items-center gap-2.5">
                        <span class="h-6 w-1.5 rounded-full bg-primary"></span>
                        <span>السيرة الذاتية والخبرات الأكاديمية</span>
                    </h2>
                    <p class="mt-4 text-sm text-slate-600 leading-relaxed max-w-4xl whitespace-pre-line">
                        {{ instructor.bio }}
                    </p>
                </section>

                <!-- Instructor Courses -->
                <section class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-black text-slate-900">المقررات التي يدرسها ({{ courses.length }})</h2>
                            <p class="text-xs text-slate-500 mt-1">تصفح كافة الدورات والمساقات التدريبية التي يقدمها الأستاذ {{ instructor.name }}</p>
                        </div>
                    </div>

                    <div v-if="courses.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <CourseCard
                            v-for="course in courses"
                            :key="course.id"
                            :course="course"
                        />
                    </div>

                    <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-xs text-slate-500">
                        لا توجد مقررات منشورة حالياً لهذا المحاضر.
                    </div>
                </section>
            </div>
        </main>

        <Footer />
    </div>
</template>
