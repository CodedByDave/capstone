import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
import item from './item'
/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/shop/payroll'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/payroll',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/shop/payroll'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/shop/payroll'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/shop/payroll'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/shop/payroll'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/shop/payroll'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/shop/payroll'
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
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:105
 * @route '/shop/payroll'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/shop/payroll',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:105
 * @route '/shop/payroll'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:105
 * @route '/shop/payroll'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:105
 * @route '/shop/payroll'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:105
 * @route '/shop/payroll'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::settings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/shop/payroll/settings'
 */
export const settings = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: settings.url(options),
    method: 'put',
})

settings.definition = {
    methods: ["put"],
    url: '/shop/payroll/settings',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::settings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/shop/payroll/settings'
 */
settings.url = (options?: RouteQueryOptions) => {
    return settings.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::settings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/shop/payroll/settings'
 */
settings.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: settings.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::settings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/shop/payroll/settings'
 */
    const settingsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: settings.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::settings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/shop/payroll/settings'
 */
        settingsForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: settings.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    settings.form = settingsForm
/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/shop/payroll/{payroll}'
 */
export const show = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/shop/payroll/{payroll}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/shop/payroll/{payroll}'
 */
show.url = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { payroll: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { payroll: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    payroll: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        payroll: typeof args.payroll === 'object'
                ? args.payroll.public_id
                : args.payroll,
                }

    return show.definition.url
            .replace('{payroll}', parsedArgs.payroll.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/shop/payroll/{payroll}'
 */
show.get = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/shop/payroll/{payroll}'
 */
show.head = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/shop/payroll/{payroll}'
 */
    const showForm = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/shop/payroll/{payroll}'
 */
        showForm.get = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/shop/payroll/{payroll}'
 */
        showForm.head = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:135
 * @route '/shop/payroll/{payroll}/recalculate'
 */
export const recalculate = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recalculate.url(args, options),
    method: 'post',
})

recalculate.definition = {
    methods: ["post"],
    url: '/shop/payroll/{payroll}/recalculate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:135
 * @route '/shop/payroll/{payroll}/recalculate'
 */
recalculate.url = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { payroll: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { payroll: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    payroll: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        payroll: typeof args.payroll === 'object'
                ? args.payroll.public_id
                : args.payroll,
                }

    return recalculate.definition.url
            .replace('{payroll}', parsedArgs.payroll.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:135
 * @route '/shop/payroll/{payroll}/recalculate'
 */
recalculate.post = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recalculate.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:135
 * @route '/shop/payroll/{payroll}/recalculate'
 */
    const recalculateForm = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: recalculate.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:135
 * @route '/shop/payroll/{payroll}/recalculate'
 */
        recalculateForm.post = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: recalculate.url(args, options),
            method: 'post',
        })
    
    recalculate.form = recalculateForm
/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:146
 * @route '/shop/payroll/{payroll}/finalize'
 */
export const finalize = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finalize.url(args, options),
    method: 'post',
})

finalize.definition = {
    methods: ["post"],
    url: '/shop/payroll/{payroll}/finalize',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:146
 * @route '/shop/payroll/{payroll}/finalize'
 */
finalize.url = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { payroll: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { payroll: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    payroll: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        payroll: typeof args.payroll === 'object'
                ? args.payroll.public_id
                : args.payroll,
                }

    return finalize.definition.url
            .replace('{payroll}', parsedArgs.payroll.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:146
 * @route '/shop/payroll/{payroll}/finalize'
 */
finalize.post = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finalize.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:146
 * @route '/shop/payroll/{payroll}/finalize'
 */
    const finalizeForm = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: finalize.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:146
 * @route '/shop/payroll/{payroll}/finalize'
 */
        finalizeForm.post = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: finalize.url(args, options),
            method: 'post',
        })
    
    finalize.form = finalizeForm
/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:157
 * @route '/shop/payroll/{payroll}'
 */
export const destroy = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/shop/payroll/{payroll}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:157
 * @route '/shop/payroll/{payroll}'
 */
destroy.url = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { payroll: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { payroll: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    payroll: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        payroll: typeof args.payroll === 'object'
                ? args.payroll.public_id
                : args.payroll,
                }

    return destroy.definition.url
            .replace('{payroll}', parsedArgs.payroll.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:157
 * @route '/shop/payroll/{payroll}'
 */
destroy.delete = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:157
 * @route '/shop/payroll/{payroll}'
 */
    const destroyForm = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:157
 * @route '/shop/payroll/{payroll}'
 */
        destroyForm.delete = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const payroll = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
settings: Object.assign(settings, settings),
show: Object.assign(show, show),
item: Object.assign(item, item),
recalculate: Object.assign(recalculate, recalculate),
finalize: Object.assign(finalize, finalize),
destroy: Object.assign(destroy, destroy),
}

export default payroll