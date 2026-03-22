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
    ShieldCheck, ShieldX, LogOut, Archive,
    Search, RefreshCcw, Trash2, ArchiveRestore,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface LoginLog {
    id: number
    user_id: number | null
    email: string
    name: string | null
    role: string | null
    ip_address: string | null
    user_agent: string | null
    status: 'success' | 'failed' | 'logout'
    failure_reason: string | null
    logged_at: string
}

interface Paginator {
    data: LoginLog[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    links: { url: string | null; label: string; active: boolean }[]
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{
    logs: Paginator
    stats: {
        total: number
        success: number
        failed: number
        logout: number
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
    { title: 'Dashboard',  href: '/admin/dashboard' },
    { title: 'Login Logs', href: '/admin/login-logs' },
]

// ─── Filters ──────────────────────────────────────────────────────────────────

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? 'all')
const role   = ref(props.filters.role   ?? 'all')
const date   = ref(props.filters.date   ?? '')

function applyFilters() {
    router.get('/admin/login-logs', {
        search: search.value  || undefined,
        status: status.value !== 'all' ? status.value : undefined,
        role:   role.value   !== 'all' ? role.value   : undefined,
        date:   date.value   || undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    search.value = ''
    status.value = 'all'
    role.value   = 'all'
    date.value   = ''
    router.get('/admin/login-logs', {}, { preserveState: true, replace: true })
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

// ─── Archive single (with confirm) ───────────────────────────────────────────

const archiveId   = ref<number | null>(null)
const archiveOpen = ref(false)

function openArchive(id: number) { archiveId.value = id; archiveOpen.value = true }
function cancelArchive()         { archiveOpen.value = false; setTimeout(() => { archiveId.value = null }, 200) }

function confirmArchive() {
    if (!archiveId.value) return
    router.delete(`/admin/login-logs/${archiveId.value}`, {
        preserveScroll: true,
        onSuccess: () => { toast.success('Log archived.'); archiveOpen.value = false },
        onError:   () => toast.error('Failed to archive.'),
    })
}

// ─── Bulk archive ─────────────────────────────────────────────────────────────

function bulkArchive() {
    if (!selected.value.length) return
    router.post('/admin/login-logs/bulk-archive', { ids: selected.value }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success(`${selected.value.length} log(s) archived.`)
            selected.value = []
        },
        onError: () => toast.error('Bulk archive failed.'),
    })
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function formatDate(d: string) {
    return new Date(d).toLocaleString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
    })
}

function parseAgent(ua: string | null) {
    if (!ua) return '—'
    if (/Edg/i.test(ua))     return 'Edge'
    if (/Chrome/i.test(ua))  return 'Chrome'
    if (/Firefox/i.test(ua)) return 'Firefox'
    if (/Safari/i.test(ua))  return 'Safari'
    return ua.slice(0, 28) + '…'
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
    <Head title="Login Logs" />
    <AdminLayout :breadcrumbs="breadcrumbs" title="Login Logs">
        <div class="px-6 space-y-6">

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Total</p>
                            <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <Search class="h-4 w-4 text-blue-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold">{{ stats.total.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Success</p>
                            <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center">
                                <ShieldCheck class="h-4 w-4 text-green-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-green-600">{{ stats.success.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Failed</p>
                            <div class="h-8 w-8 rounded-lg bg-red-100 flex items-center justify-center">
                                <ShieldX class="h-4 w-4 text-red-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-red-600">{{ stats.failed.toLocaleString() }}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent class="pt-5">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-medium">Logouts</p>
                            <div class="h-8 w-8 rounded-lg bg-gray-100 flex items-center justify-center">
                                <LogOut class="h-4 w-4 text-gray-600" />
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-600">{{ stats.logout.toLocaleString() }}</p>
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
                            <ShieldCheck class="h-4 w-4 text-muted-foreground" />
                            Login Activity
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
                            <Button size="sm" variant="outline" @click="router.visit('/admin/login-logs/archive')">
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
                                placeholder="Search email, name, IP..."
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

                        <Select v-model="role" @update:model-value="applyFilters">
                            <SelectTrigger class="w-36">
                                <SelectValue placeholder="Role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Roles</SelectItem>
                                <SelectItem value="super_admin">Super Admin</SelectItem>
                                <SelectItem value="owner">Owner</SelectItem>
                                <SelectItem value="manager">Manager</SelectItem>
                                <SelectItem value="staff">Staff</SelectItem>
                                <SelectItem value="user">User</SelectItem>
                            </SelectContent>
                        </Select>

                        <Input
                            v-model="date"
                            type="date"
                            class="w-40"
                            @change="applyFilters"
                        />
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
                                    <th class="text-left px-4 py-3 font-medium">Browser</th>
                                    <th class="text-left px-4 py-3 font-medium">Failure Reason</th>
                                    <th class="text-left px-4 py-3 font-medium">Date & Time</th>
                                    <th class="text-center px-4 py-3 font-medium">Action</th>
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
                                    <td class="px-4 py-3 text-xs text-muted-foreground">
                                        {{ parseAgent(log.user_agent) }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground">
                                        {{ log.failure_reason ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">
                                        {{ formatDate(log.logged_at) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <Button size="icon" variant="ghost" @click="openArchive(log.id)">
                                            <Trash2 class="h-4 w-4 text-amber-500" />
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="logs.data.length === 0">
                                    <td colspan="9" class="px-4 py-12 text-center text-sm text-muted-foreground">
                                        <ShieldCheck class="h-10 w-10 mx-auto mb-2 opacity-20" />
                                        No login logs found.
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

        <!-- Archive confirm dialog -->
        <AlertDialog v-model:open="archiveOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Archive Log</AlertDialogTitle>
                    <AlertDialogDescription>
                        This log will be moved to the archive. You can restore it later.
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
