<script setup lang="ts">
import {
    MessageSquareQuote,
    PieChart,
    Scale,
    SlidersHorizontal,
} from 'lucide-vue-next';
import SurveyBarChart from '@/components/surveys/SurveyBarChart.vue';
import { Badge } from '@/components/ui/badge';
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

const metricLabel = (question: SurveyAnalyticsQuestion): string =>
    question.type === 'ranking' ? 'ranking score' : 'responses';

const demographicLabel = (question: SurveyAnalyticsQuestion): string | null => {
    switch (question.demographic_key) {
        case 'location':
            return 'Demographic: location';
        case 'age_range':
            return 'Demographic: age range';
        default:
            return null;
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
                        <CardDescription>
                            {{
                                question.description ||
                                'No extra prompt provided.'
                            }}
                        </CardDescription>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2">
                    <Badge
                        v-if="question.category_name"
                        variant="outline"
                        class="rounded-full"
                    >
                        {{ question.category_name }}
                    </Badge>
                    <Badge
                        v-if="demographicLabel(question)"
                        variant="outline"
                        class="rounded-full"
                    >
                        {{ demographicLabel(question) }}
                    </Badge>
                    <span
                        class="border border-border px-3 py-1 text-xs tracking-[0.22em] text-muted-foreground uppercase"
                    >
                        {{ question.type.replace('_', ' ') }}
                    </span>
                </div>
            </div>
        </CardHeader>

        <CardContent class="space-y-4">
            <div class="grid gap-3 md:grid-cols-2">
                <div class="border border-border bg-background p-4">
                    <p
                        class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                    >
                        Captured responses
                    </p>
                    <p class="mt-2 text-3xl font-semibold">
                        {{ question.response_count }}
                    </p>
                </div>

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

                <div
                    v-else
                    class="flex items-end border border-border bg-background p-4"
                >
                    <p class="text-sm text-muted-foreground">
                        {{
                            question.type === 'open_ended'
                                ? 'Open-ended responses show a tally of repeated answers plus the raw response feed.'
                                : 'This chart shows how responses distribute across the available choices.'
                        }}
                    </p>
                </div>
            </div>

            <div v-if="question.segments?.length" class="space-y-3">
                <div class="flex items-center gap-2 text-sm font-medium">
                    <PieChart class="size-4 text-muted-foreground" />
                    <span>
                        {{
                            question.type === 'open_ended'
                                ? 'Most repeated responses'
                                : 'Pie breakdown'
                        }}
                    </span>
                </div>

                <SurveyBarChart
                    :segments="question.segments"
                    :metric-label="metricLabel(question)"
                    empty-label="Responses will appear here once participants start submitting answers."
                />
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
