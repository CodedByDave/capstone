import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/staff/finance/expenses'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/staff/finance/expenses',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/staff/finance/expenses'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/staff/finance/expenses'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/staff/finance/expenses'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/staff/finance/expenses'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/staff/finance/expenses'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::index
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:38
 * @route '/staff/finance/expenses'
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
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/staff/finance/expenses/archive'
 */
export const archive = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})

archive.definition = {
    methods: ["get","head"],
    url: '/staff/finance/expenses/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/staff/finance/expenses/archive'
 */
archive.url = (options?: RouteQueryOptions) => {
    return archive.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/staff/finance/expenses/archive'
 */
archive.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/staff/finance/expenses/archive'
 */
archive.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archive.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/staff/finance/expenses/archive'
 */
    const archiveForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: archive.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/staff/finance/expenses/archive'
 */
        archiveForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archive.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::archive
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:51
 * @route '/staff/finance/expenses/archive'
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
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/staff/finance/expenses/{expense}'
 */
export const destroy = (args: { expense: string | { public_id: string } } | [expense: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/staff/finance/expenses/{expense}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/staff/finance/expenses/{expense}'
 */
destroy.url = (args: { expense: string | { public_id: string } } | [expense: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { expense: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { expense: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    expense: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        expense: typeof args.expense === 'object'
                ? args.expense.public_id
                : args.expense,
                }

    return destroy.definition.url
            .replace('{expense}', parsedArgs.expense.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/staff/finance/expenses/{expense}'
 */
destroy.delete = (args: { expense: string | { public_id: string } } | [expense: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:96
 * @route '/staff/finance/expenses/{expense}'
 */
    const destroyForm = (args: { expense: string | { public_id: string } } | [expense: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @route '/staff/finance/expenses/{expense}'
 */
        destroyForm.delete = (args: { expense: string | { public_id: string } } | [expense: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @route '/staff/finance/expenses/{publicId}/restore'
 */
export const restore = (args: { publicId: string | number } | [publicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

restore.definition = {
    methods: ["post"],
    url: '/staff/finance/expenses/{publicId}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/staff/finance/expenses/{publicId}/restore'
 */
restore.url = (args: { publicId: string | number } | [publicId: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { publicId: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    publicId: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        publicId: args.publicId,
                }

    return restore.definition.url
            .replace('{publicId}', parsedArgs.publicId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/staff/finance/expenses/{publicId}/restore'
 */
restore.post = (args: { publicId: string | number } | [publicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/staff/finance/expenses/{publicId}/restore'
 */
    const restoreForm = (args: { publicId: string | number } | [publicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::restore
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:106
 * @route '/staff/finance/expenses/{publicId}/restore'
 */
        restoreForm.post = (args: { publicId: string | number } | [publicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore.url(args, options),
            method: 'post',
        })
    
    restore.form = restoreForm
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/staff/finance/expenses'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/staff/finance/expenses',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/staff/finance/expenses'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/staff/finance/expenses'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/staff/finance/expenses'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:60
 * @route '/staff/finance/expenses'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/staff/finance/expenses/{expense}'
 */
export const update = (args: { expense: string | { public_id: string } } | [expense: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/staff/finance/expenses/{expense}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/staff/finance/expenses/{expense}'
 */
update.url = (args: { expense: string | { public_id: string } } | [expense: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { expense: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { expense: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    expense: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        expense: typeof args.expense === 'object'
                ? args.expense.public_id
                : args.expense,
                }

    return update.definition.url
            .replace('{expense}', parsedArgs.expense.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/staff/finance/expenses/{expense}'
 */
update.put = (args: { expense: string | { public_id: string } } | [expense: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::update
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:77
 * @route '/staff/finance/expenses/{expense}'
 */
    const updateForm = (args: { expense: string | { public_id: string } } | [expense: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @route '/staff/finance/expenses/{expense}'
 */
        updateForm.put = (args: { expense: string | { public_id: string } } | [expense: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
const expenses = {
    index: Object.assign(index, index),
archive: Object.assign(archive, archive),
destroy: Object.assign(destroy, destroy),
restore: Object.assign(restore, restore),
store: Object.assign(store, store),
update: Object.assign(update, update),
}

export default expenses