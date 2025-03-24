<script setup lang="ts">

import {Dialog, DialogContent, DialogTrigger} from "@/components/ui/dialog";
import {Button} from "@/components/ui/button";

defineProps<{
    hint: number|string,
    image: string,
}>();

</script>

<template>

    <Dialog>
        <DialogTrigger>
            <Button variant="destructive">
                Bekijk hint {{ hint }}
            </Button>
        </DialogTrigger>
        <DialogContent class="w-full h-full max-w-full">
            <div class="flex justify-center">
                <img loading="lazy" :src="image" alt="" class="rounded-md object-cover">
            </div>
        </DialogContent>
    </Dialog>

</template>

