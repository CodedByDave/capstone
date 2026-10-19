import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\OtpVerificationController::page
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:26
 * @route '/verify-otp'
 */
export const page = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: page.url(options),
    method: 'get',
})

page.definition = {
    methods: ["get","head"],
    url: '/verify-otp',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\OtpVerificationController::page
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:26
 * @route '/verify-otp'
 */
page.url = (options?: RouteQueryOptions) => {
    return page.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\OtpVerificationController::page
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:26
 * @route '/verify-otp'
 */
page.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: page.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\OtpVerificationController::page
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:26
 * @route '/verify-otp'
 */
page.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: page.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\OtpVerificationController::page
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:26
 * @route '/verify-otp'
 */
    const pageForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: page.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\OtpVerificationController::page
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:26
 * @route '/verify-otp'
 */
        pageForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: page.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\OtpVerificationController::page
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:26
 * @route '/verify-otp'
 */
        pageForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: page.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    page.form = pageForm