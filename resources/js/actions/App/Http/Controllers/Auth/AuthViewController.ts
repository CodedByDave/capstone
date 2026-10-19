import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\AuthViewController::verificationNotice
 * @see app/Http/Controllers/Auth/AuthViewController.php:16
 * @route '/email/verify'
 */
export const verificationNotice = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: verificationNotice.url(options),
    method: 'get',
})

verificationNotice.definition = {
    methods: ["get","head"],
    url: '/email/verify',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\AuthViewController::verificationNotice
 * @see app/Http/Controllers/Auth/AuthViewController.php:16
 * @route '/email/verify'
 */
verificationNotice.url = (options?: RouteQueryOptions) => {
    return verificationNotice.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\AuthViewController::verificationNotice
 * @see app/Http/Controllers/Auth/AuthViewController.php:16
 * @route '/email/verify'
 */
verificationNotice.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: verificationNotice.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\AuthViewController::verificationNotice
 * @see app/Http/Controllers/Auth/AuthViewController.php:16
 * @route '/email/verify'
 */
verificationNotice.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: verificationNotice.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\AuthViewController::verificationNotice
 * @see app/Http/Controllers/Auth/AuthViewController.php:16
 * @route '/email/verify'
 */
    const verificationNoticeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: verificationNotice.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\AuthViewController::verificationNotice
 * @see app/Http/Controllers/Auth/AuthViewController.php:16
 * @route '/email/verify'
 */
        verificationNoticeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: verificationNotice.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\AuthViewController::verificationNotice
 * @see app/Http/Controllers/Auth/AuthViewController.php:16
 * @route '/email/verify'
 */
        verificationNoticeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: verificationNotice.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    verificationNotice.form = verificationNoticeForm
/**
* @see \App\Http\Controllers\Auth\AuthViewController::confirmPassword
 * @see app/Http/Controllers/Auth/AuthViewController.php:27
 * @route '/confirm-password'
 */
export const confirmPassword = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirmPassword.url(options),
    method: 'get',
})

confirmPassword.definition = {
    methods: ["get","head"],
    url: '/confirm-password',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\AuthViewController::confirmPassword
 * @see app/Http/Controllers/Auth/AuthViewController.php:27
 * @route '/confirm-password'
 */
confirmPassword.url = (options?: RouteQueryOptions) => {
    return confirmPassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\AuthViewController::confirmPassword
 * @see app/Http/Controllers/Auth/AuthViewController.php:27
 * @route '/confirm-password'
 */
confirmPassword.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirmPassword.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\AuthViewController::confirmPassword
 * @see app/Http/Controllers/Auth/AuthViewController.php:27
 * @route '/confirm-password'
 */
confirmPassword.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: confirmPassword.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\AuthViewController::confirmPassword
 * @see app/Http/Controllers/Auth/AuthViewController.php:27
 * @route '/confirm-password'
 */
    const confirmPasswordForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: confirmPassword.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\AuthViewController::confirmPassword
 * @see app/Http/Controllers/Auth/AuthViewController.php:27
 * @route '/confirm-password'
 */
        confirmPasswordForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: confirmPassword.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\AuthViewController::confirmPassword
 * @see app/Http/Controllers/Auth/AuthViewController.php:27
 * @route '/confirm-password'
 */
        confirmPasswordForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: confirmPassword.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    confirmPassword.form = confirmPasswordForm
const AuthViewController = { verificationNotice, confirmPassword }

export default AuthViewController