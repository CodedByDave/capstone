<script setup lang="ts">
import AuthSwitchScreen from '@/components/auth/AuthSwitchScreen.vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthSwitchScreen
        mode="login"
        :status="status"
        :can-reset-password="canResetPassword"
    />
</template>
