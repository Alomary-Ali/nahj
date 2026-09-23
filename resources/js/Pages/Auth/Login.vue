<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="تسجيل الدخول" />

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-black text-slate-900 font-arabic">تسجيل الدخول</h1>
            <p class="mt-1 text-xs text-slate-500">
                مرحباً بعودتك! سجّل دخولك لمتابعة دوراتك وشهاداتك الأكاديمية
            </p>
        </div>

        <div v-if="status" class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 p-3 text-xs font-medium text-emerald-800">
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

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <InputLabel for="password" value="كلمة المرور" class="!mb-0" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs font-semibold text-primary hover:underline"
                    >
                        نسيت كلمة المرور؟
                    </Link>
                </div>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <div class="pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="text-xs text-slate-600">تذكر تسجيل دخولي على هذا الجهاز</span>
                </label>
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full py-3"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'جاري التحقق...' : 'تسجيل الدخول' }}
                </PrimaryButton>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-4 text-center text-xs text-slate-500">
                <span>ليس لديك حساب بعد؟</span>
                <Link
                    :href="route('register')"
                    class="ms-1 font-bold text-primary hover:underline"
                >
                    أنشئ حسابك مجاناً
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
