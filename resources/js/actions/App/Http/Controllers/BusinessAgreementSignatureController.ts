import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::show
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:15
 * @route '/business-agreement-acceptances/{acceptancePublicId}/signature'
 */
export const show = (args: { acceptancePublicId: string | number } | [acceptancePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/business-agreement-acceptances/{acceptancePublicId}/signature',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::show
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:15
 * @route '/business-agreement-acceptances/{acceptancePublicId}/signature'
 */
show.url = (args: { acceptancePublicId: string | number } | [acceptancePublicId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { acceptancePublicId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    acceptancePublicId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        acceptancePublicId: args.acceptancePublicId,
                }

    return show.definition.url
            .replace('{acceptancePublicId}', parsedArgs.acceptancePublicId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::show
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:15
 * @route '/business-agreement-acceptances/{acceptancePublicId}/signature'
 */
show.get = (args: { acceptancePublicId: string | number } | [acceptancePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::show
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:15
 * @route '/business-agreement-acceptances/{acceptancePublicId}/signature'
 */
show.head = (args: { acceptancePublicId: string | number } | [acceptancePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::show
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:15
 * @route '/business-agreement-acceptances/{acceptancePublicId}/signature'
 */
    const showForm = (args: { acceptancePublicId: string | number } | [acceptancePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::show
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:15
 * @route '/business-agreement-acceptances/{acceptancePublicId}/signature'
 */
        showForm.get = (args: { acceptancePublicId: string | number } | [acceptancePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::show
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:15
 * @route '/business-agreement-acceptances/{acceptancePublicId}/signature'
 */
        showForm.head = (args: { acceptancePublicId: string | number } | [acceptancePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::platformShow
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:29
 * @route '/business-agreement-platform-signatures/{signaturePublicId}'
 */
export const platformShow = (args: { signaturePublicId: string | number } | [signaturePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: platformShow.url(args, options),
    method: 'get',
})

platformShow.definition = {
    methods: ["get","head"],
    url: '/business-agreement-platform-signatures/{signaturePublicId}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::platformShow
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:29
 * @route '/business-agreement-platform-signatures/{signaturePublicId}'
 */
platformShow.url = (args: { signaturePublicId: string | number } | [signaturePublicId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { signaturePublicId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    signaturePublicId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        signaturePublicId: args.signaturePublicId,
                }

    return platformShow.definition.url
            .replace('{signaturePublicId}', parsedArgs.signaturePublicId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::platformShow
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:29
 * @route '/business-agreement-platform-signatures/{signaturePublicId}'
 */
platformShow.get = (args: { signaturePublicId: string | number } | [signaturePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: platformShow.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::platformShow
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:29
 * @route '/business-agreement-platform-signatures/{signaturePublicId}'
 */
platformShow.head = (args: { signaturePublicId: string | number } | [signaturePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: platformShow.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::platformShow
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:29
 * @route '/business-agreement-platform-signatures/{signaturePublicId}'
 */
    const platformShowForm = (args: { signaturePublicId: string | number } | [signaturePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: platformShow.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::platformShow
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:29
 * @route '/business-agreement-platform-signatures/{signaturePublicId}'
 */
        platformShowForm.get = (args: { signaturePublicId: string | number } | [signaturePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: platformShow.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::platformShow
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:29
 * @route '/business-agreement-platform-signatures/{signaturePublicId}'
 */
        platformShowForm.head = (args: { signaturePublicId: string | number } | [signaturePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: platformShow.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    platformShow.form = platformShowForm
const BusinessAgreementSignatureController = { show, platformShow }

export default BusinessAgreementSignatureController