<script setup lang="ts">

import type {PuzzleDetails} from "@/types/wasgij";
import {computed} from "vue";

const props = defineProps<{
    puzzle: PuzzleDetails,
}>()

const at = computed<string[]>(() => {
    return props.puzzle.purchases.map(purchase => purchase.currently_at?.name ?? `${purchase.owner.name}?`)
});

</script>

<template>
    <p>
        <span v-if="at.length">
            Deze puzzel ligt bij
            <template v-for="(name, index) of at" :key="name"><span v-if="index > 0 && index === (at.length - 1)"> & </span><span v-else-if="index > 0">, </span><strong class="font-bold">{{ name }}</strong></template>
            <span>. </span>
        </span>
        <span v-else>
            Nog niemand heeft deze puzzel!
        </span>
        <a
            href="#purchase-overview"
            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:!decoration-current dark:decoration-neutral-500"
        >Bekijk overzicht</a
        >
    </p>
</template>
