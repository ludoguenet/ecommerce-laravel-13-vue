<template>
    <div
        class="group rounded-2xl border border-neutral-200 bg-white p-5 transition hover:-translate-y-1 hover:shadow-lg"
    >
        <div class="mb-4">
        <span
            class="inline-flex rounded-full bg-neutral-100 px-2.5 py-1 text-xs font-medium text-neutral-600"
        >
            {{ product.status }}
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
            #{{ product.id }}
        </span>

        <button
            class="rounded-xl bg-neutral-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-neutral-700"
        >
            Voir le produit
        </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Product } from '@/types/products';

defineProps<{
  product: Product;
}>();
</script>
