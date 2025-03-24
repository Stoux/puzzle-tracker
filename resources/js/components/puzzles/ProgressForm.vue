<script setup lang="ts">
import { Puzzle, PuzzleProgression } from '@/types/wasgij';
import { useForm } from '@inertiajs/vue3';
import { getStatusLabelFor, PuzzleProgressionStatus } from '@/lib/data';
import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { onBeforeMount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';
import { LoaderCircle } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    puzzle: Puzzle;
    progression?: PuzzleProgression;
}>();

const statusOptions = {
    [PuzzleProgressionStatus.STARTED]: getStatusLabelFor(PuzzleProgressionStatus.STARTED),
    [PuzzleProgressionStatus.FINISHED]: getStatusLabelFor(PuzzleProgressionStatus.FINISHED),
    [PuzzleProgressionStatus.ABORTED]: getStatusLabelFor(PuzzleProgressionStatus.ABORTED),
};

// Build the form data for a progression instance
const form = useForm({
    status: PuzzleProgressionStatus.STARTED,
    started_on: '',
    completed_on: '',
    comments: '',
    keepImages: [] as number[],
    newImages: [],
});

// Keep track of the open state (so we can manually close it)
const isOpen = ref(false);

function handleNewProgression() {
    form.newImages = [];
    if (props.progression) {
        form.status = props.progression.status;
        form.started_on = props.progression.started_on ?? '';
        form.completed_on = props.progression.completed_on ?? '';
        form.comments = props.progression.comments ?? '';
        form.keepImages = props.progression?.images.map((item) => item.id) ?? [];
    } else {
        form.status = PuzzleProgressionStatus.STARTED;
        form.started_on = '';
        form.completed_on = '';
        form.comments = '';
        form.keepImages = [];
    }
}

watch(
    () => props.progression,
    () => handleNewProgression(),
);
onBeforeMount(() => handleNewProgression());

function submit() {
    if (form.processing) {
        return;
    }

    const method = props.progression ? 'put' : 'post';
    const url = props.progression
        ? route('puzzles.progress.edit', [props.puzzle.id, props.progression.id])
        : route('puzzles.progress.new', [props.puzzle.id]);

    form.submit(method, url, {
        preserveScroll: true,
        onSuccess: () => {
            isOpen.value = false;
        },
    });
}
</script>

<template>
    <Sheet ref="sheet" v-model:open="isOpen">
        <SheetTrigger>
            <slot />
        </SheetTrigger>
        <SheetContent side="right">
            <SheetHeader>
                <SheetTitle>{{ progression ? 'Aanpassen' : 'Nieuw toevoegen' }}</SheetTitle>
                <SheetDescription>Heb jij deze puzzel al afgerond?</SheetDescription>
            </SheetHeader>

            <form @submit.prevent="submit" class="my-6 flex flex-col gap-6">
                <div class="grid gap-2">
                    <Label htmlFor="status">Status</Label>
                    <select
                        name="status"
                        id="status"
                        class="mt-1 block h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                        :disabled="form.processing"
                        v-model="form.status"
                    >
                        <option v-for="(label, value) of statusOptions" :value="value">
                            {{ label }}
                        </option>
                    </select>
                    <InputError :message="form.errors.status" />
                </div>

                <div class="grid gap-2">
                    <Label htmlFor="started_on">Gestart op</Label>
                    <Input id="started_on" type="date" class="mt-1 block w-full" v-model="form.started_on" :disabled="form.processing" />
                    <InputError :message="form.errors.started_on" />
                </div>

                <div class="grid gap-2">
                    <Label htmlFor="completed_on">Afgerond op</Label>
                    <Input id="completed_on" type="date" class="mt-1 block w-full" v-model="form.completed_on" :disabled="form.processing" />
                    <InputError :message="form.errors.completed_on" />
                </div>

                <div class="grid gap-2">
                    <Label htmlFor="comments">Opmerkingen?</Label>
                    <textarea
                        class="mt-1 block h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                        v-model="form.comments"
                        :disabled="form.processing"
                        rows="4"
                        name="comments"
                        id="comments"
                    ></textarea>
                    <InputError :message="form.errors.comments" />
                </div>

                <!-- TODO: Fotos -->
            </form>

            <SheetFooter>
                <Button @click="submit" :disabled="form.processing"> Opslaan </Button>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>
