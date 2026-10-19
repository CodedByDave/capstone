import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\User\UserDashboardController::show
 * @see app/Http/Controllers/User/UserDashboardController.php:137
 * @route '/user/shops/{shop}'
 */
export const show = (args: { shop: number | { id: number } } | [shop: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/user/shops/{shop}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserDashboardController::show
 * @see app/Http/Controllers/User/UserDashboardController.php:137
 * @route '/user/shops/{shop}'
 */
show.url = (args: { shop: number | { id: number } } | [shop: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{shop}', parsedArgs.shop.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserDashboardController::show
 * @see app/Http/Controllers/User/UserDashboardController.php:137
 * @route '/user/shops/{shop}'
 */
show.get = (args: { shop: number | { id: number } } | [shop: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserDashboardController::show
 * @see app/Http/Controllers/User/UserDashboardController.php:137
 * @route '/user/shops/{shop}'
 */
show.head = (args: { shop: number | { id: number } } | [shop: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserDashboardController::show
 * @see app/Http/Controllers/User/UserDashboardController.php:137
 * @route '/user/shops/{shop}'
 */
    const showForm = (args: { shop: number | { id: number } } | [shop: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserDashboardController::show
 * @see app/Http/Controllers/User/UserDashboardController.php:137
 * @route '/user/shops/{shop}'
 */
        showForm.get = (args: { shop: number | { id: number } } | [shop: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserDashboardController::show
 * @see app/Http/Controllers/User/UserDashboardController.php:137
 * @route '/user/shops/{shop}'
 */
        showForm.head = (args: { shop: number | { id: number } } | [shop: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
const shops = {
    show: Object.assign(show, show),
}

export default shops