import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::send
 * @see app/Http/Controllers/Auth/ForgotPasswordController.php:19
 * @route '/forgot-password'
 */
export const send = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: send.url(options),
    method: 'post',
})

send.definition = {
    methods: ["post"],
    url: '/forgot-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::send
 * @see app/Http/Controllers/Auth/ForgotPasswordController.php:19
 * @route '/forgot-password'
 */
send.url = (options?: RouteQueryOptions) => {
    return send.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::send
 * @see app/Http/Controllers/Auth/ForgotPasswordController.php:19
 * @route '/forgot-password'
 */
send.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: send.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::send
 * @see app/Http/Controllers/Auth/ForgotPasswordController.php:19
 * @route '/forgot-password'
 */
    const sendForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: send.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::send
 * @see app/Http/Controllers/Auth/ForgotPasswordController.php:19
 * @route '/forgot-password'
 */
        sendForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: send.url(options),
            method: 'post',
        })
    
    send.form = sendForm
/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::show
 * @see app/Http/Controllers/Auth/ForgotPasswordController.php:12
 * @route '/forgot-password'
 */
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/forgot-password',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::show
 * @see app/Http/Controllers/Auth/ForgotPasswordController.php:12
 * @route '/forgot-password'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::show
 * @see app/Http/Controllers/Auth/ForgotPasswordController.php:12
 * @route '/forgot-password'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::show
 * @see app/Http/Controllers/Auth/ForgotPasswordController.php:12
 * @route '/forgot-password'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::show
 * @see app/Http/Controllers/Auth/ForgotPasswordController.php:12
 * @route '/forgot-password'
 */
    const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::show
 * @see app/Http/Controllers/Auth/ForgotPasswordController.php:12
 * @route '/forgot-password'
 */
        showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\ForgotPasswordController::show
 * @see app/Http/Controllers/Auth/ForgotPasswordController.php:12
 * @route '/forgot-password'
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
const ForgotPasswordController = { send, show }

export default ForgotPasswordController