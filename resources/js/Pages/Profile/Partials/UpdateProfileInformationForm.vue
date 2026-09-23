<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: Boolean;
    status?: String;
}>();

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-base font-bold text-slate-900 font-arabic">
                معلومات الحساب الأساسية
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                قم بتحديث اسمك المعروض على الشهادات وعنوان بريدك الإلكتروني المستخدم لتسجيل الدخول.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-4 max-w-xl"
        >
            <div>
                <InputLabel for="name" value="الاسم الكامل (يظهر على الشهادات الرسمية)" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-1" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="عنوان البريد الإلكتروني" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-xs text-amber-800">
                    عنوان بريدك الإلكتروني غير مفعّل بعد.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="ms-1 font-bold text-primary underline hover:text-primary-hover"
                    >
                        اضغط هنا لإعادة إرسال رابط التفعيل.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-xs font-bold text-emerald-700"
                >
                    تم إرسال رابط تفعيل جديد إلى بريدك الإلكتروني بنجاح.
                </div>
            </div>

            <div class="flex items-center gap-4 pt-2">
                <PrimaryButton :disabled="form.processing">
                    {{ form.processing ? 'جاري الحفظ...' : 'حفظ التغييرات' }}
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
                        <span>تم حفظ التعديلات بنجاح.</span>
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
