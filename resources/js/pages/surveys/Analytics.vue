<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Copy,
    Eye,
    Pencil,
    LockKeyhole,
    PauseCircle,
    PieChart,
    PlayCircle,
    Trash2,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import {
    close as closeSurvey,
    destroy as destroySurvey,
    edit as editSurvey,
    publish as publishSurvey,
} from '@/actions/App/Http/Controllers/Survey/SurveyController';
import Heading from '@/components/Heading.vue';
import SurveyAnalyticsCard from '@/components/surveys/SurveyAnalyticsCard.vue';
import SurveyBackdrop from '@/components/surveys/SurveyBackdrop.vue';
import SurveyBarChart from '@/components/surveys/SurveyBarChart.vue';
import SurveyDonutChart from '@/components/surveys/SurveyDonutChart.vue';
import SurveyStatusBadge from '@/components/surveys/SurveyStatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem, SurveyAnalytics, SurveyBuilder } from '@/types';

type Props = {
    survey: SurveyBuilder;
    analytics: SurveyAnalytics;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    {
        title: props.survey.title,
        href: editSurvey({ survey: props.survey.id ?? 0 }),
    },
];

const isDeleteDialogOpen = ref(false);
const isDeleting = ref(false);
const isAccessCodeVisible = ref(false);

const shareUrl = computed(() => {
    if (!props.survey.share_path) {
        return '';
    }

    if (/^https?:\/\//.test(props.survey.share_path)) {
        return props.survey.share_path;
    }

    return `${window.location.origin}${props.survey.share_path}`;
});

const completionSegments = computed(() => {
    const unfinished = Math.max(
        props.analytics.summary.response_count -
            props.analytics.summary.completed_count,
        0,
    );

    return [
        {
            label: 'Completed',
            value: props.analytics.summary.completed_count,
            color: 'var(--color-chart-2)',
        },
        {
            label: 'Unfinished',
            value: unfinished,
            color: 'var(--color-chart-4)',
        },
    ];
});

const questionResponseSegments = computed(
    () => props.analytics.summary.question_response_segments,
);

const visibleAccessCode = computed(() =>
    isAccessCodeVisible.value ? props.survey.access_code : null,
);

const demographicQuestions = computed(() =>
    props.analytics.questions.filter((question) => question.demographic_key),
);

const nonDemographicQuestions = computed(() =>
    props.analytics.questions.filter((question) => !question.demographic_key),
);

const copyLink = async (): Promise<void> => {
    if (!shareUrl.value) {
        return;
    }

    await navigator.clipboard.writeText(shareUrl.value);
    toast.success('Share link copied', {
        description: 'The private survey link is ready to share.',
    });
};

const toggleAccessCode = (): void => {
    if (!props.survey.access_code) {
        return;
    }

    isAccessCodeVisible.value = !isAccessCodeVisible.value;
};

const publish = (): void => {
    router.post(publishSurvey({ survey: props.survey.id ?? 0 }).url);
};

const close = (): void => {
    router.post(closeSurvey({ survey: props.survey.id ?? 0 }).url);
};

const destroy = (): void => {
    isDeleting.value = true;

    router.delete(destroySurvey({ survey: props.survey.id ?? 0 }).url, {
        onFinish: () => {
            isDeleting.value = false;
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${survey.title} analytics`" />

        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <section
                class="relative overflow-hidden border border-border bg-card p-6 md:p-8"
            >
                <SurveyBackdrop />

                <div
                    class="relative z-10 grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]"
                >
                    <div class="space-y-5">
                        <Link
                            :href="dashboard()"
                            class="inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors hover:text-foreground"
                        >
                            <ArrowLeft class="size-4" />
                            Back to dashboard
                        </Link>

                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <SurveyStatusBadge :status="survey.status" />
                                <span
                                    class="text-xs tracking-[0.24em] text-muted-foreground uppercase"
                                    >{{ survey.public_id }}</span
                                >
                            </div>

                            <Heading
                                :title="survey.title"
                                :description="
                                    survey.description ||
                                    'This survey does not have a description yet.'
                                "
                            />
                            <p class="accent-note md:text-base">
                                response signals in one clean readout
                            </p>
                        </div>
                    </div>

                    <Card class="border-border bg-background">
                        <CardContent class="space-y-4 p-5">
                            <div class="border border-border bg-card p-4">
                                <p
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    Private share link
                                </p>
                                <p class="mt-2 text-sm leading-6 break-all">
                                    {{
                                        shareUrl ||
                                        'Publish the survey to make its respondent link available.'
                                    }}
                                </p>
                            </div>

                            <div class="border border-border bg-card p-4">
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <div>
                                        <p
                                            class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                        >
                                            Access code
                                        </p>
                                        <p
                                            class="mt-2 text-sm leading-6 break-all"
                                        >
                                            {{
                                                visibleAccessCode ??
                                                'Hidden until you choose to reveal it.'
                                            }}
                                        </p>
                                    </div>

                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        :disabled="!survey.access_code"
                                        @click="toggleAccessCode"
                                    >
                                        <Eye class="mr-2 size-4" />
                                        {{
                                            isAccessCodeVisible
                                                ? 'Hide code'
                                                : 'Show code'
                                        }}
                                    </Button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <Button
                                    type="button"
                                    variant="outline"
                                    :disabled="!shareUrl"
                                    @click="copyLink"
                                >
                                    <Copy class="mr-2 size-4" />
                                    Copy link
                                </Button>
                                <Button as-child variant="outline">
                                    <Link
                                        :href="
                                            editSurvey({
                                                survey: survey.id ?? 0,
                                            })
                                        "
                                        class="inline-flex items-center"
                                        ><Pencil class="mr-2 size-4" />Edit
                                        form</Link
                                    >
                                </Button>
                                <Button
                                    v-if="survey.status !== 'published'"
                                    type="button"
                                    @click="publish"
                                >
                                    <PlayCircle class="mr-2 size-4" />
                                    Publish
                                </Button>
                                <Button
                                    v-else
                                    type="button"
                                    variant="outline"
                                    @click="close"
                                >
                                    <PauseCircle class="mr-2 size-4" />
                                    Close
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="text-red-600 hover:text-red-600"
                                    @click="isDeleteDialogOpen = true"
                                >
                                    <Trash2 class="mr-2 size-4" />
                                    Delete
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </section>

            <section class="grid gap-4 lg:grid-cols-4">
                <Card class="border-border bg-card">
                    <CardContent class="p-5">
                        <p
                            class="text-xs tracking-[0.22em] text-muted-foreground uppercase"
                        >
                            Responses
                        </p>
                        <p class="mt-3 text-4xl font-semibold">
                            {{ analytics.summary.response_count }}
                        </p>
                    </CardContent>
                </Card>
                <Card class="border-border bg-card">
                    <CardContent class="p-5">
                        <p
                            class="text-xs tracking-[0.22em] text-muted-foreground uppercase"
                        >
                            Completed
                        </p>
                        <p class="mt-3 text-4xl font-semibold">
                            {{ analytics.summary.completed_count }}
                        </p>
                    </CardContent>
                </Card>
                <Card class="border-border bg-card">
                    <CardContent class="p-5">
                        <p
                            class="text-xs tracking-[0.22em] text-muted-foreground uppercase"
                        >
                            Questions
                        </p>
                        <p class="mt-3 text-4xl font-semibold">
                            {{ analytics.summary.question_count }}
                        </p>
                    </CardContent>
                </Card>
                <Card class="border-border bg-card">
                    <CardContent class="p-5">
                        <p
                            class="text-xs tracking-[0.22em] text-muted-foreground uppercase"
                        >
                            Completion rate
                        </p>
                        <p class="mt-3 text-4xl font-semibold">
                            {{ analytics.summary.completion_rate }}%
                        </p>
                    </CardContent>
                </Card>
            </section>

            <section
                class="grid gap-4 xl:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]"
            >
                <Card class="border-border bg-card">
                    <CardContent class="space-y-5 p-5">
                        <div class="flex items-center gap-2">
                            <PieChart class="size-4 text-muted-foreground" />
                            <h2 class="text-lg font-semibold tracking-tight">
                                Response completion
                            </h2>
                        </div>

                        <SurveyDonutChart
                            :segments="completionSegments"
                            :center-value="`${analytics.summary.completion_rate}%`"
                            :center-label="
                                analytics.summary.response_count === 0
                                    ? 'No responses yet'
                                    : 'Completion rate'
                            "
                        />
                    </CardContent>
                </Card>

                <Card class="border-border bg-card">
                    <CardContent class="space-y-5 p-5">
                        <div class="flex items-center gap-2">
                            <PieChart class="size-4 text-muted-foreground" />
                            <h2 class="text-lg font-semibold tracking-tight">
                                Response volume by question
                            </h2>
                        </div>

                        <div
                            v-if="questionResponseSegments.length"
                            class="grid gap-3"
                        >
                            <div
                                v-for="segment in questionResponseSegments"
                                :key="segment.label"
                                class="flex items-center justify-between gap-3 rounded-[1rem] border border-border bg-background px-4 py-3"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-medium text-foreground"
                                    >
                                        {{ segment.label }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Total submissions received
                                    </p>
                                </div>
                                <span
                                    class="shrink-0 text-sm font-medium text-foreground"
                                >
                                    {{ segment.count ?? segment.score ?? 0 }}
                                </span>
                            </div>
                        </div>

                        <div
                            v-else
                            class="rounded-[1rem] border border-dashed border-border bg-background px-4 py-6 text-sm text-muted-foreground"
                        >
                            Each question will show its response volume here
                            once submissions arrive.
                        </div>
                    </CardContent>
                </Card>
            </section>

            <section v-if="demographicQuestions.length" class="space-y-4">
                <div class="flex items-center gap-3">
                    <Users class="size-5 text-muted-foreground" />
                    <div>
                        <h2 class="text-2xl font-semibold tracking-tight">
                            Demographic questions
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Location and age prompts are tagged so they remain
                            easy to spot in the analytics stream.
                        </p>
                    </div>
                </div>

                <div class="grid gap-5">
                    <SurveyAnalyticsCard
                        v-for="question in demographicQuestions"
                        :key="`demographic-${question.question_id}`"
                        :question="question"
                    />
                </div>
            </section>

            <section class="space-y-4">
                <div class="flex items-center gap-3">
                    <LockKeyhole class="size-5 text-muted-foreground" />
                    <div>
                        <h2 class="text-2xl font-semibold tracking-tight">
                            Per-question analytics
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Every prompt now includes a chart or graph where the
                            response shape supports it.
                        </p>
                    </div>
                </div>

                <div class="grid gap-5">
                    <SurveyAnalyticsCard
                        v-for="question in nonDemographicQuestions"
                        :key="question.question_id"
                        :question="question"
                    />
                </div>
            </section>

            <Dialog
                :open="isDeleteDialogOpen"
                @update:open="isDeleteDialogOpen = $event"
            >
                <DialogContent
                    class="sm:max-w-md"
                    :show-close-button="!isDeleting"
                >
                    <DialogHeader>
                        <DialogTitle>Delete this survey?</DialogTitle>
                        <DialogDescription>
                            This removes the survey, every response, and all
                            analytics tied to it. This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>

                    <DialogFooter class="gap-2 sm:justify-end">
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="isDeleting"
                            @click="isDeleteDialogOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="button"
                            variant="destructive"
                            :disabled="isDeleting"
                            @click="destroy"
                        >
                            <Trash2 class="mr-2 size-4" />
                            {{ isDeleting ? 'Deleting...' : 'Delete survey' }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
