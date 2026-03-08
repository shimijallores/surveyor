<script setup lang="ts">
import { Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';
import { useAppearance } from '@/composables/useAppearance';

const { resolvedAppearance, updateAppearance } = useAppearance();

const nextAppearance = computed(() =>
    resolvedAppearance.value === 'dark' ? 'light' : 'dark',
);

const buttonLabel = computed(() =>
    resolvedAppearance.value === 'dark'
        ? 'Switch to light mode'
        : 'Switch to dark mode',
);

const toggleAppearance = (): void => {
    updateAppearance(nextAppearance.value);
};
</script>

<template>
    <button
        type="button"
        class="inline-flex size-10 items-center justify-center border border-border bg-background text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
        :aria-label="buttonLabel"
        :title="buttonLabel"
        @click="toggleAppearance"
    >
        <Sun v-if="resolvedAppearance === 'dark'" class="size-4" />
        <Moon v-else class="size-4" />
    </button>
</template>
