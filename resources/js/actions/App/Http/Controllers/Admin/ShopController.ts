import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\ShopController::index
 * @see app/Http/Controllers/Admin/ShopController.php:38
 * @route '/admin/shop'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/shop',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ShopController::index
 * @see app/Http/Controllers/Admin/ShopController.php:38
 * @route '/admin/shop'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ShopController::index
 * @see app/Http/Controllers/Admin/ShopController.php:38
 * @route '/admin/shop'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\ShopController::index
 * @see app/Http/Controllers/Admin/ShopController.php:38
 * @route '/admin/shop'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\ShopController::index
 * @see app/Http/Controllers/Admin/ShopController.php:38
 * @route '/admin/shop'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\ShopController::index
 * @see app/Http/Controllers/Admin/ShopController.php:38
 * @route '/admin/shop'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\ShopController::index
 * @see app/Http/Controllers/Admin/ShopController.php:38
 * @route '/admin/shop'
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
* @see \App\Http\Controllers\Admin\ShopController::exportCsv
 * @see app/Http/Controllers/Admin/ShopController.php:75
 * @route '/admin/shop/export'
 */
export const exportCsv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportCsv.url(options),
    method: 'get',
})

exportCsv.definition = {
    methods: ["get","head"],
    url: '/admin/shop/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ShopController::exportCsv
 * @see app/Http/Controllers/Admin/ShopController.php:75
 * @route '/admin/shop/export'
 */
exportCsv.url = (options?: RouteQueryOptions) => {
    return exportCsv.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ShopController::exportCsv
 * @see app/Http/Controllers/Admin/ShopController.php:75
 * @route '/admin/shop/export'
 */
exportCsv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportCsv.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\ShopController::exportCsv
 * @see app/Http/Controllers/Admin/ShopController.php:75
 * @route '/admin/shop/export'
 */
exportCsv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportCsv.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\ShopController::exportCsv
 * @see app/Http/Controllers/Admin/ShopController.php:75
 * @route '/admin/shop/export'
 */
    const exportCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportCsv.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\ShopController::exportCsv
 * @see app/Http/Controllers/Admin/ShopController.php:75
 * @route '/admin/shop/export'
 */
        exportCsvForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportCsv.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\ShopController::exportCsv
 * @see app/Http/Controllers/Admin/ShopController.php:75
 * @route '/admin/shop/export'
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
* @see \App\Http\Controllers\Admin\ShopController::bulkAction
 * @see app/Http/Controllers/Admin/ShopController.php:245
 * @route '/admin/shop/bulk-action'
 */
export const bulkAction = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkAction.url(options),
    method: 'post',
})

bulkAction.definition = {
    methods: ["post"],
    url: '/admin/shop/bulk-action',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ShopController::bulkAction
 * @see app/Http/Controllers/Admin/ShopController.php:245
 * @route '/admin/shop/bulk-action'
 */
bulkAction.url = (options?: RouteQueryOptions) => {
    return bulkAction.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ShopController::bulkAction
 * @see app/Http/Controllers/Admin/ShopController.php:245
 * @route '/admin/shop/bulk-action'
 */
bulkAction.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkAction.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ShopController::bulkAction
 * @see app/Http/Controllers/Admin/ShopController.php:245
 * @route '/admin/shop/bulk-action'
 */
    const bulkActionForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkAction.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ShopController::bulkAction
 * @see app/Http/Controllers/Admin/ShopController.php:245
 * @route '/admin/shop/bulk-action'
 */
        bulkActionForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkAction.url(options),
            method: 'post',
        })
    
    bulkAction.form = bulkActionForm
/**
* @see \App\Http\Controllers\Admin\ShopController::restore
 * @see app/Http/Controllers/Admin/ShopController.php:227
 * @route '/admin/shop/archive/{publicId}/restore'
 */
export const restore = (args: { publicId: string | number } | [publicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

restore.definition = {
    methods: ["post"],
    url: '/admin/shop/archive/{publicId}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ShopController::restore
 * @see app/Http/Controllers/Admin/ShopController.php:227
 * @route '/admin/shop/archive/{publicId}/restore'
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
* @see \App\Http\Controllers\Admin\ShopController::restore
 * @see app/Http/Controllers/Admin/ShopController.php:227
 * @route '/admin/shop/archive/{publicId}/restore'
 */
restore.post = (args: { publicId: string | number } | [publicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ShopController::restore
 * @see app/Http/Controllers/Admin/ShopController.php:227
 * @route '/admin/shop/archive/{publicId}/restore'
 */
    const restoreForm = (args: { publicId: string | number } | [publicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ShopController::restore
 * @see app/Http/Controllers/Admin/ShopController.php:227
 * @route '/admin/shop/archive/{publicId}/restore'
 */
        restoreForm.post = (args: { publicId: string | number } | [publicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore.url(args, options),
            method: 'post',
        })
    
    restore.form = restoreForm
/**
* @see \App\Http\Controllers\Admin\ShopController::forceDelete
 * @see app/Http/Controllers/Admin/ShopController.php:236
 * @route '/admin/shop/archive/{publicId}'
 */
export const forceDelete = (args: { publicId: string | number } | [publicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: forceDelete.url(args, options),
    method: 'delete',
})

forceDelete.definition = {
    methods: ["delete"],
    url: '/admin/shop/archive/{publicId}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\ShopController::forceDelete
 * @see app/Http/Controllers/Admin/ShopController.php:236
 * @route '/admin/shop/archive/{publicId}'
 */
forceDelete.url = (args: { publicId: string | number } | [publicId: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return forceDelete.definition.url
            .replace('{publicId}', parsedArgs.publicId.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ShopController::forceDelete
 * @see app/Http/Controllers/Admin/ShopController.php:236
 * @route '/admin/shop/archive/{publicId}'
 */
forceDelete.delete = (args: { publicId: string | number } | [publicId: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: forceDelete.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\ShopController::forceDelete
 * @see app/Http/Controllers/Admin/ShopController.php:236
 * @route '/admin/shop/archive/{publicId}'
 */
    const forceDeleteForm = (args: { publicId: string | number } | [publicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: forceDelete.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ShopController::forceDelete
 * @see app/Http/Controllers/Admin/ShopController.php:236
 * @route '/admin/shop/archive/{publicId}'
 */
        forceDeleteForm.delete = (args: { publicId: string | number } | [publicId: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\ShopController::show
 * @see app/Http/Controllers/Admin/ShopController.php:123
 * @route '/admin/shop/{shop}'
 */
export const show = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/shop/{shop}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ShopController::show
 * @see app/Http/Controllers/Admin/ShopController.php:123
 * @route '/admin/shop/{shop}'
 */
show.url = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shop: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { shop: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    shop: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        shop: typeof args.shop === 'object'
                ? args.shop.public_id
                : args.shop,
                }

    return show.definition.url
            .replace('{shop}', parsedArgs.shop.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ShopController::show
 * @see app/Http/Controllers/Admin/ShopController.php:123
 * @route '/admin/shop/{shop}'
 */
show.get = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\ShopController::show
 * @see app/Http/Controllers/Admin/ShopController.php:123
 * @route '/admin/shop/{shop}'
 */
show.head = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\ShopController::show
 * @see app/Http/Controllers/Admin/ShopController.php:123
 * @route '/admin/shop/{shop}'
 */
    const showForm = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\ShopController::show
 * @see app/Http/Controllers/Admin/ShopController.php:123
 * @route '/admin/shop/{shop}'
 */
        showForm.get = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\ShopController::show
 * @see app/Http/Controllers/Admin/ShopController.php:123
 * @route '/admin/shop/{shop}'
 */
        showForm.head = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\ShopController::edit
 * @see app/Http/Controllers/Admin/ShopController.php:194
 * @route '/admin/shop/{shop}/edit'
 */
export const edit = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/shop/{shop}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ShopController::edit
 * @see app/Http/Controllers/Admin/ShopController.php:194
 * @route '/admin/shop/{shop}/edit'
 */
edit.url = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shop: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { shop: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    shop: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        shop: typeof args.shop === 'object'
                ? args.shop.public_id
                : args.shop,
                }

    return edit.definition.url
            .replace('{shop}', parsedArgs.shop.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ShopController::edit
 * @see app/Http/Controllers/Admin/ShopController.php:194
 * @route '/admin/shop/{shop}/edit'
 */
edit.get = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\ShopController::edit
 * @see app/Http/Controllers/Admin/ShopController.php:194
 * @route '/admin/shop/{shop}/edit'
 */
edit.head = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\ShopController::edit
 * @see app/Http/Controllers/Admin/ShopController.php:194
 * @route '/admin/shop/{shop}/edit'
 */
    const editForm = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\ShopController::edit
 * @see app/Http/Controllers/Admin/ShopController.php:194
 * @route '/admin/shop/{shop}/edit'
 */
        editForm.get = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\ShopController::edit
 * @see app/Http/Controllers/Admin/ShopController.php:194
 * @route '/admin/shop/{shop}/edit'
 */
        editForm.head = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit.form = editForm
/**
* @see \App\Http\Controllers\Admin\ShopController::update
 * @see app/Http/Controllers/Admin/ShopController.php:201
 * @route '/admin/shop/{shop}'
 */
export const update = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/admin/shop/{shop}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Admin\ShopController::update
 * @see app/Http/Controllers/Admin/ShopController.php:201
 * @route '/admin/shop/{shop}'
 */
update.url = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shop: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { shop: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    shop: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        shop: typeof args.shop === 'object'
                ? args.shop.public_id
                : args.shop,
                }

    return update.definition.url
            .replace('{shop}', parsedArgs.shop.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ShopController::update
 * @see app/Http/Controllers/Admin/ShopController.php:201
 * @route '/admin/shop/{shop}'
 */
update.put = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Admin\ShopController::update
 * @see app/Http/Controllers/Admin/ShopController.php:201
 * @route '/admin/shop/{shop}'
 */
    const updateForm = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ShopController::update
 * @see app/Http/Controllers/Admin/ShopController.php:201
 * @route '/admin/shop/{shop}'
 */
        updateForm.put = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\ShopController::destroy
 * @see app/Http/Controllers/Admin/ShopController.php:216
 * @route '/admin/shop/{shop}'
 */
export const destroy = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/shop/{shop}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\ShopController::destroy
 * @see app/Http/Controllers/Admin/ShopController.php:216
 * @route '/admin/shop/{shop}'
 */
destroy.url = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shop: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { shop: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    shop: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        shop: typeof args.shop === 'object'
                ? args.shop.public_id
                : args.shop,
                }

    return destroy.definition.url
            .replace('{shop}', parsedArgs.shop.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ShopController::destroy
 * @see app/Http/Controllers/Admin/ShopController.php:216
 * @route '/admin/shop/{shop}'
 */
destroy.delete = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\ShopController::destroy
 * @see app/Http/Controllers/Admin/ShopController.php:216
 * @route '/admin/shop/{shop}'
 */
    const destroyForm = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ShopController::destroy
 * @see app/Http/Controllers/Admin/ShopController.php:216
 * @route '/admin/shop/{shop}'
 */
        destroyForm.delete = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Admin\ShopController::disable
 * @see app/Http/Controllers/Admin/ShopController.php:277
 * @route '/admin/shop/{shop}/disable'
 */
export const disable = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: disable.url(args, options),
    method: 'post',
})

disable.definition = {
    methods: ["post"],
    url: '/admin/shop/{shop}/disable',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ShopController::disable
 * @see app/Http/Controllers/Admin/ShopController.php:277
 * @route '/admin/shop/{shop}/disable'
 */
disable.url = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shop: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { shop: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    shop: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        shop: typeof args.shop === 'object'
                ? args.shop.public_id
                : args.shop,
                }

    return disable.definition.url
            .replace('{shop}', parsedArgs.shop.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ShopController::disable
 * @see app/Http/Controllers/Admin/ShopController.php:277
 * @route '/admin/shop/{shop}/disable'
 */
disable.post = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: disable.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ShopController::disable
 * @see app/Http/Controllers/Admin/ShopController.php:277
 * @route '/admin/shop/{shop}/disable'
 */
    const disableForm = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: disable.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ShopController::disable
 * @see app/Http/Controllers/Admin/ShopController.php:277
 * @route '/admin/shop/{shop}/disable'
 */
        disableForm.post = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: disable.url(args, options),
            method: 'post',
        })
    
    disable.form = disableForm
/**
* @see \App\Http\Controllers\Admin\ShopController::enable
 * @see app/Http/Controllers/Admin/ShopController.php:286
 * @route '/admin/shop/{shop}/enable'
 */
export const enable = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: enable.url(args, options),
    method: 'post',
})

enable.definition = {
    methods: ["post"],
    url: '/admin/shop/{shop}/enable',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ShopController::enable
 * @see app/Http/Controllers/Admin/ShopController.php:286
 * @route '/admin/shop/{shop}/enable'
 */
enable.url = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { shop: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { shop: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    shop: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        shop: typeof args.shop === 'object'
                ? args.shop.public_id
                : args.shop,
                }

    return enable.definition.url
            .replace('{shop}', parsedArgs.shop.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ShopController::enable
 * @see app/Http/Controllers/Admin/ShopController.php:286
 * @route '/admin/shop/{shop}/enable'
 */
enable.post = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: enable.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ShopController::enable
 * @see app/Http/Controllers/Admin/ShopController.php:286
 * @route '/admin/shop/{shop}/enable'
 */
    const enableForm = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: enable.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ShopController::enable
 * @see app/Http/Controllers/Admin/ShopController.php:286
 * @route '/admin/shop/{shop}/enable'
 */
        enableForm.post = (args: { shop: string | { public_id: string } } | [shop: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: enable.url(args, options),
            method: 'post',
        })
    
    enable.form = enableForm
const ShopController = { index, exportCsv, bulkAction, restore, forceDelete, show, edit, update, destroy, disable, enable }

export default ShopController