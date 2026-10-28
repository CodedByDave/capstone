import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import track5d0c32 from './track'
import deliveryPhotos from './delivery-photos'
import complaints from './complaints'
import rating from './rating'
import payment from './payment'
/**
* @see \App\Http\Controllers\User\UserOrderController::index
 * @see app/Http/Controllers/User/UserOrderController.php:47
 * @route '/user/orders'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/user/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserOrderController::index
 * @see app/Http/Controllers/User/UserOrderController.php:47
 * @route '/user/orders'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserOrderController::index
 * @see app/Http/Controllers/User/UserOrderController.php:47
 * @route '/user/orders'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserOrderController::index
 * @see app/Http/Controllers/User/UserOrderController.php:47
 * @route '/user/orders'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserOrderController::index
 * @see app/Http/Controllers/User/UserOrderController.php:47
 * @route '/user/orders'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserOrderController::index
 * @see app/Http/Controllers/User/UserOrderController.php:47
 * @route '/user/orders'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserOrderController::index
 * @see app/Http/Controllers/User/UserOrderController.php:47
 * @route '/user/orders'
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
* @see \App\Http\Controllers\User\UserOrderController::estimate
 * @see app/Http/Controllers/User/UserOrderController.php:136
 * @route '/user/orders/estimate'
 */
export const estimate = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: estimate.url(options),
    method: 'get',
})

estimate.definition = {
    methods: ["get","head"],
    url: '/user/orders/estimate',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserOrderController::estimate
 * @see app/Http/Controllers/User/UserOrderController.php:136
 * @route '/user/orders/estimate'
 */
estimate.url = (options?: RouteQueryOptions) => {
    return estimate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserOrderController::estimate
 * @see app/Http/Controllers/User/UserOrderController.php:136
 * @route '/user/orders/estimate'
 */
estimate.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: estimate.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserOrderController::estimate
 * @see app/Http/Controllers/User/UserOrderController.php:136
 * @route '/user/orders/estimate'
 */
estimate.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: estimate.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserOrderController::estimate
 * @see app/Http/Controllers/User/UserOrderController.php:136
 * @route '/user/orders/estimate'
 */
    const estimateForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: estimate.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserOrderController::estimate
 * @see app/Http/Controllers/User/UserOrderController.php:136
 * @route '/user/orders/estimate'
 */
        estimateForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: estimate.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserOrderController::estimate
 * @see app/Http/Controllers/User/UserOrderController.php:136
 * @route '/user/orders/estimate'
 */
        estimateForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: estimate.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    estimate.form = estimateForm
/**
* @see \App\Http\Controllers\User\UserOrderController::show
 * @see app/Http/Controllers/User/UserOrderController.php:77
 * @route '/user/orders/{order}'
 */
export const show = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/user/orders/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserOrderController::show
 * @see app/Http/Controllers/User/UserOrderController.php:77
 * @route '/user/orders/{order}'
 */
show.url = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return show.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserOrderController::show
 * @see app/Http/Controllers/User/UserOrderController.php:77
 * @route '/user/orders/{order}'
 */
show.get = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserOrderController::show
 * @see app/Http/Controllers/User/UserOrderController.php:77
 * @route '/user/orders/{order}'
 */
show.head = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserOrderController::show
 * @see app/Http/Controllers/User/UserOrderController.php:77
 * @route '/user/orders/{order}'
 */
    const showForm = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserOrderController::show
 * @see app/Http/Controllers/User/UserOrderController.php:77
 * @route '/user/orders/{order}'
 */
        showForm.get = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserOrderController::show
 * @see app/Http/Controllers/User/UserOrderController.php:77
 * @route '/user/orders/{order}'
 */
        showForm.head = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\User\UserOrderController::store
 * @see app/Http/Controllers/User/UserOrderController.php:150
 * @route '/user/orders'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/user/orders',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\User\UserOrderController::store
 * @see app/Http/Controllers/User/UserOrderController.php:150
 * @route '/user/orders'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserOrderController::store
 * @see app/Http/Controllers/User/UserOrderController.php:150
 * @route '/user/orders'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\User\UserOrderController::store
 * @see app/Http/Controllers/User/UserOrderController.php:150
 * @route '/user/orders'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\User\UserOrderController::store
 * @see app/Http/Controllers/User/UserOrderController.php:150
 * @route '/user/orders'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\User\UserOrderController::requestDelivery
 * @see app/Http/Controllers/User/UserOrderController.php:178
 * @route '/user/orders/{order}/request-delivery'
 */
export const requestDelivery = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: requestDelivery.url(args, options),
    method: 'post',
})

requestDelivery.definition = {
    methods: ["post"],
    url: '/user/orders/{order}/request-delivery',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\User\UserOrderController::requestDelivery
 * @see app/Http/Controllers/User/UserOrderController.php:178
 * @route '/user/orders/{order}/request-delivery'
 */
requestDelivery.url = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return requestDelivery.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserOrderController::requestDelivery
 * @see app/Http/Controllers/User/UserOrderController.php:178
 * @route '/user/orders/{order}/request-delivery'
 */
requestDelivery.post = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: requestDelivery.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\User\UserOrderController::requestDelivery
 * @see app/Http/Controllers/User/UserOrderController.php:178
 * @route '/user/orders/{order}/request-delivery'
 */
    const requestDeliveryForm = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: requestDelivery.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\User\UserOrderController::requestDelivery
 * @see app/Http/Controllers/User/UserOrderController.php:178
 * @route '/user/orders/{order}/request-delivery'
 */
        requestDeliveryForm.post = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: requestDelivery.url(args, options),
            method: 'post',
        })
    
    requestDelivery.form = requestDeliveryForm
/**
* @see \App\Http\Controllers\User\UserOrderController::deliveryQuote
 * @see app/Http/Controllers/User/UserOrderController.php:192
 * @route '/user/orders/{order}/delivery-quote'
 */
export const deliveryQuote = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deliveryQuote.url(args, options),
    method: 'post',
})

deliveryQuote.definition = {
    methods: ["post"],
    url: '/user/orders/{order}/delivery-quote',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\User\UserOrderController::deliveryQuote
 * @see app/Http/Controllers/User/UserOrderController.php:192
 * @route '/user/orders/{order}/delivery-quote'
 */
deliveryQuote.url = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return deliveryQuote.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserOrderController::deliveryQuote
 * @see app/Http/Controllers/User/UserOrderController.php:192
 * @route '/user/orders/{order}/delivery-quote'
 */
deliveryQuote.post = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: deliveryQuote.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\User\UserOrderController::deliveryQuote
 * @see app/Http/Controllers/User/UserOrderController.php:192
 * @route '/user/orders/{order}/delivery-quote'
 */
    const deliveryQuoteForm = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: deliveryQuote.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\User\UserOrderController::deliveryQuote
 * @see app/Http/Controllers/User/UserOrderController.php:192
 * @route '/user/orders/{order}/delivery-quote'
 */
        deliveryQuoteForm.post = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: deliveryQuote.url(args, options),
            method: 'post',
        })
    
    deliveryQuote.form = deliveryQuoteForm
/**
* @see \App\Http\Controllers\User\UserOrderController::tracking
 * @see app/Http/Controllers/User/UserOrderController.php:204
 * @route '/user/orders/{paymentPublicId}/tracking'
 */
export const tracking = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tracking.url(args, options),
    method: 'get',
})

tracking.definition = {
    methods: ["get","head"],
    url: '/user/orders/{paymentPublicId}/tracking',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserOrderController::tracking
 * @see app/Http/Controllers/User/UserOrderController.php:204
 * @route '/user/orders/{paymentPublicId}/tracking'
 */
tracking.url = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { paymentPublicId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    paymentPublicId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        paymentPublicId: args.paymentPublicId,
                }

    return tracking.definition.url
            .replace('{paymentPublicId}', parsedArgs.paymentPublicId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserOrderController::tracking
 * @see app/Http/Controllers/User/UserOrderController.php:204
 * @route '/user/orders/{paymentPublicId}/tracking'
 */
tracking.get = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tracking.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserOrderController::tracking
 * @see app/Http/Controllers/User/UserOrderController.php:204
 * @route '/user/orders/{paymentPublicId}/tracking'
 */
tracking.head = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: tracking.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserOrderController::tracking
 * @see app/Http/Controllers/User/UserOrderController.php:204
 * @route '/user/orders/{paymentPublicId}/tracking'
 */
    const trackingForm = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: tracking.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserOrderController::tracking
 * @see app/Http/Controllers/User/UserOrderController.php:204
 * @route '/user/orders/{paymentPublicId}/tracking'
 */
        trackingForm.get = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: tracking.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserOrderController::tracking
 * @see app/Http/Controllers/User/UserOrderController.php:204
 * @route '/user/orders/{paymentPublicId}/tracking'
 */
        trackingForm.head = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: tracking.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    tracking.form = trackingForm
/**
* @see \App\Http\Controllers\User\OrderTrackingController::track
 * @see app/Http/Controllers/User/OrderTrackingController.php:17
 * @route '/user/orders/{paymentPublicId}/track'
 */
export const track = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: track.url(args, options),
    method: 'get',
})

track.definition = {
    methods: ["get","head"],
    url: '/user/orders/{paymentPublicId}/track',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\OrderTrackingController::track
 * @see app/Http/Controllers/User/OrderTrackingController.php:17
 * @route '/user/orders/{paymentPublicId}/track'
 */
track.url = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { paymentPublicId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    paymentPublicId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        paymentPublicId: args.paymentPublicId,
                }

    return track.definition.url
            .replace('{paymentPublicId}', parsedArgs.paymentPublicId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\OrderTrackingController::track
 * @see app/Http/Controllers/User/OrderTrackingController.php:17
 * @route '/user/orders/{paymentPublicId}/track'
 */
track.get = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: track.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\OrderTrackingController::track
 * @see app/Http/Controllers/User/OrderTrackingController.php:17
 * @route '/user/orders/{paymentPublicId}/track'
 */
track.head = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: track.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\OrderTrackingController::track
 * @see app/Http/Controllers/User/OrderTrackingController.php:17
 * @route '/user/orders/{paymentPublicId}/track'
 */
    const trackForm = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: track.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\OrderTrackingController::track
 * @see app/Http/Controllers/User/OrderTrackingController.php:17
 * @route '/user/orders/{paymentPublicId}/track'
 */
        trackForm.get = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: track.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\OrderTrackingController::track
 * @see app/Http/Controllers/User/OrderTrackingController.php:17
 * @route '/user/orders/{paymentPublicId}/track'
 */
        trackForm.head = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: track.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    track.form = trackForm
/**
* @see \App\Http\Controllers\User\UserOrderController::cancel
 * @see app/Http/Controllers/User/UserOrderController.php:211
 * @route '/user/orders/{order}/cancel'
 */
export const cancel = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(args, options),
    method: 'post',
})

cancel.definition = {
    methods: ["post"],
    url: '/user/orders/{order}/cancel',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\User\UserOrderController::cancel
 * @see app/Http/Controllers/User/UserOrderController.php:211
 * @route '/user/orders/{order}/cancel'
 */
cancel.url = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return cancel.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserOrderController::cancel
 * @see app/Http/Controllers/User/UserOrderController.php:211
 * @route '/user/orders/{order}/cancel'
 */
cancel.post = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\User\UserOrderController::cancel
 * @see app/Http/Controllers/User/UserOrderController.php:211
 * @route '/user/orders/{order}/cancel'
 */
    const cancelForm = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: cancel.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\User\UserOrderController::cancel
 * @see app/Http/Controllers/User/UserOrderController.php:211
 * @route '/user/orders/{order}/cancel'
 */
        cancelForm.post = (args: { order: string | number | { payment_public_id: string | number } } | [order: string | number | { payment_public_id: string | number } ] | string | number | { payment_public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: cancel.url(args, options),
            method: 'post',
        })
    
    cancel.form = cancelForm
/**
* @see \App\Http\Controllers\User\UserPaymentController::payOnline
 * @see app/Http/Controllers/User/UserPaymentController.php:24
 * @route '/user/orders/{paymentPublicId}/pay-online'
 */
export const payOnline = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: payOnline.url(args, options),
    method: 'post',
})

payOnline.definition = {
    methods: ["post"],
    url: '/user/orders/{paymentPublicId}/pay-online',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\User\UserPaymentController::payOnline
 * @see app/Http/Controllers/User/UserPaymentController.php:24
 * @route '/user/orders/{paymentPublicId}/pay-online'
 */
payOnline.url = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { paymentPublicId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    paymentPublicId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        paymentPublicId: args.paymentPublicId,
                }

    return payOnline.definition.url
            .replace('{paymentPublicId}', parsedArgs.paymentPublicId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserPaymentController::payOnline
 * @see app/Http/Controllers/User/UserPaymentController.php:24
 * @route '/user/orders/{paymentPublicId}/pay-online'
 */
payOnline.post = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: payOnline.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\User\UserPaymentController::payOnline
 * @see app/Http/Controllers/User/UserPaymentController.php:24
 * @route '/user/orders/{paymentPublicId}/pay-online'
 */
    const payOnlineForm = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: payOnline.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\User\UserPaymentController::payOnline
 * @see app/Http/Controllers/User/UserPaymentController.php:24
 * @route '/user/orders/{paymentPublicId}/pay-online'
 */
        payOnlineForm.post = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: payOnline.url(args, options),
            method: 'post',
        })
    
    payOnline.form = payOnlineForm
const orders = {
    index: Object.assign(index, index),
estimate: Object.assign(estimate, estimate),
show: Object.assign(show, show),
store: Object.assign(store, store),
requestDelivery: Object.assign(requestDelivery, requestDelivery),
deliveryQuote: Object.assign(deliveryQuote, deliveryQuote),
tracking: Object.assign(tracking, tracking),
track: Object.assign(track, track5d0c32),
deliveryPhotos: Object.assign(deliveryPhotos, deliveryPhotos),
complaints: Object.assign(complaints, complaints),
rating: Object.assign(rating, rating),
cancel: Object.assign(cancel, cancel),
payOnline: Object.assign(payOnline, payOnline),
payment: Object.assign(payment, payment),
}

export default orders