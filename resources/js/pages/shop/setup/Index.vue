<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    Building2,
    Check,
    ChevronLeft,
    ChevronRight,
    MapPin,
    PackageCheck,
    Plus,
    Store,
    Trash2,
    Truck,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

type OwnershipType = 'independent' | 'company_owned' | 'franchisee';

interface SetupBranch {
    name: string;
    phone: string;
    block_street: string;
    municipality: string;
    barangay: string;
    postal_code: string;
    mayors_permit_number: string;
    mayors_permit: File | null;
    mayors_expiry_date: string;
    sanitary_permit_number: string;
    sanitary_permit: File | null;
    sanitary_expiry_date: string;
}

const props = defineProps<{
    shop: {
        shop_name: string;
        phone: string;
        address: string;
        location_mode: 'single' | 'multiple';
        ownership_type: OwnershipType;
        franchise_brand_name: string;
        franchisor_name: string;
        branches_count: number;
        offers_pickup: boolean;
        offers_delivery: boolean;
        setup_completed: boolean;
    };
    plan: {
        name: string;
        is_trial: boolean;
        allows_multiple_locations: boolean;
    };
}>();

const blankBranch = (): SetupBranch => ({
    name: '',
    phone: '',
    block_street: '',
    municipality: '',
    barangay: '',
    postal_code: '',
    mayors_permit_number: '',
    mayors_permit: null,
    mayors_expiry_date: '',
    sanitary_permit_number: '',
    sanitary_permit: null,
    sanitary_expiry_date: '',
});

const step = ref(1);
const totalSteps = 5;
const localErrors = ref<Record<string, string>>({});
const form = useForm({
    ownership_type: props.shop.ownership_type,
    franchise_brand_name: props.shop.franchise_brand_name,
    franchisor_name: props.shop.franchisor_name,
    location_mode: props.shop.location_mode,
    branches: [] as SetupBranch[],
    offers_pickup: props.shop.offers_pickup,
    offers_delivery: props.shop.offers_delivery,
});

const locationLabel = computed(() =>
    form.location_mode === 'multiple' ? 'Multiple locations' : 'One location',
);
const ownershipLabel = computed(
    () =>
        ({
            independent: 'Independent business',
            company_owned: 'Company-owned locations',
            franchisee: 'Franchise operator',
        })[form.ownership_type],
);
const needsBranchDetails = computed(
    () => form.location_mode === 'multiple' && props.shop.branches_count === 0,
);
const totalLocations = computed(
    () => 1 + props.shop.branches_count + form.branches.length,
);

function chooseOwnership(type: OwnershipType) {
    form.ownership_type = type;
    if (type !== 'franchisee') {
        form.franchise_brand_name = '';
        form.franchisor_name = '';
    }
}

function chooseLocation(mode: 'single' | 'multiple') {
    if (mode === 'multiple' && !props.plan.allows_multiple_locations) return;
    form.location_mode = mode;
    if (
        mode === 'multiple' &&
        needsBranchDetails.value &&
        form.branches.length === 0
    ) {
        form.branches.push(blankBranch());
    }
}

function addBranch() {
    form.branches.push(blankBranch());
}

function removeBranch(index: number) {
    form.branches.splice(index, 1);
}

function setBranchFile(
    branch: SetupBranch,
    field: 'mayors_permit' | 'sanitary_permit',
    event: Event,
) {
    branch[field] = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function validateCurrentStep(): boolean {
    const errors: Record<string, string> = {};

    if (step.value === 2 && form.ownership_type === 'franchisee') {
        if (!form.franchise_brand_name.trim())
            errors.franchise_brand_name = 'Enter the franchise brand name.';
        if (!form.franchisor_name.trim())
            errors.franchisor_name =
                'Enter the franchisor or franchise company.';
    }

    if (step.value === 3 && needsBranchDetails.value) {
        if (form.branches.length === 0)
            errors.branches = 'Add at least one branch.';
        form.branches.forEach((branch, index) => {
            const key = `branches.${index}`;
            if (!branch.name.trim())
                errors[`${key}.name`] = 'Branch name is required.';
            if (!branch.block_street.trim())
                errors[`${key}.block_street`] = 'Street address is required.';
            if (!branch.municipality.trim())
                errors[`${key}.municipality`] = 'Municipality is required.';
            if (!branch.barangay.trim())
                errors[`${key}.barangay`] = 'Barangay is required.';
            if (!branch.postal_code.trim())
                errors[`${key}.postal_code`] = 'Postal code is required.';
            if (!branch.mayors_permit_number.trim())
                errors[`${key}.mayors_permit_number`] =
                    'Permit number is required.';
            if (!branch.mayors_permit)
                errors[`${key}.mayors_permit`] =
                    "Mayor's permit file is required.";
            if (!branch.mayors_expiry_date)
                errors[`${key}.mayors_expiry_date`] =
                    'Expiry date is required.';
        });
    }

    localErrors.value = errors;
    return Object.keys(errors).length === 0;
}

function next() {
    if (validateCurrentStep() && step.value < totalSteps) step.value++;
}

function back() {
    localErrors.value = {};
    if (step.value > 1) step.value--;
}

function errorFor(key: string): string | undefined {
    return (
        localErrors.value[key] || (form.errors as Record<string, string>)[key]
    );
}

function submit() {
    form.transform((data) => ({ ...data, _method: 'put' })).post(
        '/shop/setup',
        {
            forceFormData: true,
        },
    );
}
</script>

<template>
    <Head title="Operations Setup" />

    <ShopLayout title="Operations Setup">
        <main class="min-h-full bg-muted/30 px-4 py-8 sm:py-12">
            <div class="mx-auto max-w-4xl">
                <div class="mb-8 text-center">
                    <div
                        class="mb-3 inline-flex items-center gap-2 rounded-full border bg-background px-3 py-1 text-xs font-medium"
                    >
                        <PackageCheck class="h-3.5 w-3.5 text-primary" />
                        {{ plan.name }} plan
                    </div>
                    <h1
                        class="text-2xl font-semibold tracking-tight sm:text-3xl"
                    >
                        Set up your shop
                    </h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Configure ownership, locations, permits, pickup, and
                        delivery.
                    </p>
                </div>

                <div
                    class="mb-6 grid grid-cols-5 gap-2"
                    aria-label="Setup progress"
                >
                    <div
                        v-for="item in totalSteps"
                        :key="item"
                        class="space-y-2"
                    >
                        <div
                            class="h-1.5 rounded-full transition-colors"
                            :class="item <= step ? 'bg-primary' : 'bg-border'"
                        />
                        <p
                            class="text-center text-[11px] text-muted-foreground"
                        >
                            Step {{ item }}
                        </p>
                    </div>
                </div>

                <Card>
                    <CardContent class="p-6 sm:p-8">
                        <section v-if="step === 1" class="space-y-6">
                            <div>
                                <h2 class="text-xl font-semibold">
                                    Confirm your registered shop
                                </h2>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    This is the main location registered to this
                                    owner account.
                                </p>
                            </div>
                            <div class="rounded-xl border bg-muted/30 p-5">
                                <div class="flex items-start gap-3">
                                    <Store
                                        class="mt-0.5 h-5 w-5 text-primary"
                                    />
                                    <div class="min-w-0 space-y-2">
                                        <p class="font-semibold">
                                            {{ shop.shop_name }}
                                        </p>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            {{ shop.phone }}
                                        </p>
                                        <p
                                            class="flex items-start gap-2 text-sm text-muted-foreground"
                                        >
                                            <MapPin
                                                class="mt-0.5 h-4 w-4 shrink-0"
                                            />
                                            {{
                                                shop.address ||
                                                'No address recorded'
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section v-else-if="step === 2" class="space-y-6">
                            <div>
                                <h2 class="text-xl font-semibold">
                                    How is this shop owned?
                                </h2>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    A shared shop name or branding does not mean
                                    the locations have the same owner.
                                </p>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <button
                                    v-for="option in [
                                        {
                                            value: 'independent',
                                            label: 'Independent',
                                            text: 'A standalone business operated by this owner.',
                                        },
                                        {
                                            value: 'company_owned',
                                            label: 'Company-owned',
                                            text: 'The same legal owner operates all added locations.',
                                        },
                                        {
                                            value: 'franchisee',
                                            label: 'Franchise operator',
                                            text: 'This owner operates locations under a franchise brand.',
                                        },
                                    ]"
                                    :key="option.value"
                                    type="button"
                                    class="rounded-xl border p-5 text-left transition"
                                    :class="
                                        form.ownership_type === option.value
                                            ? 'border-primary bg-primary/5 ring-1 ring-primary'
                                            : 'hover:border-primary/40'
                                    "
                                    @click="
                                        chooseOwnership(
                                            option.value as OwnershipType,
                                        )
                                    "
                                >
                                    <Building2
                                        class="mb-3 h-6 w-6 text-primary"
                                    />
                                    <p class="font-semibold">
                                        {{ option.label }}
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{ option.text }}
                                    </p>
                                </button>
                            </div>
                            <div
                                v-if="form.ownership_type === 'franchisee'"
                                class="space-y-4 rounded-xl border bg-muted/20 p-5"
                            >
                                <div>
                                    <Label class="mb-2"
                                        >Franchise Brand Name</Label
                                    >
                                    <Input
                                        v-model="form.franchise_brand_name"
                                        placeholder="Brand shown to customers"
                                    />
                                    <InputError
                                        :message="
                                            errorFor('franchise_brand_name')
                                        "
                                    />
                                </div>
                                <div>
                                    <Label class="mb-2"
                                        >Franchisor / Franchise Company</Label
                                    >
                                    <Input
                                        v-model="form.franchisor_name"
                                        placeholder="Legal franchisor name"
                                    />
                                    <InputError
                                        :message="errorFor('franchisor_name')"
                                    />
                                </div>
                                <p
                                    class="text-xs text-amber-700 dark:text-amber-300"
                                >
                                    Add only locations legally operated by this
                                    account owner. Locations run by other
                                    franchisees must register under their own
                                    owner accounts.
                                </p>
                            </div>
                        </section>

                        <section v-else-if="step === 3" class="space-y-6">
                            <div>
                                <h2 class="text-xl font-semibold">
                                    Do you operate additional branches?
                                </h2>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    The registered shop is the main location.
                                    Add only branches under the same owner or
                                    franchise operator.
                                </p>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <button
                                    type="button"
                                    class="rounded-xl border p-5 text-left transition"
                                    :class="
                                        form.location_mode === 'single'
                                            ? 'border-primary bg-primary/5 ring-1 ring-primary'
                                            : 'hover:border-primary/40'
                                    "
                                    @click="chooseLocation('single')"
                                >
                                    <Store class="mb-3 h-6 w-6 text-primary" />
                                    <p class="font-semibold">
                                        No additional branches
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Use only the registered main location.
                                    </p>
                                </button>
                                <button
                                    type="button"
                                    class="rounded-xl border p-5 text-left transition disabled:cursor-not-allowed disabled:opacity-60"
                                    :class="
                                        form.location_mode === 'multiple'
                                            ? 'border-primary bg-primary/5 ring-1 ring-primary'
                                            : 'hover:border-primary/40'
                                    "
                                    :disabled="!plan.allows_multiple_locations"
                                    @click="chooseLocation('multiple')"
                                >
                                    <Building2
                                        class="mb-3 h-6 w-6 text-primary"
                                    />
                                    <p class="font-semibold">
                                        Yes, add branches
                                    </p>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        {{
                                            plan.allows_multiple_locations
                                                ? 'Provide each branch location and permits.'
                                                : 'Available with the Premium plan.'
                                        }}
                                    </p>
                                </button>
                            </div>
                            <InputError :message="form.errors.location_mode" />

                            <div
                                v-if="
                                    form.location_mode === 'multiple' &&
                                    shop.branches_count > 0
                                "
                                class="rounded-xl border bg-muted/20 p-4 text-sm"
                            >
                                {{ shop.branches_count }} branch{{
                                    shop.branches_count === 1 ? '' : 'es'
                                }}
                                already configured. Manage their records from
                                Branch Management.
                            </div>

                            <div v-if="needsBranchDetails" class="space-y-4">
                                <InputError :message="errorFor('branches')" />
                                <div
                                    v-for="(branch, index) in form.branches"
                                    :key="index"
                                    class="space-y-5 rounded-xl border p-5"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <h3 class="font-semibold">
                                            Additional Branch {{ index + 1 }}
                                        </h3>
                                        <Button
                                            type="button"
                                            size="icon"
                                            variant="ghost"
                                            @click="removeBranch(index)"
                                            ><Trash2
                                                class="h-4 w-4 text-red-500"
                                        /></Button>
                                    </div>
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <Label class="mb-2"
                                                >Branch Name</Label
                                            >
                                            <Input
                                                v-model="branch.name"
                                                placeholder="e.g. Molino Branch"
                                            />
                                            <InputError
                                                :message="
                                                    errorFor(
                                                        `branches.${index}.name`,
                                                    )
                                                "
                                            />
                                        </div>
                                        <div>
                                            <Label class="mb-2"
                                                >Contact Number
                                                (optional)</Label
                                            >
                                            <PhilippinePhoneInput
                                                v-model="branch.phone"
                                            />
                                            <InputError
                                                :message="
                                                    errorFor(
                                                        `branches.${index}.phone`,
                                                    )
                                                "
                                            />
                                        </div>
                                        <div class="sm:col-span-2">
                                            <Label class="mb-2"
                                                >Block / Street /
                                                Building</Label
                                            >
                                            <Input
                                                v-model="branch.block_street"
                                            />
                                            <InputError
                                                :message="
                                                    errorFor(
                                                        `branches.${index}.block_street`,
                                                    )
                                                "
                                            />
                                        </div>
                                        <div>
                                            <Label class="mb-2"
                                                >Municipality</Label
                                            >
                                            <Input
                                                v-model="branch.municipality"
                                            />
                                            <InputError
                                                :message="
                                                    errorFor(
                                                        `branches.${index}.municipality`,
                                                    )
                                                "
                                            />
                                        </div>
                                        <div>
                                            <Label class="mb-2">Barangay</Label>
                                            <Input v-model="branch.barangay" />
                                            <InputError
                                                :message="
                                                    errorFor(
                                                        `branches.${index}.barangay`,
                                                    )
                                                "
                                            />
                                        </div>
                                        <div>
                                            <Label class="mb-2"
                                                >Postal Code</Label
                                            >
                                            <Input
                                                v-model="branch.postal_code"
                                            />
                                            <InputError
                                                :message="
                                                    errorFor(
                                                        `branches.${index}.postal_code`,
                                                    )
                                                "
                                            />
                                        </div>
                                    </div>

                                    <div class="border-t pt-5">
                                        <p class="mb-4 text-sm font-semibold">
                                            Branch-specific local permits
                                        </p>
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div>
                                                <Label class="mb-2"
                                                    >Mayor's Permit
                                                    Number</Label
                                                >
                                                <Input
                                                    v-model="
                                                        branch.mayors_permit_number
                                                    "
                                                />
                                                <InputError
                                                    :message="
                                                        errorFor(
                                                            `branches.${index}.mayors_permit_number`,
                                                        )
                                                    "
                                                />
                                            </div>
                                            <div>
                                                <Label class="mb-2"
                                                    >Mayor's Permit
                                                    Expiry</Label
                                                >
                                                <Input
                                                    v-model="
                                                        branch.mayors_expiry_date
                                                    "
                                                    type="date"
                                                />
                                                <InputError
                                                    :message="
                                                        errorFor(
                                                            `branches.${index}.mayors_expiry_date`,
                                                        )
                                                    "
                                                />
                                            </div>
                                            <div class="sm:col-span-2">
                                                <Label class="mb-2"
                                                    >Mayor's Permit File (PDF,
                                                    JPG, or PNG; max 5
                                                    MB)</Label
                                                >
                                                <input
                                                    type="file"
                                                    accept=".pdf,.jpg,.jpeg,.png"
                                                    class="block w-full text-sm"
                                                    @change="
                                                        setBranchFile(
                                                            branch,
                                                            'mayors_permit',
                                                            $event,
                                                        )
                                                    "
                                                />
                                                <InputError
                                                    :message="
                                                        errorFor(
                                                            `branches.${index}.mayors_permit`,
                                                        )
                                                    "
                                                />
                                            </div>
                                            <div>
                                                <Label class="mb-2"
                                                    >Sanitary Permit Number
                                                    (optional)</Label
                                                >
                                                <Input
                                                    v-model="
                                                        branch.sanitary_permit_number
                                                    "
                                                />
                                            </div>
                                            <div>
                                                <Label class="mb-2"
                                                    >Sanitary Permit
                                                    Expiry</Label
                                                >
                                                <Input
                                                    v-model="
                                                        branch.sanitary_expiry_date
                                                    "
                                                    type="date"
                                                />
                                                <InputError
                                                    :message="
                                                        errorFor(
                                                            `branches.${index}.sanitary_expiry_date`,
                                                        )
                                                    "
                                                />
                                            </div>
                                            <div class="sm:col-span-2">
                                                <Label class="mb-2"
                                                    >Sanitary Permit File
                                                    (optional)</Label
                                                >
                                                <input
                                                    type="file"
                                                    accept=".pdf,.jpg,.jpeg,.png"
                                                    class="block w-full text-sm"
                                                    @change="
                                                        setBranchFile(
                                                            branch,
                                                            'sanitary_permit',
                                                            $event,
                                                        )
                                                    "
                                                />
                                                <InputError
                                                    :message="
                                                        errorFor(
                                                            `branches.${index}.sanitary_permit`,
                                                        )
                                                    "
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="addBranch"
                                    ><Plus class="mr-2 h-4 w-4" /> Add Another
                                    Branch</Button
                                >
                                <p class="text-xs text-muted-foreground">
                                    Total locations after setup:
                                    {{ totalLocations }}
                                </p>
                            </div>
                        </section>

                        <section v-else-if="step === 4" class="space-y-6">
                            <div>
                                <h2 class="text-xl font-semibold">
                                    Choose customer fulfillment options
                                </h2>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Walk-in service remains available. Enable
                                    only the extra services your shop provides.
                                </p>
                            </div>
                            <div class="space-y-3">
                                <div
                                    class="flex items-center gap-4 rounded-xl border bg-muted/20 p-4"
                                >
                                    <Store class="h-5 w-5 text-primary" />
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold">
                                            Customers visit the shop
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Available by default.
                                        </p>
                                    </div>
                                    <Check class="h-5 w-5 text-emerald-600" />
                                </div>
                                <label
                                    class="flex cursor-pointer items-center gap-4 rounded-xl border p-4 hover:border-primary/40"
                                >
                                    <PackageCheck
                                        class="h-5 w-5 text-primary"
                                    />
                                    <span class="flex-1"
                                        ><span
                                            class="block text-sm font-semibold"
                                            >Pickup from customer</span
                                        ><span
                                            class="block text-xs text-muted-foreground"
                                            >Customers may ask the shop to
                                            collect their laundry.</span
                                        ></span
                                    >
                                    <input
                                        v-model="form.offers_pickup"
                                        type="checkbox"
                                        class="h-5 w-5 rounded border-border accent-primary"
                                    />
                                </label>
                                <label
                                    class="flex cursor-pointer items-center gap-4 rounded-xl border p-4 hover:border-primary/40"
                                >
                                    <Truck class="h-5 w-5 text-primary" />
                                    <span class="flex-1"
                                        ><span
                                            class="block text-sm font-semibold"
                                            >Delivery to customer</span
                                        ><span
                                            class="block text-xs text-muted-foreground"
                                            >Enable riders, logistics, and
                                            delivery requests.</span
                                        ></span
                                    >
                                    <input
                                        v-model="form.offers_delivery"
                                        type="checkbox"
                                        class="h-5 w-5 rounded border-border accent-primary"
                                    />
                                </label>
                            </div>
                        </section>

                        <section v-else class="space-y-6">
                            <div>
                                <h2 class="text-xl font-semibold">
                                    Review your setup
                                </h2>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Only locations operated by this owner
                                    account will be connected.
                                </p>
                            </div>
                            <dl class="divide-y rounded-xl border">
                                <div
                                    class="flex items-center justify-between gap-4 p-4"
                                >
                                    <dt class="text-sm text-muted-foreground">
                                        Subscription
                                    </dt>
                                    <dd class="text-sm font-semibold">
                                        {{ plan.name }}
                                    </dd>
                                </div>
                                <div
                                    class="flex items-center justify-between gap-4 p-4"
                                >
                                    <dt class="text-sm text-muted-foreground">
                                        Ownership
                                    </dt>
                                    <dd class="text-sm font-semibold">
                                        {{ ownershipLabel }}
                                    </dd>
                                </div>
                                <div
                                    v-if="form.ownership_type === 'franchisee'"
                                    class="flex items-center justify-between gap-4 p-4"
                                >
                                    <dt class="text-sm text-muted-foreground">
                                        Franchise
                                    </dt>
                                    <dd
                                        class="text-right text-sm font-semibold"
                                    >
                                        {{ form.franchise_brand_name
                                        }}<span
                                            class="block text-xs font-normal text-muted-foreground"
                                            >{{ form.franchisor_name }}</span
                                        >
                                    </dd>
                                </div>
                                <div
                                    class="flex items-center justify-between gap-4 p-4"
                                >
                                    <dt class="text-sm text-muted-foreground">
                                        Locations
                                    </dt>
                                    <dd class="text-sm font-semibold">
                                        {{ locationLabel
                                        }}<span
                                            v-if="
                                                form.location_mode ===
                                                'multiple'
                                            "
                                            class="ml-1 text-muted-foreground"
                                            >({{ totalLocations }} total)</span
                                        >
                                    </dd>
                                </div>
                                <div
                                    class="flex items-center justify-between gap-4 p-4"
                                >
                                    <dt class="text-sm text-muted-foreground">
                                        Customer pickup
                                    </dt>
                                    <dd class="text-sm font-semibold">
                                        {{
                                            form.offers_pickup
                                                ? 'Enabled'
                                                : 'Disabled'
                                        }}
                                    </dd>
                                </div>
                                <div
                                    class="flex items-center justify-between gap-4 p-4"
                                >
                                    <dt class="text-sm text-muted-foreground">
                                        Customer delivery
                                    </dt>
                                    <dd class="text-sm font-semibold">
                                        {{
                                            form.offers_delivery
                                                ? 'Enabled'
                                                : 'Disabled'
                                        }}
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        <div
                            class="mt-8 flex items-center justify-between border-t pt-5"
                        >
                            <Button
                                v-if="step > 1"
                                type="button"
                                variant="outline"
                                @click="back"
                                ><ChevronLeft class="mr-2 h-4 w-4" />
                                Back</Button
                            ><span v-else />
                            <Button
                                v-if="step < totalSteps"
                                type="button"
                                @click="next"
                                >Continue <ChevronRight class="ml-2 h-4 w-4"
                            /></Button>
                            <Button
                                v-else
                                type="button"
                                :disabled="form.processing"
                                @click="submit"
                                ><Check class="mr-2 h-4 w-4" />{{
                                    form.processing
                                        ? 'Saving...'
                                        : shop.setup_completed
                                          ? 'Save Changes'
                                          : 'Finish Setup'
                                }}</Button
                            >
                        </div>
                    </CardContent>
                </Card>
            </div>
        </main>
    </ShopLayout>
</template>
