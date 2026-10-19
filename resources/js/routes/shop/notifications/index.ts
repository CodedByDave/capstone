import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
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
* @see \App\Http\Controllers\Shop\ShopNotificationController::readAll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:28
 * @route '/shop/notifications/read-all'
 */
export const readAll = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: readAll.url(options),
    method: 'post',
})

readAll.definition = {
    methods: ["post"],
    url: '/shop/notifications/read-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::readAll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:28
 * @route '/shop/notifications/read-all'
 */
readAll.url = (options?: RouteQueryOptions) => {
    return readAll.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::readAll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:28
 * @route '/shop/notifications/read-all'
 */
readAll.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: readAll.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::readAll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:28
 * @route '/shop/notifications/read-all'
 */
    const readAllForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: readAll.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::readAll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:28
 * @route '/shop/notifications/read-all'
 */
        readAllForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: readAll.url(options),
            method: 'post',
        })
    
    readAll.form = readAllForm
const notifications = {
    index: Object.assign(index, index),
readAll: Object.assign(readAll, readAll),
}

export default notifications