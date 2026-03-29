<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img
        src="/laundryhub.png"
        alt="LaundryHub Logo"
        class="h-10 w-10 object-cover rounded-full"
    />
</template>


