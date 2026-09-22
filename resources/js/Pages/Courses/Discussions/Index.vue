<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import type { PageProps, DiscussionThread } from '@/types';

interface LessonOption {
    id: number;
    title: string;
    slug: string;
}

interface CourseHeader {
    id: number;
    title: string;
    slug: string;
}

const props = defineProps<{
    course: CourseHeader;
    threads: {
        data: DiscussionThread[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    lessons: LessonOption[];
    filters: {
        lesson_id?: string | number | null;
        search?: string | null;
        sort?: string;
    };
}>();

const page = usePage<PageProps>();

const searchQuery = ref(props.filters.search || '');
const selectedLessonId = ref(props.filters.lesson_id || '');
const selectedSort = ref(props.filters.sort || 'latest');
const showNewThreadModal = ref(false);

const newThreadForm = useForm({
    title: '',
    body: '',
    lesson_id: '' as string | number,
});

function applyFilters() {
    router.get(
        route('courses.discussions.index', props.course.slug),
        {
            search: searchQuery.value || undefined,
            lesson_id: selectedLessonId.value || undefined,
            sort: selectedSort.value !== 'latest' ? selectedSort.value : undefined,
        },
        { preserveState: true, replace: true }
    );
}

function setSort(sort: string) {
    selectedSort.value = sort;
    applyFilters();
}

function createThread() {
    newThreadForm.post(route('courses.discussions.store', props.course.slug), {
        onSuccess: () => {
            newThreadForm.reset();
            showNewThreadModal.value = false;
        },
    });
}
</script>

<template>
    <Head :title="`مجتمع ونقاشات المساق: ${course.title}`" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased flex flex-col">
        <Navbar />

        <!-- Breadcrumbs -->
        <div class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 text-xs sm:px-6">
                <div class="flex items-center gap-2 text-slate-500">
                    <Link :href="route('home')" class="hover:text-primary">الرئيسية</Link>
                    <span class="rtl:rotate-180">/</span>
                    <Link :href="route('courses.show', course.slug)" class="hover:text-primary line-clamp-1">
                        {{ course.title }}
                    </Link>
                    <span class="rtl:rotate-180">/</span>
                    <span class="font-bold text-slate-700">مجتمع النقاشات والأسئلة</span>
                </div>

                <Link
                    :href="route('courses.learn', course.slug)"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:underline"
                >
                    <span>الذهاب لغرفة المحاضرات</span>
                    <svg class="h-3.5 w-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </Link>
            </div>
        </div>

        <!-- Forum Hero Banner -->
        <header class="border-b border-slate-200 bg-white py-8">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-bold text-primary">
                            <span class="flex h-2 w-2 rounded-full bg-primary"></span>
                            <span>مجتمع التعلم التفاعلي</span>
                        </div>
                        <h1 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">
                            منتدى ونقاشات: {{ course.title }}
                        </h1>
                        <p class="mt-1 text-xs text-slate-500">
                            اطرح استفساراتك حول الدروس والمفاهيم وتبادل الخبرات مع زملائك والمدربين.
                        </p>
                    </div>

                    <button
                        @click="showNewThreadModal = true"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-xs font-bold text-white shadow-md transition hover:bg-primary-hover"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>طرح سؤال أو موضوع جديد</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 py-8">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 space-y-6">
                <!-- Search & Filters Toolbar -->
                <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row md:items-center md:justify-between">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <input
                            v-model="searchQuery"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="ابحث في أسئلة ونقاشات المساق..."
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 py-2 ps-10 pe-4 text-xs text-slate-800 placeholder-slate-400 focus:border-primary focus:bg-white focus:outline-none focus:ring-1 focus:ring-primary"
                        />
                        <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3">
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Lesson Filter Dropdown -->
                    <div class="w-full md:w-64">
                        <select
                            v-model="selectedLessonId"
                            @change="applyFilters"
                            class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 focus:border-primary focus:bg-white focus:outline-none"
                        >
                            <option value="">جميع الدروس والموضوعات</option>
                            <option v-for="lesson in lessons" :key="lesson.id" :value="lesson.id">
                                {{ lesson.title }}
                            </option>
                        </select>
                    </div>

                    <!-- Sort Filter Tabs -->
                    <div class="flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1 text-xs">
                        <button
                            @click="setSort('latest')"
                            :class="[
                                'rounded-lg px-3 py-1.5 font-bold transition',
                                selectedSort === 'latest' ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-800'
                            ]"
                        >
                            الأحدث
                        </button>
                        <button
                            @click="setSort('top')"
                            :class="[
                                'rounded-lg px-3 py-1.5 font-bold transition',
                                selectedSort === 'top' ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-800'
                            ]"
                        >
                            الأعلى تصويتاً
                        </button>
                        <button
                            @click="setSort('unanswered')"
                            :class="[
                                'rounded-lg px-3 py-1.5 font-bold transition',
                                selectedSort === 'unanswered' ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-800'
                            ]"
                        >
                            بدون إجابة
                        </button>
                    </div>
                </div>

                <!-- Discussion Threads List -->
                <div v-if="threads.data.length > 0" class="divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div
                        v-for="thread in threads.data"
                        :key="thread.id"
                        class="flex flex-col gap-4 p-5 transition hover:bg-slate-50/70 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-start gap-4 flex-1">
                            <!-- Upvote Badge Display -->
                            <div class="flex flex-col items-center justify-center rounded-xl bg-slate-100 px-3 py-2 text-center min-w-[50px] shrink-0">
                                <svg class="h-4 w-4 text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-xs font-black text-slate-800">{{ thread.upvotes_count }}</span>
                            </div>

                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span v-if="thread.is_pinned" class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800">
                                        مثبت
                                    </span>
                                    <span v-if="thread.lesson" class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                        {{ thread.lesson.title }}
                                    </span>
                                </div>

                                <h3 class="text-base font-bold text-slate-900 hover:text-primary transition">
                                    <Link :href="route('courses.discussions.show', { course: course.slug, thread: thread.id })">
                                        {{ thread.title }}
                                    </Link>
                                </h3>

                                <p class="text-xs text-slate-500 line-clamp-2">
                                    {{ thread.body }}
                                </p>

                                <div class="flex items-center gap-2 pt-1 text-[11px] text-slate-400">
                                    <span>كتبه: <strong class="text-slate-600">{{ thread.user?.name || 'طالب' }}</strong></span>
                                    <span>•</span>
                                    <span>{{ new Date(thread.created_at).toLocaleDateString('ar-EG') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Replies Counter -->
                        <div class="flex items-center gap-2 shrink-0 sm:self-center">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-3 py-1 text-xs font-bold text-primary">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                <span>{{ thread.replies_count }} إجابة</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 text-primary">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-base font-black text-slate-900">لا توجد مناقشات مطابقة حالياً</h3>
                    <p class="mx-auto mt-1 max-w-sm text-xs text-slate-500">
                        كن أول من يبدأ الحوار ويطرح سؤالاً في هذا المساق. سيتفاعل معك زملاؤك والمدرب قريباً.
                    </p>
                    <button
                        @click="showNewThreadModal = true"
                        class="mt-5 rounded-xl bg-primary px-5 py-2.5 text-xs font-bold text-white shadow hover:bg-primary-hover"
                    >
                        طرح سؤال جديد
                    </button>
                </div>
            </div>
        </main>

        <!-- New Question Modal -->
        <div
            v-if="showNewThreadModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4"
            @click.self="showNewThreadModal = false"
        >
            <div class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-slate-900">طرح سؤال أو مناقشة جديدة</h3>
                    <button @click="showNewThreadModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="createThread" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">عنوان السؤال أو الموضوع</label>
                        <input
                            v-model="newThreadForm.title"
                            type="text"
                            placeholder="مثال: كيف نقوم بحساب التدرج لدالة السيجمويد رياضياً؟"
                            required
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-primary focus:outline-none"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700">الدرس المرتبط (اختياري)</label>
                        <select
                            v-model="newThreadForm.lesson_id"
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-700 focus:border-primary focus:outline-none"
                        >
                            <option value="">مناقشة عامة للمساق</option>
                            <option v-for="lesson in lessons" :key="lesson.id" :value="lesson.id">
                                {{ lesson.title }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700">تفاصيل وسياق السؤال</label>
                        <textarea
                            v-model="newThreadForm.body"
                            rows="4"
                            placeholder="وضح سؤالك بالتفصيل، وما النقطة التي تحتاج توضيحاً فيها من زملائك أو المدرب..."
                            required
                            class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-primary focus:outline-none"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="showNewThreadModal = false"
                            class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50"
                        >
                            إلغاء
                        </button>
                        <button
                            type="submit"
                            :disabled="newThreadForm.processing"
                            class="rounded-xl bg-primary px-5 py-2 text-xs font-bold text-white shadow hover:bg-primary-hover disabled:opacity-50"
                        >
                            {{ newThreadForm.processing ? 'جاري النشر...' : 'نشر السؤال' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <Footer />
    </div>
</template>
