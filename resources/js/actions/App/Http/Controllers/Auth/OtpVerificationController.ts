import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\OtpVerificationController::show
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:26
 * @route '/verify-otp'
 */
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/verify-otp',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\OtpVerificationController::show
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:26
 * @route '/verify-otp'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\OtpVerificationController::show
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:26
 * @route '/verify-otp'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\OtpVerificationController::show
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:26
 * @route '/verify-otp'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\OtpVerificationController::show
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:26
 * @route '/verify-otp'
 */
    const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\OtpVerificationController::show
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:26
 * @route '/verify-otp'
 */
        showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\OtpVerificationController::show
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:26
 * @route '/verify-otp'
 */
        showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Auth\OtpVerificationController::verify
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:50
 * @route '/verify-otp'
 */
export const verify = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verify.url(options),
    method: 'post',
})

verify.definition = {
    methods: ["post"],
    url: '/verify-otp',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\OtpVerificationController::verify
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:50
 * @route '/verify-otp'
 */
verify.url = (options?: RouteQueryOptions) => {
    return verify.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\OtpVerificationController::verify
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:50
 * @route '/verify-otp'
 */
verify.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verify.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Auth\OtpVerificationController::verify
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:50
 * @route '/verify-otp'
 */
    const verifyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: verify.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Auth\OtpVerificationController::verify
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:50
 * @route '/verify-otp'
 */
        verifyForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: verify.url(options),
            method: 'post',
        })
    
    verify.form = verifyForm
/**
* @see \App\Http\Controllers\Auth\OtpVerificationController::resend
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:79
 * @route '/resend-otp'
 */
export const resend = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resend.url(options),
    method: 'post',
})

resend.definition = {
    methods: ["post"],
    url: '/resend-otp',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\OtpVerificationController::resend
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:79
 * @route '/resend-otp'
 */
resend.url = (options?: RouteQueryOptions) => {
    return resend.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\OtpVerificationController::resend
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:79
 * @route '/resend-otp'
 */
resend.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resend.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Auth\OtpVerificationController::resend
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:79
 * @route '/resend-otp'
 */
    const resendForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: resend.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Auth\OtpVerificationController::resend
 * @see app/Http/Controllers/Auth/OtpVerificationController.php:79
 * @route '/resend-otp'
 */
        resendForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: resend.url(options),
            method: 'post',
        })
    
    resend.form = resendForm
const OtpVerificationController = { show, verify, resend }

export default OtpVerificationController