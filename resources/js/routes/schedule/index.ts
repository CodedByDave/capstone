import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
export const destroy = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/shop/employee/{employee}/schedule/{schedule}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
destroy.url = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    employee: args[0],
                    schedule: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        employee: typeof args.employee === 'object'
                ? args.employee.public_id
                : args.employee,
                                schedule: typeof args.schedule === 'object'
                ? args.schedule.id
                : args.schedule,
                }

    return destroy.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace('{schedule}', parsedArgs.schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
destroy.delete = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
    const destroyForm = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
        destroyForm.delete = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const schedule = {
    destroy: Object.assign(destroy, destroy),
}

export default schedule