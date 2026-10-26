import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/shop/attendance'
 */
const index0e43962a8422a985c1ec144f432ca3d5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index0e43962a8422a985c1ec144f432ca3d5.url(options),
    method: 'get',
})

index0e43962a8422a985c1ec144f432ca3d5.definition = {
    methods: ["get","head"],
    url: '/shop/attendance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/shop/attendance'
 */
index0e43962a8422a985c1ec144f432ca3d5.url = (options?: RouteQueryOptions) => {
    return index0e43962a8422a985c1ec144f432ca3d5.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/shop/attendance'
 */
index0e43962a8422a985c1ec144f432ca3d5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index0e43962a8422a985c1ec144f432ca3d5.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/shop/attendance'
 */
index0e43962a8422a985c1ec144f432ca3d5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index0e43962a8422a985c1ec144f432ca3d5.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/shop/attendance'
 */
    const index0e43962a8422a985c1ec144f432ca3d5Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index0e43962a8422a985c1ec144f432ca3d5.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/shop/attendance'
 */
        index0e43962a8422a985c1ec144f432ca3d5Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index0e43962a8422a985c1ec144f432ca3d5.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/shop/attendance'
 */
        index0e43962a8422a985c1ec144f432ca3d5Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index0e43962a8422a985c1ec144f432ca3d5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index0e43962a8422a985c1ec144f432ca3d5.form = index0e43962a8422a985c1ec144f432ca3d5Form
    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/staff/attendance'
 */
const indexb2509f7bc76b442fc089cd06ff9021af = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexb2509f7bc76b442fc089cd06ff9021af.url(options),
    method: 'get',
})

indexb2509f7bc76b442fc089cd06ff9021af.definition = {
    methods: ["get","head"],
    url: '/staff/attendance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/staff/attendance'
 */
indexb2509f7bc76b442fc089cd06ff9021af.url = (options?: RouteQueryOptions) => {
    return indexb2509f7bc76b442fc089cd06ff9021af.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/staff/attendance'
 */
indexb2509f7bc76b442fc089cd06ff9021af.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexb2509f7bc76b442fc089cd06ff9021af.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/staff/attendance'
 */
indexb2509f7bc76b442fc089cd06ff9021af.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexb2509f7bc76b442fc089cd06ff9021af.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/staff/attendance'
 */
    const indexb2509f7bc76b442fc089cd06ff9021afForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexb2509f7bc76b442fc089cd06ff9021af.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/staff/attendance'
 */
        indexb2509f7bc76b442fc089cd06ff9021afForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexb2509f7bc76b442fc089cd06ff9021af.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/staff/attendance'
 */
        indexb2509f7bc76b442fc089cd06ff9021afForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexb2509f7bc76b442fc089cd06ff9021af.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexb2509f7bc76b442fc089cd06ff9021af.form = indexb2509f7bc76b442fc089cd06ff9021afForm

export const index = {
    '/shop/attendance': index0e43962a8422a985c1ec144f432ca3d5,
    '/staff/attendance': indexb2509f7bc76b442fc089cd06ff9021af,
}

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/shop/attendance'
 */
const store0e43962a8422a985c1ec144f432ca3d5 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store0e43962a8422a985c1ec144f432ca3d5.url(options),
    method: 'post',
})

store0e43962a8422a985c1ec144f432ca3d5.definition = {
    methods: ["post"],
    url: '/shop/attendance',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/shop/attendance'
 */
store0e43962a8422a985c1ec144f432ca3d5.url = (options?: RouteQueryOptions) => {
    return store0e43962a8422a985c1ec144f432ca3d5.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/shop/attendance'
 */
store0e43962a8422a985c1ec144f432ca3d5.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store0e43962a8422a985c1ec144f432ca3d5.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/shop/attendance'
 */
    const store0e43962a8422a985c1ec144f432ca3d5Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store0e43962a8422a985c1ec144f432ca3d5.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/shop/attendance'
 */
        store0e43962a8422a985c1ec144f432ca3d5Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store0e43962a8422a985c1ec144f432ca3d5.url(options),
            method: 'post',
        })
    
    store0e43962a8422a985c1ec144f432ca3d5.form = store0e43962a8422a985c1ec144f432ca3d5Form
    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/staff/attendance'
 */
const storeb2509f7bc76b442fc089cd06ff9021af = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeb2509f7bc76b442fc089cd06ff9021af.url(options),
    method: 'post',
})

storeb2509f7bc76b442fc089cd06ff9021af.definition = {
    methods: ["post"],
    url: '/staff/attendance',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/staff/attendance'
 */
storeb2509f7bc76b442fc089cd06ff9021af.url = (options?: RouteQueryOptions) => {
    return storeb2509f7bc76b442fc089cd06ff9021af.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/staff/attendance'
 */
storeb2509f7bc76b442fc089cd06ff9021af.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeb2509f7bc76b442fc089cd06ff9021af.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/staff/attendance'
 */
    const storeb2509f7bc76b442fc089cd06ff9021afForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeb2509f7bc76b442fc089cd06ff9021af.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/staff/attendance'
 */
        storeb2509f7bc76b442fc089cd06ff9021afForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeb2509f7bc76b442fc089cd06ff9021af.url(options),
            method: 'post',
        })
    
    storeb2509f7bc76b442fc089cd06ff9021af.form = storeb2509f7bc76b442fc089cd06ff9021afForm

export const store = {
    '/shop/attendance': store0e43962a8422a985c1ec144f432ca3d5,
    '/staff/attendance': storeb2509f7bc76b442fc089cd06ff9021af,
}

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/shop/attendance/{attendance}'
 */
const update1fa2a162924161645e27c9409c12cbbc = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update1fa2a162924161645e27c9409c12cbbc.url(args, options),
    method: 'put',
})

update1fa2a162924161645e27c9409c12cbbc.definition = {
    methods: ["put"],
    url: '/shop/attendance/{attendance}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/shop/attendance/{attendance}'
 */
update1fa2a162924161645e27c9409c12cbbc.url = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { attendance: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { attendance: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    attendance: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        attendance: typeof args.attendance === 'object'
                ? args.attendance.id
                : args.attendance,
                }

    return update1fa2a162924161645e27c9409c12cbbc.definition.url
            .replace('{attendance}', parsedArgs.attendance.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/shop/attendance/{attendance}'
 */
update1fa2a162924161645e27c9409c12cbbc.put = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update1fa2a162924161645e27c9409c12cbbc.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/shop/attendance/{attendance}'
 */
    const update1fa2a162924161645e27c9409c12cbbcForm = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update1fa2a162924161645e27c9409c12cbbc.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/shop/attendance/{attendance}'
 */
        update1fa2a162924161645e27c9409c12cbbcForm.put = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update1fa2a162924161645e27c9409c12cbbc.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update1fa2a162924161645e27c9409c12cbbc.form = update1fa2a162924161645e27c9409c12cbbcForm
    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/staff/attendance/{attendance}'
 */
const update1a75db1137789a1fa4b91c4a0368eaef = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update1a75db1137789a1fa4b91c4a0368eaef.url(args, options),
    method: 'put',
})

update1a75db1137789a1fa4b91c4a0368eaef.definition = {
    methods: ["put"],
    url: '/staff/attendance/{attendance}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/staff/attendance/{attendance}'
 */
update1a75db1137789a1fa4b91c4a0368eaef.url = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { attendance: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { attendance: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    attendance: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        attendance: typeof args.attendance === 'object'
                ? args.attendance.id
                : args.attendance,
                }

    return update1a75db1137789a1fa4b91c4a0368eaef.definition.url
            .replace('{attendance}', parsedArgs.attendance.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/staff/attendance/{attendance}'
 */
update1a75db1137789a1fa4b91c4a0368eaef.put = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update1a75db1137789a1fa4b91c4a0368eaef.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/staff/attendance/{attendance}'
 */
    const update1a75db1137789a1fa4b91c4a0368eaefForm = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update1a75db1137789a1fa4b91c4a0368eaef.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/staff/attendance/{attendance}'
 */
        update1a75db1137789a1fa4b91c4a0368eaefForm.put = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update1a75db1137789a1fa4b91c4a0368eaef.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update1a75db1137789a1fa4b91c4a0368eaef.form = update1a75db1137789a1fa4b91c4a0368eaefForm

export const update = {
    '/shop/attendance/{attendance}': update1fa2a162924161645e27c9409c12cbbc,
    '/staff/attendance/{attendance}': update1a75db1137789a1fa4b91c4a0368eaef,
}

const AttendanceController = { index, store, update }

export default AttendanceController