import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/shop/attendance'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/attendance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/shop/attendance'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/shop/attendance'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/shop/attendance'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/shop/attendance'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/shop/attendance'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::index
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:35
 * @route '/shop/attendance'
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
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/shop/attendance'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/shop/attendance',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/shop/attendance'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/shop/attendance'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/shop/attendance'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::store
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:62
 * @route '/shop/attendance'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/shop/attendance/{attendance}'
 */
export const update = (args: { attendance: string | number | { id: string | number } } | [attendance: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/shop/attendance/{attendance}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/shop/attendance/{attendance}'
 */
update.url = (args: { attendance: string | number | { id: string | number } } | [attendance: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/shop/attendance/{attendance}'
 */
update.put = (args: { attendance: string | number | { id: string | number } } | [attendance: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\AttendanceController::update
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/shop/attendance/{attendance}'
 */
    const updateForm = (args: { attendance: string | number | { id: string | number } } | [attendance: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/Employee/AttendanceController.php:75
 * @route '/shop/attendance/{attendance}'
 */
        updateForm.put = (args: { attendance: string | number | { id: string | number } } | [attendance: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
    index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
}

export default attendance