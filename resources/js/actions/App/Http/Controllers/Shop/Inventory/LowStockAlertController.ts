import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:34
 * @route '/shop/inventory/alerts'
 */
const index76333298f13b08967afc16f3bc3aa757 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index76333298f13b08967afc16f3bc3aa757.url(options),
    method: 'get',
})

index76333298f13b08967afc16f3bc3aa757.definition = {
    methods: ["get","head"],
    url: '/shop/inventory/alerts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:34
 * @route '/shop/inventory/alerts'
 */
index76333298f13b08967afc16f3bc3aa757.url = (options?: RouteQueryOptions) => {
    return index76333298f13b08967afc16f3bc3aa757.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:34
 * @route '/shop/inventory/alerts'
 */
index76333298f13b08967afc16f3bc3aa757.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index76333298f13b08967afc16f3bc3aa757.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:34
 * @route '/shop/inventory/alerts'
 */
index76333298f13b08967afc16f3bc3aa757.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index76333298f13b08967afc16f3bc3aa757.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:34
 * @route '/shop/inventory/alerts'
 */
    const index76333298f13b08967afc16f3bc3aa757Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index76333298f13b08967afc16f3bc3aa757.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:34
 * @route '/shop/inventory/alerts'
 */
        index76333298f13b08967afc16f3bc3aa757Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index76333298f13b08967afc16f3bc3aa757.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:34
 * @route '/shop/inventory/alerts'
 */
        index76333298f13b08967afc16f3bc3aa757Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index76333298f13b08967afc16f3bc3aa757.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index76333298f13b08967afc16f3bc3aa757.form = index76333298f13b08967afc16f3bc3aa757Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:34
 * @route '/staff/inventory/alerts'
 */
const indexbd6058da6d8f925dcbe4f9d08cbf51d8 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexbd6058da6d8f925dcbe4f9d08cbf51d8.url(options),
    method: 'get',
})

indexbd6058da6d8f925dcbe4f9d08cbf51d8.definition = {
    methods: ["get","head"],
    url: '/staff/inventory/alerts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:34
 * @route '/staff/inventory/alerts'
 */
indexbd6058da6d8f925dcbe4f9d08cbf51d8.url = (options?: RouteQueryOptions) => {
    return indexbd6058da6d8f925dcbe4f9d08cbf51d8.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:34
 * @route '/staff/inventory/alerts'
 */
indexbd6058da6d8f925dcbe4f9d08cbf51d8.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexbd6058da6d8f925dcbe4f9d08cbf51d8.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:34
 * @route '/staff/inventory/alerts'
 */
indexbd6058da6d8f925dcbe4f9d08cbf51d8.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexbd6058da6d8f925dcbe4f9d08cbf51d8.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:34
 * @route '/staff/inventory/alerts'
 */
    const indexbd6058da6d8f925dcbe4f9d08cbf51d8Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexbd6058da6d8f925dcbe4f9d08cbf51d8.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:34
 * @route '/staff/inventory/alerts'
 */
        indexbd6058da6d8f925dcbe4f9d08cbf51d8Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexbd6058da6d8f925dcbe4f9d08cbf51d8.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:34
 * @route '/staff/inventory/alerts'
 */
        indexbd6058da6d8f925dcbe4f9d08cbf51d8Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexbd6058da6d8f925dcbe4f9d08cbf51d8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexbd6058da6d8f925dcbe4f9d08cbf51d8.form = indexbd6058da6d8f925dcbe4f9d08cbf51d8Form

export const index = {
    '/shop/inventory/alerts': index76333298f13b08967afc16f3bc3aa757,
    '/staff/inventory/alerts': indexbd6058da6d8f925dcbe4f9d08cbf51d8,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:56
 * @route '/shop/inventory/alerts/mark-all-read'
 */
const markAllReadeeb232393cabc73fdfd31d4b0f3abc51 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllReadeeb232393cabc73fdfd31d4b0f3abc51.url(options),
    method: 'post',
})

markAllReadeeb232393cabc73fdfd31d4b0f3abc51.definition = {
    methods: ["post"],
    url: '/shop/inventory/alerts/mark-all-read',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:56
 * @route '/shop/inventory/alerts/mark-all-read'
 */
markAllReadeeb232393cabc73fdfd31d4b0f3abc51.url = (options?: RouteQueryOptions) => {
    return markAllReadeeb232393cabc73fdfd31d4b0f3abc51.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:56
 * @route '/shop/inventory/alerts/mark-all-read'
 */
markAllReadeeb232393cabc73fdfd31d4b0f3abc51.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllReadeeb232393cabc73fdfd31d4b0f3abc51.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:56
 * @route '/shop/inventory/alerts/mark-all-read'
 */
    const markAllReadeeb232393cabc73fdfd31d4b0f3abc51Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markAllReadeeb232393cabc73fdfd31d4b0f3abc51.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:56
 * @route '/shop/inventory/alerts/mark-all-read'
 */
        markAllReadeeb232393cabc73fdfd31d4b0f3abc51Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markAllReadeeb232393cabc73fdfd31d4b0f3abc51.url(options),
            method: 'post',
        })
    
    markAllReadeeb232393cabc73fdfd31d4b0f3abc51.form = markAllReadeeb232393cabc73fdfd31d4b0f3abc51Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:56
 * @route '/staff/inventory/alerts/mark-all-read'
 */
const markAllRead03da4f140c1dc30040a4b3d68dab913c = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllRead03da4f140c1dc30040a4b3d68dab913c.url(options),
    method: 'post',
})

markAllRead03da4f140c1dc30040a4b3d68dab913c.definition = {
    methods: ["post"],
    url: '/staff/inventory/alerts/mark-all-read',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:56
 * @route '/staff/inventory/alerts/mark-all-read'
 */
markAllRead03da4f140c1dc30040a4b3d68dab913c.url = (options?: RouteQueryOptions) => {
    return markAllRead03da4f140c1dc30040a4b3d68dab913c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:56
 * @route '/staff/inventory/alerts/mark-all-read'
 */
markAllRead03da4f140c1dc30040a4b3d68dab913c.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllRead03da4f140c1dc30040a4b3d68dab913c.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:56
 * @route '/staff/inventory/alerts/mark-all-read'
 */
    const markAllRead03da4f140c1dc30040a4b3d68dab913cForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markAllRead03da4f140c1dc30040a4b3d68dab913c.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:56
 * @route '/staff/inventory/alerts/mark-all-read'
 */
        markAllRead03da4f140c1dc30040a4b3d68dab913cForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markAllRead03da4f140c1dc30040a4b3d68dab913c.url(options),
            method: 'post',
        })
    
    markAllRead03da4f140c1dc30040a4b3d68dab913c.form = markAllRead03da4f140c1dc30040a4b3d68dab913cForm

export const markAllRead = {
    '/shop/inventory/alerts/mark-all-read': markAllReadeeb232393cabc73fdfd31d4b0f3abc51,
    '/staff/inventory/alerts/mark-all-read': markAllRead03da4f140c1dc30040a4b3d68dab913c,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:45
 * @route '/shop/inventory/alerts/{lowStockAlert}'
 */
const update8383615adc5345cf4f02c818bc9424dd = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update8383615adc5345cf4f02c818bc9424dd.url(args, options),
    method: 'patch',
})

update8383615adc5345cf4f02c818bc9424dd.definition = {
    methods: ["patch"],
    url: '/shop/inventory/alerts/{lowStockAlert}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:45
 * @route '/shop/inventory/alerts/{lowStockAlert}'
 */
update8383615adc5345cf4f02c818bc9424dd.url = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { lowStockAlert: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { lowStockAlert: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    lowStockAlert: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        lowStockAlert: typeof args.lowStockAlert === 'object'
                ? args.lowStockAlert.id
                : args.lowStockAlert,
                }

    return update8383615adc5345cf4f02c818bc9424dd.definition.url
            .replace('{lowStockAlert}', parsedArgs.lowStockAlert.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:45
 * @route '/shop/inventory/alerts/{lowStockAlert}'
 */
update8383615adc5345cf4f02c818bc9424dd.patch = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update8383615adc5345cf4f02c818bc9424dd.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:45
 * @route '/shop/inventory/alerts/{lowStockAlert}'
 */
    const update8383615adc5345cf4f02c818bc9424ddForm = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update8383615adc5345cf4f02c818bc9424dd.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:45
 * @route '/shop/inventory/alerts/{lowStockAlert}'
 */
        update8383615adc5345cf4f02c818bc9424ddForm.patch = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update8383615adc5345cf4f02c818bc9424dd.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update8383615adc5345cf4f02c818bc9424dd.form = update8383615adc5345cf4f02c818bc9424ddForm
    /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:45
 * @route '/staff/inventory/alerts/{lowStockAlert}'
 */
const updateaf31f9169cfc9bffa67aadfcb21678d1 = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateaf31f9169cfc9bffa67aadfcb21678d1.url(args, options),
    method: 'patch',
})

updateaf31f9169cfc9bffa67aadfcb21678d1.definition = {
    methods: ["patch"],
    url: '/staff/inventory/alerts/{lowStockAlert}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:45
 * @route '/staff/inventory/alerts/{lowStockAlert}'
 */
updateaf31f9169cfc9bffa67aadfcb21678d1.url = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { lowStockAlert: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { lowStockAlert: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    lowStockAlert: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        lowStockAlert: typeof args.lowStockAlert === 'object'
                ? args.lowStockAlert.id
                : args.lowStockAlert,
                }

    return updateaf31f9169cfc9bffa67aadfcb21678d1.definition.url
            .replace('{lowStockAlert}', parsedArgs.lowStockAlert.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:45
 * @route '/staff/inventory/alerts/{lowStockAlert}'
 */
updateaf31f9169cfc9bffa67aadfcb21678d1.patch = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateaf31f9169cfc9bffa67aadfcb21678d1.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:45
 * @route '/staff/inventory/alerts/{lowStockAlert}'
 */
    const updateaf31f9169cfc9bffa67aadfcb21678d1Form = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateaf31f9169cfc9bffa67aadfcb21678d1.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:45
 * @route '/staff/inventory/alerts/{lowStockAlert}'
 */
        updateaf31f9169cfc9bffa67aadfcb21678d1Form.patch = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateaf31f9169cfc9bffa67aadfcb21678d1.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateaf31f9169cfc9bffa67aadfcb21678d1.form = updateaf31f9169cfc9bffa67aadfcb21678d1Form

export const update = {
    '/shop/inventory/alerts/{lowStockAlert}': update8383615adc5345cf4f02c818bc9424dd,
    '/staff/inventory/alerts/{lowStockAlert}': updateaf31f9169cfc9bffa67aadfcb21678d1,
}

const LowStockAlertController = { index, markAllRead, update }

export default LowStockAlertController