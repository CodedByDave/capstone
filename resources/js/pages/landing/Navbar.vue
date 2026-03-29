<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue"
import { Link, router, usePage } from "@inertiajs/vue3"
import { Button } from "@/components/ui/button"
import { Menu, X, WashingMachine } from "lucide-vue-next"
import { dashboard } from '@/routes'

const page = usePage()

const navLinks = [
    { href: "#modules", label: "Modules" },
    { href: "#how-it-works", label: "How It Works" },
    { href: "#pricing", label: "Pricing" },
    { href: "#testimonials", label: "Testimonials" },
]

const isScrolled = ref(false)
const isMobileOpen = ref(false)

function toggleMobileMenu() {
    isMobileOpen.value = !isMobileOpen.value
}

function handleScroll() {
    isScrolled.value = window.scrollY > 20
}

onMounted(() => window.addEventListener("scroll", handleScroll))
onUnmounted(() => window.removeEventListener("scroll", handleScroll))
</script>

<template>
    <header :class="[
        'fixed top-0 left-0 right-0 z-50 transition-all duration-500',
        isScrolled ? 'bg-card/90 backdrop-blur-xl shadow-sm border-b border-border' : 'bg-transparent'
    ]">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 sm:px-6 py-4 lg:px-8">

            <!-- Logo -->
            <a href="#" class="flex items-center gap-2 group">
                <!-- Logo image -->
                <img src="/laundryhub.png" alt="LaundryHub Logo"
                    class="h-9 w-9 object-contain shrink-0 transition-transform duration-300 group-hover:scale-110" />

                <!-- Brand name -->
                <span class="text-xl font-bold tracking-tight text-foreground font-serif">
                    LaundryHub
                </span>
            </a>

            <!-- Desktop nav links -->
            <div class="hidden items-center gap-8 md:flex">
                <a v-for="link in navLinks" :key="link.href" :href="link.href"
                    class="text-sm font-medium text-muted-foreground transition-colors duration-300 hover:text-foreground">
                    {{ link.label }}
                </a>
            </div>

            <!-- Desktop buttons -->
            <div class="hidden items-center gap-3 md:flex">
                <template v-if="page.props.auth?.user">
                    <Link :href="dashboard.url()">
                        <Button size="sm" class="bg-primary text-primary-foreground hover:bg-accent shadow-none">
                            Dashboard
                        </Button>
                    </Link>
                </template>
                <template v-else>
                    <Button @click="router.visit('/login')" variant="ghost" size="sm"
                        class="text-muted-foreground hover:text-foreground">
                        Log in
                    </Button>
                    <Button size="sm" variant="default"
                        class="!bg-blue-600 !text-white hover:!bg-blue-500 !shadow-none transition-colors duration-300"
                        @click="router.visit('/register/shop')">
                        Register Shop
                    </Button>

                </template>
            </div>

            <!-- Mobile toggle -->
            <button class="md:hidden text-foreground p-1" @click="toggleMobileMenu"
                :aria-label="isMobileOpen ? 'Close menu' : 'Open menu'">
                <component :is="isMobileOpen ? X : Menu" class="h-6 w-6" />
            </button>
        </nav>

        <!-- Mobile menu -->
        <div class="md:hidden overflow-hidden transition-all duration-500 ease-out"
            :class="isMobileOpen ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'">
            <div class="border-t border-border bg-card/95 backdrop-blur-xl px-4 sm:px-6 py-6 flex flex-col gap-4">
                <a v-for="link in navLinks" :key="link.href" :href="link.href" @click="isMobileOpen = false"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground py-1">
                    {{ link.label }}
                </a>
                <div class="flex flex-col gap-2 pt-2 border-t border-border">
                    <template v-if="page.props.auth?.user">
                        <Link :href="dashboard.url()" class="w-full">
                            <Button size="sm" class="w-full bg-primary text-primary-foreground hover:bg-accent">
                                Dashboard
                            </Button>
                        </Link>
                    </template>
                    <template v-else>
                        <Button variant="outline" size="sm" class="w-full" @click="router.visit('/login')">
                            Log in
                        </Button>
                        <Button size="sm" class="w-full bg-primary text-primary-foreground hover:bg-accent"
                            @click="router.visit('register/shop')">
                            Register Shop
                        </Button>
                    </template>
                </div>
            </div>
        </div>
    </header>
</template>
