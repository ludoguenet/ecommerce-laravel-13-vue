<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { loadStripe } from '@stripe/stripe-js';
import type { Stripe, StripeElements } from '@stripe/stripe-js';
import { computed, onMounted, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { callback } from '@/routes/checkout';

type Address = {
    first_name: string | null;
    last_name: string | null;
    contact_email: string | null;
    contact_phone: string | null;
    line_one: string | null;
    line_two: string | null;
    city: string | null;
    state: string | null;
    postcode: string | null;
    country: { iso2: string } | null;
};

const props = defineProps<{
    clientSecret: string;
    billing: Address;
}>();

const page = usePage();
const cartLines = computed(() => page.props.cartLines as Array<{ id: number }>);
const subTotal = computed(() => page.props.subTotal);
const shippingTotal = computed(() => page.props.shippingTotal);
const taxTotal = computed(() => page.props.taxTotal);
const total = computed(() => page.props.total);
const paymentError = computed(
    () => (page.props.errors as Record<string, string>)?.payment,
);

const paymentElementRef = ref<HTMLDivElement | null>(null);
const processing = ref(false);
const stripeError = ref<string | null>(null);

let stripe: Stripe | null = null;
let elements: StripeElements | null = null;

onMounted(async () => {
    stripe = await loadStripe(import.meta.env.VITE_STRIPE_PUBLIC);

    if (!stripe || !paymentElementRef.value) {
        return;
    }

    elements = stripe.elements({ clientSecret: props.clientSecret });

    const paymentElement = elements.create('payment', {
        fields: { billingDetails: 'never' },
    });

    paymentElement.mount(paymentElementRef.value);
});

async function submit() {
    if (!stripe || !elements) {
        return;
    }

    processing.value = true;
    stripeError.value = null;

    const { error } = await stripe.confirmPayment({
        elements,
        confirmParams: {
            return_url: new URL(callback().url, window.location.origin).toString(),
            payment_method_data: {
                billing_details: {
                    name: `${props.billing.first_name} ${props.billing.last_name}`,
                    email: props.billing.contact_email ?? '',
                    phone: props.billing.contact_phone ?? '',
                    address: {
                        line1: props.billing.line_one ?? '',
                        line2: props.billing.line_two ?? '',
                        city: props.billing.city ?? '',
                        state: props.billing.state ?? '',
                        postal_code: props.billing.postcode ?? '',
                        country: props.billing.country?.iso2 ?? '',
                    },
                },
            },
        },
    });

    if (error) {
        stripeError.value =
            error.message ?? "Une erreur est survenue lors du paiement.";
        processing.value = false;
    }
}
</script>

<template>
    <Head title="Paiement" />

    <div class="mx-auto max-w-5xl space-y-8 px-4 py-8 md:py-12">
        <Heading
            title="Paiement"
            description="Renseignez vos informations de paiement pour finaliser votre commande."
        />

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="space-y-8 lg:col-span-2">
                <Alert v-if="paymentError" variant="destructive">
                    <AlertDescription>{{ paymentError }}</AlertDescription>
                </Alert>

                <Card>
                    <CardHeader>
                        <CardTitle>Carte bancaire</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div ref="paymentElementRef" />

                        <Alert v-if="stripeError" variant="destructive">
                            <AlertDescription>{{
                                stripeError
                            }}</AlertDescription>
                        </Alert>

                        <Button
                            type="button"
                            size="lg"
                            class="w-full"
                            :disabled="processing"
                            @click="submit"
                        >
                            {{
                                processing
                                    ? 'Traitement...'
                                    : `Payer ${total}`
                            }}
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <div class="lg:col-span-1">
                <Card class="sticky top-8">
                    <CardHeader>
                        <CardTitle>Récapitulatif</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <dl class="space-y-2 text-sm">
                            <div
                                class="flex justify-between text-muted-foreground"
                            >
                                <dt>
                                    Sous-total ({{
                                        cartLines?.length ?? 0
                                    }}
                                    articles)
                                </dt>
                                <dd class="font-medium text-foreground">
                                    {{ subTotal }}
                                </dd>
                            </div>
                            <div
                                v-if="shippingTotal"
                                class="flex justify-between text-muted-foreground"
                            >
                                <dt>Livraison</dt>
                                <dd class="font-medium text-foreground">
                                    {{ shippingTotal }}
                                </dd>
                            </div>
                            <div
                                class="flex justify-between text-muted-foreground"
                            >
                                <dt>TVA</dt>
                                <dd class="font-medium text-foreground">
                                    {{ taxTotal }}
                                </dd>
                            </div>
                        </dl>

                        <Separator />

                        <div
                            class="flex justify-between text-base font-semibold"
                        >
                            <span>Total</span>
                            <span>{{ total }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
