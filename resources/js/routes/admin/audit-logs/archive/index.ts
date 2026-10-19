import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\LoginLogController::index
 * @see app/Http/Controllers/Admin/LoginLogController.php:83
 * @route '/admin/audit-logs/archive'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/audit-logs/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\LoginLogController::index
 * @see app/Http/Controllers/Admin/LoginLogController.php:83
 * @route '/admin/audit-logs/archive'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LoginLogController::index
 * @see app/Http/Controllers/Admin/LoginLogController.php:83
 * @route '/admin/audit-logs/archive'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\LoginLogController::index
 * @see app/Http/Controllers/Admin/LoginLogController.php:83
 * @route '/admin/audit-logs/archive'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\LoginLogController::index
 * @see app/Http/Controllers/Admin/LoginLogController.php:83
 * @route '/admin/audit-logs/archive'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\LoginLogController::index
 * @see app/Http/Controllers/Admin/LoginLogController.php:83
 * @route '/admin/audit-logs/archive'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\LoginLogController::index
 * @see app/Http/Controllers/Admin/LoginLogController.php:83
 * @route '/admin/audit-logs/archive'
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
* @see \App\Http\Controllers\Admin\LoginLogController::restore
 * @see app/Http/Controllers/Admin/LoginLogController.php:91
 * @route '/admin/audit-logs/archive/{category}/{id}/restore'
 */
export const restore = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

restore.definition = {
    methods: ["post"],
    url: '/admin/audit-logs/archive/{category}/{id}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\LoginLogController::restore
 * @see app/Http/Controllers/Admin/LoginLogController.php:91
 * @route '/admin/audit-logs/archive/{category}/{id}/restore'
 */
restore.url = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                    id: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: args.category,
                                id: args.id,
                }

    return restore.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LoginLogController::restore
 * @see app/Http/Controllers/Admin/LoginLogController.php:91
 * @route '/admin/audit-logs/archive/{category}/{id}/restore'
 */
restore.post = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\LoginLogController::restore
 * @see app/Http/Controllers/Admin/LoginLogController.php:91
 * @route '/admin/audit-logs/archive/{category}/{id}/restore'
 */
    const restoreForm = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\LoginLogController::restore
 * @see app/Http/Controllers/Admin/LoginLogController.php:91
 * @route '/admin/audit-logs/archive/{category}/{id}/restore'
 */
        restoreForm.post = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore.url(args, options),
            method: 'post',
        })
    
    restore.form = restoreForm
/**
* @see \App\Http\Controllers\Admin\LoginLogController::bulkRestore
 * @see app/Http/Controllers/Admin/LoginLogController.php:101
 * @route '/admin/audit-logs/archive/bulk-restore'
 */
export const bulkRestore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkRestore.url(options),
    method: 'post',
})

bulkRestore.definition = {
    methods: ["post"],
    url: '/admin/audit-logs/archive/bulk-restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\LoginLogController::bulkRestore
 * @see app/Http/Controllers/Admin/LoginLogController.php:101
 * @route '/admin/audit-logs/archive/bulk-restore'
 */
bulkRestore.url = (options?: RouteQueryOptions) => {
    return bulkRestore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LoginLogController::bulkRestore
 * @see app/Http/Controllers/Admin/LoginLogController.php:101
 * @route '/admin/audit-logs/archive/bulk-restore'
 */
bulkRestore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkRestore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\LoginLogController::bulkRestore
 * @see app/Http/Controllers/Admin/LoginLogController.php:101
 * @route '/admin/audit-logs/archive/bulk-restore'
 */
    const bulkRestoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkRestore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\LoginLogController::bulkRestore
 * @see app/Http/Controllers/Admin/LoginLogController.php:101
 * @route '/admin/audit-logs/archive/bulk-restore'
 */
        bulkRestoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkRestore.url(options),
            method: 'post',
        })
    
    bulkRestore.form = bulkRestoreForm
/**
* @see \App\Http\Controllers\Admin\LoginLogController::forceDelete
 * @see app/Http/Controllers/Admin/LoginLogController.php:112
 * @route '/admin/audit-logs/archive/{category}/{id}'
 */
export const forceDelete = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: forceDelete.url(args, options),
    method: 'delete',
})

forceDelete.definition = {
    methods: ["delete"],
    url: '/admin/audit-logs/archive/{category}/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\LoginLogController::forceDelete
 * @see app/Http/Controllers/Admin/LoginLogController.php:112
 * @route '/admin/audit-logs/archive/{category}/{id}'
 */
forceDelete.url = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    category: args[0],
                    id: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        category: args.category,
                                id: args.id,
                }

    return forceDelete.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LoginLogController::forceDelete
 * @see app/Http/Controllers/Admin/LoginLogController.php:112
 * @route '/admin/audit-logs/archive/{category}/{id}'
 */
forceDelete.delete = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: forceDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\LoginLogController::forceDelete
 * @see app/Http/Controllers/Admin/LoginLogController.php:112
 * @route '/admin/audit-logs/archive/{category}/{id}'
 */
    const forceDeleteForm = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: forceDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\LoginLogController::forceDelete
 * @see app/Http/Controllers/Admin/LoginLogController.php:112
 * @route '/admin/audit-logs/archive/{category}/{id}'
 */
        forceDeleteForm.delete = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: forceDelete.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    forceDelete.form = forceDeleteForm
const archive = {
    index: Object.assign(index, index),
restore: Object.assign(restore, restore),
bulkRestore: Object.assign(bulkRestore, bulkRestore),
forceDelete: Object.assign(forceDelete, forceDelete),
}

export default archive