<script setup lang="ts">
import { usePWA } from '@/composables/usePWA';

const { showIOSBanner } = usePWA();
</script>

<template>
    <div
        v-if="showIOSBanner"
        class="fixed right-0 bottom-0 left-0 z-50 border-t border-gray-200 bg-white px-4 py-4 shadow-lg"
    >
        <div class="mx-auto flex max-w-md items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <img
                    src="/laundryhub.png"
                    class="h-10 w-10 shrink-0 rounded-xl"
                    alt="LaundryHub"
                />
                <div>
                    <p class="text-sm font-semibold text-gray-900">
                        Install LaundryHub
                    </p>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Tap
                        <svg
                            class="inline h-4 w-4 text-blue-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                            />
                        </svg>
                        then <strong>"Add to Home Screen"</strong>
                    </p>
                </div>
            </div>
            <button
                @click="showIOSBanner = false"
                class="mt-1 shrink-0 text-gray-400 hover:text-gray-600"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>
    </div>
</template>
