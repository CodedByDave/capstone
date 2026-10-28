import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:25
 * @route '/shop/employee/{employee}/schedule'
 */
export const sync = (args: { employee: string | number | { public_id: string | number } } | [employee: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: sync.url(args, options),
    method: 'put',
})

sync.definition = {
    methods: ["put"],
    url: '/shop/employee/{employee}/schedule',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:25
 * @route '/shop/employee/{employee}/schedule'
 */
sync.url = (args: { employee: string | number | { public_id: string | number } } | [employee: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { employee: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { employee: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    employee: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        employee: typeof args.employee === 'object'
                ? args.employee.public_id
                : args.employee,
                }

    return sync.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:25
 * @route '/shop/employee/{employee}/schedule'
 */
sync.put = (args: { employee: string | number | { public_id: string | number } } | [employee: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: sync.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:25
 * @route '/shop/employee/{employee}/schedule'
 */
    const syncForm = (args: { employee: string | number | { public_id: string | number } } | [employee: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: sync.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:25
 * @route '/shop/employee/{employee}/schedule'
 */
        syncForm.put = (args: { employee: string | number | { public_id: string | number } } | [employee: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: sync.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    sync.form = syncForm
/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
const destroy25c3ae7d5e6f4ced8b8a83259844cf0e = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy25c3ae7d5e6f4ced8b8a83259844cf0e.url(args, options),
    method: 'delete',
})

destroy25c3ae7d5e6f4ced8b8a83259844cf0e.definition = {
    methods: ["delete"],
    url: '/shop/employee/{employee}/schedule/{schedule}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
destroy25c3ae7d5e6f4ced8b8a83259844cf0e.url = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
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

    return destroy25c3ae7d5e6f4ced8b8a83259844cf0e.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace('{schedule}', parsedArgs.schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
destroy25c3ae7d5e6f4ced8b8a83259844cf0e.delete = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy25c3ae7d5e6f4ced8b8a83259844cf0e.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
    const destroy25c3ae7d5e6f4ced8b8a83259844cf0eForm = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy25c3ae7d5e6f4ced8b8a83259844cf0e.url(args, {
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
        destroy25c3ae7d5e6f4ced8b8a83259844cf0eForm.delete = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy25c3ae7d5e6f4ced8b8a83259844cf0e.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy25c3ae7d5e6f4ced8b8a83259844cf0e.form = destroy25c3ae7d5e6f4ced8b8a83259844cf0eForm
    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
const destroyc8c0bfb6e23a46369d3278d8e95bb4cb = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyc8c0bfb6e23a46369d3278d8e95bb4cb.url(args, options),
    method: 'delete',
})

destroyc8c0bfb6e23a46369d3278d8e95bb4cb.definition = {
    methods: ["delete"],
    url: '/staff/employee/{employee}/schedule/{schedule}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
destroyc8c0bfb6e23a46369d3278d8e95bb4cb.url = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
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

    return destroyc8c0bfb6e23a46369d3278d8e95bb4cb.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace('{schedule}', parsedArgs.schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
destroyc8c0bfb6e23a46369d3278d8e95bb4cb.delete = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyc8c0bfb6e23a46369d3278d8e95bb4cb.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:41
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
    const destroyc8c0bfb6e23a46369d3278d8e95bb4cbForm = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyc8c0bfb6e23a46369d3278d8e95bb4cb.url(args, {
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
        destroyc8c0bfb6e23a46369d3278d8e95bb4cbForm.delete = (args: { employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } } | [employee: string | number | { public_id: string | number }, schedule: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyc8c0bfb6e23a46369d3278d8e95bb4cb.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyc8c0bfb6e23a46369d3278d8e95bb4cb.form = destroyc8c0bfb6e23a46369d3278d8e95bb4cbForm

export const destroy = {
    '/shop/employee/{employee}/schedule/{schedule}': destroy25c3ae7d5e6f4ced8b8a83259844cf0e,
    '/staff/employee/{employee}/schedule/{schedule}': destroyc8c0bfb6e23a46369d3278d8e95bb4cb,
}

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
const ScheduleController = { sync, destroy, store, update }

export default ScheduleController