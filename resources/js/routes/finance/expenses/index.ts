import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/shop/finance/expenses/archive'
 */
export const archive = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})

archive.definition = {
    methods: ["get","head"],
    url: '/shop/finance/expenses/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/shop/finance/expenses/archive'
 */
archive.url = (options?: RouteQueryOptions) => {
    return archive.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/shop/finance/expenses/archive'
 */
archive.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/shop/finance/expenses/archive'
 */
archive.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archive.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/shop/finance/expenses/archive'
 */
    const archiveForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: archive.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/shop/finance/expenses/archive'
 */
        archiveForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archive.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/shop/finance/expenses/archive'
 */
        archiveForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archive.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    archive.form = archiveForm
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/shop/finance/expenses'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/finance/expenses',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/shop/finance/expenses'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/shop/finance/expenses'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/shop/finance/expenses'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/shop/finance/expenses'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/shop/finance/expenses'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/shop/finance/expenses'
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
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/shop/finance/expenses'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/shop/finance/expenses',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/shop/finance/expenses'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/shop/finance/expenses'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/shop/finance/expenses'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/shop/finance/expenses'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/shop/finance/expenses/{expense}'
 */
export const update = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/shop/finance/expenses/{expense}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/shop/finance/expenses/{expense}'
 */
update.url = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{expense}', parsedArgs.expense.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/shop/finance/expenses/{expense}'
 */
update.put = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/shop/finance/expenses/{expense}'
 */
    const updateForm = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
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
        updateForm.put = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/shop/finance/expenses/{expense}'
 */
export const destroy = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/shop/finance/expenses/{expense}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/shop/finance/expenses/{expense}'
 */
destroy.url = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{expense}', parsedArgs.expense.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/shop/finance/expenses/{expense}'
 */
destroy.delete = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/shop/finance/expenses/{expense}'
 */
    const destroyForm = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
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
        destroyForm.delete = (args: { expense: number | { id: number } } | [expense: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/shop/finance/expenses/{id}/restore'
 */
export const restore = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

restore.definition = {
    methods: ["post"],
    url: '/shop/finance/expenses/{id}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/shop/finance/expenses/{id}/restore'
 */
restore.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return restore.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/shop/finance/expenses/{id}/restore'
 */
restore.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/shop/finance/expenses/{id}/restore'
 */
    const restoreForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/shop/finance/expenses/{id}/restore'
 */
        restoreForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore.url(args, options),
            method: 'post',
        })
    
    restore.form = restoreForm
const expenses = {
    archive: Object.assign(archive, archive),
index: Object.assign(index, index),
store: Object.assign(store, store),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
restore: Object.assign(restore, restore),
}

export default expenses