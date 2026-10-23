<script setup lang="ts">
import type { VehicleKind } from '@/lib/trackingMap';

/** A side view of the rider's vehicle for the rider card, like the car in ride-hailing apps. */
defineProps<{ kind: VehicleKind }>();
</script>

<template>
    <svg
        v-if="kind === 'car'"
        viewBox="0 0 240 110"
        class="h-24 w-auto"
        role="img"
        aria-label="Delivery car"
        xmlns="http://www.w3.org/2000/svg"
    >
        <ellipse cx="120" cy="98" rx="92" ry="8" fill="rgba(15,23,42,.14)" />
        <path
            d="M22 72c0-9 5-15 14-17l24-5 26-24c4-4 9-6 15-6h38c7 0 13 3 18 8l22 22 27 5c9 2 14 8 14 17v6c0 4-3 7-7 7H29c-4 0-7-3-7-7z"
            fill="#ffffff"
            stroke="#d1d5db"
            stroke-width="2"
        />
        <path
            d="M82 50l18-17c2-2 4-3 7-3h24c3 0 6 1 8 3l17 17z"
            fill="#1f2937"
        />
        <path d="M122 30v20" stroke="#ffffff" stroke-width="3" />
        <rect x="26" y="68" width="16" height="6" rx="3" fill="#fde68a" />
        <rect x="198" y="68" width="16" height="6" rx="3" fill="#fca5a5" />
        <circle cx="68" cy="86" r="15" fill="#111827" />
        <circle cx="68" cy="86" r="7" fill="#e5e7eb" />
        <circle cx="172" cy="86" r="15" fill="#111827" />
        <circle cx="172" cy="86" r="7" fill="#e5e7eb" />
    </svg>
    <svg
        v-else
        viewBox="0 0 240 110"
        class="h-24 w-auto"
        role="img"
        aria-label="Delivery motorcycle"
        xmlns="http://www.w3.org/2000/svg"
    >
        <ellipse cx="120" cy="98" rx="84" ry="8" fill="rgba(15,23,42,.14)" />
        <circle cx="62" cy="80" r="19" fill="#111827" />
        <circle cx="62" cy="80" r="9" fill="#e5e7eb" />
        <circle cx="180" cy="80" r="19" fill="#111827" />
        <circle cx="180" cy="80" r="9" fill="#e5e7eb" />
        <path
            d="M62 80l30-26h40l22 14 26 12"
            fill="none"
            stroke="#374151"
            stroke-width="5"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <path
            d="M88 52h52c6 0 10 3 12 8l8 8h-70z"
            fill="#ffffff"
            stroke="#d1d5db"
            stroke-width="2"
        />
        <rect x="84" y="44" width="52" height="9" rx="4.5" fill="#111827" />
        <path
            d="M168 44l10-12h14"
            fill="none"
            stroke="#374151"
            stroke-width="5"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
        <circle cx="160" cy="58" r="6" fill="#fde68a" />
        <rect x="60" y="30" width="36" height="24" rx="5" fill="#10b981" />
        <path
            d="M72 40h12M78 36v8"
            stroke="#ffffff"
            stroke-width="3"
            stroke-linecap="round"
        />
    </svg>
</template>
