<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="إنشاء حساب جديد" />

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-black text-slate-900 font-arabic">إنشاء حساب جديد</h1>
            <p class="mt-1 text-xs text-slate-500">
                انضم إلى نخبة الطلاب والمتعلمين في نهج وابدأ رحلتك الأكاديمية
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="name" value="الاسم الكامل" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="مثال: أحمد عبد الله"
                />

                <InputError class="mt-1" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="البريد الإلكتروني" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="name@example.com"
                />

                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="كلمة المرور" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="8 رموز أو أكثر"
                />

                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="تأكيد كلمة المرور" />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="أعد إدخال كلمة المرور"
                />

                <InputError class="mt-1" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full py-3"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'جاري إنشاء الحساب...' : 'إنشاء الحساب وبدء التعلّم' }}
                </PrimaryButton>
            </div>

            <div class="mt-6 border-t border-slate-100 pt-4 text-center text-xs text-slate-500">
                <span>لديك حساب بالفعل؟</span>
                <Link
                    :href="route('login')"
                    class="ms-1 font-bold text-primary hover:underline"
                >
                    تسجيل الدخول
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
