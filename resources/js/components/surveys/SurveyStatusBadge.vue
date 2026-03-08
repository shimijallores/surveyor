<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { SurveyStatus } from '@/types';

defineProps<{
    status: SurveyStatus;
}>();

const labels: Record<SurveyStatus, string> = {
    draft: 'Draft',
    published: 'Live',
    closed: 'Closed',
};

const classes: Record<SurveyStatus, string> = {
    draft: 'border-amber-600/30 bg-amber-500/8 text-amber-700 dark:text-amber-300',
    published:
        'border-blue-600/30 bg-blue-500/8 text-blue-700 dark:text-blue-300',
    closed: 'border-border bg-muted/60 text-muted-foreground',
};
</script>

<template>
    <Badge variant="outline" :class="classes[status]">
        {{ labels[status] }}
    </Badge>
</template>
