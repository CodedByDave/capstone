import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::signature
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:15
 * @route '/business-agreement-acceptances/{acceptancePublicId}/signature'
 */
export const signature = (args: { acceptancePublicId: string | number } | [acceptancePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: signature.url(args, options),
    method: 'get',
})

signature.definition = {
    methods: ["get","head"],
    url: '/business-agreement-acceptances/{acceptancePublicId}/signature',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::signature
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:15
 * @route '/business-agreement-acceptances/{acceptancePublicId}/signature'
 */
signature.url = (args: { acceptancePublicId: string | number } | [acceptancePublicId: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return signature.definition.url
            .replace('{acceptancePublicId}', parsedArgs.acceptancePublicId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::signature
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:15
 * @route '/business-agreement-acceptances/{acceptancePublicId}/signature'
 */
signature.get = (args: { acceptancePublicId: string | number } | [acceptancePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: signature.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::signature
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:15
 * @route '/business-agreement-acceptances/{acceptancePublicId}/signature'
 */
signature.head = (args: { acceptancePublicId: string | number } | [acceptancePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: signature.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::signature
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:15
 * @route '/business-agreement-acceptances/{acceptancePublicId}/signature'
 */
    const signatureForm = (args: { acceptancePublicId: string | number } | [acceptancePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: signature.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::signature
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:15
 * @route '/business-agreement-acceptances/{acceptancePublicId}/signature'
 */
        signatureForm.get = (args: { acceptancePublicId: string | number } | [acceptancePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: signature.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::signature
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:15
 * @route '/business-agreement-acceptances/{acceptancePublicId}/signature'
 */
        signatureForm.head = (args: { acceptancePublicId: string | number } | [acceptancePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: signature.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    signature.form = signatureForm
/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::platformSignature
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:29
 * @route '/business-agreement-platform-signatures/{signaturePublicId}'
 */
export const platformSignature = (args: { signaturePublicId: string | number } | [signaturePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: platformSignature.url(args, options),
    method: 'get',
})

platformSignature.definition = {
    methods: ["get","head"],
    url: '/business-agreement-platform-signatures/{signaturePublicId}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::platformSignature
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:29
 * @route '/business-agreement-platform-signatures/{signaturePublicId}'
 */
platformSignature.url = (args: { signaturePublicId: string | number } | [signaturePublicId: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return platformSignature.definition.url
            .replace('{signaturePublicId}', parsedArgs.signaturePublicId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::platformSignature
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:29
 * @route '/business-agreement-platform-signatures/{signaturePublicId}'
 */
platformSignature.get = (args: { signaturePublicId: string | number } | [signaturePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: platformSignature.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::platformSignature
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:29
 * @route '/business-agreement-platform-signatures/{signaturePublicId}'
 */
platformSignature.head = (args: { signaturePublicId: string | number } | [signaturePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: platformSignature.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::platformSignature
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:29
 * @route '/business-agreement-platform-signatures/{signaturePublicId}'
 */
    const platformSignatureForm = (args: { signaturePublicId: string | number } | [signaturePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: platformSignature.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::platformSignature
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:29
 * @route '/business-agreement-platform-signatures/{signaturePublicId}'
 */
        platformSignatureForm.get = (args: { signaturePublicId: string | number } | [signaturePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: platformSignature.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\BusinessAgreementSignatureController::platformSignature
 * @see app/Http/Controllers/BusinessAgreementSignatureController.php:29
 * @route '/business-agreement-platform-signatures/{signaturePublicId}'
 */
        platformSignatureForm.head = (args: { signaturePublicId: string | number } | [signaturePublicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: platformSignature.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    platformSignature.form = platformSignatureForm
const businessAgreement = {
    signature: Object.assign(signature, signature),
platformSignature: Object.assign(platformSignature, platformSignature),
}

export default businessAgreement