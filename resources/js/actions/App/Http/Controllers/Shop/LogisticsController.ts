import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:27
 * @route '/shop/logistics'
 */
const index75e469aad6c8662cf706165facb3f457 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index75e469aad6c8662cf706165facb3f457.url(options),
    method: 'get',
})

index75e469aad6c8662cf706165facb3f457.definition = {
    methods: ["get","head"],
    url: '/shop/logistics',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:27
 * @route '/shop/logistics'
 */
index75e469aad6c8662cf706165facb3f457.url = (options?: RouteQueryOptions) => {
    return index75e469aad6c8662cf706165facb3f457.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:27
 * @route '/shop/logistics'
 */
index75e469aad6c8662cf706165facb3f457.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index75e469aad6c8662cf706165facb3f457.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:27
 * @route '/shop/logistics'
 */
index75e469aad6c8662cf706165facb3f457.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index75e469aad6c8662cf706165facb3f457.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:27
 * @route '/shop/logistics'
 */
    const index75e469aad6c8662cf706165facb3f457Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index75e469aad6c8662cf706165facb3f457.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:27
 * @route '/shop/logistics'
 */
        index75e469aad6c8662cf706165facb3f457Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index75e469aad6c8662cf706165facb3f457.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:27
 * @route '/shop/logistics'
 */
        index75e469aad6c8662cf706165facb3f457Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index75e469aad6c8662cf706165facb3f457.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index75e469aad6c8662cf706165facb3f457.form = index75e469aad6c8662cf706165facb3f457Form
    /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:27
 * @route '/staff/logistics'
 */
const indexa2d6d8bbdf8040252a1829ac57ee597b = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexa2d6d8bbdf8040252a1829ac57ee597b.url(options),
    method: 'get',
})

indexa2d6d8bbdf8040252a1829ac57ee597b.definition = {
    methods: ["get","head"],
    url: '/staff/logistics',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:27
 * @route '/staff/logistics'
 */
indexa2d6d8bbdf8040252a1829ac57ee597b.url = (options?: RouteQueryOptions) => {
    return indexa2d6d8bbdf8040252a1829ac57ee597b.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:27
 * @route '/staff/logistics'
 */
indexa2d6d8bbdf8040252a1829ac57ee597b.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexa2d6d8bbdf8040252a1829ac57ee597b.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:27
 * @route '/staff/logistics'
 */
indexa2d6d8bbdf8040252a1829ac57ee597b.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexa2d6d8bbdf8040252a1829ac57ee597b.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:27
 * @route '/staff/logistics'
 */
    const indexa2d6d8bbdf8040252a1829ac57ee597bForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexa2d6d8bbdf8040252a1829ac57ee597b.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:27
 * @route '/staff/logistics'
 */
        indexa2d6d8bbdf8040252a1829ac57ee597bForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexa2d6d8bbdf8040252a1829ac57ee597b.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:27
 * @route '/staff/logistics'
 */
        indexa2d6d8bbdf8040252a1829ac57ee597bForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexa2d6d8bbdf8040252a1829ac57ee597b.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexa2d6d8bbdf8040252a1829ac57ee597b.form = indexa2d6d8bbdf8040252a1829ac57ee597bForm

export const index = {
    '/shop/logistics': index75e469aad6c8662cf706165facb3f457,
    '/staff/logistics': indexa2d6d8bbdf8040252a1829ac57ee597b,
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/shop/logistics/{delivery}/status'
 */
const updateStatus1ffca2da8efc04d587dcae171408480e = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatus1ffca2da8efc04d587dcae171408480e.url(args, options),
    method: 'patch',
})

updateStatus1ffca2da8efc04d587dcae171408480e.definition = {
    methods: ["patch"],
    url: '/shop/logistics/{delivery}/status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/shop/logistics/{delivery}/status'
 */
updateStatus1ffca2da8efc04d587dcae171408480e.url = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return updateStatus1ffca2da8efc04d587dcae171408480e.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/shop/logistics/{delivery}/status'
 */
updateStatus1ffca2da8efc04d587dcae171408480e.patch = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatus1ffca2da8efc04d587dcae171408480e.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/shop/logistics/{delivery}/status'
 */
    const updateStatus1ffca2da8efc04d587dcae171408480eForm = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateStatus1ffca2da8efc04d587dcae171408480e.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/shop/logistics/{delivery}/status'
 */
        updateStatus1ffca2da8efc04d587dcae171408480eForm.patch = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateStatus1ffca2da8efc04d587dcae171408480e.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateStatus1ffca2da8efc04d587dcae171408480e.form = updateStatus1ffca2da8efc04d587dcae171408480eForm
    /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/staff/logistics/{delivery}/status'
 */
const updateStatusa79980c9d3025ea33828fbc3a3b8686f = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatusa79980c9d3025ea33828fbc3a3b8686f.url(args, options),
    method: 'patch',
})

updateStatusa79980c9d3025ea33828fbc3a3b8686f.definition = {
    methods: ["patch"],
    url: '/staff/logistics/{delivery}/status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/staff/logistics/{delivery}/status'
 */
updateStatusa79980c9d3025ea33828fbc3a3b8686f.url = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return updateStatusa79980c9d3025ea33828fbc3a3b8686f.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/staff/logistics/{delivery}/status'
 */
updateStatusa79980c9d3025ea33828fbc3a3b8686f.patch = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatusa79980c9d3025ea33828fbc3a3b8686f.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/staff/logistics/{delivery}/status'
 */
    const updateStatusa79980c9d3025ea33828fbc3a3b8686fForm = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateStatusa79980c9d3025ea33828fbc3a3b8686f.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:36
 * @route '/staff/logistics/{delivery}/status'
 */
        updateStatusa79980c9d3025ea33828fbc3a3b8686fForm.patch = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateStatusa79980c9d3025ea33828fbc3a3b8686f.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateStatusa79980c9d3025ea33828fbc3a3b8686f.form = updateStatusa79980c9d3025ea33828fbc3a3b8686fForm

export const updateStatus = {
    '/shop/logistics/{delivery}/status': updateStatus1ffca2da8efc04d587dcae171408480e,
    '/staff/logistics/{delivery}/status': updateStatusa79980c9d3025ea33828fbc3a3b8686f,
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:43
 * @route '/shop/logistics/{delivery}'
 */
const destroy355db6d2b756353d8258e69819b2d863 = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy355db6d2b756353d8258e69819b2d863.url(args, options),
    method: 'delete',
})

destroy355db6d2b756353d8258e69819b2d863.definition = {
    methods: ["delete"],
    url: '/shop/logistics/{delivery}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:43
 * @route '/shop/logistics/{delivery}'
 */
destroy355db6d2b756353d8258e69819b2d863.url = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroy355db6d2b756353d8258e69819b2d863.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:43
 * @route '/shop/logistics/{delivery}'
 */
destroy355db6d2b756353d8258e69819b2d863.delete = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy355db6d2b756353d8258e69819b2d863.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:43
 * @route '/shop/logistics/{delivery}'
 */
    const destroy355db6d2b756353d8258e69819b2d863Form = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy355db6d2b756353d8258e69819b2d863.url(args, {
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
        destroy355db6d2b756353d8258e69819b2d863Form.delete = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy355db6d2b756353d8258e69819b2d863.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy355db6d2b756353d8258e69819b2d863.form = destroy355db6d2b756353d8258e69819b2d863Form
    /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:43
 * @route '/staff/logistics/{delivery}'
 */
const destroy6121edffe76da4e7750d19627da92246 = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy6121edffe76da4e7750d19627da92246.url(args, options),
    method: 'delete',
})

destroy6121edffe76da4e7750d19627da92246.definition = {
    methods: ["delete"],
    url: '/staff/logistics/{delivery}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:43
 * @route '/staff/logistics/{delivery}'
 */
destroy6121edffe76da4e7750d19627da92246.url = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroy6121edffe76da4e7750d19627da92246.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:43
 * @route '/staff/logistics/{delivery}'
 */
destroy6121edffe76da4e7750d19627da92246.delete = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy6121edffe76da4e7750d19627da92246.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:43
 * @route '/staff/logistics/{delivery}'
 */
    const destroy6121edffe76da4e7750d19627da92246Form = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy6121edffe76da4e7750d19627da92246.url(args, {
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
 * @route '/staff/logistics/{delivery}'
 */
        destroy6121edffe76da4e7750d19627da92246Form.delete = (args: { delivery: number | { id: number } } | [delivery: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy6121edffe76da4e7750d19627da92246.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy6121edffe76da4e7750d19627da92246.form = destroy6121edffe76da4e7750d19627da92246Form

export const destroy = {
    '/shop/logistics/{delivery}': destroy355db6d2b756353d8258e69819b2d863,
    '/staff/logistics/{delivery}': destroy6121edffe76da4e7750d19627da92246,
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/shop/logistics/riders'
 */
const riders9e9994f56f3324f5d8b47caecbf61bcf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: riders9e9994f56f3324f5d8b47caecbf61bcf.url(options),
    method: 'get',
})

riders9e9994f56f3324f5d8b47caecbf61bcf.definition = {
    methods: ["get","head"],
    url: '/shop/logistics/riders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/shop/logistics/riders'
 */
riders9e9994f56f3324f5d8b47caecbf61bcf.url = (options?: RouteQueryOptions) => {
    return riders9e9994f56f3324f5d8b47caecbf61bcf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/shop/logistics/riders'
 */
riders9e9994f56f3324f5d8b47caecbf61bcf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: riders9e9994f56f3324f5d8b47caecbf61bcf.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/shop/logistics/riders'
 */
riders9e9994f56f3324f5d8b47caecbf61bcf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: riders9e9994f56f3324f5d8b47caecbf61bcf.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/shop/logistics/riders'
 */
    const riders9e9994f56f3324f5d8b47caecbf61bcfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: riders9e9994f56f3324f5d8b47caecbf61bcf.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/shop/logistics/riders'
 */
        riders9e9994f56f3324f5d8b47caecbf61bcfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: riders9e9994f56f3324f5d8b47caecbf61bcf.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/shop/logistics/riders'
 */
        riders9e9994f56f3324f5d8b47caecbf61bcfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: riders9e9994f56f3324f5d8b47caecbf61bcf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    riders9e9994f56f3324f5d8b47caecbf61bcf.form = riders9e9994f56f3324f5d8b47caecbf61bcfForm
    /**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/staff/logistics/riders'
 */
const riders3c6c781c44ce220bfdc8586e2862cc1d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: riders3c6c781c44ce220bfdc8586e2862cc1d.url(options),
    method: 'get',
})

riders3c6c781c44ce220bfdc8586e2862cc1d.definition = {
    methods: ["get","head"],
    url: '/staff/logistics/riders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/staff/logistics/riders'
 */
riders3c6c781c44ce220bfdc8586e2862cc1d.url = (options?: RouteQueryOptions) => {
    return riders3c6c781c44ce220bfdc8586e2862cc1d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/staff/logistics/riders'
 */
riders3c6c781c44ce220bfdc8586e2862cc1d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: riders3c6c781c44ce220bfdc8586e2862cc1d.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/staff/logistics/riders'
 */
riders3c6c781c44ce220bfdc8586e2862cc1d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: riders3c6c781c44ce220bfdc8586e2862cc1d.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/staff/logistics/riders'
 */
    const riders3c6c781c44ce220bfdc8586e2862cc1dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: riders3c6c781c44ce220bfdc8586e2862cc1d.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/staff/logistics/riders'
 */
        riders3c6c781c44ce220bfdc8586e2862cc1dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: riders3c6c781c44ce220bfdc8586e2862cc1d.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\LogisticsController::riders
 * @see app/Http/Controllers/Shop/LogisticsController.php:52
 * @route '/staff/logistics/riders'
 */
        riders3c6c781c44ce220bfdc8586e2862cc1dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: riders3c6c781c44ce220bfdc8586e2862cc1d.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    riders3c6c781c44ce220bfdc8586e2862cc1d.form = riders3c6c781c44ce220bfdc8586e2862cc1dForm

export const riders = {
    '/shop/logistics/riders': riders9e9994f56f3324f5d8b47caecbf61bcf,
    '/staff/logistics/riders': riders3c6c781c44ce220bfdc8586e2862cc1d,
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:59
 * @route '/shop/logistics/riders'
 */
const storeRider9e9994f56f3324f5d8b47caecbf61bcf = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeRider9e9994f56f3324f5d8b47caecbf61bcf.url(options),
    method: 'post',
})

storeRider9e9994f56f3324f5d8b47caecbf61bcf.definition = {
    methods: ["post"],
    url: '/shop/logistics/riders',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:59
 * @route '/shop/logistics/riders'
 */
storeRider9e9994f56f3324f5d8b47caecbf61bcf.url = (options?: RouteQueryOptions) => {
    return storeRider9e9994f56f3324f5d8b47caecbf61bcf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:59
 * @route '/shop/logistics/riders'
 */
storeRider9e9994f56f3324f5d8b47caecbf61bcf.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeRider9e9994f56f3324f5d8b47caecbf61bcf.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:59
 * @route '/shop/logistics/riders'
 */
    const storeRider9e9994f56f3324f5d8b47caecbf61bcfForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeRider9e9994f56f3324f5d8b47caecbf61bcf.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:59
 * @route '/shop/logistics/riders'
 */
        storeRider9e9994f56f3324f5d8b47caecbf61bcfForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeRider9e9994f56f3324f5d8b47caecbf61bcf.url(options),
            method: 'post',
        })
    
    storeRider9e9994f56f3324f5d8b47caecbf61bcf.form = storeRider9e9994f56f3324f5d8b47caecbf61bcfForm
    /**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:59
 * @route '/staff/logistics/riders'
 */
const storeRider3c6c781c44ce220bfdc8586e2862cc1d = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeRider3c6c781c44ce220bfdc8586e2862cc1d.url(options),
    method: 'post',
})

storeRider3c6c781c44ce220bfdc8586e2862cc1d.definition = {
    methods: ["post"],
    url: '/staff/logistics/riders',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:59
 * @route '/staff/logistics/riders'
 */
storeRider3c6c781c44ce220bfdc8586e2862cc1d.url = (options?: RouteQueryOptions) => {
    return storeRider3c6c781c44ce220bfdc8586e2862cc1d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:59
 * @route '/staff/logistics/riders'
 */
storeRider3c6c781c44ce220bfdc8586e2862cc1d.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeRider3c6c781c44ce220bfdc8586e2862cc1d.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:59
 * @route '/staff/logistics/riders'
 */
    const storeRider3c6c781c44ce220bfdc8586e2862cc1dForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeRider3c6c781c44ce220bfdc8586e2862cc1d.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::storeRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:59
 * @route '/staff/logistics/riders'
 */
        storeRider3c6c781c44ce220bfdc8586e2862cc1dForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeRider3c6c781c44ce220bfdc8586e2862cc1d.url(options),
            method: 'post',
        })
    
    storeRider3c6c781c44ce220bfdc8586e2862cc1d.form = storeRider3c6c781c44ce220bfdc8586e2862cc1dForm

export const storeRider = {
    '/shop/logistics/riders': storeRider9e9994f56f3324f5d8b47caecbf61bcf,
    '/staff/logistics/riders': storeRider3c6c781c44ce220bfdc8586e2862cc1d,
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:66
 * @route '/shop/logistics/riders/{rider}'
 */
const updateRider2f94305c4b4afed23314a0717ba3aee1 = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateRider2f94305c4b4afed23314a0717ba3aee1.url(args, options),
    method: 'patch',
})

updateRider2f94305c4b4afed23314a0717ba3aee1.definition = {
    methods: ["patch"],
    url: '/shop/logistics/riders/{rider}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:66
 * @route '/shop/logistics/riders/{rider}'
 */
updateRider2f94305c4b4afed23314a0717ba3aee1.url = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return updateRider2f94305c4b4afed23314a0717ba3aee1.definition.url
            .replace('{rider}', parsedArgs.rider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:66
 * @route '/shop/logistics/riders/{rider}'
 */
updateRider2f94305c4b4afed23314a0717ba3aee1.patch = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateRider2f94305c4b4afed23314a0717ba3aee1.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:66
 * @route '/shop/logistics/riders/{rider}'
 */
    const updateRider2f94305c4b4afed23314a0717ba3aee1Form = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateRider2f94305c4b4afed23314a0717ba3aee1.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:66
 * @route '/shop/logistics/riders/{rider}'
 */
        updateRider2f94305c4b4afed23314a0717ba3aee1Form.patch = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateRider2f94305c4b4afed23314a0717ba3aee1.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateRider2f94305c4b4afed23314a0717ba3aee1.form = updateRider2f94305c4b4afed23314a0717ba3aee1Form
    /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:66
 * @route '/staff/logistics/riders/{rider}'
 */
const updateRidercdff2f925b53883e1d912fb5e8f3ba86 = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateRidercdff2f925b53883e1d912fb5e8f3ba86.url(args, options),
    method: 'patch',
})

updateRidercdff2f925b53883e1d912fb5e8f3ba86.definition = {
    methods: ["patch"],
    url: '/staff/logistics/riders/{rider}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:66
 * @route '/staff/logistics/riders/{rider}'
 */
updateRidercdff2f925b53883e1d912fb5e8f3ba86.url = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return updateRidercdff2f925b53883e1d912fb5e8f3ba86.definition.url
            .replace('{rider}', parsedArgs.rider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:66
 * @route '/staff/logistics/riders/{rider}'
 */
updateRidercdff2f925b53883e1d912fb5e8f3ba86.patch = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateRidercdff2f925b53883e1d912fb5e8f3ba86.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:66
 * @route '/staff/logistics/riders/{rider}'
 */
    const updateRidercdff2f925b53883e1d912fb5e8f3ba86Form = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateRidercdff2f925b53883e1d912fb5e8f3ba86.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:66
 * @route '/staff/logistics/riders/{rider}'
 */
        updateRidercdff2f925b53883e1d912fb5e8f3ba86Form.patch = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateRidercdff2f925b53883e1d912fb5e8f3ba86.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateRidercdff2f925b53883e1d912fb5e8f3ba86.form = updateRidercdff2f925b53883e1d912fb5e8f3ba86Form

export const updateRider = {
    '/shop/logistics/riders/{rider}': updateRider2f94305c4b4afed23314a0717ba3aee1,
    '/staff/logistics/riders/{rider}': updateRidercdff2f925b53883e1d912fb5e8f3ba86,
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:73
 * @route '/shop/logistics/riders/{rider}'
 */
const destroyRider2f94305c4b4afed23314a0717ba3aee1 = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyRider2f94305c4b4afed23314a0717ba3aee1.url(args, options),
    method: 'delete',
})

destroyRider2f94305c4b4afed23314a0717ba3aee1.definition = {
    methods: ["delete"],
    url: '/shop/logistics/riders/{rider}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:73
 * @route '/shop/logistics/riders/{rider}'
 */
destroyRider2f94305c4b4afed23314a0717ba3aee1.url = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroyRider2f94305c4b4afed23314a0717ba3aee1.definition.url
            .replace('{rider}', parsedArgs.rider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:73
 * @route '/shop/logistics/riders/{rider}'
 */
destroyRider2f94305c4b4afed23314a0717ba3aee1.delete = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyRider2f94305c4b4afed23314a0717ba3aee1.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:73
 * @route '/shop/logistics/riders/{rider}'
 */
    const destroyRider2f94305c4b4afed23314a0717ba3aee1Form = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyRider2f94305c4b4afed23314a0717ba3aee1.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:73
 * @route '/shop/logistics/riders/{rider}'
 */
        destroyRider2f94305c4b4afed23314a0717ba3aee1Form.delete = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyRider2f94305c4b4afed23314a0717ba3aee1.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyRider2f94305c4b4afed23314a0717ba3aee1.form = destroyRider2f94305c4b4afed23314a0717ba3aee1Form
    /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:73
 * @route '/staff/logistics/riders/{rider}'
 */
const destroyRidercdff2f925b53883e1d912fb5e8f3ba86 = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyRidercdff2f925b53883e1d912fb5e8f3ba86.url(args, options),
    method: 'delete',
})

destroyRidercdff2f925b53883e1d912fb5e8f3ba86.definition = {
    methods: ["delete"],
    url: '/staff/logistics/riders/{rider}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:73
 * @route '/staff/logistics/riders/{rider}'
 */
destroyRidercdff2f925b53883e1d912fb5e8f3ba86.url = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroyRidercdff2f925b53883e1d912fb5e8f3ba86.definition.url
            .replace('{rider}', parsedArgs.rider.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:73
 * @route '/staff/logistics/riders/{rider}'
 */
destroyRidercdff2f925b53883e1d912fb5e8f3ba86.delete = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyRidercdff2f925b53883e1d912fb5e8f3ba86.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:73
 * @route '/staff/logistics/riders/{rider}'
 */
    const destroyRidercdff2f925b53883e1d912fb5e8f3ba86Form = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyRidercdff2f925b53883e1d912fb5e8f3ba86.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroyRider
 * @see app/Http/Controllers/Shop/LogisticsController.php:73
 * @route '/staff/logistics/riders/{rider}'
 */
        destroyRidercdff2f925b53883e1d912fb5e8f3ba86Form.delete = (args: { rider: number | { id: number } } | [rider: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyRidercdff2f925b53883e1d912fb5e8f3ba86.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyRidercdff2f925b53883e1d912fb5e8f3ba86.form = destroyRidercdff2f925b53883e1d912fb5e8f3ba86Form

export const destroyRider = {
    '/shop/logistics/riders/{rider}': destroyRider2f94305c4b4afed23314a0717ba3aee1,
    '/staff/logistics/riders/{rider}': destroyRidercdff2f925b53883e1d912fb5e8f3ba86,
}

const LogisticsController = { index, updateStatus, destroy, riders, storeRider, updateRider, destroyRider }

export default LogisticsController