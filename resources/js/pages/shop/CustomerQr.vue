<script setup lang="ts">
import { Button } from '@/components/ui/button';
import ShopLayout from '@/layouts/shop/ShopLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Check, Copy, Download, Printer } from 'lucide-vue-next';
import QRCode from 'qrcode';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    shopName: string;
    joinUrl: string;
}>();

// A link that points at this computer (localhost) cannot be opened from a customer's phone.
const pointsToThisDevice = computed(() => {
    try {
        return /^(localhost|127\.|\[::1\]|::1$)/.test(
            new URL(props.joinUrl).hostname,
        );
    } catch {
        return false;
    }
});

const qrDataUrl = ref('');
const copied = ref(false);

onMounted(async () => {
    qrDataUrl.value = await QRCode.toDataURL(props.joinUrl, {
        width: 640,
        margin: 2,
        errorCorrectionLevel: 'H',
    });
});

async function copyLink() {
    try {
        await navigator.clipboard.writeText(props.joinUrl);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    } catch {
        /* clipboard unavailable */
    }
}

function printPage() {
    window.print();
}

function downloadQr() {
    const link = document.createElement('a');
    link.href = qrDataUrl.value;
    link.download = `${props.shopName.replace(/\s+/g, '-').toLowerCase()}-customer-qr.png`;
    link.click();
}
</script>

<template>
    <Head title="Customer QR" />

    <ShopLayout title="Customer QR">
        <div class="mx-auto max-w-6xl space-y-8 px-6 pb-10">
            <div>
                <h2 class="text-2xl font-bold tracking-tight">Customer QR</h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Show this code to your customers so they can create an
                    account that is linked to {{ shopName }}.
                </p>
            </div>

            <div
                v-if="pointsToThisDevice"
                role="alert"
                class="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:bg-amber-950/30 dark:text-amber-200"
            >
                This QR points to <strong>{{ joinUrl }}</strong
                >, which phones cannot open. Open this page using your
                computer's network address (for example http://192.168.x.x:8000)
                or your public domain, and the QR will use that address instead.
            </div>

            <div
                class="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_320px]"
            >
                <div class="space-y-8">
                    <section>
                        <h3 class="text-base font-semibold">How it works</h3>
                        <ol
                            class="mt-4 list-decimal space-y-2 pl-5 text-sm text-muted-foreground"
                        >
                            <li>
                                The customer scans your QR code with their
                                phone's camera.
                            </li>
                            <li>
                                They are taken to the sign-up page for
                                {{ shopName }} and create an account.
                            </li>
                            <li>
                                After logging in, they only see and order from
                                your shop's services.
                            </li>
                        </ol>
                    </section>

                    <section class="border-t pt-8">
                        <h3 class="text-base font-semibold">Share the link</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">
                            Use the link when a customer can't scan the code.
                        </p>
                        <div class="mt-4 flex items-center gap-2">
                            <input
                                :value="joinUrl"
                                readonly
                                aria-label="Customer sign-up link"
                                class="h-9 w-full min-w-0 rounded-md border border-input bg-background px-3 text-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            />
                            <Button
                                type="button"
                                variant="outline"
                                class="shrink-0"
                                @click="copyLink"
                            >
                                <component
                                    :is="copied ? Check : Copy"
                                    class="mr-2 h-4 w-4"
                                />
                                {{ copied ? 'Copied' : 'Copy' }}
                            </Button>
                        </div>
                    </section>
                </div>

                <aside
                    class="rounded-xl border bg-card p-4 shadow-sm lg:sticky lg:top-4"
                >
                    <p
                        class="text-[11px] font-semibold tracking-widest text-muted-foreground uppercase"
                    >
                        Your shop QR
                    </p>
                    <div
                        class="mt-3 flex aspect-square items-center justify-center rounded-lg bg-white p-2"
                    >
                        <img
                            v-if="qrDataUrl"
                            :src="qrDataUrl"
                            :alt="`QR code for customers to join ${shopName}`"
                            class="h-full w-full"
                        />
                    </div>
                    <p class="mt-3 text-center text-sm font-semibold">
                        {{ shopName }}
                    </p>

                    <div class="mt-4 border-t pt-4">
                        <Button
                            type="button"
                            class="w-full"
                            :disabled="!qrDataUrl"
                            @click="downloadQr"
                        >
                            <Download class="mr-2 h-4 w-4" /> Download QR
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            class="mt-2 w-full"
                            @click="printPage"
                        >
                            <Printer class="mr-2 h-4 w-4" /> Print
                        </Button>
                    </div>
                </aside>
            </div>
        </div>
    </ShopLayout>
</template>
