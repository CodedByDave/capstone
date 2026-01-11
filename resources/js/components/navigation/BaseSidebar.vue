<script setup lang="ts">
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar'
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <!-- Header -->
        <SidebarHeader>
            <slot name="header" />
        </SidebarHeader>

        <!-- Main -->
        <SidebarContent>
            <slot />
        </SidebarContent>

        <!-- Footer -->
        <SidebarFooter>
            <slot name="footer" />
        </SidebarFooter>
    </Sidebar>
</template>
