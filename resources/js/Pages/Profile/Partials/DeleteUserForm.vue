<script setup lang="ts">
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => {
            form.reset();
        },
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-4">
        <header>
            <h2 class="text-base font-bold text-red-600 font-arabic">
                حذف الحساب نهائياً
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                بمجرد حذف حسابك، سيتم مسح كافة بياناتك وسجلات تقدمك والشهادات الصادرة نهائياً دون إمكانية للاسترجاع.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">
            حذف حسابي من المنصة
        </DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-base font-bold text-slate-900 font-arabic">
                    هل أنت متأكد من رغبتك في حذف الحساب نهائياً؟
                </h2>

                <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                    هذا الإجراء نهائي ولا يمكن التراجع عنه. يرجى إدخال كلمة المرور لتأكيد الحذف النهائي لحسابك.
                </p>

                <div class="mt-4">
                    <InputLabel
                        for="password"
                        value="كلمة المرور"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        placeholder="أدخل كلمة المرور للتأكيد"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-1" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal">
                        إلغاء الأمر
                    </SecondaryButton>

                    <DangerButton
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        {{ form.processing ? 'جاري الحذف...' : 'تأكيد الحذف النهائي' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
