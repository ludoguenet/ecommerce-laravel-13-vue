<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
    collection: {
        id: number;
        attribute_data: { name: { en: string }; [key: string]: unknown };
    };
    activeCollectionIds: number[];
}>();
</script>

<template>
    <Head :title="collection.attribute_data.name.en" />

    <div class="min-h-screen bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]">
        <h1 class="text-2xl font-semibold">{{ collection.attribute_data.name.en }}</h1>
    </div>
</template>
