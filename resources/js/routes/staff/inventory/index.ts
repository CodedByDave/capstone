import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import category from './category'
import alerts from './alerts'
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/staff/inventory'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/staff/inventory',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/staff/inventory'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/staff/inventory'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/staff/inventory'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/staff/inventory'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/staff/inventory'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/staff/inventory'
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
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/staff/inventory/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/staff/inventory/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/staff/inventory/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/staff/inventory/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/staff/inventory/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/staff/inventory/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/staff/inventory/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/staff/inventory/create'
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
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/staff/inventory'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/staff/inventory',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/staff/inventory'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/staff/inventory'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/staff/inventory'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/staff/inventory'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/staff/inventory/{inventory}/edit'
 */
export const edit = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/staff/inventory/{inventory}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/staff/inventory/{inventory}/edit'
 */
edit.url = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { inventory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { inventory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    inventory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inventory: typeof args.inventory === 'object'
                ? args.inventory.id
                : args.inventory,
                }

    return edit.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/staff/inventory/{inventory}/edit'
 */
edit.get = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/staff/inventory/{inventory}/edit'
 */
edit.head = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/staff/inventory/{inventory}/edit'
 */
    const editForm = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/staff/inventory/{inventory}/edit'
 */
        editForm.get = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/staff/inventory/{inventory}/edit'
 */
        editForm.head = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:103
 * @route '/staff/inventory/{inventory}'
 */
export const update = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/staff/inventory/{inventory}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:103
 * @route '/staff/inventory/{inventory}'
 */
update.url = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { inventory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { inventory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    inventory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inventory: typeof args.inventory === 'object'
                ? args.inventory.id
                : args.inventory,
                }

    return update.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:103
 * @route '/staff/inventory/{inventory}'
 */
update.put = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:103
 * @route '/staff/inventory/{inventory}'
 */
    const updateForm = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:103
 * @route '/staff/inventory/{inventory}'
 */
        updateForm.put = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjust
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/staff/inventory/{inventory}/adjust'
 */
export const adjust = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adjust.url(args, options),
    method: 'post',
})

adjust.definition = {
    methods: ["post"],
    url: '/staff/inventory/{inventory}/adjust',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjust
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/staff/inventory/{inventory}/adjust'
 */
adjust.url = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { inventory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { inventory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    inventory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inventory: typeof args.inventory === 'object'
                ? args.inventory.id
                : args.inventory,
                }

    return adjust.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjust
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/staff/inventory/{inventory}/adjust'
 */
adjust.post = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adjust.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjust
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/staff/inventory/{inventory}/adjust'
 */
    const adjustForm = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: adjust.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjust
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/staff/inventory/{inventory}/adjust'
 */
        adjustForm.post = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: adjust.url(args, options),
            method: 'post',
        })
    
    adjust.form = adjustForm
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:116
 * @route '/staff/inventory/{inventory}'
 */
export const destroy = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/staff/inventory/{inventory}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:116
 * @route '/staff/inventory/{inventory}'
 */
destroy.url = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { inventory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { inventory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    inventory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inventory: typeof args.inventory === 'object'
                ? args.inventory.id
                : args.inventory,
                }

    return destroy.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:116
 * @route '/staff/inventory/{inventory}'
 */
destroy.delete = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:116
 * @route '/staff/inventory/{inventory}'
 */
    const destroyForm = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:116
 * @route '/staff/inventory/{inventory}'
 */
        destroyForm.delete = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/staff/inventory/{inventory}'
 */
export const show = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/staff/inventory/{inventory}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/staff/inventory/{inventory}'
 */
show.url = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { inventory: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { inventory: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    inventory: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        inventory: typeof args.inventory === 'object'
                ? args.inventory.id
                : args.inventory,
                }

    return show.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/staff/inventory/{inventory}'
 */
show.get = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/staff/inventory/{inventory}'
 */
show.head = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/staff/inventory/{inventory}'
 */
    const showForm = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/staff/inventory/{inventory}'
 */
        showForm.get = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/staff/inventory/{inventory}'
 */
        showForm.head = (args: { inventory: number | { id: number } } | [inventory: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
const inventory = {
    category: Object.assign(category, category),
index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
adjust: Object.assign(adjust, adjust),
destroy: Object.assign(destroy, destroy),
alerts: Object.assign(alerts, alerts),
show: Object.assign(show, show),
}

export default inventory