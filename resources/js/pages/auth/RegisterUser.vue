<script setup lang="ts">
import AuthSwitchScreen from '@/components/auth/AuthSwitchScreen.vue';

defineProps<{
    googleUser?: {
        google_id: string;
        name: string;
        email: string;
        avatar: string | null;
    } | null;
    referralShop?: {
        name: string;
        municipality: string | null;
        barangay: string | null;
    } | null;
}>();
</script>

<template>
    <AuthSwitchScreen
        mode="register"
        :google-user="googleUser"
        :referral-shop="referralShop"
    />
</template>
