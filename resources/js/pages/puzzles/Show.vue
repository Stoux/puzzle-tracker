<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import type {PuzzleDetails} from "@/types/wasgij";
import {computed, ref, watch} from "vue";
import ImageSlider from "@/components/puzzles/ImageSlider.vue";
import {Button} from "@/components/ui/button";
import HintDialog from "@/components/puzzles/HintDialog.vue";

const props = defineProps<{
    puzzle: PuzzleDetails,
}>();


// Computed
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Puzzels',
        href: '/puzzels',
    },
    {
        title: props.puzzle.puzzle_title,
        href: '/puzzels/' + props.puzzle.id,
    }
]);

const labels = computed<{ label: string, value: string }[]>(() => {
    const values: {[label: string]: string|null|undefined} = {
        'Artikelnummer': props.puzzle.sku,
        'Aantal': props.puzzle.number_of_pieces_label,
        'Jaar': props.puzzle.year?.toString(10),
        'Kunstenaar': props.puzzle.artist,
        'Grootte': props.puzzle.dimensions,
    };

    const result: { label: string, value: string }[] = [];
    Object.keys(values).forEach((key: string) => {
        const value = values[key];
        if (value) {
            result.push({ label: key, value: value });
        }
    });

    return result;
});

const hints = computed<{ hint: number|string, image: string}[]>(() => {
    const hints: {hint: number|string, image: string}[] = [];
    Object.entries(props.puzzle.hints).forEach(([key, value]) => {
        if (value) {
            hints.push({ hint: key, image: value });
        }
    });
    return hints;
});


</script>

<template>
    <Head title="Puzzels" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 lg:grid-cols-2">

                <div class="relative">
                    <div class="pt-4 absolute">
                        <span class="p-2 rounded-full border text-gray-800 border-gray-800 dark:text-gray-100 dark:border-gray-100" v-if="puzzle.website_label">
                            {{ puzzle.website_label }}
                        </span>
                    </div>

                    <ImageSlider :puzzle="puzzle" />
                </div>

                <div class="flex flex-col gap-8">

                    <h1 class="text-7xl font-semibold tracking-tight" v-if="puzzle.collection_tag">
                        <span class="text-4xl">{{puzzle.collection_tag}} {{puzzle.collection_number}}:</span> <br />
                        {{ puzzle.puzzle_title }}
                    </h1>
                    <h1 class="text-3xl font-semibold tracking-tight" v-else>
                        {{ puzzle.puzzle_title }}
                    </h1>

                    <ul v-if="labels.length" class="flex flex-wrap gap-x-8 gap-y-2 text-lg">
                        <li v-for="(label, index) in labels" :key="index" :class="index === 0 ? '' : ''">
                            <strong>{{ label.label }}:</strong> {{ label.value }}
                        </li>
                    </ul>

                    <div v-html="puzzle.description" v-if="puzzle.description" />

                    <div class="flex gap-4" v-if="hints.length">
                        <HintDialog v-for="(hint, index) in hints" :key="hint.hint"
                            :hint="hint.hint" :image="hint.image" />
                    </div>

                    <!-- TODO: Add scroll to button: Relationships -->

                </div>

            </div>
        </div>
    </AppLayout>
</template>

