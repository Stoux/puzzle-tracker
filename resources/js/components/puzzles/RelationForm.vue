<script setup lang="ts">
import {BarePuzzleRelation} from '@/types/wasgij';
import { useForm } from '@inertiajs/vue3';
import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { onBeforeMount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import {getAllTypeOptions} from "@/lib/data";

const props = defineProps<{
    relation?: BarePuzzleRelation,
    puzzles: { id: number, title: string }[],
}>();

const typeOptions = getAllTypeOptions();

// Build the form data for a progression instance
const form = useForm({
    puzzle_id: null as number|null,
    relates_to_id: null as number|null,
    type: '',
    comment: '',
});

// Keep track of the open state (so we can manually close it)
const isOpen = ref(false);

function handleNewRelation() {
    if (props.relation) {
        form.puzzle_id = props.relation.puzzle_id;
        form.relates_to_id = props.relation.relates_to_id;
        form.type = props.relation.type;
        form.comment = props.relation.comment ?? '';
    } else {
        form.puzzle_id = null;
        form.relates_to_id = null;
        form.type = '';
        form.comment = '';
    }
}

watch(
    () => props.relation,
    () => handleNewRelation(),
);
onBeforeMount(() => handleNewRelation());

function submit() {
    if (form.processing) {
        return;
    }

    const method = props.relation ? 'put' : 'post';
    const url = props.relation
        ? route('relations.edit', [props.relation.id])
        : route('relations.new');

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
        <SheetContent side="right" class="sm:max-w-full w-[100vw] sm:w-[75vw] md:w-[50vw]">
            <SheetHeader>
                <SheetTitle>{{ relation ? 'Aanpassen' : 'Nieuw toevoegen' }}</SheetTitle>
                <SheetDescription>Wie heeft deze puzzel gekocht?</SheetDescription>
            </SheetHeader>

            <form @submit.prevent="submit" class="my-6 flex flex-col gap-6">
                <div class="grid gap-2">
                    <Label htmlFor="puzzle_id">Puzzel</Label>
                    <select
                        name="puzzle_id"
                        id="puzzle_id"
                        class="mt-1 block h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                        :disabled="form.processing"
                        v-model="form.puzzle_id"
                    >
                        <option v-for="puzzle of puzzles" :value="puzzle.id" :key="puzzle.id">
                            {{ puzzle.title }}
                        </option>
                    </select>
                    <InputError :message="form.errors.puzzle_id" />
                </div>

                <div class="grid gap-2">
                    <Label htmlFor="relates_to_id">Gerelateerd aan</Label>
                    <select
                        name="relates_to_id"
                        id="relates_to_id"
                        class="mt-1 block h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                        :disabled="form.processing"
                        v-model="form.relates_to_id"
                    >
                        <option v-for="puzzle of puzzles" :value="puzzle.id" :key="puzzle.id">
                            {{ puzzle.title }}
                        </option>
                    </select>
                    <InputError :message="form.errors.relates_to_id" />
                </div>

                <div class="grid gap-2">
                    <Label htmlFor="type">Type</Label>
                    <select
                        name="type"
                        id="type"
                        class="mt-1 block h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                        :disabled="form.processing"
                        v-model="form.type"
                    >
                        <option v-for="(label, type) of typeOptions" :value="type" :key="type">
                            {{ label }}
                        </option>
                    </select>
                    <InputError :message="form.errors.type" />
                </div>


                <div class="grid gap-2">
                    <Label htmlFor="comment">Opmerking</Label>
                    <textarea
                        class="mt-1 block h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                        v-model="form.comment"
                        :disabled="form.processing"
                        rows="4"
                        name="comment"
                        id="comment"
                    ></textarea>
                    <InputError :message="form.errors.comment" />
                </div>
            </form>

            <SheetFooter>
                <Button @click="submit" :disabled="form.processing">Opslaan</Button>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>
