<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import type {PuzzleDetails, PuzzleUser} from '@/types/wasgij';
import { computed, ref, watch } from 'vue';
import ImageSlider from '@/components/puzzles/ImageSlider.vue';
import HintDialog from '@/components/puzzles/HintDialog.vue';
import ProgressStatusText from "@/components/puzzles/ProgressStatusText.vue";
import PurchaseStatusText from "@/components/puzzles/PurchaseStatusText.vue";
import ProgressOverview from "@/components/puzzles/ProgressOverview.vue";
import PurchaseOverview from "@/components/puzzles/PurchaseOverview.vue";
import RelatedPuzzles from "@/components/puzzles/RelatedPuzzles.vue";

const props = defineProps<{
    puzzle: PuzzleDetails;
    users: PuzzleUser[],
}>();

// Computed
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Puzzels',
        href: '/puzzels',
    },
    {
        title: props.puzzle.collection_tag ? `${props.puzzle.collection_tag} ${props.puzzle.collection_number}: ${props.puzzle.puzzle_title}` : props.puzzle.puzzle_title,
        href: '/puzzels/' + props.puzzle.id,
    },
]);

const labels = computed<{ label: string; value: string }[]>(() => {
    const values: { [label: string]: string | null | undefined } = {
        Artikelnummer: props.puzzle.sku,
        Aantal: props.puzzle.number_of_pieces_label,
        Jaar: props.puzzle.year?.toString(10),
        Kunstenaar: props.puzzle.artist,
        Grootte: props.puzzle.dimensions,
    };

    const result: { label: string; value: string }[] = [];
    Object.keys(values).forEach((key: string) => {
        const value = values[key];
        if (value) {
            result.push({ label: key, value: value });
        }
    });

    return result;
});

const hints = computed<{ hint: number | string; image: string }[]>(() => {
    const hints: { hint: number | string; image: string }[] = [];
    Object.entries(props.puzzle.hints).forEach(([key, value]) => {
        if (value) {
            hints.push({ hint: key, image: value });
        }
    });
    return hints;
});

const tags = computed<string[]>(() => {
    return [props.puzzle.website_label, ...props.puzzle.tags].filter((s) => !!s) as string[];
});
</script>

<template>
    <Head :title="`Puzzels: ${puzzle.puzzle_title}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 lg:grid-cols-2">
                <div class="relative">
                    <div class="absolute flex gap-2 pt-4" v-if="tags.length">
                        <span
                            class="rounded-full border border-gray-800 p-2 text-gray-800 dark:border-gray-100 dark:text-gray-100"
                            v-for="tag of tags"
                        >
                            {{ tag }}
                        </span>
                    </div>

                    <ImageSlider :puzzle="puzzle" />
                </div>

                <div class="flex flex-col gap-8">
                    <h1 class="text-7xl font-semibold tracking-tight" v-if="puzzle.collection_tag">
                        <span class="text-4xl">{{ puzzle.collection_tag }} {{ puzzle.collection_number }}:</span> <br />
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
                        <HintDialog v-for="(hint, index) in hints" :key="hint.hint" :hint="hint.hint" :image="hint.image" />
                    </div>

                    <hr />

                    <div class="flex gap-2">
                        <div class="flex flex-col gap-2 grow">
                            <ProgressStatusText :puzzle="puzzle" />
                        </div>
                        <div class="px-4 flex items-center">
                            <a href="#status-overview"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:!decoration-current dark:decoration-neutral-500">
                                Bekijk overzicht
                            </a>
                        </div>
                    </div>

                    <hr />

                    <div class="flex gap-2">
                        <div class="flex flex-col gap-2 grow">
                            <PurchaseStatusText :puzzle="puzzle" />
                        </div>
                        <div class="px-4 flex items-center">
                            <a href="#purchase-overview"
                               class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:!decoration-current dark:decoration-neutral-500">
                                Bekijk overzicht
                            </a>
                        </div>
                    </div>

                    <hr />

                    <div class="flex flex-col gap-2" v-if="puzzle.next_in_collection || puzzle.previous_in_collection || puzzle.relations.length">
                        <RelatedPuzzles :puzzle="puzzle" />
                    </div>

                </div>

                <div class="flex flex-col gap-8">
                    <hr />
                    <ProgressOverview :puzzle="puzzle" />
                </div>

                <div class="flex flex-col gap-8">
                    <hr />
                    <PurchaseOverview :puzzle="puzzle" :users="users" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
