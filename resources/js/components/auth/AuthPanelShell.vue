<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import AuthPanelDecor from '@/components/auth/AuthPanelDecor.vue';
import {
    panelSideOf,
    rememberScreen,
    takePreviousScreen,
    type AuthScreen,
    type PanelSide,
} from '@/components/auth/authPanelMemory';
import { paneClass, SLIDE_MS } from '@/components/auth/authStyles';
import { useFlashToast } from '@/composables/useFlashToast';
import { Link } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

/**
 * Split screen for the single-form auth pages (shop registration, OTP): form beside
 * a blue panel. When the visitor arrives from a screen whose panel was on the
 * other side, the panel starts there and slides across.
 */
const props = defineProps<{
    screen: AuthScreen;
    title: string;
    description?: string;
    /** Multi-step forms: the panel switches sides each time this changes. */
    step?: number;
}>();

useFlashToast();

const previous = takePreviousScreen();
// Shop registration sits on the opposite side from the page it was opened from,
// so the panel always has somewhere to slide to.
const ownSide: PanelSide =
    props.screen === 'shop'
        ? previous?.side === 'left'
            ? 'right'
            : 'left'
        : panelSideOf[props.screen];
const panelSide = ref<PanelSide>(previous?.side ?? ownSide);
const shouldSlide = panelSide.value !== ownSide;
const formVisible = ref(!shouldSlide);
// The form sits on the half the panel is not covering; it only changes halves
// while hidden, so it never visibly jumps.
const formAtRight = ref(ownSide === 'left');
let stepTimer: number | undefined;

onMounted(() => {
    if (!shouldSlide) return;

    requestAnimationFrame(() =>
        requestAnimationFrame(() => {
            panelSide.value = ownSide;
            formVisible.value = true;
        }),
    );
});

watch(
    () => props.step,
    () => {
        window.clearTimeout(stepTimer);
        formVisible.value = false;
        panelSide.value = panelSide.value === 'left' ? 'right' : 'left';
        stepTimer = window.setTimeout(() => {
            formAtRight.value = panelSide.value === 'left';
            formVisible.value = true;
        }, SLIDE_MS / 2);
    },
);

onBeforeUnmount(() => {
    window.clearTimeout(stepTimer);
    rememberScreen(props.screen, panelSide.value);
});
</script>

<template>
    <div
        class="relative min-h-svh overflow-hidden bg-white font-customer text-slate-900 lg:grid lg:h-svh lg:grid-cols-2"
    >
        <section
            :aria-label="title"
            :class="[
                paneClass,
                formAtRight ? 'lg:col-start-2' : '',
                formVisible ? 'opacity-100' : 'opacity-0',
            ]"
            :style="
                formVisible
                    ? { transitionDelay: '300ms', transitionDuration: '500ms' }
                    : { transitionDelay: '0ms', transitionDuration: '150ms' }
            "
        >
            <div class="m-auto w-full max-w-[420px]">
                <div class="flex flex-col items-center text-center">
                    <Link href="/" aria-label="LaundryHub home">
                        <AppLogoIcon />
                    </Link>
                    <h1
                        class="mt-2 font-display text-3xl font-bold tracking-tight sm:text-4xl"
                    >
                        {{ title }}
                    </h1>
                    <p
                        v-if="description"
                        class="mt-2 text-[15px] text-slate-500"
                    >
                        {{ description }}
                    </p>
                </div>

                <slot />
            </div>
        </section>

        <aside
            class="absolute inset-y-0 left-0 z-10 hidden w-1/2 overflow-hidden bg-blue-600 text-white transition-transform duration-700 ease-[cubic-bezier(0.65,0,0.35,1)] motion-reduce:transition-none lg:block"
            :class="
                panelSide === 'right' ? 'translate-x-full' : 'translate-x-0'
            "
        >
            <AuthPanelDecor />
            <div
                class="absolute inset-0 flex flex-col items-center justify-center px-10 text-center"
            >
                <slot name="panel" />
            </div>
        </aside>
    </div>
</template>
