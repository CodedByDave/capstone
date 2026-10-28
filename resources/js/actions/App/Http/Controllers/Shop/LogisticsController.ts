import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/shop/logistics'
 */
index75e469aad6c8662cf706165facb3f457.url = (options?: RouteQueryOptions) => {
    return index75e469aad6c8662cf706165facb3f457.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/shop/logistics'
 */
index75e469aad6c8662cf706165facb3f457.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index75e469aad6c8662cf706165facb3f457.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/shop/logistics'
 */
index75e469aad6c8662cf706165facb3f457.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index75e469aad6c8662cf706165facb3f457.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/shop/logistics'
 */
    const index75e469aad6c8662cf706165facb3f457Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index75e469aad6c8662cf706165facb3f457.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/shop/logistics'
 */
        index75e469aad6c8662cf706165facb3f457Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index75e469aad6c8662cf706165facb3f457.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/staff/logistics'
 */
indexa2d6d8bbdf8040252a1829ac57ee597b.url = (options?: RouteQueryOptions) => {
    return indexa2d6d8bbdf8040252a1829ac57ee597b.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/staff/logistics'
 */
indexa2d6d8bbdf8040252a1829ac57ee597b.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexa2d6d8bbdf8040252a1829ac57ee597b.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/staff/logistics'
 */
indexa2d6d8bbdf8040252a1829ac57ee597b.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexa2d6d8bbdf8040252a1829ac57ee597b.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/staff/logistics'
 */
    const indexa2d6d8bbdf8040252a1829ac57ee597bForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexa2d6d8bbdf8040252a1829ac57ee597b.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/staff/logistics'
 */
        indexa2d6d8bbdf8040252a1829ac57ee597bForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexa2d6d8bbdf8040252a1829ac57ee597b.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\LogisticsController::index
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/shop/logistics/{delivery}/status'
 */
const updateStatus1ffca2da8efc04d587dcae171408480e = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatus1ffca2da8efc04d587dcae171408480e.url(args, options),
    method: 'patch',
})

updateStatus1ffca2da8efc04d587dcae171408480e.definition = {
    methods: ["patch"],
    url: '/shop/logistics/{delivery}/status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/shop/logistics/{delivery}/status'
 */
updateStatus1ffca2da8efc04d587dcae171408480e.url = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions) => {
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

    return updateStatus1ffca2da8efc04d587dcae171408480e.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/shop/logistics/{delivery}/status'
 */
updateStatus1ffca2da8efc04d587dcae171408480e.patch = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatus1ffca2da8efc04d587dcae171408480e.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/shop/logistics/{delivery}/status'
 */
    const updateStatus1ffca2da8efc04d587dcae171408480eForm = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/shop/logistics/{delivery}/status'
 */
        updateStatus1ffca2da8efc04d587dcae171408480eForm.patch = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/staff/logistics/{delivery}/status'
 */
const updateStatusa79980c9d3025ea33828fbc3a3b8686f = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatusa79980c9d3025ea33828fbc3a3b8686f.url(args, options),
    method: 'patch',
})

updateStatusa79980c9d3025ea33828fbc3a3b8686f.definition = {
    methods: ["patch"],
    url: '/staff/logistics/{delivery}/status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/staff/logistics/{delivery}/status'
 */
updateStatusa79980c9d3025ea33828fbc3a3b8686f.url = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions) => {
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

    return updateStatusa79980c9d3025ea33828fbc3a3b8686f.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/staff/logistics/{delivery}/status'
 */
updateStatusa79980c9d3025ea33828fbc3a3b8686f.patch = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatusa79980c9d3025ea33828fbc3a3b8686f.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::updateStatus
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/staff/logistics/{delivery}/status'
 */
    const updateStatusa79980c9d3025ea33828fbc3a3b8686fForm = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:32
 * @route '/staff/logistics/{delivery}/status'
 */
        updateStatusa79980c9d3025ea33828fbc3a3b8686fForm.patch = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/shop/logistics/{delivery}'
 */
const destroy355db6d2b756353d8258e69819b2d863 = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy355db6d2b756353d8258e69819b2d863.url(args, options),
    method: 'delete',
})

destroy355db6d2b756353d8258e69819b2d863.definition = {
    methods: ["delete"],
    url: '/shop/logistics/{delivery}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/shop/logistics/{delivery}'
 */
destroy355db6d2b756353d8258e69819b2d863.url = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions) => {
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

    return destroy355db6d2b756353d8258e69819b2d863.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/shop/logistics/{delivery}'
 */
destroy355db6d2b756353d8258e69819b2d863.delete = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy355db6d2b756353d8258e69819b2d863.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/shop/logistics/{delivery}'
 */
    const destroy355db6d2b756353d8258e69819b2d863Form = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/shop/logistics/{delivery}'
 */
        destroy355db6d2b756353d8258e69819b2d863Form.delete = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/staff/logistics/{delivery}'
 */
const destroy6121edffe76da4e7750d19627da92246 = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy6121edffe76da4e7750d19627da92246.url(args, options),
    method: 'delete',
})

destroy6121edffe76da4e7750d19627da92246.definition = {
    methods: ["delete"],
    url: '/staff/logistics/{delivery}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/staff/logistics/{delivery}'
 */
destroy6121edffe76da4e7750d19627da92246.url = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions) => {
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

    return destroy6121edffe76da4e7750d19627da92246.definition.url
            .replace('{delivery}', parsedArgs.delivery.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/staff/logistics/{delivery}'
 */
destroy6121edffe76da4e7750d19627da92246.delete = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy6121edffe76da4e7750d19627da92246.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::destroy
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/staff/logistics/{delivery}'
 */
    const destroy6121edffe76da4e7750d19627da92246Form = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/LogisticsController.php:39
 * @route '/staff/logistics/{delivery}'
 */
        destroy6121edffe76da4e7750d19627da92246Form.delete = (args: { delivery: string | number | { public_id: string | number } } | [delivery: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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

const LogisticsController = { index, updateStatus, destroy }

export default LogisticsController