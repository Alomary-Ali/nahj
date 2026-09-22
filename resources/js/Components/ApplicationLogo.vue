<script setup lang="ts">
withDefaults(
    defineProps<{
        variant?: 'icon' | 'horizontal' | 'full';
        alt?: string;
    }>(),
    {
        variant: 'horizontal',
        alt: 'منصة نهج - Nahj',
    }
);
</script>

<template>
    <img
        v-if="variant === 'icon'"
        src="/images/nahj-icon.png"
        :alt="alt"
        class="object-contain"
    />
    <img
        v-else-if="variant === 'full'"
        src="/images/nahj-logo-transparent.png"
        :alt="alt"
        class="object-contain"
    />
    <img
        v-else
        src="/images/nahj-horizontal.png"
        :alt="alt"
        class="object-contain"
    />
</template>
