<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type AppPageProps, type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';

// shadcn components
import PhilippinePhoneInput from '@/components/PhilippinePhoneInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

// icons
import { Loader2, Mail } from 'lucide-vue-next';

interface Shop {
    id: number;
    shop_name: string;
    branch_name: string | null;
}

interface PsgcItem {
    code: string;
    name: string;
}

interface EmploymentTypeOption {
    value: string;
    label: string;
}

const { branch_names, shop, roles, employment_types, pay_bases } = defineProps<{
    roles: string[];
    branch_names: string[];
    shop: Shop;
    employment_types: EmploymentTypeOption[];
    pay_bases: EmploymentTypeOption[];
}>();

const page = usePage<AppPageProps>();
const isOwner = computed(() => page.props.auth.user.role === 'owner');
const baseRoute = computed(() => (isOwner.value ? '/shop' : '/staff'));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: `${baseRoute.value}/employee` },
    { title: 'Add Employee', href: `${baseRoute.value}/employee/create` },
];
const errors = computed(() => page.props.errors as Record<string, string>);

function generateEmployeeId(): string {
    const year = new Date().getFullYear();
    const digits = String(Math.floor(10000 + Math.random() * 90000));
    return `${year}-${digits}`;
}

const BASE = 'https://psgc.cloud/api';

const provinces = ref<PsgcItem[]>([]);
const cities = ref<PsgcItem[]>([]);
const barangays = ref<PsgcItem[]>([]);

const loadingProvinces = ref(false);
const loadingCities = ref(false);
const loadingBarangays = ref(false);

const selectedProvince = ref('');
const selectedCity = ref('');
const selectedBarangay = ref('');
const streetInput = ref('');

onMounted(async () => {
    loadingProvinces.value = true;
    try {
        const res = await fetch(`${BASE}/provinces`);
        const data = await res.json();
        provinces.value = data
            .map((p: any) => ({ code: p.code, name: p.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name));
    } finally {
        loadingProvinces.value = false;
    }
    form.value.employee_id = generateEmployeeId();
});

watch(selectedProvince, async (code) => {
    selectedCity.value = '';
    selectedBarangay.value = '';
    cities.value = [];
    barangays.value = [];
    if (!code) return;
    loadingCities.value = true;
    try {
        const [citRes, munRes] = await Promise.all([
            fetch(`${BASE}/provinces/${code}/cities`),
            fetch(`${BASE}/provinces/${code}/municipalities`),
        ]);
        const citData = await citRes.json();
        const munData = await munRes.json();
        cities.value = [...(citData || []), ...(munData || [])]
            .map((c: any) => ({ code: c.code, name: c.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name));
    } finally {
        loadingCities.value = false;
    }
});

watch(selectedCity, async (code) => {
    selectedBarangay.value = '';
    barangays.value = [];
    if (!code) return;
    loadingBarangays.value = true;
    try {
        const res = await fetch(
            `${BASE}/cities-municipalities/${code}/barangays`,
        );
        const data = await res.json();
        barangays.value = (data || [])
            .map((b: any) => ({ code: b.code, name: b.name }))
            .sort((a: PsgcItem, b: PsgcItem) => a.name.localeCompare(b.name));
    } finally {
        loadingBarangays.value = false;
    }
});

const fullAddress = computed(() => {
    const parts = [
        streetInput.value.trim(),
        barangays.value.find((b) => b.code === selectedBarangay.value)?.name ??
            '',
        cities.value.find((c) => c.code === selectedCity.value)?.name ?? '',
        provinces.value.find((p) => p.code === selectedProvince.value)?.name ??
            '',
    ].filter(Boolean);
    return parts.join(', ');
});

// For staff, pre-select their only available branch; for owners use the shop default
const defaultBranch =
    branch_names.length === 1 ? branch_names[0] : (shop.branch_name ?? '');

const form = ref({
    employee_id: generateEmployeeId(),
    branch_name: defaultBranch,
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    address: '',
    position: '',
    employment_type: '',
    hire_date: '',
    pay_rate: '',
    pay_basis: 'monthly',
    status: 'Active' as 'Active' | 'Inactive',
});

const payBasisHelp = computed(() => {
    const messages: Record<string, string> = {
        monthly: 'Payroll converts this monthly rate using 26 working days.',
        daily: 'The rate is paid for each present day; half-days receive half the rate.',
        hourly: 'Attendance currently uses an 8-hour workday for hourly payroll.',
        per_shift: 'Each present attendance day is treated as one paid shift.',
        fixed_contract:
            'The full rate is paid once per payroll period and is not reduced by attendance.',
    };

    return messages[form.value.pay_basis] ?? '';
});

const isSubmitting = ref(false);

function cancel() {
    router.visit(`${baseRoute.value}/employee`);
}

function submit() {
    form.value.address = fullAddress.value;
    isSubmitting.value = true;
    const payload = {
        ...form.value,
        branch_name:
            form.value.branch_name === '__none__' ? '' : form.value.branch_name,
    };
    router.post(`${baseRoute.value}/employee`, payload, {
        preserveScroll: true,
        onError: () => {
            toast.error('Failed to add employee', {
                description: 'Please check the form for errors and try again.',
            });
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
}
</script>

<template>
    <Head title="Add New Employee" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Add New Employee">
        <div class="space-y-8 px-6">
            <div>
                <h2 class="text-lg font-semibold">New Employee</h2>
                <p class="text-sm text-muted-foreground">
                    Fill in the details below to register a new employee under
                    <span class="font-medium text-foreground">{{
                        shop.shop_name
                    }}</span
                    >.
                </p>
            </div>

            <!-- ── Section 1: Identity ────────────────────────────────────── -->
            <div class="space-y-4">
                <p
                    class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Identity
                </p>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="space-y-1">
                        <label class="text-sm font-medium"
                            >Employee ID
                            <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-model="form.employee_id"
                            class="cursor-not-allowed bg-muted font-mono"
                            :class="{ 'border-red-500': errors.employee_id }"
                            disabled
                        />
                        <p
                            v-if="errors.employee_id"
                            class="text-xs text-red-500"
                        >
                            {{ errors.employee_id }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium"
                            >First Name
                            <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-model="form.first_name"
                            :class="{ 'border-red-500': errors.first_name }"
                            placeholder="Prince Juan"
                        />
                        <p
                            v-if="errors.first_name"
                            class="text-xs text-red-500"
                        >
                            {{ errors.first_name }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium"
                            >Last Name
                            <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-model="form.last_name"
                            :class="{ 'border-red-500': errors.last_name }"
                            placeholder="Delacruz"
                        />
                        <p v-if="errors.last_name" class="text-xs text-red-500">
                            {{ errors.last_name }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-sm font-medium"
                            >Email Address
                            <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-model="form.email"
                            type="email"
                            placeholder="employee@example.com"
                            :class="{ 'border-red-500': errors.email }"
                        />
                        <p v-if="errors.email" class="text-xs text-red-500">
                            {{ errors.email }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-medium">Phone</label>
                        <PhilippinePhoneInput
                            v-model="form.phone"
                            :class="{ 'border-red-500': errors.phone }"
                        />
                        <p v-if="errors.phone" class="text-xs text-red-500">
                            {{ errors.phone }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- ── Section 2: Employment ──────────────────────────────────── -->
            <div class="space-y-4">
                <p
                    class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Employment
                </p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="space-y-1">
                        <label class="text-sm font-medium"
                            >Position <span class="text-red-500">*</span></label
                        >
                        <Select v-model="form.position">
                            <SelectTrigger
                                class="w-full"
                                :class="{ 'border-red-500': errors.position }"
                            >
                                <SelectValue placeholder="Select position" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="role in roles"
                                    :key="role"
                                    :value="role"
                                    class="capitalize"
                                >
                                    {{ role }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="errors.position" class="text-xs text-red-500">
                            {{ errors.position }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium"
                            >Employment Type
                            <span class="text-red-500">*</span></label
                        >
                        <Select v-model="form.employment_type">
                            <SelectTrigger
                                class="w-full"
                                :class="{
                                    'border-red-500': errors.employment_type,
                                }"
                            >
                                <SelectValue
                                    placeholder="Select employment type"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="type in employment_types"
                                    :key="type.value"
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p
                            v-if="errors.employment_type"
                            class="text-xs text-red-500"
                        >
                            {{ errors.employment_type }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium">Branch</label>
                        <template v-if="branch_names.length > 0">
                            <Select v-model="form.branch_name">
                                <SelectTrigger
                                    class="w-full"
                                    :class="{
                                        'border-red-500': errors.branch_name,
                                    }"
                                >
                                    <SelectValue placeholder="Select branch" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="__none__"
                                        >— No Branch —</SelectItem
                                    >
                                    <SelectItem
                                        v-for="b in branch_names"
                                        :key="b"
                                        :value="b"
                                        >{{ b }}</SelectItem
                                    >
                                </SelectContent>
                            </Select>
                        </template>
                        <template v-else>
                            <Input
                                v-model="form.branch_name"
                                placeholder="e.g. Main Branch"
                                :class="{
                                    'border-red-500': errors.branch_name,
                                }"
                            />
                        </template>
                        <p
                            v-if="errors.branch_name"
                            class="text-xs text-red-500"
                        >
                            {{ errors.branch_name }}
                        </p>
                    </div>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
                >
                    <div class="space-y-1">
                        <label class="text-sm font-medium"
                            >Hire Date
                            <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-model="form.hire_date"
                            type="date"
                            :class="{ 'border-red-500': errors.hire_date }"
                        />
                        <p v-if="errors.hire_date" class="text-xs text-red-500">
                            {{ errors.hire_date }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium"
                            >Pay Rate <span class="text-red-500">*</span></label
                        >
                        <Input
                            v-model.number="form.pay_rate"
                            type="number"
                            min="0.01"
                            step="0.01"
                            placeholder="Enter amount"
                            :class="{ 'border-red-500': errors.pay_rate }"
                        />
                        <p v-if="errors.pay_rate" class="text-xs text-red-500">
                            {{ errors.pay_rate }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium"
                            >Pay Basis
                            <span class="text-red-500">*</span></label
                        >
                        <Select v-model="form.pay_basis">
                            <SelectTrigger
                                class="w-full"
                                :class="{ 'border-red-500': errors.pay_basis }"
                            >
                                <SelectValue placeholder="Select pay basis" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="basis in pay_bases"
                                    :key="basis.value"
                                    :value="basis.value"
                                >
                                    {{ basis.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="errors.pay_basis" class="text-xs text-red-500">
                            {{ errors.pay_basis }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium"
                            >Status <span class="text-red-500">*</span></label
                        >
                        <Select v-model="form.status">
                            <SelectTrigger
                                :class="{ 'border-red-500': errors.status }"
                            >
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="Active">Active</SelectItem>
                                <SelectItem value="Inactive"
                                    >Inactive</SelectItem
                                >
                            </SelectContent>
                        </Select>
                        <p v-if="errors.status" class="text-xs text-red-500">
                            {{ errors.status }}
                        </p>
                    </div>
                </div>
                <p class="text-xs text-muted-foreground">
                    {{ payBasisHelp }}
                </p>
            </div>

            <!-- ── Section 3: Address ─────────────────────────────────────── -->
            <div class="space-y-4">
                <p
                    class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    Address
                </p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="space-y-1">
                        <label class="text-sm font-medium"
                            >Province <span class="text-red-500">*</span></label
                        >
                        <Select
                            v-model="selectedProvince"
                            :disabled="loadingProvinces"
                        >
                            <SelectTrigger class="w-full">
                                <SelectValue>
                                    <span
                                        v-if="loadingProvinces"
                                        class="flex items-center gap-1.5 text-muted-foreground"
                                    >
                                        <Loader2 class="h-3 w-3 animate-spin" />
                                        Loading...
                                    </span>
                                    <span
                                        v-else-if="!selectedProvince"
                                        class="text-muted-foreground"
                                        >Select province</span
                                    >
                                    <span v-else>{{
                                        provinces.find(
                                            (p) => p.code === selectedProvince,
                                        )?.name
                                    }}</span>
                                </SelectValue>
                            </SelectTrigger>
                            <SelectContent class="max-h-60">
                                <SelectItem
                                    v-for="p in provinces"
                                    :key="p.code"
                                    :value="p.code"
                                    >{{ p.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium"
                            >City / Municipality
                            <span class="text-red-500">*</span></label
                        >
                        <Select
                            v-model="selectedCity"
                            :disabled="!selectedProvince || loadingCities"
                        >
                            <SelectTrigger class="w-full">
                                <SelectValue>
                                    <span
                                        v-if="loadingCities"
                                        class="flex items-center gap-1.5 text-muted-foreground"
                                    >
                                        <Loader2 class="h-3 w-3 animate-spin" />
                                        Loading...
                                    </span>
                                    <span
                                        v-else-if="!selectedCity"
                                        class="text-muted-foreground"
                                        >Select city/municipality</span
                                    >
                                    <span v-else>{{
                                        cities.find(
                                            (c) => c.code === selectedCity,
                                        )?.name
                                    }}</span>
                                </SelectValue>
                            </SelectTrigger>
                            <SelectContent class="max-h-60">
                                <SelectItem
                                    v-for="c in cities"
                                    :key="c.code"
                                    :value="c.code"
                                    >{{ c.name }}</SelectItem
                                >
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-medium"
                            >Barangay <span class="text-red-500">*</span></label
                        >
                        <Select
                            v-model="selectedBarangay"
                            :disabled="!selectedCity || loadingBarangays"
                        >
                            <SelectTrigger class="w-full">
                                <SelectValue>
                                    <span
                                        v-if="loadingBarangays"
                                        class="flex items-center gap-1.5 text-muted-foreground"
                                    >
                                        <Loader2 class="h-3 w-3 animate-spin" />
                                        Loading...
                                    </span>
                                    <span
                                        v-else-if="!selectedBarangay"
                                        class="text-muted-foreground"
                                        >Select barangay</span
                                    >
                                    <span v-else>{{
                                        barangays.find(
                                            (b) => b.code === selectedBarangay,
                                        )?.name
                                    }}</span>
                                </SelectValue>
                            </SelectTrigger>
                            <SelectContent class="max-h-60">
                                <SelectItem
                                    v-for="b in barangays"
                                    :key="b.code"
                                    :value="b.code"
                                    >{{ b.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="space-y-1 sm:col-span-3">
                        <label class="text-sm font-medium"
                            >Street / Block No.</label
                        >
                        <Input
                            v-model="streetInput"
                            placeholder="e.g. 123 Rizal St."
                        />
                    </div>
                </div>

                <p v-if="fullAddress" class="text-xs text-muted-foreground">
                    <span class="font-medium">Full address:</span>
                    {{ fullAddress }}
                </p>
                <p v-if="errors.address" class="text-xs text-red-500">
                    {{ errors.address }}
                </p>
            </div>
            <!-- Section 4: System Access -->
            <div class="space-y-4">
                <p
                    class="text-xs font-semibold tracking-widest text-muted-foreground uppercase"
                >
                    System Access
                </p>

                <div
                    class="flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950/30"
                >
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/50"
                    >
                        <Mail
                            class="h-4 w-4 text-blue-600 dark:text-blue-400"
                        />
                    </div>
                    <div class="space-y-1">
                        <p
                            class="text-sm font-medium text-blue-700 dark:text-blue-300"
                        >
                            A login account will be created automatically
                        </p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">
                            The employee will receive their login credentials
                            (email + temporary password) at the email address
                            provided above. Their temporary password is their
                            last name. They can change it after logging in.
                        </p>
                    </div>
                </div>
            </div>
            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 border-t pt-4">
                <Button
                    type="button"
                    variant="outline"
                    :disabled="isSubmitting"
                    @click="cancel"
                >
                    Cancel
                </Button>
                <Button type="button" :disabled="isSubmitting" @click="submit">
                    <Loader2
                        v-if="isSubmitting"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    {{ isSubmitting ? 'Saving...' : 'Add Employee' }}
                </Button>
            </div>
        </div>
    </ShopLayout>
</template>
