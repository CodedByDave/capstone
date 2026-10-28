import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\CheckoutController::success
 * @see app/Http/Controllers/Shop/CheckoutController.php:169
 * @route '/shop/payment/success/{order}'
 */
export const success = (args: { order: string | number | { public_id: string | number } } | [order: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: success.url(args, options),
    method: 'get',
})

success.definition = {
    methods: ["get","head"],
    url: '/shop/payment/success/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\CheckoutController::success
 * @see app/Http/Controllers/Shop/CheckoutController.php:169
 * @route '/shop/payment/success/{order}'
 */
success.url = (args: { order: string | number | { public_id: string | number } } | [order: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { order: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.public_id
                : args.order,
                }

    return success.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::success
 * @see app/Http/Controllers/Shop/CheckoutController.php:169
 * @route '/shop/payment/success/{order}'
 */
success.get = (args: { order: string | number | { public_id: string | number } } | [order: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: success.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\CheckoutController::success
 * @see app/Http/Controllers/Shop/CheckoutController.php:169
 * @route '/shop/payment/success/{order}'
 */
success.head = (args: { order: string | number | { public_id: string | number } } | [order: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: success.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\CheckoutController::success
 * @see app/Http/Controllers/Shop/CheckoutController.php:169
 * @route '/shop/payment/success/{order}'
 */
    const successForm = (args: { order: string | number | { public_id: string | number } } | [order: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: success.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\CheckoutController::success
 * @see app/Http/Controllers/Shop/CheckoutController.php:169
 * @route '/shop/payment/success/{order}'
 */
        successForm.get = (args: { order: string | number | { public_id: string | number } } | [order: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: success.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\CheckoutController::success
 * @see app/Http/Controllers/Shop/CheckoutController.php:169
 * @route '/shop/payment/success/{order}'
 */
        successForm.head = (args: { order: string | number | { public_id: string | number } } | [order: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Shop\CheckoutController::cancel
 * @see app/Http/Controllers/Shop/CheckoutController.php:180
 * @route '/shop/payment/cancel'
 */
export const cancel = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cancel.url(options),
    method: 'get',
})

cancel.definition = {
    methods: ["get","head"],
    url: '/shop/payment/cancel',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\CheckoutController::cancel
 * @see app/Http/Controllers/Shop/CheckoutController.php:180
 * @route '/shop/payment/cancel'
 */
cancel.url = (options?: RouteQueryOptions) => {
    return cancel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::cancel
 * @see app/Http/Controllers/Shop/CheckoutController.php:180
 * @route '/shop/payment/cancel'
 */
cancel.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cancel.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\CheckoutController::cancel
 * @see app/Http/Controllers/Shop/CheckoutController.php:180
 * @route '/shop/payment/cancel'
 */
cancel.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: cancel.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\CheckoutController::cancel
 * @see app/Http/Controllers/Shop/CheckoutController.php:180
 * @route '/shop/payment/cancel'
 */
    const cancelForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: cancel.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\CheckoutController::cancel
 * @see app/Http/Controllers/Shop/CheckoutController.php:180
 * @route '/shop/payment/cancel'
 */
        cancelForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: cancel.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\CheckoutController::cancel
 * @see app/Http/Controllers/Shop/CheckoutController.php:180
 * @route '/shop/payment/cancel'
 */
        cancelForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: cancel.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    cancel.form = cancelForm
const payment = {
    success: Object.assign(success, success),
cancel: Object.assign(cancel, cancel),
}

export default payment