<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import type { PageProps, CourseSummary } from '@/types';

const props = defineProps<{
    course: CourseSummary & {
        price: number | string;
        category?: { name: string; slug: string };
        instructor?: { name: string };
    };
}>();

const page = usePage<PageProps>();

const form = useForm({
    payment_method: 'card',
});

const isProcessing = ref(false);

function processEnrollment() {
    if (!page.props.auth.user) {
        return;
    }

    isProcessing.value = true;
    form.post(route('courses.enroll', props.course.slug), {
        onFinish: () => {
            isProcessing.value = false;
        },
    });
}
</script>

<template>
    <Head :title="`إتمام التسجيل: ${course.title}`" />

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
                <span class="font-bold text-slate-700">إتمام التسجيل والدفع</span>
            </div>
        </div>

        <main class="flex-1 py-10">
            <div class="mx-auto max-w-5xl px-4 sm:px-6">
                <!-- Secure Checkout Header -->
                <div class="mb-8 flex items-center justify-between border-b border-slate-200 pb-4">
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 sm:text-3xl">إتمام التسجيل الأكاديمي</h1>
                        <p class="mt-1 text-xs text-slate-500">تسجيل آمن ومشفر عبر بوابة الدفع المعتمدة لمنصة كورسورا</p>
                    </div>

                    <div class="hidden items-center gap-2 rounded-full bg-emerald-50 px-3.5 py-1.5 text-xs font-bold text-emerald-700 sm:flex">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>اتصال آمن ومشفّر 256-bit</span>
                    </div>
                </div>

                <!-- Guest Notice -->
                <div v-if="!page.props.auth.user" class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 p-6 text-center shadow-sm">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h2 class="mt-3 text-base font-bold text-amber-900">يلزم تسجيل الدخول لإتمام التسجيل بالمقرر</h2>
                    <p class="mt-1 text-xs text-amber-800">
                        يرجى تسجيل الدخول بحسابك أو إنشاء حساب جديد لحفظ تسجيلك وشهاداتك المعتمدة.
                    </p>
                    <div class="mt-4 flex justify-center gap-3">
                        <Link :href="route('login')" class="rounded-lg bg-primary px-6 py-2.5 text-xs font-bold text-white shadow hover:bg-primary-hover">
                            تسجيل الدخول
                        </Link>
                        <Link :href="route('register')" class="rounded-lg border border-slate-300 bg-white px-6 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50">
                            إنشاء حساب جديد
                        </Link>
                    </div>
                </div>

                <!-- Checkout Grid Layout -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Left: Course details & Payment method (2 cols) -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Course Summary Card -->
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">تفاصيل المساق المختار</span>
                            <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center">
                                <div class="relative h-24 w-40 shrink-0 overflow-hidden rounded-xl bg-slate-900">
                                    <img
                                        :src="course.thumbnail || 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=600&auto=format&fit=crop'"
                                        :alt="course.title"
                                        class="h-full w-full object-cover"
                                    />
                                </div>
                                <div class="flex-1">
                                    <span v-if="course.category" class="rounded bg-primary-50 px-2 py-0.5 text-[11px] font-bold text-primary">
                                        {{ course.category.name }}
                                    </span>
                                    <h3 class="mt-1.5 text-base font-black text-slate-900 leading-snug">
                                        {{ course.title }}
                                    </h3>
                                    <p class="mt-1 text-xs text-slate-500">
                                        يقدمه: <strong class="text-slate-700">{{ course.instructor?.name || 'جامعة معتمدة' }}</strong> | المدة: {{ course.duration_hours || 24 }} ساعة
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method Card -->
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h3 class="text-base font-black text-slate-900">طريقة الدفع والتسجيل</h3>
                            <p class="mt-1 text-xs text-slate-500">اختر وسيلة الدفع المناسبة لك لإتمام عملية الاشتراك فوراً</p>

                            <div class="mt-5 space-y-3">
                                <!-- Option: Card -->
                                <label
                                    :class="[
                                        'flex cursor-pointer items-center justify-between rounded-xl border p-4 transition',
                                        form.payment_method === 'card'
                                            ? 'border-primary bg-primary-50/30 text-primary-900 ring-1 ring-primary'
                                            : 'border-slate-200 bg-slate-50/50 hover:bg-white'
                                    ]"
                                    @click="form.payment_method = 'card'"
                                >
                                    <div class="flex items-center gap-3">
                                        <input
                                            type="radio"
                                            v-model="form.payment_method"
                                            value="card"
                                            class="h-4 w-4 text-primary focus:ring-primary"
                                        />
                                        <div>
                                            <span class="block text-sm font-bold text-slate-800">بطاقة ائتمان / مدى (Credit & Debit Cards)</span>
                                            <span class="block text-xs text-slate-500">Visa, MasterCard, Mada, American Express</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-mono font-bold text-slate-600">VISA</span>
                                        <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-mono font-bold text-slate-600">MasterCard</span>
                                    </div>
                                </label>

                                <!-- Mock Card Details Input (Simulated) -->
                                <div v-if="form.payment_method === 'card'" class="mt-4 rounded-xl border border-slate-200 bg-slate-50/70 p-4 space-y-3">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700">رقم البطاقة (Card Number)</label>
                                        <input
                                            type="text"
                                            readonly
                                            value="•••• •••• •••• 4242 (بطاقة تجريبية معتمدة)"
                                            class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-mono text-slate-700 focus:outline-none"
                                        />
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700">تاريخ الانتهاء</label>
                                            <input
                                                type="text"
                                                readonly
                                                value="12/28"
                                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-mono text-slate-700 focus:outline-none"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700">رمز الأمان (CVC)</label>
                                            <input
                                                type="text"
                                                readonly
                                                value="123"
                                                class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-mono text-slate-700 focus:outline-none"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Trust and Guarantee Badges -->
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white p-3 text-xs text-slate-600">
                                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>شهادة معتمدة موثقة رقمياً</span>
                            </div>
                            <div class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white p-3 text-xs text-slate-600">
                                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>وصول كامل لكافة المحتويات مدى الحياة</span>
                            </div>
                            <div class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-white p-3 text-xs text-slate-600">
                                <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span>ضمان استرجاع الأموال خلال 14 يوماً</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Order Summary Sticky Card (1 col) -->
                    <div>
                        <div class="sticky top-24 rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                            <h3 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">ملخص الطلب</h3>

                            <div class="mt-4 space-y-3 text-xs">
                                <div class="flex items-center justify-between text-slate-600">
                                    <span>سعر المقرر الأساسي:</span>
                                    <span class="font-bold text-slate-800">
                                        {{ Number(course.price) === 0 ? 'مجانـاً' : `$${Number(course.price).toFixed(2)}` }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-slate-600">
                                    <span>رسوم التوثيق والشهادة:</span>
                                    <span class="font-bold text-emerald-600">مجاناً (مشمولة)</span>
                                </div>
                                <div class="flex items-center justify-between text-slate-600">
                                    <span>الضرائب المقدرة:</span>
                                    <span class="font-bold text-slate-800">$0.00</span>
                                </div>

                                <div class="border-t border-slate-200 pt-4 flex items-baseline justify-between">
                                    <span class="text-sm font-black text-slate-900">المبلغ الإجمالي المطلوب:</span>
                                    <span class="text-2xl font-black text-primary">
                                        {{ Number(course.price) === 0 ? 'مجانـاً' : `$${Number(course.price).toFixed(2)}` }}
                                    </span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <button
                                v-if="page.props.auth.user"
                                @click="processEnrollment"
                                :disabled="form.processing || isProcessing"
                                class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-primary py-3.5 text-center text-sm font-bold text-white shadow-lg shadow-primary/25 transition hover:bg-primary-hover disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg
                                    v-if="form.processing || isProcessing"
                                    class="h-4 w-4 animate-spin text-white"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span>{{ Number(course.price) === 0 ? 'تأكيد التسجيل المجاني' : 'دفع الرسوم وتأكيد التسجيل' }}</span>
                            </button>

                            <Link
                                v-else
                                :href="route('login')"
                                class="mt-6 flex w-full items-center justify-center rounded-xl bg-primary py-3.5 text-center text-sm font-bold text-white shadow hover:bg-primary-hover"
                            >
                                تسجيل الدخول للمتابعة
                            </Link>

                            <p class="mt-4 text-center text-[11px] text-slate-400">
                                بالنقر على الزر أعلاه، فإنك توافق على شروط الخدمة وسياسة الخصوصية الخاصة بمنصة نهج (Nahj).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
