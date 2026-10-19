import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
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
const paymentQr = {
    update: Object.assign(update, update),
}

export default paymentQr