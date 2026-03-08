<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowRight, LockKeyhole, Sparkles } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import SurveyBackdrop from '@/components/surveys/SurveyBackdrop.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { PublicSurvey } from '@/types';
import {
    respond,
    verify,
} from '@/actions/App/Http/Controllers/Survey/PublicSurveyController';

type Props = {
    survey: PublicSurvey;
    canRespond: boolean;
    status?: string;
};

const props = defineProps<Props>();

const form = useForm({
    access_code: '',
});

const submit = (): void => {
    form.submit(verify({ survey: props.survey.public_id }));
};
</script>

<template>
    <Head :title="survey.title" />

    <main
        class="relative min-h-screen overflow-hidden bg-background px-4 py-8 md:px-8 md:py-12"
    >
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,color-mix(in_oklab,var(--color-chart-2)_12%,transparent),transparent_32%),radial-gradient(circle_at_bottom_right,color-mix(in_oklab,var(--color-chart-4)_16%,transparent),transparent_30%)]"
        />

        <div class="relative mx-auto max-w-6xl">
            <section
                class="relative overflow-hidden border border-border bg-card p-7 md:p-9"
            >
                <SurveyBackdrop />

                <div
                    class="relative z-10 grid gap-8 lg:grid-cols-[minmax(0,1.15fr)_24rem] lg:items-start"
                >
                    <div class="space-y-6">
                        <span class="eyebrow">
                            <Sparkles class="size-3.5" />
                            Secure survey access
                        </span>

                        <div class="space-y-4">
                            <p class="accent-note text-[12px] md:text-sm">
                                you've been invited as a participant for this
                                survey
                            </p>
                            <h1
                                class="max-w-3xl text-4xl font-semibold tracking-tight text-balance md:text-5xl xl:text-[3.4rem] xl:leading-[1.02]"
                            >
                                {{ survey.title }}
                            </h1>
                            <p
                                class="max-w-2xl text-base leading-7 text-muted-foreground"
                            >
                                {{
                                    survey.description ||
                                    'Use the private access code to unlock this survey and submit your response.'
                                }}
                            </p>
                        </div>

                        <div
                            class="grid gap-4 border-t border-border/80 pt-5 md:grid-cols-[1.2fr_0.8fr]"
                        >
                            <div class="space-y-2">
                                <p
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    Before you start
                                </p>
                                <p
                                    class="max-w-xl text-sm leading-6 text-muted-foreground"
                                >
                                    The survey opens as soon as the access code
                                    is confirmed. If you've already unlocked it
                                    on this device, you can jump straight back
                                    in.
                                </p>
                            </div>
                            <div
                                class="border border-border bg-background/80 px-4 py-4"
                            >
                                <p
                                    class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                >
                                    Flow
                                </p>
                                <p
                                    class="mt-2 text-sm leading-6 text-foreground"
                                >
                                    Unlock with the shared code, answer once,
                                    and submit anonymously.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="border border-border bg-background/85 p-6 md:p-7"
                    >
                        <div class="space-y-6">
                            <div
                                class="flex size-14 items-center justify-center border border-border bg-background text-foreground"
                            >
                                <LockKeyhole class="size-6" />
                            </div>

                            <div class="space-y-2">
                                <h2
                                    class="text-2xl font-semibold tracking-tight"
                                >
                                    Enter the access code
                                </h2>
                                <p
                                    class="text-sm leading-6 text-muted-foreground"
                                >
                                    This survey is protected. Enter the code
                                    shared by the creator to continue.
                                </p>
                            </div>

                            <div class="grid gap-2">
                                <Label for="access_code">Access code</Label>
                                <Input
                                    id="access_code"
                                    v-model="form.access_code"
                                    type="password"
                                    placeholder="Enter survey access code"
                                />
                                <InputError
                                    :message="form.errors.access_code"
                                />
                            </div>

                            <Button
                                type="button"
                                class="w-full"
                                :disabled="form.processing"
                                @click="submit"
                            >
                                {{
                                    form.processing
                                        ? 'Verifying...'
                                        : 'Unlock survey'
                                }}
                            </Button>

                            <Button
                                v-if="canRespond"
                                as-child
                                variant="outline"
                                class="w-full"
                            >
                                <Link
                                    :href="
                                        respond({ survey: survey.public_id })
                                    "
                                >
                                    Continue to survey
                                    <ArrowRight class="ml-2 size-4" />
                                </Link>
                            </Button>

                            <p class="text-xs leading-5 text-muted-foreground">
                                Your answers are tied to this survey session
                                only and are submitted once you finish the full
                                form.
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>
