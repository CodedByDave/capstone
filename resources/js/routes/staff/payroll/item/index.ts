import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::update
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/staff/payroll/{payrollItem}/item'
 */
export const update = (args: { payrollItem: number | { id: number } } | [payrollItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/staff/payroll/{payrollItem}/item',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::update
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/staff/payroll/{payrollItem}/item'
 */
update.url = (args: { payrollItem: number | { id: number } } | [payrollItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { payrollItem: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { payrollItem: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    payrollItem: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        payrollItem: typeof args.payrollItem === 'object'
                ? args.payrollItem.id
                : args.payrollItem,
                }

    return update.definition.url
            .replace('{payrollItem}', parsedArgs.payrollItem.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::update
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/staff/payroll/{payrollItem}/item'
 */
update.put = (args: { payrollItem: number | { id: number } } | [payrollItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::update
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/staff/payroll/{payrollItem}/item'
 */
    const updateForm = (args: { payrollItem: number | { id: number } } | [payrollItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/staff/payroll/{payrollItem}/item'
 */
        updateForm.put = (args: { payrollItem: number | { id: number } } | [payrollItem: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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