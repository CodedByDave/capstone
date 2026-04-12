<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import UserLayout from '@/layouts/user/UserLayout.vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import {
    User, Mail, Lock, LogOut, ChevronRight, CheckCircle2, Loader2,
} from 'lucide-vue-next'
import type { AppPageProps } from '@/types'

// ─── Props ────────────────────────────────────────────────────────────────────

defineProps<{
    status?: string
}>()

// ─── Auth ─────────────────────────────────────────────────────────────────────

const page  = usePage<AppPageProps>()
const user  = computed(() => page.props.auth.user)
const errors = computed(() => page.props.errors as Record<string, string>)

onMounted(() => {
    const flashToast = page.props.toast as { type: string; message: string } | undefined
    if (flashToast?.message) {
        toast.success(flashToast.message)
    }
})

// ─── Name / email form ────────────────────────────────────────────────────────

const saving = ref(false)
const form = ref({
    name:  user.value.name,
    email: user.value.email,
})

function saveProfile() {
    saving.value = true
    router.patch('/settings/profile', form.value, {
        preserveScroll: true,
        onSuccess: () => toast.success('Profile updated.'),
        onFinish: () => { saving.value = false },
    })
}

// ─── Password form ────────────────────────────────────────────────────────────

const passwordForm = ref({ current_password: '', password: '', password_confirmation: '' })
const savingPwd    = ref(false)

function savePassword() {
    savingPwd.value = true
    router.put('/settings/password', passwordForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Password updated.')
            passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
        },
        onFinish: () => { savingPwd.value = false },
    })
}

// ─── Logout ───────────────────────────────────────────────────────────────────

function logout() {
    router.post('/logout')
}
</script>

<template>
    <Head title="Profile" />
    <UserLayout>
        <div class="px-4 pt-4 pb-8 space-y-4">

            <!-- ── Avatar & name ─────────────────────────────── -->
            <div class="flex flex-col items-center py-6 bg-white rounded-2xl border border-gray-100 shadow-sm">
                <div class="h-16 w-16 rounded-full bg-blue-600 flex items-center justify-center mb-3">
                    <span class="text-2xl font-bold text-white">{{ user.name.charAt(0).toUpperCase() }}</span>
                </div>
                <p class="text-base font-bold text-gray-900">{{ user.name }}</p>
                <p class="text-sm text-gray-400">{{ user.email }}</p>
            </div>

            <!-- ── Edit profile ───────────────────────────────── -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Edit Profile</p>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700 flex items-center gap-1.5">
                        <User class="h-3.5 w-3.5 text-gray-400" /> Name
                    </label>
                    <Input v-model="form.name" placeholder="Your full name" :class="errors.name ? 'border-red-400' : ''" />
                    <p v-if="errors.name" class="text-xs text-red-500">{{ errors.name }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700 flex items-center gap-1.5">
                        <Mail class="h-3.5 w-3.5 text-gray-400" /> Email
                    </label>
                    <Input v-model="form.email" type="email" placeholder="Email address" :class="errors.email ? 'border-red-400' : ''" />
                    <p v-if="errors.email" class="text-xs text-red-500">{{ errors.email }}</p>
                </div>

                <Button class="w-full" :disabled="saving" @click="saveProfile">
                    <Loader2 v-if="saving" class="h-4 w-4 mr-2 animate-spin" />
                    <CheckCircle2 v-else class="h-4 w-4 mr-2" />
                    Save Changes
                </Button>
            </div>

            <!-- ── Change password ────────────────────────────── -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Change Password</p>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Current Password</label>
                    <Input v-model="passwordForm.current_password" type="password" placeholder="••••••••"
                        :class="errors.current_password ? 'border-red-400' : ''" />
                    <p v-if="errors.current_password" class="text-xs text-red-500">{{ errors.current_password }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">New Password</label>
                    <Input v-model="passwordForm.password" type="password" placeholder="••••••••"
                        :class="errors.password ? 'border-red-400' : ''" />
                    <p v-if="errors.password" class="text-xs text-red-500">{{ errors.password }}</p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Confirm New Password</label>
                    <Input v-model="passwordForm.password_confirmation" type="password" placeholder="••••••••" />
                </div>

                <Button variant="outline" class="w-full" :disabled="savingPwd" @click="savePassword">
                    <Loader2 v-if="savingPwd" class="h-4 w-4 mr-2 animate-spin" />
                    <Lock v-else class="h-4 w-4 mr-2" />
                    Update Password
                </Button>
            </div>

            <!-- ── Logout ─────────────────────────────────────── -->
            <button
                class="w-full flex items-center justify-between p-4 bg-white rounded-2xl border border-red-100 text-red-600 hover:bg-red-50 transition active:scale-[0.99]"
                @click="logout"
            >
                <div class="flex items-center gap-2 font-semibold text-sm">
                    <LogOut class="h-4 w-4" />
                    Sign out
                </div>
                <ChevronRight class="h-4 w-4 opacity-50" />
            </button>

        </div>
    </UserLayout>
</template>
