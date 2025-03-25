<script setup lang="ts">
import {PurchasedPuzzle, Puzzle, type PuzzleUser} from '@/types/wasgij';
import { useForm } from '@inertiajs/vue3';
import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { onBeforeMount, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    puzzle: Puzzle;
    purchase?: PurchasedPuzzle;
    users: PuzzleUser[],
}>();

// Build the form data for a progression instance
const form = useForm({
    owner_id: null as number|null,
    currently_at_id: null as number|null,
    purchased_on: '',
});

// Keep track of the open state (so we can manually close it)
const isOpen = ref(false);

function handleNewPurchase() {
    if (props.purchase) {
        form.owner_id = props.purchase.owner?.id ?? null;
        form.currently_at_id = props.purchase.currently_at?.id ?? null;
        form.purchased_on = props.purchase.purchased_on ?? '';
    } else {
        form.owner_id = null;
        form.currently_at_id = null;
        form.purchased_on = '';
    }
}

watch(
    () => props.purchase,
    () => handleNewPurchase(),
);
onBeforeMount(() => handleNewPurchase());

function submit() {
    if (form.processing) {
        return;
    }

    const method = props.purchase ? 'put' : 'post';
    const url = props.purchase
        ? route('puzzles.purchase.edit', [props.puzzle.id, props.purchase.id])
        : route('puzzles.purchase.new', [props.puzzle.id]);

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
                <SheetTitle>{{ purchase ? 'Aanpassen' : 'Nieuw toevoegen' }}</SheetTitle>
                <SheetDescription>Wie heeft deze puzzel gekocht?</SheetDescription>
            </SheetHeader>

            <form @submit.prevent="submit" class="my-6 flex flex-col gap-6">
                <div class="grid gap-2">
                    <Label htmlFor="owner_id">Van wie?</Label>
                    <select
                        name="owner_id"
                        id="owner_id"
                        class="mt-1 block h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                        :disabled="form.processing"
                        v-model="form.owner_id"
                    >
                        <option v-for="user of users" :value="user.id" :key="user.id">
                            {{ user.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.owner_id" />
                </div>

                <div class="grid gap-2">
                    <Label htmlFor="currently_at_id">Ligt bij wie?</Label>
                    <select
                        name="currently_at_id"
                        id="currently_at_id"
                        class="mt-1 block h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-base ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                        :disabled="form.processing"
                        v-model="form.currently_at_id"
                    >
                        <option :value="''">Onbekend</option>
                        <option v-for="user of users" :value="user.id" :key="user.id">
                            {{ user.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.currently_at_id" />
                </div>

                <div class="grid gap-2">
                    <Label htmlFor="purchased_on">Gekocht op</Label>
                    <Input id="purchased_on" type="date" class="mt-1 block w-full" v-model="form.purchased_on" :disabled="form.processing" />
                    <InputError :message="form.errors.purchased_on" />
                </div>
            </form>

            <SheetFooter>
                <Button @click="submit" :disabled="form.processing">Opslaan</Button>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>
