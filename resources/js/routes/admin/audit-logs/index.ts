import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import archive from './archive'
/**
* @see \App\Http\Controllers\Admin\LoginLogController::index
 * @see app/Http/Controllers/Admin/LoginLogController.php:24
 * @route '/admin/audit-logs'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/audit-logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\LoginLogController::index
 * @see app/Http/Controllers/Admin/LoginLogController.php:24
 * @route '/admin/audit-logs'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LoginLogController::index
 * @see app/Http/Controllers/Admin/LoginLogController.php:24
 * @route '/admin/audit-logs'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\LoginLogController::index
 * @see app/Http/Controllers/Admin/LoginLogController.php:24
 * @route '/admin/audit-logs'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\LoginLogController::index
 * @see app/Http/Controllers/Admin/LoginLogController.php:24
 * @route '/admin/audit-logs'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\LoginLogController::index
 * @see app/Http/Controllers/Admin/LoginLogController.php:24
 * @route '/admin/audit-logs'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\LoginLogController::index
 * @see app/Http/Controllers/Admin/LoginLogController.php:24
 * @route '/admin/audit-logs'
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
* @see \App\Http\Controllers\Admin\LoginLogController::exportMethod
 * @see app/Http/Controllers/Admin/LoginLogController.php:32
 * @route '/admin/audit-logs/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/admin/audit-logs/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\LoginLogController::exportMethod
 * @see app/Http/Controllers/Admin/LoginLogController.php:32
 * @route '/admin/audit-logs/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LoginLogController::exportMethod
 * @see app/Http/Controllers/Admin/LoginLogController.php:32
 * @route '/admin/audit-logs/export'
 */
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\LoginLogController::exportMethod
 * @see app/Http/Controllers/Admin/LoginLogController.php:32
 * @route '/admin/audit-logs/export'
 */
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\LoginLogController::exportMethod
 * @see app/Http/Controllers/Admin/LoginLogController.php:32
 * @route '/admin/audit-logs/export'
 */
    const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMethod.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\LoginLogController::exportMethod
 * @see app/Http/Controllers/Admin/LoginLogController.php:32
 * @route '/admin/audit-logs/export'
 */
        exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\LoginLogController::exportMethod
 * @see app/Http/Controllers/Admin/LoginLogController.php:32
 * @route '/admin/audit-logs/export'
 */
        exportMethodForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportMethod.form = exportMethodForm
/**
* @see \App\Http\Controllers\Admin\LoginLogController::importMethod
 * @see app/Http/Controllers/Admin/LoginLogController.php:41
 * @route '/admin/audit-logs/import'
 */
export const importMethod = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

importMethod.definition = {
    methods: ["post"],
    url: '/admin/audit-logs/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\LoginLogController::importMethod
 * @see app/Http/Controllers/Admin/LoginLogController.php:41
 * @route '/admin/audit-logs/import'
 */
importMethod.url = (options?: RouteQueryOptions) => {
    return importMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LoginLogController::importMethod
 * @see app/Http/Controllers/Admin/LoginLogController.php:41
 * @route '/admin/audit-logs/import'
 */
importMethod.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\LoginLogController::importMethod
 * @see app/Http/Controllers/Admin/LoginLogController.php:41
 * @route '/admin/audit-logs/import'
 */
    const importMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: importMethod.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\LoginLogController::importMethod
 * @see app/Http/Controllers/Admin/LoginLogController.php:41
 * @route '/admin/audit-logs/import'
 */
        importMethodForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: importMethod.url(options),
            method: 'post',
        })
    
    importMethod.form = importMethodForm
/**
* @see \App\Http\Controllers\Admin\LoginLogController::bulkArchive
 * @see app/Http/Controllers/Admin/LoginLogController.php:72
 * @route '/admin/audit-logs/bulk-archive'
 */
export const bulkArchive = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkArchive.url(options),
    method: 'post',
})

bulkArchive.definition = {
    methods: ["post"],
    url: '/admin/audit-logs/bulk-archive',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\LoginLogController::bulkArchive
 * @see app/Http/Controllers/Admin/LoginLogController.php:72
 * @route '/admin/audit-logs/bulk-archive'
 */
bulkArchive.url = (options?: RouteQueryOptions) => {
    return bulkArchive.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LoginLogController::bulkArchive
 * @see app/Http/Controllers/Admin/LoginLogController.php:72
 * @route '/admin/audit-logs/bulk-archive'
 */
bulkArchive.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkArchive.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\LoginLogController::bulkArchive
 * @see app/Http/Controllers/Admin/LoginLogController.php:72
 * @route '/admin/audit-logs/bulk-archive'
 */
    const bulkArchiveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkArchive.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\LoginLogController::bulkArchive
 * @see app/Http/Controllers/Admin/LoginLogController.php:72
 * @route '/admin/audit-logs/bulk-archive'
 */
        bulkArchiveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkArchive.url(options),
            method: 'post',
        })
    
    bulkArchive.form = bulkArchiveForm
/**
* @see \App\Http\Controllers\Admin\LoginLogController::show
 * @see app/Http/Controllers/Admin/LoginLogController.php:55
 * @route '/admin/audit-logs/{category}/{id}'
 */
export const show = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/audit-logs/{category}/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\LoginLogController::show
 * @see app/Http/Controllers/Admin/LoginLogController.php:55
 * @route '/admin/audit-logs/{category}/{id}'
 */
show.url = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LoginLogController::show
 * @see app/Http/Controllers/Admin/LoginLogController.php:55
 * @route '/admin/audit-logs/{category}/{id}'
 */
show.get = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\LoginLogController::show
 * @see app/Http/Controllers/Admin/LoginLogController.php:55
 * @route '/admin/audit-logs/{category}/{id}'
 */
show.head = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\LoginLogController::show
 * @see app/Http/Controllers/Admin/LoginLogController.php:55
 * @route '/admin/audit-logs/{category}/{id}'
 */
    const showForm = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\LoginLogController::show
 * @see app/Http/Controllers/Admin/LoginLogController.php:55
 * @route '/admin/audit-logs/{category}/{id}'
 */
        showForm.get = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\LoginLogController::show
 * @see app/Http/Controllers/Admin/LoginLogController.php:55
 * @route '/admin/audit-logs/{category}/{id}'
 */
        showForm.head = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\LoginLogController::destroy
 * @see app/Http/Controllers/Admin/LoginLogController.php:62
 * @route '/admin/audit-logs/{category}/{id}'
 */
export const destroy = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/audit-logs/{category}/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\LoginLogController::destroy
 * @see app/Http/Controllers/Admin/LoginLogController.php:62
 * @route '/admin/audit-logs/{category}/{id}'
 */
destroy.url = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{category}', parsedArgs.category.toString())
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LoginLogController::destroy
 * @see app/Http/Controllers/Admin/LoginLogController.php:62
 * @route '/admin/audit-logs/{category}/{id}'
 */
destroy.delete = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\LoginLogController::destroy
 * @see app/Http/Controllers/Admin/LoginLogController.php:62
 * @route '/admin/audit-logs/{category}/{id}'
 */
    const destroyForm = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\LoginLogController::destroy
 * @see app/Http/Controllers/Admin/LoginLogController.php:62
 * @route '/admin/audit-logs/{category}/{id}'
 */
        destroyForm.delete = (args: { category: string | number, id: string | number } | [category: string | number, id: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const auditLogs = {
    index: Object.assign(index, index),
export: Object.assign(exportMethod, exportMethod),
import: Object.assign(importMethod, importMethod),
bulkArchive: Object.assign(bulkArchive, bulkArchive),
archive: Object.assign(archive, archive),
show: Object.assign(show, show),
destroy: Object.assign(destroy, destroy),
}

export default auditLogs