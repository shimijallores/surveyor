<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    ArrowLeftRight,
    CheckCircle2,
    CircleDot,
    ListOrdered,
    MessageSquareText,
    Scale,
} from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import SurveyBackdrop from '@/components/surveys/SurveyBackdrop.vue';
import SurveyStatusBadge from '@/components/surveys/SurveyStatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import type { PublicSurvey, SurveyAnswers, SurveyQuestion } from '@/types';
import { submit as submitResponse } from '@/actions/App/Http/Controllers/Survey/PublicSurveyController';

type Props = {
    survey: PublicSurvey;
    inviteMessage: string;
};

const props = defineProps<Props>();

const buildAnswers = (survey: PublicSurvey): SurveyAnswers => {
    return survey.questions.reduce<SurveyAnswers>((carry, question) => {
        switch (question.type) {
            case 'yes_no':
                carry[String(question.id)] = null;
                break;
            case 'multiple_choice':
                carry[String(question.id)] = question.settings.allow_multiple
                    ? []
                    : null;
                break;
            case 'ranking':
                carry[String(question.id)] = question.options.map(
                    (option) => option.id ?? 0,
                );
                break;
            default:
                carry[String(question.id)] = '';
                break;
        }

        return carry;
    }, {});
};

const form = useForm<{ answers: SurveyAnswers }>({
    answers: buildAnswers(props.survey),
});

const answerKey = (question: SurveyQuestion): string => String(question.id);

const toggleOption = (
    question: SurveyQuestion,
    optionId: number | null,
): void => {
    const key = answerKey(question);
    const normalizedId = optionId ?? 0;
    const currentValue = form.answers[key];

    if (question.settings.allow_multiple) {
        const selected = Array.isArray(currentValue) ? [...currentValue] : [];
        const hasValue = selected.includes(normalizedId);

        form.answers[key] = hasValue
            ? selected.filter((value) => value !== normalizedId)
            : [...selected, normalizedId];

        return;
    }

    form.answers[key] = normalizedId;
};

const moveRank = (
    question: SurveyQuestion,
    index: number,
    direction: -1 | 1,
): void => {
    const key = answerKey(question);
    const values = Array.isArray(form.answers[key])
        ? [...(form.answers[key] as number[])]
        : [];
    const target = index + direction;

    if (target < 0 || target >= values.length) {
        return;
    }

    const [value] = values.splice(index, 1);
    values.splice(target, 0, value);
    form.answers[key] = values;
};

const rankingLabel = (question: SurveyQuestion, optionId: number): string => {
    return (
        question.options.find((option) => (option.id ?? 0) === optionId)
            ?.label ?? 'Unknown option'
    );
};

const submit = (): void => {
    form.submit(submitResponse({ survey: props.survey.public_id }));
};
</script>

<template>
    <Head :title="`${survey.title} response`" />

    <main
        class="relative min-h-screen overflow-hidden bg-background px-4 py-8 md:px-8 md:py-12"
    >
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,color-mix(in_oklab,var(--color-chart-2)_14%,transparent),transparent_30%),radial-gradient(circle_at_bottom_left,color-mix(in_oklab,var(--color-chart-4)_12%,transparent),transparent_32%)]"
        />

        <div class="relative mx-auto flex max-w-5xl flex-col gap-6">
            <section
                class="relative overflow-hidden border border-border bg-card p-6 md:p-8"
            >
                <SurveyBackdrop />

                <div class="relative z-10 space-y-6">
                    <div class="space-y-4">
                        <span class="eyebrow"> Respondent view </span>
                        <p class="accent-note text-[12px] md:text-sm">
                            you've been invited as a participant for this survey
                        </p>
                        <p
                            class="max-w-2xl text-sm leading-6 text-muted-foreground"
                        >
                            {{ inviteMessage }}
                        </p>
                        <h1
                            class="max-w-4xl text-4xl font-semibold tracking-tight text-balance md:text-5xl xl:text-[3.4rem] xl:leading-[1.02]"
                        >
                            {{ survey.title }}
                        </h1>
                        <p
                            class="max-w-3xl text-base leading-7 text-muted-foreground"
                        >
                            {{
                                survey.description ||
                                'Complete each question below and submit when you are done.'
                            }}
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2.5">
                        <span
                            class="inline-flex items-center gap-2 border border-border bg-background px-3 py-2 text-foreground"
                        >
                            <span
                                class="text-[10px] tracking-[0.2em] text-muted-foreground uppercase"
                                >Questions</span
                            >
                            <span class="text-base font-semibold">{{
                                survey.questions.length
                            }}</span>
                        </span>
                        <span
                            class="inline-flex items-center gap-2 border border-border bg-background px-3 py-2 text-foreground"
                        >
                            <span
                                class="text-[10px] tracking-[0.2em] text-muted-foreground uppercase"
                                >Mode</span
                            >
                            <SurveyStatusBadge :status="survey.status" />
                        </span>
                        <span
                            class="inline-flex items-center gap-2 border border-border bg-background px-3 py-2 text-foreground"
                        >
                            <CheckCircle2 class="size-4 text-emerald-500" />
                            <span class="text-sm text-muted-foreground"
                                >Anonymous until submission</span
                            >
                        </span>
                    </div>

                    <div
                        class="grid gap-4 border-t border-border/80 pt-5 md:grid-cols-[1.1fr_0.9fr]"
                    >
                        <div class="space-y-2">
                            <p
                                class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                            >
                                How this works
                            </p>
                            <p
                                class="max-w-2xl text-sm leading-6 text-muted-foreground"
                            >
                                Move through the prompts at your own pace.
                                Required questions are marked, and nothing is
                                sent until you press submit at the end.
                            </p>
                        </div>
                        <div
                            class="border border-border bg-background/80 px-4 py-4"
                        >
                            <p
                                class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                            >
                                Response note
                            </p>
                            <p class="mt-2 text-sm leading-6 text-foreground">
                                Clear answers matter more than fast answers.
                                Take the time you need.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="space-y-5">
                <Card
                    v-for="question in survey.questions"
                    :key="question.id ?? question.position"
                    class="border-border bg-card shadow-none"
                >
                    <CardHeader class="pb-4">
                        <div class="flex items-start gap-4">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center border border-border bg-background text-foreground"
                            >
                                <MessageSquareText
                                    v-if="question.type === 'open_ended'"
                                    class="size-5"
                                />
                                <CircleDot
                                    v-else-if="
                                        question.type === 'yes_no' ||
                                        question.type === 'multiple_choice'
                                    "
                                    class="size-5"
                                />
                                <Scale
                                    v-else-if="question.type === 'rating_scale'"
                                    class="size-5"
                                />
                                <ListOrdered v-else class="size-5" />
                            </div>
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="text-[10px] tracking-[0.2em] text-muted-foreground uppercase"
                                    >
                                        Question {{ question.position + 1 }}
                                    </span>
                                    <span
                                        v-if="question.is_required"
                                        class="inline-flex items-center border border-red-500/20 bg-red-500/8 px-2 py-0.5 text-[10px] tracking-[0.16em] text-red-600 uppercase dark:text-red-300"
                                    >
                                        Required
                                    </span>
                                </div>
                                <CardTitle class="text-xl leading-tight">
                                    {{ question.title }}
                                </CardTitle>
                                <CardDescription
                                    class="max-w-3xl text-sm leading-6"
                                    >{{
                                        question.description ||
                                        'Respond based on your experience.'
                                    }}</CardDescription
                                >
                            </div>
                        </div>
                    </CardHeader>

                    <CardContent class="space-y-4 pt-0">
                        <textarea
                            v-if="question.type === 'open_ended'"
                            :value="
                                String(form.answers[answerKey(question)] ?? '')
                            "
                            rows="4"
                            class="min-h-32 w-full border border-input bg-background px-4 py-3 text-sm leading-6 transition outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            placeholder="Type your response here"
                            @input="
                                form.answers[answerKey(question)] = (
                                    $event.target as HTMLTextAreaElement
                                ).value
                            "
                        />

                        <div
                            v-else-if="question.type === 'yes_no'"
                            class="grid gap-3 md:grid-cols-2"
                        >
                            <button
                                type="button"
                                class="border px-4 py-4 text-left transition hover:border-foreground"
                                :class="
                                    form.answers[answerKey(question)] === true
                                        ? 'border-foreground bg-muted'
                                        : 'border-border/70'
                                "
                                @click="
                                    form.answers[answerKey(question)] = true
                                "
                            >
                                Yes
                            </button>
                            <button
                                type="button"
                                class="border px-4 py-4 text-left transition hover:border-foreground"
                                :class="
                                    form.answers[answerKey(question)] === false
                                        ? 'border-foreground bg-muted'
                                        : 'border-border/70'
                                "
                                @click="
                                    form.answers[answerKey(question)] = false
                                "
                            >
                                No
                            </button>
                        </div>

                        <div
                            v-else-if="question.type === 'multiple_choice'"
                            class="space-y-3"
                        >
                            <button
                                v-for="option in question.options"
                                :key="option.id ?? option.position"
                                type="button"
                                class="flex w-full items-center gap-3 border px-4 py-4 text-left transition hover:border-foreground"
                                :class="
                                    Array.isArray(
                                        form.answers[answerKey(question)],
                                    )
                                        ? (
                                              form.answers[
                                                  answerKey(question)
                                              ] as number[]
                                          ).includes(option.id ?? 0)
                                            ? 'border-foreground bg-muted'
                                            : 'border-border/70'
                                        : form.answers[answerKey(question)] ===
                                            (option.id ?? 0)
                                          ? 'border-foreground bg-muted'
                                          : 'border-border/70'
                                "
                                @click="toggleOption(question, option.id)"
                            >
                                <Checkbox
                                    v-if="question.settings.allow_multiple"
                                    :model-value="
                                        Array.isArray(
                                            form.answers[answerKey(question)],
                                        ) &&
                                        (
                                            form.answers[
                                                answerKey(question)
                                            ] as number[]
                                        ).includes(option.id ?? 0)
                                    "
                                />
                                <CircleDot
                                    v-else
                                    class="size-4 text-muted-foreground"
                                />
                                <span>{{ option.label }}</span>
                            </button>
                        </div>

                        <div
                            v-else-if="question.type === 'rating_scale'"
                            class="space-y-4"
                        >
                            <div class="grid gap-3 md:grid-cols-5">
                                <button
                                    v-for="value in Array.from(
                                        {
                                            length:
                                                (question.settings.max ?? 5) -
                                                (question.settings.min ?? 1) +
                                                1,
                                        },
                                        (_, index) =>
                                            (question.settings.min ?? 1) +
                                            index,
                                    )"
                                    :key="value"
                                    type="button"
                                    class="border px-4 py-4 text-center transition hover:border-foreground"
                                    :class="
                                        form.answers[answerKey(question)] ===
                                        value
                                            ? 'border-foreground bg-muted'
                                            : 'border-border/70'
                                    "
                                    @click="
                                        form.answers[answerKey(question)] =
                                            value
                                    "
                                >
                                    {{ value }}
                                </button>
                            </div>
                            <div
                                class="flex items-center justify-between text-sm text-muted-foreground"
                            >
                                <span>{{
                                    question.settings.min_label ||
                                    question.settings.min
                                }}</span>
                                <span>{{
                                    question.settings.max_label ||
                                    question.settings.max
                                }}</span>
                            </div>
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                class="flex items-center gap-2 text-sm text-muted-foreground"
                            >
                                <ArrowLeftRight class="size-4" />
                                Use the controls to move each option into your
                                preferred order.
                            </div>
                            <div
                                v-for="(optionId, index) in form.answers[
                                    answerKey(question)
                                ] as number[]"
                                :key="`${question.id}-${optionId}`"
                                class="flex items-center justify-between gap-3 border border-border px-4 py-4"
                            >
                                <div>
                                    <p
                                        class="text-xs tracking-[0.2em] text-muted-foreground uppercase"
                                    >
                                        Rank {{ index + 1 }}
                                    </p>
                                    <p class="mt-1 font-medium">
                                        {{ rankingLabel(question, optionId) }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        @click="moveRank(question, index, -1)"
                                    >
                                        Up
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        @click="moveRank(question, index, 1)"
                                    >
                                        Down
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <InputError
                            :message="form.errors[`answers.${question.id}`]"
                        />
                    </CardContent>
                </Card>

                <div class="flex justify-end">
                    <Button
                        type="button"
                        class="px-6"
                        :disabled="form.processing"
                        @click="submit"
                    >
                        {{
                            form.processing
                                ? 'Submitting...'
                                : 'Submit response'
                        }}
                    </Button>
                </div>
            </div>
        </div>
    </main>
</template>
