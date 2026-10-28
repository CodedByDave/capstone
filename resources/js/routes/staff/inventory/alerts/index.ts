import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:32
 * @route '/staff/inventory/alerts'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/staff/inventory/alerts',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:32
 * @route '/staff/inventory/alerts'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:32
 * @route '/staff/inventory/alerts'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:32
 * @route '/staff/inventory/alerts'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:32
 * @route '/staff/inventory/alerts'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:32
 * @route '/staff/inventory/alerts'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::index
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:32
 * @route '/staff/inventory/alerts'
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
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:53
 * @route '/staff/inventory/alerts/mark-all-read'
 */
export const markAllRead = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllRead.url(options),
    method: 'post',
})

markAllRead.definition = {
    methods: ["post"],
    url: '/staff/inventory/alerts/mark-all-read',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:53
 * @route '/staff/inventory/alerts/mark-all-read'
 */
markAllRead.url = (options?: RouteQueryOptions) => {
    return markAllRead.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:53
 * @route '/staff/inventory/alerts/mark-all-read'
 */
markAllRead.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllRead.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:53
 * @route '/staff/inventory/alerts/mark-all-read'
 */
    const markAllReadForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markAllRead.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::markAllRead
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:53
 * @route '/staff/inventory/alerts/mark-all-read'
 */
        markAllReadForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markAllRead.url(options),
            method: 'post',
        })
    
    markAllRead.form = markAllReadForm
/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:42
 * @route '/staff/inventory/alerts/{lowStockAlert}'
 */
export const update = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/staff/inventory/alerts/{lowStockAlert}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:42
 * @route '/staff/inventory/alerts/{lowStockAlert}'
 */
update.url = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{lowStockAlert}', parsedArgs.lowStockAlert.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:42
 * @route '/staff/inventory/alerts/{lowStockAlert}'
 */
update.patch = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:42
 * @route '/staff/inventory/alerts/{lowStockAlert}'
 */
    const updateForm = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\LowStockAlertController::update
 * @see app/Http/Controllers/Shop/Inventory/LowStockAlertController.php:42
 * @route '/staff/inventory/alerts/{lowStockAlert}'
 */
        updateForm.patch = (args: { lowStockAlert: number | { id: number } } | [lowStockAlert: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
const alerts = {
    index: Object.assign(index, index),
markAllRead: Object.assign(markAllRead, markAllRead),
update: Object.assign(update, update),
}

export default alerts