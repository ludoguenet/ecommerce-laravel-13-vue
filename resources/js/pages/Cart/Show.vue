<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { removeLine, updateLine } from '@/actions/App/Http/Controllers/CartController';
import { Minus, Plus, Trash2 } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const cartItemsCount = computed(() => page.props.cartItemsCount as number);

const subTotal = computed(() => page.props.subTotal);
// const shippingSubTotal = computed(() => page.props.shippingSubTotal);
const taxTotal = computed(() => page.props.taxTotal);
const total = computed(() => page.props.total);

const cartLines = computed(() => page.props.cartLines as Array<{
    id: number;
    quantity: number;
    name: string;
    thumbnail: string;
    price: number | null;
    currency: string | null;
    slug: string | null;
}>);

const formatPrice = (value: number, currency: string) =>
    new Intl.NumberFormat('fr-FR', { style: 'currency', currency }).format(value / 100);

const decrementQty = (lineId: number, currentQty: number) => {
    router.visit(updateLine(), {
        data: { line_id: lineId, quantity: currentQty - 1 },
        preserveScroll: true,
    });
};

const incrementQty = (lineId: number, currentQty: number) => {
    router.visit(updateLine(), {
        data: { line_id: lineId, quantity: currentQty + 1 },
        preserveScroll: true,
    });
};

const removeCartLine = (lineId: number) => {
    router.visit(removeLine({ line: lineId }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Mon panier" />

    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto max-w-4xl px-4 py-12">
            <h1 class="mb-8 text-2xl font-semibold text-gray-900">Mon panier</h1>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Cart lines -->
                <div class="lg:col-span-2">
                    <ul class="divide-y divide-gray-200 rounded-lg bg-white shadow-sm">
                        <li v-for="line in cartLines" class="flex gap-4 p-6">
                            <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md bg-gray-100">
                                <img
                                    :src=line.thumbnail.small_image_url
                                    alt="Produit"
                                    class="h-full w-full object-cover"
                                />
                            </div>

                            <div class="flex flex-1 flex-col gap-2">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="font-medium text-gray-900" v-html="line.attribute_data.description .en" />
                                        <p class="text-sm text-gray-500">{{ line.sku }}</p>
                                    </div>
                                    <p class="font-semibold text-gray-900">{{ formatPrice(line.price, line.currency) }}</p>
                                </div>

                                <div class="mt-auto flex items-center justify-between">
                                    <div class="flex items-center gap-2 rounded-md border border-gray-200">
                                        <button @click.prevent="decrementQty(line.id, line.quantity)" class="p-2 text-gray-500 hover:text-gray-900">
                                            <Minus class="h-3 w-3" />
                                        </button>
                                        <span class="w-6 text-center text-sm font-medium">{{ line.quantity }}</span>
                                        <button @click.prevent="incrementQty(line.id, line.quantity)" class="p-2 text-gray-500 hover:text-gray-900">
                                            <Plus class="h-3 w-3" />
                                        </button>
                                    </div>
                                    <button @click.prevent="removeCartLine(line.id)" class="text-gray-400 transition hover:text-red-500">
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Order summary -->
                <div class="lg:col-span-1">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-base font-semibold text-gray-900">Récapitulatif</h2>

                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <dt>Sous-total ({{ cartItemsCount }} articles)</dt>
                                <dd class="font-medium text-gray-900">{{ subTotal }}</dd>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <dt>TVA</dt>
                                <dd class="font-medium text-gray-900">{{ taxTotal }}</dd>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-3 text-base font-semibold text-gray-900">
                                <dt>Total</dt>
                                <dd>{{ total }}</dd>
                            </div>
                        </dl>

                        <button
                            class="mt-6 w-full rounded-md bg-gray-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-gray-700"
                        >
                            Passer la commande
                        </button>

                        <Link href="/" class="mt-3 block text-center text-sm text-gray-500 hover:text-gray-900">
                            Continuer mes achats
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
