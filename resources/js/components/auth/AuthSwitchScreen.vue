<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import AuthPanelDecor from '@/components/auth/AuthPanelDecor.vue';
import {
    panelSideOf,
    rememberScreen,
    takePreviousScreen,
} from '@/components/auth/authPanelMemory';
import {
    eyeButtonClass,
    googleButtonClass,
    inputClass,
    labelClass,
    paneClass,
    panelButtonClass,
    panelCopyClass,
    primaryButtonClass,
    SLIDE_MS,
    textLinkClass,
} from '@/components/auth/authStyles';
import GoogleIcon from '@/components/auth/GoogleIcon.vue';
import InputError from '@/components/InputError.vue';
import { Spinner } from '@/components/ui/spinner';
import { useFlashToast } from '@/composables/useFlashToast';
import { store as loginStore } from '@/routes/login';
import { Form, Head, Link, router, useForm } from '@inertiajs/vue3';
import { CheckCircle2, Eye, EyeOff, Store, XCircle } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

interface GoogleUser {
    google_id: string;
    name: string;
    email: string;
    avatar: string | null;
}

interface ReferralShop {
    name: string;
    municipality: string | null;
    barangay: string | null;
}

type AuthMode = 'login' | 'register';

const props = defineProps<{
    mode: AuthMode;
    status?: string;
    canResetPassword?: boolean;
    googleUser?: GoogleUser | null;
    referralShop?: ReferralShop | null;
}>();

useFlashToast();

const urls: Record<AuthMode, string> = {
    login: '/login',
    register: '/register',
};

// `active` picks the form and its blue-panel text; `panelSide` is where the blue
// panel sits. After arriving from another auth screen they start where the
// visitor left them, then flip to this page's own values so the change animates.
const arrivedFrom = takePreviousScreen();
const active = ref<AuthMode>(
    arrivedFrom?.screen === 'login' || arrivedFrom?.screen === 'register'
        ? arrivedFrom.screen
        : props.mode,
);
const panelSide = ref(arrivedFrom?.side ?? panelSideOf[props.mode]);

const loginPane = ref<HTMLElement | null>(null);
const registerPane = ref<HTMLElement | null>(null);
let focusTimer: number | undefined;

function focusFirstField() {
    const pane = props.mode === 'login' ? loginPane.value : registerPane.value;
    pane?.querySelector<HTMLInputElement>('input:not([readonly])')?.focus();
}

onMounted(() => {
    const other: AuthMode = props.mode === 'login' ? 'register' : 'login';
    router.prefetch(urls[other], { method: 'get' }, { cacheFor: '30s' });

    if (
        active.value === props.mode &&
        panelSide.value === panelSideOf[props.mode]
    ) {
        focusFirstField();
        return;
    }

    requestAnimationFrame(() =>
        requestAnimationFrame(() => {
            active.value = props.mode;
            panelSide.value = panelSideOf[props.mode];
            focusTimer = window.setTimeout(focusFirstField, SLIDE_MS);
        }),
    );
});

onBeforeUnmount(() => {
    window.clearTimeout(focusTimer);
    rememberScreen(props.mode);
});

function switchTo(target: AuthMode) {
    router.visit(urls[target]);
}

/* ---------------- Sign in ---------------- */
const showLoginPassword = ref(false);
const agreedToTerms = ref(false);

function loginWithGoogle() {
    window.location.href = '/auth/google';
}

/* ---------------- Create account ---------------- */
const form = useForm({
    name: props.googleUser?.name ?? '',
    email: props.googleUser?.email ?? '',
    password: '',
    google_id: props.googleUser?.google_id ?? '',
});

const showRegisterPassword = ref(false);
const isGoogleUser = computed(() => !!form.google_id);

const passwordRules = computed(() => [
    { label: 'At least 8 characters', pass: form.password.length >= 8 },
    {
        label: 'Contains a letter and number',
        pass: /[A-Za-z]/.test(form.password) && /\d/.test(form.password),
    },
    {
        label: 'Contains a special character',
        pass: /[^A-Za-z0-9]/.test(form.password),
    },
]);

function submitRegistration() {
    // The form has a single password field with a show/hide toggle, so the
    // confirmation the server expects is the same value.
    form.transform((data) => ({
        ...data,
        password_confirmation: data.password,
    })).post('/register');
}

function registerWithGoogle() {
    window.location.href = '/auth/google?register_as=user';
}

const hiddenPaneClass = 'max-lg:hidden lg:pointer-events-none lg:opacity-0';
</script>

<template>
    <Head :title="mode === 'login' ? 'Sign in' : 'Create account'" />

    <div
        class="relative min-h-svh overflow-hidden bg-white font-customer text-slate-900 lg:grid lg:h-svh lg:grid-cols-2"
    >
        <!-- Sign in: left half -->
        <section
            ref="loginPane"
            aria-labelledby="login-title"
            :inert="active !== 'login'"
            :class="[paneClass, active === 'login' ? '' : hiddenPaneClass]"
        >
            <div class="m-auto w-full max-w-[400px]">
                <div class="flex flex-col items-center text-center">
                    <Link href="/" aria-label="LaundryHub home">
                        <AppLogoIcon />
                    </Link>
                    <h1
                        id="login-title"
                        class="mt-2 font-display text-4xl font-bold tracking-tight"
                    >
                        Sign in
                    </h1>
                    <p class="mt-2 text-[15px] text-slate-500">
                        Orders, pickups and laundry, all in one place.
                    </p>
                </div>

                <p
                    v-if="status"
                    role="status"
                    class="mt-4 text-center text-sm font-medium text-green-700"
                >
                    {{ status }}
                </p>

                <Form
                    :action="loginStore.url()"
                    method="post"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="mt-6 space-y-5"
                >
                    <div class="space-y-2">
                        <label for="login-email" :class="labelClass"
                            >Email address</label
                        >
                        <input
                            id="login-email"
                            type="email"
                            name="email"
                            required
                            autocomplete="email"
                            placeholder="you@example.com"
                            :class="inputClass"
                        />
                        <InputError
                            :message="
                                errors.email
                                    ? 'Email or password is invalid.'
                                    : ''
                            "
                        />
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label for="login-password" :class="labelClass"
                                >Password</label
                            >
                            <Link
                                v-if="canResetPassword"
                                href="/forgot-password"
                                :class="[textLinkClass, 'text-sm']"
                            >
                                Forgot password?
                            </Link>
                        </div>
                        <div class="relative">
                            <input
                                id="login-password"
                                :type="showLoginPassword ? 'text' : 'password'"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                :class="[inputClass, 'pr-12']"
                            />
                            <button
                                type="button"
                                :class="eyeButtonClass"
                                :aria-label="
                                    showLoginPassword
                                        ? 'Hide password'
                                        : 'Show password'
                                "
                                :aria-pressed="showLoginPassword"
                                @click="showLoginPassword = !showLoginPassword"
                            >
                                <EyeOff
                                    v-if="showLoginPassword"
                                    class="size-5"
                                />
                                <Eye v-else class="size-5" />
                            </button>
                        </div>
                        <InputError :message="errors.password" />
                    </div>

                    <label
                        for="login-terms"
                        class="flex cursor-pointer items-start gap-3 text-sm leading-relaxed text-slate-500"
                    >
                        <input
                            id="login-terms"
                            v-model="agreedToTerms"
                            type="checkbox"
                            name="terms"
                            required
                            class="mt-0.5 size-4 shrink-0 accent-blue-600 focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 focus-visible:outline-none"
                        />
                        <span>
                            By logging in, you agree to our
                            <a
                                href="/terms"
                                target="_blank"
                                rel="noopener noreferrer"
                                :class="[textLinkClass, 'underline']"
                                >Terms of Service</a
                            >
                            and
                            <a
                                href="/privacy"
                                target="_blank"
                                rel="noopener noreferrer"
                                :class="[textLinkClass, 'underline']"
                                >Privacy Policy</a
                            >.
                        </span>
                    </label>

                    <button
                        type="submit"
                        :class="primaryButtonClass"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" />
                        Sign in
                    </button>

                    <div class="flex items-center gap-4" aria-hidden="true">
                        <span class="h-px flex-1 bg-slate-200" />
                        <span class="text-sm text-slate-500">or</span>
                        <span class="h-px flex-1 bg-slate-200" />
                    </div>

                    <button
                        type="button"
                        :class="[
                            googleButtonClass,
                            !agreedToTerms && 'cursor-not-allowed opacity-60',
                        ]"
                        :disabled="!agreedToTerms"
                        @click="loginWithGoogle"
                    >
                        <GoogleIcon />
                        Continue with Google
                    </button>
                </Form>

                <p class="mt-6 text-center text-sm text-slate-500 lg:hidden">
                    New here?
                    <button
                        type="button"
                        :class="textLinkClass"
                        @click="switchTo('register')"
                    >
                        Sign up
                    </button>
                </p>
            </div>
        </section>

        <!-- Create account: right half -->
        <section
            ref="registerPane"
            aria-labelledby="register-title"
            :inert="active !== 'register'"
            :class="[paneClass, active === 'register' ? '' : hiddenPaneClass]"
        >
            <div class="m-auto w-full max-w-[400px]">
                <div class="flex flex-col items-center text-center">
                    <Link href="/" aria-label="LaundryHub home">
                        <AppLogoIcon />
                    </Link>
                    <h1
                        id="register-title"
                        class="mt-2 font-display text-4xl font-bold tracking-tight"
                    >
                        Create account
                    </h1>
                    <p class="mt-2 max-w-sm text-[15px] text-slate-500">
                        Create an account to manage orders and pickups with
                        ease.
                    </p>
                </div>

                <!-- Shop the customer scanned: it becomes their home shop. Signing up does not need one. -->
                <div
                    v-if="referralShop"
                    class="mt-5 flex items-center gap-3 rounded-xl bg-blue-50 px-4 py-3"
                >
                    <Store
                        class="size-5 shrink-0 text-blue-600"
                        aria-hidden="true"
                    />
                    <div class="min-w-0">
                        <p class="text-xs text-blue-700">Your home shop</p>
                        <p class="truncate text-sm font-semibold text-blue-900">
                            {{ referralShop.name }}
                        </p>
                        <p
                            v-if="
                                referralShop.barangay ||
                                referralShop.municipality
                            "
                            class="truncate text-xs text-blue-700"
                        >
                            {{
                                [
                                    referralShop.barangay,
                                    referralShop.municipality,
                                ]
                                    .filter(Boolean)
                                    .join(', ')
                            }}
                        </p>
                    </div>
                </div>

                <!-- Google pre-fill banner -->
                <div
                    v-if="isGoogleUser"
                    class="mt-5 flex items-center gap-3 rounded-xl bg-blue-50 px-4 py-3"
                >
                    <img
                        v-if="googleUser?.avatar"
                        :src="googleUser.avatar"
                        alt=""
                        class="size-8 rounded-full"
                    />
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-blue-900">
                            {{ googleUser?.name }}
                        </p>
                        <p class="truncate text-xs text-blue-700">
                            Signed in with Google
                        </p>
                    </div>
                </div>

                <form
                    class="mt-6 space-y-5"
                    @submit.prevent="submitRegistration"
                >
                    <div class="space-y-2">
                        <label for="register-name" :class="labelClass"
                            >Full name</label
                        >
                        <input
                            id="register-name"
                            v-model="form.name"
                            type="text"
                            required
                            autocomplete="name"
                            placeholder="Your full name"
                            :class="inputClass"
                            :aria-invalid="!!form.errors.name"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <label for="register-email" :class="labelClass"
                            >Email address</label
                        >
                        <input
                            id="register-email"
                            v-model="form.email"
                            type="email"
                            required
                            autocomplete="email"
                            placeholder="you@example.com"
                            :readonly="isGoogleUser"
                            :class="[
                                inputClass,
                                isGoogleUser
                                    ? 'cursor-not-allowed text-slate-500'
                                    : '',
                            ]"
                            :aria-invalid="!!form.errors.email"
                        />
                        <p v-if="isGoogleUser" class="text-xs text-slate-500">
                            Email is set by your Google account
                        </p>
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Google accounts have no password to set -->
                    <div v-if="!isGoogleUser" class="space-y-2">
                        <label for="register-password" :class="labelClass"
                            >Password</label
                        >
                        <div class="relative">
                            <input
                                id="register-password"
                                v-model="form.password"
                                :type="
                                    showRegisterPassword ? 'text' : 'password'
                                "
                                required
                                minlength="8"
                                autocomplete="new-password"
                                placeholder="Create a password"
                                :class="[inputClass, 'pr-12']"
                                :aria-invalid="!!form.errors.password"
                            />
                            <button
                                type="button"
                                :class="eyeButtonClass"
                                :aria-label="
                                    showRegisterPassword
                                        ? 'Hide password'
                                        : 'Show password'
                                "
                                :aria-pressed="showRegisterPassword"
                                @click="
                                    showRegisterPassword = !showRegisterPassword
                                "
                            >
                                <EyeOff
                                    v-if="showRegisterPassword"
                                    class="size-5"
                                />
                                <Eye v-else class="size-5" />
                            </button>
                        </div>

                        <ul v-if="form.password.length > 0" class="space-y-1">
                            <li
                                v-for="rule in passwordRules"
                                :key="rule.label"
                                class="flex items-center gap-1.5 text-xs"
                                :class="
                                    rule.pass
                                        ? 'text-green-700'
                                        : 'text-slate-500'
                                "
                            >
                                <CheckCircle2
                                    v-if="rule.pass"
                                    class="size-3.5 shrink-0"
                                    aria-hidden="true"
                                />
                                <XCircle
                                    v-else
                                    class="size-3.5 shrink-0"
                                    aria-hidden="true"
                                />
                                {{ rule.label }}
                            </li>
                        </ul>

                        <InputError :message="form.errors.password" />
                    </div>

                    <button
                        type="submit"
                        :class="primaryButtonClass"
                        :disabled="form.processing"
                    >
                        <Spinner v-if="form.processing" />
                        {{ form.processing ? 'Creating account' : 'Sign up' }}
                    </button>

                    <template v-if="!isGoogleUser">
                        <div class="flex items-center gap-4" aria-hidden="true">
                            <span class="h-px flex-1 bg-slate-200" />
                            <span class="text-sm text-slate-500">or</span>
                            <span class="h-px flex-1 bg-slate-200" />
                        </div>

                        <button
                            type="button"
                            :class="googleButtonClass"
                            @click="registerWithGoogle"
                        >
                            <GoogleIcon />
                            Continue with Google
                        </button>
                    </template>
                </form>

                <p class="mt-6 text-center text-sm text-slate-500">
                    Own a laundry shop?
                    <Link href="/register/shop" :class="textLinkClass"
                        >Register your shop</Link
                    >
                </p>
                <p class="mt-3 text-center text-sm text-slate-500 lg:hidden">
                    Already have an account?
                    <button
                        type="button"
                        :class="textLinkClass"
                        @click="switchTo('login')"
                    >
                        Sign in
                    </button>
                </p>
            </div>
        </section>

        <!-- Blue panel: covers whichever half is not in use and slides across on a switch -->
        <aside
            class="absolute inset-y-0 left-0 z-10 hidden w-1/2 overflow-hidden bg-blue-600 text-white transition-transform duration-700 ease-[cubic-bezier(0.65,0,0.35,1)] motion-reduce:transition-none lg:block"
            :class="
                panelSide === 'right' ? 'translate-x-full' : 'translate-x-0'
            "
        >
            <AuthPanelDecor />

            <div
                :inert="active !== 'login'"
                :class="[
                    panelCopyClass,
                    active === 'login'
                        ? 'opacity-100'
                        : 'pointer-events-none translate-x-8 opacity-0',
                ]"
            >
                <h2 class="font-display text-3xl font-bold tracking-tight">
                    Hello, there!
                </h2>
                <p class="mt-3 max-w-[310px] text-[15px] leading-relaxed">
                    New here? Join us and make laundry day simple, for shops and
                    customers alike.
                </p>
                <button
                    type="button"
                    :class="panelButtonClass"
                    @click="switchTo('register')"
                >
                    Sign up
                </button>
            </div>

            <div
                :inert="active !== 'register'"
                :class="[
                    panelCopyClass,
                    active === 'register'
                        ? 'opacity-100'
                        : 'pointer-events-none -translate-x-8 opacity-0',
                ]"
            >
                <h2 class="font-display text-3xl font-bold tracking-tight">
                    Welcome back!
                </h2>
                <p class="mt-3 max-w-[310px] text-[15px] leading-relaxed">
                    Already have an account? Sign in and pick up where you left
                    off.
                </p>
                <button
                    type="button"
                    :class="panelButtonClass"
                    @click="switchTo('login')"
                >
                    Sign in
                </button>
            </div>
        </aside>
    </div>
</template>
