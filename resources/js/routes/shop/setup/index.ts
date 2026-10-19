import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\ShopSetupController::show
 * @see app/Http/Controllers/Shop/ShopSetupController.php:18
 * @route '/shop/setup'
 */
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/shop/setup',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ShopSetupController::show
 * @see app/Http/Controllers/Shop/ShopSetupController.php:18
 * @route '/shop/setup'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSetupController::show
 * @see app/Http/Controllers/Shop/ShopSetupController.php:18
 * @route '/shop/setup'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ShopSetupController::show
 * @see app/Http/Controllers/Shop/ShopSetupController.php:18
 * @route '/shop/setup'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSetupController::show
 * @see app/Http/Controllers/Shop/ShopSetupController.php:18
 * @route '/shop/setup'
 */
    const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSetupController::show
 * @see app/Http/Controllers/Shop/ShopSetupController.php:18
 * @route '/shop/setup'
 */
        showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ShopSetupController::show
 * @see app/Http/Controllers/Shop/ShopSetupController.php:18
 * @route '/shop/setup'
 */
        showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Shop\ShopSetupController::store
 * @see app/Http/Controllers/Shop/ShopSetupController.php:30
 * @route '/shop/setup'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: store.url(options),
    method: 'put',
})

store.definition = {
    methods: ["put"],
    url: '/shop/setup',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\ShopSetupController::store
 * @see app/Http/Controllers/Shop/ShopSetupController.php:30
 * @route '/shop/setup'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSetupController::store
 * @see app/Http/Controllers/Shop/ShopSetupController.php:30
 * @route '/shop/setup'
 */
store.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: store.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSetupController::store
 * @see app/Http/Controllers/Shop/ShopSetupController.php:30
 * @route '/shop/setup'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSetupController::store
 * @see app/Http/Controllers/Shop/ShopSetupController.php:30
 * @route '/shop/setup'
 */
        storeForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    store.form = storeForm
const setup = {
    show: Object.assign(show, show),
store: Object.assign(store, store),
}

export default setup