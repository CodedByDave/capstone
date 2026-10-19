import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\User\UserDashboardController::index
 * @see app/Http/Controllers/User/UserDashboardController.php:19
 * @route '/user/dashboard'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/user/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserDashboardController::index
 * @see app/Http/Controllers/User/UserDashboardController.php:19
 * @route '/user/dashboard'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserDashboardController::index
 * @see app/Http/Controllers/User/UserDashboardController.php:19
 * @route '/user/dashboard'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserDashboardController::index
 * @see app/Http/Controllers/User/UserDashboardController.php:19
 * @route '/user/dashboard'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserDashboardController::index
 * @see app/Http/Controllers/User/UserDashboardController.php:19
 * @route '/user/dashboard'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserDashboardController::index
 * @see app/Http/Controllers/User/UserDashboardController.php:19
 * @route '/user/dashboard'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserDashboardController::index
 * @see app/Http/Controllers/User/UserDashboardController.php:19
 * @route '/user/dashboard'
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
* @see \App\Http\Controllers\User\UserDashboardController::shops
 * @see app/Http/Controllers/User/UserDashboardController.php:82
 * @route '/user/shops'
 */
export const shops = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: shops.url(options),
    method: 'get',
})

shops.definition = {
    methods: ["get","head"],
    url: '/user/shops',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserDashboardController::shops
 * @see app/Http/Controllers/User/UserDashboardController.php:82
 * @route '/user/shops'
 */
shops.url = (options?: RouteQueryOptions) => {
    return shops.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserDashboardController::shops
 * @see app/Http/Controllers/User/UserDashboardController.php:82
 * @route '/user/shops'
 */
shops.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: shops.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserDashboardController::shops
 * @see app/Http/Controllers/User/UserDashboardController.php:82
 * @route '/user/shops'
 */
shops.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: shops.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserDashboardController::shops
 * @see app/Http/Controllers/User/UserDashboardController.php:82
 * @route '/user/shops'
 */
    const shopsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: shops.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserDashboardController::shops
 * @see app/Http/Controllers/User/UserDashboardController.php:82
 * @route '/user/shops'
 */
        shopsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: shops.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserDashboardController::shops
 * @see app/Http/Controllers/User/UserDashboardController.php:82
 * @route '/user/shops'
 */
        shopsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: shops.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    shops.form = shopsForm
/**
* @see \App\Http\Controllers\User\UserDashboardController::showShop
 * @see app/Http/Controllers/User/UserDashboardController.php:137
 * @route '/user/shops/{shop}'
 */
export const showShop = (args: { shop: number | { id: number } } | [shop: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showShop.url(args, options),
    method: 'get',
})

showShop.definition = {
    methods: ["get","head"],
    url: '/user/shops/{shop}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserDashboardController::showShop
 * @see app/Http/Controllers/User/UserDashboardController.php:137
 * @route '/user/shops/{shop}'
 */
showShop.url = (args: { shop: number | { id: number } } | [shop: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shop: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { shop: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    shop: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        shop: typeof args.shop === 'object'
                ? args.shop.id
                : args.shop,
                }

    return showShop.definition.url
            .replace('{shop}', parsedArgs.shop.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserDashboardController::showShop
 * @see app/Http/Controllers/User/UserDashboardController.php:137
 * @route '/user/shops/{shop}'
 */
showShop.get = (args: { shop: number | { id: number } } | [shop: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showShop.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserDashboardController::showShop
 * @see app/Http/Controllers/User/UserDashboardController.php:137
 * @route '/user/shops/{shop}'
 */
showShop.head = (args: { shop: number | { id: number } } | [shop: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showShop.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserDashboardController::showShop
 * @see app/Http/Controllers/User/UserDashboardController.php:137
 * @route '/user/shops/{shop}'
 */
    const showShopForm = (args: { shop: number | { id: number } } | [shop: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showShop.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserDashboardController::showShop
 * @see app/Http/Controllers/User/UserDashboardController.php:137
 * @route '/user/shops/{shop}'
 */
        showShopForm.get = (args: { shop: number | { id: number } } | [shop: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showShop.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserDashboardController::showShop
 * @see app/Http/Controllers/User/UserDashboardController.php:137
 * @route '/user/shops/{shop}'
 */
        showShopForm.head = (args: { shop: number | { id: number } } | [shop: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showShop.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showShop.form = showShopForm
const UserDashboardController = { index, shops, showShop }

export default UserDashboardController