import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\ShopDataController::getShop
 * @see app/Http/Controllers/Shop/ShopDataController.php:10
 * @route '/shop/data'
 */
export const getShop = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getShop.url(options),
    method: 'get',
})

getShop.definition = {
    methods: ["get","head"],
    url: '/shop/data',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ShopDataController::getShop
 * @see app/Http/Controllers/Shop/ShopDataController.php:10
 * @route '/shop/data'
 */
getShop.url = (options?: RouteQueryOptions) => {
    return getShop.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopDataController::getShop
 * @see app/Http/Controllers/Shop/ShopDataController.php:10
 * @route '/shop/data'
 */
getShop.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getShop.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ShopDataController::getShop
 * @see app/Http/Controllers/Shop/ShopDataController.php:10
 * @route '/shop/data'
 */
getShop.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getShop.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ShopDataController::getShop
 * @see app/Http/Controllers/Shop/ShopDataController.php:10
 * @route '/shop/data'
 */
    const getShopForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: getShop.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopDataController::getShop
 * @see app/Http/Controllers/Shop/ShopDataController.php:10
 * @route '/shop/data'
 */
        getShopForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getShop.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ShopDataController::getShop
 * @see app/Http/Controllers/Shop/ShopDataController.php:10
 * @route '/shop/data'
 */
        getShopForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: getShop.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    getShop.form = getShopForm
const ShopDataController = { getShop }

export default ShopDataController