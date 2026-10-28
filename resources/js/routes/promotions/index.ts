import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/shop/operations/promos'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/operations/promos',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/shop/operations/promos'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/shop/operations/promos'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/shop/operations/promos'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/shop/operations/promos'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/shop/operations/promos'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/shop/operations/promos'
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
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/shop/operations/promos/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/shop/operations/promos/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/shop/operations/promos/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/shop/operations/promos/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/shop/operations/promos/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/shop/operations/promos/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/shop/operations/promos/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/shop/operations/promos/create'
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
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/shop/operations/promos'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/shop/operations/promos',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/shop/operations/promos'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/shop/operations/promos'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/shop/operations/promos'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/shop/operations/promos'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/shop/operations/promos/{promotion}/edit'
 */
export const edit = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/shop/operations/promos/{promotion}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/shop/operations/promos/{promotion}/edit'
 */
edit.url = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { promotion: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { promotion: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    promotion: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        promotion: typeof args.promotion === 'object'
                ? args.promotion.id
                : args.promotion,
                }

    return edit.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/shop/operations/promos/{promotion}/edit'
 */
edit.get = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/shop/operations/promos/{promotion}/edit'
 */
edit.head = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/shop/operations/promos/{promotion}/edit'
 */
    const editForm = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/shop/operations/promos/{promotion}/edit'
 */
        editForm.get = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/shop/operations/promos/{promotion}/edit'
 */
        editForm.head = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Shop\Operations\PromotionController::update
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:87
 * @route '/shop/operations/promos/{promotion}'
 */
export const update = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/shop/operations/promos/{promotion}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::update
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:87
 * @route '/shop/operations/promos/{promotion}'
 */
update.url = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { promotion: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { promotion: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    promotion: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        promotion: typeof args.promotion === 'object'
                ? args.promotion.id
                : args.promotion,
                }

    return update.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::update
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:87
 * @route '/shop/operations/promos/{promotion}'
 */
update.put = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::update
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:87
 * @route '/shop/operations/promos/{promotion}'
 */
    const updateForm = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::update
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:87
 * @route '/shop/operations/promos/{promotion}'
 */
        updateForm.put = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggle
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/shop/operations/promos/{promotion}/toggle'
 */
export const toggle = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

toggle.definition = {
    methods: ["patch"],
    url: '/shop/operations/promos/{promotion}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggle
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/shop/operations/promos/{promotion}/toggle'
 */
toggle.url = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { promotion: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { promotion: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    promotion: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        promotion: typeof args.promotion === 'object'
                ? args.promotion.id
                : args.promotion,
                }

    return toggle.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggle
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/shop/operations/promos/{promotion}/toggle'
 */
toggle.patch = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggle
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/shop/operations/promos/{promotion}/toggle'
 */
    const toggleForm = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggle.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggle
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/shop/operations/promos/{promotion}/toggle'
 */
        toggleForm.patch = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggle.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    toggle.form = toggleForm
/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::destroy
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:120
 * @route '/shop/operations/promos/{promotion}'
 */
export const destroy = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/shop/operations/promos/{promotion}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::destroy
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:120
 * @route '/shop/operations/promos/{promotion}'
 */
destroy.url = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { promotion: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { promotion: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    promotion: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        promotion: typeof args.promotion === 'object'
                ? args.promotion.id
                : args.promotion,
                }

    return destroy.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::destroy
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:120
 * @route '/shop/operations/promos/{promotion}'
 */
destroy.delete = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::destroy
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:120
 * @route '/shop/operations/promos/{promotion}'
 */
    const destroyForm = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::destroy
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:120
 * @route '/shop/operations/promos/{promotion}'
 */
        destroyForm.delete = (args: { promotion: number | { id: number } } | [promotion: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Operations\PromotionController::restore
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:130
 * @route '/shop/operations/promos/{promotion}/restore'
 */
export const restore = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore.url(args, options),
    method: 'patch',
})

restore.definition = {
    methods: ["patch"],
    url: '/shop/operations/promos/{promotion}/restore',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::restore
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:130
 * @route '/shop/operations/promos/{promotion}/restore'
 */
restore.url = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { promotion: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    promotion: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        promotion: args.promotion,
                }

    return restore.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::restore
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:130
 * @route '/shop/operations/promos/{promotion}/restore'
 */
restore.patch = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::restore
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:130
 * @route '/shop/operations/promos/{promotion}/restore'
 */
    const restoreForm = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::restore
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:130
 * @route '/shop/operations/promos/{promotion}/restore'
 */
        restoreForm.patch = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    restore.form = restoreForm
const promotions = {
    index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
toggle: Object.assign(toggle, toggle),
destroy: Object.assign(destroy, destroy),
restore: Object.assign(restore, restore),
}

export default promotions