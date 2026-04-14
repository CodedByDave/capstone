<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import { Button } from '@/components/ui/button'
import { Smartphone, CreditCard, Upload, Trash2, Loader2, QrCode } from 'lucide-vue-next'

const props = defineProps<{
    gcash_qr: string | null
    maya_qr:  string | null
}>()

const page = usePage()

onMounted(() => {
    const flashToast = (page.props as any).toast as { type: string; message: string } | undefined
    if (flashToast?.type === 'success') toast.success(flashToast.message)
    else if (flashToast?.type === 'error') toast.error(flashToast.message)
})

// ─── State ────────────────────────────────────────────────────────────────────

const submitting = ref(false)

const gcashFile    = ref<File | null>(null)
const mayaFile     = ref<File | null>(null)
const gcashPreview = ref<string | null>(props.gcash_qr)
const mayaPreview  = ref<string | null>(props.maya_qr)
const removeGcash  = ref(false)
const removeMaya   = ref(false)

// ─── File handlers ────────────────────────────────────────────────────────────

function onFileChange(method: 'gcash' | 'maya', e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0]
    if (!file) return

    if (method === 'gcash') {
        gcashFile.value    = file
        gcashPreview.value = URL.createObjectURL(file)
        removeGcash.value  = false
    } else {
        mayaFile.value    = file
        mayaPreview.value = URL.createObjectURL(file)
        removeMaya.value  = false
    }
}

function clearQr(method: 'gcash' | 'maya') {
    if (method === 'gcash') {
        gcashFile.value    = null
        gcashPreview.value = null
        removeGcash.value  = true
    } else {
        mayaFile.value    = null
        mayaPreview.value = null
        removeMaya.value  = true
    }
}

// ─── Submit ───────────────────────────────────────────────────────────────────

function submit() {
    submitting.value = true

    const data = new FormData()
    if (gcashFile.value) data.append('gcash_qr', gcashFile.value)
    if (mayaFile.value)  data.append('maya_qr',  mayaFile.value)
    if (removeGcash.value) data.append('remove_gcash_qr', '1')
    if (removeMaya.value)  data.append('remove_maya_qr',  '1')

    router.post('/shop/payment-qr', data, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { toast.success('QR codes saved successfully.') },
        onError:   () => { toast.error('Failed to save QR codes. Please try again.') },
        onFinish:  () => { submitting.value = false },
    })
}
</script>

<template>
    <Head title="Payment QR Codes" />
    <ShopLayout>
        <div class="max-w-2xl mx-auto px-4 py-8 space-y-6">

            <!-- Header -->
            <div>
                <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <QrCode class="h-5 w-5 text-blue-600" />
                    Payment QR Codes
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Upload your GCash and Maya QR codes. Customers will see these when they choose online payment.
                </p>
            </div>

            <!-- Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                <!-- GCash -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="h-9 w-9 rounded-xl bg-blue-100 flex items-center justify-center">
                            <Smartphone class="h-4 w-4 text-blue-600" />
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">GCash</p>
                            <p class="text-xs text-gray-400">PNG / JPG, max 2 MB</p>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="flex items-center justify-center h-44 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 overflow-hidden relative group">
                        <img v-if="gcashPreview" :src="gcashPreview" alt="GCash QR"
                            class="h-full w-full object-contain p-2" />
                        <div v-else class="flex flex-col items-center gap-1 text-gray-300">
                            <QrCode class="h-10 w-10" />
                            <p class="text-xs">No QR uploaded</p>
                        </div>

                        <!-- Remove overlay -->
                        <button v-if="gcashPreview" type="button"
                            class="absolute top-2 right-2 h-7 w-7 rounded-full bg-red-500 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition"
                            @click="clearQr('gcash')">
                            <Trash2 class="h-3.5 w-3.5" />
                        </button>
                    </div>

                    <label class="block">
                        <span class="sr-only">Upload GCash QR</span>
                        <input type="file" accept="image/*" class="hidden" @change="onFileChange('gcash', $event)" />
                        <Button type="button" variant="outline" size="sm" class="w-full gap-2"
                            @click="($event.target as HTMLElement).previousElementSibling?.click()">
                            <Upload class="h-3.5 w-3.5" />
                            {{ gcashPreview ? 'Replace QR' : 'Upload QR' }}
                        </Button>
                    </label>
                </div>

                <!-- Maya -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="h-9 w-9 rounded-xl bg-green-100 flex items-center justify-center">
                            <CreditCard class="h-4 w-4 text-green-600" />
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">Maya</p>
                            <p class="text-xs text-gray-400">PNG / JPG, max 2 MB</p>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="flex items-center justify-center h-44 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 overflow-hidden relative group">
                        <img v-if="mayaPreview" :src="mayaPreview" alt="Maya QR"
                            class="h-full w-full object-contain p-2" />
                        <div v-else class="flex flex-col items-center gap-1 text-gray-300">
                            <QrCode class="h-10 w-10" />
                            <p class="text-xs">No QR uploaded</p>
                        </div>

                        <!-- Remove overlay -->
                        <button v-if="mayaPreview" type="button"
                            class="absolute top-2 right-2 h-7 w-7 rounded-full bg-red-500 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition"
                            @click="clearQr('maya')">
                            <Trash2 class="h-3.5 w-3.5" />
                        </button>
                    </div>

                    <label class="block">
                        <span class="sr-only">Upload Maya QR</span>
                        <input type="file" accept="image/*" class="hidden" @change="onFileChange('maya', $event)" />
                        <Button type="button" variant="outline" size="sm" class="w-full gap-2"
                            @click="($event.target as HTMLElement).previousElementSibling?.click()">
                            <Upload class="h-3.5 w-3.5" />
                            {{ mayaPreview ? 'Replace QR' : 'Upload QR' }}
                        </Button>
                    </label>
                </div>

            </div>

            <!-- Save -->
            <div class="flex justify-end">
                <Button :disabled="submitting" class="gap-2 px-6" @click="submit">
                    <Loader2 v-if="submitting" class="h-4 w-4 animate-spin" />
                    {{ submitting ? 'Saving…' : 'Save QR Codes' }}
                </Button>
            </div>

        </div>
    </ShopLayout>
</template>
