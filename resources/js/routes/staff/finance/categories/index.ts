import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/staff/finance/categories/{category}'
 */
export const destroy = (args: { category: string | { public_id: string } } | [category: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/staff/finance/categories/{category}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/staff/finance/categories/{category}'
 */
destroy.url = (args: { category: string | { public_id: string } } | [category: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { category: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { category: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: typeof args.category === 'object'
                ? args.category.public_id
                : args.category,
                }

    return destroy.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/staff/finance/categories/{category}'
 */
destroy.delete = (args: { category: string | { public_id: string } } | [category: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::destroy
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/staff/finance/categories/{category}'
 */
    const destroyForm = (args: { category: string | { public_id: string } } | [category: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:137
 * @route '/staff/finance/categories/{category}'
 */
        destroyForm.delete = (args: { category: string | { public_id: string } } | [category: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/staff/finance/categories'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/staff/finance/categories',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/staff/finance/categories'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/staff/finance/categories'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/staff/finance/categories'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\ExpenseController::store
 * @see app/Http/Controllers/Shop/Finance/ExpenseController.php:114
 * @route '/staff/finance/categories'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const categories = {
    destroy: Object.assign(destroy, destroy),
store: Object.assign(store, store),
}

export default categories