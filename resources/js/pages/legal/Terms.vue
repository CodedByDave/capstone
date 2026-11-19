<script setup lang="ts">
import LegalDocument, { type LegalSection } from './LegalDocument.vue';

// Placeholder wording: have the final text reviewed before launch.
const sections: LegalSection[] = [
    {
        heading: 'Using LaundryHub',
        body: [
            'LaundryHub connects customers with laundry shops and gives shop owners and staff tools to manage orders, inventory, payments and employees.',
            'You must provide accurate information when you register and keep your login details private. You are responsible for activity under your account.',
        ],
    },
    {
        heading: 'Orders and payments',
        body: [
            'Orders are agreed between the customer and the shop. Prices, pickup and delivery details and processing times are set by each shop.',
            'Payments made through the platform are recorded against the order. Refunds and cancellations follow the shop’s own policy and the status of the order.',
        ],
    },
    {
        heading: 'Shop owners and staff',
        body: [
            'Shop owners are responsible for the accuracy of their shop details, services, prices and staff access, and for the data their staff enter.',
        ],
    },
    {
        heading: 'Acceptable use',
        body: [
            'Do not misuse the platform, attempt to access data that is not yours, or interfere with its operation. We may suspend accounts that break these terms.',
        ],
    },
    {
        heading: 'Changes and contact',
        body: [
            'We may update these terms from time to time. Continuing to use LaundryHub after a change means you accept the updated terms.',
            'For questions about these terms, contact us through the support options in the app.',
        ],
    },
];
</script>

<template>
    <LegalDocument
        title="Terms of Service"
        updated="November 19, 2026"
        intro="These terms explain the rules for using LaundryHub. By creating an account or signing in, you agree to them."
        :sections="sections"
    />
</template>
