import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::store
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:117
 * @route '/shop/permission/roles'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/shop/permission/roles',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::store
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:117
 * @route '/shop/permission/roles'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::store
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:117
 * @route '/shop/permission/roles'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::store
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:117
 * @route '/shop/permission/roles'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::store
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:117
 * @route '/shop/permission/roles'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::destroy
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:147
 * @route '/shop/permission/roles/{role}'
 */
export const destroy = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/shop/permission/roles/{role}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::destroy
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:147
 * @route '/shop/permission/roles/{role}'
 */
destroy.url = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { role: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    role: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        role: args.role,
                }

    return destroy.definition.url
            .replace('{role}', parsedArgs.role.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::destroy
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:147
 * @route '/shop/permission/roles/{role}'
 */
destroy.delete = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::destroy
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:147
 * @route '/shop/permission/roles/{role}'
 */
    const destroyForm = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::destroy
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:147
 * @route '/shop/permission/roles/{role}'
 */
        destroyForm.delete = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const roles = {
    store: Object.assign(store, store),
destroy: Object.assign(destroy, destroy),
}

export default roles