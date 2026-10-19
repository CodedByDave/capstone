import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\PaymentHistoryController::index
 * @see app/Http/Controllers/Shop/PaymentHistoryController.php:19
 * @route '/shop/payment-history'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/payment-history',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\PaymentHistoryController::index
 * @see app/Http/Controllers/Shop/PaymentHistoryController.php:19
 * @route '/shop/payment-history'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\PaymentHistoryController::index
 * @see app/Http/Controllers/Shop/PaymentHistoryController.php:19
 * @route '/shop/payment-history'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\PaymentHistoryController::index
 * @see app/Http/Controllers/Shop/PaymentHistoryController.php:19
 * @route '/shop/payment-history'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\PaymentHistoryController::index
 * @see app/Http/Controllers/Shop/PaymentHistoryController.php:19
 * @route '/shop/payment-history'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\PaymentHistoryController::index
 * @see app/Http/Controllers/Shop/PaymentHistoryController.php:19
 * @route '/shop/payment-history'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\PaymentHistoryController::index
 * @see app/Http/Controllers/Shop/PaymentHistoryController.php:19
 * @route '/shop/payment-history'
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
* @see \App\Http\Controllers\Shop\PaymentHistoryController::show
 * @see app/Http/Controllers/Shop/PaymentHistoryController.php:27
 * @route '/shop/payment-history/{order}'
 */
export const show = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/shop/payment-history/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\PaymentHistoryController::show
 * @see app/Http/Controllers/Shop/PaymentHistoryController.php:27
 * @route '/shop/payment-history/{order}'
 */
show.url = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\PaymentHistoryController::show
 * @see app/Http/Controllers/Shop/PaymentHistoryController.php:27
 * @route '/shop/payment-history/{order}'
 */
show.get = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\PaymentHistoryController::show
 * @see app/Http/Controllers/Shop/PaymentHistoryController.php:27
 * @route '/shop/payment-history/{order}'
 */
show.head = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\PaymentHistoryController::show
 * @see app/Http/Controllers/Shop/PaymentHistoryController.php:27
 * @route '/shop/payment-history/{order}'
 */
    const showForm = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\PaymentHistoryController::show
 * @see app/Http/Controllers/Shop/PaymentHistoryController.php:27
 * @route '/shop/payment-history/{order}'
 */
        showForm.get = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\PaymentHistoryController::show
 * @see app/Http/Controllers/Shop/PaymentHistoryController.php:27
 * @route '/shop/payment-history/{order}'
 */
        showForm.head = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
const paymentHistory = {
    index: Object.assign(index, index),
show: Object.assign(show, show),
}

export default paymentHistory