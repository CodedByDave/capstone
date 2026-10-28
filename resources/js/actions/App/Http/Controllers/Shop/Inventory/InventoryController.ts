import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/shop/inventory'
 */
const indexa5d6e6a0fedc61e3676302e8c58e93ca = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexa5d6e6a0fedc61e3676302e8c58e93ca.url(options),
    method: 'get',
})

indexa5d6e6a0fedc61e3676302e8c58e93ca.definition = {
    methods: ["get","head"],
    url: '/shop/inventory',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/shop/inventory'
 */
indexa5d6e6a0fedc61e3676302e8c58e93ca.url = (options?: RouteQueryOptions) => {
    return indexa5d6e6a0fedc61e3676302e8c58e93ca.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/shop/inventory'
 */
indexa5d6e6a0fedc61e3676302e8c58e93ca.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexa5d6e6a0fedc61e3676302e8c58e93ca.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/shop/inventory'
 */
indexa5d6e6a0fedc61e3676302e8c58e93ca.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexa5d6e6a0fedc61e3676302e8c58e93ca.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/shop/inventory'
 */
    const indexa5d6e6a0fedc61e3676302e8c58e93caForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexa5d6e6a0fedc61e3676302e8c58e93ca.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/shop/inventory'
 */
        indexa5d6e6a0fedc61e3676302e8c58e93caForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexa5d6e6a0fedc61e3676302e8c58e93ca.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/shop/inventory'
 */
        indexa5d6e6a0fedc61e3676302e8c58e93caForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexa5d6e6a0fedc61e3676302e8c58e93ca.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexa5d6e6a0fedc61e3676302e8c58e93ca.form = indexa5d6e6a0fedc61e3676302e8c58e93caForm
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/staff/inventory'
 */
const indexf568996d6d4d567024babc2f120d11f4 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexf568996d6d4d567024babc2f120d11f4.url(options),
    method: 'get',
})

indexf568996d6d4d567024babc2f120d11f4.definition = {
    methods: ["get","head"],
    url: '/staff/inventory',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/staff/inventory'
 */
indexf568996d6d4d567024babc2f120d11f4.url = (options?: RouteQueryOptions) => {
    return indexf568996d6d4d567024babc2f120d11f4.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/staff/inventory'
 */
indexf568996d6d4d567024babc2f120d11f4.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexf568996d6d4d567024babc2f120d11f4.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/staff/inventory'
 */
indexf568996d6d4d567024babc2f120d11f4.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexf568996d6d4d567024babc2f120d11f4.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/staff/inventory'
 */
    const indexf568996d6d4d567024babc2f120d11f4Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexf568996d6d4d567024babc2f120d11f4.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/staff/inventory'
 */
        indexf568996d6d4d567024babc2f120d11f4Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexf568996d6d4d567024babc2f120d11f4.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:39
 * @route '/staff/inventory'
 */
        indexf568996d6d4d567024babc2f120d11f4Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexf568996d6d4d567024babc2f120d11f4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexf568996d6d4d567024babc2f120d11f4.form = indexf568996d6d4d567024babc2f120d11f4Form

export const index = {
    '/shop/inventory': indexa5d6e6a0fedc61e3676302e8c58e93ca,
    '/staff/inventory': indexf568996d6d4d567024babc2f120d11f4,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/shop/inventory/create'
 */
const createbfdc10c2c69fea200291578e628089f7 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: createbfdc10c2c69fea200291578e628089f7.url(options),
    method: 'get',
})

createbfdc10c2c69fea200291578e628089f7.definition = {
    methods: ["get","head"],
    url: '/shop/inventory/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/shop/inventory/create'
 */
createbfdc10c2c69fea200291578e628089f7.url = (options?: RouteQueryOptions) => {
    return createbfdc10c2c69fea200291578e628089f7.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/shop/inventory/create'
 */
createbfdc10c2c69fea200291578e628089f7.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: createbfdc10c2c69fea200291578e628089f7.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/shop/inventory/create'
 */
createbfdc10c2c69fea200291578e628089f7.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: createbfdc10c2c69fea200291578e628089f7.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/shop/inventory/create'
 */
    const createbfdc10c2c69fea200291578e628089f7Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: createbfdc10c2c69fea200291578e628089f7.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/shop/inventory/create'
 */
        createbfdc10c2c69fea200291578e628089f7Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: createbfdc10c2c69fea200291578e628089f7.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/shop/inventory/create'
 */
        createbfdc10c2c69fea200291578e628089f7Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: createbfdc10c2c69fea200291578e628089f7.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    createbfdc10c2c69fea200291578e628089f7.form = createbfdc10c2c69fea200291578e628089f7Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/staff/inventory/create'
 */
const createa0a5368fc7ead83a3bec6e261fc5bf67 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: createa0a5368fc7ead83a3bec6e261fc5bf67.url(options),
    method: 'get',
})

createa0a5368fc7ead83a3bec6e261fc5bf67.definition = {
    methods: ["get","head"],
    url: '/staff/inventory/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/staff/inventory/create'
 */
createa0a5368fc7ead83a3bec6e261fc5bf67.url = (options?: RouteQueryOptions) => {
    return createa0a5368fc7ead83a3bec6e261fc5bf67.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/staff/inventory/create'
 */
createa0a5368fc7ead83a3bec6e261fc5bf67.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: createa0a5368fc7ead83a3bec6e261fc5bf67.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/staff/inventory/create'
 */
createa0a5368fc7ead83a3bec6e261fc5bf67.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: createa0a5368fc7ead83a3bec6e261fc5bf67.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/staff/inventory/create'
 */
    const createa0a5368fc7ead83a3bec6e261fc5bf67Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: createa0a5368fc7ead83a3bec6e261fc5bf67.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/staff/inventory/create'
 */
        createa0a5368fc7ead83a3bec6e261fc5bf67Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: createa0a5368fc7ead83a3bec6e261fc5bf67.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::create
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:53
 * @route '/staff/inventory/create'
 */
        createa0a5368fc7ead83a3bec6e261fc5bf67Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: createa0a5368fc7ead83a3bec6e261fc5bf67.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    createa0a5368fc7ead83a3bec6e261fc5bf67.form = createa0a5368fc7ead83a3bec6e261fc5bf67Form

export const create = {
    '/shop/inventory/create': createbfdc10c2c69fea200291578e628089f7,
    '/staff/inventory/create': createa0a5368fc7ead83a3bec6e261fc5bf67,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/shop/inventory'
 */
const storea5d6e6a0fedc61e3676302e8c58e93ca = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storea5d6e6a0fedc61e3676302e8c58e93ca.url(options),
    method: 'post',
})

storea5d6e6a0fedc61e3676302e8c58e93ca.definition = {
    methods: ["post"],
    url: '/shop/inventory',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/shop/inventory'
 */
storea5d6e6a0fedc61e3676302e8c58e93ca.url = (options?: RouteQueryOptions) => {
    return storea5d6e6a0fedc61e3676302e8c58e93ca.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/shop/inventory'
 */
storea5d6e6a0fedc61e3676302e8c58e93ca.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storea5d6e6a0fedc61e3676302e8c58e93ca.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/shop/inventory'
 */
    const storea5d6e6a0fedc61e3676302e8c58e93caForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storea5d6e6a0fedc61e3676302e8c58e93ca.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/shop/inventory'
 */
        storea5d6e6a0fedc61e3676302e8c58e93caForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storea5d6e6a0fedc61e3676302e8c58e93ca.url(options),
            method: 'post',
        })
    
    storea5d6e6a0fedc61e3676302e8c58e93ca.form = storea5d6e6a0fedc61e3676302e8c58e93caForm
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/staff/inventory'
 */
const storef568996d6d4d567024babc2f120d11f4 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storef568996d6d4d567024babc2f120d11f4.url(options),
    method: 'post',
})

storef568996d6d4d567024babc2f120d11f4.definition = {
    methods: ["post"],
    url: '/staff/inventory',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/staff/inventory'
 */
storef568996d6d4d567024babc2f120d11f4.url = (options?: RouteQueryOptions) => {
    return storef568996d6d4d567024babc2f120d11f4.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/staff/inventory'
 */
storef568996d6d4d567024babc2f120d11f4.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storef568996d6d4d567024babc2f120d11f4.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/staff/inventory'
 */
    const storef568996d6d4d567024babc2f120d11f4Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storef568996d6d4d567024babc2f120d11f4.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:63
 * @route '/staff/inventory'
 */
        storef568996d6d4d567024babc2f120d11f4Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storef568996d6d4d567024babc2f120d11f4.url(options),
            method: 'post',
        })
    
    storef568996d6d4d567024babc2f120d11f4.form = storef568996d6d4d567024babc2f120d11f4Form

export const store = {
    '/shop/inventory': storea5d6e6a0fedc61e3676302e8c58e93ca,
    '/staff/inventory': storef568996d6d4d567024babc2f120d11f4,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/shop/inventory/{inventory}'
 */
const showf2c809eeead4ee69ef3d01bc911dbc25 = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showf2c809eeead4ee69ef3d01bc911dbc25.url(args, options),
    method: 'get',
})

showf2c809eeead4ee69ef3d01bc911dbc25.definition = {
    methods: ["get","head"],
    url: '/shop/inventory/{inventory}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/shop/inventory/{inventory}'
 */
showf2c809eeead4ee69ef3d01bc911dbc25.url = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return showf2c809eeead4ee69ef3d01bc911dbc25.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/shop/inventory/{inventory}'
 */
showf2c809eeead4ee69ef3d01bc911dbc25.get = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: showf2c809eeead4ee69ef3d01bc911dbc25.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/shop/inventory/{inventory}'
 */
showf2c809eeead4ee69ef3d01bc911dbc25.head = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: showf2c809eeead4ee69ef3d01bc911dbc25.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/shop/inventory/{inventory}'
 */
    const showf2c809eeead4ee69ef3d01bc911dbc25Form = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: showf2c809eeead4ee69ef3d01bc911dbc25.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/shop/inventory/{inventory}'
 */
        showf2c809eeead4ee69ef3d01bc911dbc25Form.get = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showf2c809eeead4ee69ef3d01bc911dbc25.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/shop/inventory/{inventory}'
 */
        showf2c809eeead4ee69ef3d01bc911dbc25Form.head = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: showf2c809eeead4ee69ef3d01bc911dbc25.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    showf2c809eeead4ee69ef3d01bc911dbc25.form = showf2c809eeead4ee69ef3d01bc911dbc25Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/staff/inventory/{inventory}'
 */
const show00ba00c906a7b939969d26b30b67e452 = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show00ba00c906a7b939969d26b30b67e452.url(args, options),
    method: 'get',
})

show00ba00c906a7b939969d26b30b67e452.definition = {
    methods: ["get","head"],
    url: '/staff/inventory/{inventory}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/staff/inventory/{inventory}'
 */
show00ba00c906a7b939969d26b30b67e452.url = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return show00ba00c906a7b939969d26b30b67e452.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/staff/inventory/{inventory}'
 */
show00ba00c906a7b939969d26b30b67e452.get = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show00ba00c906a7b939969d26b30b67e452.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/staff/inventory/{inventory}'
 */
show00ba00c906a7b939969d26b30b67e452.head = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show00ba00c906a7b939969d26b30b67e452.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/staff/inventory/{inventory}'
 */
    const show00ba00c906a7b939969d26b30b67e452Form = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show00ba00c906a7b939969d26b30b67e452.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/staff/inventory/{inventory}'
 */
        show00ba00c906a7b939969d26b30b67e452Form.get = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show00ba00c906a7b939969d26b30b67e452.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::show
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:75
 * @route '/staff/inventory/{inventory}'
 */
        show00ba00c906a7b939969d26b30b67e452Form.head = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show00ba00c906a7b939969d26b30b67e452.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show00ba00c906a7b939969d26b30b67e452.form = show00ba00c906a7b939969d26b30b67e452Form

export const show = {
    '/shop/inventory/{inventory}': showf2c809eeead4ee69ef3d01bc911dbc25,
    '/staff/inventory/{inventory}': show00ba00c906a7b939969d26b30b67e452,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/shop/inventory/{inventory}/edit'
 */
const edit160756faa79f3c76fa68fe5ce9e3d882 = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit160756faa79f3c76fa68fe5ce9e3d882.url(args, options),
    method: 'get',
})

edit160756faa79f3c76fa68fe5ce9e3d882.definition = {
    methods: ["get","head"],
    url: '/shop/inventory/{inventory}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/shop/inventory/{inventory}/edit'
 */
edit160756faa79f3c76fa68fe5ce9e3d882.url = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return edit160756faa79f3c76fa68fe5ce9e3d882.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/shop/inventory/{inventory}/edit'
 */
edit160756faa79f3c76fa68fe5ce9e3d882.get = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit160756faa79f3c76fa68fe5ce9e3d882.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/shop/inventory/{inventory}/edit'
 */
edit160756faa79f3c76fa68fe5ce9e3d882.head = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit160756faa79f3c76fa68fe5ce9e3d882.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/shop/inventory/{inventory}/edit'
 */
    const edit160756faa79f3c76fa68fe5ce9e3d882Form = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit160756faa79f3c76fa68fe5ce9e3d882.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/shop/inventory/{inventory}/edit'
 */
        edit160756faa79f3c76fa68fe5ce9e3d882Form.get = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit160756faa79f3c76fa68fe5ce9e3d882.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/shop/inventory/{inventory}/edit'
 */
        edit160756faa79f3c76fa68fe5ce9e3d882Form.head = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit160756faa79f3c76fa68fe5ce9e3d882.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit160756faa79f3c76fa68fe5ce9e3d882.form = edit160756faa79f3c76fa68fe5ce9e3d882Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/staff/inventory/{inventory}/edit'
 */
const edit4c0701b1cba9da17faafda5226afbfdf = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit4c0701b1cba9da17faafda5226afbfdf.url(args, options),
    method: 'get',
})

edit4c0701b1cba9da17faafda5226afbfdf.definition = {
    methods: ["get","head"],
    url: '/staff/inventory/{inventory}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/staff/inventory/{inventory}/edit'
 */
edit4c0701b1cba9da17faafda5226afbfdf.url = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return edit4c0701b1cba9da17faafda5226afbfdf.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/staff/inventory/{inventory}/edit'
 */
edit4c0701b1cba9da17faafda5226afbfdf.get = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit4c0701b1cba9da17faafda5226afbfdf.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/staff/inventory/{inventory}/edit'
 */
edit4c0701b1cba9da17faafda5226afbfdf.head = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit4c0701b1cba9da17faafda5226afbfdf.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/staff/inventory/{inventory}/edit'
 */
    const edit4c0701b1cba9da17faafda5226afbfdfForm = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit4c0701b1cba9da17faafda5226afbfdf.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/staff/inventory/{inventory}/edit'
 */
        edit4c0701b1cba9da17faafda5226afbfdfForm.get = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit4c0701b1cba9da17faafda5226afbfdf.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:88
 * @route '/staff/inventory/{inventory}/edit'
 */
        edit4c0701b1cba9da17faafda5226afbfdfForm.head = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit4c0701b1cba9da17faafda5226afbfdf.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit4c0701b1cba9da17faafda5226afbfdf.form = edit4c0701b1cba9da17faafda5226afbfdfForm

export const edit = {
    '/shop/inventory/{inventory}/edit': edit160756faa79f3c76fa68fe5ce9e3d882,
    '/staff/inventory/{inventory}/edit': edit4c0701b1cba9da17faafda5226afbfdf,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:103
 * @route '/shop/inventory/{inventory}'
 */
const updatef2c809eeead4ee69ef3d01bc911dbc25 = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatef2c809eeead4ee69ef3d01bc911dbc25.url(args, options),
    method: 'put',
})

updatef2c809eeead4ee69ef3d01bc911dbc25.definition = {
    methods: ["put"],
    url: '/shop/inventory/{inventory}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:103
 * @route '/shop/inventory/{inventory}'
 */
updatef2c809eeead4ee69ef3d01bc911dbc25.url = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return updatef2c809eeead4ee69ef3d01bc911dbc25.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:103
 * @route '/shop/inventory/{inventory}'
 */
updatef2c809eeead4ee69ef3d01bc911dbc25.put = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatef2c809eeead4ee69ef3d01bc911dbc25.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:103
 * @route '/shop/inventory/{inventory}'
 */
    const updatef2c809eeead4ee69ef3d01bc911dbc25Form = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updatef2c809eeead4ee69ef3d01bc911dbc25.url(args, {
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
 * @route '/shop/inventory/{inventory}'
 */
        updatef2c809eeead4ee69ef3d01bc911dbc25Form.put = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updatef2c809eeead4ee69ef3d01bc911dbc25.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updatef2c809eeead4ee69ef3d01bc911dbc25.form = updatef2c809eeead4ee69ef3d01bc911dbc25Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:103
 * @route '/staff/inventory/{inventory}'
 */
const update00ba00c906a7b939969d26b30b67e452 = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update00ba00c906a7b939969d26b30b67e452.url(args, options),
    method: 'put',
})

update00ba00c906a7b939969d26b30b67e452.definition = {
    methods: ["put"],
    url: '/staff/inventory/{inventory}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:103
 * @route '/staff/inventory/{inventory}'
 */
update00ba00c906a7b939969d26b30b67e452.url = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return update00ba00c906a7b939969d26b30b67e452.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:103
 * @route '/staff/inventory/{inventory}'
 */
update00ba00c906a7b939969d26b30b67e452.put = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update00ba00c906a7b939969d26b30b67e452.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:103
 * @route '/staff/inventory/{inventory}'
 */
    const update00ba00c906a7b939969d26b30b67e452Form = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update00ba00c906a7b939969d26b30b67e452.url(args, {
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
        update00ba00c906a7b939969d26b30b67e452Form.put = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update00ba00c906a7b939969d26b30b67e452.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update00ba00c906a7b939969d26b30b67e452.form = update00ba00c906a7b939969d26b30b67e452Form

export const update = {
    '/shop/inventory/{inventory}': updatef2c809eeead4ee69ef3d01bc911dbc25,
    '/staff/inventory/{inventory}': update00ba00c906a7b939969d26b30b67e452,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:116
 * @route '/shop/inventory/{inventory}'
 */
const destroyf2c809eeead4ee69ef3d01bc911dbc25 = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyf2c809eeead4ee69ef3d01bc911dbc25.url(args, options),
    method: 'delete',
})

destroyf2c809eeead4ee69ef3d01bc911dbc25.definition = {
    methods: ["delete"],
    url: '/shop/inventory/{inventory}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:116
 * @route '/shop/inventory/{inventory}'
 */
destroyf2c809eeead4ee69ef3d01bc911dbc25.url = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return destroyf2c809eeead4ee69ef3d01bc911dbc25.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:116
 * @route '/shop/inventory/{inventory}'
 */
destroyf2c809eeead4ee69ef3d01bc911dbc25.delete = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroyf2c809eeead4ee69ef3d01bc911dbc25.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:116
 * @route '/shop/inventory/{inventory}'
 */
    const destroyf2c809eeead4ee69ef3d01bc911dbc25Form = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroyf2c809eeead4ee69ef3d01bc911dbc25.url(args, {
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
 * @route '/shop/inventory/{inventory}'
 */
        destroyf2c809eeead4ee69ef3d01bc911dbc25Form.delete = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroyf2c809eeead4ee69ef3d01bc911dbc25.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroyf2c809eeead4ee69ef3d01bc911dbc25.form = destroyf2c809eeead4ee69ef3d01bc911dbc25Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:116
 * @route '/staff/inventory/{inventory}'
 */
const destroy00ba00c906a7b939969d26b30b67e452 = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy00ba00c906a7b939969d26b30b67e452.url(args, options),
    method: 'delete',
})

destroy00ba00c906a7b939969d26b30b67e452.definition = {
    methods: ["delete"],
    url: '/staff/inventory/{inventory}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:116
 * @route '/staff/inventory/{inventory}'
 */
destroy00ba00c906a7b939969d26b30b67e452.url = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return destroy00ba00c906a7b939969d26b30b67e452.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:116
 * @route '/staff/inventory/{inventory}'
 */
destroy00ba00c906a7b939969d26b30b67e452.delete = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy00ba00c906a7b939969d26b30b67e452.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:116
 * @route '/staff/inventory/{inventory}'
 */
    const destroy00ba00c906a7b939969d26b30b67e452Form = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy00ba00c906a7b939969d26b30b67e452.url(args, {
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
        destroy00ba00c906a7b939969d26b30b67e452Form.delete = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy00ba00c906a7b939969d26b30b67e452.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy00ba00c906a7b939969d26b30b67e452.form = destroy00ba00c906a7b939969d26b30b67e452Form

export const destroy = {
    '/shop/inventory/{inventory}': destroyf2c809eeead4ee69ef3d01bc911dbc25,
    '/staff/inventory/{inventory}': destroy00ba00c906a7b939969d26b30b67e452,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjustStock
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/shop/inventory/{inventory}/adjust'
 */
const adjustStockc60b8c20c52e43c06843b77546d35f2d = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adjustStockc60b8c20c52e43c06843b77546d35f2d.url(args, options),
    method: 'post',
})

adjustStockc60b8c20c52e43c06843b77546d35f2d.definition = {
    methods: ["post"],
    url: '/shop/inventory/{inventory}/adjust',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjustStock
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/shop/inventory/{inventory}/adjust'
 */
adjustStockc60b8c20c52e43c06843b77546d35f2d.url = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return adjustStockc60b8c20c52e43c06843b77546d35f2d.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjustStock
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/shop/inventory/{inventory}/adjust'
 */
adjustStockc60b8c20c52e43c06843b77546d35f2d.post = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adjustStockc60b8c20c52e43c06843b77546d35f2d.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjustStock
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/shop/inventory/{inventory}/adjust'
 */
    const adjustStockc60b8c20c52e43c06843b77546d35f2dForm = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: adjustStockc60b8c20c52e43c06843b77546d35f2d.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjustStock
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/shop/inventory/{inventory}/adjust'
 */
        adjustStockc60b8c20c52e43c06843b77546d35f2dForm.post = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: adjustStockc60b8c20c52e43c06843b77546d35f2d.url(args, options),
            method: 'post',
        })
    
    adjustStockc60b8c20c52e43c06843b77546d35f2d.form = adjustStockc60b8c20c52e43c06843b77546d35f2dForm
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjustStock
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/staff/inventory/{inventory}/adjust'
 */
const adjustStock13801b4a3a62177632d77759acbabcdf = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adjustStock13801b4a3a62177632d77759acbabcdf.url(args, options),
    method: 'post',
})

adjustStock13801b4a3a62177632d77759acbabcdf.definition = {
    methods: ["post"],
    url: '/staff/inventory/{inventory}/adjust',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjustStock
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/staff/inventory/{inventory}/adjust'
 */
adjustStock13801b4a3a62177632d77759acbabcdf.url = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return adjustStock13801b4a3a62177632d77759acbabcdf.definition.url
            .replace('{inventory}', parsedArgs.inventory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjustStock
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/staff/inventory/{inventory}/adjust'
 */
adjustStock13801b4a3a62177632d77759acbabcdf.post = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: adjustStock13801b4a3a62177632d77759acbabcdf.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjustStock
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/staff/inventory/{inventory}/adjust'
 */
    const adjustStock13801b4a3a62177632d77759acbabcdfForm = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: adjustStock13801b4a3a62177632d77759acbabcdf.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryController::adjustStock
 * @see app/Http/Controllers/Shop/Inventory/InventoryController.php:129
 * @route '/staff/inventory/{inventory}/adjust'
 */
        adjustStock13801b4a3a62177632d77759acbabcdfForm.post = (args: { inventory: string | number | { id: string | number } } | [inventory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: adjustStock13801b4a3a62177632d77759acbabcdf.url(args, options),
            method: 'post',
        })
    
    adjustStock13801b4a3a62177632d77759acbabcdf.form = adjustStock13801b4a3a62177632d77759acbabcdfForm

export const adjustStock = {
    '/shop/inventory/{inventory}/adjust': adjustStockc60b8c20c52e43c06843b77546d35f2d,
    '/staff/inventory/{inventory}/adjust': adjustStock13801b4a3a62177632d77759acbabcdf,
}

const InventoryController = { index, create, store, show, edit, update, destroy, adjustStock }

export default InventoryController