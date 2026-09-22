<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="border-t border-slate-200 bg-white pt-12 pb-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-4 lg:grid-cols-5">
                <div class="col-span-2">
                    <Link :href="route('home')" class="flex items-center gap-2.5">
                        <img src="/images/nahj-icon.png" alt="شعار نهج" class="h-9 w-9 object-contain" />
                        <div class="flex flex-col -space-y-1">
                            <span class="text-xl font-black text-[#0a3d7a] font-arabic">
                                نهج
                            </span>
                            <span class="text-[9px] font-bold tracking-wider text-primary uppercase">
                                nahj
                            </span>
                        </div>
                    </Link>
                    <p class="mt-3 max-w-sm text-xs leading-relaxed text-slate-500">
                        نهج - منصة تعليمية رائدة نحو مستقبلك، تقدم مساقات وتخصصات معتمدة بشراكة مع كبرى الجامعات والخبراء لنقل المعرفة وبناء المهارات للمستقبل.
                    </p>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">المقررات والتخصصات</h4>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600">
                        <li><a href="#" class="hover:text-primary">الذكاء الاصطناعي</a></li>
                        <li><a href="#" class="hover:text-primary">علم البيانات</a></li>
                        <li><a href="#" class="hover:text-primary">علوم الحاسوب</a></li>
                        <li><a href="#" class="hover:text-primary">تصميم تجربة المستخدم</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">المجتمع والشركاء</h4>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600">
                        <li><a href="#" class="hover:text-primary">انضم كمدرب</a></li>
                        <li><a href="#" class="hover:text-primary">نهج للأعمال (Nahj for Business)</a></li>
                        <li><a href="#" class="hover:text-primary">شراكات الجامعات</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">الدعم والمساعدة</h4>
                    <ul class="mt-3 space-y-2 text-xs text-slate-600">
                        <li><a href="#" class="hover:text-primary">مركز المساعدة</a></li>
                        <li><a href="#" class="hover:text-primary">التحقق من الشهادات</a></li>
                        <li><a href="#" class="hover:text-primary">الشروط والأحكام</a></li>
                        <li><a href="#" class="hover:text-primary">سياسة الخصوصية</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 border-t border-slate-100 pt-6 text-center text-xs text-slate-400">
                &copy; {{ new Date().getFullYear() }} نهج - Nahj. جميع الحقوق محفوظة.
            </div>
        </div>
    </footer>
</template>
