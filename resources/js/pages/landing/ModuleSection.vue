<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import { Check, Minus } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

const plans = [
    {
        name: 'Basic',
        price: 999,
        description:
            'Essential tools for small shops just getting started with digital operations.',
        featured: false,
        savings: null,
        modules: [
            { name: 'Human Resource Managament', included: true },
            {
                name: 'Operation Management',
                included: true,
            },
            { name: 'Inventory Management', included: false },
            { name: 'Finance Management', included: false },
            { name: 'Basic Reports', included: true, advanced: false },
        ],
    },
    {
        name: 'Standard',
        price: 1499,
        description:
            'A complete operational stack for growing shops that need full visibility.',
        featured: true,
        savings: null,
        modules: [
            { name: 'Human Resource Managament', included: true },
            {
                name: 'Operation Management',
                included: true,
            },
            { name: 'Inventory Management', included: true },
            { name: 'Finance Management', included: true },
            { name: 'Basic Reports', included: true, advanced: false },
        ],
    },
    {
        name: 'Premium',
        price: 1999,
        description:
            'The full suite for multi-branch shops that need deep analytics and financial control.',
        featured: false,
        savings: null,
        modules: [
            { name: 'Human Resource Managament', included: true },
            {
                name: 'Operation Management',
                included: true,
            },
            { name: 'Inventory Management', included: true },
            { name: 'Finance Management', included: true },
            {
                name: 'Advanced Reports & Analytics',
                included: true,
                advanced: true,
            },
        ],
    },
];

function goToCheckout(plan: any) {
    router.visit(`/shop/checkout/${plan.name}`);
}

function formatPHP(amount: number) {
    return amount.toLocaleString('en-PH');
}

const visibleItems = ref<Record<number, boolean>>({});
const cardRefs = ref<HTMLElement[]>([]);

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const index = Number(
                        (entry.target as HTMLElement).dataset.index,
                    );
                    visibleItems.value[index] = true;
                }
            });
        },
        { threshold: 0.15 },
    );
    cardRefs.value.forEach((el) => {
        if (el) observer.observe(el);
    });
});
</script>

<template>
    <section id="modules" class="bg-white py-24 lg:py-32">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <!-- Header -->
            <div class="mx-auto mb-14 max-w-2xl text-center">
                <span
                    class="inline-block rounded-full border border-emerald-500/40 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-600"
                >
                    Pricing
                </span>
                <h2
                    class="mt-5 bg-gradient-to-b from-neutral-900 to-neutral-500 bg-clip-text text-4xl font-bold tracking-tight text-balance text-transparent sm:text-5xl"
                >
                    Plans built for laundry businesses
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-neutral-500">
                    Start with what you need, upgrade as you grow.
                </p>
            </div>

            <!-- Plan cards -->
            <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 lg:grid-cols-3">
                <div
                    v-for="(plan, index) in plans"
                    :key="plan.name"
                    :ref="
                        (el) => {
                            if (el) {
                                (el as HTMLElement).dataset.index =
                                    String(index);
                                cardRefs[index] = el as HTMLElement;
                            }
                        }
                    "
                    :class="[
                        'relative flex flex-col rounded-2xl border p-7 transition-all duration-700',
                        plan.featured
                            ? 'border-indigo-400/40 bg-gradient-to-b from-indigo-950 via-indigo-800/60 to-indigo-600 shadow-[0_0_60px_-10px_rgba(99,102,241,0.6)]'
                            : 'border-neutral-200 bg-white shadow-sm hover:border-neutral-300 hover:shadow-md',
                        visibleItems[index]
                            ? 'translate-y-0 opacity-100'
                            : 'translate-y-10 opacity-0',
                    ]"
                >
                    <!-- Plan name & price -->
                    <div class="flex items-center justify-between">
                        <p :class="['text-lg', plan.featured ? 'text-white/80' : 'text-neutral-700']">
                            {{ plan.name }}
                        </p>
                        <span
                            v-if="plan.featured"
                            class="rounded-full bg-white/15 px-3 py-0.5 text-xs font-medium text-white"
                        >
                            Most popular
                        </span>
                    </div>
                    <div class="mt-5 flex items-baseline gap-1.5">
                        <span
                            :class="[
                                'text-5xl font-semibold tracking-tight',
                                plan.featured ? 'text-white' : 'text-neutral-900',
                            ]"
                        >
                            ₱{{ formatPHP(plan.price) }}
                        </span>
                        <span
                            :class="[
                                'text-xs',
                                plan.featured ? 'text-white/60' : 'text-neutral-500',
                            ]"
                            >/ month</span
                        >
                    </div>
                    <p
                        :class="[
                            'mt-4 text-sm leading-relaxed',
                            plan.featured ? 'text-white/70' : 'text-neutral-500',
                        ]"
                    >
                        {{ plan.description }}
                    </p>

                    <!-- CTA -->
                    <Button
                        :class="[
                            'mt-6 w-full',
                            plan.featured
                                ? 'bg-white text-black hover:bg-white/90'
                                : 'border border-neutral-200 bg-white text-neutral-900 hover:bg-neutral-50',
                        ]"
                        @click="goToCheckout(plan)"
                    >
                        Get started
                    </Button>
                    <p
                        :class="[
                            'mt-3 text-center text-xs',
                            plan.featured ? 'text-white/60' : 'text-neutral-500',
                        ]"
                    >
                        Billed monthly
                    </p>

                    <!-- Divider -->
                    <hr
                        :class="[
                            'my-6',
                            plan.featured ? 'border-white/15' : 'border-neutral-200',
                        ]"
                    />

                    <!-- Module list -->
                    <p
                        :class="[
                            'mb-4 text-sm font-semibold',
                            plan.featured ? 'text-white' : 'text-neutral-900',
                        ]"
                    >
                        {{
                            plan.name === 'Premium'
                                ? 'All Standard features, plus:'
                                : plan.name === 'Standard'
                                  ? 'All Basic features, plus:'
                                  : 'Basic plan includes:'
                        }}
                    </p>
                    <ul class="flex-1 space-y-3">
                        <li
                            v-for="mod in plan.modules"
                            :key="mod.name"
                            class="flex items-center gap-3 text-sm"
                        >
                            <Check
                                v-if="mod.included"
                                
                                :class="[
                                    'h-4 w-4 shrink-0',
                                    plan.featured ? 'text-white' : 'text-emerald-600',
                                ]"
                            />
                            <Minus
                                v-else
                                
                                :class="[
                                    'h-4 w-4 shrink-0',
                                    plan.featured ? 'text-white/30' : 'text-neutral-300',
                                ]"
                            />
                            <span
                                :class="
                                    mod.included
                                        ? plan.featured
                                            ? 'text-white'
                                            : 'text-neutral-700'
                                        : plan.featured
                                          ? 'text-white/40 line-through'
                                          : 'text-neutral-400 line-through'
                                "
                            >
                                {{ mod.name }}
                            </span>
                            <span
                                v-if="mod.advanced"
                                class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold tracking-wide text-emerald-600 uppercase"
                            >
                                Advanced
                            </span>
                        </li>
                    </ul>

                    <p
                        v-if="plan.savings"
                        class="mt-5 text-center text-xs font-medium text-emerald-600"
                    >
                        Save ₱{{ formatPHP(plan.savings) }} vs individual
                        modules
                    </p>
                </div>
            </div>

            <!-- Footer note -->
            <p class="mt-10 text-center text-sm text-neutral-500">
                Need a custom setup?
                <a
                    href="#contact"
                    class="font-medium text-indigo-600 underline-offset-4 hover:underline"
                >
                    Contact us
                </a>
                and we'll build a plan for your shop.
            </p>
        </div>
    </section>
</template>
