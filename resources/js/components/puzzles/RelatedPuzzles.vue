<script setup lang="ts">

import {Puzzle, PuzzleDetails} from "@/types/wasgij";
import {Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious} from "@/components/ui/carousel";
import {Link} from "@inertiajs/vue3";
import {AspectRatio} from "@/components/ui/aspect-ratio";
import {getTypeLabelFor} from "@/lib/data";
import {computed} from "vue";

const props = defineProps<{
    puzzle: PuzzleDetails,
}>();

type RelatedPuzzle = { puzzle: Puzzle, label: string };

const relatedPuzzles = computed<RelatedPuzzle[]>(() => {
    const puzzles: RelatedPuzzle[] = props.puzzle.relations.map(relation => {
        return {
            puzzle: relation.relates_to,
            label: relation.is_source ? getTypeLabelFor(relation.type) : 'Origineel',
        }
    })

    if (props.puzzle.next_in_collection) {
        puzzles.push({
            puzzle: props.puzzle.next_in_collection,
            label: 'Volgende in serie',
        })
    }

    if (props.puzzle.previous_in_collection) {
        puzzles.push({
            puzzle: props.puzzle.previous_in_collection,
            label: 'Vorige',
        })
    }

    return puzzles;
});

</script>

<template>
    <h3 class="text-2xl">Gerelateerde puzzels</h3>

    <!-- Main image carousel -->
    <div class="p-8">
        <Carousel class="relative w-full">
            <CarouselContent>
                <CarouselItem class="sm:max-w-[40%] max-w-[75%]" v-for="(related, index) of relatedPuzzles" :key="index">
                    <Link :href="route('puzzles.show', [ related.puzzle.id ] )">
                        <div class="flex flex-col w-full h-full items-center">
                            <AspectRatio :ratio="1">
                                <img :src="related.puzzle.thumbnail" :alt="related.puzzle.puzzle_title" v-if="related.puzzle.thumbnail" />
                            </AspectRatio>

                            <p class="text-sm">Artikel: {{ related.puzzle.sku }}</p>
                            <p class="text-sm">{{ related.puzzle.collection_tag }} {{ related.puzzle.collection_number }}</p>
                            <p class="text-lg">{{ related.label }}</p>
                        </div>
                    </Link>

                </CarouselItem>
            </CarouselContent>
            <CarouselPrevious />
            <CarouselNext />
        </Carousel>
    </div>
</template>

<style scoped>

</style>
