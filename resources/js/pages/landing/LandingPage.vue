<script setup lang="ts">
import ThemeProvider from './ThemeProvider.vue'
import Navbar from './Navbar.vue'
import HeroSection from './HeroSection.vue'
import HowItWorksSection from './HowItWorks.vue'
import ModulesSection from './ModuleSection.vue'
import TestimonialsSection from './TestimonialSection.vue'
import TrustMarquee from './TrustMarquee.vue'
import CTASection from './CTASection.vue'
import FooterSection from './Footer.vue'
</script>

<template>
    <ThemeProvider>
        <div class="landing-page">
            <Navbar />
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
