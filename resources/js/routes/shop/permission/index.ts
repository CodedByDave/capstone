import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
import roles from './roles'
import employee from './employee'
/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::update
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:84
 * @route '/shop/permission/update'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/shop/permission/update',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::update
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:84
 * @route '/shop/permission/update'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::update
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:84
 * @route '/shop/permission/update'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::update
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:84
 * @route '/shop/permission/update'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::update
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:84
 * @route '/shop/permission/update'
 */
        updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(options),
            method: 'post',
        })
    
    update.form = updateForm
const permission = {
    update: Object.assign(update, update),
roles: Object.assign(roles, roles),
employee: Object.assign(employee, employee),
}

export default permission