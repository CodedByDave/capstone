<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toast } from 'vue3-toastify';

defineProps<{
    title?: string;
    description?: string;
}>();

interface ToastMessage {
    type: 'success' | 'error' | 'warning' | 'info';
    message: string;
}

const page = usePage();

watch(
    () => page.props.toast as ToastMessage | undefined,
    (notification) => {
        if (!notification?.message) return;

        const showToast = toast[notification.type] ?? toast;
        showToast(notification.message);
    },
    { immediate: true },
);
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>
