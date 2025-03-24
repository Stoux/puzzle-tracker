<script setup lang="ts">

import type {PuzzleDetails} from "@/types/wasgij";

defineProps<{
    puzzle: PuzzleDetails,
}>()

</script>

<template>
    <p>
        Deze puzzel ligt nu bij <strong class="font-bold">jou</strong> & <strong class="font-bold">Yvonne</strong>.
        <a
            href="#purchase-overview"
            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:!decoration-current dark:decoration-neutral-500"
        >Bekijk overzicht</a
        >
    </p>
</template>
