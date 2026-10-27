import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::index
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:26
 * @route '/shop/permission'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/permission',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::index
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:26
 * @route '/shop/permission'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::index
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:26
 * @route '/shop/permission'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::index
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:26
 * @route '/shop/permission'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::index
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:26
 * @route '/shop/permission'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::index
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:26
 * @route '/shop/permission'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::index
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:26
 * @route '/shop/permission'
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
* @see \App\Http\Controllers\Shop\Employee\PermissionController::updatePermission
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:89
 * @route '/shop/permission/update'
 */
export const updatePermission = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updatePermission.url(options),
    method: 'post',
})

updatePermission.definition = {
    methods: ["post"],
    url: '/shop/permission/update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::updatePermission
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:89
 * @route '/shop/permission/update'
 */
updatePermission.url = (options?: RouteQueryOptions) => {
    return updatePermission.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::updatePermission
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:89
 * @route '/shop/permission/update'
 */
updatePermission.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updatePermission.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::updatePermission
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:89
 * @route '/shop/permission/update'
 */
    const updatePermissionForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updatePermission.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::updatePermission
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:89
 * @route '/shop/permission/update'
 */
        updatePermissionForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updatePermission.url(options),
            method: 'post',
        })
    
    updatePermission.form = updatePermissionForm
/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::storeRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:117
 * @route '/shop/permission/roles'
 */
export const storeRole = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeRole.url(options),
    method: 'post',
})

storeRole.definition = {
    methods: ["post"],
    url: '/shop/permission/roles',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::storeRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:117
 * @route '/shop/permission/roles'
 */
storeRole.url = (options?: RouteQueryOptions) => {
    return storeRole.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::storeRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:117
 * @route '/shop/permission/roles'
 */
storeRole.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeRole.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::storeRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:117
 * @route '/shop/permission/roles'
 */
    const storeRoleForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeRole.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::storeRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:117
 * @route '/shop/permission/roles'
 */
        storeRoleForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeRole.url(options),
            method: 'post',
        })
    
    storeRole.form = storeRoleForm
/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::destroyRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:147
 * @route '/shop/permission/roles/{role}'
 */
export const destroyRole = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyRole.url(args, options),
    method: 'delete',
})

destroyRole.definition = {
    methods: ["delete"],
    url: '/shop/permission/roles/{role}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::destroyRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:147
 * @route '/shop/permission/roles/{role}'
 */
destroyRole.url = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return destroyRole.definition.url
            .replace('{role}', parsedArgs.role.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::destroyRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:147
 * @route '/shop/permission/roles/{role}'
 */
destroyRole.delete = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyRole.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::destroyRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:147
 * @route '/shop/permission/roles/{role}'
 */
    const destroyRoleForm = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyRole.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::destroyRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:147
 * @route '/shop/permission/roles/{role}'
 */
        destroyRoleForm.delete = (args: { role: string | number } | [role: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyRole.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyRole.form = destroyRoleForm
/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::toggleEmployeeRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:175
 * @route '/shop/permission/employee/{employeeId}/toggle-role'
 */
export const toggleEmployeeRole = (args: { employeeId: string | number } | [employeeId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleEmployeeRole.url(args, options),
    method: 'post',
})

toggleEmployeeRole.definition = {
    methods: ["post"],
    url: '/shop/permission/employee/{employeeId}/toggle-role',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::toggleEmployeeRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:175
 * @route '/shop/permission/employee/{employeeId}/toggle-role'
 */
toggleEmployeeRole.url = (args: { employeeId: string | number } | [employeeId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { employeeId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    employeeId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        employeeId: args.employeeId,
                }

    return toggleEmployeeRole.definition.url
            .replace('{employeeId}', parsedArgs.employeeId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::toggleEmployeeRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:175
 * @route '/shop/permission/employee/{employeeId}/toggle-role'
 */
toggleEmployeeRole.post = (args: { employeeId: string | number } | [employeeId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggleEmployeeRole.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::toggleEmployeeRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:175
 * @route '/shop/permission/employee/{employeeId}/toggle-role'
 */
    const toggleEmployeeRoleForm = (args: { employeeId: string | number } | [employeeId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggleEmployeeRole.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::toggleEmployeeRole
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:175
 * @route '/shop/permission/employee/{employeeId}/toggle-role'
 */
        toggleEmployeeRoleForm.post = (args: { employeeId: string | number } | [employeeId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggleEmployeeRole.url(args, options),
            method: 'post',
        })
    
    toggleEmployeeRole.form = toggleEmployeeRoleForm
const PermissionController = { index, updatePermission, storeRole, destroyRole, toggleEmployeeRole }

export default PermissionController