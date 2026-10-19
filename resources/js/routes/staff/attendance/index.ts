import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockIn
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:17
 * @route '/staff/attendance/clock-in'
 */
export const clockIn = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clockIn.url(options),
    method: 'post',
})

clockIn.definition = {
    methods: ["post"],
    url: '/staff/attendance/clock-in',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockIn
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:17
 * @route '/staff/attendance/clock-in'
 */
clockIn.url = (options?: RouteQueryOptions) => {
    return clockIn.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockIn
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:17
 * @route '/staff/attendance/clock-in'
 */
clockIn.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clockIn.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockIn
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:17
 * @route '/staff/attendance/clock-in'
 */
    const clockInForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: clockIn.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockIn
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:17
 * @route '/staff/attendance/clock-in'
 */
        clockInForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: clockIn.url(options),
            method: 'post',
        })
    
    clockIn.form = clockInForm
/**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockOut
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:53
 * @route '/staff/attendance/clock-out'
 */
export const clockOut = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clockOut.url(options),
    method: 'post',
})

clockOut.definition = {
    methods: ["post"],
    url: '/staff/attendance/clock-out',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockOut
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:53
 * @route '/staff/attendance/clock-out'
 */
clockOut.url = (options?: RouteQueryOptions) => {
    return clockOut.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockOut
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:53
 * @route '/staff/attendance/clock-out'
 */
clockOut.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clockOut.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockOut
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:53
 * @route '/staff/attendance/clock-out'
 */
    const clockOutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: clockOut.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockOut
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:53
 * @route '/staff/attendance/clock-out'
 */
        clockOutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: clockOut.url(options),
            method: 'post',
        })
    
    clockOut.form = clockOutForm
/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:38
 * @route '/staff/attendance'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/staff/attendance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:38
 * @route '/staff/attendance'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:38
 * @route '/staff/attendance'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:38
 * @route '/staff/attendance'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:38
 * @route '/staff/attendance'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:38
 * @route '/staff/attendance'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:38
 * @route '/staff/attendance'
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
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:97
 * @route '/staff/attendance'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/staff/attendance',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:97
 * @route '/staff/attendance'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:97
 * @route '/staff/attendance'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:97
 * @route '/staff/attendance'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:97
 * @route '/staff/attendance'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:110
 * @route '/staff/attendance/{attendance}'
 */
export const update = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/staff/attendance/{attendance}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:110
 * @route '/staff/attendance/{attendance}'
 */
update.url = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{attendance}', parsedArgs.attendance.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:110
 * @route '/staff/attendance/{attendance}'
 */
update.put = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:110
 * @route '/staff/attendance/{attendance}'
 */
    const updateForm = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:110
 * @route '/staff/attendance/{attendance}'
 */
        updateForm.put = (args: { attendance: number | { id: number } } | [attendance: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
const attendance = {
    clockIn: Object.assign(clockIn, clockIn),
clockOut: Object.assign(clockOut, clockOut),
index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
}

export default attendance