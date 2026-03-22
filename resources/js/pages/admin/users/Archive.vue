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
import { Users, ArchiveRestore, Trash2, Search, ArrowLeft } from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Shop { id: number; shop_name: string }
interface UserItem {
    id: number
    name: string
    email: string
    role: string
    is_verified: boolean
    shop: Shop | null
    created_at: string
    deleted_at: string
}

interface Paginator {
    data: UserItem[]
    current_page: number
    last_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    users:   Paginator
    total:   number
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
    { title: 'Archive',   href: '/admin/users/archive' },
]

// ─── Filters ──────────────────────────────────────────────────────────────────

const search = ref(props.filters.search ?? '')
const role   = ref(props.filters.role   ?? 'all')

function applyFilters() {
    router.get('/admin/users/archive', {
        search: search.value || undefined,
        role:   role.value !== 'all' ? role.value : undefined,
    }, { preserveState: true, replace: true })
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

// ─── Restore ──────────────────────────────────────────────────────────────────

function restore(id: number) {
    router.post(`/admin/users/archive/${id}/restore`, {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('User restored.'),
        onError:   () => toast.error('Failed to restore.'),
    })
}

function bulkRestore() {
    if (!selected.value.length) return
    router.post('/admin/users/archive/bulk-restore', { ids: selected.value }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${selected.value.length} user(s) restored.`)
            selected.value = []
        },
        onError: () => toast.error('Bulk restore failed.'),
    })
}

// ─── Force Delete ─────────────────────────────────────────────────────────────

const deleteId   = ref<number | null>(null)
const deleteName = ref('')
const deleteOpen = ref(false)

function openDelete(u: UserItem) {
    deleteId.value   = u.id
    deleteName.value = u.name
    deleteOpen.value = true
}

function cancelDelete() {
    deleteOpen.value = false
    setTimeout(() => { deleteId.value = null; deleteName.value = '' }, 200)
}

function confirmDelete() {
    if (!deleteId.value) return
    router.delete(`/admin/users/archive/${deleteId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('User permanently deleted.')
            deleteOpen.value = false
        },
        onError: () => toast.error('Failed to delete.'),
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
    <Head title="Users — Archive" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="Users Archive">
        <div class="px-6 space-y-6">

            <Card>
                <CardHeader class="pb-3">
                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                        <CardTitle class="flex items-center gap-2">
                            <ArchiveRestore class="h-4 w-4 text-muted-foreground" />
                            Archived Users
                            <span class="text-xs font-normal text-muted-foreground ml-1">({{ total }} total)</span>
                        </CardTitle>
                        <div class="flex gap-2 flex-wrap">
                            <Button
                                v-if="selected.length > 0"
                                size="sm"
                                variant="outline"
                                class="border-green-300 text-green-700 hover:bg-green-50"
                                @click="bulkRestore"
                            >
                                <ArchiveRestore class="h-4 w-4 mr-1.5" />
                                Restore ({{ selected.length }})
                            </Button>
                            <Button size="sm" variant="outline" @click="router.visit('/admin/users')">
                                <ArrowLeft class="h-4 w-4 mr-1.5" /> Back to Users
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
                    </div>

                    <!-- Table -->
                    <div class="rounded-lg border overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-muted/40 text-xs text-muted-foreground border-b">
                                    <th class="px-4 py-3 w-8">
                                        <input type="checkbox" :checked="allSelected" @change="toggleAll" class="rounded" />
                                    </th>
                                    <th class="text-left px-4 py-3 font-medium">Name</th>
                                    <th class="text-left px-4 py-3 font-medium">Email</th>
                                    <th class="text-left px-4 py-3 font-medium">Role</th>
                                    <th class="text-left px-4 py-3 font-medium">Shop</th>
                                    <th class="text-left px-4 py-3 font-medium">Joined</th>
                                    <th class="text-left px-4 py-3 font-medium">Archived At</th>
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
                                    <td class="px-4 py-3 text-xs text-muted-foreground">{{ u.email }}</td>
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
                                    <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                                        {{ formatDate(u.created_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                                        {{ formatDate(u.deleted_at) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1">
                                            <Button size="icon" variant="ghost" @click="restore(u.id)">
                                                <ArchiveRestore class="h-4 w-4 text-green-500" />
                                            </Button>
                                            <Button size="icon" variant="ghost" @click="openDelete(u)">
                                                <Trash2 class="h-4 w-4 text-red-400" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="users.data.length === 0">
                                    <td colspan="8" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                        <Users class="h-10 w-10 mx-auto mb-2 opacity-20" />
                                        No archived users found.
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

        <!-- Permanent delete confirm -->
        <AlertDialog v-model:open="deleteOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Permanently Delete User</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to permanently delete
                        <strong>{{ deleteName }}</strong>?
                        This cannot be undone.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <Button variant="outline" @click="cancelDelete">Cancel</Button>
                    <Button variant="destructive" @click="confirmDelete">Delete Forever</Button>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>

    </AdminLayout>
</template>
