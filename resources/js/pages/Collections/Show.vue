<script setup lang="ts">
import ProductCard from '@/components/ProductCard.vue';
import { Product } from '@/types/products';
import { Form, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { show } from '@/actions/App/Http/Controllers/CollectionController';

const props = defineProps<{
    collection: {
        id: number;
        attribute_data: { name: { en: string }; [key: string]: unknown };
    };
    activeCollectionIds: number[];
    products: Product[];
    slug: string;
    sort: string;
    in_stock: boolean;
}>();

const refSort = ref(props.sort ?? "");
const refInStock = ref(props.in_stock ?? false);
</script>

<template>
    <Head :title="collection.attribute_data.name.en" />

    <div class="min-h-screen bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]">
        <h1 class="text-2xl font-semibold">{{ collection.attribute_data.name.en }}</h1>

        <Form :action="show(slug)" #default="{ submit }">
            <label for="sort">Trier par stock</label>
            <select name="sort" id="sort" v-model="refSort" @change="() => submit()">
                <option value="">Par défaut</option>
                <option value="stock_asc">Du plus petit au plus grand</option>
                <option value="stock_desc">Du plus grand au plus petit</option>
            </select>

            <label for="in_stock">
                <input type="checkbox" name="in_stock" id="in_stock" value="1" v-model="refInStock" @change="() => submit()">
            </label>
        </Form>

        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <ProductCard v-for="product in products" :key="product.id" :product="product" />
        </div>
    </div>
</template>
