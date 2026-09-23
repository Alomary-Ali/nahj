<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();
</script>

<template>
    <Head title="إعدادات الحساب" />

    <div class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased flex flex-col justify-between">
        <Navbar />

        <!-- Breadcrumbs Navigation -->
        <div class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center gap-2 px-4 py-3 text-xs text-slate-500 sm:px-6">
                <Link :href="route('home')" class="hover:text-primary">الرئيسية</Link>
                <span class="rtl:rotate-180">/</span>
                <Link :href="route('dashboard')" class="hover:text-primary">تعلّمي</Link>
                <span class="rtl:rotate-180">/</span>
                <span class="font-bold text-slate-700">إعدادات الحساب</span>
            </div>
        </div>

        <main class="flex-1 py-10">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 space-y-8">
                <!-- Page Title -->
                <div>
                    <h1 class="text-2xl font-black text-slate-900 font-arabic sm:text-3xl">إعدادات الحساب والملف الشخصي</h1>
                    <p class="mt-1 text-xs text-slate-500">
                        إدارة بياناتك الشخصية، كلمة المرور، وتفضيلات الأمان لحسابك في منصة نهج
                    </p>
                </div>

                <div class="space-y-6">
                    <!-- Update Profile Form -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                        />
                    </div>

                    <!-- Update Password Form -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <UpdatePasswordForm />
                    </div>

                    <!-- Delete Account Form -->
                    <div class="rounded-2xl border border-red-100 bg-white p-6 shadow-sm sm:p-8">
                        <DeleteUserForm />
                    </div>
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
