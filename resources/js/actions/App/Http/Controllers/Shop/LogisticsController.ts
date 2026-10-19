import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:26
 * @route '/shop/logistics'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/logistics',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:26
 * @route '/shop/logistics'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:26
 * @route '/shop/logistics'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:26
 * @route '/shop/logistics'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:26
 * @route '/shop/logistics'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:26
 * @route '/shop/logistics'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:26
 * @route '/shop/logistics'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\Shop\LogisticsController::store
 * @see app/Http/Controllers/Shop/LogisticsController.php:75
 * @route '/shop/logistics'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/shop/logistics',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::store
 * @see app/Http/Controllers/Shop/LogisticsController.php:75
 * @route '/shop/logistics'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::store
 * @see app/Http/Controllers/Shop/LogisticsController.php:75
 * @route '/shop/logistics'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::store
 * @see app/Http/Controllers/Shop/LogisticsController.php:75
 * @route '/shop/logistics'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::store
 * @see app/Http/Controllers/Shop/LogisticsController.php:75
 * @route '/shop/logistics'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:97
 * @route '/shop/logistics/{delivery}/status'
 */
export const updateStatus = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatus.url(args, options),
    method: 'patch',
})

updateStatus.definition = {
    methods: ["patch"],
    url: '/shop/logistics/{delivery}/status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:97
 * @route '/shop/logistics/{delivery}/status'
 */
updateStatus.url = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { delivery: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { delivery: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    delivery: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        delivery: typeof args.delivery === 'object'
                ? args.delivery.id
                : args.delivery,
                }

    return updateStatus.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:97
 * @route '/shop/logistics/{delivery}/status'
 */
updateStatus.patch = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatus.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:97
 * @route '/shop/logistics/{delivery}/status'
 */
    const updateStatusForm = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateStatus.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:97
 * @route '/shop/logistics/{delivery}/status'
 */
        updateStatusForm.patch = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateStatus.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateStatus.form = updateStatusForm
/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:132
 * @route '/shop/logistics/{delivery}'
 */
export const destroy = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/shop/logistics/{delivery}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:132
 * @route '/shop/logistics/{delivery}'
 */
destroy.url = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { delivery: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { delivery: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    delivery: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        delivery: typeof args.delivery === 'object'
                ? args.delivery.id
                : args.delivery,
                }

    return destroy.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:132
 * @route '/shop/logistics/{delivery}'
 */
destroy.delete = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:132
 * @route '/shop/logistics/{delivery}'
 */
    const destroyForm = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:132
 * @route '/shop/logistics/{delivery}'
 */
        destroyForm.delete = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:141
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:141
 * @route '/shop/logistics/riders'
 */
riders.url = (options?: RouteQueryOptions) => {
    return riders.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:141
 * @route '/shop/logistics/riders'
 */
riders.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: riders.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:141
 * @route '/shop/logistics/riders'
 */
riders.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: riders.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:141
 * @route '/shop/logistics/riders'
 */
    const ridersForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: riders.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:141
 * @route '/shop/logistics/riders'
 */
        ridersForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: riders.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:141
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
/**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:151
 * @route '/shop/logistics/riders'
 */
export const storeRider = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeRider.url(options),
    method: 'post',
})

storeRider.definition = {
    methods: ["post"],
    url: '/shop/logistics/riders',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:151
 * @route '/shop/logistics/riders'
 */
storeRider.url = (options?: RouteQueryOptions) => {
    return storeRider.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:151
 * @route '/shop/logistics/riders'
 */
storeRider.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeRider.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:151
 * @route '/shop/logistics/riders'
 */
    const storeRiderForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeRider.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:151
 * @route '/shop/logistics/riders'
 */
        storeRiderForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeRider.url(options),
            method: 'post',
        })
    
    storeRider.form = storeRiderForm
/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:161
 * @route '/shop/logistics/riders/{rider}'
 */
export const updateRider = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateRider.url(args, options),
    method: 'patch',
})

updateRider.definition = {
    methods: ["patch"],
    url: '/shop/logistics/riders/{rider}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:161
 * @route '/shop/logistics/riders/{rider}'
 */
updateRider.url = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return updateRider.definition.url
            .replace('{rider}', parsedArgs.rider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:161
 * @route '/shop/logistics/riders/{rider}'
 */
updateRider.patch = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateRider.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:161
 * @route '/shop/logistics/riders/{rider}'
 */
    const updateRiderForm = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateRider.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:161
 * @route '/shop/logistics/riders/{rider}'
 */
        updateRiderForm.patch = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateRider.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateRider.form = updateRiderForm
/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:170
 * @route '/shop/logistics/riders/{rider}'
 */
export const destroyRider = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyRider.url(args, options),
    method: 'delete',
})

destroyRider.definition = {
    methods: ["delete"],
    url: '/shop/logistics/riders/{rider}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:170
 * @route '/shop/logistics/riders/{rider}'
 */
destroyRider.url = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroyRider.definition.url
            .replace('{rider}', parsedArgs.rider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:170
 * @route '/shop/logistics/riders/{rider}'
 */
destroyRider.delete = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyRider.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:170
 * @route '/shop/logistics/riders/{rider}'
 */
    const destroyRiderForm = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyRider.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:170
 * @route '/shop/logistics/riders/{rider}'
 */
        destroyRiderForm.delete = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyRider.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyRider.form = destroyRiderForm
const LogisticsController = { index, store, updateStatus, destroy, riders, storeRider, updateRider, destroyRider }

export default LogisticsController