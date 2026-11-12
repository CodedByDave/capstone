<script setup lang="ts">
import { ChevronDown, ShoppingBag, Store } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

type Audience = 'owners' | 'customers';

interface Faq {
    question: string;
    answer: string;
}

const tabs: { key: Audience; label: string; icon: typeof Store }[] = [
    { key: 'owners', label: 'Shop owners', icon: Store },
    { key: 'customers', label: 'Customers', icon: ShoppingBag },
];

const faqs: Record<Audience, Faq[]> = {
    owners: [
        {
            question: 'What is LaundryHub?',
            answer: 'LaundryHub is a web-based platform for running a laundry shop. It brings online orders, pickup and delivery, staff, inventory, finances, and reports into one dashboard, and gives your customers a simple way to order and pay.',
        },
        {
            question: 'How much does it cost?',
            answer: 'Basic is ₱999, Standard is ₱1,499, and Premium is ₱1,999 per month, plus 12% VAT. Paying for 12, 24, or 48 months at once saves you 10%, 20%, or 30%.',
        },
        {
            question: 'Is there a free trial?',
            answer: 'Yes. Once your business documents are approved, you get a 7-day free trial of the Standard plan. It starts automatically, and no payment is needed.',
        },
        {
            question: 'What documents do I need to register my shop?',
            answer: "Your BIR Certificate of Registration, DTI registration, and Mayor's permit, all still valid. A Sanitary permit is optional. We read the details from the documents to save you typing, and our team reviews them before your shop goes live.",
        },
        {
            question: 'Are my business documents safe?',
            answer: 'Yes. Your documents are stored encrypted, only the platform administrator can open them, and they are used only to verify your business.',
        },
        {
            question: 'Which areas does LaundryHub serve?',
            answer: 'LaundryHub currently serves laundry shops in Cavite. You pin your shop on the map when you register so customers nearby can find you.',
        },
        {
            question: 'Can I change my plan later?',
            answer: 'Yes. You can upgrade to a bigger plan from your dashboard at any time to unlock more modules, such as Inventory, Financial Management, or Advanced Reports.',
        },
        {
            question: 'Do I need my own riders for pickup and delivery?',
            answer: 'Pickup and delivery are optional. If you turn them on, you add your riders and LaundryHub assigns each run to a free rider, who gets turn-by-turn directions on their phone.',
        },
    ],
    customers: [
        {
            question: 'How do I place an order?',
            answer: 'Create a free account, choose a nearby shop and a service, enter the estimated weight or number of pieces, and choose whether a rider picks it up or you drop it off at the shop.',
        },
        {
            question: 'Why can the final price differ from the estimate?',
            answer: 'The estimate uses the weight you entered. The shop weighs your laundry and sends a photo of the scale. The order starts only after you approve that weight, and if you decline, the order is cancelled.',
        },
        {
            question: 'How can I pay?',
            answer: 'Pay in cash at the shop or to the rider, or online with GCash, Maya, card, or GrabPay through PayMongo. Online payment opens once the shop records the final amount, and the smallest online payment is ₱100.',
        },
        {
            question: 'Can I track my order?',
            answer: 'Yes. You are notified as your order moves from pending to in progress to completed, and you can follow your rider on the map while your laundry is on its way.',
        },
        {
            question: 'Can I cancel an order?',
            answer: 'You can cancel an order while it is still pending, before the shop starts working on it.',
        },
        {
            question: 'Do I need to download an app?',
            answer: 'No. LaundryHub works in your phone or computer browser. You can also tap "Install App" to add it to your home screen and open it like an app.',
        },
    ],
};

const active = ref<Audience>('owners');
const open = ref<number | null>(0);

function selectTab(key: Audience) {
    active.value = key;
    open.value = 0;
}

function toggle(index: number) {
    open.value = open.value === index ? null : index;
}

// Arrow keys move between the two tabs, as a tab list should.
function onTabKeydown(event: KeyboardEvent) {
    if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
    const next = active.value === 'owners' ? 'customers' : 'owners';
    selectTab(next);
    document.getElementById(`faq-tab-${next}`)?.focus();
}

const sectionRef = ref<HTMLElement | null>(null);
const isVisible = ref(false);

onMounted(() => {
    const observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) isVisible.value = true;
        },
        { threshold: 0.1 },
    );
    if (sectionRef.value) observer.observe(sectionRef.value);
});
</script>

<template>
    <section
        id="faq"
        ref="sectionRef"
        class="scroll-mt-20 bg-background py-24 lg:py-32"
        aria-labelledby="faq-heading"
    >
        <div
            :class="[
                'mx-auto max-w-3xl px-4 transition-all duration-700 sm:px-6 lg:px-8',
                isVisible
                    ? 'translate-y-0 opacity-100'
                    : 'translate-y-8 opacity-0',
            ]"
        >
            <div class="text-center">
                <p
                    class="text-sm font-semibold tracking-widest text-primary uppercase"
                >
                    FAQ
                </p>
                <h2
                    id="faq-heading"
                    class="mt-3 font-serif text-3xl font-bold tracking-tight text-balance text-foreground sm:text-4xl sm:text-5xl"
                >
                    Frequently asked questions
                </h2>
                <p class="mt-4 text-base text-muted-foreground">
                    Answers for shop owners thinking of joining, and for
                    customers ordering their laundry.
                </p>
            </div>

            <div
                class="mx-auto mt-10 flex w-fit rounded-xl border border-border bg-card p-1"
                role="tablist"
                aria-label="Questions for"
            >
                <button
                    v-for="tab in tabs"
                    :id="`faq-tab-${tab.key}`"
                    :key="tab.key"
                    type="button"
                    role="tab"
                    :aria-selected="active === tab.key"
                    aria-controls="faq-panel"
                    :tabindex="active === tab.key ? 0 : -1"
                    class="inline-flex min-h-11 items-center gap-2 rounded-lg px-4 text-sm font-medium transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                    :class="
                        active === tab.key
                            ? 'bg-primary text-primary-foreground'
                            : 'text-muted-foreground hover:text-foreground'
                    "
                    @click="selectTab(tab.key)"
                    @keydown="onTabKeydown"
                >
                    <component
                        :is="tab.icon"
                        class="h-4 w-4"
                        aria-hidden="true"
                    />
                    {{ tab.label }}
                </button>
            </div>

            <div
                id="faq-panel"
                role="tabpanel"
                :aria-labelledby="`faq-tab-${active}`"
                class="mt-8 divide-y divide-border rounded-2xl border border-border bg-card"
            >
                <div v-for="(faq, index) in faqs[active]" :key="faq.question">
                    <h3>
                        <button
                            :id="`faq-q-${active}-${index}`"
                            type="button"
                            class="flex min-h-11 w-full items-center justify-between gap-4 px-5 py-4 text-left text-sm font-semibold text-foreground transition-colors hover:text-primary focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none focus-visible:ring-inset sm:px-6 sm:text-base"
                            :aria-expanded="open === index"
                            :aria-controls="`faq-a-${active}-${index}`"
                            @click="toggle(index)"
                        >
                            {{ faq.question }}
                            <ChevronDown
                                class="h-5 w-5 shrink-0 text-muted-foreground transition-transform duration-300"
                                :class="{
                                    'rotate-180 text-primary': open === index,
                                }"
                                aria-hidden="true"
                            />
                        </button>
                    </h3>
                    <div
                        :id="`faq-a-${active}-${index}`"
                        role="region"
                        :aria-labelledby="`faq-q-${active}-${index}`"
                        class="grid transition-[grid-template-rows] duration-300 ease-out"
                        :class="
                            open === index
                                ? 'grid-rows-[1fr]'
                                : 'grid-rows-[0fr]'
                        "
                    >
                        <div class="overflow-hidden">
                            <p
                                class="px-5 pb-5 text-sm leading-relaxed text-muted-foreground sm:px-6"
                                :inert="open !== index"
                            >
                                {{ faq.answer }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
