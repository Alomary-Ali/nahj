<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import type { PageProps, DiscussionThread } from '@/types';

const props = defineProps<{
    course: { id: number; title: string; slug: string };
    thread: DiscussionThread;
    isThreadUpvoted: boolean;
    upvotedReplyIds: number[];
    isInstructorOrAdmin: boolean;
}>();

const page = usePage<PageProps>();

const replyForm = useForm({
    body: '',
});

function toggleThreadUpvote() {
    if (!page.props.auth.user) {
        return;
    }
    useForm({
        upvotable_type: 'thread',
        upvotable_id: props.thread.id,
    }).post(route('courses.discussions.upvote', props.course.slug), {
        preserveScroll: true,
    });
}

function toggleReplyUpvote(replyId: number) {
    if (!page.props.auth.user) {
        return;
    }
    useForm({
        upvotable_type: 'reply',
        upvotable_id: replyId,
    }).post(route('courses.discussions.upvote', props.course.slug), {
        preserveScroll: true,
    });
}

function toggleEndorsement(replyId: number) {
    useForm({}).post(route('courses.discussions.endorse', { course: props.course.slug, reply: replyId }), {
        preserveScroll: true,
    });
}

function submitReply() {
    replyForm.post(route('courses.discussions.reply', { course: props.course.slug, thread: props.thread.id }), {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.reset();
        },
    });
}
</script>

<template>
    <Head :title="`${thread.title} - مجتمع المساق`" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased flex flex-col">
        <Navbar />

        <!-- Breadcrumbs -->
        <div class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3 text-xs sm:px-6">
                <div class="flex items-center gap-2 text-slate-500">
                    <Link :href="route('home')" class="hover:text-primary">الرئيسية</Link>
                    <span class="rtl:rotate-180">/</span>
                    <Link :href="route('courses.show', course.slug)" class="hover:text-primary line-clamp-1">
                        {{ course.title }}
                    </Link>
                    <span class="rtl:rotate-180">/</span>
                    <Link :href="route('courses.discussions.index', course.slug)" class="hover:text-primary">
                        المنتدى والنقاشات
                    </Link>
                    <span class="rtl:rotate-180">/</span>
                    <span class="font-bold text-slate-700 line-clamp-1">تفاصيل السؤال</span>
                </div>

                <Link
                    :href="route('courses.discussions.index', course.slug)"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-primary"
                >
                    <svg class="h-3.5 w-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>العودة لقائمة المناقشات</span>
                </Link>
            </div>
        </div>

        <main class="flex-1 py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 space-y-6">
                <!-- Flash Notification -->
                <div v-if="page.props.flash?.success" class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                    <p class="text-xs font-bold text-emerald-900">{{ page.props.flash.success }}</p>
                </div>

                <!-- Main Thread Card -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="flex items-start gap-4">
                        <!-- Upvote Button -->
                        <button
                            @click="toggleThreadUpvote"
                            :disabled="!page.props.auth.user"
                            :class="[
                                'flex flex-col items-center justify-center rounded-xl p-3 min-w-[56px] border transition',
                                isThreadUpvoted
                                    ? 'border-primary bg-primary text-white shadow-md'
                                    : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-primary/50'
                            ]"
                            title="التصويت لهذا السؤال"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            <span class="mt-1 text-xs font-black">{{ thread.upvotes_count }}</span>
                        </button>

                        <div class="flex-1 space-y-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span v-if="thread.is_pinned" class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800">
                                    مثبت
                                </span>
                                <span v-if="thread.lesson" class="rounded bg-primary-50 px-2.5 py-0.5 text-[11px] font-bold text-primary">
                                    {{ thread.lesson.title }}
                                </span>
                            </div>

                            <h1 class="text-xl font-black text-slate-900 sm:text-2xl leading-tight">
                                {{ thread.title }}
                            </h1>

                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary/10 font-bold text-[10px] text-primary">
                                    {{ thread.user?.name?.charAt(0) || 'U' }}
                                </span>
                                <span>كتبه: <strong class="text-slate-700">{{ thread.user?.name }}</strong></span>
                                <span>•</span>
                                <span>{{ new Date(thread.created_at).toLocaleDateString('ar-EG') }}</span>
                            </div>

                            <div class="pt-4 border-t border-slate-100 text-sm leading-relaxed text-slate-700 whitespace-pre-line">
                                {{ thread.body }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Replies Header & Count -->
                <div class="flex items-center justify-between pt-4">
                    <h2 class="text-base font-black text-slate-900">
                        الإجابات والمناقشات ({{ thread.replies?.length || 0 }})
                    </h2>
                </div>

                <!-- Replies List -->
                <div class="space-y-4">
                    <div
                        v-for="reply in thread.replies"
                        :key="reply.id"
                        :class="[
                            'rounded-2xl border p-6 transition shadow-sm',
                            reply.is_instructor_endorsed
                                ? 'border-emerald-300 bg-emerald-50/40 ring-1 ring-emerald-300'
                                : 'border-slate-200 bg-white'
                        ]"
                    >
                        <!-- Instructor Endorsement Banner -->
                        <div v-if="reply.is_instructor_endorsed" class="mb-4 flex items-center gap-2 rounded-xl bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-800 w-fit">
                            <svg class="h-4 w-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>إجابة موثقة ومعتمدة من مدرّب المساق الأكاديمي</span>
                        </div>

                        <div class="flex items-start gap-4">
                            <!-- Reply Upvote Button -->
                            <button
                                @click="toggleReplyUpvote(reply.id)"
                                :disabled="!page.props.auth.user"
                                :class="[
                                    'flex flex-col items-center justify-center rounded-xl p-2 min-w-[48px] border transition',
                                    upvotedReplyIds.includes(reply.id)
                                        ? 'border-primary bg-primary text-white shadow-sm'
                                        : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-primary/50'
                                ]"
                                title="تصويت لهذه الإجابة"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-xs font-black">{{ reply.upvotes_count }}</span>
                            </button>

                            <div class="flex-1 space-y-2">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-xs">
                                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-[10px] font-bold text-slate-700">
                                            {{ reply.user?.name?.charAt(0) || 'U' }}
                                        </div>
                                        <span class="font-bold text-slate-800">{{ reply.user?.name }}</span>
                                        <span class="text-slate-400">• {{ new Date(reply.created_at).toLocaleDateString('ar-EG') }}</span>
                                    </div>

                                    <!-- Instructor Endorse Action (Instructors and Admins Only) -->
                                    <button
                                        v-if="isInstructorOrAdmin"
                                        @click="toggleEndorsement(reply.id)"
                                        :class="[
                                            'text-[11px] font-bold px-2.5 py-1 rounded-lg border transition',
                                            reply.is_instructor_endorsed
                                                ? 'border-emerald-400 bg-white text-emerald-700 hover:bg-emerald-50'
                                                : 'border-slate-300 bg-white text-slate-600 hover:border-emerald-400 hover:text-emerald-700'
                                        ]"
                                    >
                                        {{ reply.is_instructor_endorsed ? 'إلغاء الاعتماد' : 'اعتماد كإجابة موثقة' }}
                                    </button>
                                </div>

                                <div class="text-sm leading-relaxed text-slate-700 whitespace-pre-line pt-1">
                                    {{ reply.body }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add Reply Form -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900">إضافة رد أو إجابة</h3>

                    <form v-if="page.props.auth.user" @submit.prevent="submitReply" class="mt-3 space-y-3">
                        <textarea
                            v-model="replyForm.body"
                            rows="4"
                            placeholder="اكتب إجابتك أو توضيحك لمساعدة زميلك في المساق..."
                            required
                            class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:border-primary focus:outline-none"
                        ></textarea>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="replyForm.processing"
                                class="rounded-xl bg-primary px-6 py-2.5 text-xs font-bold text-white shadow hover:bg-primary-hover disabled:opacity-50"
                            >
                                {{ replyForm.processing ? 'جاري الإرسال...' : 'إرسال الرد' }}
                            </button>
                        </div>
                    </form>

                    <div v-else class="mt-3 rounded-xl bg-slate-50 p-4 text-center border border-slate-200">
                        <p class="text-xs text-slate-600">يرجى تسجيل الدخول للمشاركة في النقاش وإضافة ردك.</p>
                        <Link :href="route('login')" class="mt-2 inline-block rounded-lg bg-primary px-4 py-1.5 text-xs font-bold text-white shadow hover:bg-primary-hover">
                            تسجيل الدخول
                        </Link>
                    </div>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
