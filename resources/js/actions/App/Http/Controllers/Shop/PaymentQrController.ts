import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\PaymentQrController::index
 * @see app/Http/Controllers/Shop/PaymentQrController.php:39
 * @route '/shop/payment-qr'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/payment-qr',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\PaymentQrController::index
 * @see app/Http/Controllers/Shop/PaymentQrController.php:39
 * @route '/shop/payment-qr'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\PaymentQrController::index
 * @see app/Http/Controllers/Shop/PaymentQrController.php:39
 * @route '/shop/payment-qr'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\PaymentQrController::index
 * @see app/Http/Controllers/Shop/PaymentQrController.php:39
 * @route '/shop/payment-qr'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\PaymentQrController::index
 * @see app/Http/Controllers/Shop/PaymentQrController.php:39
 * @route '/shop/payment-qr'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\PaymentQrController::index
 * @see app/Http/Controllers/Shop/PaymentQrController.php:39
 * @route '/shop/payment-qr'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\PaymentQrController::index
 * @see app/Http/Controllers/Shop/PaymentQrController.php:39
 * @route '/shop/payment-qr'
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
* @see \App\Http\Controllers\Shop\PaymentQrController::update
 * @see app/Http/Controllers/Shop/PaymentQrController.php:44
 * @route '/shop/payment-qr'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

update.definition = {
    methods: ["post"],
    url: '/shop/payment-qr',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\PaymentQrController::update
 * @see app/Http/Controllers/Shop/PaymentQrController.php:44
 * @route '/shop/payment-qr'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\PaymentQrController::update
 * @see app/Http/Controllers/Shop/PaymentQrController.php:44
 * @route '/shop/payment-qr'
 */
update.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: update.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\PaymentQrController::update
 * @see app/Http/Controllers/Shop/PaymentQrController.php:44
 * @route '/shop/payment-qr'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\PaymentQrController::update
 * @see app/Http/Controllers/Shop/PaymentQrController.php:44
 * @route '/shop/payment-qr'
 */
        updateForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(options),
            method: 'post',
        })
    
    update.form = updateForm
const PaymentQrController = { index, update }

export default PaymentQrController