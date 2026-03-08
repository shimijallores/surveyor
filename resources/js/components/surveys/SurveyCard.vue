<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { CalendarClock, Eye, ExternalLink } from 'lucide-vue-next';
import { computed } from 'vue';
import { show } from '@/actions/App/Http/Controllers/Survey/SurveyController';
import SurveyStatusBadge from '@/components/surveys/SurveyStatusBadge.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { SurveySummary } from '@/types';

const props = defineProps<{
    survey: SurveySummary;
}>();

const shareUrl = computed(() => {
    if (/^https?:\/\//.test(props.survey.share_path)) {
        return props.survey.share_path;
    }

    if (typeof window === 'undefined') {
        return props.survey.share_path;
    }

    return `${window.location.origin}${props.survey.share_path}`;
});

const viewHref = computed(() => show({ survey: props.survey.id }));

const openSurvey = (): void => {
    router.visit(viewHref.value);
};

const handleCardKeydown = (event: KeyboardEvent): void => {
    if (event.target !== event.currentTarget) {
        return;
    }

    if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        openSurvey();
    }
};
</script>

<template>
    <Card
        class="group relative cursor-pointer overflow-hidden border-border bg-card transition-colors duration-200 hover:bg-background hover:shadow-sm focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
        role="link"
        tabindex="0"
        @click="openSurvey"
        @keydown="handleCardKeydown"
    >
        <CardHeader class="space-y-3 pb-2">
            <div class="flex items-center justify-between gap-3">
                <SurveyStatusBadge :status="survey.status" />
                <span
                    class="text-[10px] tracking-[0.24em] text-muted-foreground uppercase"
                >
                    {{ survey.public_id }}
                </span>
            </div>

            <div class="space-y-2">
                <CardTitle class="text-base leading-tight sm:text-lg">
                    {{ survey.title }}
                </CardTitle>
                <CardDescription
                    class="line-clamp-2 min-h-10 text-sm leading-5"
                >
                    {{
                        survey.description ||
                        'No description yet. Add context so respondents know what this survey is for.'
                    }}
                </CardDescription>
            </div>
        </CardHeader>

        <CardContent class="space-y-3 pt-0">
            <div class="grid grid-cols-3 gap-3 text-sm">
                <div class="space-y-1">
                    <p
                        class="text-[10px] tracking-[0.2em] text-muted-foreground uppercase"
                    >
                        Questions
                    </p>
                    <p class="mt-1 text-lg leading-none font-semibold">
                        {{ survey.question_count }}
                    </p>
                </div>
                <div class="space-y-1">
                    <p
                        class="text-[10px] tracking-[0.2em] text-muted-foreground uppercase"
                    >
                        Responses
                    </p>
                    <p class="mt-1 text-lg leading-none font-semibold">
                        {{ survey.response_count }}
                    </p>
                </div>
                <div class="space-y-1">
                    <p
                        class="text-[10px] tracking-[0.2em] text-muted-foreground uppercase"
                    >
                        Complete
                    </p>
                    <p class="mt-1 text-lg leading-none font-semibold">
                        {{ survey.completed_count }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs text-muted-foreground">
                <CalendarClock class="size-4" />
                <span
                    >Updated
                    {{
                        survey.updated_at
                            ? new Date(survey.updated_at).toLocaleString()
                            : 'recently'
                    }}</span
                >
            </div>
        </CardContent>

        <CardFooter
            class="grid gap-2 border-t border-border pt-4 sm:grid-cols-2"
            @click.stop
        >
            <Button as-child variant="outline" class="h-9 px-3">
                <a :href="shareUrl" target="_blank" rel="noopener noreferrer">
                    <ExternalLink class="mr-2 size-4" />
                    Direct link
                </a>
            </Button>

            <Button as-child class="h-9 px-3">
                <Link :href="viewHref">
                    <Eye class="mr-2 size-4" />
                    View
                </Link>
            </Button>
        </CardFooter>
    </Card>
</template>
