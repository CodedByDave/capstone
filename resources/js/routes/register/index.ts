import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import shop2013be from './shop'
/**
* @see \App\Http\Controllers\Auth\ShopRegisterController::shop
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:14
 * @route '/register/shop'
 */
export const shop = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: shop.url(options),
    method: 'get',
})

shop.definition = {
    methods: ["get","head"],
    url: '/register/shop',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\ShopRegisterController::shop
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:14
 * @route '/register/shop'
 */
shop.url = (options?: RouteQueryOptions) => {
    return shop.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ShopRegisterController::shop
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:14
 * @route '/register/shop'
 */
shop.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: shop.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\ShopRegisterController::shop
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:14
 * @route '/register/shop'
 */
shop.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: shop.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\ShopRegisterController::shop
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:14
 * @route '/register/shop'
 */
    const shopForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: shop.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\ShopRegisterController::shop
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:14
 * @route '/register/shop'
 */
        shopForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: shop.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\ShopRegisterController::shop
 * @see app/Http/Controllers/Auth/ShopRegisterController.php:14
 * @route '/register/shop'
 */
        shopForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: shop.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    shop.form = shopForm
/**
* @see \App\Http\Controllers\Auth\UserRegisterController::user
 * @see app/Http/Controllers/Auth/UserRegisterController.php:17
 * @route '/register'
 */
export const user = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: user.url(options),
    method: 'get',
})

user.definition = {
    methods: ["get","head"],
    url: '/register',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\UserRegisterController::user
 * @see app/Http/Controllers/Auth/UserRegisterController.php:17
 * @route '/register'
 */
user.url = (options?: RouteQueryOptions) => {
    return user.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\UserRegisterController::user
 * @see app/Http/Controllers/Auth/UserRegisterController.php:17
 * @route '/register'
 */
user.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: user.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\UserRegisterController::user
 * @see app/Http/Controllers/Auth/UserRegisterController.php:17
 * @route '/register'
 */
user.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: user.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\UserRegisterController::user
 * @see app/Http/Controllers/Auth/UserRegisterController.php:17
 * @route '/register'
 */
    const userForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: user.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\UserRegisterController::user
 * @see app/Http/Controllers/Auth/UserRegisterController.php:17
 * @route '/register'
 */
        userForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: user.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\UserRegisterController::user
 * @see app/Http/Controllers/Auth/UserRegisterController.php:17
 * @route '/register'
 */
        userForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: user.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    user.form = userForm
const register = {
    user: Object.assign(user, user),
shop: Object.assign(shop, shop2013be),
}

export default register