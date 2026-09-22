<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import type { PageProps, Quiz, Certificate, QuizSubmission, CourseSummary } from '@/types';

const props = defineProps<{
    course: CourseSummary;
    quiz: Quiz;
    latestSubmission?: QuizSubmission | null;
    certificate?: Certificate | null;
}>();

const page = usePage<PageProps>();

// Initialize form answers dictionary: { [questionId: number]: number }
const initialAnswers: Record<number, number> = {};
if (props.latestSubmission?.answers) {
    for (const [qId, optId] of Object.entries(props.latestSubmission.answers)) {
        initialAnswers[Number(qId)] = Number(optId);
    }
}

const form = useForm({
    answers: initialAnswers as Record<number, number>,
});

const isSubmitting = ref(false);

const answeredCount = computed(() => {
    return Object.keys(form.answers).filter(
        (key) => form.answers[Number(key)] !== undefined && form.answers[Number(key)] !== null
    ).length;
});

const totalQuestions = computed(() => props.quiz.questions.length);
const totalPoints = computed(() => props.quiz.questions.reduce((sum, q) => sum + q.points, 0));
const allAnswered = computed(() => answeredCount.value === totalQuestions.value);

function selectOption(questionId: number, optionId: number) {
    form.answers[questionId] = optionId;
}

function submitQuiz() {
    if (!page.props.auth.user) {
        return;
    }

    if (answeredCount.value < totalQuestions.value) {
        const confirmSubmit = window.confirm(
            `لقد قمت بالإجابة على ${answeredCount.value} من أصل ${totalQuestions.value} أسئلة فقط. هل ترغب بالتسليم الآن؟`
        );
        if (!confirmSubmit) {
            return;
        }
    }

    isSubmitting.value = true;
    form.post(route('courses.quiz.submit', { course: props.course.slug, quiz: props.quiz.id }), {
        preserveScroll: true,
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}
</script>

<template>
    <Head :title="`${quiz.title} - ${course.title}`" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased flex flex-col">
        <Navbar />

        <!-- Breadcrumbs Navigation -->
        <div class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center gap-2 px-4 py-3 text-xs text-slate-500 sm:px-6">
                <Link :href="route('home')" class="hover:text-primary">الرئيسية</Link>
                <span class="rtl:rotate-180">/</span>
                <Link :href="route('courses.show', course.slug)" class="hover:text-primary line-clamp-1">
                    {{ course.title }}
                </Link>
                <span class="rtl:rotate-180">/</span>
                <span class="font-bold text-slate-700">تقييم المقرر</span>
            </div>
        </div>

        <main class="flex-1 py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6">
                <!-- Header Card -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-3 py-1 text-xs font-bold text-primary">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>اختبار نهائي معتمد</span>
                            </span>
                            <h1 class="mt-2 text-2xl font-black text-slate-900 sm:text-3xl">
                                {{ quiz.title }}
                            </h1>
                            <p class="mt-1 text-sm text-slate-500">
                                تابع لمقرر: <span class="font-semibold text-slate-700">{{ course.title }}</span>
                            </p>
                        </div>

                        <!-- Back to learning link -->
                        <Link
                            :href="route('courses.learn', course.slug)"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-primary"
                        >
                            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>العودة لغرفة المحاضرات</span>
                        </Link>
                    </div>

                    <p v-if="quiz.description" class="mt-4 text-sm text-slate-600 leading-relaxed">
                        {{ quiz.description }}
                    </p>

                    <!-- Assessment Criteria Grid -->
                    <div class="mt-6 grid grid-cols-2 gap-3 border-t border-slate-100 pt-6 sm:grid-cols-4">
                        <div class="rounded-xl bg-slate-50 p-3 text-center border border-slate-200/60">
                            <span class="block text-xs font-medium text-slate-500">نسبة الاجتياز</span>
                            <span class="mt-1 block text-base font-black text-primary">{{ quiz.passing_percentage }}%</span>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 text-center border border-slate-200/60">
                            <span class="block text-xs font-medium text-slate-500">عدد الأسئلة</span>
                            <span class="mt-1 block text-base font-black text-slate-800">{{ totalQuestions }}</span>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 text-center border border-slate-200/60">
                            <span class="block text-xs font-medium text-slate-500">مجموع الدرجات</span>
                            <span class="mt-1 block text-base font-black text-slate-800">{{ totalPoints }} درجة</span>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 text-center border border-slate-200/60">
                            <span class="block text-xs font-medium text-slate-500">الحد الأقصى للمحاولات</span>
                            <span class="mt-1 block text-base font-black text-slate-800">{{ quiz.max_attempts }} محاولات</span>
                        </div>
                    </div>
                </div>

                <!-- Flash Notifications -->
                <div v-if="page.props.flash?.success" class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-emerald-900">نتيجة التقييم</h3>
                            <p class="text-xs text-emerald-800 mt-0.5">{{ page.props.flash.success }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="page.props.flash?.error" class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-600 text-white shadow">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-rose-900">تنبيه النتيجة</h3>
                            <p class="text-xs text-rose-800 mt-0.5">{{ page.props.flash.error }}</p>
                        </div>
                    </div>
                </div>

                <!-- Certified Student Banner -->
                <div v-if="certificate" class="mt-6 rounded-2xl border border-primary/20 bg-gradient-to-r from-primary-50 via-white to-primary-50/50 p-6 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary text-white shadow-md">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900">شهادتك الرسمية المعتمدة جاهزة وموثقة!</h3>
                                <p class="text-xs text-slate-600 mt-0.5">
                                    كود الاعتماد: <span class="font-mono font-bold text-primary">{{ certificate.certificate_code }}</span> | نسبة الإنجاز: {{ certificate.grade_percentage }}%
                                </p>
                            </div>
                        </div>

                        <Link
                            :href="route('certificates.verify', certificate.certificate_code)"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-xs font-bold text-white shadow-md transition hover:bg-primary-hover"
                        >
                            <span>عرض وتحميل الشهادة</span>
                            <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- Latest Attempt Status (if not certified yet) -->
                <div v-else-if="latestSubmission" class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-medium text-slate-500">
                            محاولتك الأخيرة (رقم {{ latestSubmission.attempt_number }}):
                            <strong :class="latestSubmission.passed ? 'text-emerald-600 font-bold' : 'text-rose-600 font-bold'">
                                {{ latestSubmission.percentage }}% ({{ latestSubmission.passed ? 'ناجح' : 'لم يجتز' }})
                            </strong>
                        </span>
                        <span class="text-slate-400">تاريخ التقديم: {{ new Date(latestSubmission.submitted_at).toLocaleDateString('ar-EG') }}</span>
                    </div>
                </div>

                <!-- Guest Notice (if not logged in) -->
                <div v-if="!page.props.auth.user" class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-6 text-center shadow-sm">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="mt-3 text-base font-bold text-amber-900">تسجيل الدخول مطلوب لاحتساب التقييم</h3>
                    <p class="mt-1 text-xs text-amber-800">
                        يمكنك الاطلاع على الأسئلة وتجربتها، ولكن يلزم تسجيل الدخول لحفظ درجاتك وإصدار الشهادة الرسمية باسمك.
                    </p>
                    <div class="mt-4 flex justify-center gap-3">
                        <Link :href="route('login')" class="rounded-lg bg-primary px-5 py-2 text-xs font-bold text-white shadow hover:bg-primary-hover">
                            تسجيل الدخول الآن
                        </Link>
                    </div>
                </div>

                <!-- Questions Form -->
                <form @submit.prevent="submitQuiz" class="mt-8 space-y-6">
                    <div
                        v-for="(question, index) in quiz.questions"
                        :key="question.id"
                        class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300"
                    >
                        <!-- Question Header -->
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-700">
                                    {{ index + 1 }}
                                </span>
                                <span class="text-xs font-semibold text-slate-400">سؤال اختيار من متعدد</span>
                            </div>

                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-bold text-slate-600">
                                {{ question.points }} {{ question.points > 1 ? 'درجات' : 'درجة' }}
                            </span>
                        </div>

                        <!-- Question Text -->
                        <h2 class="mt-4 text-base font-bold text-slate-900 leading-relaxed">
                            {{ question.question_text }}
                        </h2>

                        <!-- Options List -->
                        <div class="mt-5 space-y-3">
                            <label
                                v-for="option in question.options"
                                :key="option.id"
                                :class="[
                                    'flex cursor-pointer items-center justify-between rounded-xl border p-4 text-sm transition',
                                    form.answers[question.id] === option.id
                                        ? 'border-primary bg-primary-50/40 text-primary-900 font-semibold ring-1 ring-primary'
                                        : 'border-slate-200 bg-slate-50/50 text-slate-700 hover:border-slate-300 hover:bg-white'
                                ]"
                                @click="selectOption(question.id, option.id)"
                            >
                                <div class="flex items-center gap-3.5">
                                    <input
                                        type="radio"
                                        :name="`question_${question.id}`"
                                        :value="option.id"
                                        :checked="form.answers[question.id] === option.id"
                                        class="h-4 w-4 border-slate-300 text-primary focus:ring-primary"
                                    />
                                    <span>{{ option.option_text }}</span>
                                </div>

                                <svg
                                    v-if="form.answers[question.id] === option.id"
                                    class="h-5 w-5 text-primary shrink-0"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </label>
                        </div>
                    </div>

                    <!-- Sticky Bottom Submit Bar -->
                    <div class="sticky bottom-4 z-20 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-xl backdrop-blur-md">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-3 text-xs">
                                <span class="font-bold text-slate-700">
                                    تمت الإجابة: {{ answeredCount }} من {{ totalQuestions }} أسئلة
                                </span>
                                <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-100 sm:w-44">
                                    <div
                                        class="h-full bg-primary transition-all duration-300"
                                        :style="{ width: `${totalQuestions > 0 ? (answeredCount / totalQuestions) * 100 : 0}%` }"
                                    ></div>
                                </div>
                            </div>

                            <button
                                v-if="page.props.auth.user"
                                type="submit"
                                :disabled="form.processing || isSubmitting"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-7 py-3 text-sm font-bold text-white shadow-md transition hover:bg-primary-hover disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg
                                    v-if="form.processing || isSubmitting"
                                    class="h-4 w-4 animate-spin text-white"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>تسليم الاختبار النهائي</span>
                            </button>

                            <Link
                                v-else
                                :href="route('login')"
                                class="rounded-xl bg-primary px-6 py-2.5 text-xs font-bold text-white shadow hover:bg-primary-hover"
                            >
                                سجل الدخول لتسليم الاختبار
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </main>

        <Footer />
    </div>
</template>
