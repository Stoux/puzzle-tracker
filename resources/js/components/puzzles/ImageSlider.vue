<script setup lang="ts">

import {
    Carousel,
    CarouselApi,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious
} from "@/components/ui/carousel";
import {ref} from "vue";
import {PuzzleDetails} from "@/types/wasgij";
import {AspectRatio} from "@/components/ui/aspect-ratio";
import {watchOnce} from "@vueuse/core";
import {Dialog, DialogContent, DialogTrigger} from "@/components/ui/dialog";

defineProps<{
    puzzle: PuzzleDetails,
}>();

const mainApi = ref<CarouselApi>()
const thumbnailApi = ref<CarouselApi>()
const selectedIndex = ref(0)

function onSelect() {
    if (!mainApi.value || !thumbnailApi.value) {
        return
    }

    selectedIndex.value = mainApi.value.selectedScrollSnap()
    thumbnailApi.value.scrollTo(selectedIndex.value)
}

function onThumbnailClicked(index: number) {
    if (!mainApi.value || !thumbnailApi.value) {
        return;
    }

    mainApi.value.scrollTo(index)
}

watchOnce(mainApi, (mainApi) => {
    if (!mainApi) {
        return
    }

    console.log('Selected index'!)

    onSelect()
    mainApi.on('select', onSelect)
    mainApi.on('reInit', onSelect)
})

</script>

<template>
    <div class="w-full p-16 flex flex-col">
        <!-- Main image carousel -->
        <Carousel class="relative w-full" @init-api="payload => { mainApi = payload }">
            <CarouselContent>
                <CarouselItem v-for="(image, index) of puzzle.images" :key="index">
                    <Dialog>
                        <DialogTrigger>
                            <img :src="image.full" alt="" class="rounded-md object-cover w-full h-full">
                        </DialogTrigger>
                        <DialogContent class="w-full h-full max-w-full">
                            <div class="flex justify-center">
                                <img :src="image.full" alt="" class="rounded-md object-cover">
                            </div>
                        </DialogContent>
                    </Dialog>
                </CarouselItem>
            </CarouselContent>
            <CarouselPrevious />
            <CarouselNext />
        </Carousel>

        <!-- Thumbnail carousel -->
        <Carousel class="relative w-full max-w-ws" @init-api="payload => { thumbnailApi = payload }">
            <CarouselContent class="flex gap-1 ml-0">
                <CarouselItem v-for="(image, index) of puzzle.images" :key="index"
                              class="pl-0 basis-1/4 cursor-pointer" @click="onThumbnailClicked(index)">
                    <AspectRatio :ratio="1" class="p-1" :class="{ 'opacity-50': index !== selectedIndex }">
                        <img :src="image.preview" alt="" class="rounded-sm object-cover w-full h-full">
                    </AspectRatio>
                </CarouselItem>
            </CarouselContent>
        </Carousel>
    </div>
</template>

<style scoped>

</style>
