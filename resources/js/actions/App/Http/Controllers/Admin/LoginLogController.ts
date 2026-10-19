import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
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
* @see \App\Http\Controllers\Admin\LoginLogController::exportCsv
 * @see app/Http/Controllers/Admin/LoginLogController.php:32
 * @route '/admin/audit-logs/export'
 */
export const exportCsv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportCsv.url(options),
    method: 'get',
})

exportCsv.definition = {
    methods: ["get","head"],
    url: '/admin/audit-logs/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\LoginLogController::exportCsv
 * @see app/Http/Controllers/Admin/LoginLogController.php:32
 * @route '/admin/audit-logs/export'
 */
exportCsv.url = (options?: RouteQueryOptions) => {
    return exportCsv.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LoginLogController::exportCsv
 * @see app/Http/Controllers/Admin/LoginLogController.php:32
 * @route '/admin/audit-logs/export'
 */
exportCsv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportCsv.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\LoginLogController::exportCsv
 * @see app/Http/Controllers/Admin/LoginLogController.php:32
 * @route '/admin/audit-logs/export'
 */
exportCsv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportCsv.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\LoginLogController::exportCsv
 * @see app/Http/Controllers/Admin/LoginLogController.php:32
 * @route '/admin/audit-logs/export'
 */
    const exportCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportCsv.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\LoginLogController::exportCsv
 * @see app/Http/Controllers/Admin/LoginLogController.php:32
 * @route '/admin/audit-logs/export'
 */
        exportCsvForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportCsv.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\LoginLogController::exportCsv
 * @see app/Http/Controllers/Admin/LoginLogController.php:32
 * @route '/admin/audit-logs/export'
 */
        exportCsvForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportCsv.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportCsv.form = exportCsvForm
/**
* @see \App\Http\Controllers\Admin\LoginLogController::importCsv
 * @see app/Http/Controllers/Admin/LoginLogController.php:41
 * @route '/admin/audit-logs/import'
 */
export const importCsv = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importCsv.url(options),
    method: 'post',
})

importCsv.definition = {
    methods: ["post"],
    url: '/admin/audit-logs/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\LoginLogController::importCsv
 * @see app/Http/Controllers/Admin/LoginLogController.php:41
 * @route '/admin/audit-logs/import'
 */
importCsv.url = (options?: RouteQueryOptions) => {
    return importCsv.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LoginLogController::importCsv
 * @see app/Http/Controllers/Admin/LoginLogController.php:41
 * @route '/admin/audit-logs/import'
 */
importCsv.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importCsv.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\LoginLogController::importCsv
 * @see app/Http/Controllers/Admin/LoginLogController.php:41
 * @route '/admin/audit-logs/import'
 */
    const importCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: importCsv.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\LoginLogController::importCsv
 * @see app/Http/Controllers/Admin/LoginLogController.php:41
 * @route '/admin/audit-logs/import'
 */
        importCsvForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: importCsv.url(options),
            method: 'post',
        })
    
    importCsv.form = importCsvForm
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
* @see \App\Http\Controllers\Admin\LoginLogController::archiveIndex
 * @see app/Http/Controllers/Admin/LoginLogController.php:83
 * @route '/admin/audit-logs/archive'
 */
export const archiveIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archiveIndex.url(options),
    method: 'get',
})

archiveIndex.definition = {
    methods: ["get","head"],
    url: '/admin/audit-logs/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\LoginLogController::archiveIndex
 * @see app/Http/Controllers/Admin/LoginLogController.php:83
 * @route '/admin/audit-logs/archive'
 */
archiveIndex.url = (options?: RouteQueryOptions) => {
    return archiveIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\LoginLogController::archiveIndex
 * @see app/Http/Controllers/Admin/LoginLogController.php:83
 * @route '/admin/audit-logs/archive'
 */
archiveIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archiveIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\LoginLogController::archiveIndex
 * @see app/Http/Controllers/Admin/LoginLogController.php:83
 * @route '/admin/audit-logs/archive'
 */
archiveIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archiveIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\LoginLogController::archiveIndex
 * @see app/Http/Controllers/Admin/LoginLogController.php:83
 * @route '/admin/audit-logs/archive'
 */
    const archiveIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: archiveIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\LoginLogController::archiveIndex
 * @see app/Http/Controllers/Admin/LoginLogController.php:83
 * @route '/admin/audit-logs/archive'
 */
        archiveIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archiveIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\LoginLogController::archiveIndex
 * @see app/Http/Controllers/Admin/LoginLogController.php:83
 * @route '/admin/audit-logs/archive'
 */
        archiveIndexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archiveIndex.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    archiveIndex.form = archiveIndexForm
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
const LoginLogController = { index, exportCsv, importCsv, bulkArchive, archiveIndex, restore, bulkRestore, forceDelete, show, destroy }

export default LoginLogController