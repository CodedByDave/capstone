import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import photos from './photos'
import riders13f500 from './riders'
/**
* @see \App\Http\Controllers\Shop\LogisticsController::status
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/shop/logistics/{delivery}/status'
 */
export const status = (args: { delivery: string | { public_id: string } } | [delivery: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: status.url(args, options),
    method: 'patch',
})

status.definition = {
    methods: ["patch"],
    url: '/shop/logistics/{delivery}/status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::status
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/shop/logistics/{delivery}/status'
 */
status.url = (args: { delivery: string | { public_id: string } } | [delivery: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/shop/logistics/{delivery}/status'
 */
status.patch = (args: { delivery: string | { public_id: string } } | [delivery: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: status.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::status
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/shop/logistics/{delivery}/status'
 */
    const statusForm = (args: { delivery: string | { public_id: string } } | [delivery: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/shop/logistics/{delivery}/status'
 */
        statusForm.patch = (args: { delivery: string | { public_id: string } } | [delivery: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:43
 * @route '/shop/logistics/{delivery}'
 */
export const destroy = (args: { delivery: string | { public_id: string } } | [delivery: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/shop/logistics/{delivery}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:43
 * @route '/shop/logistics/{delivery}'
 */
destroy.url = (args: { delivery: string | { public_id: string } } | [delivery: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:43
 * @route '/shop/logistics/{delivery}'
 */
destroy.delete = (args: { delivery: string | { public_id: string } } | [delivery: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:43
 * @route '/shop/logistics/{delivery}'
 */
    const destroyForm = (args: { delivery: string | { public_id: string } } | [delivery: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:43
 * @route '/shop/logistics/{delivery}'
 */
        destroyForm.delete = (args: { delivery: string | { public_id: string } } | [delivery: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
/**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/shop/logistics/riders'
 */
export const riders = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: riders.url(options),
    method: 'get',
})

riders.definition = {
    methods: ["get","head"],
    url: '/shop/logistics/riders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/shop/logistics/riders'
 */
riders.url = (options?: RouteQueryOptions) => {
    return riders.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/shop/logistics/riders'
 */
riders.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: riders.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/shop/logistics/riders'
 */
riders.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: riders.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/shop/logistics/riders'
 */
    const ridersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: riders.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/shop/logistics/riders'
 */
        ridersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: riders.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/shop/logistics/riders'
 */
        ridersForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: riders.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    riders.form = ridersForm
const logistics = {
    photos: Object.assign(photos, photos),
status: Object.assign(status, status),
destroy: Object.assign(destroy, destroy),
riders: Object.assign(riders, riders13f500),
}

export default logistics