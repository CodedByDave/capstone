import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\CheckoutController::confirm
 * @see app/Http/Controllers/Shop/CheckoutController.php:80
 * @route '/checkout/confirm'
 */
export const confirm = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirm.url(options),
    method: 'get',
})

confirm.definition = {
    methods: ["get","head"],
    url: '/checkout/confirm',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\CheckoutController::confirm
 * @see app/Http/Controllers/Shop/CheckoutController.php:80
 * @route '/checkout/confirm'
 */
confirm.url = (options?: RouteQueryOptions) => {
    return confirm.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::confirm
 * @see app/Http/Controllers/Shop/CheckoutController.php:80
 * @route '/checkout/confirm'
 */
confirm.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirm.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\CheckoutController::confirm
 * @see app/Http/Controllers/Shop/CheckoutController.php:80
 * @route '/checkout/confirm'
 */
confirm.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: confirm.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\CheckoutController::confirm
 * @see app/Http/Controllers/Shop/CheckoutController.php:80
 * @route '/checkout/confirm'
 */
    const confirmForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: confirm.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\CheckoutController::confirm
 * @see app/Http/Controllers/Shop/CheckoutController.php:80
 * @route '/checkout/confirm'
 */
        confirmForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: confirm.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\CheckoutController::confirm
 * @see app/Http/Controllers/Shop/CheckoutController.php:80
 * @route '/checkout/confirm'
 */
        confirmForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: confirm.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    confirm.form = confirmForm
/**
* @see \App\Http\Controllers\Shop\CheckoutController::process
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/checkout/process'
 */
export const process = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: process.url(options),
    method: 'post',
})

process.definition = {
    methods: ["post"],
    url: '/checkout/process',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\CheckoutController::process
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/checkout/process'
 */
process.url = (options?: RouteQueryOptions) => {
    return process.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::process
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/checkout/process'
 */
process.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: process.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\CheckoutController::process
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/checkout/process'
 */
    const processForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: process.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\CheckoutController::process
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/checkout/process'
 */
        processForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: process.url(options),
            method: 'post',
        })
    
    process.form = processForm