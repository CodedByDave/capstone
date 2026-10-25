<script setup lang="ts">
import CTASection from './CTASection.vue';
import FooterSection from './Footer.vue';
import HeroSection from './HeroSection.vue';
import HowItWorksSection from './HowItWorks.vue';
import ModulesSection from './ModuleSection.vue';
import Navbar from './Navbar.vue';
import TestimonialsSection from './TestimonialSection.vue';
import ThemeProvider from './ThemeProvider.vue';
import TrustMarquee from './TrustMarquee.vue';

defineProps<{ shopCount?: number }>();
</script>

<template>
    <ThemeProvider>
        <div class="landing-page">
            <Navbar />
            <HeroSection :shop-count="shopCount" />
            <TrustMarquee :shop-count="shopCount" />
            <ModulesSection />
            <HowItWorksSection />
            <TestimonialsSection />
            <CTASection />
            <FooterSection />
        </div>
    </ThemeProvider>
</template>
