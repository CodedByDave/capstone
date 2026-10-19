import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\ShopRegisterController::show
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:14
 * @route '/register/shop'
 */
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/register/shop',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\ShopRegisterController::show
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:14
 * @route '/register/shop'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ShopRegisterController::show
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:14
 * @route '/register/shop'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\ShopRegisterController::show
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:14
 * @route '/register/shop'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\ShopRegisterController::show
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:14
 * @route '/register/shop'
 */
    const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\ShopRegisterController::show
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:14
 * @route '/register/shop'
 */
        showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\ShopRegisterController::show
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:14
 * @route '/register/shop'
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
* @see \App\Http\Controllers\Auth\ShopRegisterController::store
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:25
 * @route '/register/shop'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/register/shop',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\ShopRegisterController::store
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:25
 * @route '/register/shop'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ShopRegisterController::store
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:25
 * @route '/register/shop'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Auth\ShopRegisterController::store
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:25
 * @route '/register/shop'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Auth\ShopRegisterController::store
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:25
 * @route '/register/shop'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const ShopRegisterController = { show, store }

export default ShopRegisterController