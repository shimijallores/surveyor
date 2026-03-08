<script setup lang="ts">
type Props = {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
};

withDefaults(defineProps<Props>(), {
    variant: 'default',
});
</script>

<template>
    <header :class="variant === 'small' ? '' : 'mb-6 space-y-2'">
        <h2
            :class="
                variant === 'small'
                    ? 'mb-0.5 text-base font-medium'
                    : 'text-3xl font-semibold tracking-tight text-balance md:text-4xl'
            "
        >
            {{ title }}
        </h2>
        <p
            v-if="description"
            class="max-w-2xl text-sm leading-6 text-muted-foreground"
        >
            {{ description }}
        </p>
    </header>
</template>
