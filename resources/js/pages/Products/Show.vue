<script setup lang="ts">
import { Product, ProductOption, ProductOptionValue, ProductVariant } from '@/types/products';
import { store as addCartAction } from '@/actions/App/Http/Controllers/CartController';
import { Head, router } from '@inertiajs/vue3';
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

// --- Variant selection ---

const selectedValueIds = ref<Record<number, number>>({});

const initSelection = () => {
    const first = props.product.variants?.[0];
    if (!first?.values?.length) {
        return;
    }
    first.values.forEach((v) => {
        selectedValueIds.value[v.product_option_id] = v.id;
    });
};

initSelection();

const selectedVariant = computed<ProductVariant | null>(() => {
    const selectedIds = Object.values(selectedValueIds.value);
    if (selectedIds.length === 0) {
        return props.product.variants?.[0] ?? null;
    }
    return (
        (props.product.variants ?? []).find((variant) => {
            const variantValueIds = (variant.values ?? []).map((v) => v.id);
            return selectedIds.every((id) => variantValueIds.includes(id));
        }) ?? null
    );
});

const selectValue = (optionId: number, valueId: number) => {
    selectedValueIds.value = { ...selectedValueIds.value, [optionId]: valueId };
};

const isValueSelected = (optionId: number, valueId: number): boolean =>
    selectedValueIds.value[optionId] === valueId;

const isValueAvailable = (optionId: number, valueId: number): boolean => {
    const otherSelections = Object.entries(selectedValueIds.value)
        .filter(([oid]) => Number(oid) !== optionId)
        .map(([, vid]) => Number(vid));

    return (props.product.variants ?? []).some((variant) => {
        const ids = (variant.values ?? []).map((v) => v.id);
        return ids.includes(valueId) && otherSelections.every((id) => ids.includes(id));
    });
};

const optionName = (option: ProductOption): string =>
    option.name['en'] ?? Object.values(option.name)[0] ?? '';

const optionValueName = (value: ProductOptionValue): string =>
    value.name['en'] ?? Object.values(value.name)[0] ?? '';

const selectedOptionValueName = (option: ProductOption): string => {
    const selectedId = selectedValueIds.value[option.id];
    const value = option.values.find((v) => v.id === selectedId);
    return value ? optionValueName(value) : '';
};

const addToCart = () => {
    if (! selectedVariant.value) return;

    router.visit(addCartAction({ variant: selectedVariant.value.id }), {
        data: { quantity: 1 },
        preserveScroll: true,
    });
}

const hasOptions = computed(() => (props.product.product_options?.length ?? 0) > 0);

// --- Price / stock (driven by selectedVariant) ---

const formatPrice = (value: number, currencyCode: string): string =>
    new Intl.NumberFormat('fr-FR', { style: 'currency', currency: currencyCode }).format(value / 100);

const formattedPrice = computed(() => {
    const price = selectedVariant.value?.prices?.[0];
    return price ? formatPrice(price.price.value, price.currency.code) : null;
});

const comparePrice = computed(() => {
    const price = selectedVariant.value?.prices?.[0];
    if (!price?.compare_price.value) {
        return null;
    }
    return formatPrice(price.compare_price.value, price.currency.code);
});

const stockStatus = computed(() => {
    const variant = selectedVariant.value;
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

const goBack = () => window.history.back();
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

                    <!-- Option selectors -->
                    <div v-if="hasOptions" class="flex flex-col gap-5">
                        <div v-for="option in product.product_options" :key="option.id">
                            <div class="mb-2.5 flex items-baseline gap-2">
                                <span class="text-xs font-semibold tracking-wide text-neutral-500 uppercase">
                                    {{ optionName(option) }}
                                </span>
                                <span class="text-xs text-neutral-400">
                                    {{ selectedOptionValueName(option) }}
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="value in option.values"
                                    :key="value.id"
                                    class="rounded-lg border px-3 py-1.5 text-sm font-medium transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-neutral-900 focus-visible:ring-offset-1"
                                    :class="
                                        isValueSelected(option.id, value.id)
                                            ? 'border-neutral-900 bg-neutral-900 text-white'
                                            : isValueAvailable(option.id, value.id)
                                              ? 'border-neutral-200 bg-white text-neutral-700 hover:border-neutral-400 hover:bg-neutral-50'
                                              : 'cursor-not-allowed border-neutral-100 bg-neutral-50 text-neutral-300 line-through'
                                    "
                                    :disabled="!isValueAvailable(option.id, value.id)"
                                    @click="selectValue(option.id, value.id)"
                                >
                                    {{ optionValueName(value) }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <span
                            v-if="stockStatus"
                            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
                            :class="stockStatus.color"
                        >
                            {{ stockStatus.label }}
                        </span>
                        <span v-if="selectedVariant?.sku" class="text-xs text-neutral-400">
                            SKU&nbsp;: {{ selectedVariant.sku }}
                        </span>
                        <span v-if="selectedVariant?.stock" class="text-xs text-neutral-400">
                            {{ selectedVariant.stock }} en stock
                        </span>
                    </div>

                    <div v-if="product.tags?.length" class="flex flex-wrap gap-2">
                        <span
                            v-for="tag in product.tags"
                            :key="tag.id"
                            class="inline-flex items-center rounded-full bg-neutral-100 px-3 py-1 text-xs font-medium text-neutral-600"
                        >
                            {{ tag.value }}
                        </span>
                    </div>

                    <div
                        v-if="description"
                        class="prose prose-sm prose-neutral max-w-none border-t border-neutral-100 pt-6 text-neutral-600"
                        v-html="description"
                    />

                    <button @click.prevent="addToCart" class="bg-neutral-900 border-neutral-900 border rounded-lg text-white px-3 py-1.5 font-medium text-md hover:cursor-pointer transition-all hover:border-neutral-400 hover:bg-neutral-700">
                        Ajouter au panier
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
