import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:22
 * @route '/shop/employee/{employee}/schedule'
 */
const sync62377536a33e0ceeeb98c1a9b0c5e059 = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: sync62377536a33e0ceeeb98c1a9b0c5e059.url(args, options),
    method: 'put',
})

sync62377536a33e0ceeeb98c1a9b0c5e059.definition = {
    methods: ["put"],
    url: '/shop/employee/{employee}/schedule',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:22
 * @route '/shop/employee/{employee}/schedule'
 */
sync62377536a33e0ceeeb98c1a9b0c5e059.url = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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

    return sync62377536a33e0ceeeb98c1a9b0c5e059.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:22
 * @route '/shop/employee/{employee}/schedule'
 */
sync62377536a33e0ceeeb98c1a9b0c5e059.put = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: sync62377536a33e0ceeeb98c1a9b0c5e059.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:22
 * @route '/shop/employee/{employee}/schedule'
 */
    const sync62377536a33e0ceeeb98c1a9b0c5e059Form = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: sync62377536a33e0ceeeb98c1a9b0c5e059.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:22
 * @route '/shop/employee/{employee}/schedule'
 */
        sync62377536a33e0ceeeb98c1a9b0c5e059Form.put = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: sync62377536a33e0ceeeb98c1a9b0c5e059.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    sync62377536a33e0ceeeb98c1a9b0c5e059.form = sync62377536a33e0ceeeb98c1a9b0c5e059Form
    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:22
 * @route '/staff/employee/{employee}/schedule'
 */
const sync2408df5224a6b3943e27ad3b804d5400 = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: sync2408df5224a6b3943e27ad3b804d5400.url(args, options),
    method: 'put',
})

sync2408df5224a6b3943e27ad3b804d5400.definition = {
    methods: ["put"],
    url: '/staff/employee/{employee}/schedule',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:22
 * @route '/staff/employee/{employee}/schedule'
 */
sync2408df5224a6b3943e27ad3b804d5400.url = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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

    return sync2408df5224a6b3943e27ad3b804d5400.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:22
 * @route '/staff/employee/{employee}/schedule'
 */
sync2408df5224a6b3943e27ad3b804d5400.put = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: sync2408df5224a6b3943e27ad3b804d5400.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:22
 * @route '/staff/employee/{employee}/schedule'
 */
    const sync2408df5224a6b3943e27ad3b804d5400Form = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: sync2408df5224a6b3943e27ad3b804d5400.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::sync
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:22
 * @route '/staff/employee/{employee}/schedule'
 */
        sync2408df5224a6b3943e27ad3b804d5400Form.put = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: sync2408df5224a6b3943e27ad3b804d5400.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    sync2408df5224a6b3943e27ad3b804d5400.form = sync2408df5224a6b3943e27ad3b804d5400Form

export const sync = {
    '/shop/employee/{employee}/schedule': sync62377536a33e0ceeeb98c1a9b0c5e059,
    '/staff/employee/{employee}/schedule': sync2408df5224a6b3943e27ad3b804d5400,
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:27
 * @route '/shop/employee/{employee}/schedule'
 */
const store62377536a33e0ceeeb98c1a9b0c5e059 = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store62377536a33e0ceeeb98c1a9b0c5e059.url(args, options),
    method: 'post',
})

store62377536a33e0ceeeb98c1a9b0c5e059.definition = {
    methods: ["post"],
    url: '/shop/employee/{employee}/schedule',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:27
 * @route '/shop/employee/{employee}/schedule'
 */
store62377536a33e0ceeeb98c1a9b0c5e059.url = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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

    return store62377536a33e0ceeeb98c1a9b0c5e059.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:27
 * @route '/shop/employee/{employee}/schedule'
 */
store62377536a33e0ceeeb98c1a9b0c5e059.post = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store62377536a33e0ceeeb98c1a9b0c5e059.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:27
 * @route '/shop/employee/{employee}/schedule'
 */
    const store62377536a33e0ceeeb98c1a9b0c5e059Form = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store62377536a33e0ceeeb98c1a9b0c5e059.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:27
 * @route '/shop/employee/{employee}/schedule'
 */
        store62377536a33e0ceeeb98c1a9b0c5e059Form.post = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store62377536a33e0ceeeb98c1a9b0c5e059.url(args, options),
            method: 'post',
        })
    
    store62377536a33e0ceeeb98c1a9b0c5e059.form = store62377536a33e0ceeeb98c1a9b0c5e059Form
    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:27
 * @route '/staff/employee/{employee}/schedule'
 */
const store2408df5224a6b3943e27ad3b804d5400 = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store2408df5224a6b3943e27ad3b804d5400.url(args, options),
    method: 'post',
})

store2408df5224a6b3943e27ad3b804d5400.definition = {
    methods: ["post"],
    url: '/staff/employee/{employee}/schedule',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:27
 * @route '/staff/employee/{employee}/schedule'
 */
store2408df5224a6b3943e27ad3b804d5400.url = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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

    return store2408df5224a6b3943e27ad3b804d5400.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:27
 * @route '/staff/employee/{employee}/schedule'
 */
store2408df5224a6b3943e27ad3b804d5400.post = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store2408df5224a6b3943e27ad3b804d5400.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:27
 * @route '/staff/employee/{employee}/schedule'
 */
    const store2408df5224a6b3943e27ad3b804d5400Form = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store2408df5224a6b3943e27ad3b804d5400.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::store
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:27
 * @route '/staff/employee/{employee}/schedule'
 */
        store2408df5224a6b3943e27ad3b804d5400Form.post = (args: { employee: string | { public_id: string } } | [employee: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store2408df5224a6b3943e27ad3b804d5400.url(args, options),
            method: 'post',
        })
    
    store2408df5224a6b3943e27ad3b804d5400.form = store2408df5224a6b3943e27ad3b804d5400Form

export const store = {
    '/shop/employee/{employee}/schedule': store62377536a33e0ceeeb98c1a9b0c5e059,
    '/staff/employee/{employee}/schedule': store2408df5224a6b3943e27ad3b804d5400,
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::update
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:32
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
const update25c3ae7d5e6f4ced8b8a83259844cf0e = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update25c3ae7d5e6f4ced8b8a83259844cf0e.url(args, options),
    method: 'put',
})

update25c3ae7d5e6f4ced8b8a83259844cf0e.definition = {
    methods: ["put"],
    url: '/shop/employee/{employee}/schedule/{schedule}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::update
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:32
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
update25c3ae7d5e6f4ced8b8a83259844cf0e.url = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions) => {
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

    return update25c3ae7d5e6f4ced8b8a83259844cf0e.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace('{schedule}', parsedArgs.schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::update
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:32
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
update25c3ae7d5e6f4ced8b8a83259844cf0e.put = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update25c3ae7d5e6f4ced8b8a83259844cf0e.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::update
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:32
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
    const update25c3ae7d5e6f4ced8b8a83259844cf0eForm = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update25c3ae7d5e6f4ced8b8a83259844cf0e.url(args, {
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
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
        update25c3ae7d5e6f4ced8b8a83259844cf0eForm.put = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update25c3ae7d5e6f4ced8b8a83259844cf0e.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update25c3ae7d5e6f4ced8b8a83259844cf0e.form = update25c3ae7d5e6f4ced8b8a83259844cf0eForm
    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::update
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:32
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
const updatec8c0bfb6e23a46369d3278d8e95bb4cb = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatec8c0bfb6e23a46369d3278d8e95bb4cb.url(args, options),
    method: 'put',
})

updatec8c0bfb6e23a46369d3278d8e95bb4cb.definition = {
    methods: ["put"],
    url: '/staff/employee/{employee}/schedule/{schedule}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::update
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:32
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
updatec8c0bfb6e23a46369d3278d8e95bb4cb.url = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions) => {
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

    return updatec8c0bfb6e23a46369d3278d8e95bb4cb.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace('{schedule}', parsedArgs.schedule.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::update
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:32
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
updatec8c0bfb6e23a46369d3278d8e95bb4cb.put = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatec8c0bfb6e23a46369d3278d8e95bb4cb.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::update
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:32
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
    const updatec8c0bfb6e23a46369d3278d8e95bb4cbForm = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updatec8c0bfb6e23a46369d3278d8e95bb4cb.url(args, {
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
        updatec8c0bfb6e23a46369d3278d8e95bb4cbForm.put = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updatec8c0bfb6e23a46369d3278d8e95bb4cb.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updatec8c0bfb6e23a46369d3278d8e95bb4cb.form = updatec8c0bfb6e23a46369d3278d8e95bb4cbForm

export const update = {
    '/shop/employee/{employee}/schedule/{schedule}': update25c3ae7d5e6f4ced8b8a83259844cf0e,
    '/staff/employee/{employee}/schedule/{schedule}': updatec8c0bfb6e23a46369d3278d8e95bb4cb,
}

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:39
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
const destroy25c3ae7d5e6f4ced8b8a83259844cf0e = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy25c3ae7d5e6f4ced8b8a83259844cf0e.url(args, options),
    method: 'delete',
})

destroy25c3ae7d5e6f4ced8b8a83259844cf0e.definition = {
    methods: ["delete"],
    url: '/shop/employee/{employee}/schedule/{schedule}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:39
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
destroy25c3ae7d5e6f4ced8b8a83259844cf0e.url = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions) => {
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
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:39
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
destroy25c3ae7d5e6f4ced8b8a83259844cf0e.delete = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy25c3ae7d5e6f4ced8b8a83259844cf0e.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:39
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
    const destroy25c3ae7d5e6f4ced8b8a83259844cf0eForm = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:39
 * @route '/shop/employee/{employee}/schedule/{schedule}'
 */
        destroy25c3ae7d5e6f4ced8b8a83259844cf0eForm.delete = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:39
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
const destroyc8c0bfb6e23a46369d3278d8e95bb4cb = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyc8c0bfb6e23a46369d3278d8e95bb4cb.url(args, options),
    method: 'delete',
})

destroyc8c0bfb6e23a46369d3278d8e95bb4cb.definition = {
    methods: ["delete"],
    url: '/staff/employee/{employee}/schedule/{schedule}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:39
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
destroyc8c0bfb6e23a46369d3278d8e95bb4cb.url = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions) => {
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
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:39
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
destroyc8c0bfb6e23a46369d3278d8e95bb4cb.delete = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyc8c0bfb6e23a46369d3278d8e95bb4cb.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ScheduleController::destroy
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:39
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
    const destroyc8c0bfb6e23a46369d3278d8e95bb4cbForm = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/Employee/ScheduleController.php:39
 * @route '/staff/employee/{employee}/schedule/{schedule}'
 */
        destroyc8c0bfb6e23a46369d3278d8e95bb4cbForm.delete = (args: { employee: string | { public_id: string }, schedule: number | { id: number } } | [employee: string | { public_id: string }, schedule: number | { id: number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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

const ScheduleController = { sync, store, update, destroy }

export default ScheduleController