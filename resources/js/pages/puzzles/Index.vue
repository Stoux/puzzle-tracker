<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import type {Puzzle} from "@/types/wasgij";
import {Card, CardContent, CardDescription, CardHeader, CardTitle} from "@/components/ui/card";
import {AspectRatio} from "@/components/ui/aspect-ratio";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
    DropdownMenuCheckboxItem,
    DropdownMenuLabel, DropdownMenuSeparator
} from "@/components/ui/dropdown-menu";
import {computed, ref, watch} from "vue";
import {ChevronsUpDown} from "lucide-vue-next";
import {Input} from "@/components/ui/input";
import {Button} from "@/components/ui/button";
import {ScrollArea} from "@/components/ui/scroll-area";

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Puzzels',
        href: '/puzzels',
    },
];

const props = defineProps<{
    puzzles: Puzzle[],
}>();

// Filters
const queryInput = ref('');
const queryFilter = ref('');
const filteredYears = ref<number[]>([]);
const filteredTags = ref<string[]>([]);

// Computed
const availableYears = computed(() => {
    const years: {[year: number]: number} = {};
    props.puzzles.forEach(puzzle => years[puzzle.year ?? 0] = puzzle.year ?? 0);
    return Object.values(years).sort((a,b) => b - a)
});
const availableTags = computed(() => {
    const tags : {[tag: string]: true} = {};
    props.puzzles.forEach(puzzle => {
        puzzle.tags.forEach(tag => {
            tags[tag] = true;
        });
    })
    return Object.keys(tags).sort((a,b) => a.localeCompare(b));
});
const filteredPuzzles = computed<Puzzle[]>(() => {
    console.log('recalc');
    // Early bail if no filters active
    if (!queryFilter.value && !availableYears.value.length && !availableTags.value.length) {
        return props.puzzles;
    }

    // Build filter functions
    const filters: ( (puzzle: Puzzle) => boolean )[] = [];
    if (queryFilter.value) {
        const query = queryFilter.value.toLowerCase();
        filters.push(p => p.puzzle_title.toLowerCase().includes(query)
            || p.artist?.toLowerCase().includes(query)
            || `${p.collection_tag?.toLowerCase() ?? ''} ${p.collection_number ?? ''}`.includes(query));
    }
    if (filteredYears.value.length) {
        const years = filteredYears.value;
        filters.push(p => p.year !== null && years.includes(p.year));
    }
    if (filteredTags.value.length) {
        const tags = filteredTags.value;
        filters.push(p => p.tags.some(tag => tags.includes(tag)));
    }

    // Filter the items
    return props.puzzles.filter(p => {
        for (const filter of filters) {
            if (!filter(p)) {
                return false;
            }
        }

        return true;
    })
})

// Watchers
// Debounce the query input
let queryDebounce : ReturnType<typeof setTimeout>|undefined = undefined;
watch(queryInput, (value) => {
    clearTimeout(queryDebounce);
    if (!value) {
        queryFilter.value = '';
        return;
    }

    queryDebounce = setTimeout(() => {
        queryFilter.value = value;
    }, 400);
});




// Methods

function toggleYear(event: Event, year: number) {
    event.preventDefault(); // Prevent closing
    const years = [ ...filteredYears.value ];
    const indexOf = years.indexOf(year);
    if (indexOf !== -1) {
        years.splice(indexOf, 1);
    } else {
        years.push(year);
    }
    filteredYears.value = years;
}

function toggleTag(event: Event, tag: string) {
    event.preventDefault();
    const tags = [ ...filteredTags.value ];
    const indexOf = tags.indexOf(tag);
    if (indexOf !== -1) {
        tags.splice(indexOf, 1);
    } else {
        tags.push(tag);
    }
    filteredTags.value = tags;
}

</script>

<template>
    <Head title="Puzzels" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
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
                                Jaar <span v-if="filteredYears.length">({{ filteredYears.length}})</span> <ChevronsUpDown />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>Jaar van uitgave</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <ScrollArea class="overflow-y-scroll max-h-72">
                                <DropdownMenuCheckboxItem
                                    v-for="year of availableYears" :key="year"
                                    :checked="filteredYears.includes(year)"
                                    @select="e => toggleYear(e, year)">
                                    {{ year }}
                                </DropdownMenuCheckboxItem>
                            </ScrollArea>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="ml-auto">
                                Collecties <span v-if="filteredTags.length">({{ filteredTags.length }})</span> <ChevronsUpDown />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>Collecties</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <ScrollArea class="overflow-y-scroll max-h-72">
                                <DropdownMenuCheckboxItem
                                    v-for="tag of availableTags" :key="tag"
                                    :checked="filteredTags.includes(tag)"
                                    @select="e => toggleTag(e, tag)">
                                    {{ tag }}
                                </DropdownMenuCheckboxItem>
                            </ScrollArea>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
            <div class="grid auto-rows-min gap-4 xl:grid-cols-4 lg:grid-cols-3 sm:grid-cols-2">
                <a :href="route('puzzles.show', [ puzzle.id ] )" v-for="puzzle of filteredPuzzles" :key="puzzle.id">
                    <Card class="cursor-pointer">
                        <CardHeader>
                            <CardTitle>{{ puzzle.puzzle_title }}</CardTitle>
                            <CardDescription>
                                {{ puzzle.collection_tag }} {{ puzzle.collection_number }}
                                | Jaar: {{ puzzle.year }}
                                | {{ puzzle.number_of_pieces_label}}
                                | {{ puzzle.artist }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent v-if="puzzle.thumbnail">
                            <AspectRatio :ratio="1">
                                <img :src="puzzle.thumbnail" :alt="puzzle.puzzle_title" class="rounded-md object-cover w-full h-full">
                            </AspectRatio>
                        </CardContent>
                    </Card>
                </a>
            </div>
        </div>
    </AppLayout>
</template>
