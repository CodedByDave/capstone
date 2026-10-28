import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/shop/supplier'
 */
const index6e9ba6528fc430e854109f2ba05c86df = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index6e9ba6528fc430e854109f2ba05c86df.url(options),
    method: 'get',
})

index6e9ba6528fc430e854109f2ba05c86df.definition = {
    methods: ["get","head"],
    url: '/shop/supplier',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/shop/supplier'
 */
index6e9ba6528fc430e854109f2ba05c86df.url = (options?: RouteQueryOptions) => {
    return index6e9ba6528fc430e854109f2ba05c86df.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/shop/supplier'
 */
index6e9ba6528fc430e854109f2ba05c86df.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index6e9ba6528fc430e854109f2ba05c86df.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/shop/supplier'
 */
index6e9ba6528fc430e854109f2ba05c86df.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index6e9ba6528fc430e854109f2ba05c86df.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/shop/supplier'
 */
    const index6e9ba6528fc430e854109f2ba05c86dfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index6e9ba6528fc430e854109f2ba05c86df.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/shop/supplier'
 */
        index6e9ba6528fc430e854109f2ba05c86dfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index6e9ba6528fc430e854109f2ba05c86df.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/shop/supplier'
 */
        index6e9ba6528fc430e854109f2ba05c86dfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index6e9ba6528fc430e854109f2ba05c86df.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index6e9ba6528fc430e854109f2ba05c86df.form = index6e9ba6528fc430e854109f2ba05c86dfForm
    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/staff/supplier'
 */
const index58c8d01005028baa95eea79080e74e73 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index58c8d01005028baa95eea79080e74e73.url(options),
    method: 'get',
})

index58c8d01005028baa95eea79080e74e73.definition = {
    methods: ["get","head"],
    url: '/staff/supplier',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/staff/supplier'
 */
index58c8d01005028baa95eea79080e74e73.url = (options?: RouteQueryOptions) => {
    return index58c8d01005028baa95eea79080e74e73.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/staff/supplier'
 */
index58c8d01005028baa95eea79080e74e73.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index58c8d01005028baa95eea79080e74e73.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/staff/supplier'
 */
index58c8d01005028baa95eea79080e74e73.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index58c8d01005028baa95eea79080e74e73.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/staff/supplier'
 */
    const index58c8d01005028baa95eea79080e74e73Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index58c8d01005028baa95eea79080e74e73.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/staff/supplier'
 */
        index58c8d01005028baa95eea79080e74e73Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index58c8d01005028baa95eea79080e74e73.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::index
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:33
 * @route '/staff/supplier'
 */
        index58c8d01005028baa95eea79080e74e73Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index58c8d01005028baa95eea79080e74e73.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index58c8d01005028baa95eea79080e74e73.form = index58c8d01005028baa95eea79080e74e73Form

export const index = {
    '/shop/supplier': index6e9ba6528fc430e854109f2ba05c86df,
    '/staff/supplier': index58c8d01005028baa95eea79080e74e73,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/shop/supplier/create'
 */
const create3c850cff1273649ac4d585a784308198 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create3c850cff1273649ac4d585a784308198.url(options),
    method: 'get',
})

create3c850cff1273649ac4d585a784308198.definition = {
    methods: ["get","head"],
    url: '/shop/supplier/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/shop/supplier/create'
 */
create3c850cff1273649ac4d585a784308198.url = (options?: RouteQueryOptions) => {
    return create3c850cff1273649ac4d585a784308198.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/shop/supplier/create'
 */
create3c850cff1273649ac4d585a784308198.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create3c850cff1273649ac4d585a784308198.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/shop/supplier/create'
 */
create3c850cff1273649ac4d585a784308198.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create3c850cff1273649ac4d585a784308198.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/shop/supplier/create'
 */
    const create3c850cff1273649ac4d585a784308198Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create3c850cff1273649ac4d585a784308198.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/shop/supplier/create'
 */
        create3c850cff1273649ac4d585a784308198Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create3c850cff1273649ac4d585a784308198.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/shop/supplier/create'
 */
        create3c850cff1273649ac4d585a784308198Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create3c850cff1273649ac4d585a784308198.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create3c850cff1273649ac4d585a784308198.form = create3c850cff1273649ac4d585a784308198Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/staff/supplier/create'
 */
const create00fe20f8daccb1efd9e3ffedcf0d39bc = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create00fe20f8daccb1efd9e3ffedcf0d39bc.url(options),
    method: 'get',
})

create00fe20f8daccb1efd9e3ffedcf0d39bc.definition = {
    methods: ["get","head"],
    url: '/staff/supplier/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/staff/supplier/create'
 */
create00fe20f8daccb1efd9e3ffedcf0d39bc.url = (options?: RouteQueryOptions) => {
    return create00fe20f8daccb1efd9e3ffedcf0d39bc.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/staff/supplier/create'
 */
create00fe20f8daccb1efd9e3ffedcf0d39bc.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create00fe20f8daccb1efd9e3ffedcf0d39bc.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/staff/supplier/create'
 */
create00fe20f8daccb1efd9e3ffedcf0d39bc.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create00fe20f8daccb1efd9e3ffedcf0d39bc.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/staff/supplier/create'
 */
    const create00fe20f8daccb1efd9e3ffedcf0d39bcForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create00fe20f8daccb1efd9e3ffedcf0d39bc.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/staff/supplier/create'
 */
        create00fe20f8daccb1efd9e3ffedcf0d39bcForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create00fe20f8daccb1efd9e3ffedcf0d39bc.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::create
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:42
 * @route '/staff/supplier/create'
 */
        create00fe20f8daccb1efd9e3ffedcf0d39bcForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create00fe20f8daccb1efd9e3ffedcf0d39bc.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create00fe20f8daccb1efd9e3ffedcf0d39bc.form = create00fe20f8daccb1efd9e3ffedcf0d39bcForm

export const create = {
    '/shop/supplier/create': create3c850cff1273649ac4d585a784308198,
    '/staff/supplier/create': create00fe20f8daccb1efd9e3ffedcf0d39bc,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/shop/supplier'
 */
const store6e9ba6528fc430e854109f2ba05c86df = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store6e9ba6528fc430e854109f2ba05c86df.url(options),
    method: 'post',
})

store6e9ba6528fc430e854109f2ba05c86df.definition = {
    methods: ["post"],
    url: '/shop/supplier',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/shop/supplier'
 */
store6e9ba6528fc430e854109f2ba05c86df.url = (options?: RouteQueryOptions) => {
    return store6e9ba6528fc430e854109f2ba05c86df.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/shop/supplier'
 */
store6e9ba6528fc430e854109f2ba05c86df.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store6e9ba6528fc430e854109f2ba05c86df.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/shop/supplier'
 */
    const store6e9ba6528fc430e854109f2ba05c86dfForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store6e9ba6528fc430e854109f2ba05c86df.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/shop/supplier'
 */
        store6e9ba6528fc430e854109f2ba05c86dfForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store6e9ba6528fc430e854109f2ba05c86df.url(options),
            method: 'post',
        })
    
    store6e9ba6528fc430e854109f2ba05c86df.form = store6e9ba6528fc430e854109f2ba05c86dfForm
    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/staff/supplier'
 */
const store58c8d01005028baa95eea79080e74e73 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store58c8d01005028baa95eea79080e74e73.url(options),
    method: 'post',
})

store58c8d01005028baa95eea79080e74e73.definition = {
    methods: ["post"],
    url: '/staff/supplier',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/staff/supplier'
 */
store58c8d01005028baa95eea79080e74e73.url = (options?: RouteQueryOptions) => {
    return store58c8d01005028baa95eea79080e74e73.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/staff/supplier'
 */
store58c8d01005028baa95eea79080e74e73.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store58c8d01005028baa95eea79080e74e73.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/staff/supplier'
 */
    const store58c8d01005028baa95eea79080e74e73Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store58c8d01005028baa95eea79080e74e73.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::store
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:47
 * @route '/staff/supplier'
 */
        store58c8d01005028baa95eea79080e74e73Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store58c8d01005028baa95eea79080e74e73.url(options),
            method: 'post',
        })
    
    store58c8d01005028baa95eea79080e74e73.form = store58c8d01005028baa95eea79080e74e73Form

export const store = {
    '/shop/supplier': store6e9ba6528fc430e854109f2ba05c86df,
    '/staff/supplier': store58c8d01005028baa95eea79080e74e73,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/shop/supplier/{supplier}/edit'
 */
const edit0c6ab6d54107232d4e45828eeb7251a7 = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit0c6ab6d54107232d4e45828eeb7251a7.url(args, options),
    method: 'get',
})

edit0c6ab6d54107232d4e45828eeb7251a7.definition = {
    methods: ["get","head"],
    url: '/shop/supplier/{supplier}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/shop/supplier/{supplier}/edit'
 */
edit0c6ab6d54107232d4e45828eeb7251a7.url = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return edit0c6ab6d54107232d4e45828eeb7251a7.definition.url
            .replace('{supplier}', parsedArgs.supplier.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/shop/supplier/{supplier}/edit'
 */
edit0c6ab6d54107232d4e45828eeb7251a7.get = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit0c6ab6d54107232d4e45828eeb7251a7.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/shop/supplier/{supplier}/edit'
 */
edit0c6ab6d54107232d4e45828eeb7251a7.head = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit0c6ab6d54107232d4e45828eeb7251a7.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/shop/supplier/{supplier}/edit'
 */
    const edit0c6ab6d54107232d4e45828eeb7251a7Form = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit0c6ab6d54107232d4e45828eeb7251a7.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/shop/supplier/{supplier}/edit'
 */
        edit0c6ab6d54107232d4e45828eeb7251a7Form.get = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit0c6ab6d54107232d4e45828eeb7251a7.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/shop/supplier/{supplier}/edit'
 */
        edit0c6ab6d54107232d4e45828eeb7251a7Form.head = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit0c6ab6d54107232d4e45828eeb7251a7.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit0c6ab6d54107232d4e45828eeb7251a7.form = edit0c6ab6d54107232d4e45828eeb7251a7Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/staff/supplier/{supplier}/edit'
 */
const edita494b0a8c6a3075af8912048b200805f = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edita494b0a8c6a3075af8912048b200805f.url(args, options),
    method: 'get',
})

edita494b0a8c6a3075af8912048b200805f.definition = {
    methods: ["get","head"],
    url: '/staff/supplier/{supplier}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/staff/supplier/{supplier}/edit'
 */
edita494b0a8c6a3075af8912048b200805f.url = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return edita494b0a8c6a3075af8912048b200805f.definition.url
            .replace('{supplier}', parsedArgs.supplier.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/staff/supplier/{supplier}/edit'
 */
edita494b0a8c6a3075af8912048b200805f.get = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edita494b0a8c6a3075af8912048b200805f.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/staff/supplier/{supplier}/edit'
 */
edita494b0a8c6a3075af8912048b200805f.head = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edita494b0a8c6a3075af8912048b200805f.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/staff/supplier/{supplier}/edit'
 */
    const edita494b0a8c6a3075af8912048b200805fForm = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edita494b0a8c6a3075af8912048b200805f.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/staff/supplier/{supplier}/edit'
 */
        edita494b0a8c6a3075af8912048b200805fForm.get = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edita494b0a8c6a3075af8912048b200805f.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::edit
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:57
 * @route '/staff/supplier/{supplier}/edit'
 */
        edita494b0a8c6a3075af8912048b200805fForm.head = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edita494b0a8c6a3075af8912048b200805f.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edita494b0a8c6a3075af8912048b200805f.form = edita494b0a8c6a3075af8912048b200805fForm

export const edit = {
    '/shop/supplier/{supplier}/edit': edit0c6ab6d54107232d4e45828eeb7251a7,
    '/staff/supplier/{supplier}/edit': edita494b0a8c6a3075af8912048b200805f,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::update
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:64
 * @route '/shop/supplier/{supplier}'
 */
const updateee3c239cbef8c85b0573326c696b1053 = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateee3c239cbef8c85b0573326c696b1053.url(args, options),
    method: 'put',
})

updateee3c239cbef8c85b0573326c696b1053.definition = {
    methods: ["put"],
    url: '/shop/supplier/{supplier}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::update
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:64
 * @route '/shop/supplier/{supplier}'
 */
updateee3c239cbef8c85b0573326c696b1053.url = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return updateee3c239cbef8c85b0573326c696b1053.definition.url
            .replace('{supplier}', parsedArgs.supplier.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::update
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:64
 * @route '/shop/supplier/{supplier}'
 */
updateee3c239cbef8c85b0573326c696b1053.put = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updateee3c239cbef8c85b0573326c696b1053.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::update
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:64
 * @route '/shop/supplier/{supplier}'
 */
    const updateee3c239cbef8c85b0573326c696b1053Form = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updateee3c239cbef8c85b0573326c696b1053.url(args, {
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
        updateee3c239cbef8c85b0573326c696b1053Form.put = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updateee3c239cbef8c85b0573326c696b1053.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updateee3c239cbef8c85b0573326c696b1053.form = updateee3c239cbef8c85b0573326c696b1053Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::update
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:64
 * @route '/staff/supplier/{supplier}'
 */
const update543927b6848bff5be51ec716751c4ddf = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update543927b6848bff5be51ec716751c4ddf.url(args, options),
    method: 'put',
})

update543927b6848bff5be51ec716751c4ddf.definition = {
    methods: ["put"],
    url: '/staff/supplier/{supplier}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::update
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:64
 * @route '/staff/supplier/{supplier}'
 */
update543927b6848bff5be51ec716751c4ddf.url = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return update543927b6848bff5be51ec716751c4ddf.definition.url
            .replace('{supplier}', parsedArgs.supplier.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::update
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:64
 * @route '/staff/supplier/{supplier}'
 */
update543927b6848bff5be51ec716751c4ddf.put = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update543927b6848bff5be51ec716751c4ddf.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::update
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:64
 * @route '/staff/supplier/{supplier}'
 */
    const update543927b6848bff5be51ec716751c4ddfForm = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update543927b6848bff5be51ec716751c4ddf.url(args, {
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
 * @route '/staff/supplier/{supplier}'
 */
        update543927b6848bff5be51ec716751c4ddfForm.put = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update543927b6848bff5be51ec716751c4ddf.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update543927b6848bff5be51ec716751c4ddf.form = update543927b6848bff5be51ec716751c4ddfForm

export const update = {
    '/shop/supplier/{supplier}': updateee3c239cbef8c85b0573326c696b1053,
    '/staff/supplier/{supplier}': update543927b6848bff5be51ec716751c4ddf,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::destroy
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:72
 * @route '/shop/supplier/{supplier}'
 */
const destroyee3c239cbef8c85b0573326c696b1053 = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyee3c239cbef8c85b0573326c696b1053.url(args, options),
    method: 'delete',
})

destroyee3c239cbef8c85b0573326c696b1053.definition = {
    methods: ["delete"],
    url: '/shop/supplier/{supplier}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::destroy
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:72
 * @route '/shop/supplier/{supplier}'
 */
destroyee3c239cbef8c85b0573326c696b1053.url = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroyee3c239cbef8c85b0573326c696b1053.definition.url
            .replace('{supplier}', parsedArgs.supplier.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::destroy
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:72
 * @route '/shop/supplier/{supplier}'
 */
destroyee3c239cbef8c85b0573326c696b1053.delete = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyee3c239cbef8c85b0573326c696b1053.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::destroy
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:72
 * @route '/shop/supplier/{supplier}'
 */
    const destroyee3c239cbef8c85b0573326c696b1053Form = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyee3c239cbef8c85b0573326c696b1053.url(args, {
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
        destroyee3c239cbef8c85b0573326c696b1053Form.delete = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyee3c239cbef8c85b0573326c696b1053.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyee3c239cbef8c85b0573326c696b1053.form = destroyee3c239cbef8c85b0573326c696b1053Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::destroy
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:72
 * @route '/staff/supplier/{supplier}'
 */
const destroy543927b6848bff5be51ec716751c4ddf = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy543927b6848bff5be51ec716751c4ddf.url(args, options),
    method: 'delete',
})

destroy543927b6848bff5be51ec716751c4ddf.definition = {
    methods: ["delete"],
    url: '/staff/supplier/{supplier}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::destroy
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:72
 * @route '/staff/supplier/{supplier}'
 */
destroy543927b6848bff5be51ec716751c4ddf.url = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroy543927b6848bff5be51ec716751c4ddf.definition.url
            .replace('{supplier}', parsedArgs.supplier.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::destroy
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:72
 * @route '/staff/supplier/{supplier}'
 */
destroy543927b6848bff5be51ec716751c4ddf.delete = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy543927b6848bff5be51ec716751c4ddf.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::destroy
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:72
 * @route '/staff/supplier/{supplier}'
 */
    const destroy543927b6848bff5be51ec716751c4ddfForm = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy543927b6848bff5be51ec716751c4ddf.url(args, {
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
 * @route '/staff/supplier/{supplier}'
 */
        destroy543927b6848bff5be51ec716751c4ddfForm.delete = (args: { supplier: number | { id: number } } | [supplier: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy543927b6848bff5be51ec716751c4ddf.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy543927b6848bff5be51ec716751c4ddf.form = destroy543927b6848bff5be51ec716751c4ddfForm

export const destroy = {
    '/shop/supplier/{supplier}': destroyee3c239cbef8c85b0573326c696b1053,
    '/staff/supplier/{supplier}': destroy543927b6848bff5be51ec716751c4ddf,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/shop/supplier/archive'
 */
const archiveef72c80cb61b4d4a66d71aee47dcc5db = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archiveef72c80cb61b4d4a66d71aee47dcc5db.url(options),
    method: 'get',
})

archiveef72c80cb61b4d4a66d71aee47dcc5db.definition = {
    methods: ["get","head"],
    url: '/shop/supplier/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/shop/supplier/archive'
 */
archiveef72c80cb61b4d4a66d71aee47dcc5db.url = (options?: RouteQueryOptions) => {
    return archiveef72c80cb61b4d4a66d71aee47dcc5db.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/shop/supplier/archive'
 */
archiveef72c80cb61b4d4a66d71aee47dcc5db.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archiveef72c80cb61b4d4a66d71aee47dcc5db.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/shop/supplier/archive'
 */
archiveef72c80cb61b4d4a66d71aee47dcc5db.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archiveef72c80cb61b4d4a66d71aee47dcc5db.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/shop/supplier/archive'
 */
    const archiveef72c80cb61b4d4a66d71aee47dcc5dbForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: archiveef72c80cb61b4d4a66d71aee47dcc5db.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/shop/supplier/archive'
 */
        archiveef72c80cb61b4d4a66d71aee47dcc5dbForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archiveef72c80cb61b4d4a66d71aee47dcc5db.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/shop/supplier/archive'
 */
        archiveef72c80cb61b4d4a66d71aee47dcc5dbForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archiveef72c80cb61b4d4a66d71aee47dcc5db.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    archiveef72c80cb61b4d4a66d71aee47dcc5db.form = archiveef72c80cb61b4d4a66d71aee47dcc5dbForm
    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/staff/supplier/archive'
 */
const archive5af740144f8b943698fcd31d2712e8df = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive5af740144f8b943698fcd31d2712e8df.url(options),
    method: 'get',
})

archive5af740144f8b943698fcd31d2712e8df.definition = {
    methods: ["get","head"],
    url: '/staff/supplier/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/staff/supplier/archive'
 */
archive5af740144f8b943698fcd31d2712e8df.url = (options?: RouteQueryOptions) => {
    return archive5af740144f8b943698fcd31d2712e8df.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/staff/supplier/archive'
 */
archive5af740144f8b943698fcd31d2712e8df.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive5af740144f8b943698fcd31d2712e8df.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/staff/supplier/archive'
 */
archive5af740144f8b943698fcd31d2712e8df.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archive5af740144f8b943698fcd31d2712e8df.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/staff/supplier/archive'
 */
    const archive5af740144f8b943698fcd31d2712e8dfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: archive5af740144f8b943698fcd31d2712e8df.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/staff/supplier/archive'
 */
        archive5af740144f8b943698fcd31d2712e8dfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archive5af740144f8b943698fcd31d2712e8df.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::archive
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:84
 * @route '/staff/supplier/archive'
 */
        archive5af740144f8b943698fcd31d2712e8dfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archive5af740144f8b943698fcd31d2712e8df.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    archive5af740144f8b943698fcd31d2712e8df.form = archive5af740144f8b943698fcd31d2712e8dfForm

export const archive = {
    '/shop/supplier/archive': archiveef72c80cb61b4d4a66d71aee47dcc5db,
    '/staff/supplier/archive': archive5af740144f8b943698fcd31d2712e8df,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/shop/supplier/{id}/restore'
 */
const restore5aea0d71094787f36454f7fe0dd9ac9d = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore5aea0d71094787f36454f7fe0dd9ac9d.url(args, options),
    method: 'post',
})

restore5aea0d71094787f36454f7fe0dd9ac9d.definition = {
    methods: ["post"],
    url: '/shop/supplier/{id}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/shop/supplier/{id}/restore'
 */
restore5aea0d71094787f36454f7fe0dd9ac9d.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return restore5aea0d71094787f36454f7fe0dd9ac9d.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/shop/supplier/{id}/restore'
 */
restore5aea0d71094787f36454f7fe0dd9ac9d.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore5aea0d71094787f36454f7fe0dd9ac9d.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/shop/supplier/{id}/restore'
 */
    const restore5aea0d71094787f36454f7fe0dd9ac9dForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore5aea0d71094787f36454f7fe0dd9ac9d.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/shop/supplier/{id}/restore'
 */
        restore5aea0d71094787f36454f7fe0dd9ac9dForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore5aea0d71094787f36454f7fe0dd9ac9d.url(args, options),
            method: 'post',
        })
    
    restore5aea0d71094787f36454f7fe0dd9ac9d.form = restore5aea0d71094787f36454f7fe0dd9ac9dForm
    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/staff/supplier/{id}/restore'
 */
const restore28f12e27e550867e6342123918c4d493 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore28f12e27e550867e6342123918c4d493.url(args, options),
    method: 'post',
})

restore28f12e27e550867e6342123918c4d493.definition = {
    methods: ["post"],
    url: '/staff/supplier/{id}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/staff/supplier/{id}/restore'
 */
restore28f12e27e550867e6342123918c4d493.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return restore28f12e27e550867e6342123918c4d493.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/staff/supplier/{id}/restore'
 */
restore28f12e27e550867e6342123918c4d493.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore28f12e27e550867e6342123918c4d493.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/staff/supplier/{id}/restore'
 */
    const restore28f12e27e550867e6342123918c4d493Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore28f12e27e550867e6342123918c4d493.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\SupplierController::restore
 * @see app/Http/Controllers/Shop/Inventory/SupplierController.php:93
 * @route '/staff/supplier/{id}/restore'
 */
        restore28f12e27e550867e6342123918c4d493Form.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore28f12e27e550867e6342123918c4d493.url(args, options),
            method: 'post',
        })
    
    restore28f12e27e550867e6342123918c4d493.form = restore28f12e27e550867e6342123918c4d493Form

export const restore = {
    '/shop/supplier/{id}/restore': restore5aea0d71094787f36454f7fe0dd9ac9d,
    '/staff/supplier/{id}/restore': restore28f12e27e550867e6342123918c4d493,
}

const SupplierController = { index, create, store, edit, update, destroy, archive, restore }

export default SupplierController