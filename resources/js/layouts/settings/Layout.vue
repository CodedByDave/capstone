<script setup lang="ts">
import ShopLayout from '@/layouts/shop/ShopLayout.vue'
import AdminLayout from '@/layouts/admin/AdminLayout.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import Heading from '@/components/Heading.vue'
import { useActiveUrl } from '@/composables/useActiveUrl'
import { edit as editAppearance } from '@/routes/appearance'
import { edit as editProfile } from '@/routes/profile'
import { edit as editPassword } from '@/routes/user-password'
import { UserIcon, LockIcon, PaletteIcon } from 'lucide-vue-next'
import { computed } from 'vue'

const page = usePage()
const role = computed(() => (page.props.auth as any)?.user?.role)

const layout = computed(() => {
    if (role.value === 'owner' || role.value === 'staff') return ShopLayout
    return AdminLayout
})

const sidebarNavItems = [
    { title: 'Profile', href: editProfile(), icon: UserIcon },
    { title: 'Password', href: editPassword(), icon: LockIcon },
    { title: 'Appearance', href: editAppearance(), icon: PaletteIcon },
]

const { urlIsActive } = useActiveUrl()
</script>

<template>
    <component :is="layout" title="Settings">
        <div class="px-6">
            <div class="flex flex-col lg:flex-row lg:space-x-12">
                <aside class="w-full max-w-xs lg:w-48">
                    <nav class="flex flex-col space-y-2" aria-label="Settings">
                        <Button v-for="item in sidebarNavItems" :key="item.href" variant="ghost"
                            :class="['w-full justify-start', { 'bg-muted': urlIsActive(item.href) }]" as-child>
                            <Link :href="item.href" class="flex items-center space-x-2">
                                <component :is="item.icon" class="h-4 w-4" />
                                <span>{{ item.title }}</span>
                            </Link>
                        </Button>
                    </nav>
                </aside>

                <Separator class="my-6 lg:hidden" />

                <div class="flex-1 md:max-w-2xl">
                    <Heading title="Settings" description="Manage your profile and account settings" />
                    <section class="max-w-xl space-y-12">
                        <slot />
                    </section>
                </div>
            </div>
        </div>
    </component>
</template>
