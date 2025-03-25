<script setup lang="ts">
import { type BreadcrumbItem } from '@/types';
import {computed, ref} from "vue";
import {Head, router} from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger
} from "@/components/ui/dropdown-menu";
import {Button} from "@/components/ui/button";
import {Input} from "@/components/ui/input";
import {ChevronsUpDown, CirclePlus, Edit, Trash} from "lucide-vue-next";
import {Table, TableBody, TableCell, TableHead, TableHeader, TableRow} from "@/components/ui/table";
import {BarePuzzleRelation, PurchasedPuzzle} from "@/types/wasgij";
import {getAllTypeOptions, getTypeLabelFor} from "@/lib/data";
import {Tooltip, TooltipContent, TooltipTrigger} from "@/components/ui/tooltip";
import RelationForm from "@/components/puzzles/RelationForm.vue";
import {
    Dialog,
    DialogClose,
    DialogContent, DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger
} from "@/components/ui/dialog";

const props = defineProps<{
    all_puzzles: { id: number, title: string }[],
    relations: BarePuzzleRelation[],
}>();

const typeOptions = getAllTypeOptions();

const activeTypeFilters = ref<string[]>([]);
const queryInput = ref('');

// Computed
const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Puzzel Relaties',
        href: '/relations',
    },
]);

const filteredRelations = computed(() => {
    let result = props.relations;

    if (queryInput.value) {
        const input = queryInput.value.toLowerCase();
        result = result.filter(relation => {
            if (title(relation.puzzle_id).toLowerCase().includes(input)) {
                return true;
            }

            if (title(relation.relates_to_id).toLowerCase().includes(input)) {
                return true;
            }

            return false;
        })
    }

    if (activeTypeFilters.value.length) {
        result = result.filter(relation => activeTypeFilters.value.includes(relation.type));
    }

    return result;
});

const puzzles = computed<{[id: number]: string}>(() => {
    const map: {[id: number]: string} = {};
    props.all_puzzles.forEach((element) => {
        map[element.id] = element.title;
    });
    return map;
})



function puzzleRoute(puzzle: number) {
  return route('puzzles.show', [ puzzle ]);
}

function title(puzzle: number): string {
  return puzzles.value[puzzle];
}

function deleteRelation(relation: BarePuzzleRelation) {
    router.delete(route('relations.delete', [relation.id]), {
        preserveScroll: true,
    });
}

function toggleTypeFilter(event: Event, type: string) {
    event.preventDefault(); // Prevent closing
    const types = [ ...activeTypeFilters.value ];
    const indexOf = types.indexOf(type);
    if (indexOf !== -1) {
        types.splice(indexOf, 1);
    } else {
        types.push(type);
    }
    activeTypeFilters.value = types;
}

</script>

<template>
    <Head title="Puzzel Relaties" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex w-full flex-col gap-4 rounded-xl p-4">
            <div class="auto-rows-min gap-4 lg:grid-cols-2">
                <div class="flex w-full items-stretch gap-4 py-4">
                    <Input
                        class="max-w-sm"
                        placeholder="Filter..."
                        v-model="queryInput"
                    />
                    <div class="grow"></div>
                    <div class="flex h-full gap-2">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="outline" class="ml-auto">
                                    Type <span v-if="activeTypeFilters.length">({{ activeTypeFilters.length }})</span>  <ChevronsUpDown />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuCheckboxItem
                                    v-for="(label, type) of typeOptions" :key="type"
                                    :checked="activeTypeFilters.includes(type)"
                                    @select="e => toggleTypeFilter(e, type)">
                                    {{ label }}
                                </DropdownMenuCheckboxItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                        <RelationForm :puzzles="all_puzzles">
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button variant="outline" size="icon" class="mr-2 h-9 w-9 text-green-500">
                                        <CirclePlus class="h-5 w-5" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>Voeg een nieuwe relatie tussen twee puzzels toe!</p>
                                </TooltipContent>
                            </Tooltip>
                        </RelationForm>
                    </div>
                </div>
            </div>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Puzzel</TableHead>
                    <TableHead>Gerelateerd aan</TableHead>
                    <TableHead>Type</TableHead>
                    <TableHead>Opmerking</TableHead>
                    <TableHead>Acties</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="relation of filteredRelations" :key="relation.id">
                    <TableCell><a class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:!decoration-current dark:decoration-neutral-500"
                                  :href="puzzleRoute(relation.puzzle_id)" target="_blank">{{ title(relation.puzzle_id) }}</a>
                    </TableCell>
                    <TableCell><a class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:!decoration-current dark:decoration-neutral-500"
                                  :href="puzzleRoute(relation.relates_to_id)" target="_blank">{{ title(relation.relates_to_id) }}</a>
                    </TableCell>
                    <TableCell>{{ getTypeLabelFor(relation.type) }}</TableCell>
                    <TableCell>{{ relation.comment ?? '' }}</TableCell>
                    <TableCell>
                        <RelationForm :puzzles="all_puzzles" :relation="relation">
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button variant="outline" size="icon" class="mr-2 h-9 w-9">
                                        <Edit class="h-5 w-5" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>Bewerken</p>
                                </TooltipContent>
                            </Tooltip>
                        </RelationForm>

                        <Dialog>
                            <DialogTrigger>
                                <Tooltip>
                                    <TooltipTrigger as-child>
                                        <Button variant="outline" size="icon" class="mr-2 h-9 w-9 border-red-500">
                                            <Trash class="h-5 w-5" />
                                        </Button>
                                    </TooltipTrigger>
                                    <TooltipContent>
                                        <p>Verwijderen</p>
                                    </TooltipContent>
                                </Tooltip>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Wil je deze verwijderen?</DialogTitle>
                                    <DialogDescription>Dit kan niet ongedaan gemaakt worden.</DialogDescription>
                                </DialogHeader>
                                <DialogFooter>
                                    <DialogClose>
                                        <Button variant="secondary">
                                            Annuleren
                                        </Button>
                                    </DialogClose>
                                    <DialogClose>
                                        <Button variant="destructive" @click="deleteRelation(relation)">
                                            Verwijderen
                                        </Button>
                                    </DialogClose>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>

                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>

    </AppLayout>

</template>
