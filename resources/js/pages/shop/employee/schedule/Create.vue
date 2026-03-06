<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'

// shadcn components
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

// icons
import { ArrowLeft, Calendar } from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Employee {
    id: number
    employee_id: string
    first_name: string
    last_name: string
    position: string
    hire_date: string
}

// ─── Props ────────────────────────────────────────────────────────────────────

const { employee } = defineProps<{ employee: Employee }>()

// ─── Breadcrumbs ──────────────────────────────────────────────────────────────

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Employee Management', href: '/shop/employee' },
    { title: `${employee.first_name} ${employee.last_name}`, href: `/shop/employee/${employee.id}` },
    { title: 'Add Schedule', href: '#' },
]

// ─── Form ─────────────────────────────────────────────────────────────────────

const form = useForm({
    work_date:  employee.hire_date,
    start_time: '',
    end_time:   '',
})

function submit() {
    form.post(`/shop/employee/${employee.id}/schedule`, {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Add Schedule" />

    <ShopLayout :breadcrumbs="breadcrumbs" title="Add Schedule">
        <div class="px-6 space-y-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <Button type="button" variant="outline"
                    @click="router.visit(`/shop/employee/${employee.id}`)">
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to Employee
                </Button>
            </div>

            <!-- Form Card -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-sm font-semibold uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                        <Calendar class="h-4 w-4" /> Schedule Details
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">

                        <!-- Work Date -->
                        <div class="space-y-2">
                            <Label for="work_date">Work Date</Label>
                            <Input
                                id="work_date"
                                type="date"
                                v-model="form.work_date"
                                :class="{ 'border-red-500': form.errors.work_date }"
                            />
                            <p v-if="form.errors.work_date" class="text-xs text-red-500">
                                {{ form.errors.work_date }}
                            </p>
                        </div>

                        <!-- Start Time -->
                        <div class="space-y-2">
                            <Label for="start_time">Start Time</Label>
                            <Input
                                id="start_time"
                                type="time"
                                v-model="form.start_time"
                                :class="{ 'border-red-500': form.errors.start_time }"
                            />
                            <p v-if="form.errors.start_time" class="text-xs text-red-500">
                                {{ form.errors.start_time }}
                            </p>
                        </div>

                        <!-- End Time -->
                        <div class="space-y-2">
                            <Label for="end_time">End Time</Label>
                            <Input
                                id="end_time"
                                type="time"
                                v-model="form.end_time"
                                :class="{ 'border-red-500': form.errors.end_time }"
                            />
                            <p v-if="form.errors.end_time" class="text-xs text-red-500">
                                {{ form.errors.end_time }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <Button type="button" variant="outline"
                                @click="router.visit(`/shop/employee/${employee.id}`)">
                                Cancel
                            </Button>
                            <Button type="submit" class="bg-blue-500 hover:bg-blue-700"
                                :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save Schedule' }}
                            </Button>
                        </div>

                    </form>
                </CardContent>
            </Card>

        </div>
    </ShopLayout>
</template>
