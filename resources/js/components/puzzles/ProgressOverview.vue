<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {DetailedPuzzleRelation, PuzzleDetails, PuzzleProgression} from '@/types/wasgij';
import { Button } from '@/components/ui/button';
import { CirclePlus, Edit, Eye, Trash } from 'lucide-vue-next';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import {getStatusLabelFor, getTypeLabelFor, PuzzleProgressionStatus, PuzzleRelationType} from '@/lib/data';
import {Link, router, usePage} from '@inertiajs/vue3';
import { SharedData, User } from '@/types';
import ProgressForm from '@/components/puzzles/ProgressForm.vue';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { computed } from 'vue';
import TextLink from "@/components/TextLink.vue";
import {ScrollArea} from "@/components/ui/scroll-area";

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const props = defineProps<{
    puzzle: PuzzleDetails;
}>();

const relatedProgressions = computed(() => {
    const result: { progression: PuzzleProgression, relation: DetailedPuzzleRelation }[] = [];
    props.puzzle.relations.filter((r) => r.type !== PuzzleRelationType.RETRO && r.progressions.length).forEach(relation => {
        relation.progressions.forEach((progression) => {
            result.push({
                progression,
                relation,
            })
        })
    });
    return result;
});

function deleteProgression(progression: PuzzleProgression) {
    router.delete(route('puzzles.progress.delete', [props.puzzle.id, progression.id]), {
        preserveScroll: true,
    });
}
</script>

<template>
    <div class="align-center flex justify-between">
        <h2 class="text-xl" id="status-overview">Het raadsel opgelost door</h2>
        <ProgressForm :puzzle="puzzle">
            <Tooltip>
                <TooltipTrigger as-child>
                    <Button variant="outline" size="icon" class="mr-2 h-9 w-9 text-green-500">
                        <CirclePlus class="h-5 w-5" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>Ik ben aan deze puzzel begonnen!</p>
                </TooltipContent>
            </Tooltip>
        </ProgressForm>
    </div>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead>Wie</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Wanneer</TableHead>
                <TableHead>Acties</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-if="!puzzle.progressions.length">
                <TableCell colspan="4"> Nog niemand is aan deze puzzel begonnen... </TableCell>
            </TableRow>
            <TableRow v-for="progression of puzzle.progressions" :key="progression.id">
                <TableCell>{{ progression.user.name }}</TableCell>
                <TableCell
                    :class="{
                        'text-red-600': progression.status === PuzzleProgressionStatus.ABORTED,
                        'text-green-600': progression.status === PuzzleProgressionStatus.FINISHED,
                    }"
                >
                    {{ getStatusLabelFor(progression.status) }}
                </TableCell>
                <TableCell>
                    {{ progression.when ?? '-' }}
                </TableCell>
                <TableCell>
                    <Dialog v-if="progression.comments || progression.images.length">
                        <DialogTrigger>
                            <Tooltip>
                                <TooltipTrigger as-child>
                                    <Button variant="outline" size="icon" class="mr-2 h-9 w-9">
                                        <Eye class="h-5 w-5" />
                                    </Button>
                                </TooltipTrigger>
                                <TooltipContent>
                                    <p>
                                        Bekijk {{ progression.user.name }}'s {{ progression.comments ? 'opmerking' : ''
                                        }}{{ progression.comments && progression.images.length ? ' en ' : ''
                                        }}{{ progression.images.length ? "foto's" : '' }}.
                                    </p>
                                    <p class="text-destructive">Pas op: Verklapt mogelijk de oplossing!</p>
                                </TooltipContent>
                            </Tooltip>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>{{ progression.user.name }}:</DialogTitle>
                            </DialogHeader>

                            <ScrollArea class="overflow-y-scroll max-h-[90vh]">
                                <div class="flex flex-col gap-4">
                                    <p v-if="progression.comments">
                                        {{ progression.comments }}
                                    </p>

                                    <img loading="lazy" class="w-full" :src="image.url" alt=""
                                         v-for="image of progression.images" :key="image.id" />
                                </div>

                            </ScrollArea>

                            <DialogFooter>
                                <DialogClose>
                                    <Button variant="secondary"> Sluiten </Button>
                                </DialogClose>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>

                    <ProgressForm :puzzle="puzzle" :progression="progression" v-if="progression.user.id === user.id">
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
                    </ProgressForm>

                    <Dialog v-if="progression.user.id === user.id">
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
                                <DialogDescription
                                    >Weet je zeker dat je je voortgang wil verwijderen? Dit kan niet ongedaan gemaakt worden.</DialogDescription
                                >
                            </DialogHeader>
                            <DialogFooter>
                                <DialogClose>
                                    <Button variant="secondary"> Annuleren </Button>
                                </DialogClose>
                                <DialogClose>
                                    <Button variant="destructive" @click="deleteProgression(progression)"> Verwijderen </Button>
                                </DialogClose>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </TableCell>
            </TableRow>
        </TableBody>
        <TableBody v-if="relatedProgressions.length">
            <TableRow>
                <TableHead colspan="3">Andere varianten van deze puzzel</TableHead>
                <TableHead>Variant</TableHead>
            </TableRow>
            <TableRow v-for="related of relatedProgressions" :key="related.progression.id">
                <TableCell>{{ related.progression.user.name }}</TableCell>
                <TableCell
                    :class="{
                        'text-red-600': related.progression.status === PuzzleProgressionStatus.ABORTED,
                        'text-green-600': related.progression.status === PuzzleProgressionStatus.FINISHED,
                    }"
                >
                    {{ getStatusLabelFor(related.progression.status) }}
                </TableCell>
                <TableCell>
                    {{ related.progression.when ?? '-' }}
                </TableCell>
                <TableCell>
                    <TextLink :href="route('puzzles.show', related.relation.relates_to.id)" :title="related.relation.relates_to.puzzle_title">
                        {{ getTypeLabelFor(related.relation.type ) }}
                    </TextLink>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>

<style scoped></style>
