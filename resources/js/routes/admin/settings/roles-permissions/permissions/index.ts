import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\PlatformRoleController::toggle
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:37
 * @route '/admin/settings/roles-permissions/roles/{platformRole}/permissions'
 */
export const toggle = (args: { platformRole: string | { public_id: string } } | [platformRole: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

toggle.definition = {
    methods: ["patch"],
    url: '/admin/settings/roles-permissions/roles/{platformRole}/permissions',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\PlatformRoleController::toggle
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:37
 * @route '/admin/settings/roles-permissions/roles/{platformRole}/permissions'
 */
toggle.url = (args: { platformRole: string | { public_id: string } } | [platformRole: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { platformRole: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { platformRole: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    platformRole: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        platformRole: typeof args.platformRole === 'object'
                ? args.platformRole.public_id
                : args.platformRole,
                }

    return toggle.definition.url
            .replace('{platformRole}', parsedArgs.platformRole.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\PlatformRoleController::toggle
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:37
 * @route '/admin/settings/roles-permissions/roles/{platformRole}/permissions'
 */
toggle.patch = (args: { platformRole: string | { public_id: string } } | [platformRole: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\PlatformRoleController::toggle
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:37
 * @route '/admin/settings/roles-permissions/roles/{platformRole}/permissions'
 */
    const toggleForm = (args: { platformRole: string | { public_id: string } } | [platformRole: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggle.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\PlatformRoleController::toggle
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:37
 * @route '/admin/settings/roles-permissions/roles/{platformRole}/permissions'
 */
        toggleForm.patch = (args: { platformRole: string | { public_id: string } } | [platformRole: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggle.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    toggle.form = toggleForm
const permissions = {
    toggle: Object.assign(toggle, toggle),
}

export default permissions