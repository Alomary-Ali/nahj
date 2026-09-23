<script setup lang="ts">
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="تأكيد البريد الإلكتروني" />

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-black text-slate-900 font-arabic">تأكيد البريد الإلكتروني</h1>
            <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                شكراً لانضمامك إلى نهج! قبل البدء، يرجى تأكيد بريدك الإلكتروني بالنقر على الرابط الذي أرسلناه إليك. إذا لم يصلك البريد، يسعدنا إعادة إرساله لك فوراً.
            </p>
        </div>

        <div
            class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-xs font-medium text-emerald-800"
            v-if="verificationLinkSent"
        >
            تم إرسال رابط تأكيد جديد إلى عنوان بريدك الإلكتروني المسجل.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                <PrimaryButton
                    class="w-full sm:w-auto"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'جاري الإرسال...' : 'إعادة إرسال رابط التأكيد' }}
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-xs font-bold text-slate-500 hover:text-red-600 transition"
                >
                    تسجيل الخروج
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
