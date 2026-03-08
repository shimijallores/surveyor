<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { create } from '@/actions/App/Http/Controllers/Survey/SurveyController';
import Heading from '@/components/Heading.vue';
import SurveyBackdrop from '@/components/surveys/SurveyBackdrop.vue';
import SurveyCard from '@/components/surveys/SurveyCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type {
    BreadcrumbItem,
    SurveyDashboardStats,
    SurveyQuestionTypeOption,
    SurveySummary,
} from '@/types';

type Props = {
    surveys: SurveySummary[];
    stats: SurveyDashboardStats;
    questionTypes: SurveyQuestionTypeOption[];
};

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
            <section
                class="relative overflow-hidden border border-border bg-card p-6 md:p-8"
            >
                <SurveyBackdrop />

                <div class="relative z-10 grid gap-8 lg:grid-cols-[1.4fr_1fr]">
                    <div class="space-y-5">
                        <span class="eyebrow"> Survey creator workspace </span>

                        <p class="accent-note md:text-base">
                            keep the whole studio on one page
                        </p>

                        <Heading
                            title="Create polished surveys and track every response in one place"
                            description="Draft forms, publish them with a private access code, and watch the results update inside a single analytics dashboard."
                        />

                        <div class="flex flex-wrap gap-3">
                            <Button as-child class="px-5">
                                <Link :href="create()">Create form</Link>
                            </Button>
                            <Button as-child variant="outline" class="px-5">
                                <Link href="/surveys">Open library</Link>
                            </Button>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                        <Card class="border-border bg-background">
                            <CardContent class="p-5">
                                <p
                                    class="text-xs tracking-[0.22em] text-muted-foreground uppercase"
                                >
                                    Total forms
                                </p>
                                <p class="mt-3 text-4xl font-semibold">
                                    {{ stats.total_surveys }}
                                </p>
                                <p class="mt-2 text-sm text-muted-foreground">
                                    All active, draft, and archived survey
                                    definitions.
                                </p>
                            </CardContent>
                        </Card>
                        <Card class="border-border bg-background">
                            <CardContent class="grid grid-cols-2 gap-4 p-5">
                                <div>
                                    <p
                                        class="text-xs tracking-[0.22em] text-muted-foreground uppercase"
                                    >
                                        Live
                                    </p>
                                    <p class="mt-2 text-3xl font-semibold">
                                        {{ stats.published_surveys }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs tracking-[0.22em] text-muted-foreground uppercase"
                                    >
                                        Draft
                                    </p>
                                    <p class="mt-2 text-3xl font-semibold">
                                        {{ stats.draft_surveys }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs tracking-[0.22em] text-muted-foreground uppercase"
                                    >
                                        Closed
                                    </p>
                                    <p class="mt-2 text-3xl font-semibold">
                                        {{ stats.closed_surveys }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs tracking-[0.22em] text-muted-foreground uppercase"
                                    >
                                        Responses
                                    </p>
                                    <p class="mt-2 text-3xl font-semibold">
                                        {{ stats.total_responses }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </section>

            <section id="survey-grid" class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold tracking-tight">
                            Recent surveys
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Keep the studio focused on what changed recently.
                            Open the full library when you need the complete
                            archive.
                        </p>
                    </div>
                    <Button as-child variant="outline">
                        <Link href="/surveys">View all surveys</Link>
                    </Button>
                </div>

                <div
                    v-if="surveys.length"
                    class="grid gap-5 lg:grid-cols-2 2xl:grid-cols-3"
                >
                    <SurveyCard
                        v-for="survey in surveys"
                        :key="survey.id"
                        :survey="survey"
                    />
                </div>

                <Card
                    v-else
                    class="border-dashed border-border bg-card shadow-none"
                >
                    <CardContent class="flex flex-col items-start gap-4 p-8">
                        <h3 class="text-xl font-semibold">No forms yet</h3>
                        <p class="max-w-2xl text-sm text-muted-foreground">
                            Start with a clean survey and add open-ended, yes or
                            no, multiple choice, rating, and ranking questions
                            in a few clicks.
                        </p>
                        <Button as-child class="rounded-xl">
                            <Link :href="create()">Create your first form</Link>
                        </Button>
                    </CardContent>
                </Card>
            </section>
        </div>
    </AppLayout>
</template>
