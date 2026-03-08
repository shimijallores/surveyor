<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    ChevronDown,
    ChevronUp,
    Copy,
    CopyPlus,
    Grip,
    Trash2,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import {
    create as createSurvey,
    edit as editSurvey,
    store as storeSurvey,
    update as updateSurvey,
} from '@/actions/App/Http/Controllers/Survey/SurveyController';
import Heading from '@/components/Heading.vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type {
    BreadcrumbItem,
    SurveyBuilder,
    SurveyBuilderForm,
    SurveyQuestion,
    SurveyQuestionType,
    SurveyQuestionTypeOption,
} from '@/types';

type Props = {
    mode: 'create' | 'edit';
    survey: SurveyBuilder;
    questionTypes: SurveyQuestionTypeOption[];
};

const props = defineProps<Props>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    { title: 'Dashboard', href: dashboard() },
    {
        title: props.mode === 'create' ? 'Create form' : 'Edit form',
        href:
            props.mode === 'create'
                ? createSurvey()
                : editSurvey({ survey: props.survey.id ?? 0 }),
    },
]);

const shareUrl = computed(() => {
    if (!props.survey.share_path) {
        return '';
    }

    if (/^https?:\/\//.test(props.survey.share_path)) {
        return props.survey.share_path;
    }

    if (typeof window === 'undefined') {
        return props.survey.share_path;
    }

    return `${window.location.origin}${props.survey.share_path}`;
});

const copyShareUrl = async (): Promise<void> => {
    if (!shareUrl.value) {
        return;
    }

    await navigator.clipboard.writeText(shareUrl.value);
    toast.success('Share link copied', {
        description: 'The private survey link is ready to paste anywhere.',
    });
};

const form = useForm<SurveyBuilderForm>(
    `${props.mode}-survey-builder:${props.survey.id ?? 'new'}`,
    {
        title: props.survey.title,
        description: props.survey.description ?? '',
        access_code: '',
        questions: props.survey.questions.map((question) => ({
            ...question,
            description: question.description ?? '',
            settings: {
                allow_multiple: question.settings.allow_multiple ?? false,
                min: question.settings.min ?? 1,
                max: question.settings.max ?? 5,
                min_label: question.settings.min_label ?? '',
                max_label: question.settings.max_label ?? '',
            },
        })),
    },
);

form.dontRemember('access_code');

const createQuestion = (type: SurveyQuestionType): SurveyQuestion => ({
    id: null,
    type,
    title: '',
    description: '',
    is_required: false,
    position: form.questions.length,
    settings: {
        allow_multiple: false,
        min: 1,
        max: 5,
        min_label: '',
        max_label: '',
    },
    options:
        type === 'multiple_choice' || type === 'ranking'
            ? [
                  { id: null, label: 'Option 1', position: 0 },
                  { id: null, label: 'Option 2', position: 1 },
              ]
            : [],
});

const syncPositions = (): void => {
    form.questions = form.questions.map((question, index) => ({
        ...question,
        position: index,
        options: question.options.map((option, optionIndex) => ({
            ...option,
            position: optionIndex,
        })),
    }));
};

const addQuestion = (type: SurveyQuestionType): void => {
    form.questions.push(createQuestion(type));
    syncPositions();
};

const updateQuestionType = (
    question: SurveyQuestion,
    type: SurveyQuestionType,
): void => {
    question.type = type;

    if (type === 'multiple_choice' || type === 'ranking') {
        question.options =
            question.options.length > 1
                ? question.options
                : [
                      { id: null, label: 'Option 1', position: 0 },
                      { id: null, label: 'Option 2', position: 1 },
                  ];
    } else {
        question.options = [];
    }

    if (type !== 'multiple_choice') {
        question.settings.allow_multiple = false;
    }

    if (type !== 'rating_scale') {
        question.settings.min = 1;
        question.settings.max = 5;
        question.settings.min_label = '';
        question.settings.max_label = '';
    }

    syncPositions();
};

const removeQuestion = (index: number): void => {
    form.questions.splice(index, 1);
    syncPositions();
};

const moveQuestion = (index: number, direction: -1 | 1): void => {
    const target = index + direction;

    if (target < 0 || target >= form.questions.length) {
        return;
    }

    const [question] = form.questions.splice(index, 1);
    form.questions.splice(target, 0, question);
    syncPositions();
};

const addOption = (question: SurveyQuestion): void => {
    question.options.push({
        id: null,
        label: `Option ${question.options.length + 1}`,
        position: question.options.length,
    });
    syncPositions();
};

const removeOption = (question: SurveyQuestion, index: number): void => {
    question.options.splice(index, 1);
    syncPositions();
};

const moveOption = (
    question: SurveyQuestion,
    index: number,
    direction: -1 | 1,
): void => {
    const target = index + direction;

    if (target < 0 || target >= question.options.length) {
        return;
    }

    const [option] = question.options.splice(index, 1);
    question.options.splice(target, 0, option);
    syncPositions();
};

const duplicateQuestion = (question: SurveyQuestion): void => {
    form.questions.push({
        ...structuredClone(question),
        id: null,
        title: `${question.title || 'Untitled question'} copy`,
    });
    syncPositions();
};

const fieldError = (path: string): string | undefined =>
    (form.errors as Record<string, string | undefined>)[path];

const showOptions = (type: SurveyQuestionType): boolean =>
    type === 'multiple_choice' || type === 'ranking';
const showRatingSettings = (type: SurveyQuestionType): boolean =>
    type === 'rating_scale';
const showMultipleChoiceSettings = (type: SurveyQuestionType): boolean =>
    type === 'multiple_choice';

const submit = (): void => {
    form.transform((data) => ({
        ...data,
        description: data.description || null,
        questions: data.questions.map((question) => ({
            ...question,
            description: question.description || null,
            settings: {
                ...question.settings,
                min: Number(question.settings.min ?? 1),
                max: Number(question.settings.max ?? 5),
                min_label: question.settings.min_label || null,
                max_label: question.settings.max_label || null,
            },
        })),
    }));

    if (props.mode === 'create') {
        form.submit(storeSurvey());

        return;
    }

    form.submit(updateSurvey({ survey: props.survey.id ?? 0 }));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head
            :title="mode === 'create' ? 'Create form' : `Edit ${survey.title}`"
        />

        <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
            <section
                class="relative overflow-hidden border border-border bg-card p-6 md:p-8"
            >
                <SurveyBackdrop />

                <div
                    class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between"
                >
                    <div class="space-y-4">
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
                                >
                                    {{
                                        mode === 'create'
                                            ? 'New survey'
                                            : survey.public_id
                                    }}
                                </span>
                            </div>

                            <Heading
                                :title="
                                    mode === 'create'
                                        ? 'Build your next form'
                                        : 'Refine the survey experience'
                                "
                                description="Keep it simple: define the title, add questions, tune the access code, and save changes without leaving the page."
                            />
                            <p class="accent-note md:text-base">
                                make it tight, clear, and easy to answer
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <Button
                            type="button"
                            class="px-5"
                            :disabled="form.processing"
                            @click="submit"
                        >
                            {{
                                form.processing
                                    ? 'Saving...'
                                    : mode === 'create'
                                      ? 'Create survey'
                                      : 'Save changes'
                            }}
                        </Button>
                    </div>
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
                <div class="space-y-6">
                    <Card class="border-border bg-card">
                        <CardHeader>
                            <CardTitle>Survey details</CardTitle>
                            <CardDescription
                                >Set the core information respondents will see
                                before they unlock the survey.</CardDescription
                            >
                        </CardHeader>
                        <CardContent class="grid gap-5">
                            <div class="grid gap-2">
                                <Label for="title">Title</Label>
                                <Input
                                    id="title"
                                    v-model="form.title"
                                    placeholder="Product feedback pulse"
                                />
                                <InputError :message="fieldError('title')" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="description">Description</Label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="min-h-28 border border-input bg-background px-4 py-3 text-sm transition outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                    placeholder="Tell participants what this survey is for and how long it should take."
                                />
                                <InputError
                                    :message="fieldError('description')"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="access_code"> Access code </Label>
                                <Input
                                    id="access_code"
                                    v-model="form.access_code"
                                    type="password"
                                    :placeholder="
                                        mode === 'create'
                                            ? 'Create a private access code'
                                            : 'Leave blank to keep the current code'
                                    "
                                />
                                <p class="text-xs text-muted-foreground">
                                    This code is required before participants
                                    can answer the survey.
                                </p>
                                <InputError
                                    :message="fieldError('access_code')"
                                />
                            </div>
                        </CardContent>
                    </Card>

                    <div class="space-y-4">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-semibold">
                                    Question flow
                                </h2>
                                <p class="text-sm text-muted-foreground">
                                    Move questions up or down, add options, and
                                    keep the form readable for respondents.
                                </p>
                            </div>
                            <span
                                class="border border-border px-3 py-1 text-xs tracking-[0.2em] text-muted-foreground uppercase"
                            >
                                {{ form.questions.length }} questions
                            </span>
                        </div>

                        <Card
                            v-for="(question, questionIndex) in form.questions"
                            :key="`${question.type}-${questionIndex}`"
                            class="border-border bg-card"
                        >
                            <CardHeader
                                class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-10 items-center justify-center border border-border bg-background text-muted-foreground"
                                    >
                                        <Grip class="size-4" />
                                    </div>
                                    <div>
                                        <CardTitle
                                            >Question
                                            {{ questionIndex + 1 }}</CardTitle
                                        >
                                        <CardDescription>{{
                                            questionTypes.find(
                                                (type) =>
                                                    type.value ===
                                                    question.type,
                                            )?.description
                                        }}</CardDescription>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        @click="moveQuestion(questionIndex, -1)"
                                    >
                                        <ChevronUp class="size-4" />
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        @click="moveQuestion(questionIndex, 1)"
                                    >
                                        <ChevronDown class="size-4" />
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        @click="duplicateQuestion(question)"
                                    >
                                        <CopyPlus class="size-4" />
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        class="text-red-600 hover:text-red-600"
                                        @click="removeQuestion(questionIndex)"
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </CardHeader>

                            <CardContent class="grid gap-5">
                                <div
                                    class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_15rem]"
                                >
                                    <div class="grid gap-2">
                                        <Label
                                            :for="`question-title-${questionIndex}`"
                                            >Prompt</Label
                                        >
                                        <Input
                                            :id="`question-title-${questionIndex}`"
                                            v-model="question.title"
                                            placeholder="What should we ask?"
                                        />
                                        <InputError
                                            :message="
                                                fieldError(
                                                    `questions.${questionIndex}.title`,
                                                )
                                            "
                                        />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label>Type</Label>
                                        <Select
                                            :model-value="question.type"
                                            @update:model-value="
                                                (value) =>
                                                    updateQuestionType(
                                                        question,
                                                        value as SurveyQuestionType,
                                                    )
                                            "
                                        >
                                            <SelectTrigger class="w-full">
                                                <SelectValue
                                                    placeholder="Choose a type"
                                                />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="type in questionTypes"
                                                    :key="type.value"
                                                    :value="type.value"
                                                >
                                                    {{ type.label }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </div>

                                <div class="grid gap-2">
                                    <Label
                                        :for="`question-description-${questionIndex}`"
                                        >Helper text</Label
                                    >
                                    <textarea
                                        :id="`question-description-${questionIndex}`"
                                        v-model="question.description"
                                        rows="3"
                                        class="min-h-24 border border-input bg-background px-4 py-3 text-sm transition outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                                        placeholder="Optional instructions, context, or examples for this question."
                                    />
                                </div>

                                <div
                                    class="flex flex-wrap items-center gap-6 text-sm"
                                >
                                    <label
                                        class="inline-flex items-center gap-3"
                                    >
                                        <Checkbox
                                            :model-value="question.is_required"
                                            @update:model-value="
                                                question.is_required =
                                                    Boolean($event)
                                            "
                                        />
                                        <span>Required question</span>
                                    </label>

                                    <label
                                        v-if="
                                            showMultipleChoiceSettings(
                                                question.type,
                                            )
                                        "
                                        class="inline-flex items-center gap-3"
                                    >
                                        <Checkbox
                                            :model-value="
                                                Boolean(
                                                    question.settings
                                                        .allow_multiple,
                                                )
                                            "
                                            @update:model-value="
                                                question.settings.allow_multiple =
                                                    Boolean($event)
                                            "
                                        />
                                        <span>Allow multiple selections</span>
                                    </label>
                                </div>

                                <div
                                    v-if="showRatingSettings(question.type)"
                                    class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                                >
                                    <div class="grid gap-2">
                                        <Label>Minimum</Label>
                                        <Input
                                            v-model="question.settings.min"
                                            type="number"
                                            min="0"
                                            max="100"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label>Maximum</Label>
                                        <Input
                                            v-model="question.settings.max"
                                            type="number"
                                            min="1"
                                            max="100"
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label>Minimum label</Label>
                                        <Input
                                            :model-value="
                                                question.settings.min_label ??
                                                ''
                                            "
                                            placeholder="Not likely"
                                            @update:model-value="
                                                question.settings.min_label =
                                                    String($event)
                                            "
                                        />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label>Maximum label</Label>
                                        <Input
                                            :model-value="
                                                question.settings.max_label ??
                                                ''
                                            "
                                            placeholder="Very likely"
                                            @update:model-value="
                                                question.settings.max_label =
                                                    String($event)
                                            "
                                        />
                                    </div>
                                    <InputError
                                        class="md:col-span-2 xl:col-span-4"
                                        :message="
                                            fieldError(
                                                `questions.${questionIndex}.settings.max`,
                                            )
                                        "
                                    />
                                </div>

                                <div
                                    v-if="showOptions(question.type)"
                                    class="space-y-3 border border-border bg-background p-4"
                                >
                                    <div
                                        class="flex items-end justify-between gap-4"
                                    >
                                        <div>
                                            <h3 class="font-medium">Options</h3>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                Add the choices respondents will
                                                select or rank.
                                            </p>
                                        </div>
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant="outline"
                                            @click="addOption(question)"
                                        >
                                            Add option
                                        </Button>
                                    </div>

                                    <div
                                        v-for="(
                                            option, optionIndex
                                        ) in question.options"
                                        :key="`${questionIndex}-${optionIndex}`"
                                        class="grid gap-3 border border-border bg-card p-3 md:grid-cols-[minmax(0,1fr)_auto]"
                                    >
                                        <div class="grid gap-2">
                                            <Label
                                                :for="`option-${questionIndex}-${optionIndex}`"
                                                >Option
                                                {{ optionIndex + 1 }}</Label
                                            >
                                            <Input
                                                :id="`option-${questionIndex}-${optionIndex}`"
                                                v-model="option.label"
                                                placeholder="Option label"
                                            />
                                        </div>

                                        <div class="flex items-end gap-2">
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                @click="
                                                    moveOption(
                                                        question,
                                                        optionIndex,
                                                        -1,
                                                    )
                                                "
                                            >
                                                <ChevronUp class="size-4" />
                                            </Button>
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                @click="
                                                    moveOption(
                                                        question,
                                                        optionIndex,
                                                        1,
                                                    )
                                                "
                                            >
                                                <ChevronDown class="size-4" />
                                            </Button>
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="outline"
                                                class="text-red-600 hover:text-red-600"
                                                @click="
                                                    removeOption(
                                                        question,
                                                        optionIndex,
                                                    )
                                                "
                                            >
                                                <Trash2 class="size-4" />
                                            </Button>
                                        </div>
                                    </div>

                                    <InputError
                                        :message="
                                            fieldError(
                                                `questions.${questionIndex}.options`,
                                            )
                                        "
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        <Card
                            class="border-dashed border-border bg-card shadow-none"
                        >
                            <CardContent class="space-y-4 p-6">
                                <div>
                                    <h3 class="text-lg font-semibold">
                                        Add another question
                                    </h3>
                                    <p class="text-sm text-muted-foreground">
                                        Choose a question type and it will be
                                        appended to the end of the form.
                                    </p>
                                </div>

                                <div
                                    class="grid [grid-template-columns:repeat(auto-fit,minmax(11rem,1fr))] gap-3"
                                >
                                    <Button
                                        v-for="type in questionTypes"
                                        :key="type.value"
                                        type="button"
                                        variant="outline"
                                        class="h-auto min-w-0 flex-col items-start border-border px-4 py-4 text-left"
                                        @click="addQuestion(type.value)"
                                    >
                                        <span
                                            class="w-full overflow-hidden font-medium text-ellipsis"
                                            >{{ type.label }}</span
                                        >
                                        <span
                                            class="mt-1 block w-full overflow-hidden text-xs leading-5 text-ellipsis text-muted-foreground"
                                            >{{ type.description }}</span
                                        >
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <div class="space-y-6">
                    <Card class="border-border bg-card">
                        <CardHeader>
                            <CardTitle>Publishing snapshot</CardTitle>
                            <CardDescription
                                >Keep an eye on the public share link and
                                current survey state while you
                                edit.</CardDescription
                            >
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="border border-border bg-background p-4">
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="text-xs tracking-[0.22em] text-muted-foreground uppercase"
                                        >
                                            Share link
                                        </p>
                                        <p
                                            class="mt-2 text-sm font-medium break-all"
                                        >
                                            {{
                                                shareUrl ||
                                                'Generated after the survey is created.'
                                            }}
                                        </p>
                                    </div>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        :disabled="!shareUrl"
                                        @click="copyShareUrl"
                                    >
                                        <Copy class="size-4" />
                                    </Button>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div
                                    class="border border-border bg-background p-4"
                                >
                                    <p
                                        class="text-xs tracking-[0.22em] text-muted-foreground uppercase"
                                    >
                                        Questions
                                    </p>
                                    <p class="mt-2 text-3xl font-semibold">
                                        {{ form.questions.length }}
                                    </p>
                                </div>
                                <div
                                    class="border border-border bg-background p-4"
                                >
                                    <p
                                        class="text-xs tracking-[0.22em] text-muted-foreground uppercase"
                                    >
                                        Status
                                    </p>
                                    <SurveyStatusBadge
                                        :status="survey.status"
                                        class="mt-2"
                                    />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
