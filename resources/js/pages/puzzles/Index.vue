<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import type {FilterablePuzzle, Puzzle} from "@/types/wasgij";
import {Card, CardContent, CardDescription, CardHeader, CardTitle} from "@/components/ui/card";
import {AspectRatio} from "@/components/ui/aspect-ratio";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
    DropdownMenuCheckboxItem,
    DropdownMenuLabel, DropdownMenuSeparator,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
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
    puzzles: FilterablePuzzle[],
}>();

enum StatusFilterModes {
    FINISHED = '1',
    NOT_FINISHED = '2',
    PURCHASED_OWN = '3',
    PURCHASED_OWN_NOT_AT_ME = '4',
    PURCHASED_ANYONE = '5',
    PURCHASED_ANYONE_AT_ME = '6',
    NOT_PURCHASED_ANYONE = '7',
}

const filterModeOptions = {
    [StatusFilterModes.FINISHED]: "Opgelost",
    [StatusFilterModes.NOT_FINISHED]: "Niet opgelost",
    [StatusFilterModes.PURCHASED_OWN]: "Jouw gekochte puzzels",
    [StatusFilterModes.PURCHASED_OWN_NOT_AT_ME]: '- Die bij iemand anders liggen',
    [StatusFilterModes.PURCHASED_ANYONE]: 'Alle gekochte puzzels (van iedereen)',
    [StatusFilterModes.PURCHASED_ANYONE_AT_ME]: 'Alle puzzels die bij mij liggen',
    [StatusFilterModes.NOT_PURCHASED_ANYONE]: 'Niet gekochte puzzels',
}

// Filters
const queryInput = ref('');
const queryFilter = ref('');
const filteredYears = ref<number[]>([]);
const filteredTags = ref<string[]>([]);
const filterMode = ref<StatusFilterModes|''>('');
const assumeRelationsAreIdentical = ref(true);
const hideRereleases = ref(false);

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
    // Early bail if no filters active
    if (!queryFilter.value && !availableYears.value.length && !availableTags.value.length) {
        return props.puzzles;
    }

    // Build filter functions
    const filters: ( (puzzle: FilterablePuzzle) => boolean )[] = [];

    // Filter on rerelease (if supported)
    if (hideRereleases.value && filterModeSupportsHideRereleases.value) {
        filters.push(p => !p.is_rerelease)
    }

    // Filter on mode
    if (filterMode.value) {
        const value = filterMode.value as StatusFilterModes;
        const countRelations = assumeRelationsAreIdentical.value;
        switch(value) {
            case StatusFilterModes.FINISHED:
                filters.push(p => p.finished.self || ( countRelations && p.finished.related ))
                break;
            case StatusFilterModes.NOT_FINISHED:
                filters.push(p => !p.finished.self && !( countRelations && p.finished.related ))
                break;
            case StatusFilterModes.PURCHASED_OWN:
                filters.push(p => p.purchased.own)
                break;
            case StatusFilterModes.PURCHASED_OWN_NOT_AT_ME:
                filters.push(p => p.purchased.own && !p.purchased.at_me)
                break;
            case StatusFilterModes.PURCHASED_ANYONE:
                filters.push(p => p.purchased.own || p.purchased.anyone)
                break;
            case StatusFilterModes.PURCHASED_ANYONE_AT_ME:
                filters.push(p => p.purchased.at_me)
                break;
            case StatusFilterModes.NOT_PURCHASED_ANYONE:
                filters.push(p => !p.purchased.own && p.purchased.anyone)
                break;

        }
    }

    // Filter for the query
    if (queryFilter.value) {
        const query = queryFilter.value.toLowerCase();
        filters.push(p => p.puzzle_title.toLowerCase().includes(query)
            || p.artist?.toLowerCase().includes(query)
            || `${p.collection_tag?.toLowerCase() ?? ''} ${p.collection_number ?? ''}`.includes(query));
    }

    // Filter for active years
    if (filteredYears.value.length) {
        const years = filteredYears.value;
        filters.push(p => p.year !== null && years.includes(p.year));
    }

    // Filter for tags
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
});
const filterModeSupportsHideRereleases = computed(() => {
    switch(filterMode.value) {
        case '':
        case StatusFilterModes.FINISHED:
        case StatusFilterModes.NOT_FINISHED:
        case StatusFilterModes.NOT_PURCHASED_ANYONE:
            return true;

        default:
            return false;
    }
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
                                Filter op status {{ filterMode || hideRereleases ? '(!)' : '' }} <ChevronsUpDown />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>Instellingen</DropdownMenuLabel>
                            <DropdownMenuCheckboxItem :checked="assumeRelationsAreIdentical"
                                                      @select="e => { e.preventDefault(); (assumeRelationsAreIdentical = !assumeRelationsAreIdentical) }">
                                Zie heruitgaven als dezelfde puzzel
                            </DropdownMenuCheckboxItem>
                            <DropdownMenuCheckboxItem :checked="hideRereleases" :disabled="!filterModeSupportsHideRereleases"
                                                      @select="e => { e.preventDefault(); (hideRereleases = !hideRereleases) }">
                                Verberg heruitgaven {{ !filterModeSupportsHideRereleases ? ' (niet actief)' : '' }}
                            </DropdownMenuCheckboxItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuLabel>Filter op</DropdownMenuLabel>
                            <DropdownMenuRadioGroup v-model="filterMode">
                                <DropdownMenuRadioItem value="" @select="e => e.preventDefault()">
                                    Filter uitschakelen
                                </DropdownMenuRadioItem>
                                <DropdownMenuRadioItem :value="option" v-for="(label, option) of filterModeOptions" :key="option" @select="e => e.preventDefault()">
                                    {{ label }}
                                </DropdownMenuRadioItem>
                            </DropdownMenuRadioGroup>
                        </DropdownMenuContent>
                    </DropdownMenu>

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
                <Link :href="route('puzzles.show', [ puzzle.id ] )" v-for="puzzle of filteredPuzzles" :key="puzzle.id" as="a">
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
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
