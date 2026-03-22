<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Shield, Plus, Trash2, Check, X, Search, Package, UserCog } from 'lucide-vue-next'
import axios from 'axios'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Action {
    key: string
    label: string
}

interface Module {
    id: number
    name: string
    actions: Action[]
}

interface StaffUser {
    id:    number
    name:  string
    email: string
    roles: string[]
}

interface RoleItem {
    name:      string
    deletable: boolean
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    purchasedModules: { name: string; price: number }[]
    staff:            StaffUser[]
    permissions:      Record<string, Record<string, string[]>>
    roles:            RoleItem[]
}>()

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Settings', href: '#' },
    { title: 'Roles & Permissions', href: '/shop/permission' },
]

// ─── Action map ───────────────────────────────────────────────────────────────

const moduleActionMap: Record<string, Action[]> = {
    'Employee Management': [
        { key: 'view',    label: 'View'    },
        { key: 'create',  label: 'Create'  },
        { key: 'update',  label: 'Update'  },
        { key: 'archive', label: 'Archive' },
    ],
    'Inventory Management': [
        { key: 'view',    label: 'View'    },
        { key: 'create',  label: 'Create'  },
        { key: 'update',  label: 'Update'  },
        { key: 'archive', label: 'Archive' },
    ],
    'Order Management': [
        { key: 'view',   label: 'View'   },
        { key: 'create', label: 'Create' },
        { key: 'manage', label: 'Manage' },
    ],
    'Services & Pricing': [
        { key: 'view',    label: 'View'    },
        { key: 'create',  label: 'Create'  },
        { key: 'update',  label: 'Update'  },
        { key: 'archive', label: 'Archive' },
    ],
    'Reports & Analytics': [
        { key: 'view',   label: 'View'   },
        { key: 'export', label: 'Export' },
    ],
}

// ─── Modules ──────────────────────────────────────────────────────────────────

const purchasedModules = computed<Module[]>(() =>
    props.purchasedModules.map((m, i) => ({
        id:      i + 1,
        name:    m.name,
        actions: moduleActionMap[m.name] ?? [{ key: 'view', label: 'View' }],
    }))
)

// ─── Roles ────────────────────────────────────────────────────────────────────

const roles            = ref<RoleItem[]>(props.roles ?? [])
const selectedRole     = ref('owner')
const isOwnerRole      = computed(() => selectedRole.value === 'owner')
const nonOwnerRoles    = computed(() => roles.value.filter(r => r.name !== 'owner'))
const showNewRoleInput = ref(false)
const newRoleName      = ref('')
const roleError        = ref('')

async function createRole() {
    const name = newRoleName.value.trim().toLowerCase()

    if (!name) {
        roleError.value = 'Role name is required.'
        return
    }

    if (roles.value.find(r => r.name === name)) {
        roleError.value = 'Role already exists.'
        return
    }

    try {
        const res = await axios.post('/shop/permission/roles', { name })
        roles.value.push(res.data)
        selectedRole.value     = res.data.name
        newRoleName.value      = ''
        roleError.value        = ''
        showNewRoleInput.value = false
    } catch (err: any) {
        roleError.value = err.response?.data?.error ?? 'Failed to create role.'
    }
}

async function deleteRole(roleName: string) {
    try {
        await axios.delete(`/shop/permission/roles/${roleName}`)
        roles.value = roles.value.filter(r => r.name !== roleName)

        // Remove deleted role from all staff
        staffList.value.forEach(u => {
            u.roles = u.roles.filter(r => r !== roleName)
            if (u.roles.length === 0) u.roles = ['staff']
        })

        if (selectedRole.value === roleName) selectedRole.value = 'owner'
    } catch {
        alert('Failed to delete role.')
    }
}

// ─── Permissions ──────────────────────────────────────────────────────────────

const permissionsState = ref<Record<string, Record<string, string[]>>>(
    JSON.parse(JSON.stringify(props.permissions ?? {}))
)

function hasAction(role: string, moduleName: string, actionKey: string): boolean {
    if (role === 'owner') return true
    return permissionsState.value[role]?.[moduleName]?.includes(actionKey) ?? false
}

const savingAction = ref<string | null>(null)

async function toggleAction(moduleName: string, actionKey: string) {
    if (isOwnerRole.value) return

    const role = selectedRole.value

    if (!permissionsState.value[role])             permissionsState.value[role] = {}
    if (!permissionsState.value[role][moduleName]) permissionsState.value[role][moduleName] = []

    const actions = permissionsState.value[role][moduleName]
    const isRemoving = actions.includes(actionKey)

    // If adding a non-view action, auto-add 'view' too
    if (!isRemoving && actionKey !== 'view' && !actions.includes('view')) {
        await saveAction(moduleName, 'view')
    }

    await saveAction(moduleName, actionKey)

    // If removing an action and no non-view actions remain, auto-remove 'view'
    if (isRemoving && actionKey !== 'view') {
        const remaining = actions.filter(a => a !== actionKey && a !== 'view')
        if (remaining.length === 0) {
            await saveAction(moduleName, 'view')
        }
    }
}

async function saveAction(moduleName: string, actionKey: string) {
    const role    = selectedRole.value
    const key     = `${role}-${moduleName}-${actionKey}`
    const actions = permissionsState.value[role][moduleName]
    const idx     = actions.indexOf(actionKey)

    if (idx >= 0) actions.splice(idx, 1)
    else actions.push(actionKey)

    savingAction.value = key

    try {
        await axios.post('/shop/permission/update', {
            role:   role,
            module: moduleName,
            action: actionKey,
        })
    } catch {
        // Revert on failure
        if (idx >= 0) actions.push(actionKey)
        else actions.splice(actions.indexOf(actionKey), 1)
        alert('Failed to save permission.')
    } finally {
        savingAction.value = null
    }
}

// ─── Staff ────────────────────────────────────────────────────────────────────

const staffList   = ref<StaffUser[]>(props.staff ?? [])
const searchQuery = ref('')

const filteredStaff = computed(() =>
    staffList.value.filter(u =>
        u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        u.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
)

function userHasRole(user: StaffUser, role: string): boolean {
    return user.roles.includes(role)
}

async function toggleUserRole(employeeId: number, role: string) {
    const user = staffList.value.find(u => u.id === employeeId)
    if (!user) return

    try {
        const res  = await axios.post(`/shop/permission/employee/${employeeId}/toggle-role`, { role })
        user.roles = res.data.roles
    } catch (err: any) {
        alert(err.response?.data?.error ?? 'Failed to update role.')
    }
}

// ─── UI Helpers ───────────────────────────────────────────────────────────────

const activeTab = ref<'roles' | 'users'>('roles')

const moduleColors: Record<string, string> = {
    'Employee Management':  'bg-blue-100 text-blue-700',
    'Inventory Management': 'bg-teal-100 text-teal-700',
    'Order Management':     'bg-orange-100 text-orange-700',
    'Services & Pricing':   'bg-yellow-100 text-yellow-700',
    'Reports & Analytics':  'bg-green-100 text-green-700',
    default:                'bg-gray-100 text-gray-700',
}

function moduleColor(name: string) {
    return moduleColors[name] ?? moduleColors['default']
}

function roleColor(role: string) {
    const map: Record<string, string> = {
        owner:   'bg-green-100 text-green-700',
        manager: 'bg-blue-100 text-blue-700',
        cashier: 'bg-orange-100 text-orange-700',
        washer:  'bg-purple-100 text-purple-700',
        staff:   'bg-gray-100 text-gray-700',
    }
    return map[role] ?? 'bg-indigo-100 text-indigo-700'
}
</script>

<template>
    <Head title="Roles & Permissions" />
    <ShopLayout :breadcrumbs="breadcrumbs" title="Roles & Permissions">
        <div class="px-6 space-y-6">

            <!-- Header -->
            <div class="flex items-center gap-2">
                <Shield class="h-5 w-5 text-muted-foreground" />
                <p class="text-sm text-muted-foreground">
                    Control module access per role. Permissions are saved automatically.
                </p>
            </div>

            <!-- No modules fallback -->
            <div v-if="purchasedModules.length === 0"
                class="text-center py-16 text-muted-foreground text-sm">
                <Package class="h-10 w-10 mx-auto mb-3 opacity-30" />
                No modules purchased yet.
            </div>

            <template v-else>

                <!-- Tabs -->
                <div class="flex gap-1 border-b">
                    <button
                        v-for="tab in [
                            { key: 'roles', label: 'Roles & Permissions', icon: Shield  },
                            { key: 'users', label: 'Staff Assignment',    icon: UserCog },
                        ]"
                        :key="tab.key"
                        @click="activeTab = tab.key as any"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium border-b-2 transition-colors"
                        :class="activeTab === tab.key
                            ? 'border-blue-500 text-blue-600'
                            : 'border-transparent text-muted-foreground hover:text-foreground'"
                    >
                        <component :is="tab.icon" class="h-4 w-4" />
                        {{ tab.label }}
                    </button>
                </div>

                <!-- ══ ROLES TAB ══ -->
                <div v-if="activeTab === 'roles'" class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Role list -->
                    <Card class="md:col-span-1">
                        <CardHeader class="pb-3">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-sm font-semibold uppercase tracking-widest text-muted-foreground">
                                    Roles
                                </CardTitle>
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    @click="showNewRoleInput = !showNewRoleInput"
                                >
                                    <Plus class="h-4 w-4" />
                                </Button>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-2">

                            <!-- New role input -->
                            <div v-if="showNewRoleInput" class="space-y-1 mb-3">
                                <div class="flex gap-2">
                                    <Input
                                        v-model="newRoleName"
                                        placeholder="e.g. supervisor"
                                        class="h-8 text-sm"
                                        @keyup.enter="createRole"
                                    />
                                    <Button
                                        size="sm"
                                        class="bg-blue-500 hover:bg-blue-700 h-8"
                                        @click="createRole"
                                    >
                                        <Check class="h-3 w-3" />
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        class="h-8"
                                        @click="showNewRoleInput = false; newRoleName = ''; roleError = ''"
                                    >
                                        <X class="h-3 w-3" />
                                    </Button>
                                </div>
                                <p v-if="roleError" class="text-xs text-red-500">{{ roleError }}</p>
                            </div>

                            <!-- Role items -->
                            <div
                                v-for="role in roles"
                                :key="role.name"
                                @click="selectedRole = role.name"
                                class="flex items-center justify-between p-3 rounded-lg cursor-pointer transition-colors border"
                                :class="selectedRole === role.name
                                    ? 'bg-blue-50 border-blue-200'
                                    : 'border-transparent hover:bg-muted'"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-0.5 text-xs font-semibold rounded-full capitalize"
                                        :class="roleColor(role.name)"
                                    >
                                        {{ role.name }}
                                    </span>
                                    <span v-if="role.name === 'owner'"
                                        class="text-xs text-muted-foreground">
                                        Full access
                                    </span>
                                </div>
                                <Button
                                    v-if="role.deletable"
                                    size="sm"
                                    variant="ghost"
                                    class="h-7 w-7 p-0 text-red-400 hover:text-red-600 hover:bg-red-50"
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
                            <CardTitle class="text-sm font-semibold uppercase tracking-widest text-muted-foreground">
                                Module Access —
                                <span class="text-foreground capitalize ml-1">{{ selectedRole }}</span>
                                <span v-if="isOwnerRole"
                                    class="ml-2 text-xs font-normal text-muted-foreground normal-case tracking-normal">
                                    (full access, not editable)
                                </span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div v-for="module in purchasedModules" :key="module.id">

                                <!-- Module header -->
                                <div class="flex items-center gap-2 mb-3">
                                    <Package class="h-4 w-4 text-muted-foreground" />
                                    <span
                                        class="text-xs font-semibold uppercase tracking-widest px-2 py-0.5 rounded-full"
                                        :class="moduleColor(module.name)"
                                    >
                                        {{ module.name }}
                                    </span>
                                </div>

                                <!-- Actions grid -->
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <button
                                        v-for="action in module.actions"
                                        :key="action.key"
                                        @click="toggleAction(module.name, action.key)"
                                        :disabled="isOwnerRole || savingAction === `${selectedRole}-${module.name}-${action.key}`"
                                        class="flex items-center gap-2 px-3 py-2 rounded-lg border text-sm transition-colors text-left"
                                        :class="[
                                            hasAction(selectedRole, module.name, action.key)
                                                ? 'bg-blue-50 border-blue-300 text-blue-700'
                                                : 'border-border hover:bg-muted text-muted-foreground',
                                            isOwnerRole
                                                ? 'cursor-not-allowed opacity-80'
                                                : 'cursor-pointer'
                                        ]"
                                    >
                                        <Check
                                            v-if="hasAction(selectedRole, module.name, action.key)"
                                            class="h-3 w-3 flex-shrink-0"
                                        />
                                        <div
                                            v-else
                                            class="h-3 w-3 rounded-full border border-current flex-shrink-0"
                                        />
                                        {{ action.label }}
                                    </button>
                                </div>

                                <div class="border-b mt-4 last:hidden" />
                            </div>
                        </CardContent>
                    </Card>

                </div>

                <!-- ══ STAFF ASSIGNMENT TAB ══ -->
                <div v-if="activeTab === 'users'">
                    <Card>
                        <CardHeader class="pb-3">
                            <div class="flex items-center justify-between">
                                <CardTitle class="text-sm font-semibold uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                                    <UserCog class="h-4 w-4" /> Staff Role Assignment
                                </CardTitle>
                                <div class="relative">
                                    <Search class="h-4 w-4 absolute left-2.5 top-2.5 text-muted-foreground" />
                                    <Input
                                        v-model="searchQuery"
                                        placeholder="Search staff..."
                                        class="pl-8 h-8 text-sm w-56"
                                    />
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent>
                            <p class="text-xs text-muted-foreground mb-4">
                                Staff can hold multiple roles. Toggle roles on/off per employee.
                            </p>

                            <!-- No staff fallback -->
                            <div v-if="staffList.length === 0"
                                class="text-center py-12 text-muted-foreground text-sm">
                                <UserCog class="h-10 w-10 mx-auto mb-3 opacity-30" />
                                No staff found for this shop.
                            </div>

                            <table v-else class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-xs text-muted-foreground border-b">
                                        <th class="pb-2 font-medium w-1/4">Name</th>
                                        <th class="pb-2 font-medium w-1/4">Email</th>
                                        <th class="pb-2 font-medium">Current Roles</th>
                                        <th class="pb-2 font-medium">Assign Roles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="user in filteredStaff"
                                        :key="user.id"
                                        class="border-b last:border-0"
                                    >
                                        <td class="py-3 font-medium">{{ user.name }}</td>
                                        <td class="py-3 text-muted-foreground text-xs">{{ user.email }}</td>

                                        <!-- Current roles -->
                                        <td class="py-3">
                                            <div class="flex flex-wrap gap-1">
                                                <span
                                                    v-for="role in user.roles"
                                                    :key="role"
                                                    class="px-2 py-0.5 text-xs font-semibold rounded-full capitalize"
                                                    :class="roleColor(role)"
                                                >
                                                    {{ role }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Toggle roles -->
                                        <td class="py-3">
                                            <div class="flex flex-wrap gap-1">
                                                <button
                                                    v-for="role in nonOwnerRoles"
                                                    :key="role.name"
                                                    @click="toggleUserRole(user.id, role.name)"
                                                    class="px-2 py-0.5 text-xs font-medium rounded-full border capitalize transition-colors"
                                                    :class="userHasRole(user, role.name)
                                                        ? 'bg-blue-50 border-blue-300 text-blue-700'
                                                        : 'border-border text-muted-foreground hover:bg-muted'"
                                                >
                                                    <Check
                                                        v-if="userHasRole(user, role.name)"
                                                        class="inline h-2.5 w-2.5 mr-0.5"
                                                    />
                                                    {{ role.name }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr v-if="filteredStaff.length === 0">
                                        <td colspan="4" class="py-8 text-center text-muted-foreground">
                                            No staff matched your search.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </CardContent>
                    </Card>
                </div>

            </template>
        </div>
    </ShopLayout>
</template>
