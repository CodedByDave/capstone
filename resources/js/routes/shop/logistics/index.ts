import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import photos from './photos'
/**
* @see \App\Http\Controllers\Shop\LogisticsController::status
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/shop/logistics/{delivery}/status'
 */
export const status = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: status.url(args, options),
    method: 'patch',
})

status.definition = {
    methods: ["patch"],
    url: '/shop/logistics/{delivery}/status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::status
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/shop/logistics/{delivery}/status'
 */
status.url = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { delivery: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { delivery: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    delivery: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        delivery: typeof args.delivery === 'object'
                ? args.delivery.public_id
                : args.delivery,
                }

    return status.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::status
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/shop/logistics/{delivery}/status'
 */
status.patch = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: status.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::status
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/shop/logistics/{delivery}/status'
 */
    const statusForm = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: status.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::status
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/shop/logistics/{delivery}/status'
 */
        statusForm.patch = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: status.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    status.form = statusForm
/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/shop/logistics/{delivery}'
 */
export const destroy = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/shop/logistics/{delivery}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/shop/logistics/{delivery}'
 */
destroy.url = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { delivery: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { delivery: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    delivery: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        delivery: typeof args.delivery === 'object'
                ? args.delivery.public_id
                : args.delivery,
                }

    return destroy.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/shop/logistics/{delivery}'
 */
destroy.delete = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/shop/logistics/{delivery}'
 */
    const destroyForm = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/shop/logistics/{delivery}'
 */
        destroyForm.delete = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const logistics = {
    photos: Object.assign(photos, photos),
status: Object.assign(status, status),
destroy: Object.assign(destroy, destroy),
}

export default logistics