<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CircleCheckBig, Mail, MapPin, Truck } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';

type OrderLine = {
    description: string;
    quantity?: number;
    total: string;
};

type Address = {
    name: string;
    lineOne: string | null;
    lineTwo: string | null;
    city: string | null;
    postcode: string | null;
    country: string | null;
};

defineProps<{
    order: {
        reference: string | null;
        placedAt: string | null;
        contactEmail: string | null;
        lines: OrderLine[];
        shippingLines: OrderLine[];
        subTotal: string;
        discountTotal: string | null;
        shippingTotal: string | null;
        taxTotal: string;
        total: string;
        billingAddress: Address | null;
        shippingAddress: Address | null;
    };
}>();
</script>

<template>
    <Head title="Commande confirmée" />

    <div class="mx-auto max-w-2xl space-y-8 px-4 py-8 md:py-12">
        <div class="flex flex-col items-center space-y-4 text-center">
            <div
                class="flex size-16 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-950"
            >
                <CircleCheckBig
                    class="size-8 text-emerald-600 dark:text-emerald-400"
                />
            </div>
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight">
                    Commande confirmée
                </h1>
                <p class="text-muted-foreground">
                    Merci pour votre commande, elle est en cours de
                    préparation.
                </p>
            </div>
            <p
                v-if="order.contactEmail"
                class="flex items-center gap-1.5 text-sm text-muted-foreground"
            >
                <Mail class="size-4" />
                Un email de confirmation a été envoyé à
                <span class="font-medium text-foreground">{{
                    order.contactEmail
                }}</span>
            </p>
        </div>

        <Card>
            <CardHeader class="flex items-center justify-between">
                <div>
                    <CardTitle class="text-base"
                        >Commande {{ order.reference }}</CardTitle
                    >
                    <p
                        v-if="order.placedAt"
                        class="text-sm text-muted-foreground"
                    >
                        Passée le {{ order.placedAt }}
                    </p>
                </div>
                <Badge
                    variant="secondary"
                    class="bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400"
                >
                    Payée
                </Badge>
            </CardHeader>
            <CardContent class="space-y-6">
                <div class="space-y-3">
                    <div
                        v-for="(line, index) in order.lines"
                        :key="index"
                        class="flex items-start justify-between gap-2 text-sm"
                    >
                        <div>
                            <p class="font-medium">{{ line.description }}</p>
                            <p
                                v-if="line.quantity"
                                class="text-muted-foreground"
                            >
                                Quantité : {{ line.quantity }}
                            </p>
                        </div>
                        <span class="font-medium whitespace-nowrap">{{
                            line.total
                        }}</span>
                    </div>
                </div>

                <div
                    v-if="order.shippingLines.length"
                    class="space-y-3 border-t pt-4"
                >
                    <div
                        v-for="(line, index) in order.shippingLines"
                        :key="index"
                        class="flex items-start justify-between gap-2 text-sm"
                    >
                        <p class="font-medium">{{ line.description }}</p>
                        <span class="font-medium whitespace-nowrap">{{
                            line.total
                        }}</span>
                    </div>
                </div>

                <Separator />

                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between text-muted-foreground">
                        <dt>Sous-total</dt>
                        <dd class="font-medium text-foreground">
                            {{ order.subTotal }}
                        </dd>
                    </div>
                    <div
                        v-if="order.discountTotal"
                        class="flex justify-between text-muted-foreground"
                    >
                        <dt>Remise</dt>
                        <dd class="font-medium text-foreground">
                            -{{ order.discountTotal }}
                        </dd>
                    </div>
                    <div
                        v-if="order.shippingTotal"
                        class="flex justify-between text-muted-foreground"
                    >
                        <dt>Livraison</dt>
                        <dd class="font-medium text-foreground">
                            {{ order.shippingTotal }}
                        </dd>
                    </div>
                    <div class="flex justify-between text-muted-foreground">
                        <dt>TVA</dt>
                        <dd class="font-medium text-foreground">
                            {{ order.taxTotal }}
                        </dd>
                    </div>
                </dl>

                <Separator />

                <div class="flex justify-between text-base font-semibold">
                    <span>Total</span>
                    <span>{{ order.total }}</span>
                </div>

                <div
                    v-if="order.billingAddress || order.shippingAddress"
                    class="grid gap-6 border-t pt-6 sm:grid-cols-2"
                >
                    <div v-if="order.billingAddress" class="space-y-1.5">
                        <p
                            class="flex items-center gap-1.5 text-sm font-medium"
                        >
                            <MapPin class="size-4 text-muted-foreground" />
                            Adresse de facturation
                        </p>
                        <div class="text-sm text-muted-foreground">
                            <p>{{ order.billingAddress.name }}</p>
                            <p>{{ order.billingAddress.lineOne }}</p>
                            <p v-if="order.billingAddress.lineTwo">
                                {{ order.billingAddress.lineTwo }}
                            </p>
                            <p>
                                {{ order.billingAddress.city }},
                                {{ order.billingAddress.postcode }}
                            </p>
                            <p v-if="order.billingAddress.country">
                                {{ order.billingAddress.country }}
                            </p>
                        </div>
                    </div>
                    <div
                        v-if="
                            order.shippingAddress &&
                            order.shippingLines.length
                        "
                        class="space-y-1.5"
                    >
                        <p
                            class="flex items-center gap-1.5 text-sm font-medium"
                        >
                            <Truck class="size-4 text-muted-foreground" />
                            Adresse de livraison
                        </p>
                        <div class="text-sm text-muted-foreground">
                            <p>{{ order.shippingAddress.name }}</p>
                            <p>{{ order.shippingAddress.lineOne }}</p>
                            <p v-if="order.shippingAddress.lineTwo">
                                {{ order.shippingAddress.lineTwo }}
                            </p>
                            <p>
                                {{ order.shippingAddress.city }},
                                {{ order.shippingAddress.postcode }}
                            </p>
                            <p v-if="order.shippingAddress.country">
                                {{ order.shippingAddress.country }}
                            </p>
                        </div>
                    </div>
                </div>
            </CardContent>
            <CardFooter>
                <Button as-child size="lg" class="w-full">
                    <Link href="/">Retour à l'accueil</Link>
                </Button>
            </CardFooter>
        </Card>
    </div>
</template>
