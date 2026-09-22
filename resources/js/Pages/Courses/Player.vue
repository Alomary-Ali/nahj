<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import type { DiscussionThread, LessonNote, PageProps } from '@/types';

interface Lesson {
    id: number;
    title: string;
    slug: string;
    type: string;
    duration_seconds: number;
    content?: string;
    video_url?: string;
    is_preview: boolean;
}

interface Module {
    id: number;
    title: string;
    lessons: Lesson[];
}

interface Course {
    id: number;
    title: string;
    slug: string;
    modules: Module[];
}

const props = defineProps<{
    course: Course;
    currentLesson: Lesson;
    discussions?: DiscussionThread[];
    notes?: LessonNote[];
    currentProgress?: {
        seconds_watched: number;
        is_completed: boolean;
    } | null;
    completedLessonIds?: number[];
    courseProgressPercentage?: number;
    navigation: {
        previous_lesson_slug?: string | null;
        next_lesson_slug?: string | null;
        current_index: number;
        total_lessons: number;
    };
}>();

const page = usePage<PageProps>();
const isTheaterMode = ref(false);
const showMobileDrawer = ref(false);
const activeTab = ref<'overview' | 'notes' | 'discussions'>('overview');
const videoPlaybackRate = ref(1.0);
const videoPlayerRef = ref<HTMLVideoElement | null>(null);

const completedIds = ref<number[]>([...(props.completedLessonIds || [])]);
const progressPercentage = ref(props.courseProgressPercentage || 0);
let lastSavedTime = 0;

const noteForm = useForm({
    timestamp_seconds: 0,
    note_text: '',
});

function captureCurrentTimestamp() {
    if (videoPlayerRef.value) {
        noteForm.timestamp_seconds = Math.floor(videoPlayerRef.value.currentTime);
    }
}

function submitNote() {
    captureCurrentTimestamp();
    noteForm.post(route('courses.lecture.notes.store', { course: props.course.slug, lesson: props.currentLesson.slug }), {
        preserveScroll: true,
        onSuccess: () => {
            noteForm.note_text = '';
        },
    });
}

function deleteNote(noteId: number) {
    if (!confirm('هل تريد حذف هذه الملاحظة؟')) return;
    router.delete(route('courses.lecture.notes.destroy', { course: props.course.slug, lesson: props.currentLesson.slug, note: noteId }), {
        preserveScroll: true,
    });
}

function seekTo(seconds: number) {
    if (videoPlayerRef.value) {
        videoPlayerRef.value.currentTime = seconds;
        videoPlayerRef.value.play();
    }
}

function formatTime(seconds: number): string {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

function onVideoLoaded() {
    if (videoPlayerRef.value && props.currentProgress?.seconds_watched) {
        videoPlayerRef.value.currentTime = props.currentProgress.seconds_watched;
    }
}

function sendProgressUpdate(seconds: number, duration: number) {
    if (!page.props.auth.user) return;

    fetch(route('courses.lecture.progress', { course: props.course.slug, lesson: props.currentLesson.slug }), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            seconds_watched: Math.floor(seconds),
            duration_seconds: Math.floor(duration),
        }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.is_completed && !completedIds.value.includes(props.currentLesson.id)) {
            completedIds.value.push(props.currentLesson.id);
        }
        if (data.completion_percentage !== undefined) {
            progressPercentage.value = data.completion_percentage;
        }
    })
    .catch(() => {});
}

function onTimeUpdate() {
    if (!videoPlayerRef.value) return;
    const current = Math.floor(videoPlayerRef.value.currentTime);
    const duration = Math.floor(videoPlayerRef.value.duration) || props.currentLesson.duration_seconds;

    if (Math.abs(current - lastSavedTime) >= 6) {
        lastSavedTime = current;
        sendProgressUpdate(current, duration);
    }
}

function onVideoEnded() {
    if (!videoPlayerRef.value) return;
    const duration = Math.floor(videoPlayerRef.value.duration) || props.currentLesson.duration_seconds;
    sendProgressUpdate(duration, duration);
}

const questionForm = useForm({
    title: '',
    body: '',
    lesson_id: props.currentLesson.id,
});

function submitQuestion() {
    questionForm.lesson_id = props.currentLesson.id;
    questionForm.post(route('courses.discussions.store', props.course.slug), {
        preserveScroll: true,
        onSuccess: () => {
            questionForm.reset();
        },
    });
}

function setPlaybackRate(rate: number) {
    videoPlaybackRate.value = rate;
    if (videoPlayerRef.value) {
        videoPlayerRef.value.playbackRate = rate;
    }
}
</script>

<template>
    <Head :title="`${currentLesson.title} - ${course.title}`" />

    <div class="flex h-screen flex-col bg-slate-900 font-sans text-slate-100 antialiased">
        <!-- Top App Bar -->
        <header class="flex h-14 shrink-0 items-center justify-between border-b border-slate-800 bg-slate-950 px-4 sm:px-6">
            <!-- Left: Back Button & Course Breadcrumb -->
            <div class="flex items-center gap-3">
                <Link
                    :href="route('courses.show', course.slug)"
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-800 text-slate-300 transition hover:bg-slate-700 hover:text-white"
                    title="الرجوع لصفحة المساق"
                >
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>

                <div class="hidden sm:block">
                    <p class="text-xs text-slate-400 line-clamp-1">{{ course.title }}</p>
                    <h1 class="text-sm font-bold text-white line-clamp-1">{{ currentLesson.title }}</h1>
                </div>
            </div>

            <!-- Right: Actions & Progress -->
            <div class="flex items-center gap-3 text-xs">
                <div class="hidden items-center gap-2 rounded-full bg-slate-800/80 px-3 py-1 font-medium text-slate-300 sm:flex">
                    <span>إنجاز المساق: {{ progressPercentage }}%</span>
                    <div class="h-1.5 w-16 overflow-hidden rounded-full bg-slate-700">
                        <div
                            class="h-full bg-emerald-500 transition-all duration-300"
                            :style="{ width: `${progressPercentage}%` }"
                        ></div>
                    </div>
                    <span class="text-[11px] text-slate-400">({{ navigation.current_index }}/{{ navigation.total_lessons }})</span>
                </div>

                <!-- Theater Mode Toggle (Desktop) -->
                <button
                    @click="isTheaterMode = !isTheaterMode"
                    class="hidden rounded-lg bg-slate-800 p-2 text-slate-300 transition hover:bg-slate-700 hover:text-white lg:flex"
                    :title="isTheaterMode ? 'إلغاء وضع التركيز' : 'وضع التركيز والمسرح'"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                </button>

                <!-- Mobile Curriculum Drawer Button -->
                <button
                    @click="showMobileDrawer = !showMobileDrawer"
                    class="flex items-center gap-1.5 rounded-lg bg-primary px-3 py-1.5 font-bold text-white shadow lg:hidden"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>فهرس الدروس</span>
                </button>
            </div>
        </header>

        <!-- Main Workspace Area -->
        <div class="flex flex-1 overflow-hidden">
            <!-- Left/Center: Video or Article Lecture (70% or 100% in Theater mode) -->
            <main class="flex flex-1 flex-col overflow-y-auto bg-slate-950">
                <!-- Video / Media Container (16:9) -->
                <div class="relative w-full bg-black flex items-center justify-center">
                    <div :class="['w-full transition-all duration-300', isTheaterMode ? 'max-w-full' : 'max-w-5xl']">
                        <!-- Video Mode -->
                        <div v-if="currentLesson.type === 'video'" class="relative aspect-video w-full bg-black">
                            <video
                                ref="videoPlayerRef"
                                controls
                                autoplay
                                class="h-full w-full object-contain"
                                :src="currentLesson.video_url || 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4'"
                                @loadedmetadata="onVideoLoaded"
                                @timeupdate="onTimeUpdate"
                                @ended="onVideoEnded"
                            >
                                متصفحك لا يدعم مشغل الفيديو.
                            </video>
                        </div>

                        <!-- Article Reading Mode -->
                        <div v-else class="mx-auto max-w-3xl p-8 text-slate-100">
                            <div class="rounded-xl border border-slate-800 bg-slate-900 p-8 shadow-xl">
                                <span class="rounded bg-primary-900/60 px-2.5 py-1 text-xs font-bold text-primary-300">
                                    مقال وقراءة إثرائية
                                </span>
                                <h2 class="mt-4 text-2xl font-black text-white">{{ currentLesson.title }}</h2>
                                <div class="prose prose-invert mt-6 text-sm text-slate-300 leading-loose whitespace-pre-line">
                                    {{ currentLesson.content || 'محتوى الدرس قيد الإعداد والمراجعة من قبل المدرب.' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lecture Bar & Navigation Controls -->
                <div class="border-t border-slate-800 bg-slate-900 px-4 py-4 sm:px-8">
                    <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-4">
                        <!-- Left: Title & Speed Controls -->
                        <div class="flex items-center gap-4">
                            <div>
                                <h2 class="text-base font-bold text-white">{{ currentLesson.title }}</h2>
                                <span class="text-xs text-slate-400">المدة: {{ Math.round(currentLesson.duration_seconds / 60) }} دقيقة</span>
                            </div>

                            <!-- Speed Selector -->
                            <div v-if="currentLesson.type === 'video'" class="hidden items-center gap-1 rounded-lg border border-slate-700 bg-slate-800 p-1 text-xs sm:flex">
                                <button
                                    v-for="rate in [0.75, 1.0, 1.25, 1.5, 2.0]"
                                    :key="rate"
                                    @click="setPlaybackRate(rate)"
                                    :class="[
                                        'rounded px-2 py-0.5 font-bold transition',
                                        videoPlaybackRate === rate ? 'bg-primary text-white' : 'text-slate-400 hover:text-white'
                                    ]"
                                >
                                    {{ rate }}x
                                </button>
                            </div>
                        </div>

                        <!-- Right: Next / Prev Navigation -->
                        <div class="flex items-center gap-3">
                            <Link
                                v-if="navigation.previous_lesson_slug"
                                :href="route('courses.lecture', { course: course.slug, lesson: navigation.previous_lesson_slug })"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-700 bg-slate-800 px-3.5 py-2 text-xs font-bold text-slate-300 transition hover:bg-slate-700 hover:text-white"
                            >
                                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                <span>الدرس السابق</span>
                            </Link>

                            <Link
                                v-if="navigation.next_lesson_slug"
                                :href="route('courses.lecture', { course: course.slug, lesson: navigation.next_lesson_slug })"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white shadow transition hover:bg-primary-hover"
                            >
                                <span>الدرس التالي</span>
                                <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>

                            <span v-else class="rounded-lg bg-emerald-950/60 border border-emerald-800 px-3 py-2 text-xs font-bold text-emerald-400">
                                تهانينا! أنهيت جميع الدروس
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Lecture Content Tabs (Overview, Notes) -->
                <div class="flex-1 bg-slate-950 p-6 sm:p-8">
                    <div class="mx-auto max-w-5xl">
                        <div class="flex items-center gap-6 border-b border-slate-800 text-xs font-bold">
                            <button
                                @click="activeTab = 'overview'"
                                :class="[
                                    'pb-3 text-start transition',
                                    activeTab === 'overview' ? 'border-b-2 border-primary text-primary' : 'text-slate-400 hover:text-slate-200'
                                ]"
                            >
                                تفاصيل وملاحظات الدرس
                            </button>
                            <button
                                @click="activeTab = 'notes'"
                                :class="[
                                    'pb-3 text-start transition flex items-center gap-2',
                                    activeTab === 'notes' ? 'border-b-2 border-primary text-primary' : 'text-slate-400 hover:text-slate-200'
                                ]"
                            >
                                <span>ملاحظاتي الموقوتة (Notes)</span>
                                <span class="rounded-full bg-slate-800 px-2 py-0.5 text-[10px] text-slate-300">
                                    {{ notes?.length || 0 }}
                                </span>
                            </button>
                            <button
                                @click="activeTab = 'discussions'"
                                :class="[
                                    'pb-3 text-start transition flex items-center gap-2',
                                    activeTab === 'discussions' ? 'border-b-2 border-primary text-primary' : 'text-slate-400 hover:text-slate-200'
                                ]"
                            >
                                <span>أسئلة ونقاشات الدرس</span>
                                <span class="rounded-full bg-slate-800 px-2 py-0.5 text-[10px] text-slate-300">
                                    {{ discussions?.length || 0 }}
                                </span>
                            </button>
                        </div>

                        <!-- Overview Tab -->
                        <div v-if="activeTab === 'overview'" class="py-6 text-sm text-slate-300 leading-relaxed">
                            <p v-if="currentLesson.content">{{ currentLesson.content }}</p>
                            <p v-else class="text-xs text-slate-500">لا توجد ملاحظات إضافية لهذا الدرس حالياً.</p>
                        </div>

                        <!-- Timestamped Notes Tab -->
                        <div v-else-if="activeTab === 'notes'" class="py-6 space-y-6">
                            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                                <div>
                                    <h3 class="text-sm font-bold text-white">ملاحظاتي وإشاراتي المرجعية</h3>
                                    <p class="text-xs text-slate-400">سجّل أفكارك عند أي نقطة زمنية في الفيديو وارجع إليها بنقرة واحدة</p>
                                </div>
                                <button
                                    v-if="currentLesson.type === 'video'"
                                    @click="captureCurrentTimestamp"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800 border border-slate-700 px-3 py-1.5 text-xs font-bold text-slate-200 hover:bg-slate-700"
                                >
                                    <svg class="h-3.5 w-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" />
                                    </svg>
                                    <span>التقاط التوقيت الحالي</span>
                                </button>
                            </div>

                            <!-- Note Input Form -->
                            <form v-if="page.props.auth.user" @submit.prevent="submitNote" class="rounded-xl border border-slate-800 bg-slate-900 p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-200">تدوين ملاحظة جديدة:</span>
                                    <span class="rounded bg-primary-950/80 border border-primary-800 px-2 py-0.5 text-[11px] font-mono font-bold text-primary-300">
                                        التوقيت: {{ formatTime(noteForm.timestamp_seconds) }}
                                    </span>
                                </div>
                                <textarea
                                    v-model="noteForm.note_text"
                                    rows="2"
                                    placeholder="اكتب ملاحظتك التوضيحية أو فكرة مهمة هنا..."
                                    required
                                    class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white placeholder-slate-500 focus:border-primary focus:outline-none"
                                ></textarea>
                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        :disabled="noteForm.processing"
                                        class="rounded-lg bg-primary px-4 py-1.5 text-xs font-bold text-white shadow hover:bg-primary-hover disabled:opacity-50"
                                    >
                                        {{ noteForm.processing ? 'جاري الحفظ...' : 'حفظ الملاحظة' }}
                                    </button>
                                </div>
                            </form>

                            <!-- Notes List -->
                            <div v-if="notes && notes.length > 0" class="space-y-3">
                                <div
                                    v-for="note in notes"
                                    :key="note.id"
                                    class="flex items-start justify-between gap-4 rounded-xl border border-slate-800 bg-slate-900/60 p-4 transition hover:border-slate-700"
                                >
                                    <div class="space-y-1.5 flex-1">
                                        <button
                                            @click="seekTo(note.timestamp_seconds)"
                                            class="inline-flex items-center gap-1.5 rounded bg-primary/20 border border-primary/40 px-2 py-0.5 text-[11px] font-mono font-bold text-primary-300 hover:bg-primary hover:text-white transition"
                                            title="القفز إلى هذه النقطة في الفيديو"
                                        >
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z" />
                                            </svg>
                                            <span>{{ formatTime(note.timestamp_seconds) }}</span>
                                        </button>
                                        <p class="text-xs text-slate-200 leading-relaxed">{{ note.note_text }}</p>
                                    </div>

                                    <button
                                        @click="deleteNote(note.id)"
                                        class="text-slate-500 hover:text-red-400 p-1 text-xs transition"
                                        title="حذف الملاحظة"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div v-else class="rounded-xl border border-slate-800/80 bg-slate-900/40 p-6 text-center text-xs text-slate-500">
                                لم تدون أي ملاحظات لهذا الدرس بعد. التقط ملاحظتك الأولى لتسهيل مراجعتك لاحقاً!
                            </div>
                        </div>

                        <!-- Discussions & Q&A Tab -->
                        <div v-else-if="activeTab === 'discussions'" class="py-6 space-y-6">
                            <!-- Forum Link & Ask Action -->
                            <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                                <div>
                                    <h3 class="text-sm font-bold text-white">استفسارات هذا الدرس</h3>
                                    <p class="text-xs text-slate-400">اطرح سؤالك وسيجيبك المدرب وزملاؤك في المساق</p>
                                </div>
                                <Link
                                    :href="route('courses.discussions.index', course.slug)"
                                    class="text-xs font-bold text-primary hover:underline flex items-center gap-1"
                                >
                                    <span>منتدى المساق العام</span>
                                    <svg class="h-3.5 w-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </Link>
                            </div>

                            <!-- Inline Question Form -->
                            <form v-if="page.props.auth.user" @submit.prevent="submitQuestion" class="rounded-xl border border-slate-800 bg-slate-900 p-4 space-y-3">
                                <h4 class="text-xs font-bold text-slate-200">اطرح سؤالاً حول هذا الدرس</h4>
                                <input
                                    v-model="questionForm.title"
                                    type="text"
                                    placeholder="عنوان السؤال بإيجاز..."
                                    required
                                    class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white placeholder-slate-500 focus:border-primary focus:outline-none"
                                />
                                <textarea
                                    v-model="questionForm.body"
                                    rows="2"
                                    placeholder="اكتب تفاصيل استفسارك..."
                                    required
                                    class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-white placeholder-slate-500 focus:border-primary focus:outline-none"
                                ></textarea>
                                <div class="flex justify-end">
                                    <button
                                        type="submit"
                                        :disabled="questionForm.processing"
                                        class="rounded-lg bg-primary px-4 py-1.5 text-xs font-bold text-white shadow hover:bg-primary-hover disabled:opacity-50"
                                    >
                                        {{ questionForm.processing ? 'جاري الإرسال...' : 'نشر السؤال' }}
                                    </button>
                                </div>
                            </form>

                            <!-- Discussions List -->
                            <div v-if="discussions && discussions.length > 0" class="space-y-3">
                                <Link
                                    v-for="item in discussions"
                                    :key="item.id"
                                    :href="route('courses.discussions.show', { course: course.slug, thread: item.id })"
                                    class="block rounded-xl border border-slate-800 bg-slate-900/60 p-4 transition hover:border-slate-700 hover:bg-slate-900"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="space-y-1">
                                            <h4 class="text-xs font-bold text-white line-clamp-1">{{ item.title }}</h4>
                                            <p class="text-[11px] text-slate-400 line-clamp-1">{{ item.body }}</p>
                                            <div class="flex items-center gap-2 text-[10px] text-slate-500 pt-1">
                                                <span>{{ item.user?.name }}</span>
                                                <span>•</span>
                                                <span>{{ new Date(item.created_at).toLocaleDateString('ar-EG') }}</span>
                                                <span v-if="item.replies?.some(r => r.is_instructor_endorsed)" class="rounded bg-emerald-950/80 border border-emerald-800 px-1.5 py-0.5 text-emerald-400 font-bold">
                                                    إجابة معتمدة من المدرب
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0 text-[11px] text-slate-400">
                                            <span>{{ item.replies_count }} ردود</span>
                                            <span>•</span>
                                            <span>{{ item.upvotes_count }} تصويت</span>
                                        </div>
                                    </div>
                                </Link>
                            </div>

                            <div v-else class="rounded-xl border border-slate-800/80 bg-slate-900/40 p-6 text-center text-xs text-slate-500">
                                لا توجد أسئلة مسجلة حول هذا الدرس بعد. كن أول من يطرح سؤالاً!
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Right Sidebar: Interactive Playlist (30% on Desktop) -->
            <aside
                :class="[
                    'w-80 shrink-0 border-s border-slate-800 bg-slate-900 overflow-y-auto transition-all duration-300',
                    isTheaterMode ? 'hidden' : 'hidden lg:block'
                ]"
            >
                <div class="sticky top-0 z-10 border-b border-slate-800 bg-slate-900 p-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">منهج المقرر الدراسي</h3>
                    <p class="text-xs font-semibold text-white line-clamp-1 mt-0.5">{{ course.title }}</p>
                </div>

                <!-- Modules & Lessons List -->
                <div class="divide-y divide-slate-800">
                    <div v-for="(module, mIndex) in course.modules" :key="module.id" class="p-3">
                        <div class="px-2 py-1 text-xs font-bold text-slate-400">
                            الوحدة {{ mIndex + 1 }}: {{ module.title }}
                        </div>

                        <div class="mt-2 space-y-1">
                            <Link
                                v-for="lesson in module.lessons"
                                :key="lesson.id"
                                :href="route('courses.lecture', { course: course.slug, lesson: lesson.slug })"
                                :class="[
                                    'flex items-center justify-between rounded-lg px-3 py-2 text-xs font-medium transition',
                                    lesson.id === currentLesson.id
                                        ? 'bg-primary text-white shadow-sm font-bold'
                                        : 'text-slate-300 hover:bg-slate-800 hover:text-white'
                                ]"
                            >
                                <div class="flex items-center gap-2 line-clamp-1">
                                    <svg v-if="completedIds.includes(lesson.id)" class="h-4 w-4 shrink-0 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else-if="lesson.type === 'video'" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <svg v-else-if="lesson.type === 'quiz'" class="h-3.5 w-3.5 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <svg v-else class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="line-clamp-1">{{ lesson.title }}</span>
                                    <span v-if="lesson.type === 'quiz'" class="rounded bg-amber-500/20 px-1.5 py-0.5 text-[9px] font-bold text-amber-300 shrink-0">اختبار</span>
                                </div>

                                <span class="text-[10px] opacity-75 shrink-0 ms-2">
                                    {{ Math.round(lesson.duration_seconds / 60) }}د
                                </span>
                            </Link>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <!-- Mobile Bottom Drawer / Playlist (Thumb-friendly Rule 03) -->
        <div
            v-if="showMobileDrawer"
            class="fixed inset-0 z-50 flex flex-col justify-end bg-black/60 backdrop-blur-sm lg:hidden"
            @click.self="showMobileDrawer = false"
        >
            <div class="max-h-[75vh] w-full overflow-y-auto rounded-t-2xl bg-slate-900 p-5 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-white">فهرس المحاضرات</h3>
                    <button @click="showMobileDrawer = false" class="text-slate-400 hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div v-for="(module, mIndex) in course.modules" :key="module.id">
                        <p class="text-xs font-bold text-slate-400">الوحدة {{ mIndex + 1 }}: {{ module.title }}</p>
                        <div class="mt-2 space-y-1">
                            <Link
                                v-for="lesson in module.lessons"
                                :key="lesson.id"
                                :href="route('courses.lecture', { course: course.slug, lesson: lesson.slug })"
                                @click="showMobileDrawer = false"
                                :class="[
                                    'flex items-center justify-between rounded-lg p-2.5 text-xs font-medium',
                                    lesson.id === currentLesson.id ? 'bg-primary text-white font-bold' : 'text-slate-300 bg-slate-800/50'
                                ]"
                            >
                                <div class="flex items-center gap-2 line-clamp-1">
                                    <svg v-if="completedIds.includes(lesson.id)" class="h-4 w-4 shrink-0 text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                    <svg v-else-if="lesson.type === 'video'" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <svg v-else-if="lesson.type === 'quiz'" class="h-3.5 w-3.5 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <svg v-else class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="line-clamp-1">{{ lesson.title }}</span>
                                    <span v-if="lesson.type === 'quiz'" class="rounded bg-amber-500/20 px-1.5 py-0.5 text-[9px] font-bold text-amber-300 shrink-0">اختبار</span>
                                </div>
                                <span class="text-[10px] opacity-75 ms-2">{{ Math.round(lesson.duration_seconds / 60) }}د</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
