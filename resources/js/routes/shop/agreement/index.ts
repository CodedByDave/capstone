import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\BusinessAgreementController::show
 * @see app/Http/Controllers/Shop/BusinessAgreementController.php:19
 * @route '/shop/agreement'
 */
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/shop/agreement',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\BusinessAgreementController::show
 * @see app/Http/Controllers/Shop/BusinessAgreementController.php:19
 * @route '/shop/agreement'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\BusinessAgreementController::show
 * @see app/Http/Controllers/Shop/BusinessAgreementController.php:19
 * @route '/shop/agreement'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\BusinessAgreementController::show
 * @see app/Http/Controllers/Shop/BusinessAgreementController.php:19
 * @route '/shop/agreement'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\BusinessAgreementController::show
 * @see app/Http/Controllers/Shop/BusinessAgreementController.php:19
 * @route '/shop/agreement'
 */
    const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\BusinessAgreementController::show
 * @see app/Http/Controllers/Shop/BusinessAgreementController.php:19
 * @route '/shop/agreement'
 */
        showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\BusinessAgreementController::show
 * @see app/Http/Controllers/Shop/BusinessAgreementController.php:19
 * @route '/shop/agreement'
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
* @see \App\Http\Controllers\Shop\BusinessAgreementController::accept
 * @see app/Http/Controllers/Shop/BusinessAgreementController.php:27
 * @route '/shop/agreement'
 */
export const accept = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: accept.url(options),
    method: 'post',
})

accept.definition = {
    methods: ["post"],
    url: '/shop/agreement',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\BusinessAgreementController::accept
 * @see app/Http/Controllers/Shop/BusinessAgreementController.php:27
 * @route '/shop/agreement'
 */
accept.url = (options?: RouteQueryOptions) => {
    return accept.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\BusinessAgreementController::accept
 * @see app/Http/Controllers/Shop/BusinessAgreementController.php:27
 * @route '/shop/agreement'
 */
accept.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: accept.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\BusinessAgreementController::accept
 * @see app/Http/Controllers/Shop/BusinessAgreementController.php:27
 * @route '/shop/agreement'
 */
    const acceptForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: accept.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\BusinessAgreementController::accept
 * @see app/Http/Controllers/Shop/BusinessAgreementController.php:27
 * @route '/shop/agreement'
 */
        acceptForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: accept.url(options),
            method: 'post',
        })
    
    accept.form = acceptForm
const agreement = {
    show: Object.assign(show, show),
accept: Object.assign(accept, accept),
}

export default agreement