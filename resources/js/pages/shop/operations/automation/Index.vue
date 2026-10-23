<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    BellRing,
    CheckCircle2,
    Clock,
    Loader2,
    Lock,
    PlayCircle,
    Scale,
    Wallet,
} from 'lucide-vue-next';
import { watch } from 'vue';
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

function submit() {
    form.put('/shop/operations/order-automation', { preserveScroll: true });
}
</script>

<template>
    <Head title="Order Automation" />
    <ShopLayout title="Order Automation" :breadcrumbs="breadcrumbs">
        <div class="mx-auto max-w-3xl space-y-6 px-6 pb-10">
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

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <PlayCircle class="h-5 w-5" aria-hidden="true" />
                        Move orders along automatically
                    </CardTitle>
                    <CardDescription>
                        Choose which status changes the system makes for you.
                        Every rule is off until you switch it on, staff can
                        still change any status by hand, and each automatic
                        change is saved in your activity log.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <fieldset :disabled="!available" class="space-y-6">
                        <legend
                            class="mb-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            Pending → In Progress
                        </legend>

                        <label
                            v-for="rule in startRules"
                            :key="rule.key"
                            :for="`rule-${rule.key}`"
                            class="flex cursor-pointer items-start gap-3 rounded-lg border p-4"
                            :class="{ 'opacity-60': !available }"
                        >
                            <Checkbox
                                :id="`rule-${rule.key}`"
                                class="mt-1"
                                :model-value="form[rule.key]"
                                @update:model-value="
                                    (value) => (form[rule.key] = value === true)
                                "
                            />
                            <span class="space-y-1">
                                <span
                                    class="flex items-center gap-2 text-sm font-medium"
                                >
                                    <component
                                        :is="rule.icon"
                                        class="h-4 w-4 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    {{ rule.title }}
                                </span>
                                <span
                                    class="block text-sm text-muted-foreground"
                                >
                                    {{ rule.description }}
                                </span>
                            </span>
                        </label>

                        <legend
                            class="mt-2 mb-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            In Progress → Completed
                        </legend>

                        <div
                            class="space-y-3 rounded-lg border p-4"
                            :class="{ 'opacity-60': !available }"
                        >
                            <label
                                for="rule-auto_complete"
                                class="flex cursor-pointer items-start gap-3"
                            >
                                <Checkbox
                                    id="rule-auto_complete"
                                    class="mt-1"
                                    :model-value="form.auto_complete"
                                    @update:model-value="
                                        (value) =>
                                            (form.auto_complete =
                                                value === true)
                                    "
                                />
                                <span class="space-y-1">
                                    <span
                                        class="flex items-center gap-2 text-sm font-medium"
                                    >
                                        <CheckCircle2
                                            class="h-4 w-4 text-muted-foreground"
                                            aria-hidden="true"
                                        />
                                        Complete orders after their due time
                                    </span>
                                    <span
                                        class="block text-sm text-muted-foreground"
                                    >
                                        An in-progress order is marked Completed
                                        once its due time has passed and the
                                        wait below is over. The due time is the
                                        order's estimated completion; if it has
                                        none, it is when work started plus the
                                        service's estimated hours. Orders with
                                        neither, or without a confirmed weight,
                                        are never completed automatically.
                                    </span>
                                </span>
                            </label>

                            <div class="ml-7 max-w-xs space-y-1.5">
                                <label
                                    for="grace-minutes"
                                    class="flex items-center gap-2 text-sm font-medium"
                                >
                                    <Clock
                                        class="h-4 w-4 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    Wait after the due time (minutes)
                                </label>
                                <Input
                                    id="grace-minutes"
                                    v-model.number="
                                        form.auto_complete_grace_minutes
                                    "
                                    type="number"
                                    min="0"
                                    :max="limits.max_grace_minutes"
                                    step="1"
                                    :disabled="!form.auto_complete"
                                />
                                <p
                                    v-if="
                                        form.errors.auto_complete_grace_minutes
                                    "
                                    class="text-xs text-red-500"
                                    role="alert"
                                >
                                    {{
                                        form.errors.auto_complete_grace_minutes
                                    }}
                                </p>
                                <p v-else class="text-xs text-muted-foreground">
                                    0 to {{ limits.max_grace_minutes }}. A
                                    longer wait gives staff time to finish
                                    first.
                                </p>
                            </div>
                        </div>

                        <legend
                            class="mt-2 mb-3 text-xs font-semibold tracking-wide text-muted-foreground uppercase"
                        >
                            Reminders
                        </legend>

                        <label
                            for="rule-remind_overdue"
                            class="flex cursor-pointer items-start gap-3 rounded-lg border p-4"
                            :class="{ 'opacity-60': !available }"
                        >
                            <Checkbox
                                id="rule-remind_overdue"
                                class="mt-1"
                                :model-value="form.remind_overdue"
                                @update:model-value="
                                    (value) =>
                                        (form.remind_overdue = value === true)
                                "
                            />
                            <span class="space-y-1">
                                <span
                                    class="flex items-center gap-2 text-sm font-medium"
                                >
                                    <BellRing
                                        class="h-4 w-4 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    Remind me about overdue orders
                                </span>
                                <span
                                    class="block text-sm text-muted-foreground"
                                >
                                    You get one notification when an order is
                                    still in progress after its due time.
                                </span>
                            </span>
                        </label>
                    </fieldset>

                    <div class="flex items-center justify-end gap-3">
                        <Button
                            :disabled="!available || form.processing"
                            @click="submit"
                        >
                            <Loader2
                                v-if="form.processing"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            {{ form.processing ? 'Saving…' : 'Save rules' }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </ShopLayout>
</template>
