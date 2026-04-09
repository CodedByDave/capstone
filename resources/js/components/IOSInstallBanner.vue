<script setup lang="ts">
import { usePWA } from '@/composables/usePWA'

const { showIOSBanner } = usePWA()
</script>

<template>
    <div
        v-if="showIOSBanner"
        class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 shadow-lg px-4 py-4"
    >
        <div class="flex items-start justify-between gap-3 max-w-md mx-auto">
            <div class="flex items-start gap-3">
                <img src="/laundryhub.png" class="h-10 w-10 rounded-xl shrink-0" alt="LaundryHub" />
                <div>
                    <p class="text-sm font-semibold text-gray-900">Install LaundryHub</p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Tap
                        <svg class="inline h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        then <strong>"Add to Home Screen"</strong>
                    </p>
                </div>
            </div>
            <button @click="showIOSBanner = false" class="text-gray-400 hover:text-gray-600 shrink-0 mt-1">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</template>