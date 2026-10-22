import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\User\UserPaymentController::complete
 * @see app/Http/Controllers/User/UserPaymentController.php:69
 * @route '/user/orders/{paymentPublicId}/payment/demo/complete'
 */
export const complete = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: complete.url(args, options),
    method: 'post',
})

complete.definition = {
    methods: ["post"],
    url: '/user/orders/{paymentPublicId}/payment/demo/complete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\User\UserPaymentController::complete
 * @see app/Http/Controllers/User/UserPaymentController.php:69
 * @route '/user/orders/{paymentPublicId}/payment/demo/complete'
 */
complete.url = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return complete.definition.url
            .replace('{paymentPublicId}', parsedArgs.paymentPublicId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserPaymentController::complete
 * @see app/Http/Controllers/User/UserPaymentController.php:69
 * @route '/user/orders/{paymentPublicId}/payment/demo/complete'
 */
complete.post = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: complete.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\User\UserPaymentController::complete
 * @see app/Http/Controllers/User/UserPaymentController.php:69
 * @route '/user/orders/{paymentPublicId}/payment/demo/complete'
 */
    const completeForm = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: complete.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\User\UserPaymentController::complete
 * @see app/Http/Controllers/User/UserPaymentController.php:69
 * @route '/user/orders/{paymentPublicId}/payment/demo/complete'
 */
        completeForm.post = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: complete.url(args, options),
            method: 'post',
        })
    
    complete.form = completeForm
const demo = {
    complete: Object.assign(complete, complete),
}

export default demo