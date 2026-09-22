<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import CourseCard from '@/Components/CourseCard.vue';
import type { PageProps, Certificate } from '@/types';

interface ModuleLesson {
    id: number;
    title: string;
    slug: string;
    type: string;
    duration_seconds: number;
}

interface CourseModule {
    id: number;
    lessons: ModuleLesson[];
}

interface EnrolledCourse {
    id: number;
    title: string;
    slug: string;
    headline?: string;
    thumbnail?: string;
    duration_hours: number;
    level: string;
    instructor?: { id: number; name: string };
    category?: { id: number; name: string };
    modules: CourseModule[];
}

interface EnrollmentItem {
    id: number;
    user_id: number;
    course_id: number;
    status: string;
    price_paid: string | number;
    enrolled_at: string;
    progress_percentage?: number;
    completed_lessons_count?: number;
    total_lessons_count?: number;
    course: EnrolledCourse;
}

const props = defineProps<{
    enrollments: EnrollmentItem[];
    certificates: Certificate[];
    exploreCourses: any[];
    stats: {
        enrolled_count: number;
        certificates_count: number;
        total_learning_hours: number;
    };
}>();

const page = usePage<PageProps>();
const activeTab = ref<'courses' | 'certificates'>('courses');
</script>

<template>
    <Head title="لوحة تعلّمي (My Learning) - نهج" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased flex flex-col">
        <Navbar />

        <!-- Header Greeting & Learning Stats Banner -->
        <header class="border-b border-slate-200 bg-white py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-bold text-primary">
                            <span class="flex h-2 w-2 rounded-full bg-primary animate-pulse"></span>
                            <span>بوابة الطالب الأكاديمية</span>
                        </div>
                        <h1 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">
                            مرحباً بك مجدداً، {{ page.props.auth.user.name }}!
                        </h1>
                        <p class="mt-1 text-xs text-slate-500">
                            تابع تقدمك التعليمي واستأنف مساقاتك للحصول على الشهادات المعتمدة.
                        </p>
                    </div>

                    <!-- Learning Statistics Counters -->
                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-3.5 text-center min-w-[100px]">
                            <span class="block text-2xl font-black text-primary">{{ stats.enrolled_count }}</span>
                            <span class="block text-[11px] font-bold text-slate-500 mt-0.5">مساقات مسجلة</span>
                        </div>
                        <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-3.5 text-center min-w-[100px]">
                            <span class="block text-2xl font-black text-emerald-600">{{ stats.certificates_count }}</span>
                            <span class="block text-[11px] font-bold text-slate-500 mt-0.5">شهادات معتمدة</span>
                        </div>
                        <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-3.5 text-center min-w-[100px]">
                            <span class="block text-2xl font-black text-slate-800">{{ stats.total_learning_hours }}</span>
                            <span class="block text-[11px] font-bold text-slate-500 mt-0.5">ساعات تدريبية</span>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="mt-8 flex gap-6 border-b border-slate-200 text-sm font-bold">
                    <button
                        @click="activeTab = 'courses'"
                        :class="[
                            'pb-3 transition flex items-center gap-2',
                            activeTab === 'courses'
                                ? 'border-b-2 border-primary text-primary'
                                : 'text-slate-500 hover:text-slate-800'
                        ]"
                    >
                        <span>مقرراتي الجارية (In Progress)</span>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                            {{ enrollments.length }}
                        </span>
                    </button>

                    <button
                        @click="activeTab = 'certificates'"
                        :class="[
                            'pb-3 transition flex items-center gap-2',
                            activeTab === 'certificates'
                                ? 'border-b-2 border-primary text-primary'
                                : 'text-slate-500 hover:text-slate-800'
                        ]"
                    >
                        <span>الشهادات المكتسبة (Accomplishments)</span>
                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">
                            {{ certificates.length }}
                        </span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Body Content -->
        <main class="flex-1 py-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Flash Notification Banner -->
                <div v-if="page.props.flash?.success" class="mb-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <p class="text-xs font-bold text-emerald-900">{{ page.props.flash.success }}</p>
                    </div>
                </div>

                <!-- Tab 1: Enrolled Courses List -->
                <section v-if="activeTab === 'courses'">
                    <!-- If User Has Enrollments -->
                    <div v-if="enrollments.length > 0" class="space-y-6">
                        <div
                            v-for="enrollment in enrollments"
                            :key="enrollment.id"
                            class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 md:flex-row md:items-center md:justify-between gap-6"
                        >
                            <!-- Course Thumbnail and Details -->
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 flex-1">
                                <div class="relative h-28 w-44 shrink-0 overflow-hidden rounded-xl bg-slate-900">
                                    <img
                                        :src="enrollment.course.thumbnail || 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=600&auto=format&fit=crop'"
                                        :alt="enrollment.course.title"
                                        class="h-full w-full object-cover"
                                    />
                                    <div class="absolute bottom-2 start-2 rounded bg-black/70 px-2 py-0.5 text-[10px] font-bold text-white">
                                        {{ enrollment.course.duration_hours }} ساعة
                                    </div>
                                </div>

                                <div class="space-y-1.5 flex-1">
                                    <span v-if="enrollment.course.category" class="rounded bg-primary-50 px-2.5 py-0.5 text-[10px] font-bold text-primary">
                                        {{ enrollment.course.category.name }}
                                    </span>
                                    <h2 class="text-lg font-black text-slate-900 hover:text-primary transition">
                                        <Link :href="route('courses.show', enrollment.course.slug)">
                                            {{ enrollment.course.title }}
                                        </Link>
                                    </h2>
                                    <p class="text-xs text-slate-500">
                                        المدرب: <strong class="text-slate-700">{{ enrollment.course.instructor?.name || 'جامعة معتمدة' }}</strong>
                                        | مسجل منذ: {{ new Date(enrollment.enrolled_at).toLocaleDateString('ar-EG') }}
                                    </p>

                                    <!-- Progress indicator -->
                                    <div class="pt-2">
                                        <div class="flex items-center justify-between text-[11px] text-slate-500 font-semibold mb-1">
                                            <span>
                                                التقدم الدراسي:
                                                <span v-if="enrollment.total_lessons_count" class="font-normal text-slate-400">
                                                    ({{ enrollment.completed_lessons_count }} من {{ enrollment.total_lessons_count }} درس)
                                                </span>
                                            </span>
                                            <span :class="['font-bold', (enrollment.progress_percentage || 0) === 100 ? 'text-emerald-600' : 'text-primary']">
                                                {{ enrollment.progress_percentage || 0 }}%
                                            </span>
                                        </div>
                                        <div class="h-2 w-full max-w-md overflow-hidden rounded-full bg-slate-100">
                                            <div
                                                :class="['h-full rounded-full transition-all duration-500', (enrollment.progress_percentage || 0) === 100 ? 'bg-emerald-500' : 'bg-primary']"
                                                :style="{ width: `${enrollment.progress_percentage || 0}%` }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resume Button Action -->
                            <div class="flex sm:flex-col items-center gap-3 shrink-0">
                                <Link
                                    :href="route('courses.learn', enrollment.course.slug)"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3 text-xs font-bold text-white shadow-md transition hover:bg-primary-hover w-full sm:w-auto text-center"
                                >
                                    <span>استئناف التعلّم</span>
                                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </Link>

                                <Link
                                    :href="route('courses.show', enrollment.course.slug)"
                                    class="text-xs font-semibold text-slate-500 hover:text-primary text-center"
                                >
                                    عرض تفاصيل المنهج
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State for Courses -->
                    <div v-else class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-primary-50 text-primary">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-black text-slate-900">لم تسجل في أي مقرر بعد</h3>
                        <p class="mx-auto mt-1 max-w-sm text-xs text-slate-500">
                            ابدأ رحلتك التعليمية اليوم وتصفح كتالوج الدورات المعتمدة في مجالات الذكاء الاصطناعي والبرمجة والتصميم.
                        </p>
                        <div class="mt-6">
                            <Link
                                :href="route('home')"
                                class="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-xs font-bold text-white shadow transition hover:bg-primary-hover"
                            >
                                <span>استكشاف الدورات الآن</span>
                                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </section>

                <!-- Tab 2: Earned Certificates List -->
                <section v-else-if="activeTab === 'certificates'">
                    <div v-if="certificates.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="cert in certificates"
                            :key="cert.id"
                            class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-primary/40 hover:shadow-lg"
                        >
                            <div class="flex items-center justify-between">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                    </svg>
                                </span>
                                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700">
                                    درجة الإنجاز: {{ cert.grade_percentage }}%
                                </span>
                            </div>

                            <h3 class="mt-4 text-base font-black text-slate-900 leading-snug">
                                {{ cert.course?.title }}
                            </h3>
                            <p class="mt-1 text-xs text-slate-500">
                                تاريخ المنح: {{ new Date(cert.issue_date).toLocaleDateString('ar-EG') }}
                            </p>
                            <p class="mt-2 font-mono text-[11px] text-slate-400">
                                الرمز: {{ cert.certificate_code }}
                            </p>

                            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                                <Link
                                    :href="route('certificates.verify', cert.certificate_code)"
                                    class="text-xs font-bold text-primary hover:underline flex items-center gap-1"
                                >
                                    <span>عرض وثيقة التوثيق</span>
                                    <svg class="h-3.5 w-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State for Certificates -->
                    <div v-else class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-lg font-black text-slate-900">لا توجد شهادات مكتسبة بعد</h3>
                        <p class="mx-auto mt-1 max-w-sm text-xs text-slate-500">
                            أكمل متطلبات مساقاتك واجتز الاختبار النهائي بنسبة 80% فما فوق لتحصل على شهاداتك الرسمية المعتمدة هنا.
                        </p>
                    </div>
                </section>

                <!-- Section: Explore More Courses -->
                <section v-if="exploreCourses && exploreCourses.length > 0" class="mt-16">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">مقترحة لك (Recommended for You)</h2>
                            <p class="text-xs text-slate-500 mt-0.5">مساقات مختارة لتوسيع معرفتك واكتساب مهارات إضافية</p>
                        </div>
                        <Link :href="route('home')" class="text-xs font-bold text-primary hover:underline">
                            تصفح جميع المقررات
                        </Link>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <CourseCard
                            v-for="course in exploreCourses"
                            :key="course.id"
                            :course="course"
                        />
                    </div>
                </section>
            </div>
        </main>

        <Footer />
    </div>
</template>
