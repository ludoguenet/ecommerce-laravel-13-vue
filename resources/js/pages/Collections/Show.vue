<script setup lang="ts">
import ProductCard from '@/components/ProductCard.vue';
import { Product } from '@/types/products';

const props = defineProps<{
    collection: {
        id: number;
        attribute_data: { name: { en: string }; [key: string]: unknown };
    };
    activeCollectionIds: number[];
    products: Product[];
}>();
</script>

<template>
    <Head :title="collection.attribute_data.name.en" />

    <div class="min-h-screen bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]">
        <h1 class="text-2xl font-semibold">{{ collection.attribute_data.name.en }}</h1>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <ProductCard v-for="product in products" :key="product.id" :product="product" />
        </div>
    </div>
</template>
