<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { Loader2, Plus, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Role {
    public_id: string;
    name: string;
    slug: string;
    description: string | null;
    is_system: boolean;
    permissions: string[];
}

interface PermissionGroup {
    name: string;
    permissions: { key: string; label: string }[];
}

const props = defineProps<{
    roles: Role[];
    permissionGroups: PermissionGroup[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    {
        title: 'Roles & Permissions',
        href: '/admin/settings/roles-permissions',
    },
];

const roleList = ref<Role[]>(
    props.roles.map((role) => ({
        ...role,
        permissions: [...role.permissions],
    })),
);
const showCreate = ref(false);
const showManage = ref(false);
const newRoleName = ref('');
const newRoleDescription = ref('');
const formError = ref('');
const creating = ref(false);
const saving = ref<string | null>(null);
const deleting = ref<string | null>(null);

const customRoles = computed(() =>
    roleList.value.filter((role) => !role.is_system),
);

const hasPermission = (role: Role, permission: string) =>
    role.permissions.includes(permission);

async function togglePermission(role: Role, permission: string) {
    if (role.slug === 'super_admin') return;

    const key = `${role.public_id}:${permission}`;
    if (saving.value === key) return;
    saving.value = key;

    try {
        const { data } = await axios.patch(
            `/admin/settings/roles-permissions/roles/${role.public_id}/permissions`,
            { permission },
        );

        if (data.enabled && !role.permissions.includes(permission)) {
            role.permissions.push(permission);
        } else if (!data.enabled) {
            role.permissions = role.permissions.filter(
                (item) => item !== permission,
            );
        }
    } finally {
        saving.value = null;
    }
}

async function createRole() {
    formError.value = '';
    if (!newRoleName.value.trim()) {
        formError.value = 'Role name is required.';
        return;
    }

    creating.value = true;
    try {
        const { data } = await axios.post(
            '/admin/settings/roles-permissions/roles',
            {
                name: newRoleName.value.trim(),
                description: newRoleDescription.value.trim() || null,
            },
        );
        roleList.value.push(data);
        closeCreate();
    } catch (error: any) {
        formError.value =
            error.response?.data?.message ?? 'Unable to create the role.';
    } finally {
        creating.value = false;
    }
}

function closeCreate() {
    showCreate.value = false;
    newRoleName.value = '';
    newRoleDescription.value = '';
    formError.value = '';
}

async function deleteRole(role: Role) {
    if (role.is_system || !confirm(`Delete the ${role.name} role?`)) return;

    deleting.value = role.public_id;
    try {
        await axios.delete(
            `/admin/settings/roles-permissions/roles/${role.public_id}`,
        );
        roleList.value = roleList.value.filter(
            (item) => item.public_id !== role.public_id,
        );
        if (customRoles.value.length === 0) showManage.value = false;
    } finally {
        deleting.value = null;
    }
}
</script>

<template>
    <Head title="Roles & Permissions" />

    <AdminLayout :breadcrumbs="breadcrumbs" title="Roles & Permissions">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 px-1 sm:flex-row sm:items-start sm:justify-between"
            >
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">
                        Roles & Permissions
                    </h2>
                    <p class="mt-1 max-w-3xl text-sm text-muted-foreground">
                        Control which capabilities are available to each
                        platform role.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button
                        v-if="customRoles.length"
                        variant="outline"
                        @click="showManage = !showManage"
                    >
                        {{ showManage ? 'Close role manager' : 'Manage roles' }}
                    </Button>
                    <Button v-if="!showCreate" @click="showCreate = true">
                        <Plus class="mr-2 h-4 w-4" /> Add role
                    </Button>
                </div>
            </div>

            <form
                v-if="showCreate"
                class="grid gap-3 rounded-xl border bg-card p-4 shadow-sm md:grid-cols-[240px_1fr_auto]"
                @submit.prevent="createRole"
            >
                <div>
                    <label class="mb-1.5 block text-xs font-medium">
                        Role name
                    </label>
                    <Input
                        v-model="newRoleName"
                        maxlength="50"
                        placeholder="e.g. Support Agent"
                        autofocus
                    />
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium">
                        Description
                    </label>
                    <Input
                        v-model="newRoleDescription"
                        maxlength="255"
                        placeholder="Describe what this role is for"
                    />
                </div>
                <div class="flex items-end gap-2">
                    <Button type="submit" :disabled="creating">
                        <Loader2
                            v-if="creating"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        Create role
                    </Button>
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        aria-label="Cancel"
                        @click="closeCreate"
                    >
                        <X class="h-4 w-4" />
                    </Button>
                </div>
                <p
                    v-if="formError"
                    class="text-sm text-destructive md:col-span-3"
                >
                    {{ formError }}
                </p>
            </form>

            <section
                v-if="showManage"
                class="overflow-hidden rounded-xl border bg-card shadow-sm"
            >
                <div class="border-b px-4 py-3">
                    <h3 class="font-semibold">Custom roles</h3>
                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Delete roles that are no longer needed. System roles
                        cannot be removed.
                    </p>
                </div>
                <div class="divide-y">
                    <div
                        v-for="role in customRoles"
                        :key="role.public_id"
                        class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <p class="text-sm font-medium">{{ role.name }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ role.description || 'No description' }}
                            </p>
                        </div>
                        <Button
                            variant="outline"
                            size="sm"
                            class="text-destructive hover:text-destructive"
                            :disabled="deleting === role.public_id"
                            @click="deleteRole(role)"
                        >
                            <Loader2
                                v-if="deleting === role.public_id"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            Delete
                        </Button>
                    </div>
                </div>
            </section>

            <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-sm">
                        <thead class="border-b bg-muted/40">
                            <tr>
                                <th
                                    class="sticky left-0 z-10 min-w-56 bg-muted px-5 py-4 text-left font-semibold"
                                >
                                    Permission
                                </th>
                                <th
                                    v-for="role in roleList"
                                    :key="role.public_id"
                                    class="min-w-40 px-4 py-4 text-center align-top"
                                >
                                    <p class="font-semibold">
                                        {{ role.name }}
                                    </p>
                                    <p
                                        v-if="role.is_system"
                                        class="mt-1 text-[10px] font-medium tracking-wide text-muted-foreground uppercase"
                                    >
                                        System role
                                    </p>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template
                                v-for="group in permissionGroups"
                                :key="group.name"
                            >
                                <tr class="border-y bg-muted/20">
                                    <th
                                        :colspan="roleList.length + 1"
                                        class="px-5 py-2 text-left text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                                    >
                                        {{ group.name }}
                                    </th>
                                </tr>
                                <tr
                                    v-for="permission in group.permissions"
                                    :key="permission.key"
                                    class="border-b transition-colors last:border-0 hover:bg-muted/20"
                                >
                                    <td
                                        class="sticky left-0 bg-card px-5 py-3.5 font-medium"
                                    >
                                        {{ permission.label }}
                                    </td>
                                    <td
                                        v-for="role in roleList"
                                        :key="role.public_id"
                                        class="px-4 py-3.5 text-center"
                                    >
                                        <div class="flex justify-center">
                                            <Loader2
                                                v-if="
                                                    saving ===
                                                    `${role.public_id}:${permission.key}`
                                                "
                                                class="h-4 w-4 animate-spin text-muted-foreground"
                                            />
                                            <Checkbox
                                                v-else
                                                :model-value="
                                                    hasPermission(
                                                        role,
                                                        permission.key,
                                                    )
                                                "
                                                :disabled="
                                                    role.slug === 'super_admin'
                                                "
                                                :aria-label="`${permission.label} for ${role.name}`"
                                                @update:model-value="
                                                    togglePermission(
                                                        role,
                                                        permission.key,
                                                    )
                                                "
                                            />
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="px-1 text-xs text-muted-foreground">
                Super Admin remains locked with full access. Shop-level staff
                permissions can still be refined by each shop owner.
            </p>
        </div>
    </AdminLayout>
</template>
