<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar'
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible'
import { useActiveUrl } from '@/composables/useActiveUrl'
import { type NavItem } from '@/types'
import { Link } from '@inertiajs/vue3'
import { ChevronDown } from 'lucide-vue-next'
import { ref } from 'vue'

defineProps<{
    items: NavItem[]
}>()

const { urlIsActive } = useActiveUrl()

function isGroupActive(item: NavItem): boolean {
    return item.children?.some(child => urlIsActive(child.href)) ?? false
}

// Initialize open state — open if a child is active
const openItems = ref<Record<string, boolean>>({})

function getOpen(item: NavItem): boolean {
    if (!(item.title in openItems.value)) {
        openItems.value[item.title] = isGroupActive(item)
    }
    return openItems.value[item.title]
}

function setOpen(title: string, val: boolean) {
    openItems.value[title] = val
}
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="item in items" :key="item.title">

                <!-- ── Collapsible group ── -->
                <Collapsible
                    v-if="item.children?.length"
                    as-child
                    :open="getOpen(item)"
                    @update:open="(val) => setOpen(item.title, val)"
                >
                    <SidebarMenuItem>

                        <!-- The entire row is the trigger -->
                        <CollapsibleTrigger as-child>
                            <SidebarMenuButton
                                :is-active="isGroupActive(item)"
                                :tooltip="item.title"
                            >
                                <component :is="item.icon" />
                                <span class="flex-1">{{ item.title }}</span>
                                <ChevronDown
                                    class="h-4 w-4 shrink-0 transition-transform duration-200"
                                    :class="{ 'rotate-180': getOpen(item) }"
                                />
                            </SidebarMenuButton>
                        </CollapsibleTrigger>

                        <CollapsibleContent>
                            <SidebarMenuSub>
                                <SidebarMenuSubItem
                                    v-for="child in item.children"
                                    :key="child.title"
                                >
                                    <SidebarMenuSubButton
                                        as-child
                                        :is-active="urlIsActive(child.href)"
                                    >
                                        <Link :href="child.href">
                                            <component v-if="child.icon" :is="child.icon" />
                                            <span>{{ child.title }}</span>
                                        </Link>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            </SidebarMenuSub>
                        </CollapsibleContent>

                    </SidebarMenuItem>
                </Collapsible>

                <!-- ── Regular item ── -->
                <SidebarMenuItem v-else>
                    <SidebarMenuButton
                        as-child
                        :is-active="urlIsActive(item.href)"
                        :tooltip="item.title"
                    >
                        <Link :href="item.href">
                            <component :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>

            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
