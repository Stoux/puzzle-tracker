<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Thema instellingen',
        href: '/settings/appearance',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Thema instellingen" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Thema instellingen" description="Update je thema instellingen" />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
