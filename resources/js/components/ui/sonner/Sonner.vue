<script setup lang="ts">
import { Toaster as Sonner, type ToasterProps } from 'vue-sonner';
import { useAppearance } from '@/composables/useAppearance';

withDefaults(defineProps<ToasterProps>(), {
    closeButton: true,
    expand: true,
    position: 'top-right',
    richColors: true,
    visibleToasts: 4,
});

const { resolvedAppearance } = useAppearance();
</script>

<template>
    <Sonner
        :theme="resolvedAppearance"
        class="toaster group"
        :toast-options="{
            classes: {
                toast: 'group toast group-[.toaster]:border-border group-[.toaster]:bg-card group-[.toaster]:text-card-foreground group-[.toaster]:shadow-lg',
                description: 'group-[.toast]:text-muted-foreground',
                actionButton: 'group-[.toast]:bg-foreground group-[.toast]:text-background',
                cancelButton: 'group-[.toast]:bg-muted group-[.toast]:text-muted-foreground',
            },
        }"
        v-bind="$props"
    />
</template>