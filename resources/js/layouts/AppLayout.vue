<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { CirclePlus, LayoutGrid, Rows3 } from 'lucide-vue-next';
import { computed } from 'vue';
import { create } from '@/actions/App/Http/Controllers/Survey/SurveyController';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { dashboard, home, login, register } from '@/routes';
import type { BreadcrumbItem, User } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();

const user = computed(() => page.props.auth?.user as User | null | undefined);
const currentPath = computed(() => page.url.split('?')[0]);

const navigationItems = [
    {
        title: 'Studio',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Library',
        href: '/surveys',
        icon: Rows3,
    },
    {
        title: 'New form',
        href: create(),
        icon: CirclePlus,
    },
];

const resolveHref = (href: string | { url: string }): string =>
    typeof href === 'string' ? href : href.url;

const isActive = (href: string | { url: string }): boolean => {
    const target = resolveHref(href);

    if (target === '/dashboard') {
        return currentPath.value === '/dashboard';
    }

    if (target === '/surveys') {
        return currentPath.value === '/surveys';
    }

    return currentPath.value.startsWith(target);
};
</script>

<template>
    <div
        class="relative min-h-screen overflow-hidden bg-background text-foreground"
    >
        <div class="absolute inset-x-0 top-0 h-1 bg-[var(--color-chart-2)]" />

        <div
            class="relative mx-auto flex min-h-screen w-full max-w-5xl flex-col px-4 py-5 sm:px-6 xl:max-w-[70vw] xl:px-0"
        >
            <header
                class="sticky top-0 z-30 mb-6 border-b border-border bg-background/95 px-1 py-4 backdrop-blur sm:px-0"
            >
                <div class="flex items-center gap-3">
                    <Link
                        :href="user ? dashboard() : home()"
                        class="flex min-w-0 items-center gap-3"
                    >
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
                                built for clean answers
                            </p>
                        </div>
                    </Link>

                    <nav
                        v-if="user"
                        class="ml-4 hidden items-center gap-4 md:flex"
                    >
                        <Link
                            v-for="item in navigationItems"
                            :key="item.title"
                            :href="item.href"
                            class="inline-flex items-center gap-2 border-b px-0 py-2 text-sm transition-colors"
                            :class="
                                isActive(item.href)
                                    ? 'border-foreground text-foreground'
                                    : 'border-transparent text-muted-foreground hover:border-border hover:text-foreground'
                            "
                        >
                            <component :is="item.icon" class="size-4" />
                            {{ item.title }}
                        </Link>
                    </nav>

                    <div class="ml-auto flex items-center gap-2">
                        <div class="hidden sm:block">
                            <AppearanceTabs />
                        </div>

                        <template v-if="user">
                            <DropdownMenu>
                                <DropdownMenuTrigger :as-child="true">
                                    <Button variant="ghost" class="h-10 px-1.5">
                                        <Avatar
                                            class="size-8 border border-border"
                                        >
                                            <AvatarImage
                                                v-if="user.avatar"
                                                :src="user.avatar"
                                                :alt="user.name"
                                            />
                                            <AvatarFallback
                                                class="bg-muted font-semibold text-foreground"
                                            >
                                                {{ getInitials(user.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    align="end"
                                    class="w-60 border border-border bg-popover"
                                >
                                    <UserMenuContent :user="user" />
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </template>
                        <template v-else>
                            <Button
                                as-child
                                variant="ghost"
                                class="px-4 text-sm"
                            >
                                <Link :href="login()">Log in</Link>
                            </Button>
                            <Button as-child class="px-4 text-sm">
                                <Link :href="register()">Get started</Link>
                            </Button>
                        </template>
                    </div>
                </div>

                <div
                    v-if="props.breadcrumbs.length > 1"
                    class="mt-3 border-t border-border pt-3 text-sm text-muted-foreground"
                >
                    <Breadcrumbs :breadcrumbs="breadcrumbs" />
                </div>
            </header>

            <main class="flex-1">
                <slot />
            </main>
        </div>
    </div>
</template>
