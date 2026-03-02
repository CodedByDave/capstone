<!-- <script setup lang="ts">
import { dashboard, login, register} from '@/routes'


                <template v-if="$page.props.auth?.user">
                    <Link :href="dashboard.url()" class="px-5 py-1.5 text-sm hover:underline">Dashboard</Link>
                </template>
                 -->

<script setup lang="ts">
import LandingPage from './landing/LandingPage.vue'
</script>

<template>
    <LandingPage />
</template>
