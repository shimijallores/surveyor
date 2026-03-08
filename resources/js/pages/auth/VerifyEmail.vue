<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue-sonner';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

const props = defineProps<{
    status?: string;
}>();

watch(
    () => props.status,
    (status) => {
        if (status !== 'verification-link-sent') {
            return;
        }

        toast.success('Verification link sent', {
            description:
                'Check your inbox for the latest email verification link.',
        });
    },
    { immediate: true },
);
</script>

<template>
    <AuthLayout
        title="Verify email"
        description="Please verify your email address by clicking on the link we just emailed to you."
    >
        <Head title="Email verification" />

        <Form
            v-bind="send.form()"
            class="space-y-6 text-center"
            v-slot="{ processing }"
        >
            <Button :disabled="processing" variant="secondary">
                <Spinner v-if="processing" />
                Resend verification email
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm"
            >
                Log out
            </TextLink>
        </Form>
    </AuthLayout>
</template>
