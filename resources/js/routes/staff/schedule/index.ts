import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:0
 * @route '/staff/employee/{employee}/schedule'
 */
export const store = (args: { employee: string | number } | [employee: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/staff/employee/{employee}/schedule',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:0
 * @route '/staff/employee/{employee}/schedule'
 */
store.url = (args: { employee: string | number } | [employee: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { employee: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    employee: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        employee: args.employee,
                }

    return store.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:0
 * @route '/staff/employee/{employee}/schedule'
 */
store.post = (args: { employee: string | number } | [employee: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:0
 * @route '/staff/employee/{employee}/schedule'
 */
    const storeForm = (args: { employee: string | number } | [employee: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:0
 * @route '/staff/employee/{employee}/schedule'
 */
        storeForm.post = (args: { employee: string | number } | [employee: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(args, options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::update
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:32
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
export const update = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/staff/employee/{employee}/schedule/{schedule}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::update
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:32
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
update.url = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace('{schedule}', parsedArgs.schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::update
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:32
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
update.put = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::update
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:32
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
    const updateForm = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::update
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:32
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
        updateForm.put = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
export const destroy = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/staff/employee/{employee}/schedule/{schedule}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
destroy.url = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
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
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
destroy.delete = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
    const destroyForm = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
        destroyForm.delete = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
    store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default schedule