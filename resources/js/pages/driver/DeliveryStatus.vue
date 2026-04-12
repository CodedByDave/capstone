<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { onMounted } from 'vue'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import {
    MapPin, Phone, Package, Truck, CheckCircle2,
    XCircle, Clock, User, ChevronRight, Loader2,
} from 'lucide-vue-next'

// ─── Types ────────────────────────────────────────────────────────────────────

interface Delivery {
    id: number
    token: string
    status: string
    customer_name: string
    customer_phone: string | null
    delivery_address: string | null
    notes: string | null
    order_number: string | null
    shop_name: string | null
    rider_name: string | null
    assigned_at: string | null
    picked_up_at: string | null
    delivered_at: string | null
}

// ─── Props ────────────────────────────────────────────────────────────────────

const props = defineProps<{ delivery: Delivery }>()

// ─── Flash toast ──────────────────────────────────────────────────────────────

const page = usePage()
onMounted(() => {
    const flash = (page.props as any).toast as { type: string; message: string } | undefined
    if (!flash) return
    if (flash.type === 'success') toast.success(flash.message)
    else toast.error(flash.message)
})

// ─── Status config ────────────────────────────────────────────────────────────

const STATUS: Record<string, { label: string; color: string; icon: any }> = {
    pending:   { label: 'Pending',   color: 'text-amber-600 bg-amber-50 border-amber-200',   icon: Clock        },
    assigned:  { label: 'Assigned',  color: 'text-blue-600 bg-blue-50 border-blue-200',      icon: User         },
    picked_up: { label: 'Picked Up', color: 'text-violet-600 bg-violet-50 border-violet-200', icon: Truck       },
    delivered: { label: 'Delivered', color: 'text-green-600 bg-green-50 border-green-200',   icon: CheckCircle2 },
    failed:    { label: 'Failed',    color: 'text-red-600 bg-red-50 border-red-200',          icon: XCircle     },
}

// Timeline steps shown to the rider
const STEPS = [
    { key: 'assigned',  label: 'Assigned',  timeKey: 'assigned_at'  },
    { key: 'picked_up', label: 'Picked Up', timeKey: 'picked_up_at' },
    { key: 'delivered', label: 'Delivered', timeKey: 'delivered_at' },
]

const statusOrder = ['pending', 'assigned', 'picked_up', 'delivered', 'failed']

function stepDone(key: string) {
    return statusOrder.indexOf(props.delivery.status) >= statusOrder.indexOf(key)
}

// ─── Next action ─────────────────────────────────────────────────────────────

const NEXT: Record<string, { status: string; label: string; color: string }> = {
    assigned:  { status: 'picked_up', label: 'Mark as Picked Up',  color: 'bg-violet-600 hover:bg-violet-700' },
    picked_up: { status: 'delivered', label: 'Mark as Delivered',  color: 'bg-green-600 hover:bg-green-700'   },
}

const next = computed(() => NEXT[props.delivery.status] ?? null)

// ─── Fail action ──────────────────────────────────────────────────────────────

const showFailForm = ref(false)
const failNotes    = ref('')
const submitting   = ref(false)

function submitStatus(status: string, notes?: string) {
    submitting.value = true
    router.patch(`/driver/${props.delivery.token}/status`, {
        status,
        notes: notes || undefined,
    }, {
        preserveScroll: true,
        onFinish: () => { submitting.value = false; showFailForm.value = false },
    })
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

function fmt(iso: string | null) {
    if (!iso) return ''
    return new Date(iso).toLocaleString('en-PH', {
        month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}

function callCustomer() {
    if (props.delivery.customer_phone) {
        window.location.href = `tel:${props.delivery.customer_phone}`
    }
}
</script>

<template>
    <Head title="My Delivery" />

    <div class="min-h-screen bg-gray-50 flex flex-col">

        <!-- ── Header ─────────────────────────────────────────────────────── -->
        <div class="bg-white border-b px-4 py-4 flex items-center gap-3 shadow-sm">
            <div class="h-9 w-9 rounded-xl bg-primary/10 flex items-center justify-center">
                <Truck class="h-5 w-5 text-primary" />
            </div>
            <div>
                <p class="text-sm font-bold text-gray-900 leading-tight">Delivery</p>
                <p v-if="delivery.shop_name" class="text-xs text-gray-500">{{ delivery.shop_name }}</p>
            </div>
            <div class="ml-auto">
                <span class="text-xs px-2.5 py-1 rounded-full border font-medium"
                    :class="STATUS[delivery.status]?.color">
                    {{ STATUS[delivery.status]?.label }}
                </span>
            </div>
        </div>

        <div class="flex-1 px-4 py-5 space-y-4 max-w-md mx-auto w-full">

            <!-- ── Order ref ───────────────────────────────────────────────── -->
            <div v-if="delivery.order_number"
                class="flex items-center gap-2 text-xs text-gray-500 font-mono bg-white border rounded-xl px-4 py-2.5">
                <Package class="h-3.5 w-3.5 shrink-0" />
                Order #{{ delivery.order_number }}
            </div>

            <!-- ── Customer card ───────────────────────────────────────────── -->
            <div class="bg-white rounded-2xl border shadow-sm p-4 space-y-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Customer</p>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-900">{{ delivery.customer_name }}</p>
                        <p v-if="delivery.customer_phone" class="text-sm text-gray-500 flex items-center gap-1 mt-0.5">
                            <Phone class="h-3.5 w-3.5" /> {{ delivery.customer_phone }}
                        </p>
                    </div>
                    <!-- Call button -->
                    <button v-if="delivery.customer_phone"
                        @click="callCustomer"
                        class="h-10 w-10 rounded-full bg-green-600 text-white flex items-center justify-center shadow hover:bg-green-700 active:scale-95 transition-all">
                        <Phone class="h-4.5 w-4.5" />
                    </button>
                </div>

                <div v-if="delivery.delivery_address"
                    class="flex items-start gap-2 text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2.5">
                    <MapPin class="h-4 w-4 shrink-0 mt-0.5 text-gray-400" />
                    <span>{{ delivery.delivery_address }}</span>
                </div>

                <div v-if="delivery.notes"
                    class="text-xs text-gray-500 italic border-t pt-2">
                    Note: {{ delivery.notes }}
                </div>
            </div>

            <!-- ── Timeline ────────────────────────────────────────────────── -->
            <div class="bg-white rounded-2xl border shadow-sm p-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">Progress</p>
                <div class="space-y-3">
                    <div v-for="(step, i) in STEPS" :key="step.key" class="flex items-start gap-3">
                        <!-- Circle -->
                        <div class="flex flex-col items-center">
                            <div class="h-6 w-6 rounded-full border-2 flex items-center justify-center transition-colors"
                                :class="stepDone(step.key)
                                    ? 'bg-primary border-primary'
                                    : 'bg-white border-gray-200'">
                                <CheckCircle2 v-if="stepDone(step.key)" class="h-3.5 w-3.5 text-white" />
                            </div>
                            <!-- Connector line -->
                            <div v-if="i < STEPS.length - 1"
                                class="w-0.5 h-6 mt-1 transition-colors"
                                :class="stepDone(STEPS[i + 1].key) ? 'bg-primary' : 'bg-gray-200'" />
                        </div>
                        <!-- Label -->
                        <div class="pt-0.5">
                            <p class="text-sm font-medium" :class="stepDone(step.key) ? 'text-gray-900' : 'text-gray-400'">
                                {{ step.label }}
                            </p>
                            <p v-if="(delivery as any)[step.timeKey]" class="text-xs text-gray-400 mt-0.5">
                                {{ fmt((delivery as any)[step.timeKey]) }}
                            </p>
                        </div>
                    </div>

                    <!-- Failed state -->
                    <div v-if="delivery.status === 'failed'" class="flex items-start gap-3">
                        <div class="h-6 w-6 rounded-full bg-red-500 border-2 border-red-500 flex items-center justify-center">
                            <XCircle class="h-3.5 w-3.5 text-white" />
                        </div>
                        <div class="pt-0.5">
                            <p class="text-sm font-medium text-red-600">Failed</p>
                            <p v-if="delivery.notes" class="text-xs text-gray-400 mt-0.5">{{ delivery.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Action buttons ──────────────────────────────────────────── -->
            <div v-if="next && delivery.status !== 'delivered' && delivery.status !== 'failed'"
                class="space-y-2">

                <!-- Primary next action -->
                <button
                    :disabled="submitting"
                    @click="submitStatus(next.status)"
                    class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl text-white font-semibold text-sm shadow-md active:scale-[0.98] transition-all disabled:opacity-60"
                    :class="next.color">
                    <Loader2 v-if="submitting" class="h-4 w-4 animate-spin" />
                    <ChevronRight v-else class="h-4 w-4" />
                    {{ submitting ? 'Updating…' : next.label }}
                </button>

                <!-- Report failure -->
                <div v-if="!showFailForm">
                    <button @click="showFailForm = true"
                        class="w-full py-2.5 rounded-2xl border border-red-200 text-red-600 text-sm font-medium hover:bg-red-50 active:scale-[0.98] transition-all">
                        Report Failed Delivery
                    </button>
                </div>
                <div v-else class="bg-white border border-red-200 rounded-2xl p-4 space-y-3">
                    <p class="text-sm font-semibold text-red-600">Report Failed Delivery</p>
                    <textarea v-model="failNotes" rows="3" placeholder="Reason (e.g. customer not home, wrong address…)"
                        class="w-full text-sm border rounded-xl px-3 py-2 resize-none focus:outline-none focus:ring-2 focus:ring-red-300" />
                    <div class="flex gap-2">
                        <button @click="showFailForm = false"
                            class="flex-1 py-2 rounded-xl border text-sm font-medium text-gray-600 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button :disabled="submitting" @click="submitStatus('failed', failNotes)"
                            class="flex-1 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 disabled:opacity-60">
                            {{ submitting ? 'Saving…' : 'Confirm Failed' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Done state ──────────────────────────────────────────────── -->
            <div v-if="delivery.status === 'delivered'"
                class="bg-green-50 border border-green-200 rounded-2xl px-4 py-5 text-center">
                <CheckCircle2 class="h-10 w-10 text-green-500 mx-auto mb-2" />
                <p class="font-bold text-green-700">Delivery Complete!</p>
                <p class="text-xs text-green-600 mt-1">Great job. The customer has been notified.</p>
            </div>

            <div v-if="delivery.status === 'failed'"
                class="bg-red-50 border border-red-200 rounded-2xl px-4 py-5 text-center">
                <XCircle class="h-10 w-10 text-red-400 mx-auto mb-2" />
                <p class="font-bold text-red-700">Delivery Failed</p>
                <p class="text-xs text-red-500 mt-1">Please contact the shop for further instructions.</p>
            </div>

        </div>
    </div>
</template>
