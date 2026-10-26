import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::index
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:35
 * @route '/shop/settings'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::index
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:35
 * @route '/shop/settings'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::index
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:35
 * @route '/shop/settings'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::index
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:35
 * @route '/shop/settings'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::index
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:35
 * @route '/shop/settings'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::index
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:35
 * @route '/shop/settings'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::index
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:35
 * @route '/shop/settings'
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
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateGeo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:139
 * @route '/shop/settings/geo'
 */
export const updateGeo = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateGeo.url(options),
    method: 'post',
})

updateGeo.definition = {
    methods: ["post"],
    url: '/shop/settings/geo',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateGeo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:139
 * @route '/shop/settings/geo'
 */
updateGeo.url = (options?: RouteQueryOptions) => {
    return updateGeo.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateGeo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:139
 * @route '/shop/settings/geo'
 */
updateGeo.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateGeo.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateGeo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:139
 * @route '/shop/settings/geo'
 */
    const updateGeoForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateGeo.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateGeo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:139
 * @route '/shop/settings/geo'
 */
        updateGeoForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateGeo.url(options),
            method: 'post',
        })
    
    updateGeo.form = updateGeoForm
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clearGeo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:158
 * @route '/shop/settings/geo/clear'
 */
export const clearGeo = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clearGeo.url(options),
    method: 'post',
})

clearGeo.definition = {
    methods: ["post"],
    url: '/shop/settings/geo/clear',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clearGeo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:158
 * @route '/shop/settings/geo/clear'
 */
clearGeo.url = (options?: RouteQueryOptions) => {
    return clearGeo.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clearGeo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:158
 * @route '/shop/settings/geo/clear'
 */
clearGeo.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clearGeo.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clearGeo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:158
 * @route '/shop/settings/geo/clear'
 */
    const clearGeoForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: clearGeo.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::clearGeo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:158
 * @route '/shop/settings/geo/clear'
 */
        clearGeoForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: clearGeo.url(options),
            method: 'post',
        })
    
    clearGeo.form = clearGeoForm
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateCoverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:100
 * @route '/shop/settings/cover-photo'
 */
export const updateCoverPhoto = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateCoverPhoto.url(options),
    method: 'post',
})

updateCoverPhoto.definition = {
    methods: ["post"],
    url: '/shop/settings/cover-photo',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateCoverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:100
 * @route '/shop/settings/cover-photo'
 */
updateCoverPhoto.url = (options?: RouteQueryOptions) => {
    return updateCoverPhoto.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateCoverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:100
 * @route '/shop/settings/cover-photo'
 */
updateCoverPhoto.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateCoverPhoto.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateCoverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:100
 * @route '/shop/settings/cover-photo'
 */
    const updateCoverPhotoForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateCoverPhoto.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateCoverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:100
 * @route '/shop/settings/cover-photo'
 */
        updateCoverPhotoForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateCoverPhoto.url(options),
            method: 'post',
        })
    
    updateCoverPhoto.form = updateCoverPhotoForm
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::removeCoverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:123
 * @route '/shop/settings/cover-photo/clear'
 */
export const removeCoverPhoto = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: removeCoverPhoto.url(options),
    method: 'post',
})

removeCoverPhoto.definition = {
    methods: ["post"],
    url: '/shop/settings/cover-photo/clear',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::removeCoverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:123
 * @route '/shop/settings/cover-photo/clear'
 */
removeCoverPhoto.url = (options?: RouteQueryOptions) => {
    return removeCoverPhoto.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::removeCoverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:123
 * @route '/shop/settings/cover-photo/clear'
 */
removeCoverPhoto.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: removeCoverPhoto.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::removeCoverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:123
 * @route '/shop/settings/cover-photo/clear'
 */
    const removeCoverPhotoForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: removeCoverPhoto.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::removeCoverPhoto
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:123
 * @route '/shop/settings/cover-photo/clear'
 */
        removeCoverPhotoForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: removeCoverPhoto.url(options),
            method: 'post',
        })
    
    removeCoverPhoto.form = removeCoverPhotoForm
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updatePaymongo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:57
 * @route '/shop/settings/paymongo'
 */
export const updatePaymongo = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updatePaymongo.url(options),
    method: 'post',
})

updatePaymongo.definition = {
    methods: ["post"],
    url: '/shop/settings/paymongo',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updatePaymongo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:57
 * @route '/shop/settings/paymongo'
 */
updatePaymongo.url = (options?: RouteQueryOptions) => {
    return updatePaymongo.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updatePaymongo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:57
 * @route '/shop/settings/paymongo'
 */
updatePaymongo.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updatePaymongo.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updatePaymongo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:57
 * @route '/shop/settings/paymongo'
 */
    const updatePaymongoForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updatePaymongo.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updatePaymongo
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:57
 * @route '/shop/settings/paymongo'
 */
        updatePaymongoForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updatePaymongo.url(options),
            method: 'post',
        })
    
    updatePaymongo.form = updatePaymongoForm
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateOperatingHours
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:64
 * @route '/shop/settings/operating-hours'
 */
export const updateOperatingHours = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateOperatingHours.url(options),
    method: 'patch',
})

updateOperatingHours.definition = {
    methods: ["patch"],
    url: '/shop/settings/operating-hours',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateOperatingHours
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:64
 * @route '/shop/settings/operating-hours'
 */
updateOperatingHours.url = (options?: RouteQueryOptions) => {
    return updateOperatingHours.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateOperatingHours
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:64
 * @route '/shop/settings/operating-hours'
 */
updateOperatingHours.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateOperatingHours.url(options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateOperatingHours
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:64
 * @route '/shop/settings/operating-hours'
 */
    const updateOperatingHoursForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateOperatingHours.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateOperatingHours
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:64
 * @route '/shop/settings/operating-hours'
 */
        updateOperatingHoursForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateOperatingHours.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateOperatingHours.form = updateOperatingHoursForm
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateDriverPay
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:76
 * @route '/shop/settings/driver-pay'
 */
export const updateDriverPay = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateDriverPay.url(options),
    method: 'patch',
})

updateDriverPay.definition = {
    methods: ["patch"],
    url: '/shop/settings/driver-pay',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateDriverPay
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:76
 * @route '/shop/settings/driver-pay'
 */
updateDriverPay.url = (options?: RouteQueryOptions) => {
    return updateDriverPay.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateDriverPay
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:76
 * @route '/shop/settings/driver-pay'
 */
updateDriverPay.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateDriverPay.url(options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateDriverPay
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:76
 * @route '/shop/settings/driver-pay'
 */
    const updateDriverPayForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateDriverPay.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateDriverPay
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:76
 * @route '/shop/settings/driver-pay'
 */
        updateDriverPayForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateDriverPay.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateDriverPay.form = updateDriverPayForm
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateDeliveryPricing
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:88
 * @route '/shop/settings/delivery-pricing'
 */
export const updateDeliveryPricing = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateDeliveryPricing.url(options),
    method: 'patch',
})

updateDeliveryPricing.definition = {
    methods: ["patch"],
    url: '/shop/settings/delivery-pricing',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateDeliveryPricing
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:88
 * @route '/shop/settings/delivery-pricing'
 */
updateDeliveryPricing.url = (options?: RouteQueryOptions) => {
    return updateDeliveryPricing.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateDeliveryPricing
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:88
 * @route '/shop/settings/delivery-pricing'
 */
updateDeliveryPricing.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateDeliveryPricing.url(options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateDeliveryPricing
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:88
 * @route '/shop/settings/delivery-pricing'
 */
    const updateDeliveryPricingForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateDeliveryPricing.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::updateDeliveryPricing
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:88
 * @route '/shop/settings/delivery-pricing'
 */
        updateDeliveryPricingForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateDeliveryPricing.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateDeliveryPricing.form = updateDeliveryPricingForm
const ShopSettingsController = { index, updateGeo, clearGeo, updateCoverPhoto, removeCoverPhoto, updatePaymongo, updateOperatingHours, updateDriverPay, updateDeliveryPricing }

export default ShopSettingsController