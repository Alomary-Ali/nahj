<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import CourseCard from '@/Components/CourseCard.vue';
import Footer from '@/Components/Footer.vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    courses_count?: number;
}

interface Course {
    id: number;
    ulid: string;
    title: string;
    slug: string;
    headline?: string;
    thumbnail?: string;
    level: string;
    duration_hours: number;
    price: string | number;
    category?: {
        name: string;
        slug: string;
    };
    instructor?: {
        name: string;
    };
}

interface PaginatedData<T> {
    data: T[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    total: number;
}

const props = defineProps<{
    categories: Category[];
    courses: PaginatedData<Course>;
    filters: {
        category?: string;
        search?: string;
    };
}>();

function filterByCategory(slug?: string) {
    router.get(
        route('home'),
        { category: slug, search: props.filters.search },
        { preserveState: true, preserveScroll: true }
    );
}
</script>

<template>
    <Head title="منصة نهج - تعلّم مهارات المستقبل واحصل على شهادات معتمدة" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-800">
        <Navbar />

        <!-- Hero Section -->
        <section class="relative overflow-hidden bg-white border-b border-slate-200 py-16 lg:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid items-center gap-12 lg:grid-cols-2">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary-50 px-3 py-1 text-xs font-bold text-primary">
                            <span class="flex h-2 w-2 rounded-full bg-primary animate-pulse" />
                            مساقات جديدة معتمدة في الذكاء الاصطناعي 2026
                        </div>

                        <h1 class="mt-4 text-3xl font-black tracking-tight text-slate-900 sm:text-5xl lg:leading-tight">
                            تعلّم مهارات المستقبل واحصل على <span class="text-primary">شهادات معتمدة عالمياً</span>
                        </h1>

                        <p class="mt-4 text-base text-slate-600 leading-relaxed sm:text-lg">
                            انضم لملايين الطلاب حول العالم وطوّر مسيرتك المهنية عبر دورات وتخصصات مقدمة من أفضل الخبراء والجامعات الرائدة.
                        </p>

                        <div class="mt-8 flex flex-wrap items-center gap-4">
                            <Link
                                :href="route('register')"
                                class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-3.5 text-base font-bold text-white shadow-lg shadow-primary/25 transition hover:bg-primary-hover hover:shadow-xl"
                            >
                                ابدأ التعلم مجاناً الآن
                            </Link>

                            <a
                                href="#courses-catalog"
                                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3.5 text-base font-bold text-slate-700 transition hover:bg-slate-50"
                            >
                                تصفح كافة الدورات
                            </a>
                        </div>

                        <!-- Highlights Stats -->
                        <div class="mt-10 grid grid-cols-3 gap-6 border-t border-slate-100 pt-6">
                            <div>
                                <p class="text-2xl font-black text-slate-900">100%</p>
                                <p class="text-xs text-slate-500">شهادات قابلة للتحقق</p>
                            </div>
                            <div>
                                <p class="text-2xl font-black text-slate-900">+50</p>
                                <p class="text-xs text-slate-500">مشروع تطبيقي عملي</p>
                            </div>
                            <div>
                                <p class="text-2xl font-black text-slate-900">24/7</p>
                                <p class="text-xs text-slate-500">تعلّم بالسرعة التي تناسبك</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hero Visual Card -->
                    <div class="relative lg:block">
                        <div class="relative mx-auto aspect-video max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-slate-900 shadow-2xl">
                            <img
                                src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=800&auto=format&fit=crop"
                                alt="Students learning"
                                class="h-full w-full object-cover opacity-85"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent" />
                            <div class="absolute bottom-6 start-6 end-6 text-white">
                                <span class="rounded bg-primary px-2 py-0.5 text-xs font-bold">تخصص مميز</span>
                                <h3 class="mt-1 text-lg font-bold">الشبكات العصبية والتعلم العميق</h3>
                                <p class="text-xs text-slate-300">مقدمة من الدكتور أندرو إنغ ومعهد DeepLearning.AI</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Course Catalog Section -->
        <section id="courses-catalog" class="py-14">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 sm:text-3xl">
                            استكشف الكتالوج الأكاديمي
                        </h2>
                        <p class="mt-1 text-sm text-slate-600">
                            اختر من بين أحدث المساقات المتاحة وابدأ في بناء مهاراتك اليوم.
                        </p>
                    </div>

                    <div v-if="filters.search" class="text-xs text-slate-500">
                        نتائج البحث عن: <strong class="text-slate-800">"{{ filters.search }}"</strong>
                        <Link :href="route('home')" class="ms-2 text-primary underline">مسح البحث</Link>
                    </div>
                </div>

                <!-- Category Filter Pills -->
                <div class="mt-6 flex flex-wrap items-center gap-2 border-b border-slate-200 pb-4">
                    <button
                        @click="filterByCategory(undefined)"
                        :class="[
                            'rounded-full px-4 py-1.5 text-xs font-bold transition',
                            !filters.category
                                ? 'bg-primary text-white shadow-sm'
                                : 'bg-white text-slate-700 border border-slate-200 hover:border-primary/40'
                        ]"
                    >
                        الكل (All)
                    </button>

                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        @click="filterByCategory(cat.slug)"
                        :class="[
                            'rounded-full px-4 py-1.5 text-xs font-bold transition',
                            filters.category === cat.slug
                                ? 'bg-primary text-white shadow-sm'
                                : 'bg-white text-slate-700 border border-slate-200 hover:border-primary/40'
                        ]"
                    >
                        {{ cat.name }}
                        <span v-if="cat.courses_count" class="ms-1 opacity-75">({{ cat.courses_count }})</span>
                    </button>
                </div>

                <!-- Courses Grid -->
                <div v-if="courses.data.length > 0" class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <CourseCard
                        v-for="course in courses.data"
                        :key="course.id"
                        :course="course"
                    />
                </div>

                <!-- Empty State (Rule 03 compliant) -->
                <div v-else class="my-16 flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-bold text-slate-900">لم يتم العثور على مقررات مطابقة</h3>
                    <p class="mt-1 max-w-sm text-xs text-slate-500">
                        جرّب اختيار تصنيف آخر أو البحث عن مصطلح مختلف في شريط البحث.
                    </p>
                    <button
                        @click="filterByCategory(undefined)"
                        class="mt-5 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white shadow hover:bg-primary-hover"
                    >
                        عرض كافة المقررات
                    </button>
                </div>
            </div>
        </section>

        <Footer />
    </div>
</template>
