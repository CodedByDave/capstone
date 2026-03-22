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
import { ArchiveRestore, Trash2, Search, ArrowLeft, ShieldCheck } from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface LoginLog {
    id: number
    email: string
    name: string | null
    role: string | null
    ip_address: string | null
    status: 'success' | 'failed' | 'logout'
    failure_reason: string | null
    logged_at: string
    deleted_at: string
}

interface Paginator {
    data: LoginLog[]
    current_page: number
    last_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    logs:    Paginator
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
    { title: 'Dashboard',  href: '/admin/dashboard' },
    { title: 'Login Logs', href: '/admin/login-logs' },
    { title: 'Archive',    href: '/admin/login-logs/archive' },
]

// ─── Filters ──────────────────────────────────────────────────────────────────

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? 'all')

function applyFilters() {
    router.get('/admin/login-logs/archive', {
        search: search.value || undefined,
        status: status.value !== 'all' ? status.value : undefined,
    }, { preserveState: true, replace: true })
}

// ─── Selection ────────────────────────────────────────────────────────────────

const selected    = ref<number[]>([])
const allSelected = computed(() =>
    props.logs.data.length > 0 &&
    props.logs.data.every(l => selected.value.includes(l.id))
)

function toggleAll() {
    allSelected.value
        ? selected.value = []
        : selected.value = props.logs.data.map(l => l.id)
}

function toggleOne(id: number) {
    selected.value.includes(id)
        ? selected.value = selected.value.filter(i => i !== id)
        : selected.value.push(id)
}

// ─── Restore ──────────────────────────────────────────────────────────────────

function restore(id: number) {
    router.post(`/admin/login-logs/archive/${id}/restore`, {}, {
        preserveScroll: true,
        onSuccess: () => toast.success('Log restored.'),
        onError:   () => toast.error('Failed to restore.'),
    })
}

function bulkRestore() {
    if (!selected.value.length) return
    router.post('/admin/login-logs/archive/bulk-restore', { ids: selected.value }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${selected.value.length} log(s) restored.`)
            selected.value = []
        },
        onError: () => toast.error('Bulk restore failed.'),
    })
}

// ─── Force Delete ─────────────────────────────────────────────────────────────

const deleteId   = ref<number | null>(null)
const deleteOpen = ref(false)

function openDelete(id: number) { deleteId.value = id; deleteOpen.value = true }
function cancelDelete()         { deleteOpen.value = false; setTimeout(() => { deleteId.value = null }, 200) }

function confirmDelete() {
    if (!deleteId.value) return
    router.delete(`/admin/login-logs/archive/${deleteId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Log permanently deleted.')
            deleteOpen.value = false
        },
        onError: () => toast.error('Failed to delete.'),
    })
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(d: string) {
    return new Date(d).toLocaleString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

const statusBadge: Record<string, string> = {
    success: 'bg-green-100 text-green-700',
    failed:  'bg-red-100 text-red-700',
    logout:  'bg-gray-100 text-gray-600',
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
    <Head title="Login Logs — Archive" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="Login Logs Archive">
        <div class="px-6 space-y-6">

            <Card>
                <CardHeader class="pb-3">
                    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                        <CardTitle class="flex items-center gap-2">
                            <ArchiveRestore class="h-4 w-4 text-muted-foreground" />
                            Archived Logs
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
                            <Button size="sm" variant="outline" @click="router.visit('/admin/login-logs')">
                                <ArrowLeft class="h-4 w-4 mr-1.5" /> Back to Logs
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
                                placeholder="Search email or name..."
                                class="pl-8"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <Select v-model="status" @update:model-value="applyFilters">
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="success">Success</SelectItem>
                                <SelectItem value="failed">Failed</SelectItem>
                                <SelectItem value="logout">Logout</SelectItem>
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
                                    <th class="text-left px-4 py-3 font-medium">User</th>
                                    <th class="text-left px-4 py-3 font-medium">Role</th>
                                    <th class="text-left px-4 py-3 font-medium">Status</th>
                                    <th class="text-left px-4 py-3 font-medium">IP Address</th>
                                    <th class="text-left px-4 py-3 font-medium">Logged At</th>
                                    <th class="text-left px-4 py-3 font-medium">Archived At</th>
                                    <th class="text-center px-4 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="log in logs.data" :key="log.id"
                                    class="border-b last:border-0 hover:bg-muted/20 transition-colors"
                                    :class="{ 'bg-muted/10': selected.includes(log.id) }"
                                >
                                    <td class="px-4 py-3">
                                        <input
                                            type="checkbox"
                                            :checked="selected.includes(log.id)"
                                            @change="toggleOne(log.id)"
                                            class="rounded"
                                        />
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium">{{ log.name ?? '—' }}</p>
                                        <p class="text-xs text-muted-foreground">{{ log.email }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            v-if="log.role"
                                            class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                            :class="roleBadge[log.role] ?? 'bg-gray-100 text-gray-600'"
                                        >
                                            {{ log.role.replace('_', ' ') }}
                                        </span>
                                        <span v-else class="text-xs text-muted-foreground">—</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                                            :class="statusBadge[log.status]"
                                        >
                                            {{ log.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                        {{ log.ip_address ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                                        {{ formatDate(log.logged_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                                        {{ formatDate(log.deleted_at) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-1">
                                            <Button size="icon" variant="ghost" @click="restore(log.id)">
                                                <ArchiveRestore class="h-4 w-4 text-green-500" />
                                            </Button>
                                            <Button size="icon" variant="ghost" @click="openDelete(log.id)">
                                                <Trash2 class="h-4 w-4 text-red-400" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="logs.data.length === 0">
                                    <td colspan="8" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                        <ShieldCheck class="h-10 w-10 mx-auto mb-2 opacity-20" />
                                        No archived logs found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="logs.last_page > 1" class="flex items-center justify-between pt-2">
                        <p class="text-xs text-muted-foreground">
                            Showing {{ logs.data.length }} of {{ logs.total }} logs
                        </p>
                        <div class="flex gap-1">
                            <Button
                                v-for="link in logs.links" :key="link.label"
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
                    <AlertDialogTitle>Permanently Delete Log</AlertDialogTitle>
                    <AlertDialogDescription>
                        This log will be permanently removed and cannot be recovered.
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
