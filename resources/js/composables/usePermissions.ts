import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function usePermissions() {
    const page = usePage()

    const user = computed(() => page.props.auth?.user)
    const isOwner = computed(() => user.value?.role === 'owner')
    const isStaff = computed(() => user.value?.role === 'staff')

    // Owner has all permissions
    function can(module: string, action: string): boolean {
        if (isOwner.value) return true

        const permissions = user.value?.permissions ?? {}
        return permissions[module]?.includes(action) ?? false
    }

    function canAccessModule(module: string): boolean {
        if (isOwner.value) return true

        const permissions = user.value?.permissions ?? {}
        return module in permissions && permissions[module].length > 0
    }

    return { can, canAccessModule, isOwner, isStaff, user }
}
