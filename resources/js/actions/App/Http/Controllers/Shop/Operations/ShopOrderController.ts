import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:54
 * @route '/shop/operations/orders'
 */
const index3046662a2b56aac3a381595943a566e4 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index3046662a2b56aac3a381595943a566e4.url(options),
    method: 'get',
})

index3046662a2b56aac3a381595943a566e4.definition = {
    methods: ["get","head"],
    url: '/shop/operations/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:54
 * @route '/shop/operations/orders'
 */
index3046662a2b56aac3a381595943a566e4.url = (options?: RouteQueryOptions) => {
    return index3046662a2b56aac3a381595943a566e4.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:54
 * @route '/shop/operations/orders'
 */
index3046662a2b56aac3a381595943a566e4.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index3046662a2b56aac3a381595943a566e4.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:54
 * @route '/shop/operations/orders'
 */
index3046662a2b56aac3a381595943a566e4.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index3046662a2b56aac3a381595943a566e4.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:54
 * @route '/shop/operations/orders'
 */
    const index3046662a2b56aac3a381595943a566e4Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index3046662a2b56aac3a381595943a566e4.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:54
 * @route '/shop/operations/orders'
 */
        index3046662a2b56aac3a381595943a566e4Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index3046662a2b56aac3a381595943a566e4.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:54
 * @route '/shop/operations/orders'
 */
        index3046662a2b56aac3a381595943a566e4Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index3046662a2b56aac3a381595943a566e4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index3046662a2b56aac3a381595943a566e4.form = index3046662a2b56aac3a381595943a566e4Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:54
 * @route '/staff/operations/orders'
 */
const index1ca873fe54e10e608543305c9bad9bf6 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index1ca873fe54e10e608543305c9bad9bf6.url(options),
    method: 'get',
})

index1ca873fe54e10e608543305c9bad9bf6.definition = {
    methods: ["get","head"],
    url: '/staff/operations/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:54
 * @route '/staff/operations/orders'
 */
index1ca873fe54e10e608543305c9bad9bf6.url = (options?: RouteQueryOptions) => {
    return index1ca873fe54e10e608543305c9bad9bf6.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:54
 * @route '/staff/operations/orders'
 */
index1ca873fe54e10e608543305c9bad9bf6.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index1ca873fe54e10e608543305c9bad9bf6.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:54
 * @route '/staff/operations/orders'
 */
index1ca873fe54e10e608543305c9bad9bf6.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index1ca873fe54e10e608543305c9bad9bf6.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:54
 * @route '/staff/operations/orders'
 */
    const index1ca873fe54e10e608543305c9bad9bf6Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index1ca873fe54e10e608543305c9bad9bf6.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:54
 * @route '/staff/operations/orders'
 */
        index1ca873fe54e10e608543305c9bad9bf6Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index1ca873fe54e10e608543305c9bad9bf6.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::index
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:54
 * @route '/staff/operations/orders'
 */
        index1ca873fe54e10e608543305c9bad9bf6Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index1ca873fe54e10e608543305c9bad9bf6.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index1ca873fe54e10e608543305c9bad9bf6.form = index1ca873fe54e10e608543305c9bad9bf6Form

export const index = {
    '/shop/operations/orders': index3046662a2b56aac3a381595943a566e4,
    '/staff/operations/orders': index1ca873fe54e10e608543305c9bad9bf6,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:74
 * @route '/shop/operations/orders/create'
 */
const create203d115f540b6dfaa806642627917908 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create203d115f540b6dfaa806642627917908.url(options),
    method: 'get',
})

create203d115f540b6dfaa806642627917908.definition = {
    methods: ["get","head"],
    url: '/shop/operations/orders/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:74
 * @route '/shop/operations/orders/create'
 */
create203d115f540b6dfaa806642627917908.url = (options?: RouteQueryOptions) => {
    return create203d115f540b6dfaa806642627917908.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:74
 * @route '/shop/operations/orders/create'
 */
create203d115f540b6dfaa806642627917908.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create203d115f540b6dfaa806642627917908.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:74
 * @route '/shop/operations/orders/create'
 */
create203d115f540b6dfaa806642627917908.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create203d115f540b6dfaa806642627917908.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:74
 * @route '/shop/operations/orders/create'
 */
    const create203d115f540b6dfaa806642627917908Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create203d115f540b6dfaa806642627917908.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:74
 * @route '/shop/operations/orders/create'
 */
        create203d115f540b6dfaa806642627917908Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create203d115f540b6dfaa806642627917908.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:74
 * @route '/shop/operations/orders/create'
 */
        create203d115f540b6dfaa806642627917908Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create203d115f540b6dfaa806642627917908.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create203d115f540b6dfaa806642627917908.form = create203d115f540b6dfaa806642627917908Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:74
 * @route '/staff/operations/orders/create'
 */
const createba311394b42e87ea949046d6a4ae45c8 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: createba311394b42e87ea949046d6a4ae45c8.url(options),
    method: 'get',
})

createba311394b42e87ea949046d6a4ae45c8.definition = {
    methods: ["get","head"],
    url: '/staff/operations/orders/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:74
 * @route '/staff/operations/orders/create'
 */
createba311394b42e87ea949046d6a4ae45c8.url = (options?: RouteQueryOptions) => {
    return createba311394b42e87ea949046d6a4ae45c8.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:74
 * @route '/staff/operations/orders/create'
 */
createba311394b42e87ea949046d6a4ae45c8.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: createba311394b42e87ea949046d6a4ae45c8.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:74
 * @route '/staff/operations/orders/create'
 */
createba311394b42e87ea949046d6a4ae45c8.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: createba311394b42e87ea949046d6a4ae45c8.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:74
 * @route '/staff/operations/orders/create'
 */
    const createba311394b42e87ea949046d6a4ae45c8Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: createba311394b42e87ea949046d6a4ae45c8.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:74
 * @route '/staff/operations/orders/create'
 */
        createba311394b42e87ea949046d6a4ae45c8Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: createba311394b42e87ea949046d6a4ae45c8.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::create
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:74
 * @route '/staff/operations/orders/create'
 */
        createba311394b42e87ea949046d6a4ae45c8Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: createba311394b42e87ea949046d6a4ae45c8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    createba311394b42e87ea949046d6a4ae45c8.form = createba311394b42e87ea949046d6a4ae45c8Form

export const create = {
    '/shop/operations/orders/create': create203d115f540b6dfaa806642627917908,
    '/staff/operations/orders/create': createba311394b42e87ea949046d6a4ae45c8,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:101
 * @route '/shop/operations/orders'
 */
const store3046662a2b56aac3a381595943a566e4 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store3046662a2b56aac3a381595943a566e4.url(options),
    method: 'post',
})

store3046662a2b56aac3a381595943a566e4.definition = {
    methods: ["post"],
    url: '/shop/operations/orders',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:101
 * @route '/shop/operations/orders'
 */
store3046662a2b56aac3a381595943a566e4.url = (options?: RouteQueryOptions) => {
    return store3046662a2b56aac3a381595943a566e4.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:101
 * @route '/shop/operations/orders'
 */
store3046662a2b56aac3a381595943a566e4.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store3046662a2b56aac3a381595943a566e4.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:101
 * @route '/shop/operations/orders'
 */
    const store3046662a2b56aac3a381595943a566e4Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store3046662a2b56aac3a381595943a566e4.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:101
 * @route '/shop/operations/orders'
 */
        store3046662a2b56aac3a381595943a566e4Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store3046662a2b56aac3a381595943a566e4.url(options),
            method: 'post',
        })
    
    store3046662a2b56aac3a381595943a566e4.form = store3046662a2b56aac3a381595943a566e4Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:101
 * @route '/staff/operations/orders'
 */
const store1ca873fe54e10e608543305c9bad9bf6 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store1ca873fe54e10e608543305c9bad9bf6.url(options),
    method: 'post',
})

store1ca873fe54e10e608543305c9bad9bf6.definition = {
    methods: ["post"],
    url: '/staff/operations/orders',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:101
 * @route '/staff/operations/orders'
 */
store1ca873fe54e10e608543305c9bad9bf6.url = (options?: RouteQueryOptions) => {
    return store1ca873fe54e10e608543305c9bad9bf6.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:101
 * @route '/staff/operations/orders'
 */
store1ca873fe54e10e608543305c9bad9bf6.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store1ca873fe54e10e608543305c9bad9bf6.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:101
 * @route '/staff/operations/orders'
 */
    const store1ca873fe54e10e608543305c9bad9bf6Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store1ca873fe54e10e608543305c9bad9bf6.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::store
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:101
 * @route '/staff/operations/orders'
 */
        store1ca873fe54e10e608543305c9bad9bf6Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store1ca873fe54e10e608543305c9bad9bf6.url(options),
            method: 'post',
        })
    
    store1ca873fe54e10e608543305c9bad9bf6.form = store1ca873fe54e10e608543305c9bad9bf6Form

export const store = {
    '/shop/operations/orders': store3046662a2b56aac3a381595943a566e4,
    '/staff/operations/orders': store1ca873fe54e10e608543305c9bad9bf6,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:120
 * @route '/shop/operations/orders/{order}'
 */
const show27635f40a22aa7da9bdd32cbbfd0f9c0 = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show27635f40a22aa7da9bdd32cbbfd0f9c0.url(args, options),
    method: 'get',
})

show27635f40a22aa7da9bdd32cbbfd0f9c0.definition = {
    methods: ["get","head"],
    url: '/shop/operations/orders/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:120
 * @route '/shop/operations/orders/{order}'
 */
show27635f40a22aa7da9bdd32cbbfd0f9c0.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return show27635f40a22aa7da9bdd32cbbfd0f9c0.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:120
 * @route '/shop/operations/orders/{order}'
 */
show27635f40a22aa7da9bdd32cbbfd0f9c0.get = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show27635f40a22aa7da9bdd32cbbfd0f9c0.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:120
 * @route '/shop/operations/orders/{order}'
 */
show27635f40a22aa7da9bdd32cbbfd0f9c0.head = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show27635f40a22aa7da9bdd32cbbfd0f9c0.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:120
 * @route '/shop/operations/orders/{order}'
 */
    const show27635f40a22aa7da9bdd32cbbfd0f9c0Form = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show27635f40a22aa7da9bdd32cbbfd0f9c0.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:120
 * @route '/shop/operations/orders/{order}'
 */
        show27635f40a22aa7da9bdd32cbbfd0f9c0Form.get = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show27635f40a22aa7da9bdd32cbbfd0f9c0.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:120
 * @route '/shop/operations/orders/{order}'
 */
        show27635f40a22aa7da9bdd32cbbfd0f9c0Form.head = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show27635f40a22aa7da9bdd32cbbfd0f9c0.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show27635f40a22aa7da9bdd32cbbfd0f9c0.form = show27635f40a22aa7da9bdd32cbbfd0f9c0Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:120
 * @route '/staff/operations/orders/{order}'
 */
const show9b2a634ffe9535792984c712b770b227 = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show9b2a634ffe9535792984c712b770b227.url(args, options),
    method: 'get',
})

show9b2a634ffe9535792984c712b770b227.definition = {
    methods: ["get","head"],
    url: '/staff/operations/orders/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:120
 * @route '/staff/operations/orders/{order}'
 */
show9b2a634ffe9535792984c712b770b227.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return show9b2a634ffe9535792984c712b770b227.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:120
 * @route '/staff/operations/orders/{order}'
 */
show9b2a634ffe9535792984c712b770b227.get = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show9b2a634ffe9535792984c712b770b227.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:120
 * @route '/staff/operations/orders/{order}'
 */
show9b2a634ffe9535792984c712b770b227.head = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show9b2a634ffe9535792984c712b770b227.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:120
 * @route '/staff/operations/orders/{order}'
 */
    const show9b2a634ffe9535792984c712b770b227Form = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show9b2a634ffe9535792984c712b770b227.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:120
 * @route '/staff/operations/orders/{order}'
 */
        show9b2a634ffe9535792984c712b770b227Form.get = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show9b2a634ffe9535792984c712b770b227.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::show
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:120
 * @route '/staff/operations/orders/{order}'
 */
        show9b2a634ffe9535792984c712b770b227Form.head = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show9b2a634ffe9535792984c712b770b227.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show9b2a634ffe9535792984c712b770b227.form = show9b2a634ffe9535792984c712b770b227Form

export const show = {
    '/shop/operations/orders/{order}': show27635f40a22aa7da9bdd32cbbfd0f9c0,
    '/staff/operations/orders/{order}': show9b2a634ffe9535792984c712b770b227,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:144
 * @route '/shop/operations/orders/{order}/edit'
 */
const edit8553c4209e292d88c8408c54f41397fe = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit8553c4209e292d88c8408c54f41397fe.url(args, options),
    method: 'get',
})

edit8553c4209e292d88c8408c54f41397fe.definition = {
    methods: ["get","head"],
    url: '/shop/operations/orders/{order}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:144
 * @route '/shop/operations/orders/{order}/edit'
 */
edit8553c4209e292d88c8408c54f41397fe.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return edit8553c4209e292d88c8408c54f41397fe.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:144
 * @route '/shop/operations/orders/{order}/edit'
 */
edit8553c4209e292d88c8408c54f41397fe.get = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit8553c4209e292d88c8408c54f41397fe.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:144
 * @route '/shop/operations/orders/{order}/edit'
 */
edit8553c4209e292d88c8408c54f41397fe.head = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit8553c4209e292d88c8408c54f41397fe.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:144
 * @route '/shop/operations/orders/{order}/edit'
 */
    const edit8553c4209e292d88c8408c54f41397feForm = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit8553c4209e292d88c8408c54f41397fe.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:144
 * @route '/shop/operations/orders/{order}/edit'
 */
        edit8553c4209e292d88c8408c54f41397feForm.get = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit8553c4209e292d88c8408c54f41397fe.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:144
 * @route '/shop/operations/orders/{order}/edit'
 */
        edit8553c4209e292d88c8408c54f41397feForm.head = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit8553c4209e292d88c8408c54f41397fe.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit8553c4209e292d88c8408c54f41397fe.form = edit8553c4209e292d88c8408c54f41397feForm
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:144
 * @route '/staff/operations/orders/{order}/edit'
 */
const edit223e40850db6d7ee7cc15c6cb055c2ad = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit223e40850db6d7ee7cc15c6cb055c2ad.url(args, options),
    method: 'get',
})

edit223e40850db6d7ee7cc15c6cb055c2ad.definition = {
    methods: ["get","head"],
    url: '/staff/operations/orders/{order}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:144
 * @route '/staff/operations/orders/{order}/edit'
 */
edit223e40850db6d7ee7cc15c6cb055c2ad.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return edit223e40850db6d7ee7cc15c6cb055c2ad.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:144
 * @route '/staff/operations/orders/{order}/edit'
 */
edit223e40850db6d7ee7cc15c6cb055c2ad.get = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit223e40850db6d7ee7cc15c6cb055c2ad.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:144
 * @route '/staff/operations/orders/{order}/edit'
 */
edit223e40850db6d7ee7cc15c6cb055c2ad.head = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit223e40850db6d7ee7cc15c6cb055c2ad.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:144
 * @route '/staff/operations/orders/{order}/edit'
 */
    const edit223e40850db6d7ee7cc15c6cb055c2adForm = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit223e40850db6d7ee7cc15c6cb055c2ad.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:144
 * @route '/staff/operations/orders/{order}/edit'
 */
        edit223e40850db6d7ee7cc15c6cb055c2adForm.get = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit223e40850db6d7ee7cc15c6cb055c2ad.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:144
 * @route '/staff/operations/orders/{order}/edit'
 */
        edit223e40850db6d7ee7cc15c6cb055c2adForm.head = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit223e40850db6d7ee7cc15c6cb055c2ad.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit223e40850db6d7ee7cc15c6cb055c2ad.form = edit223e40850db6d7ee7cc15c6cb055c2adForm

export const edit = {
    '/shop/operations/orders/{order}/edit': edit8553c4209e292d88c8408c54f41397fe,
    '/staff/operations/orders/{order}/edit': edit223e40850db6d7ee7cc15c6cb055c2ad,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:172
 * @route '/shop/operations/orders/{order}'
 */
const update27635f40a22aa7da9bdd32cbbfd0f9c0 = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update27635f40a22aa7da9bdd32cbbfd0f9c0.url(args, options),
    method: 'put',
})

update27635f40a22aa7da9bdd32cbbfd0f9c0.definition = {
    methods: ["put"],
    url: '/shop/operations/orders/{order}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:172
 * @route '/shop/operations/orders/{order}'
 */
update27635f40a22aa7da9bdd32cbbfd0f9c0.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return update27635f40a22aa7da9bdd32cbbfd0f9c0.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:172
 * @route '/shop/operations/orders/{order}'
 */
update27635f40a22aa7da9bdd32cbbfd0f9c0.put = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update27635f40a22aa7da9bdd32cbbfd0f9c0.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:172
 * @route '/shop/operations/orders/{order}'
 */
    const update27635f40a22aa7da9bdd32cbbfd0f9c0Form = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update27635f40a22aa7da9bdd32cbbfd0f9c0.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:172
 * @route '/shop/operations/orders/{order}'
 */
        update27635f40a22aa7da9bdd32cbbfd0f9c0Form.put = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update27635f40a22aa7da9bdd32cbbfd0f9c0.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update27635f40a22aa7da9bdd32cbbfd0f9c0.form = update27635f40a22aa7da9bdd32cbbfd0f9c0Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:172
 * @route '/staff/operations/orders/{order}'
 */
const update9b2a634ffe9535792984c712b770b227 = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update9b2a634ffe9535792984c712b770b227.url(args, options),
    method: 'put',
})

update9b2a634ffe9535792984c712b770b227.definition = {
    methods: ["put"],
    url: '/staff/operations/orders/{order}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:172
 * @route '/staff/operations/orders/{order}'
 */
update9b2a634ffe9535792984c712b770b227.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return update9b2a634ffe9535792984c712b770b227.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:172
 * @route '/staff/operations/orders/{order}'
 */
update9b2a634ffe9535792984c712b770b227.put = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update9b2a634ffe9535792984c712b770b227.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:172
 * @route '/staff/operations/orders/{order}'
 */
    const update9b2a634ffe9535792984c712b770b227Form = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update9b2a634ffe9535792984c712b770b227.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::update
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:172
 * @route '/staff/operations/orders/{order}'
 */
        update9b2a634ffe9535792984c712b770b227Form.put = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update9b2a634ffe9535792984c712b770b227.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update9b2a634ffe9535792984c712b770b227.form = update9b2a634ffe9535792984c712b770b227Form

export const update = {
    '/shop/operations/orders/{order}': update27635f40a22aa7da9bdd32cbbfd0f9c0,
    '/staff/operations/orders/{order}': update9b2a634ffe9535792984c712b770b227,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:229
 * @route '/shop/operations/orders/{order}'
 */
const destroy27635f40a22aa7da9bdd32cbbfd0f9c0 = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy27635f40a22aa7da9bdd32cbbfd0f9c0.url(args, options),
    method: 'delete',
})

destroy27635f40a22aa7da9bdd32cbbfd0f9c0.definition = {
    methods: ["delete"],
    url: '/shop/operations/orders/{order}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:229
 * @route '/shop/operations/orders/{order}'
 */
destroy27635f40a22aa7da9bdd32cbbfd0f9c0.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return destroy27635f40a22aa7da9bdd32cbbfd0f9c0.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:229
 * @route '/shop/operations/orders/{order}'
 */
destroy27635f40a22aa7da9bdd32cbbfd0f9c0.delete = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy27635f40a22aa7da9bdd32cbbfd0f9c0.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:229
 * @route '/shop/operations/orders/{order}'
 */
    const destroy27635f40a22aa7da9bdd32cbbfd0f9c0Form = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy27635f40a22aa7da9bdd32cbbfd0f9c0.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:229
 * @route '/shop/operations/orders/{order}'
 */
        destroy27635f40a22aa7da9bdd32cbbfd0f9c0Form.delete = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy27635f40a22aa7da9bdd32cbbfd0f9c0.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy27635f40a22aa7da9bdd32cbbfd0f9c0.form = destroy27635f40a22aa7da9bdd32cbbfd0f9c0Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:229
 * @route '/staff/operations/orders/{order}'
 */
const destroy9b2a634ffe9535792984c712b770b227 = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy9b2a634ffe9535792984c712b770b227.url(args, options),
    method: 'delete',
})

destroy9b2a634ffe9535792984c712b770b227.definition = {
    methods: ["delete"],
    url: '/staff/operations/orders/{order}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:229
 * @route '/staff/operations/orders/{order}'
 */
destroy9b2a634ffe9535792984c712b770b227.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return destroy9b2a634ffe9535792984c712b770b227.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:229
 * @route '/staff/operations/orders/{order}'
 */
destroy9b2a634ffe9535792984c712b770b227.delete = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy9b2a634ffe9535792984c712b770b227.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:229
 * @route '/staff/operations/orders/{order}'
 */
    const destroy9b2a634ffe9535792984c712b770b227Form = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy9b2a634ffe9535792984c712b770b227.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:229
 * @route '/staff/operations/orders/{order}'
 */
        destroy9b2a634ffe9535792984c712b770b227Form.delete = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy9b2a634ffe9535792984c712b770b227.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy9b2a634ffe9535792984c712b770b227.form = destroy9b2a634ffe9535792984c712b770b227Form

export const destroy = {
    '/shop/operations/orders/{order}': destroy27635f40a22aa7da9bdd32cbbfd0f9c0,
    '/staff/operations/orders/{order}': destroy9b2a634ffe9535792984c712b770b227,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::recordPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:193
 * @route '/shop/operations/orders/{order}/payments'
 */
const recordPaymentf3b50fd002e9152ec0cf94bde684bdbc = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recordPaymentf3b50fd002e9152ec0cf94bde684bdbc.url(args, options),
    method: 'post',
})

recordPaymentf3b50fd002e9152ec0cf94bde684bdbc.definition = {
    methods: ["post"],
    url: '/shop/operations/orders/{order}/payments',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::recordPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:193
 * @route '/shop/operations/orders/{order}/payments'
 */
recordPaymentf3b50fd002e9152ec0cf94bde684bdbc.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return recordPaymentf3b50fd002e9152ec0cf94bde684bdbc.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::recordPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:193
 * @route '/shop/operations/orders/{order}/payments'
 */
recordPaymentf3b50fd002e9152ec0cf94bde684bdbc.post = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recordPaymentf3b50fd002e9152ec0cf94bde684bdbc.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::recordPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:193
 * @route '/shop/operations/orders/{order}/payments'
 */
    const recordPaymentf3b50fd002e9152ec0cf94bde684bdbcForm = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: recordPaymentf3b50fd002e9152ec0cf94bde684bdbc.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::recordPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:193
 * @route '/shop/operations/orders/{order}/payments'
 */
        recordPaymentf3b50fd002e9152ec0cf94bde684bdbcForm.post = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: recordPaymentf3b50fd002e9152ec0cf94bde684bdbc.url(args, options),
            method: 'post',
        })
    
    recordPaymentf3b50fd002e9152ec0cf94bde684bdbc.form = recordPaymentf3b50fd002e9152ec0cf94bde684bdbcForm
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::recordPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:193
 * @route '/staff/operations/orders/{order}/payments'
 */
const recordPaymenta507d8de96b348dcc09a8ac7624e92c6 = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recordPaymenta507d8de96b348dcc09a8ac7624e92c6.url(args, options),
    method: 'post',
})

recordPaymenta507d8de96b348dcc09a8ac7624e92c6.definition = {
    methods: ["post"],
    url: '/staff/operations/orders/{order}/payments',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::recordPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:193
 * @route '/staff/operations/orders/{order}/payments'
 */
recordPaymenta507d8de96b348dcc09a8ac7624e92c6.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return recordPaymenta507d8de96b348dcc09a8ac7624e92c6.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::recordPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:193
 * @route '/staff/operations/orders/{order}/payments'
 */
recordPaymenta507d8de96b348dcc09a8ac7624e92c6.post = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: recordPaymenta507d8de96b348dcc09a8ac7624e92c6.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::recordPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:193
 * @route '/staff/operations/orders/{order}/payments'
 */
    const recordPaymenta507d8de96b348dcc09a8ac7624e92c6Form = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: recordPaymenta507d8de96b348dcc09a8ac7624e92c6.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::recordPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:193
 * @route '/staff/operations/orders/{order}/payments'
 */
        recordPaymenta507d8de96b348dcc09a8ac7624e92c6Form.post = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: recordPaymenta507d8de96b348dcc09a8ac7624e92c6.url(args, options),
            method: 'post',
        })
    
    recordPaymenta507d8de96b348dcc09a8ac7624e92c6.form = recordPaymenta507d8de96b348dcc09a8ac7624e92c6Form

export const recordPayment = {
    '/shop/operations/orders/{order}/payments': recordPaymentf3b50fd002e9152ec0cf94bde684bdbc,
    '/staff/operations/orders/{order}/payments': recordPaymenta507d8de96b348dcc09a8ac7624e92c6,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::checkPayments
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:215
 * @route '/shop/operations/orders/{order}/payments/check'
 */
const checkPayments54904557fbb5997b3d93aca95f48b84d = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkPayments54904557fbb5997b3d93aca95f48b84d.url(args, options),
    method: 'post',
})

checkPayments54904557fbb5997b3d93aca95f48b84d.definition = {
    methods: ["post"],
    url: '/shop/operations/orders/{order}/payments/check',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::checkPayments
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:215
 * @route '/shop/operations/orders/{order}/payments/check'
 */
checkPayments54904557fbb5997b3d93aca95f48b84d.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return checkPayments54904557fbb5997b3d93aca95f48b84d.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::checkPayments
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:215
 * @route '/shop/operations/orders/{order}/payments/check'
 */
checkPayments54904557fbb5997b3d93aca95f48b84d.post = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkPayments54904557fbb5997b3d93aca95f48b84d.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::checkPayments
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:215
 * @route '/shop/operations/orders/{order}/payments/check'
 */
    const checkPayments54904557fbb5997b3d93aca95f48b84dForm = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: checkPayments54904557fbb5997b3d93aca95f48b84d.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::checkPayments
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:215
 * @route '/shop/operations/orders/{order}/payments/check'
 */
        checkPayments54904557fbb5997b3d93aca95f48b84dForm.post = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: checkPayments54904557fbb5997b3d93aca95f48b84d.url(args, options),
            method: 'post',
        })
    
    checkPayments54904557fbb5997b3d93aca95f48b84d.form = checkPayments54904557fbb5997b3d93aca95f48b84dForm
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::checkPayments
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:215
 * @route '/staff/operations/orders/{order}/payments/check'
 */
const checkPayments70b0be731a2ff95d09c41d406c741384 = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkPayments70b0be731a2ff95d09c41d406c741384.url(args, options),
    method: 'post',
})

checkPayments70b0be731a2ff95d09c41d406c741384.definition = {
    methods: ["post"],
    url: '/staff/operations/orders/{order}/payments/check',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::checkPayments
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:215
 * @route '/staff/operations/orders/{order}/payments/check'
 */
checkPayments70b0be731a2ff95d09c41d406c741384.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return checkPayments70b0be731a2ff95d09c41d406c741384.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::checkPayments
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:215
 * @route '/staff/operations/orders/{order}/payments/check'
 */
checkPayments70b0be731a2ff95d09c41d406c741384.post = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkPayments70b0be731a2ff95d09c41d406c741384.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::checkPayments
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:215
 * @route '/staff/operations/orders/{order}/payments/check'
 */
    const checkPayments70b0be731a2ff95d09c41d406c741384Form = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: checkPayments70b0be731a2ff95d09c41d406c741384.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::checkPayments
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:215
 * @route '/staff/operations/orders/{order}/payments/check'
 */
        checkPayments70b0be731a2ff95d09c41d406c741384Form.post = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: checkPayments70b0be731a2ff95d09c41d406c741384.url(args, options),
            method: 'post',
        })
    
    checkPayments70b0be731a2ff95d09c41d406c741384.form = checkPayments70b0be731a2ff95d09c41d406c741384Form

export const checkPayments = {
    '/shop/operations/orders/{order}/payments/check': checkPayments54904557fbb5997b3d93aca95f48b84d,
    '/staff/operations/orders/{order}/payments/check': checkPayments70b0be731a2ff95d09c41d406c741384,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::voidPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:205
 * @route '/shop/operations/orders/{order}/payments/{payment}/void'
 */
const voidPaymentfe4c03a56e701e772aa589eb2efefa65 = (args: { order: string | { payment_public_id: string }, payment: string | number } | [order: string | { payment_public_id: string }, payment: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: voidPaymentfe4c03a56e701e772aa589eb2efefa65.url(args, options),
    method: 'post',
})

voidPaymentfe4c03a56e701e772aa589eb2efefa65.definition = {
    methods: ["post"],
    url: '/shop/operations/orders/{order}/payments/{payment}/void',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::voidPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:205
 * @route '/shop/operations/orders/{order}/payments/{payment}/void'
 */
voidPaymentfe4c03a56e701e772aa589eb2efefa65.url = (args: { order: string | { payment_public_id: string }, payment: string | number } | [order: string | { payment_public_id: string }, payment: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                    payment: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                                payment: args.payment,
                }

    return voidPaymentfe4c03a56e701e772aa589eb2efefa65.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace('{payment}', parsedArgs.payment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::voidPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:205
 * @route '/shop/operations/orders/{order}/payments/{payment}/void'
 */
voidPaymentfe4c03a56e701e772aa589eb2efefa65.post = (args: { order: string | { payment_public_id: string }, payment: string | number } | [order: string | { payment_public_id: string }, payment: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: voidPaymentfe4c03a56e701e772aa589eb2efefa65.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::voidPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:205
 * @route '/shop/operations/orders/{order}/payments/{payment}/void'
 */
    const voidPaymentfe4c03a56e701e772aa589eb2efefa65Form = (args: { order: string | { payment_public_id: string }, payment: string | number } | [order: string | { payment_public_id: string }, payment: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: voidPaymentfe4c03a56e701e772aa589eb2efefa65.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::voidPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:205
 * @route '/shop/operations/orders/{order}/payments/{payment}/void'
 */
        voidPaymentfe4c03a56e701e772aa589eb2efefa65Form.post = (args: { order: string | { payment_public_id: string }, payment: string | number } | [order: string | { payment_public_id: string }, payment: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: voidPaymentfe4c03a56e701e772aa589eb2efefa65.url(args, options),
            method: 'post',
        })
    
    voidPaymentfe4c03a56e701e772aa589eb2efefa65.form = voidPaymentfe4c03a56e701e772aa589eb2efefa65Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::voidPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:205
 * @route '/staff/operations/orders/{order}/payments/{payment}/void'
 */
const voidPayment2914ef91516307a77cce6e42afb4823a = (args: { order: string | { payment_public_id: string }, payment: string | number } | [order: string | { payment_public_id: string }, payment: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: voidPayment2914ef91516307a77cce6e42afb4823a.url(args, options),
    method: 'post',
})

voidPayment2914ef91516307a77cce6e42afb4823a.definition = {
    methods: ["post"],
    url: '/staff/operations/orders/{order}/payments/{payment}/void',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::voidPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:205
 * @route '/staff/operations/orders/{order}/payments/{payment}/void'
 */
voidPayment2914ef91516307a77cce6e42afb4823a.url = (args: { order: string | { payment_public_id: string }, payment: string | number } | [order: string | { payment_public_id: string }, payment: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                    payment: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                                payment: args.payment,
                }

    return voidPayment2914ef91516307a77cce6e42afb4823a.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace('{payment}', parsedArgs.payment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::voidPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:205
 * @route '/staff/operations/orders/{order}/payments/{payment}/void'
 */
voidPayment2914ef91516307a77cce6e42afb4823a.post = (args: { order: string | { payment_public_id: string }, payment: string | number } | [order: string | { payment_public_id: string }, payment: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: voidPayment2914ef91516307a77cce6e42afb4823a.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::voidPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:205
 * @route '/staff/operations/orders/{order}/payments/{payment}/void'
 */
    const voidPayment2914ef91516307a77cce6e42afb4823aForm = (args: { order: string | { payment_public_id: string }, payment: string | number } | [order: string | { payment_public_id: string }, payment: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: voidPayment2914ef91516307a77cce6e42afb4823a.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::voidPayment
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:205
 * @route '/staff/operations/orders/{order}/payments/{payment}/void'
 */
        voidPayment2914ef91516307a77cce6e42afb4823aForm.post = (args: { order: string | { payment_public_id: string }, payment: string | number } | [order: string | { payment_public_id: string }, payment: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: voidPayment2914ef91516307a77cce6e42afb4823a.url(args, options),
            method: 'post',
        })
    
    voidPayment2914ef91516307a77cce6e42afb4823a.form = voidPayment2914ef91516307a77cce6e42afb4823aForm

export const voidPayment = {
    '/shop/operations/orders/{order}/payments/{payment}/void': voidPaymentfe4c03a56e701e772aa589eb2efefa65,
    '/staff/operations/orders/{order}/payments/{payment}/void': voidPayment2914ef91516307a77cce6e42afb4823a,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:132
 * @route '/shop/operations/orders/{order}/send-for-delivery'
 */
const sendForDeliveryb2c05d83311c9a8ac191eb6dc84e5653 = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendForDeliveryb2c05d83311c9a8ac191eb6dc84e5653.url(args, options),
    method: 'post',
})

sendForDeliveryb2c05d83311c9a8ac191eb6dc84e5653.definition = {
    methods: ["post"],
    url: '/shop/operations/orders/{order}/send-for-delivery',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:132
 * @route '/shop/operations/orders/{order}/send-for-delivery'
 */
sendForDeliveryb2c05d83311c9a8ac191eb6dc84e5653.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return sendForDeliveryb2c05d83311c9a8ac191eb6dc84e5653.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:132
 * @route '/shop/operations/orders/{order}/send-for-delivery'
 */
sendForDeliveryb2c05d83311c9a8ac191eb6dc84e5653.post = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendForDeliveryb2c05d83311c9a8ac191eb6dc84e5653.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:132
 * @route '/shop/operations/orders/{order}/send-for-delivery'
 */
    const sendForDeliveryb2c05d83311c9a8ac191eb6dc84e5653Form = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: sendForDeliveryb2c05d83311c9a8ac191eb6dc84e5653.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:132
 * @route '/shop/operations/orders/{order}/send-for-delivery'
 */
        sendForDeliveryb2c05d83311c9a8ac191eb6dc84e5653Form.post = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: sendForDeliveryb2c05d83311c9a8ac191eb6dc84e5653.url(args, options),
            method: 'post',
        })
    
    sendForDeliveryb2c05d83311c9a8ac191eb6dc84e5653.form = sendForDeliveryb2c05d83311c9a8ac191eb6dc84e5653Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:132
 * @route '/staff/operations/orders/{order}/send-for-delivery'
 */
const sendForDelivery3f3122164782bf150d593c15237c6c62 = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendForDelivery3f3122164782bf150d593c15237c6c62.url(args, options),
    method: 'post',
})

sendForDelivery3f3122164782bf150d593c15237c6c62.definition = {
    methods: ["post"],
    url: '/staff/operations/orders/{order}/send-for-delivery',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:132
 * @route '/staff/operations/orders/{order}/send-for-delivery'
 */
sendForDelivery3f3122164782bf150d593c15237c6c62.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return sendForDelivery3f3122164782bf150d593c15237c6c62.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:132
 * @route '/staff/operations/orders/{order}/send-for-delivery'
 */
sendForDelivery3f3122164782bf150d593c15237c6c62.post = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendForDelivery3f3122164782bf150d593c15237c6c62.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:132
 * @route '/staff/operations/orders/{order}/send-for-delivery'
 */
    const sendForDelivery3f3122164782bf150d593c15237c6c62Form = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: sendForDelivery3f3122164782bf150d593c15237c6c62.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::sendForDelivery
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:132
 * @route '/staff/operations/orders/{order}/send-for-delivery'
 */
        sendForDelivery3f3122164782bf150d593c15237c6c62Form.post = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: sendForDelivery3f3122164782bf150d593c15237c6c62.url(args, options),
            method: 'post',
        })
    
    sendForDelivery3f3122164782bf150d593c15237c6c62.form = sendForDelivery3f3122164782bf150d593c15237c6c62Form

export const sendForDelivery = {
    '/shop/operations/orders/{order}/send-for-delivery': sendForDeliveryb2c05d83311c9a8ac191eb6dc84e5653,
    '/staff/operations/orders/{order}/send-for-delivery': sendForDelivery3f3122164782bf150d593c15237c6c62,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:252
 * @route '/shop/operations/orders/restore-all'
 */
const restoreAll05359cba787ad6011630036e5678dbd3 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreAll05359cba787ad6011630036e5678dbd3.url(options),
    method: 'post',
})

restoreAll05359cba787ad6011630036e5678dbd3.definition = {
    methods: ["post"],
    url: '/shop/operations/orders/restore-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:252
 * @route '/shop/operations/orders/restore-all'
 */
restoreAll05359cba787ad6011630036e5678dbd3.url = (options?: RouteQueryOptions) => {
    return restoreAll05359cba787ad6011630036e5678dbd3.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:252
 * @route '/shop/operations/orders/restore-all'
 */
restoreAll05359cba787ad6011630036e5678dbd3.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreAll05359cba787ad6011630036e5678dbd3.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:252
 * @route '/shop/operations/orders/restore-all'
 */
    const restoreAll05359cba787ad6011630036e5678dbd3Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restoreAll05359cba787ad6011630036e5678dbd3.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:252
 * @route '/shop/operations/orders/restore-all'
 */
        restoreAll05359cba787ad6011630036e5678dbd3Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restoreAll05359cba787ad6011630036e5678dbd3.url(options),
            method: 'post',
        })
    
    restoreAll05359cba787ad6011630036e5678dbd3.form = restoreAll05359cba787ad6011630036e5678dbd3Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:252
 * @route '/staff/operations/orders/restore-all'
 */
const restoreAllb53e8d9bd0c80a798445ffd326144dac = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreAllb53e8d9bd0c80a798445ffd326144dac.url(options),
    method: 'post',
})

restoreAllb53e8d9bd0c80a798445ffd326144dac.definition = {
    methods: ["post"],
    url: '/staff/operations/orders/restore-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:252
 * @route '/staff/operations/orders/restore-all'
 */
restoreAllb53e8d9bd0c80a798445ffd326144dac.url = (options?: RouteQueryOptions) => {
    return restoreAllb53e8d9bd0c80a798445ffd326144dac.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:252
 * @route '/staff/operations/orders/restore-all'
 */
restoreAllb53e8d9bd0c80a798445ffd326144dac.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreAllb53e8d9bd0c80a798445ffd326144dac.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:252
 * @route '/staff/operations/orders/restore-all'
 */
    const restoreAllb53e8d9bd0c80a798445ffd326144dacForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restoreAllb53e8d9bd0c80a798445ffd326144dac.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:252
 * @route '/staff/operations/orders/restore-all'
 */
        restoreAllb53e8d9bd0c80a798445ffd326144dacForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restoreAllb53e8d9bd0c80a798445ffd326144dac.url(options),
            method: 'post',
        })
    
    restoreAllb53e8d9bd0c80a798445ffd326144dac.form = restoreAllb53e8d9bd0c80a798445ffd326144dacForm

export const restoreAll = {
    '/shop/operations/orders/restore-all': restoreAll05359cba787ad6011630036e5678dbd3,
    '/staff/operations/orders/restore-all': restoreAllb53e8d9bd0c80a798445ffd326144dac,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:240
 * @route '/shop/operations/orders/{order}/restore'
 */
const restore7e2cbae9ec08c3398f3896c88ccc477c = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore7e2cbae9ec08c3398f3896c88ccc477c.url(args, options),
    method: 'patch',
})

restore7e2cbae9ec08c3398f3896c88ccc477c.definition = {
    methods: ["patch"],
    url: '/shop/operations/orders/{order}/restore',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:240
 * @route '/shop/operations/orders/{order}/restore'
 */
restore7e2cbae9ec08c3398f3896c88ccc477c.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return restore7e2cbae9ec08c3398f3896c88ccc477c.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:240
 * @route '/shop/operations/orders/{order}/restore'
 */
restore7e2cbae9ec08c3398f3896c88ccc477c.patch = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore7e2cbae9ec08c3398f3896c88ccc477c.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:240
 * @route '/shop/operations/orders/{order}/restore'
 */
    const restore7e2cbae9ec08c3398f3896c88ccc477cForm = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore7e2cbae9ec08c3398f3896c88ccc477c.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:240
 * @route '/shop/operations/orders/{order}/restore'
 */
        restore7e2cbae9ec08c3398f3896c88ccc477cForm.patch = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore7e2cbae9ec08c3398f3896c88ccc477c.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    restore7e2cbae9ec08c3398f3896c88ccc477c.form = restore7e2cbae9ec08c3398f3896c88ccc477cForm
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:240
 * @route '/staff/operations/orders/{order}/restore'
 */
const restore7ecbb9f0c73a6910b6185bd2204974f6 = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore7ecbb9f0c73a6910b6185bd2204974f6.url(args, options),
    method: 'patch',
})

restore7ecbb9f0c73a6910b6185bd2204974f6.definition = {
    methods: ["patch"],
    url: '/staff/operations/orders/{order}/restore',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:240
 * @route '/staff/operations/orders/{order}/restore'
 */
restore7ecbb9f0c73a6910b6185bd2204974f6.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return restore7ecbb9f0c73a6910b6185bd2204974f6.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:240
 * @route '/staff/operations/orders/{order}/restore'
 */
restore7ecbb9f0c73a6910b6185bd2204974f6.patch = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore7ecbb9f0c73a6910b6185bd2204974f6.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:240
 * @route '/staff/operations/orders/{order}/restore'
 */
    const restore7ecbb9f0c73a6910b6185bd2204974f6Form = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore7ecbb9f0c73a6910b6185bd2204974f6.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:240
 * @route '/staff/operations/orders/{order}/restore'
 */
        restore7ecbb9f0c73a6910b6185bd2204974f6Form.patch = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore7ecbb9f0c73a6910b6185bd2204974f6.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    restore7ecbb9f0c73a6910b6185bd2204974f6.form = restore7ecbb9f0c73a6910b6185bd2204974f6Form

export const restore = {
    '/shop/operations/orders/{order}/restore': restore7e2cbae9ec08c3398f3896c88ccc477c,
    '/staff/operations/orders/{order}/restore': restore7ecbb9f0c73a6910b6185bd2204974f6,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::updateStatus
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:183
 * @route '/shop/operations/orders/{order}/status'
 */
const updateStatus8c419c3d03b0da5c49a088747a7ee2c7 = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatus8c419c3d03b0da5c49a088747a7ee2c7.url(args, options),
    method: 'patch',
})

updateStatus8c419c3d03b0da5c49a088747a7ee2c7.definition = {
    methods: ["patch"],
    url: '/shop/operations/orders/{order}/status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::updateStatus
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:183
 * @route '/shop/operations/orders/{order}/status'
 */
updateStatus8c419c3d03b0da5c49a088747a7ee2c7.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return updateStatus8c419c3d03b0da5c49a088747a7ee2c7.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::updateStatus
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:183
 * @route '/shop/operations/orders/{order}/status'
 */
updateStatus8c419c3d03b0da5c49a088747a7ee2c7.patch = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatus8c419c3d03b0da5c49a088747a7ee2c7.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::updateStatus
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:183
 * @route '/shop/operations/orders/{order}/status'
 */
    const updateStatus8c419c3d03b0da5c49a088747a7ee2c7Form = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateStatus8c419c3d03b0da5c49a088747a7ee2c7.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::updateStatus
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:183
 * @route '/shop/operations/orders/{order}/status'
 */
        updateStatus8c419c3d03b0da5c49a088747a7ee2c7Form.patch = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateStatus8c419c3d03b0da5c49a088747a7ee2c7.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateStatus8c419c3d03b0da5c49a088747a7ee2c7.form = updateStatus8c419c3d03b0da5c49a088747a7ee2c7Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::updateStatus
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:183
 * @route '/staff/operations/orders/{order}/status'
 */
const updateStatus1408d9eab3157e71b3908eed25954ec5 = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatus1408d9eab3157e71b3908eed25954ec5.url(args, options),
    method: 'patch',
})

updateStatus1408d9eab3157e71b3908eed25954ec5.definition = {
    methods: ["patch"],
    url: '/staff/operations/orders/{order}/status',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::updateStatus
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:183
 * @route '/staff/operations/orders/{order}/status'
 */
updateStatus1408d9eab3157e71b3908eed25954ec5.url = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'payment_public_id' in args) {
            args = { order: args.payment_public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.payment_public_id
                : args.order,
                }

    return updateStatus1408d9eab3157e71b3908eed25954ec5.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::updateStatus
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:183
 * @route '/staff/operations/orders/{order}/status'
 */
updateStatus1408d9eab3157e71b3908eed25954ec5.patch = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updateStatus1408d9eab3157e71b3908eed25954ec5.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::updateStatus
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:183
 * @route '/staff/operations/orders/{order}/status'
 */
    const updateStatus1408d9eab3157e71b3908eed25954ec5Form = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateStatus1408d9eab3157e71b3908eed25954ec5.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopOrderController::updateStatus
 * @see app/Http/Controllers/Shop/Operations/ShopOrderController.php:183
 * @route '/staff/operations/orders/{order}/status'
 */
        updateStatus1408d9eab3157e71b3908eed25954ec5Form.patch = (args: { order: string | { payment_public_id: string } } | [order: string | { payment_public_id: string } ] | string | { payment_public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateStatus1408d9eab3157e71b3908eed25954ec5.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateStatus1408d9eab3157e71b3908eed25954ec5.form = updateStatus1408d9eab3157e71b3908eed25954ec5Form

export const updateStatus = {
    '/shop/operations/orders/{order}/status': updateStatus8c419c3d03b0da5c49a088747a7ee2c7,
    '/staff/operations/orders/{order}/status': updateStatus1408d9eab3157e71b3908eed25954ec5,
}

const ShopOrderController = { index, create, store, show, edit, update, destroy, recordPayment, checkPayments, voidPayment, sendForDelivery, restoreAll, restore, updateStatus }

export default ShopOrderController