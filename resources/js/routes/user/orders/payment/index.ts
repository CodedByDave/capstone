import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
import demoBe5688 from './demo'
/**
* @see \App\Http\Controllers\User\UserPaymentController::demo
 * @see app/Http/Controllers/User/UserPaymentController.php:56
 * @route '/user/orders/{paymentPublicId}/payment/demo'
 */
export const demo = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: demo.url(args, options),
    method: 'get',
})

demo.definition = {
    methods: ["get","head"],
    url: '/user/orders/{paymentPublicId}/payment/demo',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserPaymentController::demo
 * @see app/Http/Controllers/User/UserPaymentController.php:56
 * @route '/user/orders/{paymentPublicId}/payment/demo'
 */
demo.url = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return demo.definition.url
            .replace('{paymentPublicId}', parsedArgs.paymentPublicId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserPaymentController::demo
 * @see app/Http/Controllers/User/UserPaymentController.php:56
 * @route '/user/orders/{paymentPublicId}/payment/demo'
 */
demo.get = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: demo.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserPaymentController::demo
 * @see app/Http/Controllers/User/UserPaymentController.php:56
 * @route '/user/orders/{paymentPublicId}/payment/demo'
 */
demo.head = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: demo.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserPaymentController::demo
 * @see app/Http/Controllers/User/UserPaymentController.php:56
 * @route '/user/orders/{paymentPublicId}/payment/demo'
 */
    const demoForm = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: demo.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserPaymentController::demo
 * @see app/Http/Controllers/User/UserPaymentController.php:56
 * @route '/user/orders/{paymentPublicId}/payment/demo'
 */
        demoForm.get = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: demo.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserPaymentController::demo
 * @see app/Http/Controllers/User/UserPaymentController.php:56
 * @route '/user/orders/{paymentPublicId}/payment/demo'
 */
        demoForm.head = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: demo.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    demo.form = demoForm
/**
* @see \App\Http\Controllers\User\UserPaymentController::receipt
 * @see app/Http/Controllers/User/UserPaymentController.php:126
 * @route '/user/orders/{paymentPublicId}/payment/receipt'
 */
export const receipt = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: receipt.url(args, options),
    method: 'get',
})

receipt.definition = {
    methods: ["get","head"],
    url: '/user/orders/{paymentPublicId}/payment/receipt',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserPaymentController::receipt
 * @see app/Http/Controllers/User/UserPaymentController.php:126
 * @route '/user/orders/{paymentPublicId}/payment/receipt'
 */
receipt.url = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return receipt.definition.url
            .replace('{paymentPublicId}', parsedArgs.paymentPublicId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserPaymentController::receipt
 * @see app/Http/Controllers/User/UserPaymentController.php:126
 * @route '/user/orders/{paymentPublicId}/payment/receipt'
 */
receipt.get = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: receipt.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserPaymentController::receipt
 * @see app/Http/Controllers/User/UserPaymentController.php:126
 * @route '/user/orders/{paymentPublicId}/payment/receipt'
 */
receipt.head = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: receipt.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserPaymentController::receipt
 * @see app/Http/Controllers/User/UserPaymentController.php:126
 * @route '/user/orders/{paymentPublicId}/payment/receipt'
 */
    const receiptForm = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: receipt.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserPaymentController::receipt
 * @see app/Http/Controllers/User/UserPaymentController.php:126
 * @route '/user/orders/{paymentPublicId}/payment/receipt'
 */
        receiptForm.get = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: receipt.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserPaymentController::receipt
 * @see app/Http/Controllers/User/UserPaymentController.php:126
 * @route '/user/orders/{paymentPublicId}/payment/receipt'
 */
        receiptForm.head = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: receipt.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    receipt.form = receiptForm
/**
* @see \App\Http\Controllers\User\UserPaymentController::success
 * @see app/Http/Controllers/User/UserPaymentController.php:82
 * @route '/user/orders/{paymentPublicId}/payment/success'
 */
export const success = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: success.url(args, options),
    method: 'get',
})

success.definition = {
    methods: ["get","head"],
    url: '/user/orders/{paymentPublicId}/payment/success',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserPaymentController::success
 * @see app/Http/Controllers/User/UserPaymentController.php:82
 * @route '/user/orders/{paymentPublicId}/payment/success'
 */
success.url = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return success.definition.url
            .replace('{paymentPublicId}', parsedArgs.paymentPublicId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserPaymentController::success
 * @see app/Http/Controllers/User/UserPaymentController.php:82
 * @route '/user/orders/{paymentPublicId}/payment/success'
 */
success.get = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: success.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserPaymentController::success
 * @see app/Http/Controllers/User/UserPaymentController.php:82
 * @route '/user/orders/{paymentPublicId}/payment/success'
 */
success.head = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: success.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserPaymentController::success
 * @see app/Http/Controllers/User/UserPaymentController.php:82
 * @route '/user/orders/{paymentPublicId}/payment/success'
 */
    const successForm = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: success.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserPaymentController::success
 * @see app/Http/Controllers/User/UserPaymentController.php:82
 * @route '/user/orders/{paymentPublicId}/payment/success'
 */
        successForm.get = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: success.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserPaymentController::success
 * @see app/Http/Controllers/User/UserPaymentController.php:82
 * @route '/user/orders/{paymentPublicId}/payment/success'
 */
        successForm.head = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: success.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    success.form = successForm
/**
* @see \App\Http\Controllers\User\UserPaymentController::cancel
 * @see app/Http/Controllers/User/UserPaymentController.php:116
 * @route '/user/orders/{paymentPublicId}/payment/cancel'
 */
export const cancel = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cancel.url(args, options),
    method: 'get',
})

cancel.definition = {
    methods: ["get","head"],
    url: '/user/orders/{paymentPublicId}/payment/cancel',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserPaymentController::cancel
 * @see app/Http/Controllers/User/UserPaymentController.php:116
 * @route '/user/orders/{paymentPublicId}/payment/cancel'
 */
cancel.url = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return cancel.definition.url
            .replace('{paymentPublicId}', parsedArgs.paymentPublicId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserPaymentController::cancel
 * @see app/Http/Controllers/User/UserPaymentController.php:116
 * @route '/user/orders/{paymentPublicId}/payment/cancel'
 */
cancel.get = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cancel.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserPaymentController::cancel
 * @see app/Http/Controllers/User/UserPaymentController.php:116
 * @route '/user/orders/{paymentPublicId}/payment/cancel'
 */
cancel.head = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: cancel.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserPaymentController::cancel
 * @see app/Http/Controllers/User/UserPaymentController.php:116
 * @route '/user/orders/{paymentPublicId}/payment/cancel'
 */
    const cancelForm = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: cancel.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserPaymentController::cancel
 * @see app/Http/Controllers/User/UserPaymentController.php:116
 * @route '/user/orders/{paymentPublicId}/payment/cancel'
 */
        cancelForm.get = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: cancel.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserPaymentController::cancel
 * @see app/Http/Controllers/User/UserPaymentController.php:116
 * @route '/user/orders/{paymentPublicId}/payment/cancel'
 */
        cancelForm.head = (args: { paymentPublicId: string | number } | [paymentPublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: cancel.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    cancel.form = cancelForm
const payment = {
    demo: Object.assign(demo, demoBe5688),
receipt: Object.assign(receipt, receipt),
success: Object.assign(success, success),
cancel: Object.assign(cancel, cancel),
}

export default payment