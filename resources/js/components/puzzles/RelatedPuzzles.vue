<script setup lang="ts">

import {PuzzleDetails} from "@/types/wasgij";
import {Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious} from "@/components/ui/carousel";
import {Link} from "@inertiajs/vue3";
import {AspectRatio} from "@/components/ui/aspect-ratio";
import {getTypeLabelFor} from "@/lib/data";

defineProps<{
    puzzle: PuzzleDetails,
}>();

</script>

<template>
    <h3 class="text-2xl">Gerelateerde puzzels</h3>

    <!-- Main image carousel -->
    <div class="p-8">
        <Carousel class="relative w-full">
            <CarouselContent>
                <CarouselItem class="sm:max-w-[50%] max-w-[75%]" v-for="relation of puzzle.relations" :key="relation.id">
                    <Link :href="route('puzzles.show', [ relation.relates_to.id ] )">
                        <div class="flex flex-col w-full h-full items-center">
                            <AspectRatio :ratio="1">
                                <img :src="relation.relates_to.thumbnail" :alt="relation.relates_to.puzzle_title" v-if="relation.relates_to.thumbnail" />
                            </AspectRatio>

                            <p class="text-sm">Artikel: {{ relation.relates_to.sku }}</p>
                            <p class="text-sm">{{ relation.relates_to.collection_tag }} {{ relation.relates_to.collection_number }}</p>
                            <p class="text-lg">{{ !relation.is_source ? 'Origineel' : getTypeLabelFor(relation.type) }}</p>
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
