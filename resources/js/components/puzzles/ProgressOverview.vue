<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { PuzzleDetails } from '@/types/wasgij';
import { Button } from '@/components/ui/button';
import { CirclePlus, Eye, Edit } from 'lucide-vue-next';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import {getStatusLabelFor, PuzzleProgressionStatus} from '@/lib/data';
import {usePage} from "@inertiajs/vue3";
import {SharedData, User} from "@/types";
import ProgressForm from "@/components/puzzles/ProgressForm.vue";

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const props = defineProps<{
    puzzle: PuzzleDetails;
}>();

</script>

<template>
    <div class="flex justify-between align-center">
        <h2 class="text-xl" id="status-overview">Afgerond door</h2>
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
                    <Tooltip v-if="progression.comments || progression.images.length">
                        <TooltipTrigger as-child>
                            <Button variant="outline" size="icon" class="mr-2 h-9 w-9">
                                <Eye class="h-5 w-5" />
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent>
                            <p>Bekijk {{ progression.user.name }}'s
                                {{ progression.comments ? 'opmerking' : '' }}{{ progression.comments && progression.images.length ? ' en ' : '' }}{{ progression.images.length ? 'foto\'s' : '' }}.</p>
                            <p class="text-destructive">Pas op: Verklapt mogelijk de oplossing!</p>
                        </TooltipContent>
                    </Tooltip>

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

                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>

<style scoped></style>
