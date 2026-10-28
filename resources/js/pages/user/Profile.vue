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
import UserLayout from '@/layouts/user/UserLayout.vue';
import type { AppPageProps } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Loader2, LogOut } from 'lucide-vue-next';
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

const profileDirty = computed(
    () =>
        form.value.name.trim() !== '' &&
        form.value.email.trim() !== '' &&
        (form.value.name !== user.value.name ||
            form.value.email !== user.value.email),
);

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

const passwordReady = computed(
    () =>
        passwordForm.value.current_password !== '' &&
        passwordForm.value.password.length >= 8 &&
        passwordForm.value.password_confirmation !== '',
);
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
        <div class="px-5 pt-6 pb-10">
            <!-- ── Avatar & name ─────────────────────────────── -->
            <div class="flex flex-col items-center text-center">
                <div
                    class="mb-3 flex h-20 w-20 items-center justify-center rounded-full border border-slate-200 font-display text-3xl font-bold text-blue-600"
                    aria-hidden="true"
                >
                    {{ user.name.charAt(0).toUpperCase() }}
                </div>
                <h1
                    class="font-display text-[22px] leading-7 font-bold text-slate-900"
                >
                    {{ user.name }}
                </h1>
                <p class="mt-0.5 text-sm text-slate-500">{{ user.email }}</p>
            </div>

            <!-- ── Your details ───────────────────────────────── -->
            <section class="mt-8">
                <h2
                    class="mb-4 text-[11px] font-medium tracking-[0.12em] text-slate-500 uppercase"
                >
                    Your details
                </h2>

                <div class="space-y-5">
                    <div>
                        <label
                            for="profile-name"
                            class="text-xs font-medium text-slate-500"
                            >Name</label
                        >
                        <input
                            id="profile-name"
                            v-model="form.name"
                            type="text"
                            autocomplete="name"
                            placeholder="Your full name"
                            class="mt-1 block min-h-11 w-full border-0 border-b bg-transparent px-0 text-[15px] text-slate-900 placeholder:text-slate-400 focus:ring-0 focus:outline-none"
                            :class="
                                errors.name
                                    ? 'border-red-400'
                                    : 'border-slate-200 focus:border-blue-600'
                            "
                        />
                        <p v-if="errors.name" class="mt-1 text-xs text-red-600">
                            {{ errors.name }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="profile-email"
                            class="text-xs font-medium text-slate-500"
                            >Email</label
                        >
                        <input
                            id="profile-email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            placeholder="Email address"
                            class="mt-1 block min-h-11 w-full border-0 border-b bg-transparent px-0 text-[15px] text-slate-900 placeholder:text-slate-400 focus:ring-0 focus:outline-none"
                            :class="
                                errors.email
                                    ? 'border-red-400'
                                    : 'border-slate-200 focus:border-blue-600'
                            "
                        />
                        <p
                            v-if="errors.email"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ errors.email }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="mt-6 flex min-h-12 w-full items-center justify-center gap-2 rounded-xl text-sm font-semibold transition"
                    :class="
                        profileDirty
                            ? 'bg-blue-600 text-white hover:bg-blue-700'
                            : 'cursor-not-allowed bg-slate-100 text-slate-400'
                    "
                    :disabled="!profileDirty || saving"
                    @click="saveProfile"
                >
                    <Loader2 v-if="saving" class="h-4 w-4 animate-spin" />
                    Save changes
                </button>
            </section>

            <!-- ── Password ───────────────────────────────────── -->
            <section class="mt-10">
                <h2
                    class="mb-4 text-[11px] font-medium tracking-[0.12em] text-slate-500 uppercase"
                >
                    Password
                </h2>

                <div class="space-y-5">
                    <div>
                        <label
                            for="current-password"
                            class="text-xs font-medium text-slate-500"
                            >Current password</label
                        >
                        <input
                            id="current-password"
                            v-model="passwordForm.current_password"
                            type="password"
                            autocomplete="current-password"
                            class="mt-1 block min-h-11 w-full border-0 border-b bg-transparent px-0 text-[15px] text-slate-900 focus:ring-0 focus:outline-none"
                            :class="
                                errors.current_password
                                    ? 'border-red-400'
                                    : 'border-slate-200 focus:border-blue-600'
                            "
                        />
                        <p
                            v-if="errors.current_password"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ errors.current_password }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="new-password"
                            class="text-xs font-medium text-slate-500"
                            >New password</label
                        >
                        <input
                            id="new-password"
                            v-model="passwordForm.password"
                            type="password"
                            autocomplete="new-password"
                            class="mt-1 block min-h-11 w-full border-0 border-b bg-transparent px-0 text-[15px] text-slate-900 focus:ring-0 focus:outline-none"
                            :class="
                                errors.password
                                    ? 'border-red-400'
                                    : 'border-slate-200 focus:border-blue-600'
                            "
                        />
                        <p
                            class="mt-1 text-xs"
                            :class="
                                errors.password
                                    ? 'text-red-600'
                                    : 'text-slate-500'
                            "
                        >
                            {{ errors.password || 'At least 8 characters.' }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="confirm-password"
                            class="text-xs font-medium text-slate-500"
                            >Confirm new password</label
                        >
                        <input
                            id="confirm-password"
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            class="mt-1 block min-h-11 w-full border-0 border-b border-slate-200 bg-transparent px-0 text-[15px] text-slate-900 focus:border-blue-600 focus:ring-0 focus:outline-none"
                        />
                    </div>
                </div>

                <button
                    type="button"
                    class="mt-6 flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border text-sm font-semibold transition"
                    :class="
                        passwordReady
                            ? 'border-blue-600 text-blue-600 hover:bg-blue-50'
                            : 'cursor-not-allowed border-slate-200 text-slate-400'
                    "
                    :disabled="!passwordReady || savingPwd"
                    @click="savePassword"
                >
                    <Loader2 v-if="savingPwd" class="h-4 w-4 animate-spin" />
                    Update password
                </button>
            </section>

            <!-- ── Sign out ───────────────────────────────────── -->
            <div class="mt-10 border-t border-slate-100">
                <button
                    type="button"
                    class="flex min-h-14 w-full items-center gap-2 text-sm font-semibold text-red-600 transition hover:text-red-700"
                    @click="logoutDialogOpen = true"
                >
                    <LogOut class="h-4 w-4" />
                    Sign out
                </button>
            </div>

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
