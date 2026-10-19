import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clear
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:114
 * @route '/shop/settings/geo/clear'
 */
export const clear = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clear.url(options),
    method: 'post',
})

clear.definition = {
    methods: ["post"],
    url: '/shop/settings/geo/clear',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clear
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:114
 * @route '/shop/settings/geo/clear'
 */
clear.url = (options?: RouteQueryOptions) => {
    return clear.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clear
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:114
 * @route '/shop/settings/geo/clear'
 */
clear.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clear.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clear
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:114
 * @route '/shop/settings/geo/clear'
 */
    const clearForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: clear.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clear
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:114
 * @route '/shop/settings/geo/clear'
 */
        clearForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: clear.url(options),
            method: 'post',
        })
    
    clear.form = clearForm
const geo = {
    clear: Object.assign(clear, clear),
}

export default geo