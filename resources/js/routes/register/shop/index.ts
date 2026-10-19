import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
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
const shop = {
    store: Object.assign(store, store),
}

export default shop