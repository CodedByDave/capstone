<script setup lang="ts">
import {
    DataTable,
    DataTableToolbar,
    IdentityCell,
    type DataTableHeader,
} from '@/components/data-table';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useDataTable } from '@/composables/useDataTable';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import {
    Check,
    Package,
    Plus,
    Shield,
    Trash2,
    UserCog,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

// ─── Types ────────────────────────────────────────────────────────────────────

interface Action {
    key: string;
    label: string;
}

interface Module {
    id: number;
    name: string;
    actions: Action[];
}

interface StaffUser {
    id: number;
    name: string;
    email: string;
    roles: string[];
}

interface RoleItem {
    name: string;
    deletable: boolean;
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    purchasedModules: { name: string; price: number }[];
    staff: StaffUser[];
    permissions: Record<string, Record<string, string[]>>;
    roles: RoleItem[];
}>();

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '#' },
    { title: 'Roles & Permissions', href: '/shop/permission' },
];

// ─── Action map ───────────────────────────────────────────────────────────────

const moduleActionMap: Record<string, Action[]> = {
    HRM: [
        { key: 'view', label: 'View' },
        { key: 'create', label: 'Create' },
        { key: 'update', label: 'Update' },
        { key: 'archive', label: 'Archive' },
        { key: 'import', label: 'Import' },
    ],
    Operations: [
        { key: 'view', label: 'View' },
        { key: 'create', label: 'Create' },
        { key: 'manage', label: 'Manage' },
    ],
    'Inventory Management': [
        { key: 'view', label: 'View' },
        { key: 'create', label: 'Create' },
        { key: 'update', label: 'Update' },
        { key: 'archive', label: 'Archive' },
    ],
    'Finance Management': [
        { key: 'view', label: 'View' },
        { key: 'create', label: 'Create' },
        { key: 'update', label: 'Update' },
        { key: 'archive', label: 'Archive' },
    ],
    'Reports & Analytics': [
        { key: 'view', label: 'View' },
        { key: 'export', label: 'Export' },
    ],
    // View the board; add riders; assign riders, book couriers, update statuses
    // and upload proof; remove deliveries, riders and photos.
    Logistics: [
        { key: 'view', label: 'View' },
        { key: 'create', label: 'Add Riders' },
        { key: 'update', label: 'Assign & Update' },
        { key: 'archive', label: 'Delete' },
    ],
};

// ─── Modules ──────────────────────────────────────────────────────────────────

const purchasedModules = computed<Module[]>(() =>
    props.purchasedModules.map((m, i) => ({
        id: i + 1,
        name: m.name,
        actions: moduleActionMap[m.name] ?? [{ key: 'view', label: 'View' }],
    })),
);

// ─── Roles ────────────────────────────────────────────────────────────────────

const roles = ref<RoleItem[]>(props.roles ?? []);
const selectedRole = ref('owner');
const isOwnerRole = computed(() => selectedRole.value === 'owner');
const nonOwnerRoles = computed(() =>
    roles.value.filter((r) => r.name !== 'owner'),
);
const showNewRoleInput = ref(false);
const newRoleName = ref('');
const roleError = ref('');

async function createRole() {
    const name = newRoleName.value.trim().toLowerCase();

    if (!name) {
        roleError.value = 'Role name is required.';
        return;
    }

    if (roles.value.find((r) => r.name === name)) {
        roleError.value = 'Role already exists.';
        return;
    }

    try {
        const res = await axios.post('/shop/permission/roles', { name });
        roles.value.push(res.data);
        selectedRole.value = res.data.name;
        newRoleName.value = '';
        roleError.value = '';
        showNewRoleInput.value = false;
    } catch (err: any) {
        roleError.value = err.response?.data?.error ?? 'Failed to create role.';
    }
}

async function deleteRole(roleName: string) {
    try {
        await axios.delete(`/shop/permission/roles/${roleName}`);
        roles.value = roles.value.filter((r) => r.name !== roleName);

        // Remove deleted role from all staff
        staffList.value.forEach((u) => {
            u.roles = u.roles.filter((r) => r !== roleName);
            if (u.roles.length === 0) u.roles = ['staff'];
        });

        if (selectedRole.value === roleName) selectedRole.value = 'owner';
    } catch {
        alert('Failed to delete role.');
    }
}

// ─── Permissions ──────────────────────────────────────────────────────────────

const permissionsState = ref<Record<string, Record<string, string[]>>>(
    JSON.parse(JSON.stringify(props.permissions ?? {})),
);

function hasAction(
    role: string,
    moduleName: string,
    actionKey: string,
): boolean {
    if (role === 'owner') return true;
    return (
        permissionsState.value[role]?.[moduleName]?.includes(actionKey) ?? false
    );
}

const savingAction = ref<string | null>(null);

async function toggleAction(moduleName: string, actionKey: string) {
    if (isOwnerRole.value) return;

    const role = selectedRole.value;

    if (!permissionsState.value[role]) permissionsState.value[role] = {};
    if (!permissionsState.value[role][moduleName])
        permissionsState.value[role][moduleName] = [];

    const actions = permissionsState.value[role][moduleName];
    const isRemoving = actions.includes(actionKey);

    // If adding a non-view action, auto-add 'view' too
    if (!isRemoving && actionKey !== 'view' && !actions.includes('view')) {
        await saveAction(moduleName, 'view');
    }

    await saveAction(moduleName, actionKey);

    // If removing an action and no non-view actions remain, auto-remove 'view'
    if (isRemoving && actionKey !== 'view') {
        const remaining = actions.filter(
            (a) => a !== actionKey && a !== 'view',
        );
        if (remaining.length === 0) {
            await saveAction(moduleName, 'view');
        }
    }
}

async function saveAction(moduleName: string, actionKey: string) {
    const role = selectedRole.value;
    const key = `${role}-${moduleName}-${actionKey}`;
    const actions = permissionsState.value[role][moduleName];
    const idx = actions.indexOf(actionKey);

    if (idx >= 0) actions.splice(idx, 1);
    else actions.push(actionKey);

    savingAction.value = key;

    try {
        await axios.post('/shop/permission/update', {
            role: role,
            module: moduleName,
            action: actionKey,
        });
    } catch {
        // Revert on failure
        if (idx >= 0) actions.push(actionKey);
        else actions.splice(actions.indexOf(actionKey), 1);
        alert('Failed to save permission.');
    } finally {
        savingAction.value = null;
    }
}

// ─── Staff ────────────────────────────────────────────────────────────────────

const staffList = ref<StaffUser[]>(props.staff ?? []);

const {
    search: searchQuery,
    options: staffOptions,
    pageItems: staffPage,
    total: staffTotal,
    isFiltered: staffFiltered,
    reset: resetStaff,
} = useDataTable(staffList, {
    searchFields: (user) => [user.name, user.email],
    sortBy: 'name',
});

const staffHeaders: DataTableHeader[] = [
    { text: 'Name', value: 'name', sortable: true, width: 220 },
    { text: 'Email', value: 'email', sortable: true },
    { text: 'Current roles', value: 'roles' },
    { text: 'Assign roles', value: 'assign' },
];

function userHasRole(user: StaffUser, role: string): boolean {
    return user.roles.includes(role);
}

async function toggleUserRole(employeeId: number, role: string) {
    const user = staffList.value.find((u) => u.id === employeeId);
    if (!user) return;

    try {
        const res = await axios.post(
            `/shop/permission/employee/${employeeId}/toggle-role`,
            { role },
        );
        user.roles = res.data.roles;
    } catch (err: any) {
        alert(err.response?.data?.error ?? 'Failed to update role.');
    }
}

// ─── UI Helpers ───────────────────────────────────────────────────────────────

const activeTab = ref<'roles' | 'users'>('roles');

const moduleColors: Record<string, string> = {
    'Employee Management': 'bg-blue-100 text-blue-700',
    'Inventory Management': 'bg-teal-100 text-teal-700',
    'Order Management': 'bg-orange-100 text-orange-700',
    'Services & Pricing': 'bg-yellow-100 text-yellow-700',
    'Reports & Analytics': 'bg-green-100 text-green-700',
    default: 'bg-gray-100 text-gray-700',
};

function moduleColor(name: string) {
    return moduleColors[name] ?? moduleColors['default'];
}

function roleColor(role: string) {
    const map: Record<string, string> = {
        owner: 'bg-green-100 text-green-700',
        manager: 'bg-blue-100 text-blue-700',
        cashier: 'bg-orange-100 text-orange-700',
        washer: 'bg-purple-100 text-purple-700',
        staff: 'bg-gray-100 text-gray-700',
    };
    return map[role] ?? 'bg-indigo-100 text-indigo-700';
}
</script>

<template>
    <Head title="Roles & Permissions" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Roles & Permissions">
        <div class="space-y-6 px-6">
            <!-- Header -->
            <div class="flex items-center gap-2">
                <Shield class="h-5 w-5 text-muted-foreground" />
                <p class="text-sm text-muted-foreground">
                    Control module access per role. Permissions are saved
                    automatically.
                </p>
            </div>

            <!-- No modules fallback -->
            <div
                v-if="purchasedModules.length === 0"
                class="py-16 text-center text-sm text-muted-foreground"
            >
                <Package class="mx-auto mb-3 h-10 w-10 opacity-30" />
                No modules purchased yet.
            </div>

            <template v-else>
                <!-- Tabs -->
                <div class="flex gap-1 border-b">
                    <button
                        v-for="tab in [
                            {
                                key: 'roles',
                                label: 'Roles & Permissions',
                                icon: Shield,
                            },
                            {
                                key: 'users',
                                label: 'Staff Assignment',
                                icon: UserCog,
                            },
                        ]"
                        :key="tab.key"
                        @click="activeTab = tab.key as any"
                        class="flex items-center gap-2 border-b-2 px-4 py-2 text-sm font-medium transition-colors"
                        :class="
                            activeTab === tab.key
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-muted-foreground hover:text-foreground'
                        "
                    >
                        <component :is="tab.icon" class="h-4 w-4" />
                        {{ tab.label }}
                    </button>
                </div>

                <!-- ══ ROLES TAB ══ -->
                <div
                    v-if="activeTab === 'roles'"
                    class="grid grid-cols-1 gap-6 md:grid-cols-3"
                >
                    <!-- Role list -->
                    <Card class="md:col-span-1">
                        <CardHeader class="pb-3">
                            <div class="flex items-center justify-between">
                                <CardTitle
                                    class="text-sm font-semibold tracking-widest text-muted-foreground uppercase"
                                >
                                    Roles
                                </CardTitle>
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    @click="
                                        showNewRoleInput = !showNewRoleInput
                                    "
                                >
                                    <Plus class="h-4 w-4" />
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <!-- New role input -->
                            <div v-if="showNewRoleInput" class="mb-3 space-y-1">
                                <div class="flex gap-2">
                                    <Input
                                        v-model="newRoleName"
                                        placeholder="e.g. supervisor"
                                        class="h-8 text-sm"
                                        @keyup.enter="createRole"
                                    />
                                    <Button
                                        size="sm"
                                        class="h-8 bg-blue-500 hover:bg-blue-700"
                                        @click="createRole"
                                    >
                                        <Check class="h-3 w-3" />
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        class="h-8"
                                        @click="
                                            showNewRoleInput = false;
                                            newRoleName = '';
                                            roleError = '';
                                        "
                                    >
                                        <X class="h-3 w-3" />
                                    </Button>
                                </div>
                                <p
                                    v-if="roleError"
                                    class="text-xs text-red-500"
                                >
                                    {{ roleError }}
                                </p>
                            </div>

                            <!-- Role items -->
                            <div
                                v-for="role in roles"
                                :key="role.name"
                                @click="selectedRole = role.name"
                                class="flex cursor-pointer items-center justify-between rounded-lg border p-3 transition-colors"
                                :class="
                                    selectedRole === role.name
                                        ? 'border-blue-200 bg-blue-50'
                                        : 'border-transparent hover:bg-muted'
                                "
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-semibold capitalize"
                                        :class="roleColor(role.name)"
                                    >
                                        {{ role.name }}
                                    </span>
                                    <span
                                        v-if="role.name === 'owner'"
                                        class="text-xs text-muted-foreground"
                                    >
                                        Full access
                                    </span>
                                </div>
                                <Button
                                    v-if="role.deletable"
                                    size="sm"
                                    variant="ghost"
                                    class="h-7 w-7 p-0 text-red-400 hover:bg-red-50 hover:text-red-600"
                                    @click.stop="deleteRole(role.name)"
                                >
                                    <Trash2 class="h-3 w-3" />
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Permission matrix -->
                    <Card class="md:col-span-2">
                        <CardHeader class="pb-3">
                            <CardTitle
                                class="text-sm font-semibold tracking-widest text-muted-foreground uppercase"
                            >
                                Module Access —
                                <span class="ml-1 text-foreground capitalize">{{
                                    selectedRole
                                }}</span>
                                <span
                                    v-if="isOwnerRole"
                                    class="ml-2 text-xs font-normal tracking-normal text-muted-foreground normal-case"
                                >
                                    (full access, not editable)
                                </span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div
                                v-for="module in purchasedModules"
                                :key="module.id"
                            >
                                <!-- Module header -->
                                <div class="mb-3 flex items-center gap-2">
                                    <Package
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-semibold tracking-widest uppercase"
                                        :class="moduleColor(module.name)"
                                    >
                                        {{ module.name }}
                                    </span>
                                </div>

                                <!-- Actions grid -->
                                <div
                                    class="grid grid-cols-2 gap-2 sm:grid-cols-4"
                                >
                                    <button
                                        v-for="action in module.actions"
                                        :key="action.key"
                                        @click="
                                            toggleAction(
                                                module.name,
                                                action.key,
                                            )
                                        "
                                        :disabled="
                                            isOwnerRole ||
                                            savingAction ===
                                                `${selectedRole}-${module.name}-${action.key}`
                                        "
                                        class="flex items-center gap-2 rounded-lg border px-3 py-2 text-left text-sm transition-colors"
                                        :class="[
                                            hasAction(
                                                selectedRole,
                                                module.name,
                                                action.key,
                                            )
                                                ? 'border-blue-300 bg-blue-50 text-blue-700'
                                                : 'border-border text-muted-foreground hover:bg-muted',
                                            isOwnerRole
                                                ? 'cursor-not-allowed opacity-80'
                                                : 'cursor-pointer',
                                        ]"
                                    >
                                        <Check
                                            v-if="
                                                hasAction(
                                                    selectedRole,
                                                    module.name,
                                                    action.key,
                                                )
                                            "
                                            class="h-3 w-3 flex-shrink-0"
                                        />
                                        <div
                                            v-else
                                            class="h-3 w-3 flex-shrink-0 rounded-full border border-current"
                                        />
                                        {{ action.label }}
                                    </button>
                                </div>

                                <div class="mt-4 border-b last:hidden" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- ══ STAFF ASSIGNMENT TAB ══ -->
                <div v-if="activeTab === 'users'">
                    <!-- No staff fallback -->
                    <div
                        v-if="staffList.length === 0"
                        class="rounded-[14px] border bg-card py-12 text-center text-sm text-muted-foreground"
                    >
                        <UserCog class="mx-auto mb-3 h-10 w-10 opacity-30" />
                        No staff found for this shop.
                    </div>

                    <DataTable
                        v-else
                        v-model:options="staffOptions"
                        :headers="staffHeaders"
                        :items="staffPage"
                        :total="staffTotal"
                        noun="staff"
                        :empty-icon="UserCog"
                        empty-message="No staff matched your search."
                        :resettable="staffFiltered"
                        :min-width="820"
                        @reset="resetStaff"
                    >
                        <template #toolbar>
                            <div class="space-y-3">
                                <div>
                                    <h2
                                        class="flex items-center gap-2 text-sm font-semibold"
                                    >
                                        <UserCog class="h-4 w-4" /> Staff Role
                                        Assignment
                                    </h2>
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Staff can hold multiple roles. Toggle
                                        roles on/off per employee.
                                    </p>
                                </div>
                                <DataTableToolbar
                                    v-model:search="searchQuery"
                                    search-placeholder="Search staff..."
                                    search-label="Search staff"
                                />
                            </div>
                        </template>

                        <template #item-name="user">
                            <IdentityCell :name="user.name" />
                        </template>

                        <template #item-email="user">
                            <span class="text-xs text-muted-foreground">{{
                                user.email
                            }}</span>
                        </template>

                        <!-- Current roles -->
                        <template #item-roles="user">
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="role in user.roles"
                                    :key="role"
                                    class="rounded-full px-2 py-0.5 text-xs font-semibold capitalize"
                                    :class="roleColor(role)"
                                >
                                    {{ role }}
                                </span>
                            </div>
                        </template>

                        <!-- Toggle roles -->
                        <template #item-assign="user">
                            <div class="flex flex-wrap gap-1">
                                <button
                                    v-for="role in nonOwnerRoles"
                                    :key="role.name"
                                    type="button"
                                    class="rounded-full border px-2 py-0.5 text-xs font-medium capitalize transition-colors"
                                    :class="
                                        userHasRole(user, role.name)
                                            ? 'border-blue-300 bg-blue-50 text-blue-700'
                                            : 'border-border text-muted-foreground hover:bg-muted'
                                    "
                                    :aria-pressed="userHasRole(user, role.name)"
                                    :aria-label="`${userHasRole(user, role.name) ? 'Remove' : 'Assign'} ${role.name} role ${userHasRole(user, role.name) ? 'from' : 'to'} ${user.name}`"
                                    @click="toggleUserRole(user.id, role.name)"
                                >
                                    <Check
                                        v-if="userHasRole(user, role.name)"
                                        class="mr-0.5 inline h-2.5 w-2.5"
                                    />
                                    {{ role.name }}
                                </button>
                            </div>
                        </template>
                    </DataTable>
                </div>
            </template>
        </div>
    </ShopLayout>
</template>
