<script setup lang="ts">
import { useFlashToast } from '@/composables/useFlashToast';
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';

defineProps<{
    title?: string;
    description?: string;
}>();

useFlashToast();
</script>

<template>
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>
