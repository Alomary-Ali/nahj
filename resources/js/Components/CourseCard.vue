<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface CourseProps {
    id: number;
    ulid: string;
    title: string;
    slug: string;
    headline?: string;
    thumbnail?: string;
    level: string;
    duration_hours: number;
    price: string | number;
    category?: {
        name: string;
        slug: string;
    };
    instructor_id?: number;
    instructor?: {
        id?: number;
        name: string;
    };
    modules_count?: number;
}

defineProps<{
    course: CourseProps;
}>();

function formatLevel(level: string): string {
    switch (level) {
        case 'beginner': return 'مبتدئ';
        case 'intermediate': return 'متوسط';
        case 'advanced': return 'متقدم';
        default: return 'كافة المستويات';
    }
}
</script>

<template>
    <div class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-primary/40 hover:shadow-lg">
        <!-- Thumbnail 16:9 -->
        <Link :href="route('courses.show', course.slug)" class="relative aspect-video w-full overflow-hidden bg-slate-100">
            <img
                :src="course.thumbnail || 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=600&auto=format&fit=crop'"
                :alt="course.title"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100" />
            
            <div v-if="course.category" class="absolute bottom-2.5 start-2.5">
                <span class="rounded-md bg-white/95 px-2.5 py-1 text-xs font-bold text-primary shadow backdrop-blur-sm">
                    {{ course.category.name }}
                </span>
            </div>
        </Link>

        <!-- Content Body -->
        <div class="flex flex-1 flex-col p-5">
            <!-- Instructor Info -->
            <div class="mb-2 flex items-center gap-2">
                <Link
                    v-if="course.instructor?.id || course.instructor_id"
                    :href="route('instructors.show', course.instructor?.id || course.instructor_id!)"
                    class="flex items-center gap-2 text-xs font-medium text-slate-500 hover:text-primary transition"
                >
                    <div class="flex h-5 w-5 items-center justify-center rounded-full bg-primary-100 font-bold text-[10px] text-primary">
                        {{ course.instructor?.name?.charAt(0) || 'C' }}
                    </div>
                    <span>{{ course.instructor?.name || 'جامعة معتمدة' }}</span>
                </Link>
                <div v-else class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    <div class="flex h-5 w-5 items-center justify-center rounded-full bg-primary-100 font-bold text-[10px] text-primary">
                        {{ course.instructor?.name?.charAt(0) || 'C' }}
                    </div>
                    <span>{{ course.instructor?.name || 'جامعة معتمدة' }}</span>
                </div>
            </div>

            <!-- Course Title -->
            <Link :href="route('courses.show', course.slug)">
                <h3 class="line-clamp-2 text-base font-bold text-slate-900 transition group-hover:text-primary leading-snug">
                    {{ course.title }}
                </h3>
            </Link>

            <!-- Headline / Subtitle -->
            <p v-if="course.headline" class="mt-1 line-clamp-2 text-xs text-slate-600 leading-relaxed">
                {{ course.headline }}
            </p>

            <div class="mt-auto pt-4">
                <!-- Metadata Badges -->
                <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-0.5 font-medium text-slate-700">
                        <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        {{ formatLevel(course.level) }}
                    </span>

                    <span v-if="course.duration_hours" class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-0.5 font-medium text-slate-700">
                        <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ course.duration_hours }} ساعة
                    </span>
                </div>

                <!-- Footer / Price & Certificate -->
                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                        <svg class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        شهادة معتمدة
                    </span>

                    <span class="text-sm font-black text-slate-900">
                        {{ Number(course.price) === 0 ? 'مجانـاً' : `$${Number(course.price).toFixed(2)}` }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
