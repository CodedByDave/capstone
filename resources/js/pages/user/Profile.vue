<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import UserLayout from '@/layouts/user/UserLayout.vue';
import type { AppPageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    ChevronRight,
    Loader2,
    Lock,
    LogOut,
    Mail,
    User,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

// ─── Props ────────────────────────────────────────────────────────────────────

defineProps<{
    status?: string;
}>();

// ─── Auth ─────────────────────────────────────────────────────────────────────

const page = usePage<AppPageProps>();
const user = computed(() => page.props.auth.user);
const errors = computed(() => page.props.errors as Record<string, string>);

onMounted(() => {
    const flashToast = page.props.toast as
        | { type: string; message: string }
        | undefined;
    if (flashToast?.message) {
        toast.success(flashToast.message);
    }
});

// ─── Name / email form ────────────────────────────────────────────────────────

const saving = ref(false);
const form = ref({
    name: user.value.name,
    email: user.value.email,
});

function saveProfile() {
    saving.value = true;
    router.patch('/settings/profile', form.value, {
        preserveScroll: true,
        onSuccess: () => toast.success('Profile updated.'),
        onFinish: () => {
            saving.value = false;
        },
    });
}

// ─── Password form ────────────────────────────────────────────────────────────

const passwordForm = ref({
    current_password: '',
    password: '',
    password_confirmation: '',
});
const savingPwd = ref(false);
const logoutDialogOpen = ref(false);

function savePassword() {
    savingPwd.value = true;
    router.put('/settings/password', passwordForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Password updated.');
            passwordForm.value = {
                current_password: '',
                password: '',
                password_confirmation: '',
            };
        },
        onFinish: () => {
            savingPwd.value = false;
        },
    });
}

// ─── Logout ───────────────────────────────────────────────────────────────────

function logout() {
    router.post('/logout');
}
</script>

<template>
    <Head title="Profile" />
    <UserLayout>
        <div class="space-y-4 px-4 pt-4 pb-8">
            <!-- ── Avatar & name ─────────────────────────────── -->
            <div
                class="flex flex-col items-center rounded-2xl border border-gray-100 bg-white py-6 shadow-sm"
            >
                <div
                    class="mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-blue-600"
                >
                    <span class="text-2xl font-bold text-white">{{
                        user.name.charAt(0).toUpperCase()
                    }}</span>
                </div>
                <p class="text-base font-bold text-gray-900">{{ user.name }}</p>
                <p class="text-sm text-gray-400">{{ user.email }}</p>
            </div>

            <!-- ── Edit profile ───────────────────────────────── -->
            <div
                class="space-y-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
            >
                <p
                    class="text-xs font-semibold tracking-wide text-gray-400 uppercase"
                >
                    Edit Profile
                </p>

                <div class="space-y-1.5">
                    <label
                        class="flex items-center gap-1.5 text-sm font-medium text-gray-700"
                    >
                        <User class="h-3.5 w-3.5 text-gray-400" /> Name
                    </label>
                    <Input
                        v-model="form.name"
                        placeholder="Your full name"
                        :class="errors.name ? 'border-red-400' : ''"
                    />
                    <p v-if="errors.name" class="text-xs text-red-500">
                        {{ errors.name }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <label
                        class="flex items-center gap-1.5 text-sm font-medium text-gray-700"
                    >
                        <Mail class="h-3.5 w-3.5 text-gray-400" /> Email
                    </label>
                    <Input
                        v-model="form.email"
                        type="email"
                        placeholder="Email address"
                        :class="errors.email ? 'border-red-400' : ''"
                    />
                    <p v-if="errors.email" class="text-xs text-red-500">
                        {{ errors.email }}
                    </p>
                </div>

                <Button class="w-full" :disabled="saving" @click="saveProfile">
                    <Loader2 v-if="saving" class="mr-2 h-4 w-4 animate-spin" />
                    <CheckCircle2 v-else class="mr-2 h-4 w-4" />
                    Save Changes
                </Button>
            </div>

            <!-- ── Change password ────────────────────────────── -->
            <div
                class="space-y-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm"
            >
                <p
                    class="text-xs font-semibold tracking-wide text-gray-400 uppercase"
                >
                    Change Password
                </p>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700"
                        >Current Password</label
                    >
                    <Input
                        v-model="passwordForm.current_password"
                        type="password"
                        placeholder="••••••••"
                        :class="errors.current_password ? 'border-red-400' : ''"
                    />
                    <p
                        v-if="errors.current_password"
                        class="text-xs text-red-500"
                    >
                        {{ errors.current_password }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700"
                        >New Password</label
                    >
                    <Input
                        v-model="passwordForm.password"
                        type="password"
                        placeholder="••••••••"
                        :class="errors.password ? 'border-red-400' : ''"
                    />
                    <p v-if="errors.password" class="text-xs text-red-500">
                        {{ errors.password }}
                    </p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700"
                        >Confirm New Password</label
                    >
                    <Input
                        v-model="passwordForm.password_confirmation"
                        type="password"
                        placeholder="••••••••"
                    />
                </div>

                <Button
                    variant="outline"
                    class="w-full"
                    :disabled="savingPwd"
                    @click="savePassword"
                >
                    <Loader2
                        v-if="savingPwd"
                        class="mr-2 h-4 w-4 animate-spin"
                    />
                    <Lock v-else class="mr-2 h-4 w-4" />
                    Update Password
                </Button>
            </div>

            <!-- ── Logout ─────────────────────────────────────── -->
            <button
                class="flex w-full items-center justify-between rounded-2xl border border-red-100 bg-white p-4 text-red-600 transition hover:bg-red-50 active:scale-[0.99]"
                @click="logoutDialogOpen = true"
            >
                <div class="flex items-center gap-2 text-sm font-semibold">
                    <LogOut class="h-4 w-4" />
                    Sign out
                </div>
                <ChevronRight class="h-4 w-4 opacity-50" />
            </button>

            <AlertDialog v-model:open="logoutDialogOpen">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Log out?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Are you sure you want to log out of your account?
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction @click="logout"
                            >Log out</AlertDialogAction
                        >
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
    </UserLayout>
</template>
