<script setup lang="ts">
import { Product, ProductVariant } from '@/types/products';
import { Head } from '@inertiajs/vue3';
import { ChevronLeft } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    product: Product;
}>();

const activeImageIndex = ref(0);

const images = computed(() => props.product.medium_images_urls ?? []);

const description = computed(() => props.product.attribute_data?.description?.en ?? '');

const productName = computed(() => props.product.attribute_data?.name?.en ?? '');

const brandName = computed(() => {
    if (!props.product.brand) {
        return null;
    }
    return props.product.brand.attribute_data?.name?.en ?? props.product.brand.name ?? null;
});

const firstVariant = computed<ProductVariant | null>(() => props.product.variants?.[0] ?? null);

const formattedPrice = computed(() => {
    const price = firstVariant.value?.prices?.[0];
    if (!price) {
        return null;
    }
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: price.currency.code,
    }).format(price.price.value / 100);
});

const comparePrice = computed(() => {
    const price = firstVariant.value?.prices?.[0];
    if (!price || !price.compare_price.value) {
        return null;
    }
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: price.currency.code,
    }).format(price.compare_price.value / 100);
});

const goBack = () => window.history.back();

const stockStatus = computed(() => {
    const variant = firstVariant.value;
    if (!variant) {
        return null;
    }

    if (variant.purchasable === 'always') {
         return { label: 'En stock', color: 'text-green-700 bg-green-50' };
    }

    if (variant.purchasable === 'in_stock' && variant.stock > 0) {
        return { label: 'En stock', color: 'text-green-700 bg-green-50' };
    }
    if (variant.purchasable === 'backorder' || variant.backorder) {
        return { label: 'Sur commande', color: 'text-amber-700 bg-amber-50' };
    }
    return { label: 'Épuisé', color: 'text-red-700 bg-red-50' };
});
</script>

<template>
    <Head :title="productName" />

    <div class="min-h-screen bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a]">
        <div class="mx-auto max-w-6xl px-6 py-10">
            <button
                class="mb-8 flex items-center gap-1.5 text-sm text-neutral-500 transition hover:text-neutral-900"
                @click="goBack"
            >
                <ChevronLeft class="size-4" />
                Retour aux produits
            </button>

            <div class="grid gap-12 lg:grid-cols-2">
                <!-- Gallery -->
                <div class="flex flex-col gap-4">
                    <div class="aspect-square overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-100">
                        <img
                            v-if="images[activeImageIndex]"
                            :src="images[activeImageIndex]"
                            :alt="productName"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-gradient-to-br from-amber-50 to-stone-100"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" class="h-24 w-24 opacity-30" fill="none">
                                <path d="M18 34h34l-5 24H23L18 34Z" fill="#92400e" opacity="0.7" />
                                <rect x="16" y="30" width="38" height="6" rx="3" fill="#78350f" opacity="0.8" />
                                <path d="M52 40 Q64 40 64 50 Q64 60 52 60" stroke="#78350f" stroke-width="4" stroke-linecap="round" fill="none" opacity="0.75" />
                            </svg>
                        </div>
                    </div>

                    <div v-if="images.length > 1" class="flex gap-3">
                        <button
                            v-for="(url, index) in images"
                            :key="index"
                            class="aspect-square w-20 overflow-hidden rounded-xl border-2 transition"
                            :class="index === activeImageIndex ? 'border-neutral-900' : 'border-neutral-200 hover:border-neutral-400'"
                            @click="activeImageIndex = index"
                        >
                            <img :src="url" :alt="`${productName} ${index + 1}`" class="h-full w-full object-cover" />
                        </button>
                    </div>
                </div>

                <!-- Product info -->
                <div class="flex flex-col gap-6 py-2">
                    <div class="flex flex-col gap-2">
                        <span v-if="brandName" class="text-sm font-medium tracking-wide text-neutral-400 uppercase">
                            {{ brandName }}
                        </span>
                        <h1 class="text-3xl font-bold tracking-tight text-neutral-900">
                            {{ productName }}
                        </h1>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-2xl font-semibold text-neutral-900">
                            {{ formattedPrice ?? '—' }}
                        </span>
                        <span v-if="comparePrice" class="text-base text-neutral-400 line-through">
                            {{ comparePrice }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <span
                        v-if="stockStatus"
                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                        :class="stockStatus.color"
                        >
                        {{ stockStatus.label }}
                    </span>
                        <span v-if="firstVariant?.sku" class="text-xs text-neutral-400">
                            SKU&nbsp;: {{ firstVariant.sku }}
                        </span>
                        <span v-if="firstVariant?.stock" class="text-xs text-neutral-400">
                            {{ firstVariant.stock }} en stock
                        </span>
                    </div>

                    <div
                        v-if="description"
                        class="prose prose-sm prose-neutral max-w-none border-t border-neutral-100 pt-6 text-neutral-600"
                        v-html="description"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
