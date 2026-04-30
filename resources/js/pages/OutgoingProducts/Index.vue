<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { User } from '@/types';

type OutgoingProduct = {
    id: number;
    product_name: string;
    quantity: number;
    destination: string;
    status: 'draft' | 'released' | 'delivered' | string;
    preparedBy?: {
        id: number;
        name: string;
        role: string;
    };
    checkedBy?: {
        id: number;
        name: string;
        role: string;
    };
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Outgoing products',
                href: '/outgoing-products',
            },
        ],
    },
});

const props = defineProps<{
    products: OutgoingProduct[];
}>();

const page = usePage();

const user = computed(() => page.props.auth.user as User);
const isChecker = computed(() => user.value.role === 'checker');

const createForm = useForm({
    product_name: '',
    quantity: 1,
    destination: '',
});

const createProduct = () => {
    createForm.post('/outgoing-products', {
        preserveScroll: true,
        onSuccess: () => createForm.reset(),
    });
};

const releaseProduct = (productId: number) => {
    useForm({}).patch(`/outgoing-products/${productId}/release`, {
        preserveScroll: true,
    });
};

const deliverProduct = (productId: number) => {
    useForm({}).patch(`/outgoing-products/${productId}/deliver`, {
        preserveScroll: true,
    });
};

const statusLabel = (status: string): string => {
    if (status === 'draft') return 'Draft';
    if (status === 'released') return 'Released';
    if (status === 'delivered') return 'Delivered';

    return status;
};

const badgeVariant = (
    status: string,
): 'outline' | 'secondary' | 'default' | 'destructive' => {
    if (status === 'delivered') return 'default';
    if (status === 'released') return 'secondary';

    return 'outline';
};
</script>

<template>
    <Head title="Outgoing products" />

    <div class="space-y-6 p-4">
        <Heading
            variant="small"
            title="Outgoing products"
            description="Staff records outgoing items, checker validates release and delivery."
        />

        <form
            class="grid gap-4 rounded-xl border p-4 md:grid-cols-4"
            @submit.prevent="createProduct"
        >
            <div class="grid gap-2 md:col-span-2">
                <Label for="product_name">Product name</Label>
                <Input
                    id="product_name"
                    v-model="createForm.product_name"
                    placeholder="Ex: Fiber optic cable"
                />
                <InputError :message="createForm.errors.product_name" />
            </div>

            <div class="grid gap-2">
                <Label for="quantity">Quantity</Label>
                <Input
                    id="quantity"
                    v-model.number="createForm.quantity"
                    type="number"
                    min="1"
                />
                <InputError :message="createForm.errors.quantity" />
            </div>

            <div class="grid gap-2">
                <Label for="destination">Destination</Label>
                <Input
                    id="destination"
                    v-model="createForm.destination"
                    placeholder="Warehouse B"
                />
                <InputError :message="createForm.errors.destination" />
            </div>

            <div class="md:col-span-4">
                <Button :disabled="createForm.processing">Create outgoing record</Button>
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border">
            <table class="w-full text-sm">
                <thead class="bg-muted/40 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">Product</th>
                        <th class="px-4 py-3 font-medium">Qty</th>
                        <th class="px-4 py-3 font-medium">Destination</th>
                        <th class="px-4 py-3 font-medium">Prepared by</th>
                        <th class="px-4 py-3 font-medium">Checked by</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="product in props.products"
                        :key="product.id"
                        class="border-t"
                    >
                        <td class="px-4 py-3">{{ product.product_name }}</td>
                        <td class="px-4 py-3">{{ product.quantity }}</td>
                        <td class="px-4 py-3">{{ product.destination }}</td>
                        <td class="px-4 py-3">{{ product.preparedBy?.name ?? 'N/A' }}</td>
                        <td class="px-4 py-3">{{ product.checkedBy?.name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <Badge :variant="badgeVariant(product.status)">
                                {{ statusLabel(product.status) }}
                            </Badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <Button
                                    v-if="isChecker && product.status === 'draft'"
                                    size="sm"
                                    variant="secondary"
                                    @click="releaseProduct(product.id)"
                                >
                                    Release
                                </Button>
                                <Button
                                    v-if="isChecker && product.status === 'released'"
                                    size="sm"
                                    @click="deliverProduct(product.id)"
                                >
                                    Mark delivered
                                </Button>
                                <span
                                    v-if="!isChecker"
                                    class="text-xs text-muted-foreground"
                                >
                                    Checker approval required
                                </span>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="props.products.length === 0">
                        <td
                            colspan="7"
                            class="px-4 py-8 text-center text-muted-foreground"
                        >
                            No outgoing products yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
