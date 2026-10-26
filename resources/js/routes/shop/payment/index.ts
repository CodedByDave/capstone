import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\CheckoutController::pay
 * @see app/Http/Controllers/Shop/CheckoutController.php:145
 * @route '/shop/payment/pay'
 */
export const pay = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: pay.url(options),
    method: 'post',
})

pay.definition = {
    methods: ["post"],
    url: '/shop/payment/pay',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\CheckoutController::pay
 * @see app/Http/Controllers/Shop/CheckoutController.php:145
 * @route '/shop/payment/pay'
 */
pay.url = (options?: RouteQueryOptions) => {
    return pay.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::pay
 * @see app/Http/Controllers/Shop/CheckoutController.php:145
 * @route '/shop/payment/pay'
 */
pay.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: pay.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\CheckoutController::pay
 * @see app/Http/Controllers/Shop/CheckoutController.php:145
 * @route '/shop/payment/pay'
 */
    const payForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: pay.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\CheckoutController::pay
 * @see app/Http/Controllers/Shop/CheckoutController.php:145
 * @route '/shop/payment/pay'
 */
        payForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: pay.url(options),
            method: 'post',
        })
    
    pay.form = payForm
const payment = {
    pay: Object.assign(pay, pay),
}

export default payment