<script setup lang="ts">
import { saveAddresses } from '@/actions/App/Http/Controllers/CheckoutController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardAction, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

type Address = {
    country_id: number | null;
    first_name: string | null;
    last_name: string | null;
    company_name: string | null;
    line_one: string | null;
    line_two: string | null;
    line_three: string | null;
    city: string | null;
    state: string | null;
    postcode: string | null;
    contact_email: string | null;
    contact_phone: string | null;
};

const props = defineProps<{
    countries: Array<{ id: number; name: string }>;
    billing: Address | null;
    shipping: (Address & { delivery_instructions: string | null }) | null;
    isShippable: boolean;
}>();

const page = usePage();
const cartLines = computed(() => page.props.cartLines as Array<{ id: number }>);
const subTotal = computed(() => page.props.subTotal);
const taxTotal = computed(() => page.props.taxTotal);
const total = computed(() => page.props.total);

const shipToBilling = ref(props.shipping === null);
</script>

<template>
    <Head title="Livraison et facturation" />

    <div class="mx-auto max-w-5xl space-y-8 px-4 py-8 md:py-12">
        <Heading
            title="Livraison et facturation"
            description="Renseignez vos adresses pour continuer votre commande."
        />

        <Form v-bind="saveAddresses.form()" v-slot="{ errors, processing }" class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="space-y-8 lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Adresse de facturation</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="billing_country_id">Pays</Label>
                            <Select name="billing[country_id]" :default-value="billing?.country_id ? String(billing.country_id) : undefined">
                                <SelectTrigger id="billing_country_id" class="w-full">
                                    <SelectValue placeholder="Choisir un pays" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="country in countries" :key="country.id" :value="String(country.id)">
                                        {{ country.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors['billing.country_id']" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="billing_first_name">Prénom</Label>
                                <Input id="billing_first_name" name="billing[first_name]" :default-value="billing?.first_name ?? undefined" />
                                <InputError :message="errors['billing.first_name']" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="billing_last_name">Nom</Label>
                                <Input id="billing_last_name" name="billing[last_name]" :default-value="billing?.last_name ?? undefined" />
                                <InputError :message="errors['billing.last_name']" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="billing_company_name">Société (optionnel)</Label>
                            <Input id="billing_company_name" name="billing[company_name]" :default-value="billing?.company_name ?? undefined" />
                            <InputError :message="errors['billing.company_name']" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="billing_line_one">Adresse</Label>
                            <Input id="billing_line_one" name="billing[line_one]" :default-value="billing?.line_one ?? undefined" />
                            <InputError :message="errors['billing.line_one']" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="billing_line_two">Complément d'adresse (optionnel)</Label>
                            <Input id="billing_line_two" name="billing[line_two]" :default-value="billing?.line_two ?? undefined" />
                            <InputError :message="errors['billing.line_two']" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="billing_city">Ville</Label>
                                <Input id="billing_city" name="billing[city]" :default-value="billing?.city ?? undefined" />
                                <InputError :message="errors['billing.city']" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="billing_postcode">Code postal</Label>
                                <Input id="billing_postcode" name="billing[postcode]" :default-value="billing?.postcode ?? undefined" />
                                <InputError :message="errors['billing.postcode']" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="billing_state">Région (optionnel)</Label>
                            <Input id="billing_state" name="billing[state]" :default-value="billing?.state ?? undefined" />
                            <InputError :message="errors['billing.state']" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="billing_contact_email">Email</Label>
                                <Input
                                    id="billing_contact_email"
                                    type="email"
                                    name="billing[contact_email]"
                                    :default-value="billing?.contact_email ?? undefined"
                                />
                                <InputError :message="errors['billing.contact_email']" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="billing_contact_phone">Téléphone (optionnel)</Label>
                                <Input id="billing_contact_phone" name="billing[contact_phone]" :default-value="billing?.contact_phone ?? undefined" />
                                <InputError :message="errors['billing.contact_phone']" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="isShippable">
                    <CardHeader>
                        <CardTitle>Adresse de livraison</CardTitle>
                        <CardAction>
                            <Label class="text-sm font-normal">
                                <Checkbox name="ship_to_billing" value="1" v-model="shipToBilling" />
                                Identique à la facturation
                            </Label>
                        </CardAction>
                    </CardHeader>
                    <CardContent v-show="!shipToBilling" class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="shipping_country_id">Pays</Label>
                            <Select
                                name="shipping[country_id]"
                                :disabled="shipToBilling"
                                :default-value="shipping?.country_id ? String(shipping.country_id) : undefined"
                            >
                                <SelectTrigger id="shipping_country_id" class="w-full">
                                    <SelectValue placeholder="Choisir un pays" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="country in countries" :key="country.id" :value="String(country.id)">
                                        {{ country.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="errors['shipping.country_id']" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="shipping_first_name">Prénom</Label>
                                <Input
                                    id="shipping_first_name"
                                    name="shipping[first_name]"
                                    :disabled="shipToBilling"
                                    :default-value="shipping?.first_name ?? undefined"
                                />
                                <InputError :message="errors['shipping.first_name']" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="shipping_last_name">Nom</Label>
                                <Input
                                    id="shipping_last_name"
                                    name="shipping[last_name]"
                                    :disabled="shipToBilling"
                                    :default-value="shipping?.last_name ?? undefined"
                                />
                                <InputError :message="errors['shipping.last_name']" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="shipping_company_name">Société (optionnel)</Label>
                            <Input
                                id="shipping_company_name"
                                name="shipping[company_name]"
                                :disabled="shipToBilling"
                                :default-value="shipping?.company_name ?? undefined"
                            />
                            <InputError :message="errors['shipping.company_name']" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="shipping_line_one">Adresse</Label>
                            <Input id="shipping_line_one" name="shipping[line_one]" :disabled="shipToBilling" :default-value="shipping?.line_one ?? undefined" />
                            <InputError :message="errors['shipping.line_one']" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="shipping_line_two">Complément d'adresse (optionnel)</Label>
                            <Input
                                id="shipping_line_two"
                                name="shipping[line_two]"
                                :disabled="shipToBilling"
                                :default-value="shipping?.line_two ?? undefined"
                            />
                            <InputError :message="errors['shipping.line_two']" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="shipping_city">Ville</Label>
                                <Input id="shipping_city" name="shipping[city]" :disabled="shipToBilling" :default-value="shipping?.city ?? undefined" />
                                <InputError :message="errors['shipping.city']" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="shipping_postcode">Code postal</Label>
                                <Input
                                    id="shipping_postcode"
                                    name="shipping[postcode]"
                                    :disabled="shipToBilling"
                                    :default-value="shipping?.postcode ?? undefined"
                                />
                                <InputError :message="errors['shipping.postcode']" />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="shipping_state">Région (optionnel)</Label>
                            <Input id="shipping_state" name="shipping[state]" :disabled="shipToBilling" :default-value="shipping?.state ?? undefined" />
                            <InputError :message="errors['shipping.state']" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="shipping_delivery_instructions">Instructions de livraison (optionnel)</Label>
                            <Input
                                id="shipping_delivery_instructions"
                                name="shipping[delivery_instructions]"
                                :disabled="shipToBilling"
                                :default-value="shipping?.delivery_instructions ?? undefined"
                            />
                            <InputError :message="errors['shipping.delivery_instructions']" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="shipping_contact_email">Email (optionnel)</Label>
                                <Input
                                    id="shipping_contact_email"
                                    type="email"
                                    name="shipping[contact_email]"
                                    :disabled="shipToBilling"
                                    :default-value="shipping?.contact_email ?? undefined"
                                />
                                <InputError :message="errors['shipping.contact_email']" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="shipping_contact_phone">Téléphone (optionnel)</Label>
                                <Input
                                    id="shipping_contact_phone"
                                    name="shipping[contact_phone]"
                                    :disabled="shipToBilling"
                                    :default-value="shipping?.contact_phone ?? undefined"
                                />
                                <InputError :message="errors['shipping.contact_phone']" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Button type="submit" size="lg" class="w-full" :disabled="processing">
                    Continuer
                </Button>
            </div>

            <div class="lg:col-span-1">
                <Card class="sticky top-8">
                    <CardHeader>
                        <CardTitle>Récapitulatif</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between text-muted-foreground">
                                <dt>Sous-total ({{ cartLines?.length ?? 0 }} articles)</dt>
                                <dd class="font-medium text-foreground">{{ subTotal }}</dd>
                            </div>
                            <div class="flex justify-between text-muted-foreground">
                                <dt>TVA</dt>
                                <dd class="font-medium text-foreground">{{ taxTotal }}</dd>
                            </div>
                        </dl>

                        <Separator />

                        <div class="flex justify-between text-base font-semibold">
                            <span>Total</span>
                            <span>{{ total }}</span>
                        </div>

                        <Button as-child variant="link" class="w-full">
                            <Link href="/cart">Retour au panier</Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </Form>
    </div>
</template>
