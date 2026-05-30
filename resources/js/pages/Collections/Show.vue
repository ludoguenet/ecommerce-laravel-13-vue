<script setup lang="ts">
import ProductCard from '@/components/ProductCard.vue';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import Label from '@/components/ui/label/Label.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Slider } from '@/components/ui/slider';
import { Product } from '@/types/products';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { show } from '@/actions/App/Http/Controllers/CollectionController';
import { InfiniteScroll } from '@inertiajs/vue3';

const props = defineProps<{
    collection: {
        id: number;
        attribute_data: { name: { en: string }; [key: string]: unknown };
    };
    activeCollectionIds: number[];
    products: {
        data: Product[];
        links: { first: string; last: string; prev: string | null; next: string | null };
        meta: { current_page: number; last_page: number; total: number };
    };
    slug: string;
    sort: string;
    in_stock: boolean;
    min_price: number | null;
    max_price: number | null;
}>();

const refSort = ref(props.sort ?? 'default');
const refInStock = ref(props.in_stock ?? false);
const refPriceRange = ref<[number, number]>([
    props.min_price ?? 0,
    props.max_price ?? 1000,
]);

const applyFilters = () => {
    router.visit(show(props.slug).url, {
        data: {
            sort: refSort.value,
            in_stock: refInStock.value ? '1' : undefined,
            min_price: refPriceRange.value[0],
            max_price: refPriceRange.value[1],
        },
        preserveState: true,
        preserveScroll: true,
        reset: ['products'],
    });
};

const handleInStockChange = (val: boolean | 'indeterminate') => {
    refInStock.value = val === true;
    applyFilters();
};
</script>

<template>
    <Head :title="collection.attribute_data.name.en" />

    <div class="min-h-screen bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]">
        <h1 class="text-2xl font-semibold">
            {{ collection.attribute_data.name.en }}
        </h1>

        <div class="mt-4 flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-1.5">
                <Label>Trier</Label>
                <Select v-model="refSort" @update:modelValue="applyFilters">
                    <SelectTrigger class="w-56">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="default">Par défaut</SelectItem>
                        <SelectItem value="stock_asc"
                            >Stock croissant</SelectItem
                        >
                        <SelectItem value="stock_desc"
                            >Stock décroissant</SelectItem
                        >
                        <SelectItem value="price_asc"
                            >Prix croissant</SelectItem
                        >
                        <SelectItem value="price_desc"
                            >Prix décroissant</SelectItem
                        >
                    </SelectContent>
                </Select>
            </div>

            <div class="flex items-center gap-2 pb-1">
                <Checkbox
                    id="in_stock"
                    v-model="refInStock"
                    @update:modelValue="handleInStockChange"
                />
                <Label for="in_stock" class="cursor-pointer font-normal"
                    >En stock uniquement</Label
                >
            </div>
            <div class="flex flex-col gap-1.5">
                <Label class="font-normal">
                    Prix : {{ refPriceRange[0] }}€ – {{ refPriceRange[1] }}€
                </Label>
                <Slider
                    v-model="refPriceRange"
                    :min="0"
                    :max="1000"
                    :step="10"
                    class="w-56"
                    @update:model-value="applyFilters"
                />
            </div>
        </div>

        <div class="overflow-y-auto grow">
            <InfiniteScroll data="products" class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="product in products.data" :key="product.id" >
                    <ProductCard v-if="product" :product="product" />
                </div>
            </InfiniteScroll>
        </div>
        <!-- <div v-else class="mt-16 flex flex-col items-center gap-3 text-center text-neutral-400">
            <span class="text-4xl">☕</span>
            <p class="text-sm">Aucun produit ne correspond à vos filtres.</p>
        </div> -->
    </div>
</template>
