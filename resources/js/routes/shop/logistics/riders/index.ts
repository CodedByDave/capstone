import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\LogisticsController::store
 * @see app/Http/Controllers/Shop/LogisticsController.php:151
 * @route '/shop/logistics/riders'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/shop/logistics/riders',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::store
 * @see app/Http/Controllers/Shop/LogisticsController.php:151
 * @route '/shop/logistics/riders'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::store
 * @see app/Http/Controllers/Shop/LogisticsController.php:151
 * @route '/shop/logistics/riders'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::store
 * @see app/Http/Controllers/Shop/LogisticsController.php:151
 * @route '/shop/logistics/riders'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::store
 * @see app/Http/Controllers/Shop/LogisticsController.php:151
 * @route '/shop/logistics/riders'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\LogisticsController::update
 * @see app/Http/Controllers/Shop/LogisticsController.php:161
 * @route '/shop/logistics/riders/{rider}'
 */
export const update = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/shop/logistics/riders/{rider}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::update
 * @see app/Http/Controllers/Shop/LogisticsController.php:161
 * @route '/shop/logistics/riders/{rider}'
 */
update.url = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { rider: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { rider: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    rider: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        rider: typeof args.rider === 'object'
                ? args.rider.id
                : args.rider,
                }

    return update.definition.url
            .replace('{rider}', parsedArgs.rider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::update
 * @see app/Http/Controllers/Shop/LogisticsController.php:161
 * @route '/shop/logistics/riders/{rider}'
 */
update.patch = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::update
 * @see app/Http/Controllers/Shop/LogisticsController.php:161
 * @route '/shop/logistics/riders/{rider}'
 */
    const updateForm = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::update
 * @see app/Http/Controllers/Shop/LogisticsController.php:161
 * @route '/shop/logistics/riders/{rider}'
 */
        updateForm.patch = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:170
 * @route '/shop/logistics/riders/{rider}'
 */
export const destroy = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/shop/logistics/riders/{rider}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:170
 * @route '/shop/logistics/riders/{rider}'
 */
destroy.url = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { rider: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { rider: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    rider: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        rider: typeof args.rider === 'object'
                ? args.rider.id
                : args.rider,
                }

    return destroy.definition.url
            .replace('{rider}', parsedArgs.rider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:170
 * @route '/shop/logistics/riders/{rider}'
 */
destroy.delete = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:170
 * @route '/shop/logistics/riders/{rider}'
 */
    const destroyForm = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:170
 * @route '/shop/logistics/riders/{rider}'
 */
        destroyForm.delete = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const riders = {
    store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
}

export default riders