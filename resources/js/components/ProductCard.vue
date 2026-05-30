<template>
    <div
        class="group overflow-hidden rounded-2xl border border-neutral-200 bg-white transition hover:-translate-y-1 hover:shadow-lg"
    >
        <div class="aspect-[4/3] overflow-hidden bg-neutral-100">
            <img
                v-if="product.small_image_url"
                :src="product.small_image_url"
                :alt="product.attribute_data.name?.en"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-gradient-to-br from-amber-50 to-stone-100"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 80 80"
                    class="h-20 w-20 opacity-40 transition duration-300 group-hover:scale-110 group-hover:opacity-60"
                    fill="none"
                >
                    <!-- Cup body -->
                    <path
                        d="M18 34h34l-5 24H23L18 34Z"
                        fill="#92400e"
                        opacity="0.7"
                    />
                    <!-- Cup rim -->
                    <rect
                        x="16"
                        y="30"
                        width="38"
                        height="6"
                        rx="3"
                        fill="#78350f"
                        opacity="0.8"
                    />
                    <!-- Handle -->
                    <path
                        d="M52 40 Q64 40 64 50 Q64 60 52 60"
                        stroke="#78350f"
                        stroke-width="4"
                        stroke-linecap="round"
                        fill="none"
                        opacity="0.75"
                    />
                    <!-- Steam lines -->
                    <path
                        d="M30 24 Q32 18 30 12"
                        stroke="#a16207"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        fill="none"
                        opacity="0.5"
                    />
                    <path
                        d="M40 22 Q42 16 40 10"
                        stroke="#a16207"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        fill="none"
                        opacity="0.5"
                    />
                    <path
                        d="M50 24 Q52 18 50 12"
                        stroke="#a16207"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        fill="none"
                        opacity="0.5"
                    />
                    <!-- Saucer -->
                    <ellipse
                        cx="35"
                        cy="59"
                        rx="22"
                        ry="4"
                        fill="#92400e"
                        opacity="0.3"
                    />
                </svg>
            </div>
        </div>

        <div class="p-5">
            <div class="mb-4 flex flex-wrap gap-1.5">
                <span
                    class="inline-flex rounded-full bg-neutral-100 px-2.5 py-1 text-xs font-medium text-neutral-600"
                >
                    {{ product.status }}
                </span>

                <span
                    v-for="tag in product.tags"
                    :key="tag.id"
                    class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700"
                >
                    {{ tag.value }}
                </span>
            </div>

            <div class="space-y-2">
                <h2
                    class="line-clamp-1 text-lg font-semibold tracking-tight text-neutral-900"
                >
                    {{ product.attribute_data.name?.en }}
                </h2>

                <div
                    class="line-clamp-3 text-sm leading-relaxed text-neutral-600"
                    v-html="product.attribute_data.description?.en"
                />
            </div>

            <div
                class="mt-6 flex items-center justify-between border-t border-neutral-100 pt-4"
            >
                <span class="text-xs text-neutral-400">
                 {{ computedPrice()}}
                </span>

                <button
                    class="rounded-xl bg-neutral-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-700"
                >
                    Voir le produit
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Product } from '@/types/products';

const props = defineProps<{
    product: Product;
}>();

const computedPrice = () => {
    return Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: props.product.variants[0].prices[0].currency.code,
    }).format(props.product.variants[0].prices[0].price.value / 100);
};
</script>
