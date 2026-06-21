<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import CatalogMenu from '@/components/CatalogMenu.vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Link, usePage } from '@inertiajs/vue3';
import { ShoppingCart } from 'lucide-vue-next';
import { computed } from 'vue';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const cartItemsCount = computed(() => page.props.cartItemsCount as number);
const cartLines = computed(() => page.props.cartLines as Array<{
    id: number;
    quantity: number;
    name: string;
    price: number | null;
    currency: string | null;
    slug: string | null;
}>);

const formatPrice = (value: number, currency: string) =>
    new Intl.NumberFormat('fr-FR', { style: 'currency', currency }).format(value / 100);
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        <CatalogMenu :items="$page.props.catalogMenu" />

        <div class="ml-auto">
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <div class="relative">
                        <Button variant="ghost" size="icon" class="h-9 w-9">
                            <ShoppingCart class="size-5 opacity-80" />
                        </Button>
                        <span
                            v-if="cartItemsCount > 0"
                            class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-neutral-900 text-[10px] font-bold text-white dark:bg-white dark:text-neutral-900 pointer-events-none"
                        >
                            {{ cartItemsCount > 99 ? '99+' : cartItemsCount }}
                        </span>
                    </div>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-72">
                    <DropdownMenuLabel>Panier</DropdownMenuLabel>
                    <template v-if="cartLines.length > 0">
                        <DropdownMenuSeparator />
                        <div class="max-h-64 overflow-y-auto">
                            <div
                                v-for="line in cartLines"
                                :key="line.id"
                                class="flex items-center justify-between px-3 py-2 text-sm"
                            >
                                <div class="flex flex-col gap-0.5 min-w-0">
                                    <Link
                                        v-if="line.slug"
                                        :href="`/products/${line.slug}`"
                                        class="truncate font-medium hover:underline"
                                    >
                                        {{ line.name }}
                                    </Link>
                                    <span v-else class="truncate font-medium">{{ line.name }}</span>
                                    <span class="text-xs text-muted-foreground">Qté : {{ line.quantity }}</span>
                                </div>
                                <span v-if="line.price && line.currency" class="ml-3 shrink-0 text-xs font-medium">
                                    {{ formatPrice(line.price * line.quantity, line.currency) }}
                                </span>
                            </div>
                        </div>
                    </template>
                    <div v-else class="px-3 py-6 text-center text-sm text-muted-foreground">
                        Votre panier est vide
                    </div>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </header>
</template>
