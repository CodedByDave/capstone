import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::toggle
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:175
 * @route '/shop/permission/employee/{employeeId}/toggle-role'
 */
export const toggle = (args: { employeeId: string | number } | [employeeId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggle.url(args, options),
    method: 'post',
})

toggle.definition = {
    methods: ["post"],
    url: '/shop/permission/employee/{employeeId}/toggle-role',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::toggle
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:175
 * @route '/shop/permission/employee/{employeeId}/toggle-role'
 */
toggle.url = (args: { employeeId: string | number } | [employeeId: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return toggle.definition.url
            .replace('{employeeId}', parsedArgs.employeeId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::toggle
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:175
 * @route '/shop/permission/employee/{employeeId}/toggle-role'
 */
toggle.post = (args: { employeeId: string | number } | [employeeId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: toggle.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::toggle
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:175
 * @route '/shop/permission/employee/{employeeId}/toggle-role'
 */
    const toggleForm = (args: { employeeId: string | number } | [employeeId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggle.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::toggle
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:175
 * @route '/shop/permission/employee/{employeeId}/toggle-role'
 */
        toggleForm.post = (args: { employeeId: string | number } | [employeeId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggle.url(args, options),
            method: 'post',
        })
    
    toggle.form = toggleForm
const employee = {
    toggle: Object.assign(toggle, toggle),
}

export default employee