<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { create } from '@/actions/App/Http/Controllers/Survey/SurveyController';
import SurveyCard from '@/components/surveys/SurveyCard.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, SurveySummary } from '@/types';

type Props = {
    surveys: SurveySummary[];
};

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Survey library',
        href: '/surveys',
    },
];
</script>

<template>
    <Head title="Survey Library" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
            <section class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold tracking-tight">
                            All surveys
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Open any card to review analytics, or jump back into
                            the builder to refine the form.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <p class="text-sm text-muted-foreground">
                            {{ surveys.length }} surveys in your archive
                        </p>
                        <Button as-child variant="outline">
                            <Link href="/dashboard">Back to studio</Link>
                        </Button>
                        <Button as-child>
                            <Link :href="create()">Create form</Link>
                        </Button>
                    </div>
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
                        <Button as-child>
                            <Link :href="create()">Create your first form</Link>
                        </Button>
                    </CardContent>
                </Card>
            </section>
        </div>
    </AppLayout>
</template>
