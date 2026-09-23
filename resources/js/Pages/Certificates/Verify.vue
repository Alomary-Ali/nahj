<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import type { Certificate } from '@/types';

const props = defineProps<{
    certificate: Certificate;
}>();

const copied = ref(false);

function printCertificate() {
    window.print();
}

function copyVerificationLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 3000);
    });
}
</script>

<template>
    <Head :title="`شهادة معتمدة: ${certificate.user?.name} - ${certificate.course?.title}`" />

    <div class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased flex flex-col print:bg-white print:p-0">
        <!-- Hide on print -->
        <div class="print:hidden">
            <Navbar />

            <!-- Breadcrumbs -->
            <div class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 text-xs sm:px-6">
                    <div class="flex items-center gap-2 text-slate-500">
                        <Link :href="route('home')" class="hover:text-primary">الرئيسية</Link>
                        <span class="rtl:rotate-180">/</span>
                        <span class="text-slate-700">التحقق من الشهادات الرسمية</span>
                        <span class="rtl:rotate-180">/</span>
                        <span class="font-mono font-bold text-primary">{{ certificate.certificate_code }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <button
                            @click="copyVerificationLink"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 font-bold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-primary"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                            <span>{{ copied ? 'تم نسخ الرابط!' : 'نسخ رابط التوثيق' }}</span>
                        </button>

                        <button
                            @click="printCertificate"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3.5 py-1.5 font-bold text-white shadow-sm transition hover:bg-primary-hover"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>طباعة / PDF</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <main class="flex-1 py-8 print:py-0">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 print:max-w-none print:p-0">
                <!-- Verification Status Banner (Hidden on Print) -->
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 shadow-sm print:hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-sm font-black text-emerald-950">شهادة رسمية موثقة ومعتمدة من منصة كورسورا</h2>
                                <p class="text-xs text-emerald-800">
                                    تم التحقق من إنجاز الطالب لكافة متطلبات المساق والاختبارات بنجاح.
                                </p>
                            </div>
                        </div>

                        <div class="text-end text-xs font-mono text-emerald-900">
                            <span class="block font-bold">كود التحقق: {{ certificate.certificate_code }}</span>
                            <span class="block text-[11px] text-emerald-700">تاريخ الإصدار: {{ new Date(certificate.issue_date).toLocaleDateString('ar-EG') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Official Certificate Sheet (Coursera Master Grade) -->
                <div class="relative overflow-hidden rounded-3xl border-8 border-slate-900 bg-white p-8 shadow-2xl sm:p-14 print:rounded-none print:border-4 print:border-black print:p-8 print:shadow-none">
                    <!-- Subtle Watermark Background -->
                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center opacity-[0.03]">
                        <span class="text-[140px] font-black uppercase tracking-widest text-slate-900">NAHJ</span>
                    </div>

                    <!-- Inner Decorative Border -->
                    <div class="relative rounded-2xl border-2 border-primary/20 p-6 sm:p-10">
                        <!-- Top Header & Crest -->
                        <div class="flex flex-col items-center justify-center text-center">
                            <div class="flex items-center gap-3">
                                <img src="/images/nahj-icon.png" alt="منصة نهج" class="h-12 w-12 object-contain" />
                                <div class="flex items-baseline gap-2">
                                    <span class="text-3xl font-black tracking-tight text-[#042F2E] font-arabic">
                                        نهج
                                    </span>
                                    <span class="text-xs font-bold tracking-widest text-primary uppercase">
                                        NAHJ
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center gap-2">
                                <div class="h-px w-12 bg-primary/40"></div>
                                <span class="text-xs font-bold uppercase tracking-widest text-slate-500">
                                    وثيقة إتمام أكاديمية معتمدة
                                </span>
                                <div class="h-px w-12 bg-primary/40"></div>
                            </div>

                            <h1 class="mt-3 text-2xl font-black text-slate-900 sm:text-4xl">
                                شهادة إنجاز واجتياز رسمي
                            </h1>
                        </div>

                        <!-- Recipient Section -->
                        <div class="mt-8 text-center sm:mt-12">
                            <p class="text-sm font-medium text-slate-500">تشهد منصة نهج التعليمية بأنّ:</p>
                            <h2 class="mt-3 text-3xl font-black text-primary sm:text-5xl font-sans tracking-tight">
                                {{ certificate.user?.name || 'الطالب المميز' }}
                            </h2>
                            <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-slate-600 sm:text-base">
                                قد اجتاز بنجاح وبدرجة تفوّق كافة المتطلبات التدريبية والتقييمات العملية المقررة لمساق:
                            </p>
                        </div>

                        <!-- Course Title Box -->
                        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50/70 p-6 text-center sm:mt-8">
                            <h3 class="text-xl font-black text-slate-900 sm:text-2xl">
                                {{ certificate.course?.title }}
                            </h3>
                            <p v-if="certificate.course?.headline" class="mt-2 text-xs text-slate-600 sm:text-sm">
                                {{ certificate.course?.headline }}
                            </p>
                            <div class="mt-4 flex flex-wrap items-center justify-center gap-3 text-xs">
                                <span class="rounded-full bg-primary-50 px-3.5 py-1 font-bold text-primary">
                                    مقرر معتمد بواقع {{ certificate.course?.duration_hours || 24 }} ساعة تعليمية
                                </span>
                                <span class="rounded-full bg-emerald-50 px-3.5 py-1 font-bold text-emerald-700">
                                    نسبة التفوّق: {{ certificate.grade_percentage }}%
                                </span>
                            </div>
                        </div>

                        <!-- Signatures & Official Digital Seal -->
                        <div class="mt-12 grid grid-cols-1 items-end gap-8 border-t border-slate-200 pt-8 sm:grid-cols-3">
                            <!-- Instructor Signature -->
                            <div class="text-center sm:text-start">
                                <div class="h-12 flex items-end justify-center sm:justify-start">
                                    <span class="font-serif italic text-xl font-bold text-slate-800 tracking-wider">
                                        {{ certificate.instructor?.name || 'Andrew Ng' }}
                                    </span>
                                </div>
                                <div class="mt-2 border-t border-slate-300 pt-1.5">
                                    <p class="text-xs font-bold text-slate-900">{{ certificate.instructor?.name || 'مدرّب المساق المعتمد' }}</p>
                                    <p class="text-[11px] text-slate-500">أستاذ المادة والمدرب الأكاديمي</p>
                                </div>
                            </div>

                            <!-- Central Golden Digital Seal -->
                            <div class="flex flex-col items-center justify-center">
                                <div class="relative flex h-20 w-20 items-center justify-center rounded-full border-4 border-amber-500 bg-amber-50 shadow-md">
                                    <svg class="h-10 w-10 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                    </svg>
                                </div>
                                <span class="mt-2 text-[10px] font-black uppercase tracking-widest text-amber-700">
                                    VERIFIED CREDENTIAL
                                </span>
                            </div>

                            <!-- Academic Director Signature -->
                            <div class="text-center sm:text-end">
                                <div class="h-12 flex items-end justify-center sm:justify-end">
                                    <span class="font-serif italic text-xl font-bold text-primary tracking-wider">
                                        Nahj Academic Council
                                    </span>
                                </div>
                                <div class="mt-2 border-t border-slate-300 pt-1.5">
                                    <p class="text-xs font-bold text-slate-900">هيئة الاعتماد الأكاديمي والتوثيق</p>
                                    <p class="text-[11px] text-slate-500">منصة نهج للتعليم والتدريب المعتمد</p>
                                </div>
                            </div>
                        </div>

                        <!-- Cryptographic Security Footer -->
                        <div class="mt-10 border-t border-slate-200/80 pt-4 text-center sm:flex sm:items-center sm:justify-between sm:text-start text-[11px] text-slate-500 font-mono">
                            <div>
                                <span class="block">رمز الوثيقة: <strong>{{ certificate.certificate_code }}</strong></span>
                                <span class="block mt-0.5 break-all text-[9px] text-slate-400">SHA-256 Hash: {{ certificate.verification_hash }}</span>
                            </div>
                            <div class="mt-2 sm:mt-0 sm:text-end">
                                <span class="block">تاريخ التوثيق: {{ new Date(certificate.issue_date).toLocaleDateString('ar-EG') }}</span>
                                <span class="block text-primary font-sans text-[10px]">التحقق الفوري متاح عالمياً عبر رمز الشهادة</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Verification Info Box (Hidden on Print) -->
                <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm print:hidden">
                    <h3 class="text-sm font-bold text-slate-900">حول التوثيق الرقمي لشهادات نهج (Nahj)</h3>
                    <p class="mt-2 text-xs text-slate-600 leading-relaxed">
                        تُصدر شهادات إتمام المقررات في منصة نهج (Nahj) بصورة مشفرة ومحمية ببصمة تشفيرية رقمية (SHA-256 Tamper-Proof Cryptographic Hash). يمكن لأصحاب العمل والجامعات التحقق من صحة هذه الشهادة مباشرة ودون وساطة عبر إدخال رمز التحقق في أي وقت.
                    </p>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <Link
                            v-if="certificate.course?.slug"
                            :href="route('courses.show', certificate.course.slug)"
                            class="text-xs font-bold text-primary hover:underline"
                        >
                            استعراض تفاصيل مقرر: {{ certificate.course?.title }}
                        </Link>
                    </div>
                </div>
            </div>
        </main>

        <div class="print:hidden">
            <Footer />
        </div>
    </div>
</template>

<style scoped>
@media print {
    @page {
        size: landscape;
        margin: 0.5cm;
    }
}
</style>
