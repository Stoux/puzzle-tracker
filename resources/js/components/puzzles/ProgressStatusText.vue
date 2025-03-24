<script setup lang="ts">
import type { PuzzleDetails } from '@/types/wasgij';
import { usePage } from '@inertiajs/vue3';
import type { SharedData, User } from '@/types';
import {computed} from "vue";
import {PuzzleProgressionStatus} from "@/lib/data";

const props = defineProps<{
    puzzle: PuzzleDetails;
}>();

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const finished = computed(() =>
    props.puzzle.progressions.some((progression) => progression.user.id === user.id && progression.status === PuzzleProgressionStatus.FINISHED),
);
</script>

<template>
    <p>
        Jij hebt deze puzzel
        <span v-if="finished">al <strong class="font-bold text-green-600 dark:text-green-400">wel</strong></span>
        <span v-if="!finished">nog <strong class="font-bold text-destructive">niet</strong></span>
        afgerond.
        <a
            href="#status-overview"
            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:!decoration-current dark:decoration-neutral-500"
            >Bekijk overzicht</a
        >
    </p>
</template>
