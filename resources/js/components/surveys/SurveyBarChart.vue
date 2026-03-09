<script setup lang="ts">
import { computed } from 'vue';
import type { SurveyAnalyticsSegment } from '@/types';

const props = withDefaults(
    defineProps<{
        segments: SurveyAnalyticsSegment[];
        metricLabel?: string;
        emptyLabel?: string;
    }>(),
    {
        metricLabel: 'responses',
        emptyLabel: 'No data available yet.',
    },
);

const palette = [
    'var(--color-chart-2)',
    'var(--color-chart-4)',
    'var(--color-chart-5)',
    'var(--color-chart-1)',
    'var(--color-chart-3)',
];

const segmentValue = (segment: SurveyAnalyticsSegment): number =>
    segment.count ?? segment.score ?? 0;

const maxValue = computed<number>(() => {
    if (props.segments.length === 0) {
        return 1;
    }

    return Math.max(...props.segments.map(segmentValue), 1);
});

const coloredSegments = computed(() =>
    props.segments.map((segment, index) => ({
        ...segment,
        value: segmentValue(segment),
        color: palette[index % palette.length],
    })),
);
</script>

<template>
    <div v-if="coloredSegments.length" class="space-y-3">
        <div
            v-for="segment in coloredSegments"
            :key="segment.label"
            class="space-y-2"
        >
            <div class="flex items-start justify-between gap-3 text-sm">
                <div class="min-w-0">
                    <p class="truncate font-medium text-foreground">
                        {{ segment.label }}
                    </p>
                    <p
                        v-if="segment.percentage !== undefined"
                        class="text-xs text-muted-foreground"
                    >
                        {{ segment.percentage }}% of {{ metricLabel }}
                    </p>
                </div>
                <span class="shrink-0 font-medium text-foreground">
                    {{ segment.value }}
                </span>
            </div>

            <div class="h-3 overflow-hidden rounded-full bg-muted">
                <div
                    class="h-full rounded-full transition-all duration-500"
                    :style="{
                        width: `${((segment.value / maxValue) * 100).toFixed(2)}%`,
                        backgroundColor: segment.color,
                    }"
                />
            </div>
        </div>
    </div>

    <div
        v-else
        class="rounded-[1rem] border border-dashed border-border bg-background px-4 py-6 text-sm text-muted-foreground"
    >
        {{ emptyLabel }}
    </div>
</template>
