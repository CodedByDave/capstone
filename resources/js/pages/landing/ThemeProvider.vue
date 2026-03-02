<script setup lang="ts">
import { provide, ref, watchEffect } from 'vue'

type Theme = 'light' | 'dark' | 'system'

// Props for the provider
interface Props {
    defaultTheme?: Theme
}

defineProps<Props>()

// Reactive theme state
const theme = ref<Theme>('light')

// Function to set the theme
function setTheme(newTheme: Theme) {
    theme.value = newTheme
    if (newTheme === 'system') {
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
        document.documentElement.classList.toggle('dark', systemPrefersDark)
    } else {
        document.documentElement.classList.toggle('dark', newTheme === 'dark')
    }
}

// Initialize default theme
setTheme((__props.defaultTheme ?? 'light') as Theme)

// Provide the theme to child components
provide('theme', theme)
provide('setTheme', setTheme)
</script>

<template>
    <slot />
</template>
