import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::update
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:124
 * @route '/shop/payroll/{payrollItem}/item'
 */
export const update = (args: { payrollItem: string | number | { public_id: string | number } } | [payrollItem: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/shop/payroll/{payrollItem}/item',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::update
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:124
 * @route '/shop/payroll/{payrollItem}/item'
 */
update.url = (args: { payrollItem: string | number | { public_id: string | number } } | [payrollItem: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { payrollItem: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { payrollItem: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    payrollItem: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        payrollItem: typeof args.payrollItem === 'object'
                ? args.payrollItem.public_id
                : args.payrollItem,
                }

    return update.definition.url
            .replace('{payrollItem}', parsedArgs.payrollItem.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::update
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:124
 * @route '/shop/payroll/{payrollItem}/item'
 */
update.put = (args: { payrollItem: string | number | { public_id: string | number } } | [payrollItem: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::update
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:124
 * @route '/shop/payroll/{payrollItem}/item'
 */
    const updateForm = (args: { payrollItem: string | number | { public_id: string | number } } | [payrollItem: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::update
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:124
 * @route '/shop/payroll/{payrollItem}/item'
 */
        updateForm.put = (args: { payrollItem: string | number | { public_id: string | number } } | [payrollItem: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
const item = {
    update: Object.assign(update, update),
}

export default item