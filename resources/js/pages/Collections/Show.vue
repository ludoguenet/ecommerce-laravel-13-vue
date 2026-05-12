<script setup lang="ts">
import ProductCard from '@/components/ProductCard.vue';
import { show } from '@/actions/App/Http/Controllers/CollectionController';
import { Product } from '@/types/products';
import { Head, Form } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    collection: {
        id: number;
        attribute_data: { name: { en: string }; [key: string]: unknown };
    };
    activeCollectionIds: number[];
    products: Product[];
    slug: string;
    filters: { sort?: string; in_stock?: boolean };
}>();

const selectedSort = ref(props.filters.sort ?? '');
const inStock = ref(props.filters.in_stock ?? false);
</script>

<template>
    <Head :title="collection.attribute_data.name.en" />

    <div class="min-h-screen bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]">
        <h1 class="text-2xl font-semibold">{{ collection.attribute_data.name.en }}</h1>

        <Form v-bind="show.form(slug)" #default="{ submit }" class="mt-4 flex items-center gap-4">
            <select
                name="sort"
                v-model="selectedSort"
                @change="() => submit()"
                class="rounded-xl border border-neutral-200 bg-white px-3 py-2 text-sm text-neutral-700 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-neutral-300"
            >
                <option value="">Default</option>
                <option value="stock_desc">Stock: High to Low</option>
                <option value="stock_asc">Stock: Low to High</option>
            </select>

            <label class="flex cursor-pointer items-center gap-2 text-sm text-neutral-600">
                <input
                    type="checkbox"
                    name="in_stock"
                    value="1"
                    v-model="inStock"
                    @change="() => submit()"
                    class="h-4 w-4 rounded border-neutral-300 accent-neutral-900"
                />
                In stock only
            </label>
        </Form>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <ProductCard v-for="product in products" :key="product.id" :product="product" />
        </div>
    </div>
</template>
