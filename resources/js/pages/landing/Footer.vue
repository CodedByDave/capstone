<script setup lang="ts">
import { WashingMachine } from "lucide-vue-next"

const footerLinks = {
    Platform: ["Modules", "Pricing", "Demo", "API", "Integrations"],
    Company: ["About Us", "Careers", "Blog", "Partners"],
    Support: ["Help Center", "Contact Us", "FAQ", "Status"],
    Legal: ["Privacy Policy", "Terms of Service", "Cookie Policy"],
}

const year = new Date().getFullYear()
</script>

<template>
    <footer class="border-t border-border bg-card">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 py-12 sm:py-16 lg:px-8">

            <div class="grid grid-cols-2 gap-8 sm:grid-cols-2 lg:grid-cols-5">

                <!-- Brand Column -->
                <div class="col-span-2 lg:col-span-1">
                    <a href="#" class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                            <WashingMachine class="h-4 w-4" />
                        </div>
                        <span class="text-lg font-bold text-foreground font-serif">LaundryHub</span>
                    </a>
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-muted-foreground">
                        The modular platform for laundry businesses. Register your shop, pick your tools, and grow.
                    </p>
                </div>

                <!-- Link Columns -->
                <div v-for="(links, category) in footerLinks" :key="category">
                    <h3 class="text-sm font-semibold text-foreground">{{ category }}</h3>
                    <ul class="mt-4 flex flex-col gap-2.5">
                        <li v-for="link in links" :key="link">
                            <a href="#" class="text-sm text-muted-foreground transition-colors duration-200 hover:text-foreground">
                                {{ link }}
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Bottom Bar -->
            <div class="mt-12 sm:mt-16 flex flex-col items-center justify-between gap-4 border-t border-border pt-8 sm:flex-row">
                <p class="text-sm text-muted-foreground">© {{ year }} LaundryHub. All rights reserved.</p>
                <div class="flex gap-6">
                    <a v-for="social in ['Twitter', 'Facebook', 'LinkedIn']" :key="social" href="#"
                        class="text-sm text-muted-foreground transition-colors duration-200 hover:text-foreground">
                        {{ social }}
                    </a>
                </div>
            </div>

        </div>
    </footer>
</template>
