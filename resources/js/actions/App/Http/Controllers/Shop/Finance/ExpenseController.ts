import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/shop/finance/expenses/archive'
 */
const archiveb71b21fa9584b56f9d64c53288db0fd0 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archiveb71b21fa9584b56f9d64c53288db0fd0.url(options),
    method: 'get',
})

archiveb71b21fa9584b56f9d64c53288db0fd0.definition = {
    methods: ["get","head"],
    url: '/shop/finance/expenses/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/shop/finance/expenses/archive'
 */
archiveb71b21fa9584b56f9d64c53288db0fd0.url = (options?: RouteQueryOptions) => {
    return archiveb71b21fa9584b56f9d64c53288db0fd0.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/shop/finance/expenses/archive'
 */
archiveb71b21fa9584b56f9d64c53288db0fd0.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archiveb71b21fa9584b56f9d64c53288db0fd0.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/shop/finance/expenses/archive'
 */
archiveb71b21fa9584b56f9d64c53288db0fd0.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archiveb71b21fa9584b56f9d64c53288db0fd0.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/shop/finance/expenses/archive'
 */
    const archiveb71b21fa9584b56f9d64c53288db0fd0Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: archiveb71b21fa9584b56f9d64c53288db0fd0.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/shop/finance/expenses/archive'
 */
        archiveb71b21fa9584b56f9d64c53288db0fd0Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archiveb71b21fa9584b56f9d64c53288db0fd0.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/shop/finance/expenses/archive'
 */
        archiveb71b21fa9584b56f9d64c53288db0fd0Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archiveb71b21fa9584b56f9d64c53288db0fd0.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    archiveb71b21fa9584b56f9d64c53288db0fd0.form = archiveb71b21fa9584b56f9d64c53288db0fd0Form
    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/staff/finance/expenses/archive'
 */
const archiveed89e6c713086d3217402199d84dfe22 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archiveed89e6c713086d3217402199d84dfe22.url(options),
    method: 'get',
})

archiveed89e6c713086d3217402199d84dfe22.definition = {
    methods: ["get","head"],
    url: '/staff/finance/expenses/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/staff/finance/expenses/archive'
 */
archiveed89e6c713086d3217402199d84dfe22.url = (options?: RouteQueryOptions) => {
    return archiveed89e6c713086d3217402199d84dfe22.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/staff/finance/expenses/archive'
 */
archiveed89e6c713086d3217402199d84dfe22.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archiveed89e6c713086d3217402199d84dfe22.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/staff/finance/expenses/archive'
 */
archiveed89e6c713086d3217402199d84dfe22.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archiveed89e6c713086d3217402199d84dfe22.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/staff/finance/expenses/archive'
 */
    const archiveed89e6c713086d3217402199d84dfe22Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: archiveed89e6c713086d3217402199d84dfe22.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/staff/finance/expenses/archive'
 */
        archiveed89e6c713086d3217402199d84dfe22Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archiveed89e6c713086d3217402199d84dfe22.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/staff/finance/expenses/archive'
 */
        archiveed89e6c713086d3217402199d84dfe22Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archiveed89e6c713086d3217402199d84dfe22.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    archiveed89e6c713086d3217402199d84dfe22.form = archiveed89e6c713086d3217402199d84dfe22Form

export const archive = {
    '/shop/finance/expenses/archive': archiveb71b21fa9584b56f9d64c53288db0fd0,
    '/staff/finance/expenses/archive': archiveed89e6c713086d3217402199d84dfe22,
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/shop/finance/expenses'
 */
const indexe1f5df15dce6955d208e6e55cc431576 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexe1f5df15dce6955d208e6e55cc431576.url(options),
    method: 'get',
})

indexe1f5df15dce6955d208e6e55cc431576.definition = {
    methods: ["get","head"],
    url: '/shop/finance/expenses',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/shop/finance/expenses'
 */
indexe1f5df15dce6955d208e6e55cc431576.url = (options?: RouteQueryOptions) => {
    return indexe1f5df15dce6955d208e6e55cc431576.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/shop/finance/expenses'
 */
indexe1f5df15dce6955d208e6e55cc431576.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexe1f5df15dce6955d208e6e55cc431576.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/shop/finance/expenses'
 */
indexe1f5df15dce6955d208e6e55cc431576.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexe1f5df15dce6955d208e6e55cc431576.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/shop/finance/expenses'
 */
    const indexe1f5df15dce6955d208e6e55cc431576Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexe1f5df15dce6955d208e6e55cc431576.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/shop/finance/expenses'
 */
        indexe1f5df15dce6955d208e6e55cc431576Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexe1f5df15dce6955d208e6e55cc431576.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/shop/finance/expenses'
 */
        indexe1f5df15dce6955d208e6e55cc431576Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexe1f5df15dce6955d208e6e55cc431576.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexe1f5df15dce6955d208e6e55cc431576.form = indexe1f5df15dce6955d208e6e55cc431576Form
    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/staff/finance/expenses'
 */
const index1d34d1f73e4cbe3544919fd9db59e00c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index1d34d1f73e4cbe3544919fd9db59e00c.url(options),
    method: 'get',
})

index1d34d1f73e4cbe3544919fd9db59e00c.definition = {
    methods: ["get","head"],
    url: '/staff/finance/expenses',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/staff/finance/expenses'
 */
index1d34d1f73e4cbe3544919fd9db59e00c.url = (options?: RouteQueryOptions) => {
    return index1d34d1f73e4cbe3544919fd9db59e00c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/staff/finance/expenses'
 */
index1d34d1f73e4cbe3544919fd9db59e00c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index1d34d1f73e4cbe3544919fd9db59e00c.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/staff/finance/expenses'
 */
index1d34d1f73e4cbe3544919fd9db59e00c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index1d34d1f73e4cbe3544919fd9db59e00c.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/staff/finance/expenses'
 */
    const index1d34d1f73e4cbe3544919fd9db59e00cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index1d34d1f73e4cbe3544919fd9db59e00c.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/staff/finance/expenses'
 */
        index1d34d1f73e4cbe3544919fd9db59e00cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index1d34d1f73e4cbe3544919fd9db59e00c.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/staff/finance/expenses'
 */
        index1d34d1f73e4cbe3544919fd9db59e00cForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index1d34d1f73e4cbe3544919fd9db59e00c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index1d34d1f73e4cbe3544919fd9db59e00c.form = index1d34d1f73e4cbe3544919fd9db59e00cForm

export const index = {
    '/shop/finance/expenses': indexe1f5df15dce6955d208e6e55cc431576,
    '/staff/finance/expenses': index1d34d1f73e4cbe3544919fd9db59e00c,
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/shop/finance/expenses'
 */
const storee1f5df15dce6955d208e6e55cc431576 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storee1f5df15dce6955d208e6e55cc431576.url(options),
    method: 'post',
})

storee1f5df15dce6955d208e6e55cc431576.definition = {
    methods: ["post"],
    url: '/shop/finance/expenses',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/shop/finance/expenses'
 */
storee1f5df15dce6955d208e6e55cc431576.url = (options?: RouteQueryOptions) => {
    return storee1f5df15dce6955d208e6e55cc431576.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/shop/finance/expenses'
 */
storee1f5df15dce6955d208e6e55cc431576.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storee1f5df15dce6955d208e6e55cc431576.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/shop/finance/expenses'
 */
    const storee1f5df15dce6955d208e6e55cc431576Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storee1f5df15dce6955d208e6e55cc431576.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/shop/finance/expenses'
 */
        storee1f5df15dce6955d208e6e55cc431576Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storee1f5df15dce6955d208e6e55cc431576.url(options),
            method: 'post',
        })
    
    storee1f5df15dce6955d208e6e55cc431576.form = storee1f5df15dce6955d208e6e55cc431576Form
    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/staff/finance/expenses'
 */
const store1d34d1f73e4cbe3544919fd9db59e00c = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store1d34d1f73e4cbe3544919fd9db59e00c.url(options),
    method: 'post',
})

store1d34d1f73e4cbe3544919fd9db59e00c.definition = {
    methods: ["post"],
    url: '/staff/finance/expenses',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/staff/finance/expenses'
 */
store1d34d1f73e4cbe3544919fd9db59e00c.url = (options?: RouteQueryOptions) => {
    return store1d34d1f73e4cbe3544919fd9db59e00c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/staff/finance/expenses'
 */
store1d34d1f73e4cbe3544919fd9db59e00c.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store1d34d1f73e4cbe3544919fd9db59e00c.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/staff/finance/expenses'
 */
    const store1d34d1f73e4cbe3544919fd9db59e00cForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store1d34d1f73e4cbe3544919fd9db59e00c.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/staff/finance/expenses'
 */
        store1d34d1f73e4cbe3544919fd9db59e00cForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store1d34d1f73e4cbe3544919fd9db59e00c.url(options),
            method: 'post',
        })
    
    store1d34d1f73e4cbe3544919fd9db59e00c.form = store1d34d1f73e4cbe3544919fd9db59e00cForm

export const store = {
    '/shop/finance/expenses': storee1f5df15dce6955d208e6e55cc431576,
    '/staff/finance/expenses': store1d34d1f73e4cbe3544919fd9db59e00c,
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/shop/finance/expenses/{expense}'
 */
const updatef4fa2f469553ee471c6e52cd19bbbe79 = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatef4fa2f469553ee471c6e52cd19bbbe79.url(args, options),
    method: 'put',
})

updatef4fa2f469553ee471c6e52cd19bbbe79.definition = {
    methods: ["put"],
    url: '/shop/finance/expenses/{expense}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/shop/finance/expenses/{expense}'
 */
updatef4fa2f469553ee471c6e52cd19bbbe79.url = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { expense: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { expense: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    expense: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        expense: typeof args.expense === 'object'
                ? args.expense.id
                : args.expense,
                }

    return updatef4fa2f469553ee471c6e52cd19bbbe79.definition.url
            .replace('{expense}', parsedArgs.expense.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/shop/finance/expenses/{expense}'
 */
updatef4fa2f469553ee471c6e52cd19bbbe79.put = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatef4fa2f469553ee471c6e52cd19bbbe79.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/shop/finance/expenses/{expense}'
 */
    const updatef4fa2f469553ee471c6e52cd19bbbe79Form = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updatef4fa2f469553ee471c6e52cd19bbbe79.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/shop/finance/expenses/{expense}'
 */
        updatef4fa2f469553ee471c6e52cd19bbbe79Form.put = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updatef4fa2f469553ee471c6e52cd19bbbe79.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updatef4fa2f469553ee471c6e52cd19bbbe79.form = updatef4fa2f469553ee471c6e52cd19bbbe79Form
    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/staff/finance/expenses/{expense}'
 */
const update8dbe08ed7ad71707d6675635051b7b87 = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update8dbe08ed7ad71707d6675635051b7b87.url(args, options),
    method: 'put',
})

update8dbe08ed7ad71707d6675635051b7b87.definition = {
    methods: ["put"],
    url: '/staff/finance/expenses/{expense}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/staff/finance/expenses/{expense}'
 */
update8dbe08ed7ad71707d6675635051b7b87.url = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { expense: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { expense: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    expense: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        expense: typeof args.expense === 'object'
                ? args.expense.id
                : args.expense,
                }

    return update8dbe08ed7ad71707d6675635051b7b87.definition.url
            .replace('{expense}', parsedArgs.expense.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/staff/finance/expenses/{expense}'
 */
update8dbe08ed7ad71707d6675635051b7b87.put = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update8dbe08ed7ad71707d6675635051b7b87.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/staff/finance/expenses/{expense}'
 */
    const update8dbe08ed7ad71707d6675635051b7b87Form = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update8dbe08ed7ad71707d6675635051b7b87.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/staff/finance/expenses/{expense}'
 */
        update8dbe08ed7ad71707d6675635051b7b87Form.put = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update8dbe08ed7ad71707d6675635051b7b87.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update8dbe08ed7ad71707d6675635051b7b87.form = update8dbe08ed7ad71707d6675635051b7b87Form

export const update = {
    '/shop/finance/expenses/{expense}': updatef4fa2f469553ee471c6e52cd19bbbe79,
    '/staff/finance/expenses/{expense}': update8dbe08ed7ad71707d6675635051b7b87,
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/shop/finance/expenses/{expense}'
 */
const destroyf4fa2f469553ee471c6e52cd19bbbe79 = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyf4fa2f469553ee471c6e52cd19bbbe79.url(args, options),
    method: 'delete',
})

destroyf4fa2f469553ee471c6e52cd19bbbe79.definition = {
    methods: ["delete"],
    url: '/shop/finance/expenses/{expense}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/shop/finance/expenses/{expense}'
 */
destroyf4fa2f469553ee471c6e52cd19bbbe79.url = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { expense: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { expense: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    expense: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        expense: typeof args.expense === 'object'
                ? args.expense.id
                : args.expense,
                }

    return destroyf4fa2f469553ee471c6e52cd19bbbe79.definition.url
            .replace('{expense}', parsedArgs.expense.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/shop/finance/expenses/{expense}'
 */
destroyf4fa2f469553ee471c6e52cd19bbbe79.delete = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyf4fa2f469553ee471c6e52cd19bbbe79.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/shop/finance/expenses/{expense}'
 */
    const destroyf4fa2f469553ee471c6e52cd19bbbe79Form = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyf4fa2f469553ee471c6e52cd19bbbe79.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/shop/finance/expenses/{expense}'
 */
        destroyf4fa2f469553ee471c6e52cd19bbbe79Form.delete = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyf4fa2f469553ee471c6e52cd19bbbe79.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyf4fa2f469553ee471c6e52cd19bbbe79.form = destroyf4fa2f469553ee471c6e52cd19bbbe79Form
    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/staff/finance/expenses/{expense}'
 */
const destroy8dbe08ed7ad71707d6675635051b7b87 = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy8dbe08ed7ad71707d6675635051b7b87.url(args, options),
    method: 'delete',
})

destroy8dbe08ed7ad71707d6675635051b7b87.definition = {
    methods: ["delete"],
    url: '/staff/finance/expenses/{expense}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/staff/finance/expenses/{expense}'
 */
destroy8dbe08ed7ad71707d6675635051b7b87.url = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { expense: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { expense: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    expense: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        expense: typeof args.expense === 'object'
                ? args.expense.id
                : args.expense,
                }

    return destroy8dbe08ed7ad71707d6675635051b7b87.definition.url
            .replace('{expense}', parsedArgs.expense.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/staff/finance/expenses/{expense}'
 */
destroy8dbe08ed7ad71707d6675635051b7b87.delete = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy8dbe08ed7ad71707d6675635051b7b87.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/staff/finance/expenses/{expense}'
 */
    const destroy8dbe08ed7ad71707d6675635051b7b87Form = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy8dbe08ed7ad71707d6675635051b7b87.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/staff/finance/expenses/{expense}'
 */
        destroy8dbe08ed7ad71707d6675635051b7b87Form.delete = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy8dbe08ed7ad71707d6675635051b7b87.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy8dbe08ed7ad71707d6675635051b7b87.form = destroy8dbe08ed7ad71707d6675635051b7b87Form

export const destroy = {
    '/shop/finance/expenses/{expense}': destroyf4fa2f469553ee471c6e52cd19bbbe79,
    '/staff/finance/expenses/{expense}': destroy8dbe08ed7ad71707d6675635051b7b87,
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/shop/finance/expenses/{id}/restore'
 */
const restoree948a6a114baf36e8a42500a40b44ca8 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoree948a6a114baf36e8a42500a40b44ca8.url(args, options),
    method: 'post',
})

restoree948a6a114baf36e8a42500a40b44ca8.definition = {
    methods: ["post"],
    url: '/shop/finance/expenses/{id}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/shop/finance/expenses/{id}/restore'
 */
restoree948a6a114baf36e8a42500a40b44ca8.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return restoree948a6a114baf36e8a42500a40b44ca8.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/shop/finance/expenses/{id}/restore'
 */
restoree948a6a114baf36e8a42500a40b44ca8.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoree948a6a114baf36e8a42500a40b44ca8.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/shop/finance/expenses/{id}/restore'
 */
    const restoree948a6a114baf36e8a42500a40b44ca8Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restoree948a6a114baf36e8a42500a40b44ca8.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/shop/finance/expenses/{id}/restore'
 */
        restoree948a6a114baf36e8a42500a40b44ca8Form.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restoree948a6a114baf36e8a42500a40b44ca8.url(args, options),
            method: 'post',
        })
    
    restoree948a6a114baf36e8a42500a40b44ca8.form = restoree948a6a114baf36e8a42500a40b44ca8Form
    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/staff/finance/expenses/{id}/restore'
 */
const restoreef269ad88bab44b75d76e2e9b569f69b = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreef269ad88bab44b75d76e2e9b569f69b.url(args, options),
    method: 'post',
})

restoreef269ad88bab44b75d76e2e9b569f69b.definition = {
    methods: ["post"],
    url: '/staff/finance/expenses/{id}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/staff/finance/expenses/{id}/restore'
 */
restoreef269ad88bab44b75d76e2e9b569f69b.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return restoreef269ad88bab44b75d76e2e9b569f69b.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/staff/finance/expenses/{id}/restore'
 */
restoreef269ad88bab44b75d76e2e9b569f69b.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreef269ad88bab44b75d76e2e9b569f69b.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/staff/finance/expenses/{id}/restore'
 */
    const restoreef269ad88bab44b75d76e2e9b569f69bForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restoreef269ad88bab44b75d76e2e9b569f69b.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/staff/finance/expenses/{id}/restore'
 */
        restoreef269ad88bab44b75d76e2e9b569f69bForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restoreef269ad88bab44b75d76e2e9b569f69b.url(args, options),
            method: 'post',
        })
    
    restoreef269ad88bab44b75d76e2e9b569f69b.form = restoreef269ad88bab44b75d76e2e9b569f69bForm

export const restore = {
    '/shop/finance/expenses/{id}/restore': restoree948a6a114baf36e8a42500a40b44ca8,
    '/staff/finance/expenses/{id}/restore': restoreef269ad88bab44b75d76e2e9b569f69b,
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::storeCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/shop/finance/categories'
 */
const storeCategory4a60445694f863bd8fe509711eca0452 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCategory4a60445694f863bd8fe509711eca0452.url(options),
    method: 'post',
})

storeCategory4a60445694f863bd8fe509711eca0452.definition = {
    methods: ["post"],
    url: '/shop/finance/categories',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::storeCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/shop/finance/categories'
 */
storeCategory4a60445694f863bd8fe509711eca0452.url = (options?: RouteQueryOptions) => {
    return storeCategory4a60445694f863bd8fe509711eca0452.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::storeCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/shop/finance/categories'
 */
storeCategory4a60445694f863bd8fe509711eca0452.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCategory4a60445694f863bd8fe509711eca0452.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::storeCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/shop/finance/categories'
 */
    const storeCategory4a60445694f863bd8fe509711eca0452Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeCategory4a60445694f863bd8fe509711eca0452.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::storeCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/shop/finance/categories'
 */
        storeCategory4a60445694f863bd8fe509711eca0452Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeCategory4a60445694f863bd8fe509711eca0452.url(options),
            method: 'post',
        })
    
    storeCategory4a60445694f863bd8fe509711eca0452.form = storeCategory4a60445694f863bd8fe509711eca0452Form
    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::storeCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/staff/finance/categories'
 */
const storeCategory8d240a6c91a50e4aaecf714e97aaee82 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCategory8d240a6c91a50e4aaecf714e97aaee82.url(options),
    method: 'post',
})

storeCategory8d240a6c91a50e4aaecf714e97aaee82.definition = {
    methods: ["post"],
    url: '/staff/finance/categories',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::storeCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/staff/finance/categories'
 */
storeCategory8d240a6c91a50e4aaecf714e97aaee82.url = (options?: RouteQueryOptions) => {
    return storeCategory8d240a6c91a50e4aaecf714e97aaee82.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::storeCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/staff/finance/categories'
 */
storeCategory8d240a6c91a50e4aaecf714e97aaee82.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeCategory8d240a6c91a50e4aaecf714e97aaee82.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::storeCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/staff/finance/categories'
 */
    const storeCategory8d240a6c91a50e4aaecf714e97aaee82Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storeCategory8d240a6c91a50e4aaecf714e97aaee82.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::storeCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/staff/finance/categories'
 */
        storeCategory8d240a6c91a50e4aaecf714e97aaee82Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storeCategory8d240a6c91a50e4aaecf714e97aaee82.url(options),
            method: 'post',
        })
    
    storeCategory8d240a6c91a50e4aaecf714e97aaee82.form = storeCategory8d240a6c91a50e4aaecf714e97aaee82Form

export const storeCategory = {
    '/shop/finance/categories': storeCategory4a60445694f863bd8fe509711eca0452,
    '/staff/finance/categories': storeCategory8d240a6c91a50e4aaecf714e97aaee82,
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroyCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/shop/finance/categories/{category}'
 */
const destroyCategory31181b5d6883b6ed6de5f524a009a977 = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyCategory31181b5d6883b6ed6de5f524a009a977.url(args, options),
    method: 'delete',
})

destroyCategory31181b5d6883b6ed6de5f524a009a977.definition = {
    methods: ["delete"],
    url: '/shop/finance/categories/{category}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroyCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/shop/finance/categories/{category}'
 */
destroyCategory31181b5d6883b6ed6de5f524a009a977.url = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: typeof args.category === 'object'
                ? args.category.id
                : args.category,
                }

    return destroyCategory31181b5d6883b6ed6de5f524a009a977.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroyCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/shop/finance/categories/{category}'
 */
destroyCategory31181b5d6883b6ed6de5f524a009a977.delete = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyCategory31181b5d6883b6ed6de5f524a009a977.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroyCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/shop/finance/categories/{category}'
 */
    const destroyCategory31181b5d6883b6ed6de5f524a009a977Form = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyCategory31181b5d6883b6ed6de5f524a009a977.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroyCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/shop/finance/categories/{category}'
 */
        destroyCategory31181b5d6883b6ed6de5f524a009a977Form.delete = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyCategory31181b5d6883b6ed6de5f524a009a977.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyCategory31181b5d6883b6ed6de5f524a009a977.form = destroyCategory31181b5d6883b6ed6de5f524a009a977Form
    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroyCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/staff/finance/categories/{category}'
 */
const destroyCategory38f448f8ee856b5b8e6a38e8e75d786f = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyCategory38f448f8ee856b5b8e6a38e8e75d786f.url(args, options),
    method: 'delete',
})

destroyCategory38f448f8ee856b5b8e6a38e8e75d786f.definition = {
    methods: ["delete"],
    url: '/staff/finance/categories/{category}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroyCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/staff/finance/categories/{category}'
 */
destroyCategory38f448f8ee856b5b8e6a38e8e75d786f.url = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { category: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: typeof args.category === 'object'
                ? args.category.id
                : args.category,
                }

    return destroyCategory38f448f8ee856b5b8e6a38e8e75d786f.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroyCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/staff/finance/categories/{category}'
 */
destroyCategory38f448f8ee856b5b8e6a38e8e75d786f.delete = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyCategory38f448f8ee856b5b8e6a38e8e75d786f.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroyCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/staff/finance/categories/{category}'
 */
    const destroyCategory38f448f8ee856b5b8e6a38e8e75d786fForm = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyCategory38f448f8ee856b5b8e6a38e8e75d786f.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroyCategory
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/staff/finance/categories/{category}'
 */
        destroyCategory38f448f8ee856b5b8e6a38e8e75d786fForm.delete = (args: { category: number | { id: number } } | [category: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyCategory38f448f8ee856b5b8e6a38e8e75d786f.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyCategory38f448f8ee856b5b8e6a38e8e75d786f.form = destroyCategory38f448f8ee856b5b8e6a38e8e75d786fForm

export const destroyCategory = {
    '/shop/finance/categories/{category}': destroyCategory31181b5d6883b6ed6de5f524a009a977,
    '/staff/finance/categories/{category}': destroyCategory38f448f8ee856b5b8e6a38e8e75d786f,
}

const ExpenseController = { archive, index, store, update, destroy, restore, storeCategory, destroyCategory }

export default ExpenseController