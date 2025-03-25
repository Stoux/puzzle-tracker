<script setup lang="ts">
import type {DetailedPuzzleRelation, PurchasedPuzzle, Puzzle, PuzzleDetails} from '@/types/wasgij';
import { computed } from 'vue';
import { PuzzleRelationType } from '@/lib/data';

const props = defineProps<{
    puzzle: PuzzleDetails;
}>();

function getPuzzleAt(purchasedPuzzles: PurchasedPuzzle[]) {
    return purchasedPuzzles.map((purchase) => purchase.currently_at?.name ?? `${purchase.owner.name}?`);
}

function getPuzzleRelationTitle(relation: DetailedPuzzleRelation) {
    switch (relation.type) {
        case PuzzleRelationType.RETRO:
            return relation.is_source ? 'De retro editie' : 'Het origineel van deze retro';
        default:
            return relation.is_source ? 'De heruitgave' : 'De vorige uitgave'
    }
}

// Get the puzzle (+ with relations) & where it's at.
const puzzles = computed(() => {
    const result: { title: string, puzzle: Puzzle, at: string[] }[] = [
        {
            title: 'Deze puzzel',
            puzzle: props.puzzle,
            at: getPuzzleAt(props.puzzle.purchases)
        },
    ];

    props.puzzle.relations.forEach((relation) => {
        if (!relation.purchases.length) {
            return;
        }

        result.push({
            title: getPuzzleRelationTitle(relation),
            puzzle: relation.relates_to,
            at: getPuzzleAt(relation.purchases),
        })
    });

    return result;
});
</script>

<template>
    <p v-for="(status, index) in puzzles" :key="status.puzzle.id">
        <span v-if="status.at.length">
            {{ status.title }} ligt bij
            <template v-for="(name, index) of status.at" :key="name"
                ><span v-if="index > 0 && index === status.at.length - 1"> & </span><span v-else-if="index > 0">, </span
                ><strong class="font-bold">{{ name }}</strong></template
            >
            <span>. </span>
        </span>
        <span v-else> Nog niemand heeft deze puzzel! </span>
        <!-- Only show overview for the current puzzle -->
    </p>
</template>
