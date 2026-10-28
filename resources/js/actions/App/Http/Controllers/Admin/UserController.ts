import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\UserController::index
 * @see app/Http/Controllers/Admin/UserController.php:22
 * @route '/admin/users'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\UserController::index
 * @see app/Http/Controllers/Admin/UserController.php:22
 * @route '/admin/users'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserController::index
 * @see app/Http/Controllers/Admin/UserController.php:22
 * @route '/admin/users'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\UserController::index
 * @see app/Http/Controllers/Admin/UserController.php:22
 * @route '/admin/users'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::index
 * @see app/Http/Controllers/Admin/UserController.php:22
 * @route '/admin/users'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::index
 * @see app/Http/Controllers/Admin/UserController.php:22
 * @route '/admin/users'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\UserController::index
 * @see app/Http/Controllers/Admin/UserController.php:22
 * @route '/admin/users'
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
* @see \App\Http\Controllers\Admin\UserController::exportCsv
 * @see app/Http/Controllers/Admin/UserController.php:41
 * @route '/admin/users/export'
 */
export const exportCsv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportCsv.url(options),
    method: 'get',
})

exportCsv.definition = {
    methods: ["get","head"],
    url: '/admin/users/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\UserController::exportCsv
 * @see app/Http/Controllers/Admin/UserController.php:41
 * @route '/admin/users/export'
 */
exportCsv.url = (options?: RouteQueryOptions) => {
    return exportCsv.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserController::exportCsv
 * @see app/Http/Controllers/Admin/UserController.php:41
 * @route '/admin/users/export'
 */
exportCsv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportCsv.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\UserController::exportCsv
 * @see app/Http/Controllers/Admin/UserController.php:41
 * @route '/admin/users/export'
 */
exportCsv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportCsv.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::exportCsv
 * @see app/Http/Controllers/Admin/UserController.php:41
 * @route '/admin/users/export'
 */
    const exportCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportCsv.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::exportCsv
 * @see app/Http/Controllers/Admin/UserController.php:41
 * @route '/admin/users/export'
 */
        exportCsvForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportCsv.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\UserController::exportCsv
 * @see app/Http/Controllers/Admin/UserController.php:41
 * @route '/admin/users/export'
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
* @see \App\Http\Controllers\Admin\UserController::importCsv
 * @see app/Http/Controllers/Admin/UserController.php:72
 * @route '/admin/users/import'
 */
export const importCsv = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importCsv.url(options),
    method: 'post',
})

importCsv.definition = {
    methods: ["post"],
    url: '/admin/users/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\UserController::importCsv
 * @see app/Http/Controllers/Admin/UserController.php:72
 * @route '/admin/users/import'
 */
importCsv.url = (options?: RouteQueryOptions) => {
    return importCsv.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserController::importCsv
 * @see app/Http/Controllers/Admin/UserController.php:72
 * @route '/admin/users/import'
 */
importCsv.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importCsv.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::importCsv
 * @see app/Http/Controllers/Admin/UserController.php:72
 * @route '/admin/users/import'
 */
    const importCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: importCsv.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::importCsv
 * @see app/Http/Controllers/Admin/UserController.php:72
 * @route '/admin/users/import'
 */
        importCsvForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: importCsv.url(options),
            method: 'post',
        })
    
    importCsv.form = importCsvForm
/**
* @see \App\Http\Controllers\Admin\UserController::bulkArchive
 * @see app/Http/Controllers/Admin/UserController.php:118
 * @route '/admin/users/bulk-archive'
 */
export const bulkArchive = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkArchive.url(options),
    method: 'post',
})

bulkArchive.definition = {
    methods: ["post"],
    url: '/admin/users/bulk-archive',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\UserController::bulkArchive
 * @see app/Http/Controllers/Admin/UserController.php:118
 * @route '/admin/users/bulk-archive'
 */
bulkArchive.url = (options?: RouteQueryOptions) => {
    return bulkArchive.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserController::bulkArchive
 * @see app/Http/Controllers/Admin/UserController.php:118
 * @route '/admin/users/bulk-archive'
 */
bulkArchive.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkArchive.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::bulkArchive
 * @see app/Http/Controllers/Admin/UserController.php:118
 * @route '/admin/users/bulk-archive'
 */
    const bulkArchiveForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkArchive.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::bulkArchive
 * @see app/Http/Controllers/Admin/UserController.php:118
 * @route '/admin/users/bulk-archive'
 */
        bulkArchiveForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkArchive.url(options),
            method: 'post',
        })
    
    bulkArchive.form = bulkArchiveForm
/**
* @see \App\Http\Controllers\Admin\UserController::archiveIndex
 * @see app/Http/Controllers/Admin/UserController.php:136
 * @route '/admin/users/archive'
 */
export const archiveIndex = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archiveIndex.url(options),
    method: 'get',
})

archiveIndex.definition = {
    methods: ["get","head"],
    url: '/admin/users/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\UserController::archiveIndex
 * @see app/Http/Controllers/Admin/UserController.php:136
 * @route '/admin/users/archive'
 */
archiveIndex.url = (options?: RouteQueryOptions) => {
    return archiveIndex.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserController::archiveIndex
 * @see app/Http/Controllers/Admin/UserController.php:136
 * @route '/admin/users/archive'
 */
archiveIndex.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archiveIndex.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\UserController::archiveIndex
 * @see app/Http/Controllers/Admin/UserController.php:136
 * @route '/admin/users/archive'
 */
archiveIndex.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archiveIndex.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::archiveIndex
 * @see app/Http/Controllers/Admin/UserController.php:136
 * @route '/admin/users/archive'
 */
    const archiveIndexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: archiveIndex.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::archiveIndex
 * @see app/Http/Controllers/Admin/UserController.php:136
 * @route '/admin/users/archive'
 */
        archiveIndexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archiveIndex.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\UserController::archiveIndex
 * @see app/Http/Controllers/Admin/UserController.php:136
 * @route '/admin/users/archive'
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
* @see \App\Http\Controllers\Admin\UserController::restore
 * @see app/Http/Controllers/Admin/UserController.php:149
 * @route '/admin/users/archive/{id}/restore'
 */
export const restore = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

restore.definition = {
    methods: ["post"],
    url: '/admin/users/archive/{id}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\UserController::restore
 * @see app/Http/Controllers/Admin/UserController.php:149
 * @route '/admin/users/archive/{id}/restore'
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
* @see \App\Http\Controllers\Admin\UserController::restore
 * @see app/Http/Controllers/Admin/UserController.php:149
 * @route '/admin/users/archive/{id}/restore'
 */
restore.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::restore
 * @see app/Http/Controllers/Admin/UserController.php:149
 * @route '/admin/users/archive/{id}/restore'
 */
    const restoreForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::restore
 * @see app/Http/Controllers/Admin/UserController.php:149
 * @route '/admin/users/archive/{id}/restore'
 */
        restoreForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore.url(args, options),
            method: 'post',
        })
    
    restore.form = restoreForm
/**
* @see \App\Http\Controllers\Admin\UserController::bulkRestore
 * @see app/Http/Controllers/Admin/UserController.php:164
 * @route '/admin/users/archive/bulk-restore'
 */
export const bulkRestore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkRestore.url(options),
    method: 'post',
})

bulkRestore.definition = {
    methods: ["post"],
    url: '/admin/users/archive/bulk-restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\UserController::bulkRestore
 * @see app/Http/Controllers/Admin/UserController.php:164
 * @route '/admin/users/archive/bulk-restore'
 */
bulkRestore.url = (options?: RouteQueryOptions) => {
    return bulkRestore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserController::bulkRestore
 * @see app/Http/Controllers/Admin/UserController.php:164
 * @route '/admin/users/archive/bulk-restore'
 */
bulkRestore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkRestore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::bulkRestore
 * @see app/Http/Controllers/Admin/UserController.php:164
 * @route '/admin/users/archive/bulk-restore'
 */
    const bulkRestoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkRestore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::bulkRestore
 * @see app/Http/Controllers/Admin/UserController.php:164
 * @route '/admin/users/archive/bulk-restore'
 */
        bulkRestoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkRestore.url(options),
            method: 'post',
        })
    
    bulkRestore.form = bulkRestoreForm
/**
* @see \App\Http\Controllers\Admin\UserController::forceDelete
 * @see app/Http/Controllers/Admin/UserController.php:182
 * @route '/admin/users/archive/{id}'
 */
export const forceDelete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: forceDelete.url(args, options),
    method: 'delete',
})

forceDelete.definition = {
    methods: ["delete"],
    url: '/admin/users/archive/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\UserController::forceDelete
 * @see app/Http/Controllers/Admin/UserController.php:182
 * @route '/admin/users/archive/{id}'
 */
forceDelete.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return forceDelete.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserController::forceDelete
 * @see app/Http/Controllers/Admin/UserController.php:182
 * @route '/admin/users/archive/{id}'
 */
forceDelete.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: forceDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::forceDelete
 * @see app/Http/Controllers/Admin/UserController.php:182
 * @route '/admin/users/archive/{id}'
 */
    const forceDeleteForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: forceDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::forceDelete
 * @see app/Http/Controllers/Admin/UserController.php:182
 * @route '/admin/users/archive/{id}'
 */
        forceDeleteForm.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\UserController::show
 * @see app/Http/Controllers/Admin/UserController.php:95
 * @route '/admin/users/{user}'
 */
export const show = (args: { user: string | number | { public_id: string | number } } | [user: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/users/{user}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\UserController::show
 * @see app/Http/Controllers/Admin/UserController.php:95
 * @route '/admin/users/{user}'
 */
show.url = (args: { user: string | number | { public_id: string | number } } | [user: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { user: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.public_id
                : args.user,
                }

    return show.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserController::show
 * @see app/Http/Controllers/Admin/UserController.php:95
 * @route '/admin/users/{user}'
 */
show.get = (args: { user: string | number | { public_id: string | number } } | [user: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\UserController::show
 * @see app/Http/Controllers/Admin/UserController.php:95
 * @route '/admin/users/{user}'
 */
show.head = (args: { user: string | number | { public_id: string | number } } | [user: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::show
 * @see app/Http/Controllers/Admin/UserController.php:95
 * @route '/admin/users/{user}'
 */
    const showForm = (args: { user: string | number | { public_id: string | number } } | [user: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::show
 * @see app/Http/Controllers/Admin/UserController.php:95
 * @route '/admin/users/{user}'
 */
        showForm.get = (args: { user: string | number | { public_id: string | number } } | [user: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\UserController::show
 * @see app/Http/Controllers/Admin/UserController.php:95
 * @route '/admin/users/{user}'
 */
        showForm.head = (args: { user: string | number | { public_id: string | number } } | [user: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\UserController::destroy
 * @see app/Http/Controllers/Admin/UserController.php:104
 * @route '/admin/users/{user}'
 */
export const destroy = (args: { user: string | number | { id: string | number } } | [user: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/users/{user}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\UserController::destroy
 * @see app/Http/Controllers/Admin/UserController.php:104
 * @route '/admin/users/{user}'
 */
destroy.url = (args: { user: string | number | { id: string | number } } | [user: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { user: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.id
                : args.user,
                }

    return destroy.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserController::destroy
 * @see app/Http/Controllers/Admin/UserController.php:104
 * @route '/admin/users/{user}'
 */
destroy.delete = (args: { user: string | number | { id: string | number } } | [user: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\UserController::destroy
 * @see app/Http/Controllers/Admin/UserController.php:104
 * @route '/admin/users/{user}'
 */
    const destroyForm = (args: { user: string | number | { id: string | number } } | [user: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\UserController::destroy
 * @see app/Http/Controllers/Admin/UserController.php:104
 * @route '/admin/users/{user}'
 */
        destroyForm.delete = (args: { user: string | number | { id: string | number } } | [user: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
const UserController = { index, exportCsv, importCsv, bulkArchive, archiveIndex, restore, bulkRestore, forceDelete, show, destroy }

export default UserController