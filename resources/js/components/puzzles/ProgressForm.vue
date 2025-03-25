<script setup lang="ts">
import { Puzzle, PuzzleProgression } from '@/types/wasgij';
import { useForm } from '@inertiajs/vue3';
import { getStatusLabelFor, PuzzleProgressionStatus } from '@/lib/data';
import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { onBeforeMount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';
import {ImagePlus, CircleMinus} from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {Checkbox} from "@/components/ui/checkbox";
import {ScrollArea} from "@/components/ui/scroll-area";

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
    deleteImages: [] as number[],
    newImages: [] as (File|undefined)[],
});

// Keep track of the open state (so we can manually close it)
const isOpen = ref(false);

function handleNewProgression() {
    form.newImages = [];
    form.deleteImages = [];
    if (props.progression) {
        form.status = props.progression.status;
        form.started_on = props.progression.started_on ?? '';
        form.completed_on = props.progression.completed_on ?? '';
        form.comments = props.progression.comments ?? '';
    } else {
        form.status = PuzzleProgressionStatus.FINISHED;
        form.started_on = '';
        form.completed_on = '';
        form.comments = '';
    }
}

watch(
    () => props.progression,
    () => handleNewProgression(),
);
onBeforeMount(() => handleNewProgression());

function addPhoto() {
    form.newImages.push(undefined);
}

function toggleDeleteImage(id: number) {
   const indexOf = form.deleteImages.indexOf(id);
   if (indexOf === -1) {
       form.deleteImages = [ ...form.deleteImages, id ];
   } else {
       const copy = [ ...form.deleteImages ];
       copy.splice(indexOf, 1);
       form.deleteImages = copy;
   }
}

function submit() {
    if (form.processing) {
        return;
    }

    const url = props.progression
        ? route('puzzles.progress.edit', [props.puzzle.id, props.progression.id])
        : route('puzzles.progress.new', [props.puzzle.id]);

    // Always do POST as PUT doesn't play nice with multipart/formdata
    form.submit('post', url, {
        preserveScroll: true,
        forceFormData: true,
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
        <SheetContent side="right" class="flex flex-col">
            <SheetHeader>
                <SheetTitle>{{ progression ? 'Aanpassen' : 'Nieuw toevoegen' }}</SheetTitle>
                <SheetDescription>Heb jij deze puzzel al afgerond?</SheetDescription>
            </SheetHeader>

            <form @submit.prevent="submit" class="my-6 flex flex-col gap-6 grow overflow-y-scroll max-h-[100%]">
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

                <div class="grid gap-2" v-for="(image, index) of (progression?.images ?? [])">
                    <Label>Foto: {{ index + 1 }}</Label>
                    <a :href="image.url" target="_blank" class="flex items-center justify-center">
                        <img loading="lazy" :src="image.url" alt="">
                    </a>

                    <div class="items-top flex gap-x-2">
                        <Checkbox :id="'old-photo-' + image.id" :checked="form.deleteImages.includes(image.id)" @click="toggleDeleteImage(image.id)" />
                        <div class="grid gap-1.5 leading-none">
                            <label
                                :for="'old-photo-' + image.id"
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                Foto verwijderen?
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid gap-2" v-for="(image, index) of form.newImages">
                    <Label :htmlFor="'new-photo-' + index" class="flex items-center justify-between">
                        Foto: {{ (progression?.images?.length ?? 0) + index + 1 }}
<!--                        <Button variant="ghost" size="icon" class="mr-2 h-9 w-9 text-red-500" @click.prevent="addPhoto">-->
<!--                            <CircleMinus class="h-5 w-5" />-->
<!--                        </Button>-->
                    </Label>
                    <input :id="'new-photo-' + index" type="file" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                           @change="(event: any) => { console.log('Changed', event); form.newImages[index] = event.target.files[0] ?? undefined }" :disabled="form.processing" />
                    <InputError :message="form.errors.newImages ? form.errors.newImages[index] : ''" />
                </div>

                <div class="grid gap-2">
                    <Label>Foto toevoegen</Label>
                    <Button variant="secondary" size="icon" class="mr-2 h-9 w-9 text-green-500" @click.prevent="addPhoto">
                        <ImagePlus class="h-5 w-5" />
                    </Button>
                </div>

            </form>

            <SheetFooter>
                <Button @click="submit" :disabled="form.processing"> Opslaan </Button>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>
