<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    ChartNoAxesCombined,
    ClipboardPenLine,
    KeyRound,
    Sparkles,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import SurveyBackdrop from '@/components/surveys/SurveyBackdrop.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard, login, register } from '@/routes';

type Props = {
    canRegister: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    canRegister: true,
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

const featureCards = [
    {
        title: 'Fast builder',
        description:
            'Draft compact forms with ranking, rating, multiple-choice, and open answers in one editor.',
        icon: ClipboardPenLine,
    },
    {
        title: 'Private access',
        description:
            'Share a clean public link backed by per-survey access codes so responses stay controlled.',
        icon: KeyRound,
    },
    {
        title: 'Live insight',
        description:
            'See completion, response volume, and question-by-question signals without leaving the studio.',
        icon: ChartNoAxesCombined,
    },
];
</script>

<template>
    <Head title="Surveyor" />

    <main
        class="relative min-h-screen overflow-hidden bg-background text-foreground"
    >
        <div class="absolute inset-x-0 top-0 h-1 bg-(--color-chart-2)" />

        <div
            class="relative mx-auto flex min-h-screen w-full max-w-5xl flex-col px-4 py-5 sm:px-6 xl:max-w-[70vw] xl:px-0"
        >
            <header
                class="mb-8 flex items-center justify-between border-b border-border px-1 py-4 sm:px-0"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <div
                        class="flex size-9 items-center justify-center border border-foreground bg-foreground text-background"
                    >
                        <AppLogoIcon class="size-5 fill-current" />
                    </div>
                    <div class="min-w-0">
                        <p
                            class="truncate text-sm font-semibold tracking-[0.18em] uppercase"
                        >
                            Surveyor
                        </p>
                        <p class="brand-note truncate">
                            notes, forms, and clean charts
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        v-if="!user"
                        as-child
                        variant="ghost"
                        class="px-4 text-sm"
                    >
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button as-child class="px-4 text-sm">
                        <Link :href="user ? dashboard() : register()">
                            {{ user ? 'Open studio' : 'Start creating' }}
                        </Link>
                    </Button>
                </div>
            </header>

            <section
                class="relative overflow-hidden border border-border bg-card p-5 md:p-7"
            >
                <SurveyBackdrop />

                <div
                    class="relative z-10 grid gap-6 xl:grid-cols-[1.15fr_0.85fr] xl:items-center"
                >
                    <div class="space-y-5">
                        <span class="eyebrow">
                            <Sparkles class="size-3.5" />
                            Flat, private, and quick to scan
                        </span>

                        <div class="space-y-3">
                            <h1
                                class="max-w-3xl text-4xl font-semibold tracking-tight text-balance md:text-5xl xl:text-[3.7rem] xl:leading-[1.02]"
                            >
                                <span class="accent-note block md:text-base">
                                    forms with a human pulse
                                </span>
                                Build surveys that feel designed, not generated.
                            </h1>
                            <p
                                class="max-w-2xl text-sm leading-7 text-muted-foreground md:text-base"
                            >
                                Surveyor gives you a compact studio for creating
                                polished forms, locking access with a code, and
                                reading elegant analytics.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <Button as-child class="px-5">
                                <Link :href="user ? dashboard() : login()">
                                    {{
                                        user
                                            ? 'Go to dashboard'
                                            : 'Sign in to create'
                                    }}
                                    <ArrowRight class="ml-2 size-4" />
                                </Link>
                            </Button>
                            <Button
                                v-if="!user && props.canRegister"
                                as-child
                                variant="outline"
                                class="px-5"
                            >
                                <Link :href="register()"
                                    >Create an account</Link
                                >
                            </Button>
                        </div>

                        <div
                            class="flex flex-wrap gap-2 text-xs text-muted-foreground"
                        >
                            <span
                                class="border border-border bg-background px-3 py-1.5"
                                >Ranking and rating questions</span
                            >
                            <span
                                class="border border-border bg-background px-3 py-1.5"
                                >Access-code protected response flow</span
                            >
                            <span
                                class="border border-border bg-background px-3 py-1.5"
                                >Compact analytics dashboard</span
                            >
                        </div>
                    </div>

                    <div class="grid gap-3">
                        <Card class="border-border bg-background">
                            <CardContent class="space-y-4 p-5">
                                <div class="flex items-center justify-between">
                                    <p
                                        class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >
                                        Release feedback pulse
                                    </p>
                                    <span
                                        class="border border-blue-600/25 bg-blue-500/8 px-2.5 py-1 text-[10px] font-medium tracking-[0.18em] text-blue-700 uppercase dark:text-blue-300"
                                        >Live</span
                                    >
                                </div>
                                <div class="grid gap-2">
                                    <div
                                        class="border border-border bg-card px-3 py-3"
                                    >
                                        <p class="text-sm font-medium">
                                            What should we improve next?
                                        </p>
                                        <p
                                            class="mt-1 text-xs leading-5 text-muted-foreground"
                                        >
                                            Open answer · required
                                        </p>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <div
                                            class="border border-border bg-card px-3 py-3 text-center"
                                        >
                                            <p
                                                class="text-[10px] tracking-[0.2em] text-muted-foreground uppercase"
                                            >
                                                Responses
                                            </p>
                                            <p
                                                class="mt-1 text-2xl font-semibold"
                                            >
                                                84
                                            </p>
                                        </div>
                                        <div
                                            class="border border-border bg-card px-3 py-3 text-center"
                                        >
                                            <p
                                                class="text-[10px] tracking-[0.2em] text-muted-foreground uppercase"
                                            >
                                                Complete
                                            </p>
                                            <p
                                                class="mt-1 text-2xl font-semibold"
                                            >
                                                91%
                                            </p>
                                        </div>
                                        <div
                                            class="border border-border bg-card px-3 py-3 text-center"
                                        >
                                            <p
                                                class="text-[10px] tracking-[0.2em] text-muted-foreground uppercase"
                                            >
                                                Code
                                            </p>
                                            <p
                                                class="mt-1 text-sm font-semibold"
                                            >
                                                LOCK-24
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <Card class="border-border bg-card">
                                <CardContent class="space-y-2 p-4">
                                    <p
                                        class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >
                                        Builder rhythm
                                    </p>
                                    <p class="text-lg font-semibold">
                                        Drag less, decide faster.
                                    </p>
                                    <p
                                        class="text-sm leading-6 text-muted-foreground"
                                    >
                                        Small controls, calm spacing, and clear
                                        question blocks keep the editor focused.
                                    </p>
                                </CardContent>
                            </Card>
                            <Card class="border-border bg-card">
                                <CardContent class="space-y-2 p-4">
                                    <p
                                        class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >
                                        Analytics tone
                                    </p>
                                    <p class="text-lg font-semibold">
                                        Readable at a glance.
                                    </p>
                                    <p
                                        class="text-sm leading-6 text-muted-foreground"
                                    >
                                        Closed answers summarize visually while
                                        open answers remain easy to scan.
                                    </p>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-4 py-6 md:grid-cols-3">
                <Card
                    v-for="feature in featureCards"
                    :key="feature.title"
                    class="border-border bg-card"
                >
                    <CardContent class="space-y-3 p-4">
                        <div
                            class="flex size-10 items-center justify-center border border-border bg-background text-foreground"
                        >
                            <component :is="feature.icon" class="size-4.5" />
                        </div>
                        <div>
                            <p class="text-base font-semibold">
                                {{ feature.title }}
                            </p>
                            <p
                                class="mt-1 text-sm leading-6 text-muted-foreground"
                            >
                                {{ feature.description }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </section>
        </div>
    </main>
</template>
