import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:18
 * @route '/shop/notifications'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/notifications',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:18
 * @route '/shop/notifications'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:18
 * @route '/shop/notifications'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:18
 * @route '/shop/notifications'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:18
 * @route '/shop/notifications'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:18
 * @route '/shop/notifications'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:18
 * @route '/shop/notifications'
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
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:28
 * @route '/shop/notifications/read-all'
 */
export const markAllRead = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllRead.url(options),
    method: 'post',
})

markAllRead.definition = {
    methods: ["post"],
    url: '/shop/notifications/read-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:28
 * @route '/shop/notifications/read-all'
 */
markAllRead.url = (options?: RouteQueryOptions) => {
    return markAllRead.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:28
 * @route '/shop/notifications/read-all'
 */
markAllRead.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllRead.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:28
 * @route '/shop/notifications/read-all'
 */
    const markAllReadForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markAllRead.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:28
 * @route '/shop/notifications/read-all'
 */
        markAllReadForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markAllRead.url(options),
            method: 'post',
        })
    
    markAllRead.form = markAllReadForm
const ShopNotificationController = { index, markAllRead }

export default ShopNotificationController