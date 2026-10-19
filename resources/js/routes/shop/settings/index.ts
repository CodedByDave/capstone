import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
import geo1a384f from './geo'
import coverPhoto500c46 from './cover-photo'
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::geo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:95
 * @route '/shop/settings/geo'
 */
export const geo = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: geo.url(options),
    method: 'post',
})

geo.definition = {
    methods: ["post"],
    url: '/shop/settings/geo',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::geo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:95
 * @route '/shop/settings/geo'
 */
geo.url = (options?: RouteQueryOptions) => {
    return geo.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::geo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:95
 * @route '/shop/settings/geo'
 */
geo.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: geo.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::geo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:95
 * @route '/shop/settings/geo'
 */
    const geoForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: geo.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::geo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:95
 * @route '/shop/settings/geo'
 */
        geoForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: geo.url(options),
            method: 'post',
        })
    
    geo.form = geoForm
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::coverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:56
 * @route '/shop/settings/cover-photo'
 */
export const coverPhoto = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: coverPhoto.url(options),
    method: 'post',
})

coverPhoto.definition = {
    methods: ["post"],
    url: '/shop/settings/cover-photo',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::coverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:56
 * @route '/shop/settings/cover-photo'
 */
coverPhoto.url = (options?: RouteQueryOptions) => {
    return coverPhoto.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::coverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:56
 * @route '/shop/settings/cover-photo'
 */
coverPhoto.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: coverPhoto.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::coverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:56
 * @route '/shop/settings/cover-photo'
 */
    const coverPhotoForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: coverPhoto.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::coverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:56
 * @route '/shop/settings/cover-photo'
 */
        coverPhotoForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: coverPhoto.url(options),
            method: 'post',
        })
    
    coverPhoto.form = coverPhotoForm
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::paymongo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:49
 * @route '/shop/settings/paymongo'
 */
export const paymongo = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: paymongo.url(options),
    method: 'post',
})

paymongo.definition = {
    methods: ["post"],
    url: '/shop/settings/paymongo',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::paymongo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:49
 * @route '/shop/settings/paymongo'
 */
paymongo.url = (options?: RouteQueryOptions) => {
    return paymongo.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::paymongo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:49
 * @route '/shop/settings/paymongo'
 */
paymongo.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: paymongo.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::paymongo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:49
 * @route '/shop/settings/paymongo'
 */
    const paymongoForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: paymongo.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::paymongo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:49
 * @route '/shop/settings/paymongo'
 */
        paymongoForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: paymongo.url(options),
            method: 'post',
        })
    
    paymongo.form = paymongoForm
const settings = {
    geo: Object.assign(geo, geo1a384f),
coverPhoto: Object.assign(coverPhoto, coverPhoto500c46),
paymongo: Object.assign(paymongo, paymongo),
}

export default settings