<script setup lang="ts">
import IOSInstallBanner from '@/components/IOSInstallBanner.vue';
import { Button } from '@/components/ui/button';
import { usePWA } from '@/composables/usePWA';
import { dashboard } from '@/routes/user';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Menu, X } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

const page = usePage();
const { installApp } = usePWA();

const navLinks = [
    { href: '#modules', label: 'Modules' },
    { href: '#how-it-works', label: 'How It Works' },
    { href: '#pricing', label: 'Pricing' },
    { href: '#testimonials', label: 'Testimonials' },
];

const isScrolled = ref(false);
const isMobileOpen = ref(false);

function toggleMobileMenu() {
    isMobileOpen.value = !isMobileOpen.value;
}

function handleScroll() {
    isScrolled.value = window.scrollY > 20;
}

onMounted(() => window.addEventListener('scroll', handleScroll));
onUnmounted(() => window.removeEventListener('scroll', handleScroll));
</script>

<template>
    <header
        :class="[
            'fixed top-0 right-0 left-0 z-50 transition-all duration-500',
            isScrolled
                ? 'border-b border-border bg-card/90 shadow-sm backdrop-blur-xl'
                : 'bg-transparent',
        ]"
    >
        <nav
            class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8"
        >
            <!-- Logo -->
            <a href="#" class="group flex items-center gap-2">
                <img
                    src="/laundryhub.png"
                    alt="LaundryHub Logo"
                    class="h-9 w-9 shrink-0 object-contain transition-transform duration-300 group-hover:scale-110"
                />
                <span
                    class="font-serif text-xl font-bold tracking-tight text-foreground"
                >
                    LaundryHub
                </span>
            </a>

            <!-- Desktop nav links -->
            <div class="hidden items-center gap-8 md:flex">
                <a
                    v-for="link in navLinks"
                    :key="link.href"
                    :href="link.href"
                    class="text-sm font-medium text-muted-foreground transition-colors duration-300 hover:text-foreground"
                >
                    {{ link.label }}
                </a>
            </div>

            <!-- Desktop buttons -->
            <div class="hidden items-center gap-3 md:flex">
                <!-- Install App button -->
                <Button
                    size="sm"
                    variant="outline"
                    class="flex items-center gap-2 border-blue-600 text-blue-600 hover:bg-blue-50"
                    @click="installApp"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4"
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
                    Install App
                </Button>

                <template v-if="page.props.auth?.user">
                    <Link :href="dashboard.url()">
                        <Button
                            size="sm"
                            class="bg-primary text-primary-foreground shadow-none hover:bg-accent"
                        >
                            Dashboard
                        </Button>
                    </Link>
                </template>
                <template v-else>
                    <Button
                        @click="router.visit('/login')"
                        variant="ghost"
                        size="sm"
                        class="text-muted-foreground hover:text-foreground"
                    >
                        Log in
                    </Button>
                    <Button
                        size="sm"
                        variant="default"
                        class="!bg-blue-600 !text-white !shadow-none transition-colors duration-300 hover:!bg-blue-500"
                        @click="router.visit('/register/shop')"
                    >
                        Register Shop
                    </Button>
                </template>
            </div>

            <!-- Mobile toggle -->
            <button
                class="p-1 text-foreground md:hidden"
                @click="toggleMobileMenu"
                :aria-label="isMobileOpen ? 'Close menu' : 'Open menu'"
            >
                <component :is="isMobileOpen ? X : Menu" class="h-6 w-6" />
            </button>
        </nav>

        <!-- Mobile menu -->
        <div
            class="overflow-hidden transition-all duration-500 ease-out md:hidden"
            :class="isMobileOpen ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'"
        >
            <div
                class="flex flex-col gap-4 border-t border-border bg-card/95 px-4 py-6 backdrop-blur-xl sm:px-6"
            >
                <a
                    v-for="link in navLinks"
                    :key="link.href"
                    :href="link.href"
                    @click="isMobileOpen = false"
                    class="py-1 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                >
                    {{ link.label }}
                </a>
                <div class="flex flex-col gap-2 border-t border-border pt-2">
                    <!-- Mobile Install App button -->
                    <Button
                        size="sm"
                        variant="outline"
                        class="flex w-full items-center justify-center gap-2 border-blue-600 text-blue-600 hover:bg-blue-50"
                        @click="installApp"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
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
                        Install App
                    </Button>

                    <template v-if="page.props.auth?.user">
                        <Link :href="dashboard.url()" class="w-full">
                            <Button
                                size="sm"
                                class="w-full bg-primary text-primary-foreground hover:bg-accent"
                            >
                                Dashboard
                            </Button>
                        </Link>
                    </template>
                    <template v-else>
                        <Button
                            variant="outline"
                            size="sm"
                            class="w-full"
                            @click="router.visit('/login')"
                        >
                            Log in
                        </Button>
                        <Button
                            size="sm"
                            class="w-full bg-primary text-primary-foreground hover:bg-accent"
                            @click="router.visit('register/shop')"
                        >
                            Register Shop
                        </Button>
                    </template>
                </div>
            </div>
        </div>
    </header>

    <IOSInstallBanner />
</template>
