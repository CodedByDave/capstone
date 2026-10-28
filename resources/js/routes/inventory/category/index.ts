import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/shop/inventory/category'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/inventory/category',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/shop/inventory/category'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/shop/inventory/category'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/shop/inventory/category'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/shop/inventory/category'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/shop/inventory/category'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/shop/inventory/category'
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
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/shop/inventory/category'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/shop/inventory/category',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/shop/inventory/category'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/shop/inventory/category'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/shop/inventory/category'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/shop/inventory/category'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/shop/inventory/category/{inventoryCategory}/edit'
 */
export const edit = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/shop/inventory/category/{inventoryCategory}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/shop/inventory/category/{inventoryCategory}/edit'
 */
edit.url = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { inventoryCategory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { inventoryCategory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    inventoryCategory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inventoryCategory: typeof args.inventoryCategory === 'object'
                ? args.inventoryCategory.id
                : args.inventoryCategory,
                }

    return edit.definition.url
            .replace('{inventoryCategory}', parsedArgs.inventoryCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/shop/inventory/category/{inventoryCategory}/edit'
 */
edit.get = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/shop/inventory/category/{inventoryCategory}/edit'
 */
edit.head = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/shop/inventory/category/{inventoryCategory}/edit'
 */
    const editForm = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/shop/inventory/category/{inventoryCategory}/edit'
 */
        editForm.get = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/shop/inventory/category/{inventoryCategory}/edit'
 */
        editForm.head = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:72
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
export const update = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/shop/inventory/category/{inventoryCategory}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:72
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
update.url = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { inventoryCategory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { inventoryCategory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    inventoryCategory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inventoryCategory: typeof args.inventoryCategory === 'object'
                ? args.inventoryCategory.id
                : args.inventoryCategory,
                }

    return update.definition.url
            .replace('{inventoryCategory}', parsedArgs.inventoryCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:72
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
update.put = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:72
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
    const updateForm = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:72
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
        updateForm.put = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:86
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
export const destroy = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/shop/inventory/category/{inventoryCategory}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:86
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
destroy.url = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { inventoryCategory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { inventoryCategory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    inventoryCategory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inventoryCategory: typeof args.inventoryCategory === 'object'
                ? args.inventoryCategory.id
                : args.inventoryCategory,
                }

    return destroy.definition.url
            .replace('{inventoryCategory}', parsedArgs.inventoryCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:86
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
destroy.delete = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:86
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
    const destroyForm = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:86
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
        destroyForm.delete = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/shop/inventory/category/archive'
 */
export const archive = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})

archive.definition = {
    methods: ["get","head"],
    url: '/shop/inventory/category/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/shop/inventory/category/archive'
 */
archive.url = (options?: RouteQueryOptions) => {
    return archive.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/shop/inventory/category/archive'
 */
archive.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/shop/inventory/category/archive'
 */
archive.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archive.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/shop/inventory/category/archive'
 */
    const archiveForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: archive.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/shop/inventory/category/archive'
 */
        archiveForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archive.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/shop/inventory/category/archive'
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
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/shop/inventory/category/{id}/restore'
 */
export const restore = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

restore.definition = {
    methods: ["post"],
    url: '/shop/inventory/category/{id}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/shop/inventory/category/{id}/restore'
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
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/shop/inventory/category/{id}/restore'
 */
restore.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/shop/inventory/category/{id}/restore'
 */
    const restoreForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/shop/inventory/category/{id}/restore'
 */
        restoreForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore.url(args, options),
            method: 'post',
        })
    
    restore.form = restoreForm
const category = {
    index: Object.assign(index, index),
store: Object.assign(store, store),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
archive: Object.assign(archive, archive),
restore: Object.assign(restore, restore),
}

export default category