<script setup lang="ts">
import { usePreferredDark } from '@vueuse/core';

const prefersDark = usePreferredDark();
</script>

<template>
    <div
        class="pointer-events-none absolute inset-0 overflow-hidden rounded-[1.25rem]"
    >
        <div
            :class="[
                'paper-grid absolute inset-0 opacity-40 transition-opacity duration-500',
                prefersDark ? 'opacity-20' : 'opacity-55',
            ]"
        />
        <div
            :class="[
                'absolute top-0 right-12 h-full w-px',
                prefersDark
                    ? 'bg-white/8'
                    : 'bg-[color-mix(in_oklab,var(--color-chart-2)_26%,transparent)]',
            ]"
        />
        <div
            :class="[
                'absolute top-8 right-8 h-10 w-10 border',
                prefersDark
                    ? 'border-white/10 bg-white/4'
                    : 'border-foreground/8 bg-[color-mix(in_oklab,var(--color-chart-4)_18%,white)]',
            ]"
        />
    </div>
</template>
