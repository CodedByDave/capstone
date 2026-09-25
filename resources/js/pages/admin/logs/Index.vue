<script setup lang="ts">
<<<<<<< HEAD
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
=======
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    Archive,
    ArchiveRestore,
    ArrowDown,
    ArrowUp,
    ArrowUpDown,
    Download,
    Eye,
    FileClock,
    RefreshCcw,
    Search,
    Trash2,
    Upload,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import Vue3EasyDataTable, {
    type Header,
    type ServerOptions,
} from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

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
<<<<<<< HEAD
    const flash = page.props.toast as { type: string; message: string } | undefined
    if (!flash) return
    switch (flash.type) {
        case 'success': toast.success(flash.message); break
        case 'error':   toast.error(flash.message);   break
        default:        toast(flash.message)
    }
})
=======
    const flash = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (!flash) return;
    if (flash.type === 'error') {
        toast.error(flash.message);
        return;
    }

    toast.success(flash.message);
});
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

<<<<<<< HEAD
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard',  href: '/admin/dashboard' },
    { title: 'Login Logs', href: '/admin/login-logs' },
]
=======
type AuditSortColumn =
    | 'name'
    | 'email'
    | 'category'
    | 'module'
    | 'event'
    | 'occurred_at';

const headers: Header[] = [
    { text: '', value: 'selection', width: 48 },
    { text: 'User', value: 'name', width: 185 },
    { text: 'Email', value: 'email', width: 210 },
    { text: 'Role', value: 'role', width: 125 },
    { text: 'Type', value: 'category', width: 150 },
    { text: 'Module', value: 'module', width: 155 },
    { text: 'Event', value: 'event', width: 135 },
    { text: 'Context', value: 'context', width: 230 },
    { text: 'Date & Time', value: 'occurred_at', width: 190 },
    { text: 'Actions', value: 'actions', width: 105 },
];
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

// ─── Filters ──────────────────────────────────────────────────────────────────

<<<<<<< HEAD
const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? 'all')
const role   = ref(props.filters.role   ?? 'all')
const date   = ref(props.filters.date   ?? '')
=======
function toggleSort(column: AuditSortColumn) {
    if (serverOptions.value.sortBy === column) {
        serverOptions.value.sortType =
            serverOptions.value.sortType === 'asc' ? 'desc' : 'asc';
        return;
    }

    serverOptions.value.sortBy = column;
    serverOptions.value.sortType = 'asc';
}

function sortIcon(column: AuditSortColumn) {
    if (serverOptions.value.sortBy !== column) return ArrowUpDown;

    return serverOptions.value.sortType === 'asc' ? ArrowUp : ArrowDown;
}

function queryParams() {
    return {
        search: search.value || undefined,
        category: category.value !== 'all' ? category.value : undefined,
        role: role.value !== 'all' ? role.value : undefined,
        module: module.value !== 'all' ? module.value : undefined,
        event: event.value || undefined,
        date: date.value || undefined,
        sort_by: serverOptions.value.sortBy,
        sort_direction: serverOptions.value.sortType,
        page: serverOptions.value.page,
        per_page: serverOptions.value.rowsPerPage,
    };
}
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)

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

<<<<<<< HEAD
=======
                        <template #header-name="{ text }">
                            <button
                                type="button"
                                class="sortable-column"
                                :aria-label="`Sort users ${serverOptions.sortBy === 'name' && serverOptions.sortType === 'asc' ? 'descending' : 'ascending'}`"
                                @click="toggleSort('name')"
                            >
                                <span>{{ text }}</span>
                                <component
                                    :is="sortIcon('name')"
                                    class="h-3.5 w-3.5"
                                    :class="{
                                        'opacity-60':
                                            serverOptions.sortBy !== 'name',
                                    }"
                                />
                            </button>
                        </template>

                        <template #header-email="{ text }">
                            <button
                                type="button"
                                class="sortable-column"
                                :aria-label="`Sort emails ${serverOptions.sortBy === 'email' && serverOptions.sortType === 'asc' ? 'descending' : 'ascending'}`"
                                @click="toggleSort('email')"
                            >
                                <span>{{ text }}</span>
                                <component
                                    :is="sortIcon('email')"
                                    class="h-3.5 w-3.5"
                                    :class="{
                                        'opacity-60':
                                            serverOptions.sortBy !== 'email',
                                    }"
                                />
                            </button>
                        </template>

                        <template #header-role="{ text }">
                            <div class="column-filter">
                                <span>{{ text }}</span>
                                <select
                                    v-model="role"
                                    class="column-filter-input"
                                    @click.stop
                                    @change="filterTable"
                                >
                                    <option value="all">All roles</option>
                                    <option value="super_admin">
                                        Super Admin
                                    </option>
                                    <option value="owner">Owner</option>
                                    <option value="staff">Staff</option>
                                    <option value="user">Customer</option>
                                </select>
                            </div>
                        </template>

                        <template #header-category="{ text }">
                            <div class="column-filter">
                                <button
                                    type="button"
                                    class="sortable-column"
                                    :aria-label="`Sort types ${serverOptions.sortBy === 'category' && serverOptions.sortType === 'asc' ? 'descending' : 'ascending'}`"
                                    @click="toggleSort('category')"
                                >
                                    <span>{{ text }}</span>
                                    <component
                                        :is="sortIcon('category')"
                                        class="h-3.5 w-3.5"
                                        :class="{
                                            'opacity-60':
                                                serverOptions.sortBy !==
                                                'category',
                                        }"
                                    />
                                </button>
                                <select
                                    v-model="category"
                                    class="column-filter-input"
                                    @click.stop
                                    @change="filterTable"
                                >
                                    <option value="all">All types</option>
                                    <option value="authentication">
                                        Authentication
                                    </option>
                                    <option value="activity">
                                        User Activity
                                    </option>
                                </select>
                            </div>
                        </template>

                        <template #header-module="{ text }">
                            <div class="column-filter">
                                <button
                                    type="button"
                                    class="sortable-column"
                                    :aria-label="`Sort modules ${serverOptions.sortBy === 'module' && serverOptions.sortType === 'asc' ? 'descending' : 'ascending'}`"
                                    @click="toggleSort('module')"
                                >
                                    <span>{{ text }}</span>
                                    <component
                                        :is="sortIcon('module')"
                                        class="h-3.5 w-3.5"
                                        :class="{
                                            'opacity-60':
                                                serverOptions.sortBy !==
                                                'module',
                                        }"
                                    />
                                </button>
                                <select
                                    v-model="module"
                                    class="column-filter-input"
                                    @click.stop
                                    @change="filterTable"
                                >
                                    <option value="all">All modules</option>
                                    <option value="Authentication">
                                        Authentication
                                    </option>
                                    <option
                                        v-for="item in modules"
                                        :key="item"
                                        :value="item"
                                    >
                                        {{ item }}
                                    </option>
                                </select>
                            </div>
                        </template>

                        <template #header-event="{ text }">
                            <div class="column-filter">
                                <button
                                    type="button"
                                    class="sortable-column"
                                    :aria-label="`Sort events ${serverOptions.sortBy === 'event' && serverOptions.sortType === 'asc' ? 'descending' : 'ascending'}`"
                                    @click="toggleSort('event')"
                                >
                                    <span>{{ text }}</span>
                                    <component
                                        :is="sortIcon('event')"
                                        class="h-3.5 w-3.5"
                                        :class="{
                                            'opacity-60':
                                                serverOptions.sortBy !==
                                                'event',
                                        }"
                                    />
                                </button>
                                <input
                                    v-model="event"
                                    class="column-filter-input"
                                    placeholder="Filter event"
                                    @click.stop
                                    @keyup.enter="filterTable"
                                />
                            </div>
                        </template>

                        <template #header-occurred_at="{ text }">
                            <div class="column-filter">
                                <button
                                    type="button"
                                    class="sortable-column"
                                    :aria-label="`Sort dates ${serverOptions.sortBy === 'occurred_at' && serverOptions.sortType === 'asc' ? 'descending' : 'ascending'}`"
                                    @click="toggleSort('occurred_at')"
                                >
                                    <span>{{ text }}</span>
                                    <component
                                        :is="sortIcon('occurred_at')"
                                        class="h-3.5 w-3.5"
                                        :class="{
                                            'opacity-60':
                                                serverOptions.sortBy !==
                                                'occurred_at',
                                        }"
                                    />
                                </button>
                                <input
                                    v-model="date"
                                    type="date"
                                    class="column-filter-input"
                                    @click.stop
                                    @change="filterTable"
                                />
                            </div>
                        </template>

                        <template #item-selection="log">
                            <input
                                type="checkbox"
                                :checked="selected.includes(entryKey(log))"
                                :aria-label="`Select audit log ${log.record_id}`"
                                class="rounded"
                                @change="toggleOne(log)"
                            />
                        </template>

                        <template #item-name="log">
                            <span class="font-medium whitespace-nowrap">{{
                                log.name || 'Unknown user'
                            }}</span>
                        </template>
                        <template #item-email="log">
                            <span class="text-xs text-muted-foreground">{{
                                log.email || '—'
                            }}</span>
                        </template>
                        <template #item-role="log">
                            <span
                                v-if="log.role"
                                class="rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                :class="roleBadge[log.role] ?? roleBadge.user"
                            >
                                {{ log.role.replace('_', ' ') }}
                            </span>
                            <span v-else class="text-xs text-muted-foreground"
                                >—</span
                            >
                        </template>
                        <template #item-category="log">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="
                                    log.category === 'authentication'
                                        ? 'bg-indigo-100 text-indigo-700'
                                        : 'bg-emerald-100 text-emerald-700'
                                "
                            >
                                {{
                                    log.category === 'authentication'
                                        ? 'Authentication'
                                        : 'User Activity'
                                }}
                            </span>
                        </template>
                        <template #item-module="log">
                            <span class="text-xs font-medium">{{
                                log.module
                            }}</span>
                        </template>
                        <template #item-event="log">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="
                                    eventBadge[log.event] ??
                                    'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300'
                                "
                            >
                                {{ eventLabel(log.event) }}
                            </span>
                        </template>
                        <template #item-context="log">
                            <span
                                class="line-clamp-2 text-xs text-muted-foreground"
                                :title="log.context"
                                >{{ log.context }}</span
                            >
                        </template>
                        <template #item-occurred_at="log">
                            <span
                                class="text-xs whitespace-nowrap text-muted-foreground"
                                >{{ formatDate(log.occurred_at) }}</span
                            >
                        </template>
                        <template #item-actions="log">
                            <div class="flex items-center justify-center gap-1">
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    aria-label="View audit event"
                                    @click="
                                        router.visit(
                                            `/admin/audit-logs/${log.category}/${log.record_id}`,
                                        )
                                    "
                                >
                                    <Eye class="h-4 w-4 text-blue-500" />
                                </Button>
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    aria-label="Archive log"
                                    @click="openArchive(log)"
                                >
                                    <Trash2 class="h-4 w-4 text-amber-500" />
                                </Button>
                            </div>
                        </template>

                        <template #empty-message>
                            <div
                                class="py-10 text-center text-sm text-muted-foreground"
                            >
                                <FileClock
                                    class="mx-auto mb-2 h-10 w-10 opacity-20"
                                />
                                No audit events found.
                            </div>
                        </template>
                    </EasyDataTable>
                </div>
            </CardContent>
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
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
<<<<<<< HEAD
=======

<style scoped>
.audit-data-table {
    --easy-table-border: 0;
    --easy-table-row-border: 1px solid var(--border);
    --easy-table-header-background-color: var(--muted);
    --easy-table-header-font-color: var(--muted-foreground);
    --easy-table-header-font-size: 12px;
    --easy-table-header-height: 82px;
    --easy-table-header-item-padding: 10px 12px;
    --easy-table-body-row-background-color: var(--background);
    --easy-table-body-even-row-background-color: color-mix(
        in srgb,
        var(--muted) 35%,
        transparent
    );
    --easy-table-body-row-font-color: var(--foreground);
    --easy-table-body-even-row-font-color: var(--foreground);
    --easy-table-body-row-hover-background-color: color-mix(
        in srgb,
        var(--muted) 65%,
        transparent
    );
    --easy-table-body-row-hover-font-color: var(--foreground);
    --easy-table-body-row-height: 62px;
    --easy-table-body-item-padding: 10px 12px;
    --easy-table-message-font-color: var(--muted-foreground);
    --easy-table-footer-background-color: var(--background);
    --easy-table-footer-font-color: var(--muted-foreground);
    --easy-table-footer-font-size: 12px;
    --easy-table-footer-height: 56px;
    --easy-table-footer-padding: 0 16px;
    --easy-table-buttons-pagination-border: 1px solid var(--border);
    width: 100%;
}

.sortable-column {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: inherit;
    font: inherit;
}

.sortable-column:hover {
    color: var(--foreground);
}

.column-filter {
    display: flex;
    min-width: 0;
    flex-direction: column;
    gap: 6px;
    text-align: left;
}

.column-filter-input {
    height: 30px;
    width: 100%;
    min-width: 95px;
    border: 1px solid var(--border);
    border-radius: 6px;
    background: var(--background);
    padding: 0 8px;
    color: var(--foreground);
    font-size: 12px;
    font-weight: 400;
    outline: none;
}

.column-filter-input:focus {
    border-color: var(--ring);
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--ring) 20%, transparent);
}

:deep(.vue3-easy-data-table__main) {
    background: var(--background);
}

:deep(.vue3-easy-data-table__main table) {
    min-width: 1500px;
}
</style>
>>>>>>> 7b1b8656 (feat(admin): added issue reports features for system users and RBAC for giving users access what they can do)
