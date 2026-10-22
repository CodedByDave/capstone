import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import payments from './payments'
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:58
 * @route '/shop/operations/orders'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/operations/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:58
 * @route '/shop/operations/orders'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:58
 * @route '/shop/operations/orders'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:58
 * @route '/shop/operations/orders'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:58
 * @route '/shop/operations/orders'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:58
 * @route '/shop/operations/orders'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:58
 * @route '/shop/operations/orders'
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
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:80
 * @route '/shop/operations/orders/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/shop/operations/orders/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:80
 * @route '/shop/operations/orders/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:80
 * @route '/shop/operations/orders/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:80
 * @route '/shop/operations/orders/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:80
 * @route '/shop/operations/orders/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:80
 * @route '/shop/operations/orders/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:80
 * @route '/shop/operations/orders/create'
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
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:105
 * @route '/shop/operations/orders'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/shop/operations/orders',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:105
 * @route '/shop/operations/orders'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:105
 * @route '/shop/operations/orders'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:105
 * @route '/shop/operations/orders'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:105
 * @route '/shop/operations/orders'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:125
 * @route '/shop/operations/orders/{order}'
 */
export const show = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/shop/operations/orders/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:125
 * @route '/shop/operations/orders/{order}'
 */
show.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return show.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:125
 * @route '/shop/operations/orders/{order}'
 */
show.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:125
 * @route '/shop/operations/orders/{order}'
 */
show.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:125
 * @route '/shop/operations/orders/{order}'
 */
    const showForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:125
 * @route '/shop/operations/orders/{order}'
 */
        showForm.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:125
 * @route '/shop/operations/orders/{order}'
 */
        showForm.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:184
 * @route '/shop/operations/orders/{order}/edit'
 */
export const edit = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/shop/operations/orders/{order}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:184
 * @route '/shop/operations/orders/{order}/edit'
 */
edit.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return edit.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:184
 * @route '/shop/operations/orders/{order}/edit'
 */
edit.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:184
 * @route '/shop/operations/orders/{order}/edit'
 */
edit.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:184
 * @route '/shop/operations/orders/{order}/edit'
 */
    const editForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:184
 * @route '/shop/operations/orders/{order}/edit'
 */
        editForm.get = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:184
 * @route '/shop/operations/orders/{order}/edit'
 */
        editForm.head = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:211
 * @route '/shop/operations/orders/{order}'
 */
export const update = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/shop/operations/orders/{order}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:211
 * @route '/shop/operations/orders/{order}'
 */
update.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return update.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:211
 * @route '/shop/operations/orders/{order}'
 */
update.put = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:211
 * @route '/shop/operations/orders/{order}'
 */
    const updateForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:211
 * @route '/shop/operations/orders/{order}'
 */
        updateForm.put = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:272
 * @route '/shop/operations/orders/{order}'
 */
export const destroy = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/shop/operations/orders/{order}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:272
 * @route '/shop/operations/orders/{order}'
 */
destroy.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return destroy.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:272
 * @route '/shop/operations/orders/{order}'
 */
destroy.delete = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:272
 * @route '/shop/operations/orders/{order}'
 */
    const destroyForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:272
 * @route '/shop/operations/orders/{order}'
 */
        destroyForm.delete = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::paymentRequest
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:154
 * @route '/shop/operations/orders/{order}/payment-request'
 */
export const paymentRequest = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: paymentRequest.url(args, options),
    method: 'post',
})

paymentRequest.definition = {
    methods: ["post"],
    url: '/shop/operations/orders/{order}/payment-request',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::paymentRequest
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:154
 * @route '/shop/operations/orders/{order}/payment-request'
 */
paymentRequest.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return paymentRequest.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::paymentRequest
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:154
 * @route '/shop/operations/orders/{order}/payment-request'
 */
paymentRequest.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: paymentRequest.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::paymentRequest
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:154
 * @route '/shop/operations/orders/{order}/payment-request'
 */
    const paymentRequestForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: paymentRequest.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::paymentRequest
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:154
 * @route '/shop/operations/orders/{order}/payment-request'
 */
        paymentRequestForm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: paymentRequest.url(args, options),
            method: 'post',
        })
    
    paymentRequest.form = paymentRequestForm
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:142
 * @route '/shop/operations/orders/{order}/send-for-delivery'
 */
export const sendForDelivery = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendForDelivery.url(args, options),
    method: 'post',
})

sendForDelivery.definition = {
    methods: ["post"],
    url: '/shop/operations/orders/{order}/send-for-delivery',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:142
 * @route '/shop/operations/orders/{order}/send-for-delivery'
 */
sendForDelivery.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return sendForDelivery.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:142
 * @route '/shop/operations/orders/{order}/send-for-delivery'
 */
sendForDelivery.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendForDelivery.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:142
 * @route '/shop/operations/orders/{order}/send-for-delivery'
 */
    const sendForDeliveryForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: sendForDelivery.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:142
 * @route '/shop/operations/orders/{order}/send-for-delivery'
 */
        sendForDeliveryForm.post = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: sendForDelivery.url(args, options),
            method: 'post',
        })
    
    sendForDelivery.form = sendForDeliveryForm
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:295
 * @route '/shop/operations/orders/restore-all'
 */
export const restoreAll = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreAll.url(options),
    method: 'post',
})

restoreAll.definition = {
    methods: ["post"],
    url: '/shop/operations/orders/restore-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:295
 * @route '/shop/operations/orders/restore-all'
 */
restoreAll.url = (options?: RouteQueryOptions) => {
    return restoreAll.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:295
 * @route '/shop/operations/orders/restore-all'
 */
restoreAll.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreAll.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:295
 * @route '/shop/operations/orders/restore-all'
 */
    const restoreAllForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restoreAll.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:295
 * @route '/shop/operations/orders/restore-all'
 */
        restoreAllForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restoreAll.url(options),
            method: 'post',
        })
    
    restoreAll.form = restoreAllForm
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:283
 * @route '/shop/operations/orders/{order}/restore'
 */
export const restore = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore.url(args, options),
    method: 'patch',
})

restore.definition = {
    methods: ["patch"],
    url: '/shop/operations/orders/{order}/restore',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:283
 * @route '/shop/operations/orders/{order}/restore'
 */
restore.url = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { order: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.id
                : args.order,
                }

    return restore.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:283
 * @route '/shop/operations/orders/{order}/restore'
 */
restore.patch = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:283
 * @route '/shop/operations/orders/{order}/restore'
 */
    const restoreForm = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:283
 * @route '/shop/operations/orders/{order}/restore'
 */
        restoreForm.patch = (args: { order: number | { id: number } } | [order: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    restore.form = restoreForm
const orders = {
    index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
show: Object.assign(show, show),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
paymentRequest: Object.assign(paymentRequest, paymentRequest),
payments: Object.assign(payments, payments),
sendForDelivery: Object.assign(sendForDelivery, sendForDelivery),
restoreAll: Object.assign(restoreAll, restoreAll),
restore: Object.assign(restore, restore),
}

export default orders