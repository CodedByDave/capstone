import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
import permissions from './permissions'
/**
* @see \App\Http\Controllers\Admin\PlatformRoleController::index
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:21
 * @route '/admin/settings/roles-permissions'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/settings/roles-permissions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\PlatformRoleController::index
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:21
 * @route '/admin/settings/roles-permissions'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\PlatformRoleController::index
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:21
 * @route '/admin/settings/roles-permissions'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\PlatformRoleController::index
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:21
 * @route '/admin/settings/roles-permissions'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\PlatformRoleController::index
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:21
 * @route '/admin/settings/roles-permissions'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\PlatformRoleController::index
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:21
 * @route '/admin/settings/roles-permissions'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\PlatformRoleController::index
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:21
 * @route '/admin/settings/roles-permissions'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\Admin\PlatformRoleController::store
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:29
 * @route '/admin/settings/roles-permissions/roles'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/settings/roles-permissions/roles',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\PlatformRoleController::store
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:29
 * @route '/admin/settings/roles-permissions/roles'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\PlatformRoleController::store
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:29
 * @route '/admin/settings/roles-permissions/roles'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\PlatformRoleController::store
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:29
 * @route '/admin/settings/roles-permissions/roles'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\PlatformRoleController::store
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:29
 * @route '/admin/settings/roles-permissions/roles'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\PlatformRoleController::destroy
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:49
 * @route '/admin/settings/roles-permissions/roles/{platformRole}'
 */
export const destroy = (args: { platformRole: string | number | { public_id: string | number } } | [platformRole: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/settings/roles-permissions/roles/{platformRole}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\PlatformRoleController::destroy
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:49
 * @route '/admin/settings/roles-permissions/roles/{platformRole}'
 */
destroy.url = (args: { platformRole: string | number | { public_id: string | number } } | [platformRole: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{platformRole}', parsedArgs.platformRole.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\PlatformRoleController::destroy
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:49
 * @route '/admin/settings/roles-permissions/roles/{platformRole}'
 */
destroy.delete = (args: { platformRole: string | number | { public_id: string | number } } | [platformRole: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\PlatformRoleController::destroy
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:49
 * @route '/admin/settings/roles-permissions/roles/{platformRole}'
 */
    const destroyForm = (args: { platformRole: string | number | { public_id: string | number } } | [platformRole: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\PlatformRoleController::destroy
 * @see app/Http/Controllers/Admin/PlatformRoleController.php:49
 * @route '/admin/settings/roles-permissions/roles/{platformRole}'
 */
        destroyForm.delete = (args: { platformRole: string | number | { public_id: string | number } } | [platformRole: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const rolesPermissions = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
permissions: Object.assign(permissions, permissions),
destroy: Object.assign(destroy, destroy),
}

export default rolesPermissions