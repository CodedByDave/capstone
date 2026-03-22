<script setup lang="ts">
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
    AlertDialog, AlertDialogContent, AlertDialogHeader,
    AlertDialogTitle, AlertDialogDescription, AlertDialogFooter,
} from '@/components/ui/alert-dialog'
import {
    Users, UserCheck, Store, User,
    Search, Archive, ArchiveRestore, Trash2, Eye, RefreshCcw,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Shop {
    id: number
    shop_name: string
}

interface UserItem {
    id: number
    name: string
    email: string
    role: string
    is_verified: boolean
    shop_id: number | null
    shop: Shop | null
    created_at: string
}

interface Paginator {
    data: UserItem[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    users: Paginator
    stats: {
        total: number
        owners: number
        staff: number
        users: number
        archived: number
    }
    filters: Record<string, string>
}>()

// ─── Flash ────────────────────────────────────────────────────────────────────

const page = usePage()

onMounted(() => {
    const flash = page.props.toast as { type: string; message: string } | undefined
    if (!flash) return
    switch (flash.type) {
        case 'success': toast.success(flash.message); break
        case 'error':   toast.error(flash.message);   break
        default:        toast(flash.message)
    }
})

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Users',     href: '/admin/users' },
]

// ─── Filters ──────────────────────────────────────────────────────────────────

const search   = ref(props.filters.search   ?? '')
const role     = ref(props.filters.role     ?? 'all')
const verified = ref(props.filters.verified ?? 'all')

function applyFilters() {
    router.get('/admin/users', {
        search:   search.value   || undefined,
        role:     role.value     !== 'all' ? role.value     : undefined,
        verified: verified.value !== 'all' ? verified.value : undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    search.value   = ''
    role.value     = 'all'
    verified.value = 'all'
    router.get('/admin/users', {}, { preserveState: true, replace: true })
}

// ─── Selection ────────────────────────────────────────────────────────────────

const selected    = ref<number[]>([])
const allSelected = computed(() =>
    props.users.data.length > 0 &&
    props.users.data.every(u => selected.value.includes(u.id))
)

function toggleAll() {
    allSelected.value
        ? selected.value = []
        : selected.value = props.users.data.map(u => u.id)
}

function toggleOne(id: number) {
    selected.value.includes(id)
        ? selected.value = selected.value.filter(i => i !== id)
        : selected.value.push(id)
}

// ─── Archive single ───────────────────────────────────────────────────────────

const archiveId   = ref<number | null>(null)
const archiveName = ref('')
const archiveOpen = ref(false)

function openArchive(user: UserItem) {
    archiveId.value   = user.id
    archiveName.value = user.name
    archiveOpen.value = true
}

function cancelArchive() {
    archiveOpen.value = false
    setTimeout(() => { archiveId.value = null; archiveName.value = '' }, 200)
}

function confirmArchive() {
    if (!archiveId.value) return
    router.delete(`/admin/users/${archiveId.value}`, {
        preserveScroll: true,
        onSuccess: () => { toast.success('User archived.'); archiveOpen.value = false },
        onError:   () => toast.error('Failed to archive user.'),
    })
}

// ─── Bulk archive ─────────────────────────────────────────────────────────────

function bulkArchive() {
    if (!selected.value.length) return
    router.post('/admin/users/bulk-archive', { ids: selected.value }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${selected.value.length} user(s) archived.`)
            selected.value = []
        },
        onError: () => toast.error('Bulk archive failed.'),
    })
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(d: string) {
    return new Date(d).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
    })
}

const roleBadge: Record<string, string> = {
    super_admin: 'bg-purple-100 text-purple-700',
    owner:       'bg-blue-100 text-blue-700',
    manager:     'bg-sky-100 text-sky-700',
    staff:       'bg-orange-100 text-orange-700',
    user:        'bg-gray-100 text-gray-600',
}
</script>

<template>
    <Head title="Users" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="User Management">
        <div class="px-6 space-y-6">

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Total</p>
                            <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <Users class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold">{{ stats.total.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Owners</p>
                            <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <Store class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-blue-600">{{ stats.owners.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Staff</p>
                            <div class="h-8 w-8 rounded-lg bg-orange-100 flex items-center justify-center">
                                <UserCheck class="h-4 w-4 text-orange-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-orange-600">{{ stats.staff.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Customers</p>
                            <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                                <User class="h-4 w-4 text-green-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-green-600">{{ stats.users.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Archived</p>
                            <div class="h-8 w-8 rounded-lg bg-amber-100 flex items-center justify-center">
                                <Archive class="h-4 w-4 text-amber-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-amber-600">{{ stats.archived.toLocaleString() }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Table card -->
            <Card>
                <CardHeader class="pb-3">
                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                        <CardTitle class="flex items-center gap-2">
                            <Users class="h-4 w-4 text-muted-foreground" />
                            All Users
                        </CardTitle>
                        <div class="flex gap-2 flex-wrap">
                            <Button
                                v-if="selected.length > 0"
                                size="sm"
                                variant="outline"
                                class="border-amber-300 text-amber-700 hover:bg-amber-50"
                                @click="bulkArchive"
                            >
                                <Archive class="h-4 w-4 mr-1.5" />
                                Archive ({{ selected.length }})
                            </Button>
                            <Button size="sm" variant="outline" @click="router.visit('/admin/users/archive')">
                                <ArchiveRestore class="h-4 w-4 mr-1.5" /> View Archive
                            </Button>
                            <Button size="sm" variant="ghost" @click="resetFilters">
                                <RefreshCcw class="h-4 w-4 mr-1.5" /> Reset
                            </Button>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="space-y-4">
                    <!-- Filters -->
                    <div class="flex flex-wrap gap-2">
                        <div class="relative flex-1 min-w-48">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                placeholder="Search name or email..."
                                class="pl-8"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <Select v-model="role" @update:model-value="applyFilters">
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Roles</SelectItem>
                                <SelectItem value="owner">Owner</SelectItem>
                                <SelectItem value="manager">Manager</SelectItem>
                                <SelectItem value="staff">Staff</SelectItem>
                                <SelectItem value="user">Customer</SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="verified" @update:model-value="applyFilters">
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Account" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All</SelectItem>
                                <SelectItem value="yes">Verified</SelectItem>
                                <SelectItem value="no">Unverified</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Table -->
                    <div class="rounded-lg border overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                    <th class="px-4 py-3 w-8">
                                        <input
                                            type="checkbox"
                                            :checked="allSelected"
                                            @change="toggleAll"
                                            class="rounded"
                                        />
                                    </th>
                                    <th class="text-left px-4 py-3 font-medium">Name</th>
                                    <th class="text-left px-4 py-3 font-medium">Email</th>
                                    <th class="text-left px-4 py-3 font-medium">Role</th>
                                    <th class="text-left px-4 py-3 font-medium">Shop</th>
                                    <th class="text-left px-4 py-3 font-medium">Account</th>
                                    <th class="text-left px-4 py-3 font-medium">Joined</th>
                                    <th class="text-center px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="u in users.data" :key="u.id"
                                    class="border-b last:border-0 hover:bg-muted/20 transition-colors"
                                    :class="{ 'bg-muted/10': selected.includes(u.id) }"
                                >
                                    <td class="px-4 py-3">
                                        <input
                                            type="checkbox"
                                            :checked="selected.includes(u.id)"
                                            @change="toggleOne(u.id)"
                                            class="rounded"
                                        />
                                    </td>
                                    <td class="px-4 py-3 font-medium whitespace-nowrap">{{ u.name }}</td>
                                    <td class="px-4 py-3 text-muted-foreground text-xs">{{ u.email }}</td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                            :class="roleBadge[u.role] ?? 'bg-gray-100 text-gray-600'"
                                        >
                                            {{ u.role.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground">
                                        {{ u.shop?.shop_name ?? '—' }}
                                    </td>

                                    <!-- Account — verified for all roles since all go through OTP -->
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full font-medium"
                                            :class="u.is_verified
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-600'"
                                        >
                                            {{ u.is_verified ? 'Verified' : 'Unverified' }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                                        {{ formatDate(u.created_at) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1">
                                            <Button
                                                size="icon" variant="ghost"
                                                @click="router.visit(`/admin/users/${u.id}`)"
                                            >
                                                <Eye class="h-4 w-4 text-blue-500" />
                                            </Button>
                                            <Button
                                                size="icon" variant="ghost"
                                                @click="openArchive(u)"
                                            >
                                                <Trash2 class="h-4 w-4 text-amber-500" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="users.data.length === 0">
                                    <td colspan="8" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                        <Users class="h-10 w-10 mx-auto mb-2 opacity-20" />
                                        No users found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="users.last_page > 1" class="flex items-center justify-between pt-2">
                        <p class="text-xs text-muted-foreground">
                            Showing {{ users.data.length }} of {{ users.total }} users
                        </p>
                        <div class="flex gap-1">
                            <Button
                                v-for="link in users.links" :key="link.label"
                                size="sm"
                                :variant="link.active ? 'default' : 'outline'"
                                :disabled="!link.url"
                                class="h-7 min-w-7 text-xs"
                                @click="link.url && router.visit(link.url)"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>

        </div>

        <!-- Archive confirm -->
        <AlertDialog v-model:open="archiveOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive User</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to archive
                        <strong>{{ archiveName }}</strong>?
                        They will lose access but can be restored later.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="cancelArchive">Cancel</Button>
                    <Button variant="destructive" @click="confirmArchive">Archive</Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </AdminLayout>
</template>
