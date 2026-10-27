<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    BellRing,
    CheckCircle2,
    Loader2,
    Lock,
    Scale,
    Wallet,
} from 'lucide-vue-next';
import { computed, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

interface Settings {
    start_on_weigh_in: boolean;
    start_on_payment: boolean;
    auto_complete: boolean;
    auto_complete_grace_minutes: number;
    remind_overdue: boolean;
}

const props = defineProps<{
    settings: Settings;
    available: boolean;
    limits: { max_grace_minutes: number };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Operations', href: '/shop/operations/orders' },
    { title: 'Order Automation', href: '/shop/operations/order-automation' },
];

const form = useForm<Settings>({ ...props.settings });

const page = usePage();
watch(
    () => page.props.toast as { type: string; message: string } | undefined,
    (t) => {
        if (!t) return;
        if (t.type === 'success') toast.success(t.message);
        if (t.type === 'error') toast.error(t.message);
    },
    { immediate: true },
);

type RuleKey = 'start_on_weigh_in' | 'start_on_payment' | 'remind_overdue';

const startRules: {
    key: RuleKey;
    title: string;
    description: string;
    icon: typeof Scale;
}[] = [
    {
        key: 'start_on_weigh_in',
        title: 'Start when the weight is confirmed',
        description:
            'A pending order moves to In Progress as soon as staff enter its actual weight.',
        icon: Scale,
    },
    {
        key: 'start_on_payment',
        title: 'Start on the first payment',
        description:
            'A pending order moves to In Progress when its first payment is recorded or paid online.',
        icon: Wallet,
    },
];

const presets = [
    { label: '30 min', value: 30 },
    { label: '1 hr', value: 60 },
    { label: '2 hr', value: 120 },
    { label: '4 hr', value: 240 },
];

const rulesOn = computed(
    () =>
        [
            form.start_on_weigh_in,
            form.start_on_payment,
            form.auto_complete,
            form.remind_overdue,
        ].filter(Boolean).length,
);

function discard() {
    form.reset();
    form.clearErrors();
}

function submit() {
    form.put('/shop/operations/order-automation', { preserveScroll: true });
}
</script>

<template>
    <Head title="Order Automation" />
    <ShopLayout title="Order Automation" :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-6xl space-y-6 px-6 pb-10">
            <p class="max-w-2xl text-sm text-muted-foreground">
                Let the system move orders along for you. Every rule is off
                until you turn it on. Staff can still change any status by hand,
                and each automatic change is saved in your activity log.
            </p>

            <Alert v-if="!available" class="border-amber-300 bg-amber-50">
                <Lock class="h-4 w-4" aria-hidden="true" />
                <AlertTitle>Available on paid plans</AlertTitle>
                <AlertDescription>
                    Order automation is included with a paid Basic, Standard or
                    Premium plan. A free trial does not include it, and it
                    pauses if your plan expires.
                    <Link
                        href="/shop/upgrade"
                        class="font-medium underline underline-offset-2"
                    >
                        View plans
                    </Link>
                </AlertDescription>
            </Alert>

            <div class="grid items-start gap-6 lg:grid-cols-[1fr_20rem]">
                <fieldset :disabled="!available" class="min-w-0 space-y-3">
                    <legend
                        class="mb-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                    >
                        Pending
                        <span aria-hidden="true">→</span>
                        <span class="text-foreground">In progress</span>
                    </legend>

                    <div
                        v-for="rule in startRules"
                        :key="rule.key"
                        class="flex items-center gap-4 rounded-xl border p-4 transition-colors"
                        :class="[
                            form[rule.key] ? 'bg-primary/5' : 'bg-card',
                            { 'opacity-60': !available },
                        ]"
                    >
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-muted"
                        >
                            <component
                                :is="rule.icon"
                                class="h-5 w-5"
                                :class="
                                    form[rule.key]
                                        ? 'text-primary'
                                        : 'text-muted-foreground'
                                "
                                aria-hidden="true"
                            />
                        </span>
                        <div class="min-w-0 flex-1">
                            <label
                                :for="`rule-${rule.key}`"
                                class="block text-sm font-semibold"
                            >
                                {{ rule.title }}
                            </label>
                            <p class="text-sm text-muted-foreground">
                                {{ rule.description }}
                            </p>
                        </div>
                        <button
                            :id="`rule-${rule.key}`"
                            type="button"
                            role="switch"
                            :aria-checked="form[rule.key]"
                            class="switch"
                            :class="{ 'switch-on': form[rule.key] }"
                            @click="form[rule.key] = !form[rule.key]"
                        >
                            <span class="switch-thumb" />
                        </button>
                    </div>

                    <div
                        class="mt-6 mb-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                    >
                        In progress
                        <span aria-hidden="true">→</span>
                        <span class="text-foreground">Completed</span>
                    </div>

                    <div
                        class="rounded-xl border p-4 transition-colors"
                        :class="[
                            form.auto_complete ? 'bg-primary/5' : 'bg-card',
                            { 'opacity-60': !available },
                        ]"
                    >
                        <div class="flex items-center gap-4">
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-muted"
                            >
                                <CheckCircle2
                                    class="h-5 w-5"
                                    :class="
                                        form.auto_complete
                                            ? 'text-primary'
                                            : 'text-muted-foreground'
                                    "
                                    aria-hidden="true"
                                />
                            </span>
                            <div class="min-w-0 flex-1">
                                <label
                                    for="rule-auto_complete"
                                    class="block text-sm font-semibold"
                                >
                                    Complete orders after their due time
                                </label>
                                <p class="text-sm text-muted-foreground">
                                    An in-progress order is marked Completed
                                    once its due time and the wait below have
                                    passed.
                                </p>
                            </div>
                            <button
                                id="rule-auto_complete"
                                type="button"
                                role="switch"
                                :aria-checked="form.auto_complete"
                                class="switch"
                                :class="{ 'switch-on': form.auto_complete }"
                                @click="
                                    form.auto_complete = !form.auto_complete
                                "
                            >
                                <span class="switch-thumb" />
                            </button>
                        </div>

                        <div class="mt-4 pl-14">
                            <label
                                for="grace-minutes"
                                class="text-sm font-semibold"
                            >
                                Wait after the due time
                            </label>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <div class="relative w-44">
                                    <Input
                                        id="grace-minutes"
                                        v-model.number="
                                            form.auto_complete_grace_minutes
                                        "
                                        type="number"
                                        min="0"
                                        :max="limits.max_grace_minutes"
                                        step="1"
                                        class="h-11 pr-20"
                                        :disabled="!form.auto_complete"
                                    />
                                    <span
                                        class="pointer-events-none absolute top-1/2 right-3 -translate-y-1/2 text-sm text-muted-foreground"
                                    >
                                        minutes
                                    </span>
                                </div>
                                <button
                                    v-for="preset in presets"
                                    :key="preset.value"
                                    type="button"
                                    class="min-h-11 rounded-full border px-4 text-sm font-medium transition-colors hover:bg-muted focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:opacity-50"
                                    :class="
                                        form.auto_complete_grace_minutes ===
                                        preset.value
                                            ? 'border-primary bg-primary/10 text-primary'
                                            : ''
                                    "
                                    :disabled="!form.auto_complete"
                                    :aria-pressed="
                                        form.auto_complete_grace_minutes ===
                                        preset.value
                                    "
                                    @click="
                                        form.auto_complete_grace_minutes =
                                            preset.value
                                    "
                                >
                                    {{ preset.label }}
                                </button>
                            </div>
                            <p
                                v-if="form.errors.auto_complete_grace_minutes"
                                class="mt-2 text-xs text-red-500"
                                role="alert"
                            >
                                {{ form.errors.auto_complete_grace_minutes }}
                            </p>
                            <p
                                v-else
                                class="mt-2 text-xs text-muted-foreground"
                            >
                                0 to {{ limits.max_grace_minutes }} minutes. A
                                longer wait gives staff time to finish first.
                            </p>
                            <details class="mt-3 text-sm">
                                <summary
                                    class="cursor-pointer font-medium focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                >
                                    How the due time is worked out
                                </summary>
                                <p class="mt-2 text-muted-foreground">
                                    New orders get their due time from the wait
                                    above, counted from when they are created.
                                    If an order has none, it is when work
                                    started plus the service's estimated hours.
                                    Orders with neither, or without a confirmed
                                    weight, are never completed automatically.
                                </p>
                            </details>
                        </div>
                    </div>

                    <div
                        class="mt-6 mb-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                    >
                        Reminders
                    </div>

                    <div
                        class="flex items-center gap-4 rounded-xl border p-4 transition-colors"
                        :class="[
                            form.remind_overdue ? 'bg-primary/5' : 'bg-card',
                            { 'opacity-60': !available },
                        ]"
                    >
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-muted"
                        >
                            <BellRing
                                class="h-5 w-5"
                                :class="
                                    form.remind_overdue
                                        ? 'text-primary'
                                        : 'text-muted-foreground'
                                "
                                aria-hidden="true"
                            />
                        </span>
                        <div class="min-w-0 flex-1">
                            <label
                                for="rule-remind_overdue"
                                class="block text-sm font-semibold"
                            >
                                Remind me about overdue orders
                            </label>
                            <p class="text-sm text-muted-foreground">
                                You get one notification when an order is still
                                in progress after its due time.
                            </p>
                        </div>
                        <button
                            id="rule-remind_overdue"
                            type="button"
                            role="switch"
                            :aria-checked="form.remind_overdue"
                            class="switch"
                            :class="{ 'switch-on': form.remind_overdue }"
                            @click="form.remind_overdue = !form.remind_overdue"
                        >
                            <span class="switch-thumb" />
                        </button>
                    </div>
                </fieldset>

                <aside
                    class="space-y-4 rounded-xl border bg-card p-5 lg:sticky lg:top-6"
                    aria-label="Order lifecycle"
                >
                    <div class="flex items-center justify-between">
                        <h2
                            class="text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            Order lifecycle
                        </h2>
                        <span
                            class="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary"
                        >
                            {{ rulesOn }} of 4 rules on
                        </span>
                    </div>

                    <ol class="relative space-y-5 pl-7">
                        <span
                            class="absolute top-2 bottom-2 left-[7px] w-px bg-border"
                            aria-hidden="true"
                        />
                        <li class="relative">
                            <span class="dot" aria-hidden="true" />
                            <p class="text-sm font-semibold">Pending</p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span
                                    class="chip"
                                    :class="{
                                        'chip-on': form.start_on_weigh_in,
                                    }"
                                >
                                    Weight confirmed
                                </span>
                                <span
                                    class="chip"
                                    :class="{
                                        'chip-on': form.start_on_payment,
                                    }"
                                >
                                    First payment
                                </span>
                            </div>
                        </li>
                        <li class="relative">
                            <span class="dot" aria-hidden="true" />
                            <p class="text-sm font-semibold">In progress</p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span
                                    class="chip"
                                    :class="{ 'chip-on': form.auto_complete }"
                                >
                                    Auto-complete after
                                    {{ form.auto_complete_grace_minutes }} min
                                </span>
                                <span
                                    class="chip"
                                    :class="{ 'chip-on': form.remind_overdue }"
                                >
                                    Overdue reminder
                                </span>
                            </div>
                        </li>
                        <li class="relative">
                            <span class="dot dot-filled" aria-hidden="true" />
                            <p class="text-sm font-semibold">Completed</p>
                        </li>
                    </ol>

                    <div class="border-t pt-4">
                        <p class="mb-3 text-sm text-muted-foreground">
                            {{
                                form.isDirty
                                    ? 'You have unsaved changes.'
                                    : 'No changes yet.'
                            }}
                        </p>
                        <Button
                            class="h-11 w-full"
                            :disabled="
                                !available || form.processing || !form.isDirty
                            "
                            @click="submit"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            {{ form.processing ? 'Saving…' : 'Save rules' }}
                        </Button>
                        <Button
                            variant="outline"
                            class="mt-2 h-11 w-full"
                            :disabled="form.processing || !form.isDirty"
                            @click="discard"
                        >
                            Discard changes
                        </Button>
                    </div>
                </aside>
            </div>
        </div>
    </ShopLayout>
</template>

<style scoped>
.switch {
    position: relative;
    display: inline-flex;
    height: 1.75rem;
    width: 3rem;
    flex-shrink: 0;
    align-items: center;
    border-radius: 9999px;
    background: var(--muted-foreground);
    opacity: 0.5;
    transition:
        background-color 0.15s,
        opacity 0.15s;
}
.switch:focus-visible {
    outline: 2px solid var(--ring);
    outline-offset: 2px;
}
.switch-on {
    background: var(--primary);
    opacity: 1;
}
.switch-thumb {
    height: 1.25rem;
    width: 1.25rem;
    transform: translateX(0.25rem);
    border-radius: 9999px;
    background: #fff;
    transition: transform 0.15s;
}
.switch-on .switch-thumb {
    transform: translateX(1.5rem);
}
.dot {
    position: absolute;
    top: 0.25rem;
    left: -1.75rem;
    height: 0.875rem;
    width: 0.875rem;
    border-radius: 9999px;
    border: 2px solid var(--primary);
    background: var(--card);
}
.dot-filled {
    background: var(--primary);
}
.chip {
    border-radius: 9999px;
    background: var(--muted);
    padding: 0.125rem 0.625rem;
    font-size: 0.75rem;
    color: var(--muted-foreground);
}
.chip-on {
    background: color-mix(in srgb, var(--primary) 12%, transparent);
    color: var(--primary);
    font-weight: 500;
}
</style>
