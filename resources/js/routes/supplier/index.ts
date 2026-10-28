import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/shop/supplier'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/supplier',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/shop/supplier'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/shop/supplier'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/shop/supplier'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/shop/supplier'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/shop/supplier'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/shop/supplier'
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
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/shop/supplier/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/shop/supplier/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/shop/supplier/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/shop/supplier/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/shop/supplier/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/shop/supplier/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/shop/supplier/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/shop/supplier/create'
 */
        createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create.form = createForm
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/shop/supplier'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/shop/supplier',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/shop/supplier'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/shop/supplier'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/shop/supplier'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/shop/supplier'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/shop/supplier/{supplier}/edit'
 */
export const edit = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/shop/supplier/{supplier}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/shop/supplier/{supplier}/edit'
 */
edit.url = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { supplier: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { supplier: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    supplier: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        supplier: typeof args.supplier === 'object'
                ? args.supplier.id
                : args.supplier,
                }

    return edit.definition.url
            .replace('{supplier}', parsedArgs.supplier.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/shop/supplier/{supplier}/edit'
 */
edit.get = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/shop/supplier/{supplier}/edit'
 */
edit.head = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/shop/supplier/{supplier}/edit'
 */
    const editForm = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/shop/supplier/{supplier}/edit'
 */
        editForm.get = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/shop/supplier/{supplier}/edit'
 */
        editForm.head = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::update
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:64
 * @route '/shop/supplier/{supplier}'
 */
export const update = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/shop/supplier/{supplier}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::update
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:64
 * @route '/shop/supplier/{supplier}'
 */
update.url = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { supplier: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { supplier: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    supplier: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        supplier: typeof args.supplier === 'object'
                ? args.supplier.id
                : args.supplier,
                }

    return update.definition.url
            .replace('{supplier}', parsedArgs.supplier.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::update
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:64
 * @route '/shop/supplier/{supplier}'
 */
update.put = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::update
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:64
 * @route '/shop/supplier/{supplier}'
 */
    const updateForm = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::update
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:64
 * @route '/shop/supplier/{supplier}'
 */
        updateForm.put = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::destroy
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:72
 * @route '/shop/supplier/{supplier}'
 */
export const destroy = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/shop/supplier/{supplier}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::destroy
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:72
 * @route '/shop/supplier/{supplier}'
 */
destroy.url = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { supplier: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { supplier: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    supplier: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        supplier: typeof args.supplier === 'object'
                ? args.supplier.id
                : args.supplier,
                }

    return destroy.definition.url
            .replace('{supplier}', parsedArgs.supplier.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::destroy
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:72
 * @route '/shop/supplier/{supplier}'
 */
destroy.delete = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::destroy
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:72
 * @route '/shop/supplier/{supplier}'
 */
    const destroyForm = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::destroy
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:72
 * @route '/shop/supplier/{supplier}'
 */
        destroyForm.delete = (args: { supplier: string | number | { id: string | number } } | [supplier: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/shop/supplier/archive'
 */
export const archive = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})

archive.definition = {
    methods: ["get","head"],
    url: '/shop/supplier/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/shop/supplier/archive'
 */
archive.url = (options?: RouteQueryOptions) => {
    return archive.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/shop/supplier/archive'
 */
archive.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/shop/supplier/archive'
 */
archive.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archive.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/shop/supplier/archive'
 */
    const archiveForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: archive.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/shop/supplier/archive'
 */
        archiveForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archive.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/shop/supplier/archive'
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
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/shop/supplier/{id}/restore'
 */
export const restore = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

restore.definition = {
    methods: ["post"],
    url: '/shop/supplier/{id}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/shop/supplier/{id}/restore'
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
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/shop/supplier/{id}/restore'
 */
restore.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/shop/supplier/{id}/restore'
 */
    const restoreForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/shop/supplier/{id}/restore'
 */
        restoreForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore.url(args, options),
            method: 'post',
        })
    
    restore.form = restoreForm
const supplier = {
    index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
archive: Object.assign(archive, archive),
restore: Object.assign(restore, restore),
}

export default supplier