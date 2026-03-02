<script setup lang="ts">
import { ref } from 'vue'

import ThemeProvider from './ThemeProvider.vue'
import Navbar from './Navbar.vue'
import HeroSection from './HeroSection.vue'
import HowItWorksSection from './HowItWorks.vue'
import ModulesSection from './ModuleSection.vue'
import TestimonialsSection from './TestimonialSection.vue'
import TrustMarquee from './TrustMarquee.vue'
import CTASection from './CTASection.vue'
import FooterSection from './Footer.vue'

const showMobileMenu = ref(false)

const scrollToSection = (sectionId: string) => {
    const el = document.getElementById(sectionId)
    if (el) el.scrollIntoView({ behavior: 'smooth' })
    showMobileMenu.value = false
}
</script>

<template>
    <ThemeProvider>
        <div class="landing-page">
            <Navbar :showMobileMenu="showMobileMenu" @toggleMobileMenu="showMobileMenu = !showMobileMenu" />
            <HeroSection />
            <TrustMarquee />
            <ModulesSection />
            <HowItWorksSection />
            <TestimonialsSection />
            <CTASection />
            <FooterSection />
        </div>
    </ThemeProvider>
</template>
