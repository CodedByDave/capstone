import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
import payment from './payment'
import agreement from './agreement'
import notifications from './notifications'
import paymentHistory from './payment-history'
import setup from './setup'
import permissionF99818 from './permission'
import orders from './orders'
import services from './services'
import paymentQrBef25d from './payment-qr'
import upgradeE62d79 from './upgrade'
import settings69f00b from './settings'
import logisticsC73990 from './logistics'
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
* @see \App\Http\Controllers\Admin\ShopController::exportMethod
 * @see app/Http/Controllers/Admin/ShopController.php:75
 * @route '/admin/shop/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/admin/shop/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ShopController::exportMethod
 * @see app/Http/Controllers/Admin/ShopController.php:75
 * @route '/admin/shop/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ShopController::exportMethod
 * @see app/Http/Controllers/Admin/ShopController.php:75
 * @route '/admin/shop/export'
 */
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\ShopController::exportMethod
 * @see app/Http/Controllers/Admin/ShopController.php:75
 * @route '/admin/shop/export'
 */
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\ShopController::exportMethod
 * @see app/Http/Controllers/Admin/ShopController.php:75
 * @route '/admin/shop/export'
 */
    const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMethod.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\ShopController::exportMethod
 * @see app/Http/Controllers/Admin/ShopController.php:75
 * @route '/admin/shop/export'
 */
        exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\ShopController::exportMethod
 * @see app/Http/Controllers/Admin/ShopController.php:75
 * @route '/admin/shop/export'
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
/**
* @see \App\Http\Controllers\Shop\ShopDashboardController::dashboard
 * @see app/Http/Controllers/Shop/ShopDashboardController.php:27
 * @route '/shop/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/shop/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ShopDashboardController::dashboard
 * @see app/Http/Controllers/Shop/ShopDashboardController.php:27
 * @route '/shop/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopDashboardController::dashboard
 * @see app/Http/Controllers/Shop/ShopDashboardController.php:27
 * @route '/shop/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ShopDashboardController::dashboard
 * @see app/Http/Controllers/Shop/ShopDashboardController.php:27
 * @route '/shop/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ShopDashboardController::dashboard
 * @see app/Http/Controllers/Shop/ShopDashboardController.php:27
 * @route '/shop/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopDashboardController::dashboard
 * @see app/Http/Controllers/Shop/ShopDashboardController.php:27
 * @route '/shop/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ShopDashboardController::dashboard
 * @see app/Http/Controllers/Shop/ShopDashboardController.php:27
 * @route '/shop/dashboard'
 */
        dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dashboard.form = dashboardForm
/**
* @see \App\Http\Controllers\Shop\ShopDataController::data
 * @see app/Http/Controllers/Shop/ShopDataController.php:10
 * @route '/shop/data'
 */
export const data = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: data.url(options),
    method: 'get',
})

data.definition = {
    methods: ["get","head"],
    url: '/shop/data',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ShopDataController::data
 * @see app/Http/Controllers/Shop/ShopDataController.php:10
 * @route '/shop/data'
 */
data.url = (options?: RouteQueryOptions) => {
    return data.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopDataController::data
 * @see app/Http/Controllers/Shop/ShopDataController.php:10
 * @route '/shop/data'
 */
data.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: data.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ShopDataController::data
 * @see app/Http/Controllers/Shop/ShopDataController.php:10
 * @route '/shop/data'
 */
data.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: data.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ShopDataController::data
 * @see app/Http/Controllers/Shop/ShopDataController.php:10
 * @route '/shop/data'
 */
    const dataForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: data.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopDataController::data
 * @see app/Http/Controllers/Shop/ShopDataController.php:10
 * @route '/shop/data'
 */
        dataForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: data.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ShopDataController::data
 * @see app/Http/Controllers/Shop/ShopDataController.php:10
 * @route '/shop/data'
 */
        dataForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: data.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    data.form = dataForm
/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::permission
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:26
 * @route '/shop/permission'
 */
export const permission = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: permission.url(options),
    method: 'get',
})

permission.definition = {
    methods: ["get","head"],
    url: '/shop/permission',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::permission
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:26
 * @route '/shop/permission'
 */
permission.url = (options?: RouteQueryOptions) => {
    return permission.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::permission
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:26
 * @route '/shop/permission'
 */
permission.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: permission.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::permission
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:26
 * @route '/shop/permission'
 */
permission.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: permission.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::permission
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:26
 * @route '/shop/permission'
 */
    const permissionForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: permission.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::permission
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:26
 * @route '/shop/permission'
 */
        permissionForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: permission.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\PermissionController::permission
 * @see app/Http/Controllers/Shop/Employee/PermissionController.php:26
 * @route '/shop/permission'
 */
        permissionForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: permission.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    permission.form = permissionForm
/**
* @see \App\Http\Controllers\Shop\PaymentQrController::paymentQr
 * @see app/Http/Controllers/Shop/PaymentQrController.php:39
 * @route '/shop/payment-qr'
 */
export const paymentQr = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: paymentQr.url(options),
    method: 'get',
})

paymentQr.definition = {
    methods: ["get","head"],
    url: '/shop/payment-qr',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\PaymentQrController::paymentQr
 * @see app/Http/Controllers/Shop/PaymentQrController.php:39
 * @route '/shop/payment-qr'
 */
paymentQr.url = (options?: RouteQueryOptions) => {
    return paymentQr.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\PaymentQrController::paymentQr
 * @see app/Http/Controllers/Shop/PaymentQrController.php:39
 * @route '/shop/payment-qr'
 */
paymentQr.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: paymentQr.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\PaymentQrController::paymentQr
 * @see app/Http/Controllers/Shop/PaymentQrController.php:39
 * @route '/shop/payment-qr'
 */
paymentQr.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: paymentQr.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\PaymentQrController::paymentQr
 * @see app/Http/Controllers/Shop/PaymentQrController.php:39
 * @route '/shop/payment-qr'
 */
    const paymentQrForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: paymentQr.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\PaymentQrController::paymentQr
 * @see app/Http/Controllers/Shop/PaymentQrController.php:39
 * @route '/shop/payment-qr'
 */
        paymentQrForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: paymentQr.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\PaymentQrController::paymentQr
 * @see app/Http/Controllers/Shop/PaymentQrController.php:39
 * @route '/shop/payment-qr'
 */
        paymentQrForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: paymentQr.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    paymentQr.form = paymentQrForm
/**
* @see \App\Http\Controllers\Shop\UpgradeController::upgrade
 * @see app/Http/Controllers/Shop/UpgradeController.php:32
 * @route '/shop/upgrade'
 */
export const upgrade = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: upgrade.url(options),
    method: 'get',
})

upgrade.definition = {
    methods: ["get","head"],
    url: '/shop/upgrade',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\UpgradeController::upgrade
 * @see app/Http/Controllers/Shop/UpgradeController.php:32
 * @route '/shop/upgrade'
 */
upgrade.url = (options?: RouteQueryOptions) => {
    return upgrade.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\UpgradeController::upgrade
 * @see app/Http/Controllers/Shop/UpgradeController.php:32
 * @route '/shop/upgrade'
 */
upgrade.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: upgrade.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\UpgradeController::upgrade
 * @see app/Http/Controllers/Shop/UpgradeController.php:32
 * @route '/shop/upgrade'
 */
upgrade.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: upgrade.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\UpgradeController::upgrade
 * @see app/Http/Controllers/Shop/UpgradeController.php:32
 * @route '/shop/upgrade'
 */
    const upgradeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: upgrade.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\UpgradeController::upgrade
 * @see app/Http/Controllers/Shop/UpgradeController.php:32
 * @route '/shop/upgrade'
 */
        upgradeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: upgrade.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\UpgradeController::upgrade
 * @see app/Http/Controllers/Shop/UpgradeController.php:32
 * @route '/shop/upgrade'
 */
        upgradeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: upgrade.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    upgrade.form = upgradeForm
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::settings
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:29
 * @route '/shop/settings'
 */
export const settings = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(options),
    method: 'get',
})

settings.definition = {
    methods: ["get","head"],
    url: '/shop/settings',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::settings
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:29
 * @route '/shop/settings'
 */
settings.url = (options?: RouteQueryOptions) => {
    return settings.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::settings
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:29
 * @route '/shop/settings'
 */
settings.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ShopSettingsController::settings
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:29
 * @route '/shop/settings'
 */
settings.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: settings.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::settings
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:29
 * @route '/shop/settings'
 */
    const settingsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: settings.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::settings
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:29
 * @route '/shop/settings'
 */
        settingsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: settings.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ShopSettingsController::settings
 * @see app/Http/Controllers/Shop/ShopSettingsController.php:29
 * @route '/shop/settings'
 */
        settingsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: settings.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    settings.form = settingsForm
/**
* @see \App\Http\Controllers\Shop\LogisticsController::logistics
 * @see app/Http/Controllers/Shop/LogisticsController.php:26
 * @route '/shop/logistics'
 */
export const logistics = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: logistics.url(options),
    method: 'get',
})

logistics.definition = {
    methods: ["get","head"],
    url: '/shop/logistics',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::logistics
 * @see app/Http/Controllers/Shop/LogisticsController.php:26
 * @route '/shop/logistics'
 */
logistics.url = (options?: RouteQueryOptions) => {
    return logistics.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::logistics
 * @see app/Http/Controllers/Shop/LogisticsController.php:26
 * @route '/shop/logistics'
 */
logistics.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: logistics.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\LogisticsController::logistics
 * @see app/Http/Controllers/Shop/LogisticsController.php:26
 * @route '/shop/logistics'
 */
logistics.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: logistics.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::logistics
 * @see app/Http/Controllers/Shop/LogisticsController.php:26
 * @route '/shop/logistics'
 */
    const logisticsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: logistics.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::logistics
 * @see app/Http/Controllers/Shop/LogisticsController.php:26
 * @route '/shop/logistics'
 */
        logisticsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: logistics.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\LogisticsController::logistics
 * @see app/Http/Controllers/Shop/LogisticsController.php:26
 * @route '/shop/logistics'
 */
        logisticsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: logistics.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    logistics.form = logisticsForm
const shop = {
    payment: Object.assign(payment, payment),
index: Object.assign(index, index),
export: Object.assign(exportMethod, exportMethod),
bulkAction: Object.assign(bulkAction, bulkAction),
restore: Object.assign(restore, restore),
forceDelete: Object.assign(forceDelete, forceDelete),
show: Object.assign(show, show),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
disable: Object.assign(disable, disable),
enable: Object.assign(enable, enable),
agreement: Object.assign(agreement, agreement),
notifications: Object.assign(notifications, notifications),
paymentHistory: Object.assign(paymentHistory, paymentHistory),
setup: Object.assign(setup, setup),
dashboard: Object.assign(dashboard, dashboard),
data: Object.assign(data, data),
permission: Object.assign(permission, permissionF99818),
orders: Object.assign(orders, orders),
services: Object.assign(services, services),
paymentQr: Object.assign(paymentQr, paymentQrBef25d),
upgrade: Object.assign(upgrade, upgradeE62d79),
settings: Object.assign(settings, settings69f00b),
logistics: Object.assign(logistics, logisticsC73990),
}

export default shop