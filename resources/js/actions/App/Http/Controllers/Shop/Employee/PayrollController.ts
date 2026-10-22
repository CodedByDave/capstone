import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/shop/payroll'
 */
const indexddd525b71526ca67fe471a34585f4768 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexddd525b71526ca67fe471a34585f4768.url(options),
    method: 'get',
})

indexddd525b71526ca67fe471a34585f4768.definition = {
    methods: ["get","head"],
    url: '/shop/payroll',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/shop/payroll'
 */
indexddd525b71526ca67fe471a34585f4768.url = (options?: RouteQueryOptions) => {
    return indexddd525b71526ca67fe471a34585f4768.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/shop/payroll'
 */
indexddd525b71526ca67fe471a34585f4768.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexddd525b71526ca67fe471a34585f4768.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/shop/payroll'
 */
indexddd525b71526ca67fe471a34585f4768.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexddd525b71526ca67fe471a34585f4768.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/shop/payroll'
 */
    const indexddd525b71526ca67fe471a34585f4768Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexddd525b71526ca67fe471a34585f4768.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/shop/payroll'
 */
        indexddd525b71526ca67fe471a34585f4768Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexddd525b71526ca67fe471a34585f4768.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/shop/payroll'
 */
        indexddd525b71526ca67fe471a34585f4768Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexddd525b71526ca67fe471a34585f4768.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexddd525b71526ca67fe471a34585f4768.form = indexddd525b71526ca67fe471a34585f4768Form
    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/staff/payroll'
 */
const indexf2aa4538e4801c2f4f9d89889e13c966 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexf2aa4538e4801c2f4f9d89889e13c966.url(options),
    method: 'get',
})

indexf2aa4538e4801c2f4f9d89889e13c966.definition = {
    methods: ["get","head"],
    url: '/staff/payroll',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/staff/payroll'
 */
indexf2aa4538e4801c2f4f9d89889e13c966.url = (options?: RouteQueryOptions) => {
    return indexf2aa4538e4801c2f4f9d89889e13c966.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/staff/payroll'
 */
indexf2aa4538e4801c2f4f9d89889e13c966.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexf2aa4538e4801c2f4f9d89889e13c966.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/staff/payroll'
 */
indexf2aa4538e4801c2f4f9d89889e13c966.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexf2aa4538e4801c2f4f9d89889e13c966.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/staff/payroll'
 */
    const indexf2aa4538e4801c2f4f9d89889e13c966Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexf2aa4538e4801c2f4f9d89889e13c966.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/staff/payroll'
 */
        indexf2aa4538e4801c2f4f9d89889e13c966Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexf2aa4538e4801c2f4f9d89889e13c966.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::index
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:43
 * @route '/staff/payroll'
 */
        indexf2aa4538e4801c2f4f9d89889e13c966Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexf2aa4538e4801c2f4f9d89889e13c966.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexf2aa4538e4801c2f4f9d89889e13c966.form = indexf2aa4538e4801c2f4f9d89889e13c966Form

export const index = {
    '/shop/payroll': indexddd525b71526ca67fe471a34585f4768,
    '/staff/payroll': indexf2aa4538e4801c2f4f9d89889e13c966,
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:108
 * @route '/shop/payroll'
 */
const storeddd525b71526ca67fe471a34585f4768 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeddd525b71526ca67fe471a34585f4768.url(options),
    method: 'post',
})

storeddd525b71526ca67fe471a34585f4768.definition = {
    methods: ["post"],
    url: '/shop/payroll',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:108
 * @route '/shop/payroll'
 */
storeddd525b71526ca67fe471a34585f4768.url = (options?: RouteQueryOptions) => {
    return storeddd525b71526ca67fe471a34585f4768.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:108
 * @route '/shop/payroll'
 */
storeddd525b71526ca67fe471a34585f4768.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeddd525b71526ca67fe471a34585f4768.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:108
 * @route '/shop/payroll'
 */
    const storeddd525b71526ca67fe471a34585f4768Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeddd525b71526ca67fe471a34585f4768.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:108
 * @route '/shop/payroll'
 */
        storeddd525b71526ca67fe471a34585f4768Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeddd525b71526ca67fe471a34585f4768.url(options),
            method: 'post',
        })
    
    storeddd525b71526ca67fe471a34585f4768.form = storeddd525b71526ca67fe471a34585f4768Form
    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:108
 * @route '/staff/payroll'
 */
const storef2aa4538e4801c2f4f9d89889e13c966 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storef2aa4538e4801c2f4f9d89889e13c966.url(options),
    method: 'post',
})

storef2aa4538e4801c2f4f9d89889e13c966.definition = {
    methods: ["post"],
    url: '/staff/payroll',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:108
 * @route '/staff/payroll'
 */
storef2aa4538e4801c2f4f9d89889e13c966.url = (options?: RouteQueryOptions) => {
    return storef2aa4538e4801c2f4f9d89889e13c966.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:108
 * @route '/staff/payroll'
 */
storef2aa4538e4801c2f4f9d89889e13c966.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storef2aa4538e4801c2f4f9d89889e13c966.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:108
 * @route '/staff/payroll'
 */
    const storef2aa4538e4801c2f4f9d89889e13c966Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storef2aa4538e4801c2f4f9d89889e13c966.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::store
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:108
 * @route '/staff/payroll'
 */
        storef2aa4538e4801c2f4f9d89889e13c966Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storef2aa4538e4801c2f4f9d89889e13c966.url(options),
            method: 'post',
        })
    
    storef2aa4538e4801c2f4f9d89889e13c966.form = storef2aa4538e4801c2f4f9d89889e13c966Form

export const store = {
    '/shop/payroll': storeddd525b71526ca67fe471a34585f4768,
    '/staff/payroll': storef2aa4538e4801c2f4f9d89889e13c966,
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateSettings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/shop/payroll/settings'
 */
const updateSettings924c520ec3c706584158d10e538955ef = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateSettings924c520ec3c706584158d10e538955ef.url(options),
    method: 'put',
})

updateSettings924c520ec3c706584158d10e538955ef.definition = {
    methods: ["put"],
    url: '/shop/payroll/settings',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateSettings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/shop/payroll/settings'
 */
updateSettings924c520ec3c706584158d10e538955ef.url = (options?: RouteQueryOptions) => {
    return updateSettings924c520ec3c706584158d10e538955ef.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateSettings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/shop/payroll/settings'
 */
updateSettings924c520ec3c706584158d10e538955ef.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateSettings924c520ec3c706584158d10e538955ef.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateSettings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/shop/payroll/settings'
 */
    const updateSettings924c520ec3c706584158d10e538955efForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateSettings924c520ec3c706584158d10e538955ef.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateSettings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/shop/payroll/settings'
 */
        updateSettings924c520ec3c706584158d10e538955efForm.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateSettings924c520ec3c706584158d10e538955ef.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateSettings924c520ec3c706584158d10e538955ef.form = updateSettings924c520ec3c706584158d10e538955efForm
    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateSettings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/staff/payroll/settings'
 */
const updateSettings910c31f6bbc3160588b38e37259924c6 = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateSettings910c31f6bbc3160588b38e37259924c6.url(options),
    method: 'put',
})

updateSettings910c31f6bbc3160588b38e37259924c6.definition = {
    methods: ["put"],
    url: '/staff/payroll/settings',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateSettings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/staff/payroll/settings'
 */
updateSettings910c31f6bbc3160588b38e37259924c6.url = (options?: RouteQueryOptions) => {
    return updateSettings910c31f6bbc3160588b38e37259924c6.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateSettings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/staff/payroll/settings'
 */
updateSettings910c31f6bbc3160588b38e37259924c6.put = (options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateSettings910c31f6bbc3160588b38e37259924c6.url(options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateSettings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/staff/payroll/settings'
 */
    const updateSettings910c31f6bbc3160588b38e37259924c6Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateSettings910c31f6bbc3160588b38e37259924c6.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateSettings
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:59
 * @route '/staff/payroll/settings'
 */
        updateSettings910c31f6bbc3160588b38e37259924c6Form.put = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateSettings910c31f6bbc3160588b38e37259924c6.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateSettings910c31f6bbc3160588b38e37259924c6.form = updateSettings910c31f6bbc3160588b38e37259924c6Form

export const updateSettings = {
    '/shop/payroll/settings': updateSettings924c520ec3c706584158d10e538955ef,
    '/staff/payroll/settings': updateSettings910c31f6bbc3160588b38e37259924c6,
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/shop/payroll/{payroll}'
 */
const show8461ea432174a5551c07a77ffc6e2b3d = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show8461ea432174a5551c07a77ffc6e2b3d.url(args, options),
    method: 'get',
})

show8461ea432174a5551c07a77ffc6e2b3d.definition = {
    methods: ["get","head"],
    url: '/shop/payroll/{payroll}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/shop/payroll/{payroll}'
 */
show8461ea432174a5551c07a77ffc6e2b3d.url = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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

    return show8461ea432174a5551c07a77ffc6e2b3d.definition.url
            .replace('{payroll}', parsedArgs.payroll.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/shop/payroll/{payroll}'
 */
show8461ea432174a5551c07a77ffc6e2b3d.get = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show8461ea432174a5551c07a77ffc6e2b3d.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/shop/payroll/{payroll}'
 */
show8461ea432174a5551c07a77ffc6e2b3d.head = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show8461ea432174a5551c07a77ffc6e2b3d.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/shop/payroll/{payroll}'
 */
    const show8461ea432174a5551c07a77ffc6e2b3dForm = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show8461ea432174a5551c07a77ffc6e2b3d.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/shop/payroll/{payroll}'
 */
        show8461ea432174a5551c07a77ffc6e2b3dForm.get = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show8461ea432174a5551c07a77ffc6e2b3d.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/shop/payroll/{payroll}'
 */
        show8461ea432174a5551c07a77ffc6e2b3dForm.head = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show8461ea432174a5551c07a77ffc6e2b3d.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show8461ea432174a5551c07a77ffc6e2b3d.form = show8461ea432174a5551c07a77ffc6e2b3dForm
    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/staff/payroll/{payroll}'
 */
const showaebd371d6c35e9295977e81fc40c7e2e = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showaebd371d6c35e9295977e81fc40c7e2e.url(args, options),
    method: 'get',
})

showaebd371d6c35e9295977e81fc40c7e2e.definition = {
    methods: ["get","head"],
    url: '/staff/payroll/{payroll}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/staff/payroll/{payroll}'
 */
showaebd371d6c35e9295977e81fc40c7e2e.url = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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

    return showaebd371d6c35e9295977e81fc40c7e2e.definition.url
            .replace('{payroll}', parsedArgs.payroll.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/staff/payroll/{payroll}'
 */
showaebd371d6c35e9295977e81fc40c7e2e.get = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showaebd371d6c35e9295977e81fc40c7e2e.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/staff/payroll/{payroll}'
 */
showaebd371d6c35e9295977e81fc40c7e2e.head = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showaebd371d6c35e9295977e81fc40c7e2e.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/staff/payroll/{payroll}'
 */
    const showaebd371d6c35e9295977e81fc40c7e2eForm = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showaebd371d6c35e9295977e81fc40c7e2e.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/staff/payroll/{payroll}'
 */
        showaebd371d6c35e9295977e81fc40c7e2eForm.get = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showaebd371d6c35e9295977e81fc40c7e2e.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::show
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:82
 * @route '/staff/payroll/{payroll}'
 */
        showaebd371d6c35e9295977e81fc40c7e2eForm.head = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showaebd371d6c35e9295977e81fc40c7e2e.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showaebd371d6c35e9295977e81fc40c7e2e.form = showaebd371d6c35e9295977e81fc40c7e2eForm

export const show = {
    '/shop/payroll/{payroll}': show8461ea432174a5551c07a77ffc6e2b3d,
    '/staff/payroll/{payroll}': showaebd371d6c35e9295977e81fc40c7e2e,
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateItem
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/shop/payroll/{payrollItem}/item'
 */
const updateItem587bebdd237de786e34e68acd2c3887f = (args: { payrollItem: string | { public_id: string } } | [payrollItem: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateItem587bebdd237de786e34e68acd2c3887f.url(args, options),
    method: 'put',
})

updateItem587bebdd237de786e34e68acd2c3887f.definition = {
    methods: ["put"],
    url: '/shop/payroll/{payrollItem}/item',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateItem
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/shop/payroll/{payrollItem}/item'
 */
updateItem587bebdd237de786e34e68acd2c3887f.url = (args: { payrollItem: string | { public_id: string } } | [payrollItem: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { payrollItem: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { payrollItem: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    payrollItem: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        payrollItem: typeof args.payrollItem === 'object'
                ? args.payrollItem.public_id
                : args.payrollItem,
                }

    return updateItem587bebdd237de786e34e68acd2c3887f.definition.url
            .replace('{payrollItem}', parsedArgs.payrollItem.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateItem
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/shop/payroll/{payrollItem}/item'
 */
updateItem587bebdd237de786e34e68acd2c3887f.put = (args: { payrollItem: string | { public_id: string } } | [payrollItem: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateItem587bebdd237de786e34e68acd2c3887f.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateItem
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/shop/payroll/{payrollItem}/item'
 */
    const updateItem587bebdd237de786e34e68acd2c3887fForm = (args: { payrollItem: string | { public_id: string } } | [payrollItem: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateItem587bebdd237de786e34e68acd2c3887f.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateItem
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/shop/payroll/{payrollItem}/item'
 */
        updateItem587bebdd237de786e34e68acd2c3887fForm.put = (args: { payrollItem: string | { public_id: string } } | [payrollItem: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateItem587bebdd237de786e34e68acd2c3887f.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateItem587bebdd237de786e34e68acd2c3887f.form = updateItem587bebdd237de786e34e68acd2c3887fForm
    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateItem
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/staff/payroll/{payrollItem}/item'
 */
const updateItemcfd10266ea987ef8465ead699f9fc1da = (args: { payrollItem: string | { public_id: string } } | [payrollItem: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateItemcfd10266ea987ef8465ead699f9fc1da.url(args, options),
    method: 'put',
})

updateItemcfd10266ea987ef8465ead699f9fc1da.definition = {
    methods: ["put"],
    url: '/staff/payroll/{payrollItem}/item',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateItem
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/staff/payroll/{payrollItem}/item'
 */
updateItemcfd10266ea987ef8465ead699f9fc1da.url = (args: { payrollItem: string | { public_id: string } } | [payrollItem: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { payrollItem: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { payrollItem: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    payrollItem: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        payrollItem: typeof args.payrollItem === 'object'
                ? args.payrollItem.public_id
                : args.payrollItem,
                }

    return updateItemcfd10266ea987ef8465ead699f9fc1da.definition.url
            .replace('{payrollItem}', parsedArgs.payrollItem.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateItem
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/staff/payroll/{payrollItem}/item'
 */
updateItemcfd10266ea987ef8465ead699f9fc1da.put = (args: { payrollItem: string | { public_id: string } } | [payrollItem: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateItemcfd10266ea987ef8465ead699f9fc1da.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateItem
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/staff/payroll/{payrollItem}/item'
 */
    const updateItemcfd10266ea987ef8465ead699f9fc1daForm = (args: { payrollItem: string | { public_id: string } } | [payrollItem: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateItemcfd10266ea987ef8465ead699f9fc1da.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::updateItem
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:127
 * @route '/staff/payroll/{payrollItem}/item'
 */
        updateItemcfd10266ea987ef8465ead699f9fc1daForm.put = (args: { payrollItem: string | { public_id: string } } | [payrollItem: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateItemcfd10266ea987ef8465ead699f9fc1da.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateItemcfd10266ea987ef8465ead699f9fc1da.form = updateItemcfd10266ea987ef8465ead699f9fc1daForm

export const updateItem = {
    '/shop/payroll/{payrollItem}/item': updateItem587bebdd237de786e34e68acd2c3887f,
    '/staff/payroll/{payrollItem}/item': updateItemcfd10266ea987ef8465ead699f9fc1da,
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:138
 * @route '/shop/payroll/{payroll}/recalculate'
 */
const recalculate747e84e8b22598afdc6f2429086ba963 = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recalculate747e84e8b22598afdc6f2429086ba963.url(args, options),
    method: 'post',
})

recalculate747e84e8b22598afdc6f2429086ba963.definition = {
    methods: ["post"],
    url: '/shop/payroll/{payroll}/recalculate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:138
 * @route '/shop/payroll/{payroll}/recalculate'
 */
recalculate747e84e8b22598afdc6f2429086ba963.url = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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

    return recalculate747e84e8b22598afdc6f2429086ba963.definition.url
            .replace('{payroll}', parsedArgs.payroll.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:138
 * @route '/shop/payroll/{payroll}/recalculate'
 */
recalculate747e84e8b22598afdc6f2429086ba963.post = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recalculate747e84e8b22598afdc6f2429086ba963.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:138
 * @route '/shop/payroll/{payroll}/recalculate'
 */
    const recalculate747e84e8b22598afdc6f2429086ba963Form = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: recalculate747e84e8b22598afdc6f2429086ba963.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:138
 * @route '/shop/payroll/{payroll}/recalculate'
 */
        recalculate747e84e8b22598afdc6f2429086ba963Form.post = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: recalculate747e84e8b22598afdc6f2429086ba963.url(args, options),
            method: 'post',
        })
    
    recalculate747e84e8b22598afdc6f2429086ba963.form = recalculate747e84e8b22598afdc6f2429086ba963Form
    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:138
 * @route '/staff/payroll/{payroll}/recalculate'
 */
const recalculatefd51c045274b219f4218e7f9cb8e4c2a = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recalculatefd51c045274b219f4218e7f9cb8e4c2a.url(args, options),
    method: 'post',
})

recalculatefd51c045274b219f4218e7f9cb8e4c2a.definition = {
    methods: ["post"],
    url: '/staff/payroll/{payroll}/recalculate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:138
 * @route '/staff/payroll/{payroll}/recalculate'
 */
recalculatefd51c045274b219f4218e7f9cb8e4c2a.url = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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

    return recalculatefd51c045274b219f4218e7f9cb8e4c2a.definition.url
            .replace('{payroll}', parsedArgs.payroll.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:138
 * @route '/staff/payroll/{payroll}/recalculate'
 */
recalculatefd51c045274b219f4218e7f9cb8e4c2a.post = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recalculatefd51c045274b219f4218e7f9cb8e4c2a.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:138
 * @route '/staff/payroll/{payroll}/recalculate'
 */
    const recalculatefd51c045274b219f4218e7f9cb8e4c2aForm = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: recalculatefd51c045274b219f4218e7f9cb8e4c2a.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::recalculate
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:138
 * @route '/staff/payroll/{payroll}/recalculate'
 */
        recalculatefd51c045274b219f4218e7f9cb8e4c2aForm.post = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: recalculatefd51c045274b219f4218e7f9cb8e4c2a.url(args, options),
            method: 'post',
        })
    
    recalculatefd51c045274b219f4218e7f9cb8e4c2a.form = recalculatefd51c045274b219f4218e7f9cb8e4c2aForm

export const recalculate = {
    '/shop/payroll/{payroll}/recalculate': recalculate747e84e8b22598afdc6f2429086ba963,
    '/staff/payroll/{payroll}/recalculate': recalculatefd51c045274b219f4218e7f9cb8e4c2a,
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:149
 * @route '/shop/payroll/{payroll}/finalize'
 */
const finalize3dae19da9221437178f207cd20ebbe34 = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finalize3dae19da9221437178f207cd20ebbe34.url(args, options),
    method: 'post',
})

finalize3dae19da9221437178f207cd20ebbe34.definition = {
    methods: ["post"],
    url: '/shop/payroll/{payroll}/finalize',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:149
 * @route '/shop/payroll/{payroll}/finalize'
 */
finalize3dae19da9221437178f207cd20ebbe34.url = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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

    return finalize3dae19da9221437178f207cd20ebbe34.definition.url
            .replace('{payroll}', parsedArgs.payroll.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:149
 * @route '/shop/payroll/{payroll}/finalize'
 */
finalize3dae19da9221437178f207cd20ebbe34.post = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finalize3dae19da9221437178f207cd20ebbe34.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:149
 * @route '/shop/payroll/{payroll}/finalize'
 */
    const finalize3dae19da9221437178f207cd20ebbe34Form = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: finalize3dae19da9221437178f207cd20ebbe34.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:149
 * @route '/shop/payroll/{payroll}/finalize'
 */
        finalize3dae19da9221437178f207cd20ebbe34Form.post = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: finalize3dae19da9221437178f207cd20ebbe34.url(args, options),
            method: 'post',
        })
    
    finalize3dae19da9221437178f207cd20ebbe34.form = finalize3dae19da9221437178f207cd20ebbe34Form
    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:149
 * @route '/staff/payroll/{payroll}/finalize'
 */
const finalize5fc387858a315086365f60c5e6cb18b3 = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finalize5fc387858a315086365f60c5e6cb18b3.url(args, options),
    method: 'post',
})

finalize5fc387858a315086365f60c5e6cb18b3.definition = {
    methods: ["post"],
    url: '/staff/payroll/{payroll}/finalize',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:149
 * @route '/staff/payroll/{payroll}/finalize'
 */
finalize5fc387858a315086365f60c5e6cb18b3.url = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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

    return finalize5fc387858a315086365f60c5e6cb18b3.definition.url
            .replace('{payroll}', parsedArgs.payroll.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:149
 * @route '/staff/payroll/{payroll}/finalize'
 */
finalize5fc387858a315086365f60c5e6cb18b3.post = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: finalize5fc387858a315086365f60c5e6cb18b3.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:149
 * @route '/staff/payroll/{payroll}/finalize'
 */
    const finalize5fc387858a315086365f60c5e6cb18b3Form = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: finalize5fc387858a315086365f60c5e6cb18b3.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::finalize
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:149
 * @route '/staff/payroll/{payroll}/finalize'
 */
        finalize5fc387858a315086365f60c5e6cb18b3Form.post = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: finalize5fc387858a315086365f60c5e6cb18b3.url(args, options),
            method: 'post',
        })
    
    finalize5fc387858a315086365f60c5e6cb18b3.form = finalize5fc387858a315086365f60c5e6cb18b3Form

export const finalize = {
    '/shop/payroll/{payroll}/finalize': finalize3dae19da9221437178f207cd20ebbe34,
    '/staff/payroll/{payroll}/finalize': finalize5fc387858a315086365f60c5e6cb18b3,
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:160
 * @route '/shop/payroll/{payroll}'
 */
const destroy8461ea432174a5551c07a77ffc6e2b3d = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy8461ea432174a5551c07a77ffc6e2b3d.url(args, options),
    method: 'delete',
})

destroy8461ea432174a5551c07a77ffc6e2b3d.definition = {
    methods: ["delete"],
    url: '/shop/payroll/{payroll}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:160
 * @route '/shop/payroll/{payroll}'
 */
destroy8461ea432174a5551c07a77ffc6e2b3d.url = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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

    return destroy8461ea432174a5551c07a77ffc6e2b3d.definition.url
            .replace('{payroll}', parsedArgs.payroll.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:160
 * @route '/shop/payroll/{payroll}'
 */
destroy8461ea432174a5551c07a77ffc6e2b3d.delete = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy8461ea432174a5551c07a77ffc6e2b3d.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:160
 * @route '/shop/payroll/{payroll}'
 */
    const destroy8461ea432174a5551c07a77ffc6e2b3dForm = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy8461ea432174a5551c07a77ffc6e2b3d.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:160
 * @route '/shop/payroll/{payroll}'
 */
        destroy8461ea432174a5551c07a77ffc6e2b3dForm.delete = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy8461ea432174a5551c07a77ffc6e2b3d.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy8461ea432174a5551c07a77ffc6e2b3d.form = destroy8461ea432174a5551c07a77ffc6e2b3dForm
    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:160
 * @route '/staff/payroll/{payroll}'
 */
const destroyaebd371d6c35e9295977e81fc40c7e2e = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyaebd371d6c35e9295977e81fc40c7e2e.url(args, options),
    method: 'delete',
})

destroyaebd371d6c35e9295977e81fc40c7e2e.definition = {
    methods: ["delete"],
    url: '/staff/payroll/{payroll}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:160
 * @route '/staff/payroll/{payroll}'
 */
destroyaebd371d6c35e9295977e81fc40c7e2e.url = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
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

    return destroyaebd371d6c35e9295977e81fc40c7e2e.definition.url
            .replace('{payroll}', parsedArgs.payroll.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:160
 * @route '/staff/payroll/{payroll}'
 */
destroyaebd371d6c35e9295977e81fc40c7e2e.delete = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyaebd371d6c35e9295977e81fc40c7e2e.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:160
 * @route '/staff/payroll/{payroll}'
 */
    const destroyaebd371d6c35e9295977e81fc40c7e2eForm = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyaebd371d6c35e9295977e81fc40c7e2e.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PayrollController::destroy
 * @see app/Http/Controllers/Shop/Employee/PayrollController.php:160
 * @route '/staff/payroll/{payroll}'
 */
        destroyaebd371d6c35e9295977e81fc40c7e2eForm.delete = (args: { payroll: string | { public_id: string } } | [payroll: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyaebd371d6c35e9295977e81fc40c7e2e.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyaebd371d6c35e9295977e81fc40c7e2e.form = destroyaebd371d6c35e9295977e81fc40c7e2eForm

export const destroy = {
    '/shop/payroll/{payroll}': destroy8461ea432174a5551c07a77ffc6e2b3d,
    '/staff/payroll/{payroll}': destroyaebd371d6c35e9295977e81fc40c7e2e,
}

const PayrollController = { index, store, updateSettings, show, updateItem, recalculate, finalize, destroy }

export default PayrollController