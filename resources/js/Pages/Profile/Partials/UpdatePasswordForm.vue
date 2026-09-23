<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-base font-bold text-slate-900 font-arabic">
                تحديث كلمة المرور
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                تأكد من اختيار كلمة مرور قوية تحتوي على رموز وأرقام لحماية حسابك التعليمي.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-4 max-w-xl">
            <div>
                <InputLabel for="current_password" value="كلمة المرور الحالية" />

                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                    placeholder="••••••••"
                />

                <InputError
                    :message="form.errors.current_password"
                    class="mt-1"
                />
            </div>

            <div>
                <InputLabel for="password" value="كلمة المرور الجديدة" />

                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    placeholder="8 رموز أو أكثر"
                />

                <InputError :message="form.errors.password" class="mt-1" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="تأكيد كلمة المرور الجديدة"
                />

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                    placeholder="أعد إدخال كلمة المرور الجديدة"
                />

                <InputError
                    :message="form.errors.password_confirmation"
                    class="mt-1"
                />
            </div>

            <div class="flex items-center gap-4 pt-2">
                <PrimaryButton :disabled="form.processing">
                    {{ form.processing ? 'جاري الحفظ...' : 'تحديث كلمة المرور' }}
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out duration-300"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out duration-300"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-xs font-bold text-emerald-600 flex items-center gap-1"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>تم تحديث كلمة المرور بنجاح.</span>
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
