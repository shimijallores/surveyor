<script setup lang="ts">
import { MessageSquareQuote, Scale, SlidersHorizontal } from 'lucide-vue-next';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { SurveyAnalyticsQuestion } from '@/types';

defineProps<{
    question: SurveyAnalyticsQuestion;
}>();

const maxSegmentValue = (question: SurveyAnalyticsQuestion): number => {
    if (!question.segments || question.segments.length === 0) {
        return 1;
    }

    return Math.max(
        ...question.segments.map(
            (segment) => segment.count ?? segment.score ?? 0,
        ),
        1,
    );
};

const questionIcon = (type: SurveyAnalyticsQuestion['type']) => {
    switch (type) {
        case 'open_ended':
            return MessageSquareQuote;
        case 'ranking':
            return Scale;
        default:
            return SlidersHorizontal;
    }
};
</script>

<template>
    <Card class="border-border bg-card">
        <CardHeader class="space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div
                        class="flex size-11 items-center justify-center border border-border bg-background text-foreground"
                    >
                        <component
                            :is="questionIcon(question.type)"
                            class="size-5"
                        />
                    </div>
                    <div>
                        <CardTitle class="text-lg">{{
                            question.title
                        }}</CardTitle>
                        <CardDescription>{{
                            question.description || 'No extra prompt provided.'
                        }}</CardDescription>
                    </div>
                </div>
                <span
                    class="border border-border px-3 py-1 text-xs tracking-[0.22em] text-muted-foreground uppercase"
                >
                    {{ question.type.replace('_', ' ') }}
                </span>
            </div>
        </CardHeader>

        <CardContent class="space-y-4">
            <div
                v-if="question.average !== undefined"
                class="border border-border bg-background p-4"
            >
                <p
                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                >
                    Average rating
                </p>
                <p class="mt-2 text-3xl font-semibold">
                    {{ question.average ?? 'n/a' }}
                </p>
                <p
                    v-if="question.scale"
                    class="mt-2 text-sm text-muted-foreground"
                >
                    {{ question.scale.min_label || question.scale.min }} to
                    {{ question.scale.max_label || question.scale.max }}
                </p>
            </div>

            <div v-if="question.segments?.length" class="space-y-3">
                <div
                    v-for="segment in question.segments"
                    :key="segment.label"
                    class="space-y-2"
                >
                    <div class="flex items-center justify-between text-sm">
                        <span>{{ segment.label }}</span>
                        <span class="font-medium">{{
                            segment.count ?? segment.score ?? 0
                        }}</span>
                    </div>
                    <div class="h-3 bg-muted">
                        <div
                            class="h-3 bg-[var(--color-chart-2)] transition-all duration-500"
                            :style="{
                                width: `${(((segment.count ?? segment.score ?? 0) / maxSegmentValue(question)) * 100).toFixed(2)}%`,
                            }"
                        />
                    </div>
                </div>
            </div>

            <div
                v-if="question.responses?.length"
                class="grid gap-3 md:grid-cols-2"
            >
                <div
                    v-for="(response, index) in question.responses"
                    :key="`${question.question_id}-${index}`"
                    class="border border-border bg-background p-4 text-sm leading-6"
                >
                    {{ response }}
                </div>
            </div>
        </CardContent>
    </Card>
</template>
