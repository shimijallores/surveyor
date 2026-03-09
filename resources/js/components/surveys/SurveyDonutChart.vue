<script setup lang="ts">
import {
    ArcElement,
    Chart as ChartJS,
    DoughnutController,
    Legend,
    Tooltip,
} from 'chart.js';
import type { ChartData, ChartOptions } from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';

ChartJS.register(
    DoughnutController,
    ArcElement,
    Tooltip,
    Legend,
    ChartDataLabels,
);

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
    cssColor('--chart-2', '#2368c4'),
    cssColor('--chart-4', '#e2af2f'),
    cssColor('--chart-5', '#3d8f66'),
    cssColor('--chart-1', '#e25f44'),
]);

const textColor = computed(() => cssColor('--foreground', '#1f2937'));
const gridColor = computed(() => cssColor('--border', '#d6d3d1'));
const cardColor = computed(() => cssColor('--card', '#faf7f2'));

const normalizedSegments = computed(() =>
    props.segments.map((segment, index) => ({
        ...segment,
        color: segment.color ?? palette.value[index % palette.value.length],
        value: Math.max(segment.value, 0),
    })),
);

const hasData = computed(() =>
    normalizedSegments.value.some((segment) => segment.value > 0),
);

const chartData = computed<ChartData<'doughnut'>>(() => ({
    labels: normalizedSegments.value.map((segment) => segment.label),
    datasets: [
        {
            data: normalizedSegments.value.map((segment) => segment.value),
            backgroundColor: normalizedSegments.value.map(
                (segment) => segment.color,
            ),
            borderColor: gridColor.value,
            borderWidth: 2,
            hoverOffset: 8,
        },
    ],
}));

const chartOptions = computed<ChartOptions<'doughnut'>>(() => ({
    responsive: true,
    maintainAspectRatio: false,
    cutout: '68%',
    animation: {
        duration: 500,
    },
    plugins: {
        legend: {
            display: false,
        },
        datalabels: {
            color: textColor.value,
            backgroundColor: `${cardColor.value}F2`,
            borderColor: `${gridColor.value}AA`,
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
            anchor: 'end',
            align: 'end',
            offset: 8,
            clamp: true,
            formatter: (value, context) => {
                const dataset = context.dataset.data as number[];
                const total = dataset.reduce(
                    (carry, item) => carry + Number(item),
                    0,
                );

                if (total === 0 || Number(value) === 0) {
                    return '';
                }

                const percentage = Math.round((Number(value) / total) * 100);
                const label = String(
                    context.chart.data.labels?.[context.dataIndex] ?? '',
                );

                return `${label}: ${percentage}%`;
            },
        },
        tooltip: {
            backgroundColor: 'rgba(15, 23, 42, 0.92)',
            titleColor: '#f8fafc',
            bodyColor: '#f8fafc',
            padding: 12,
            callbacks: {
                label: (context) => {
                    const dataset = context.dataset.data as number[];
                    const total = dataset.reduce(
                        (carry, value) => carry + value,
                        0,
                    );
                    const value = Number(context.raw ?? 0);
                    const percentage =
                        total === 0 ? 0 : Math.round((value / total) * 100);

                    return `${context.label}: ${value} (${percentage}%)`;
                },
            },
        },
    },
    color: textColor.value,
}));

const ariaLabel = computed(() => {
    if (!hasData.value) {
        return props.centerLabel;
    }

    return normalizedSegments.value
        .map((segment) => `${segment.label}: ${segment.value}`)
        .join(', ');
});
</script>

<template>
    <div class="grid gap-4 md:grid-cols-[14rem_minmax(0,1fr)] md:items-center">
        <div class="relative mx-auto h-56 w-full max-w-[14rem]">
            <Doughnut
                :data="chartData"
                :options="chartOptions"
                :aria-label="ariaLabel"
            >
                {{ centerLabel }}
            </Doughnut>

            <div
                class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center"
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

        <div class="grid gap-3">
            <div
                v-for="segment in normalizedSegments"
                :key="`legend-${segment.label}`"
                class="flex items-center justify-between gap-3 rounded-[1rem] border border-border bg-background px-4 py-3"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <span
                        class="size-3 shrink-0 rounded-full"
                        :style="{ backgroundColor: segment.color }"
                    />
                    <span class="truncate text-sm text-foreground">
                        {{ segment.label }}
                    </span>
                </div>
                <span class="text-sm font-medium text-foreground">
                    {{ segment.value }}
                </span>
            </div>
        </div>
    </div>
</template>
