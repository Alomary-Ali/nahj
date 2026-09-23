<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="استعادة كلمة المرور" />

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-black text-slate-900 font-arabic">استعادة كلمة المرور</h1>
            <p class="mt-1 text-xs text-slate-500 leading-relaxed">
                نسيت كلمة المرور؟ لا تقلق، أدخل بريدك الإلكتروني المسجل وسنرسل لك رابطاً لإعادة تعيينها بكل سهولة.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-xs font-medium text-emerald-800"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="البريد الإلكتروني" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="name@example.com"
                />

                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full py-3"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'جاري الإرسال...' : 'إرسال رابط إعادة التعيين' }}
                </PrimaryButton>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-4 text-center text-xs text-slate-500">
                <Link
                    :href="route('login')"
                    class="font-bold text-primary hover:underline"
                >
                    العودة لصفحة تسجيل الدخول
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
