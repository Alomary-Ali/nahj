<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

interface CourseInSpec {
    id: number;
    title: string;
    slug: string;
    headline?: string;
    thumbnail?: string;
    level: string;
    duration_hours: number;
    modules_count?: number;
    enrollments_count?: number;
    reviews_avg_rating?: number;
    category?: {
        name: string;
    };
    instructor?: {
        id?: number;
        name: string;
    };
}

interface Specialization {
    id: number;
    title: string;
    slug: string;
    headline?: string;
    description?: string;
    thumbnail?: string;
    level: string;
    instructor_id?: number;
    instructor?: {
        id: number;
        name: string;
        email: string;
    };
    category?: {
        name: string;
        slug: string;
    };
    courses: CourseInSpec[];
}

const props = defineProps<{
    specialization: Specialization;
    stats: {
        total_courses: number;
        total_hours: number;
        total_students: number;
    };
}>();

function formatLevel(level: string): string {
    switch (level) {
        case 'beginner': return 'مبتدئ (Beginner)';
        case 'intermediate': return 'متوسط (Intermediate)';
        case 'advanced': return 'متقدم (Advanced)';
        default: return 'كافة المستويات';
    }
}
</script>

<template>
    <Head :title="`${specialization.title} - تخصص مهني معتمد`" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased flex flex-col">
        <Navbar />

        <!-- Specialization Hero Header (Coursera Slate 900 Background) -->
        <header class="border-b border-slate-800 bg-slate-900 text-white py-12 lg:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-xs text-slate-400 mb-6">
                    <Link :href="route('home')" class="hover:text-white">الرئيسية</Link>
                    <span>/</span>
                    <span v-if="specialization.category">{{ specialization.category.name }}</span>
                    <span>/</span>
                    <span class="text-slate-200 line-clamp-1">{{ specialization.title }}</span>
                </div>

                <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
                    <div class="lg:col-span-2 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="rounded-full bg-primary/20 border border-primary/40 px-3 py-1 text-xs font-bold text-primary-200">
                                تخصص مهني متكامل (Specialization)
                            </span>
                            <span class="rounded-full bg-slate-800 px-3 py-1 text-xs font-semibold text-slate-300">
                                {{ stats.total_courses }} مقررات دراسية متتالية
                            </span>
                        </div>

                        <h1 class="text-2xl sm:text-4xl font-black text-white leading-tight">
                            {{ specialization.title }}
                        </h1>

                        <p v-if="specialization.headline" class="text-base text-slate-300 leading-relaxed max-w-3xl">
                            {{ specialization.headline }}
                        </p>

                        <!-- Instructor & Highlights -->
                        <div class="flex flex-wrap items-center gap-6 text-xs text-slate-300 pt-3">
                            <Link
                                v-if="specialization.instructor_id"
                                :href="route('instructors.show', specialization.instructor_id)"
                                class="flex items-center gap-2 hover:text-white transition group"
                            >
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary font-bold text-white text-xs group-hover:bg-primary-hover">
                                    {{ specialization.instructor?.name?.charAt(0) || 'C' }}
                                </div>
                                <span>إعداد وتقديم: <strong class="text-white underline decoration-slate-600 underline-offset-4">{{ specialization.instructor?.name || 'جامعة معتمدة' }}</strong></span>
                            </Link>

                            <div class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>شهادة تخصص معتمدة للمسار كاملاً</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>إجمالي الساعات: {{ stats.total_hours }} ساعة</span>
                            </div>
                        </div>
                    </div>

                    <!-- Call-to-action Card -->
                    <div class="lg:col-span-1">
                        <div class="rounded-2xl border border-slate-800 bg-slate-800/80 p-6 shadow-xl backdrop-blur-sm space-y-5">
                            <h3 class="text-base font-black text-white">ابدأ رحلتك في هذا التخصص</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                سجل في أول مقرر من السلسلة لتكتسب المهارات الأساسية وتتقدم نحو شهادة التخصص الرسمية.
                            </p>

                            <div class="border-t border-slate-700/80 pt-4 space-y-2 text-xs text-slate-300">
                                <div class="flex justify-between">
                                    <span>المستوى الأكاديمي:</span>
                                    <strong class="text-white">{{ formatLevel(specialization.level) }}</strong>
                                </div>
                                <div class="flex justify-between">
                                    <span>المقررات المشمولة:</span>
                                    <strong class="text-white">{{ stats.total_courses }} مقررات</strong>
                                </div>
                                <div class="flex justify-between">
                                    <span>الطلاب الملتحقون:</span>
                                    <strong class="text-emerald-400 font-bold">{{ stats.total_students }} طالب</strong>
                                </div>
                            </div>

                            <Link
                                v-if="specialization.courses.length > 0"
                                :href="route('courses.show', specialization.courses[0].slug)"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary py-3.5 text-center text-xs font-bold text-white shadow-lg shadow-primary/25 transition hover:bg-primary-hover"
                            >
                                <span>ابدأ بالمقرر الأول الآن</span>
                                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body: About & Course Sequence Roadmap -->
        <main class="flex-1 py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-12">
                <!-- Specialization Description & What You Learn -->
                <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
                    <h2 class="text-xl font-black text-slate-900 flex items-center gap-2.5">
                        <span class="h-6 w-1.5 rounded-full bg-primary"></span>
                        <span>عن هذا التخصص الأكاديمي والمهني</span>
                    </h2>
                    <div
                        class="prose prose-slate mt-4 text-sm text-slate-600 leading-relaxed max-w-none"
                        v-html="specialization.description || specialization.headline"
                    />

                    <!-- Highlights Grid -->
                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-slate-100 pt-6">
                        <div class="rounded-2xl bg-slate-50 p-5 space-y-1.5">
                            <span class="text-primary font-bold text-lg">01</span>
                            <h4 class="text-xs font-bold text-slate-900">سلسلة تدريبية متدرجة</h4>
                            <p class="text-[11px] text-slate-500">تم بناء كل مقرر ليعتمد على ما سبقه بسلاسة ومنهجية واضحة.</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-5 space-y-1.5">
                            <span class="text-primary font-bold text-lg">02</span>
                            <h4 class="text-xs font-bold text-slate-900">مشاريع تطبيقية حقيقية</h4>
                            <p class="text-[11px] text-slate-500">تطبيق المهارات في مشاريع برمجية وتصميمية قابلة للإضافة إلى معرض أعمالك.</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-5 space-y-1.5">
                            <span class="text-primary font-bold text-lg">03</span>
                            <h4 class="text-xs font-bold text-slate-900">شهادة مهنية شاملة</h4>
                            <p class="text-[11px] text-slate-500">الحصول على شهادة تخصص رقمية موثقة برمز استجابة QR بعد إتمام كافة المقررات.</p>
                        </div>
                    </div>
                </section>

                <!-- Course Sequence (Roadmap) -->
                <section class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900">المقررات المشمولة في هذا التخصص ({{ specialization.courses.length }})</h2>
                        <p class="text-xs text-slate-500 mt-1">تعلّم بالترتيب الموصى به لضمان أفضل استيعاب للمفاهيم الأكاديمية والتطبيقية</p>
                    </div>

                    <div v-if="specialization.courses.length > 0" class="relative space-y-6">
                        <!-- Connecting line for roadmap -->
                        <div
                            v-for="(course, idx) in specialization.courses"
                            :key="course.id"
                            class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow-md"
                        >
                            <!-- Step Number & Details -->
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 flex-1">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary-50 text-primary font-black text-base shadow-sm">
                                    {{ idx + 1 }}
                                </div>

                                <div class="relative h-24 w-40 shrink-0 overflow-hidden rounded-xl bg-slate-900">
                                    <img
                                        :src="course.thumbnail || 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=600&auto=format&fit=crop'"
                                        :alt="course.title"
                                        class="h-full w-full object-cover"
                                    />
                                </div>

                                <div class="space-y-1.5 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="rounded bg-primary-50 px-2 py-0.5 text-[10px] font-bold text-primary">
                                            المقرر {{ idx + 1 }} في المسار
                                        </span>
                                        <span v-if="course.reviews_avg_rating" class="text-[11px] font-bold text-amber-500 flex items-center gap-1">
                                            <span>★</span>
                                            <span>{{ Number(course.reviews_avg_rating).toFixed(1) }}</span>
                                        </span>
                                    </div>

                                    <h3 class="text-base font-bold text-slate-900 hover:text-primary transition">
                                        <Link :href="route('courses.show', course.slug)">
                                            {{ course.title }}
                                        </Link>
                                    </h3>

                                    <p v-if="course.headline" class="text-xs text-slate-500 line-clamp-2">
                                        {{ course.headline }}
                                    </p>

                                    <div class="flex items-center gap-3 text-[11px] text-slate-400 pt-1">
                                        <span>{{ course.duration_hours }} ساعة تدريبية</span>
                                        <span>•</span>
                                        <span>{{ course.modules_count || 0 }} أسابيع / وحدات</span>
                                        <span>•</span>
                                        <span>المستوى: {{ formatLevel(course.level) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Course Action Link -->
                            <div class="shrink-0 w-full sm:w-auto">
                                <Link
                                    :href="route('courses.show', course.slug)"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 px-5 py-3 text-xs font-bold text-slate-700 transition hover:bg-primary hover:text-white w-full sm:w-auto text-center"
                                >
                                    <span>استعراض المقرر</span>
                                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div v-else class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-xs text-slate-500">
                        لا توجد مقررات مضافة إلى هذا التخصص حتى الآن.
                    </div>
                </section>
            </div>
        </main>

        <Footer />
    </div>
</template>
