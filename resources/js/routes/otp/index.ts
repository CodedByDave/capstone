import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
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
const otp = {
    verify: Object.assign(verify, verify),
resend: Object.assign(resend, resend),
}

export default otp