<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="تعيين كلمة المرور الجديدة" />

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-black text-slate-900 font-arabic">تعيين كلمة المرور الجديدة</h1>
            <p class="mt-1 text-xs text-slate-500">
                أدخل كلمة المرور الجديدة لحسابك لتتمكن من تسجيل الدخول
            </p>
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
                />

                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="كلمة المرور الجديدة" />

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
                <InputLabel
                    for="password_confirmation"
                    value="تأكيد كلمة المرور الجديدة"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="أعد إدخال كلمة المرور"
                />

                <InputError
                    class="mt-1"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full py-3"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'جاري الحفظ...' : 'حفظ كلمة المرور وتسجيل الدخول' }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
