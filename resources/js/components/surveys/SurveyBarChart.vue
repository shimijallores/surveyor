<script setup lang="ts">
import { ArcElement, Chart as ChartJS, Legend, Title, Tooltip } from 'chart.js';
import type { ChartData, ChartOptions } from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import { computed } from 'vue';
import { Pie } from 'vue-chartjs';
import type { SurveyAnalyticsSegment } from '@/types';

ChartJS.register(Title, Tooltip, Legend, ArcElement, ChartDataLabels);

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

const cssColor = (name: string, fallback: string): string => {
    if (typeof window === 'undefined') {
        return fallback;
    }

    return (
        getComputedStyle(document.documentElement)
            .getPropertyValue(name)
            .trim() || fallback
    );
};

const palette = computed(() => [
    cssColor('--foreground', '#2f3640'),
    cssColor('--muted-foreground', '#68707a'),
    cssColor('--border', '#b8b0a8'),
    '#9a938b',
    '#c7c1ba',
]);

const textColor = computed(() => cssColor('--foreground', '#1f2937'));
const gridColor = computed(() => cssColor('--border', '#d6d3d1'));
const hasData = computed(() => props.segments.length > 0);
const cardColor = computed(() => cssColor('--card', '#faf7f2'));
const mutedColor = computed(() => cssColor('--muted', '#ebe5dd'));

const normalizedSegments = computed(() =>
    props.segments.map((segment, index) => ({
        label: segment.label,
        value: segment.count ?? segment.score ?? 0,
        percentage: segment.percentage,
        color: palette.value[index % palette.value.length],
    })),
);

const chartData = computed<ChartData<'pie'>>(() => ({
    labels: normalizedSegments.value.map((segment) => segment.label),
    datasets: [
        {
            label: props.metricLabel,
            data: normalizedSegments.value.map((segment) => segment.value),
            backgroundColor: normalizedSegments.value.map(
                (segment) => segment.color,
            ),
            borderColor: gridColor.value,
            borderWidth: 2,
            hoverOffset: 10,
        },
    ],
}));

const chartOptions = computed<ChartOptions<'pie'>>(() => ({
    responsive: true,
    maintainAspectRatio: false,
    animation: {
        duration: 500,
    },
    plugins: {
        legend: {
            display: false,
        },
        title: {
            display: false,
        },
        datalabels: {
            color: textColor.value,
            backgroundColor: cardColor.value,
            borderColor: mutedColor.value,
            borderWidth: 1,
            borderRadius: 999,
            padding: {
                top: 4,
                right: 8,
                bottom: 4,
                left: 8,
            },
            font: {
                weight: 700,
                size: 11,
            },
            textAlign: 'center',
            formatter: (value, context) => {
                const total = normalizedSegments.value.reduce(
                    (carry, segment) => carry + segment.value,
                    0,
                );

                if (total === 0 || Number(value) === 0) {
                    return '';
                }

                const percentage = Math.round((Number(value) / total) * 100);
                const label = String(
                    context.chart.data.labels?.[context.dataIndex] ?? '',
                );

                if (percentage < 8) {
                    return `${percentage}%`;
                }

                return `${label}\n${percentage}%`;
            },
        },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.92)',
            titleColor: '#f8fafc',
            bodyColor: '#f8fafc',
            padding: 12,
            displayColors: true,
            callbacks: {
                label: (context) => {
                    const value = Number(context.raw ?? 0);
                    const percentage =
                        normalizedSegments.value[context.dataIndex]?.percentage;

                    if (percentage === undefined) {
                        return `${value} ${props.metricLabel}`;
                    }

                    return `${value} ${props.metricLabel} (${percentage}%)`;
                },
            },
        },
    },
    color: textColor.value,
}));

const ariaLabel = computed(() => {
    if (!hasData.value) {
        return props.emptyLabel;
    }

    return normalizedSegments.value
        .map((segment) => `${segment.label}: ${segment.value}`)
        .join(', ');
});
</script>

<template>
    <div v-if="hasData" class="space-y-4">
        <div
            class="grid gap-4 rounded-[1rem] border border-border bg-background p-4 lg:grid-cols-[minmax(16rem,20rem)_minmax(0,1fr)] lg:items-start"
        >
            <div class="mx-auto h-[18rem] w-full max-w-[18rem] lg:mx-0">
                <Pie
                    :data="chartData"
                    :options="chartOptions"
                    :aria-label="ariaLabel"
                >
                    {{ emptyLabel }}
                </Pie>
            </div>

            <div class="grid gap-2 sm:grid-cols-2 lg:self-stretch">
                <div
                    v-for="segment in normalizedSegments"
                    :key="segment.label"
                    class="flex items-start justify-between gap-3 rounded-[1rem] border border-border bg-card px-4 py-3"
                >
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-foreground">
                            {{ segment.label }}
                        </p>
                        <p
                            v-if="segment.percentage !== undefined"
                            class="text-xs text-muted-foreground"
                        >
                            {{ segment.percentage }}% of {{ metricLabel }}
                        </p>
                    </div>
                    <span class="shrink-0 text-sm font-medium text-foreground">
                        {{ segment.value }}
                    </span>
                </div>
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
