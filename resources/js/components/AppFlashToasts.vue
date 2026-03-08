<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';

type ToastKind = 'success' | 'info' | 'warning' | 'error';

type ToastPayload = {
    title: string;
    description?: string;
    type: ToastKind;
};

const page = usePage();

const normalizeFlashStatus = (status: unknown): ToastPayload | null => {
    if (typeof status !== 'string' || status.length === 0) {
        return null;
    }

    if (status === 'response-submitted') {
        return {
            title: 'Response submitted',
            description: 'Your answers have been recorded successfully.',
            type: 'success',
        };
    }

    return {
        title: status,
        type: 'success',
    };
};

watch(
    () => page.props,
    (props) => {
        const payload = normalizeFlashStatus(props.flash?.status);

        if (!payload) {
            return;
        }

        toast[payload.type](payload.title, {
            description: payload.description,
        });
    },
    { immediate: true },
);
</script>

<template />
