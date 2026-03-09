<script setup lang="ts">
import { computed } from 'vue';

type DonutSegment = {
    label: string;
    value: number;
    color?: string;
};

const props = withDefaults(
    defineProps<{
        segments: DonutSegment[];
        centerValue?: string;
        centerLabel?: string;
    }>(),
    {
        centerValue: '0%',
        centerLabel: 'No responses',
    },
);

const radius = 44;
const circumference = 2 * Math.PI * radius;
const palette = [
    'var(--color-chart-2)',
    'var(--color-chart-4)',
    'var(--color-chart-5)',
    'var(--color-chart-1)',
];

const total = computed<number>(() =>
    props.segments.reduce(
        (carry, segment) => carry + Math.max(segment.value, 0),
        0,
    ),
);

const arcs = computed(() => {
    let offset = 0;

    return props.segments.map((segment, index) => {
        const value = Math.max(segment.value, 0);
        const length =
            total.value === 0 ? 0 : (value / total.value) * circumference;
        const arc = {
            ...segment,
            color: segment.color ?? palette[index % palette.length],
            dasharray: `${length} ${circumference - length}`,
            dashoffset: -offset,
        };

        offset += length;

        return arc;
    });
});
</script>

<template>
    <div class="grid gap-4 md:grid-cols-[11rem_minmax(0,1fr)] md:items-center">
        <div
            class="relative mx-auto flex h-44 w-44 items-center justify-center"
        >
            <svg viewBox="0 0 120 120" class="h-full w-full -rotate-90">
                <circle
                    cx="60"
                    cy="60"
                    :r="radius"
                    class="fill-none stroke-muted"
                    stroke-width="14"
                />
                <circle
                    v-for="arc in arcs"
                    :key="arc.label"
                    cx="60"
                    cy="60"
                    :r="radius"
                    class="fill-none transition-all duration-500"
                    stroke-linecap="round"
                    stroke-width="14"
                    :stroke="arc.color"
                    :stroke-dasharray="arc.dasharray"
                    :stroke-dashoffset="arc.dashoffset"
                />
            </svg>

            <div
                class="absolute inset-0 flex flex-col items-center justify-center text-center"
            >
                <span class="text-3xl font-semibold text-foreground">
                    {{ centerValue }}
                </span>
                <span
                    class="px-6 text-xs tracking-[0.18em] text-muted-foreground uppercase"
                >
                    {{ centerLabel }}
                </span>
            </div>
        </div>

        <div class="space-y-3">
            <div
                v-for="arc in arcs"
                :key="`legend-${arc.label}`"
                class="flex items-center justify-between gap-3 rounded-[1rem] border border-border bg-background px-4 py-3"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <span
                        class="size-3 shrink-0 rounded-full"
                        :style="{ backgroundColor: arc.color }"
                    />
                    <span class="truncate text-sm text-foreground">
                        {{ arc.label }}
                    </span>
                </div>
                <span class="text-sm font-medium text-foreground">
                    {{ arc.value }}
                </span>
            </div>
        </div>
    </div>
</template>
