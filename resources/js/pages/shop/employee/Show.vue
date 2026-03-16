<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'

// shadcn components
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

// icons
import {
    Pencil, Trash2, ArrowLeft,
    User, Briefcase, MapPin, Phone,
    Calendar, Banknote, BadgeCheck, Building2, Hash, Clock,
} from 'lucide-vue-next'

// AlertDialog
import {
    AlertDialog,
    AlertDialogTrigger,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
} from '@/components/ui/alert-dialog'

// ─── Types ────────────────────────────────────────────────────────────────────

interface AuditUser {
    id: number
    name: string
}

interface Employee {
    id: number
    employee_id: string
    first_name: string
    last_name: string
    phone: string | null
    address: string | null
    position: string
    branch_name: string | null
    hire_date: string
    salary: string | null
    status: 'Active' | 'Inactive'
    created_at: string
    updated_at: string
    creator: AuditUser | null
    updater: AuditUser | null
}

interface Schedule {
    id: number
    work_date: string
    start_time: string
    end_time: string
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { employee, schedule } = defineProps<{ employee: Employee, schedule: Schedule | null }>()

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: '/shop/employee' },
    { title: `${employee.first_name} ${employee.last_name}`, href: `/shop/employee/${employee.id}` },
]

// ─── Helpers ──────────────────────────────────────────────────────────────────

const formatSalary = (val: string | null) =>
    val ? `₱${parseFloat(val).toLocaleString('en-PH', { minimumFractionDigits: 2 })}` : '—'

const formatDate = (val: string) =>
    new Date(val).toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' })

const formatDateTime = (val: string) =>
    new Date(val).toLocaleString('en-PH', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })

// ─── Archive ──────────────────────────────────────────────────────────────────

function archiveEmployee() {
    router.delete(`/shop/employee/${employee.id}`, {
        preserveScroll: true,
        onSuccess: () => router.visit('/shop/employee'),
    })
}
</script>

<template>
    <Head :title="`${employee.first_name} ${employee.last_name}`" />

    <ShopLayout :breadcrumbs="breadcrumbs" :title="`${employee.first_name} ${employee.last_name}`">

        <div class="px-6 space-y-6">

            <!-- ── Actions bar ─────────────────────────────────────────── -->
            <div class="flex items-center justify-between">
                <Button type="button" variant="outline" @click="router.visit('/shop/employee')">
                    <ArrowLeft class="h-4 w-4 mr-2" /> Back to Employees
                </Button>
            </div>

            <!-- ── Profile header ──────────────────────────────────────── -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex items-center gap-5">
                        <div class="h-16 w-16 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                            <span class="text-2xl font-bold text-primary">
                                {{ employee.first_name[0] }}{{ employee.last_name[0] }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 flex-wrap">
                                <h2 class="text-xl font-bold">
                                    {{ employee.first_name }} {{ employee.last_name }}
                                </h2>
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full text-white"
                                    :class="{
                                        'bg-green-500': employee.status === 'Active',
                                        'bg-red-500':   employee.status === 'Inactive',
                                    }">
                                    {{ employee.status }}
                                </span>
                            </div>
                            <p class="text-sm text-muted-foreground mt-0.5">{{ employee.position }}</p>
                            <p class="text-xs text-muted-foreground font-mono mt-1">{{ employee.employee_id }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- ── Identity ───────────────────────────────────────── -->
                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm font-semibold uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                            <User class="h-4 w-4" /> Identity
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-start gap-3">
                            <Hash class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                            <div>
                                <p class="text-xs text-muted-foreground">Employee ID</p>
                                <p class="text-sm font-mono font-medium">{{ employee.employee_id }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <User class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                            <div>
                                <p class="text-xs text-muted-foreground">Full Name</p>
                                <p class="text-sm font-medium">{{ employee.first_name }} {{ employee.last_name }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Phone class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                            <div>
                                <p class="text-xs text-muted-foreground">Phone</p>
                                <p class="text-sm font-medium">{{ employee.phone ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <MapPin class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                            <div>
                                <p class="text-xs text-muted-foreground">Address</p>
                                <p class="text-sm font-medium leading-relaxed">{{ employee.address ?? '—' }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- ── Employment ─────────────────────────────────────── -->
                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm font-semibold uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                            <Briefcase class="h-4 w-4" /> Employment
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-start gap-3">
                            <Briefcase class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                            <div>
                                <p class="text-xs text-muted-foreground">Position</p>
                                <p class="text-sm font-medium">{{ employee.position }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Building2 class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                            <div>
                                <p class="text-xs text-muted-foreground">Branch</p>
                                <p class="text-sm font-medium">{{ employee.branch_name ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Calendar class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                            <div>
                                <p class="text-xs text-muted-foreground">Hire Date</p>
                                <p class="text-sm font-medium">{{ formatDate(employee.hire_date) }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Banknote class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                            <div>
                                <p class="text-xs text-muted-foreground">Salary</p>
                                <p class="text-sm font-medium">{{ formatSalary(employee.salary) }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <BadgeCheck class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                            <div>
                                <p class="text-xs text-muted-foreground">Status</p>
                                <span class="inline-block mt-0.5 px-2.5 py-0.5 text-xs font-semibold rounded-full text-white"
                                    :class="{
                                        'bg-green-500': employee.status === 'Active',
                                        'bg-red-500':   employee.status === 'Inactive',
                                    }">
                                    {{ employee.status }}
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

            </div>

            <!-- ── Schedule ───────────────────────────────────────────── -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-sm font-semibold uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                        <Calendar class="h-4 w-4" /> Schedule
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="!schedule" class="text-sm text-muted-foreground text-center py-6">
                        No schedule assigned yet.
                    </div>
                    <div v-else class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-muted-foreground">Work Date</p>
                            <p class="text-sm font-medium">{{ formatDate(schedule.work_date) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Start Time</p>
                            <p class="text-sm font-medium">{{ schedule.start_time }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">End Time</p>
                            <p class="text-sm font-medium">{{ schedule.end_time ?? '—' }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- ── Audit info ──────────────────────────────────────────── -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-sm font-semibold uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                        <Clock class="h-4 w-4" /> Record Info
                    </CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="flex items-start gap-3">
                        <User class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="text-xs text-muted-foreground">Added By</p>
                            <p class="text-sm font-medium">{{ employee.creator?.name ?? '—' }}</p>
                            <p class="text-xs text-muted-foreground">{{ formatDateTime(employee.created_at) }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <Pencil class="h-4 w-4 text-muted-foreground mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="text-xs text-muted-foreground">Last Modified By</p>
                            <p class="text-sm font-medium">{{ employee.updater?.name ?? '—' }}</p>
                            <p class="text-xs text-muted-foreground">{{ formatDateTime(employee.updated_at) }}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>

        </div>

    </ShopLayout>
</template>
