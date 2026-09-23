<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="تأكيد كلمة المرور" />

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-black text-slate-900 font-arabic">منطقة آمنة</h1>
            <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                هذه منطقة محمية من النظام. يرجى تأكيد كلمة المرور للمتابعة.
            </p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="password" value="كلمة المرور" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                    placeholder="••••••••"
                />
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <div class="pt-2">
                <PrimaryButton
                    class="w-full py-3"
                    :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'جاري التأكيد...' : 'تأكيد ومتابعة' }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
