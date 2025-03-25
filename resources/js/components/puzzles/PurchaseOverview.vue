<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import {
    DetailedPuzzleRelation,
    PurchasedPuzzle,
    PuzzleDetails,
    PuzzleProgression,
    type PuzzleUser
} from '@/types/wasgij';
import { Button } from '@/components/ui/button';
import { CirclePlus, Eye, Edit, Trash } from 'lucide-vue-next';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import {router} from "@inertiajs/vue3";
import {
    Dialog, DialogClose,
    DialogContent,
    DialogDescription, DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger
} from "@/components/ui/dialog";
import PurchaseForm from "@/components/puzzles/PurchaseForm.vue";
import {computed} from "vue";
import {getStatusLabelFor, getTypeLabelFor, PuzzleProgressionStatus, PuzzleRelationType} from "@/lib/data";
import TextLink from "@/components/TextLink.vue";

const props = defineProps<{
    puzzle: PuzzleDetails;
    users: PuzzleUser[],
}>();

const relatedPurchases = computed(() => {
    const result: { purchase: PurchasedPuzzle, relation: DetailedPuzzleRelation }[] = [];
    props.puzzle.relations.filter((r) => r.type !== PuzzleRelationType.RETRO && r.purchases.length).forEach(relation => {
        relation.purchases.forEach((purchase) => {
            result.push({
                purchase,
                relation,
            })
        })
    });
    return result;
});

function deletePurchase(progression: PurchasedPuzzle) {
    router.delete(route('puzzles.purchase.delete', [props.puzzle.id, progression.id]), {
        preserveScroll: true,
    });
}

</script>

<template>
    <div class="flex justify-between align-center">
        <h2 class="text-xl" id="purchase-overview">Gekocht door</h2>
        <PurchaseForm :puzzle="puzzle" :users="users">
            <Tooltip>
                <TooltipTrigger as-child>
                    <Button variant="outline" size="icon" class="mr-2 h-9 w-9 text-green-500">
                        <CirclePlus class="h-5 w-5" />
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>Ik heb deze puzzel gekocht!</p>
                </TooltipContent>
            </Tooltip>
        </PurchaseForm>
    </div>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead>Van</TableHead>
                <TableHead>Ligt bij</TableHead>
                <TableHead>Gekocht op</TableHead>
                <TableHead>Acties</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-if="!puzzle.purchases.length">
                <TableCell colspan="4"> Nog niemand heeft deze puzzel... </TableCell>
            </TableRow>
            <TableRow v-for="purchase of puzzle.purchases" :key="purchase.id">
                <TableCell>{{ purchase.owner.name }}</TableCell>
                <TableCell>{{ purchase.currently_at?.name ?? 'Onbekend?' }}</TableCell>
                <TableCell>
                    {{ purchase.purchased_on_formatted ?? '' }}
                </TableCell>
                <TableCell>

                    <PurchaseForm :puzzle="puzzle" :purchase="purchase" :users="users">
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
                    </PurchaseForm>

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
                                <DialogDescription>Is deze weggegooid ofzo? Weet je zeker dat je deze wil verwijderen? Dit kan niet ongedaan gemaakt worden.</DialogDescription>
                            </DialogHeader>
                            <DialogFooter>
                                <DialogClose>
                                    <Button variant="secondary">
                                        Annuleren
                                    </Button>
                                </DialogClose>
                                <DialogClose>
                                    <Button variant="destructive" @click="deletePurchase(purchase)">
                                        Verwijderen
                                    </Button>
                                </DialogClose>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>

                </TableCell>
            </TableRow>
        </TableBody>
        <TableBody v-if="relatedPurchases.length">
            <TableRow>
                <TableHead colspan="3">Andere varianten van deze puzzel</TableHead>
                <TableHead>Variant</TableHead>
            </TableRow>
            <TableRow v-for="related of relatedPurchases" :key="related.purchase.id">
                <TableCell>{{ related.purchase.owner.name }}</TableCell>
                <TableCell>{{ related.purchase.currently_at?.name ?? 'Onbekend?' }}</TableCell>
                <TableCell>
                    {{ related.purchase.purchased_on_formatted ?? '' }}
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
