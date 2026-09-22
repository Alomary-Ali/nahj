<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import type { CourseReview, RatingStats, PageProps } from '@/types';

interface Lesson {
    id: number;
    title: string;
    slug: string;
    type: string;
    duration_seconds: number;
    is_preview: boolean;
}

interface Module {
    id: number;
    title: string;
    description?: string;
    lessons: Lesson[];
}

interface Course {
    id: number;
    ulid: string;
    title: string;
    slug: string;
    headline?: string;
    description?: string;
    thumbnail?: string;
    preview_video_url?: string;
    level: string;
    language: string;
    price: string | number;
    duration_hours: number;
    instructor_id?: number;
    category?: {
        name: string;
        slug: string;
    };
    specialization?: {
        title: string;
        slug: string;
    };
    instructor?: {
        id?: number;
        name: string;
        email: string;
    };
    modules: Module[];
}

const props = defineProps<{
    course: Course;
    isEnrolled?: boolean;
    userReview?: CourseReview | null;
    ratingStats?: RatingStats;
    reviews?: CourseReview[];
    stats: {
        total_lessons: number;
        total_modules: number;
        total_duration_minutes: number;
    };
    relatedCourses: Course[];
}>();

const page = usePage<PageProps>();
const isEditingReview = ref(false);

const reviewForm = useForm({
    rating: props.userReview?.rating || 5,
    review_text: props.userReview?.review_text || '',
});

function submitReview() {
    reviewForm.post(route('courses.reviews.store', props.course.slug), {
        preserveScroll: true,
        onSuccess: () => {
            isEditingReview.value = false;
        },
    });
}

const openModules = ref<Record<number, boolean>>({
    0: true, // First module open by default
});

function toggleModule(index: number) {
    openModules.value[index] = !openModules.value[index];
}

function formatDuration(seconds: number): string {
    const mins = Math.floor(seconds / 60);
    return `${mins} دقيقة`;
}

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
    <Head :title="`${course.title} - منصة نهج`" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-800">
        <Navbar />

        <!-- Course Header Hero (Coursera Slate 900 Background) -->
        <section class="border-b border-slate-800 bg-slate-900 text-white py-12 lg:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <Link :href="route('home')" class="hover:text-white">الرئيسية</Link>
                    <span>/</span>
                    <span v-if="course.category">{{ course.category.name }}</span>
                    <span>/</span>
                    <span class="text-slate-200 line-clamp-1">{{ course.title }}</span>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-10 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span v-if="course.category" class="rounded bg-primary-600/30 px-2.5 py-1 text-xs font-bold text-primary-200 border border-primary-500/30">
                                {{ course.category.name }}
                            </span>

                            <Link
                                v-if="course.specialization"
                                :href="route('specializations.show', course.specialization.slug)"
                                class="rounded bg-slate-800 px-2.5 py-1 text-xs font-bold text-slate-300 border border-slate-700 hover:bg-slate-700 hover:text-white transition flex items-center gap-1.5"
                            >
                                <svg class="h-3 w-3 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a10.99 10.99 0 00-.25 2.299c0 .77.106 1.517.305 2.227l-2.911 1.248a1 1 0 00-.594.92v2a1 1 0 00.594.92l7 3a1 1 0 00.788 0l7-3a1 1 0 00.594-.92v-2a1 1 0 00-.594-.92l-2.911-1.248c.199-.71.305-1.457.305-2.227 0-.79-.089-1.564-.25-2.299l2.644-1.131a1 1 0 000-1.84l-7-3z" />
                                </svg>
                                <span>جزء من تخصص: {{ course.specialization.title }}</span>
                            </Link>
                        </div>

                        <h1 class="mt-3 text-2xl font-black sm:text-4xl leading-tight">
                            {{ course.title }}
                        </h1>

                        <p v-if="course.headline" class="mt-3 text-base text-slate-300 leading-relaxed">
                            {{ course.headline }}
                        </p>

                        <!-- Instructor & Stats Row -->
                        <div class="mt-6 flex flex-wrap items-center gap-6 text-xs text-slate-300">
                            <Link
                                v-if="course.instructor_id"
                                :href="route('instructors.show', course.instructor_id)"
                                class="flex items-center gap-2 hover:text-white transition group"
                            >
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary font-bold text-white text-xs group-hover:bg-primary-hover">
                                    {{ course.instructor?.name?.charAt(0) || 'C' }}
                                </div>
                                <span>يقدمه: <strong class="text-white underline decoration-slate-600 underline-offset-4">{{ course.instructor?.name || 'جامعة معتمدة' }}</strong></span>
                            </Link>
                            <div v-else class="flex items-center gap-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full bg-primary font-bold text-white text-xs">
                                    {{ course.instructor?.name?.charAt(0) || 'C' }}
                                </div>
                                <span>يقدمه: <strong class="text-white">{{ course.instructor?.name || 'جامعة معتمدة' }}</strong></span>
                            </div>

                            <!-- Star Rating -->
                            <div class="flex items-center gap-1.5 text-amber-400 font-bold">
                                <span>★</span>
                                <span class="text-white">{{ ratingStats?.average || 4.8 }}</span>
                                <span class="text-slate-400 font-normal">({{ ratingStats?.total_reviews || 0 }} تقييم)</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>شهادة إتمام معتمدة</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                </svg>
                                <span>اللغة: {{ course.language === 'ar' ? 'العربية' : 'الإنجليزية' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Body Layout -->
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
                <!-- Left/Center Content (70%) -->
                <div class="lg:col-span-2 space-y-10">
                    <!-- About the Course -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                        <h2 class="text-xl font-black text-slate-900">عن هذا المقرر الدراسي</h2>
                        <div
                            class="prose prose-slate mt-4 text-sm text-slate-600 leading-relaxed max-w-none"
                            v-html="course.description || course.headline"
                        />

                        <div class="mt-6 grid grid-cols-2 gap-4 border-t border-slate-100 pt-6 sm:grid-cols-3">
                            <div class="rounded-xl bg-slate-50 p-4">
                                <span class="text-xs text-slate-500">المستوى الأكاديمي</span>
                                <p class="mt-1 font-bold text-slate-900 text-sm">{{ formatLevel(course.level) }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4">
                                <span class="text-xs text-slate-500">المدة المقدرة</span>
                                <p class="mt-1 font-bold text-slate-900 text-sm">{{ course.duration_hours }} ساعة تدريبية</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4 col-span-2 sm:col-span-1">
                                <span class="text-xs text-slate-500">إجمالي الدروس</span>
                                <p class="mt-1 font-bold text-slate-900 text-sm">{{ stats.total_lessons }} درساً ({{ stats.total_modules }} وحدات)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Syllabus / Modules Accordion -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-black text-slate-900">منهج المقرر الدراسي (Syllabus)</h2>
                                <p class="mt-1 text-xs text-slate-500">{{ stats.total_modules }} أسابيع / وحدات تدريبية متكاملة</p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-3">
                            <div
                                v-for="(module, mIndex) in course.modules"
                                :key="module.id"
                                class="overflow-hidden rounded-xl border border-slate-200 transition"
                            >
                                <button
                                    @click="toggleModule(mIndex)"
                                    class="flex w-full items-center justify-between bg-slate-50 px-5 py-4 text-start font-bold text-slate-800 hover:bg-slate-100"
                                >
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-primary">
                                            {{ mIndex + 1 }}
                                        </span>
                                        <span class="text-sm font-bold text-slate-900">{{ module.title }}</span>
                                    </div>

                                    <div class="flex items-center gap-2 text-xs text-slate-500 font-normal">
                                        <span>{{ module.lessons.length }} درساً</span>
                                        <svg
                                            class="h-4 w-4 transition-transform duration-200"
                                            :class="{ 'rotate-180': openModules[mIndex] }"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </button>

                                <div v-show="openModules[mIndex]" class="divide-y divide-slate-100 bg-white px-5 py-2">
                                    <div
                                        v-for="lesson in module.lessons"
                                        :key="lesson.id"
                                        class="flex items-center justify-between py-3 text-xs"
                                    >
                                        <div class="flex items-center gap-2.5">
                                            <!-- Video or Article Icon -->
                                            <svg v-if="lesson.type === 'video'" class="h-4 w-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <svg v-else class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>

                                            <span class="font-medium text-slate-800">{{ lesson.title }}</span>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <span v-if="lesson.is_preview" class="rounded bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                                معاينة مجانية
                                            </span>
                                            <span class="text-slate-400">{{ formatDuration(lesson.duration_seconds) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructor Info Card -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                            <div class="flex items-center gap-4">
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-primary to-blue-500 text-2xl font-black text-white shadow-md">
                                    {{ course.instructor?.name?.charAt(0) || 'C' }}
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-primary">المحاضر الأكاديمي</span>
                                    <h3 class="text-lg font-black text-slate-900">{{ course.instructor?.name || 'جامعة معتمدة' }}</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">محاضر معتمد في تدريس علوم الحاسب والتقنيات المعاصرة</p>
                                </div>
                            </div>

                            <Link
                                v-if="course.instructor_id"
                                :href="route('instructors.show', course.instructor_id)"
                                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:border-primary hover:text-primary"
                            >
                                <span>الملف التعريفي الكامل</span>
                                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Reviews & Ratings Section -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-8">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">تقييمات وآراء الطلاب (Learner Reviews)</h2>
                            <p class="text-xs text-slate-500 mt-1">تجارب حقيقية من طلاب معتمدين أتموا دراسة هذا المساق</p>
                        </div>

                        <!-- Rating Overview Box (Coursera Style) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 rounded-2xl bg-slate-50 p-6 border border-slate-200/80">
                            <!-- Left: Overall Score -->
                            <div class="flex flex-col items-center justify-center text-center border-b md:border-b-0 md:border-e border-slate-200 pb-6 md:pb-0 md:pe-6">
                                <span class="text-5xl font-black text-slate-900">{{ ratingStats?.average || 4.8 }}</span>
                                <div class="flex items-center gap-1 text-amber-400 text-lg my-1.5">
                                    <span v-for="s in 5" :key="s">★</span>
                                </div>
                                <span class="text-xs font-bold text-slate-500">متوسط تقييم المساق</span>
                                <span class="text-[11px] text-slate-400 mt-0.5">من أصل {{ ratingStats?.total_reviews || 0 }} تقييماً</span>
                            </div>

                            <!-- Right: Star Distribution Bars (2 cols) -->
                            <div class="md:col-span-2 space-y-2 flex flex-col justify-center">
                                <div
                                    v-for="star in [5, 4, 3, 2, 1]"
                                    :key="star"
                                    class="flex items-center gap-3 text-xs"
                                >
                                    <span class="w-14 text-slate-600 font-bold shrink-0">{{ star }} نجوم</span>
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200">
                                        <div
                                            class="h-full bg-amber-400 rounded-full transition-all duration-300"
                                            :style="{ width: `${ratingStats?.distribution?.[star]?.percentage || 0}%` }"
                                        ></div>
                                    </div>
                                    <span class="w-10 text-slate-500 text-end text-[11px] shrink-0 font-medium">
                                        {{ ratingStats?.distribution?.[star]?.percentage || 0 }}%
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit / Edit Review for Enrolled Students -->
                        <div v-if="props.isEnrolled && page.props.auth.user" class="rounded-2xl border border-primary-100 bg-primary-50/50 p-6">
                            <div v-if="!userReview || isEditingReview" class="space-y-4">
                                <h3 class="text-sm font-bold text-slate-900">
                                    {{ userReview ? 'تعديل تقييمك للمساق' : 'شارك تجربتك وقيم هذا المساق' }}
                                </h3>
                                
                                <!-- Star Rating Selector -->
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-600 font-medium">تقييمك:</span>
                                    <div class="flex items-center gap-1">
                                        <button
                                            v-for="star in 5"
                                            :key="star"
                                            type="button"
                                            @click="reviewForm.rating = star"
                                            class="text-2xl transition hover:scale-125 focus:outline-none"
                                            :class="star <= reviewForm.rating ? 'text-amber-400' : 'text-slate-300'"
                                        >
                                            ★
                                        </button>
                                    </div>
                                    <span class="text-xs font-bold text-slate-700 ms-2">({{ reviewForm.rating }} من 5)</span>
                                </div>

                                <textarea
                                    v-model="reviewForm.review_text"
                                    rows="3"
                                    placeholder="اكتب ملاحظاتك وانطباعك عن جودة المحتوى والمدرب لتفيد زملاءك..."
                                    class="w-full rounded-xl border border-slate-300 bg-white p-3 text-xs text-slate-800 placeholder-slate-400 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                ></textarea>

                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        v-if="userReview"
                                        type="button"
                                        @click="isEditingReview = false"
                                        class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100"
                                    >
                                        إلغاء
                                    </button>
                                    <button
                                        type="button"
                                        @click="submitReview"
                                        :disabled="reviewForm.processing"
                                        class="rounded-xl bg-primary px-5 py-2 text-xs font-bold text-white shadow hover:bg-primary-hover disabled:opacity-50"
                                    >
                                        {{ reviewForm.processing ? 'جاري الحفظ...' : (userReview ? 'تحديث التقييم' : 'إرسال التقييم') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Existing Review Card -->
                            <div v-else class="flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-md">تقييمك الحالي</span>
                                        <div class="flex items-center text-amber-400 text-sm">
                                            <span v-for="s in userReview.rating" :key="s">★</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-700 mt-1">{{ userReview.review_text || 'بدون نص مراجعة' }}</p>
                                </div>
                                <button
                                    @click="isEditingReview = true"
                                    class="text-xs font-bold text-primary hover:underline shrink-0"
                                >
                                    تعديل التقييم
                                </button>
                            </div>
                        </div>

                        <!-- Published Reviews List -->
                        <div v-if="reviews && reviews.length > 0" class="space-y-4">
                            <div
                                v-for="review in reviews"
                                :key="review.id"
                                class="rounded-xl border border-slate-100 bg-slate-50/60 p-5 space-y-2.5 transition hover:bg-slate-50"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-100 font-bold text-primary text-xs">
                                            {{ review.user?.name?.charAt(0) || 'ط' }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h4 class="text-xs font-bold text-slate-900">{{ review.user?.name || 'طالب مسجل' }}</h4>
                                                <span class="rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700">طالب معتمد</span>
                                            </div>
                                            <span class="text-[11px] text-slate-400">{{ new Date(review.created_at).toLocaleDateString('ar-EG') }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1 text-amber-400 text-sm">
                                        <span v-for="s in review.rating" :key="s">★</span>
                                    </div>
                                </div>

                                <p v-if="review.review_text" class="text-xs text-slate-600 leading-relaxed ps-12">
                                    {{ review.review_text }}
                                </p>
                            </div>
                        </div>

                        <div v-else class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-xs text-slate-500">
                            لم تُسجّل أي مراجعات مكتوبة لهذا المقرر حتى الآن.
                        </div>
                    </div>
                </div>

                <!-- Right Sticky Enrollment Card (30%) -->
                <div class="relative">
                    <div class="sticky top-24 rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                        <!-- Preview Thumbnail -->
                        <div class="relative aspect-video w-full overflow-hidden rounded-xl bg-slate-900">
                            <img
                                :src="course.thumbnail || 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=600&auto=format&fit=crop'"
                                :alt="course.title"
                                class="h-full w-full object-cover"
                            />
                            <Link
                                :href="route('courses.learn', course.slug)"
                                class="absolute inset-0 flex items-center justify-center bg-black/40 transition hover:bg-black/20"
                            >
                                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-primary shadow-lg transition hover:scale-110">
                                    <svg class="h-6 w-6 ms-0.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                            </Link>
                        </div>

                        <!-- Price Section -->
                        <div class="mt-6 flex items-baseline justify-between">
                            <span class="text-xs text-slate-500 font-semibold">رسوم المساق والشهادة:</span>
                            <span class="text-2xl font-black text-slate-900">
                                {{ Number(course.price) === 0 ? 'مجانـاً' : `$${Number(course.price).toFixed(2)}` }}
                            </span>
                        </div>

                        <!-- Action Button -->
                        <Link
                            v-if="props.isEnrolled"
                            :href="route('courses.learn', course.slug)"
                            class="mt-5 flex w-full items-center justify-center rounded-xl bg-emerald-600 py-3.5 text-center text-sm font-bold text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-700 hover:shadow-xl"
                        >
                            متابعة التعلم (Continue Learning)
                        </Link>
                        <Link
                            v-else
                            :href="route('courses.checkout.show', course.slug)"
                            class="mt-5 flex w-full items-center justify-center rounded-xl bg-primary py-3.5 text-center text-sm font-bold text-white shadow-lg shadow-primary/25 transition hover:bg-primary-hover hover:shadow-xl"
                        >
                            {{ Number(course.price) === 0 ? 'التسجيل المجاني في المقرر' : `سجل الآن مقابل $${Number(course.price).toFixed(2)}` }}
                        </Link>

                        <!-- Highlights List -->
                        <ul class="mt-6 space-y-2.5 text-xs text-slate-600 border-t border-slate-100 pt-5">
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>وصول كامل لكافة المحاضرات والمشاريع</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>شهادة إتمام رسمية قابلة للمشاركة على LinkedIn</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>متوافق تماماً مع الهواتف الذكية والأجهزة اللوحية</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>
