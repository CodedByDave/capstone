<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import { ref, computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
    Table, TableBody, TableCell, TableHead,
    TableHeader, TableRow,
} from '@/components/ui/table'
import {
    UserPlus, Pencil, RefreshCw, Archive,
    CircleDot, DollarSign, Clock, FileUp,
    ChevronLeft, Search, X,
    ChevronRight, ChevronsLeft, ChevronsRight,
} from 'lucide-vue-next'

/* ───────── TYPES ───────── */

interface Performer { id: number; name: string }

interface Subject {
    id: number
    first_name?: string
    last_name?: string
    employee_id?: string
    name?: string
    sku?: string
}

interface ActivityLog {
    id: number
    module: string
    action: string
    subject_type: string
    subject_id: number
    subject: Subject | null
    changes: Record<string, { old: string; new: string }> | null
    performed_by: number | null
    performer: Performer | null
    created_at: string
}

interface PaginatedLogs {
    data: ActivityLog[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
}

/* ───────── PROPS ───────── */

const page = usePage()
const isOwner = computed(() => page.props.auth.user.role === 'owner')
const baseRoute = computed(() => isOwner.value ? '/shop' : '/staff')

const { logs, performers, modules, filters } = defineProps<{
    logs: PaginatedLogs
    performers: Performer[]
    modules: string[]
    filters: {
        action?: string
        performed_by?: string
        module?: string
        date_from?: string
        date_to?: string
        search?: string
    }
}>()

/* ───────── BREADCRUMBS ───────── */

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: `${baseRoute.value}/employee` },
    { title: 'Activity Logs', href: `${baseRoute.value}/logs` },
]

/* ───────── FILTER STATE ───────── */

const search       = ref(filters.search ?? '')
const action       = ref(filters.action ?? '__all__')
const performedBy  = ref(filters.performed_by ?? '__all__')
const moduleFilter = ref(filters.module ?? '__all__')
const dateFrom     = ref(filters.date_from ?? '')
const dateTo       = ref(filters.date_to ?? '')

/* ───────── FILTER FUNCTIONS ───────── */

function applyFilters(currentPage = 1) {
    router.get(`${baseRoute.value}/logs`, {
        search:       search.value || undefined,
        action:       action.value === '__all__' ? undefined : action.value,
        performed_by: performedBy.value === '__all__' ? undefined : performedBy.value,
        module:       moduleFilter.value === '__all__' ? undefined : moduleFilter.value,
        date_from:    dateFrom.value || undefined,
        date_to:      dateTo.value || undefined,
        per_page:     10,
        page: currentPage,
    }, { preserveScroll: true, preserveState: false })
}

function clearFilters() {
    search.value       = ''
    action.value       = '__all__'
    performedBy.value  = '__all__'
    moduleFilter.value = '__all__'
    dateFrom.value     = ''
    dateTo.value       = ''
    applyFilters()
}

function goToPage(page: number) { applyFilters(page) }

function visiblePages(current: number, total: number): number[] {
    const pages: number[] = []
    for (let i = Math.max(1, current - 2); i <= Math.min(total, current + 2); i++) {
        pages.push(i)
    }
    return pages
}

/* ───────── SUBJECT HELPERS ───────── */

function subjectLabel(log: ActivityLog): string {
    if (!log.subject) return 'Deleted Record'
    const s = log.subject
    if (s.first_name) return `${s.first_name} ${s.last_name ?? ''}`.trim()
    if (s.name)       return s.name
    return `#${log.subject_id}`
}

function subjectMeta(log: ActivityLog): string | null {
    if (!log.subject) return null
    return log.subject.employee_id ?? log.subject.sku ?? null
}

const moduleRoutes = computed<Record<string, string>>(() => ({
    Employee: `${baseRoute.value}/employee`,
    Product:  `${baseRoute.value}/product`,
    Attendance: `${baseRoute.value}/attendance`,
}))

function subjectUrl(log: ActivityLog): string | null {
    if (!log.subject) return null
    const base = moduleRoutes.value[log.module]
    return base ? `${base}/${log.subject.id}` : null
}

/* ───────── ACTION BADGES ───────── */

const actionConfig: Record<string, { label: string; color: string; icon: any }> = {
    created:        { label: 'Created',        color: 'bg-green-100 text-green-700 border-green-200',    icon: UserPlus   },
    updated:        { label: 'Updated',        color: 'bg-blue-100 text-blue-700 border-blue-200',       icon: Pencil     },
    status_changed: { label: 'Status Changed', color: 'bg-yellow-100 text-yellow-700 border-yellow-200', icon: CircleDot  },
    salary_changed: { label: 'Salary Changed', color: 'bg-purple-100 text-purple-700 border-purple-200', icon: DollarSign },
    archived:       { label: 'Archived',       color: 'bg-red-100 text-red-700 border-red-200',          icon: Archive    },
    restored:       { label: 'Restored',       color: 'bg-teal-100 text-teal-700 border-teal-200',       icon: RefreshCw  },
    imported:       { label: 'Imported',       color: 'bg-orange-100 text-orange-700 border-orange-200', icon: FileUp     },
    clocked_in:     { label: 'Clocked In',  color: 'bg-emerald-100 text-emerald-700 border-emerald-200', icon: Clock },
    clocked_out:    { label: 'Clocked Out', color: 'bg-slate-100 text-slate-700 border-slate-200',       icon: Clock },
    marked_present: { label: 'Marked Present', color: 'bg-green-100 text-green-700 border-green-200', icon: UserPlus },
    marked_absent:  { label: 'Marked Absent',  color: 'bg-red-100 text-red-700 border-red-200',       icon: X },
}

function getConfig(a: string) {
    return actionConfig[a] ?? { label: a, color: 'bg-gray-100 text-gray-700 border-gray-200', icon: Clock }
}

/* ───────── HELPERS ───────── */

function formatFieldName(field: string) {
    return field.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

const formatDate = (val: string) =>
    new Date(val).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })

/* ───────── EXPANDED ROWS ───────── */

const expandedRows = ref<Set<number>>(new Set())

function toggleRow(id: number) {
    expandedRows.value.has(id)
        ? expandedRows.value.delete(id)
        : expandedRows.value.add(id)
}
</script>

<template>
    <Head title="Activity Logs" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Activity Logs">
        <div class="px-6 space-y-6">

            <!-- HEADER -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Activity Logs</h2>
                    <p class="text-sm text-muted-foreground">
                        Track all changes across every system module.
                    </p>
                </div>
                <Button variant="outline" @click="router.visit(`${baseRoute}/employee`)">
                    <ChevronLeft class="h-4 w-4 mr-1.5" /> Back to Employees
                </Button>
            </div>

            <!-- FILTERS -->
            <Card>
                <CardContent class="pt-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">

                        <!-- Search -->
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-muted-foreground">Search</label>
                            <div class="relative">
                                <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input v-model="search" placeholder="Subject name..." class="pl-8"
                                    @keyup.enter="applyFilters()" />
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-muted-foreground">Action</label>
                            <Select :model-value="action" @update:model-value="action = $event">
                                <SelectTrigger><SelectValue placeholder="All actions" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="__all__">All actions</SelectItem>
                                    <SelectItem value="created">Created</SelectItem>
                                    <SelectItem value="updated">Updated</SelectItem>
                                    <SelectItem value="status_changed">Status Changed</SelectItem>
                                    <SelectItem value="salary_changed">Salary Changed</SelectItem>
                                    <SelectItem value="archived">Archived</SelectItem>
                                    <SelectItem value="restored">Restored</SelectItem>
                                    <SelectItem value="imported">Imported</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Module -->
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-muted-foreground">Module</label>
                            <Select :model-value="moduleFilter" @update:model-value="moduleFilter = $event">
                                <SelectTrigger><SelectValue placeholder="All modules" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="__all__">All modules</SelectItem>
                                    <SelectItem v-for="m in modules" :key="m" :value="m">{{ m }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Performed By -->
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-muted-foreground">Performed By</label>
                            <Select :model-value="performedBy" @update:model-value="performedBy = $event">
                                <SelectTrigger><SelectValue placeholder="All performers" /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="__all__">All performers</SelectItem>
                                    <SelectItem v-for="p in performers" :key="p.id" :value="String(p.id)">
                                        {{ p.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Date From -->
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-muted-foreground">Date From</label>
                            <Input v-model="dateFrom" type="date" />
                        </div>

                        <!-- Date To -->
                        <div class="space-y-1">
                            <label class="text-xs font-medium text-muted-foreground">Date To</label>
                            <Input v-model="dateTo" type="date" />
                        </div>

                    </div>

                    <div class="flex items-center gap-2 mt-3">
                        <Button @click="applyFilters()">
                            <Search class="h-4 w-4 mr-1.5" /> Apply Filters
                        </Button>
                        <Button variant="outline" @click="clearFilters">
                            <X class="h-4 w-4 mr-1.5" /> Clear All
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- TABLE -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">
                        All Activity
                        <span class="text-muted-foreground font-normal text-sm ml-1">
                            ({{ logs.total }} records)
                        </span>
                    </CardTitle>
                </CardHeader>

                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Module</TableHead>
                                    <TableHead>Subject</TableHead>
                                    <TableHead>Action</TableHead>
                                    <TableHead>Performed By</TableHead>
                                    <TableHead>Changes</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <TableRow v-if="logs.data.length === 0">
                                    <TableCell colspan="6" class="text-center text-muted-foreground py-10">
                                        No activity logs found.
                                    </TableCell>
                                </TableRow>

                                <template v-for="log in logs.data" :key="log.id">
                                    <TableRow class="hover:bg-muted/40">

                                        <!-- Date -->
                                        <TableCell class="text-xs text-muted-foreground whitespace-nowrap">
                                            {{ formatDate(log.created_at) }}
                                        </TableCell>

                                        <!-- Module badge -->
                                        <TableCell>
                                            <span class="text-xs font-semibold px-2 py-1 rounded bg-muted">
                                                {{ log.module }}
                                            </span>
                                        </TableCell>

                                        <!-- Subject -->
                                        <TableCell>
                                            <template v-if="log.subject">
                                                <button v-if="subjectUrl(log)"
                                                    class="text-left hover:underline text-sm font-medium"
                                                    @click="router.visit(subjectUrl(log)!)">
                                                    {{ subjectLabel(log) }}
                                                </button>
                                                <span v-else class="text-sm font-medium">
                                                    {{ subjectLabel(log) }}
                                                </span>
                                                <p v-if="subjectMeta(log)"
                                                    class="text-xs text-muted-foreground font-mono">
                                                    {{ subjectMeta(log) }}
                                                </p>
                                            </template>
                                            <span v-else class="text-xs italic text-muted-foreground">
                                                Deleted Record
                                            </span>
                                        </TableCell>

                                        <!-- Action badge -->
                                        <TableCell>
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                                                :class="getConfig(log.action).color">
                                                <component :is="getConfig(log.action).icon" class="h-3 w-3" />
                                                {{ getConfig(log.action).label }}
                                            </span>
                                        </TableCell>

                                        <!-- Performer -->
                                        <TableCell class="text-sm">
                                            {{ log.performer?.name ?? '—' }}
                                        </TableCell>

                                        <!-- Changes toggle -->
                                        <TableCell>
                                            <Button
                                                v-if="log.changes && Object.keys(log.changes).length"
                                                size="sm" variant="ghost" class="text-xs h-7"
                                                @click="toggleRow(log.id)">
                                                {{ expandedRows.has(log.id) ? 'Hide' : 'View' }} changes
                                            </Button>
                                            <span v-else class="text-xs text-muted-foreground">—</span>
                                        </TableCell>

                                    </TableRow>

                                    <!-- Expanded change details -->
                                    <TableRow v-if="expandedRows.has(log.id) && log.changes" class="bg-muted/20">
                                        <TableCell colspan="6" class="px-6 py-3">
                                            <table class="w-full text-xs border rounded-md">
                                                <thead class="bg-muted text-muted-foreground">
                                                    <tr>
                                                        <th class="text-left px-3 py-1.5">Field</th>
                                                        <th class="text-left px-3 py-1.5">Old</th>
                                                        <th class="text-left px-3 py-1.5">New</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(change, field) in log.changes" :key="field"
                                                        class="border-t">
                                                        <td class="px-3 py-1.5 font-medium">
                                                            {{ formatFieldName(String(field)) }}
                                                        </td>
                                                        <td class="px-3 py-1.5 text-red-600 line-through">
                                                            {{ change.old ?? '—' }}
                                                        </td>
                                                        <td class="px-3 py-1.5 text-green-600 font-medium">
                                                            {{ change.new ?? '—' }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- PAGINATION -->
                    <div v-if="logs.last_page >= 1"
                        class="flex items-center justify-between px-4 py-3 border-t text-sm">

                        <!-- Record count -->
                        <span class="text-muted-foreground text-xs">
                            <template v-if="logs.total === 0">No records</template>
                            <template v-else>
                                Showing {{ logs.from }}–{{ logs.to }} of {{ logs.total }} records
                            </template>
                        </span>

                        <!-- Page controls -->
                        <div v-if="logs.last_page > 1" class="flex items-center gap-1">

                            <!-- First page -->
                            <Button
                                variant="outline" size="sm"
                                :disabled="logs.current_page === 1"
                                class="h-8 w-8 p-0"
                                @click="goToPage(1)">
                                <ChevronsLeft class="h-3.5 w-3.5" />
                            </Button>

                            <!-- Prev page -->
                            <Button
                                variant="outline" size="sm"
                                :disabled="logs.current_page === 1"
                                class="h-8 w-8 p-0"
                                @click="goToPage(logs.current_page - 1)">
                                <ChevronLeft class="h-3.5 w-3.5" />
                            </Button>

                            <!-- Numbered pages -->
                            <Button
                                v-for="page in visiblePages(logs.current_page, logs.last_page)"
                                :key="page"
                                size="sm"
                                class="h-8 w-8 p-0"
                                :variant="page === logs.current_page ? 'default' : 'outline'"
                                @click="goToPage(page)">
                                {{ page }}
                            </Button>

                            <!-- Next page -->
                            <Button
                                variant="outline" size="sm"
                                :disabled="logs.current_page === logs.last_page"
                                class="h-8 w-8 p-0"
                                @click="goToPage(logs.current_page + 1)">
                                <ChevronRight class="h-3.5 w-3.5" />
                            </Button>

                            <!-- Last page -->
                            <Button
                                variant="outline" size="sm"
                                :disabled="logs.current_page === logs.last_page"
                                class="h-8 w-8 p-0"
                                @click="goToPage(logs.last_page)">
                                <ChevronsRight class="h-3.5 w-3.5" />
                            </Button>

                        </div>
                    </div>

                </CardContent>
            </Card>

        </div>
    </ShopLayout>
</template>
