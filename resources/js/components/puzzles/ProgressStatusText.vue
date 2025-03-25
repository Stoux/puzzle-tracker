<script setup lang="ts">
import type { DetailedPuzzleRelation, PuzzleDetails, PuzzleProgression } from '@/types/wasgij';
import { usePage } from '@inertiajs/vue3';
import type { SharedData, User } from '@/types';
import { computed } from 'vue';
import { PuzzleProgressionStatus, PuzzleRelationType } from '@/lib/data';

const props = defineProps<{
    puzzle: PuzzleDetails;
}>();

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

enum FinishType {
    FINISHED,
    FINISHED_RERELEASE,
    FINISHED_ORIGINAL,
    NOT_FINISHED,
}

const finished = computed<FinishType>(() => {
    // Finished this puzzle?
    if (hasFinished(props.puzzle.progressions)) {
        return FinishType.FINISHED;
    }

    // Maybe finished a puzzle (that isn't the retro edition)
    const finishedRelation = props.puzzle.relations.find((relation: DetailedPuzzleRelation) => {
        if (relation.type === PuzzleRelationType.RETRO) {
            return false;
        }

        return hasFinished(relation.progressions);
    });
    if (finishedRelation) {
        // Is the prop puzzle the original one? => We finished the rerelease
        return finishedRelation.is_source ? FinishType.FINISHED_RERELEASE : FinishType.FINISHED_ORIGINAL;
    }

    // Nope, not finished.
    return FinishType.NOT_FINISHED;
});

const finishedRetro = computed(() => {
    return props.puzzle.relations.some((r) => r.type === PuzzleRelationType.RETRO && hasFinished(r.progressions));
});

function hasFinished(progressions: PuzzleProgression[]) {
    return progressions.some((progression) => progression.user.id === user.id && progression.status === PuzzleProgressionStatus.FINISHED);
}
</script>

<template>
    <p>
        Jij hebt deze puzzel
        <span v-if="finished === FinishType.NOT_FINISHED">nog <strong class="font-bold text-destructive">niet</strong></span>
        <span v-else>
            <span class="text-sm" v-if="finished === FinishType.FINISHED_RERELEASE"> (de heruitgave) </span>
            <span class="text-sm" v-if="finished === FinishType.FINISHED_ORIGINAL"> (de originele uitgave) </span>
            al <strong class="font-bold text-green-600 dark:text-green-400">wel</strong>
        </span>
        afgerond.
    </p>
    <p class="text-sm" v-if="finishedRetro">
        (Je hebt de Retro versie <strong class="font-bold text-green-600 dark:text-green-400">{{ finished === FinishType.NOT_FINISHED ? 'wel' : 'ook' }}</strong> afgerond.)
    </p>
</template>
