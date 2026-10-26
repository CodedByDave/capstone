import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clear
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:108
 * @route '/shop/settings/cover-photo/clear'
 */
export const clear = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clear.url(options),
    method: 'post',
})

clear.definition = {
    methods: ["post"],
    url: '/shop/settings/cover-photo/clear',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clear
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:108
 * @route '/shop/settings/cover-photo/clear'
 */
clear.url = (options?: RouteQueryOptions) => {
    return clear.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clear
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:108
 * @route '/shop/settings/cover-photo/clear'
 */
clear.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clear.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clear
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:108
 * @route '/shop/settings/cover-photo/clear'
 */
    const clearForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: clear.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clear
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:108
 * @route '/shop/settings/cover-photo/clear'
 */
        clearForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: clear.url(options),
            method: 'post',
        })
    
    clear.form = clearForm
const coverPhoto = {
    clear: Object.assign(clear, clear),
}

export default coverPhoto