import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/shop/inventory/category'
 */
const index0d4990ab933a7660111612f30ab7d02b = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index0d4990ab933a7660111612f30ab7d02b.url(options),
    method: 'get',
})

index0d4990ab933a7660111612f30ab7d02b.definition = {
    methods: ["get","head"],
    url: '/shop/inventory/category',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/shop/inventory/category'
 */
index0d4990ab933a7660111612f30ab7d02b.url = (options?: RouteQueryOptions) => {
    return index0d4990ab933a7660111612f30ab7d02b.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/shop/inventory/category'
 */
index0d4990ab933a7660111612f30ab7d02b.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index0d4990ab933a7660111612f30ab7d02b.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/shop/inventory/category'
 */
index0d4990ab933a7660111612f30ab7d02b.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index0d4990ab933a7660111612f30ab7d02b.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/shop/inventory/category'
 */
    const index0d4990ab933a7660111612f30ab7d02bForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index0d4990ab933a7660111612f30ab7d02b.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/shop/inventory/category'
 */
        index0d4990ab933a7660111612f30ab7d02bForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index0d4990ab933a7660111612f30ab7d02b.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/shop/inventory/category'
 */
        index0d4990ab933a7660111612f30ab7d02bForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index0d4990ab933a7660111612f30ab7d02b.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index0d4990ab933a7660111612f30ab7d02b.form = index0d4990ab933a7660111612f30ab7d02bForm
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/staff/inventory/category'
 */
const index8ed479659968e6106cd3ed91513dd65d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index8ed479659968e6106cd3ed91513dd65d.url(options),
    method: 'get',
})

index8ed479659968e6106cd3ed91513dd65d.definition = {
    methods: ["get","head"],
    url: '/staff/inventory/category',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/staff/inventory/category'
 */
index8ed479659968e6106cd3ed91513dd65d.url = (options?: RouteQueryOptions) => {
    return index8ed479659968e6106cd3ed91513dd65d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/staff/inventory/category'
 */
index8ed479659968e6106cd3ed91513dd65d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index8ed479659968e6106cd3ed91513dd65d.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/staff/inventory/category'
 */
index8ed479659968e6106cd3ed91513dd65d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index8ed479659968e6106cd3ed91513dd65d.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/staff/inventory/category'
 */
    const index8ed479659968e6106cd3ed91513dd65dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index8ed479659968e6106cd3ed91513dd65d.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/staff/inventory/category'
 */
        index8ed479659968e6106cd3ed91513dd65dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index8ed479659968e6106cd3ed91513dd65d.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::index
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:33
 * @route '/staff/inventory/category'
 */
        index8ed479659968e6106cd3ed91513dd65dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index8ed479659968e6106cd3ed91513dd65d.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index8ed479659968e6106cd3ed91513dd65d.form = index8ed479659968e6106cd3ed91513dd65dForm

export const index = {
    '/shop/inventory/category': index0d4990ab933a7660111612f30ab7d02b,
    '/staff/inventory/category': index8ed479659968e6106cd3ed91513dd65d,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/shop/inventory/category'
 */
const store0d4990ab933a7660111612f30ab7d02b = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store0d4990ab933a7660111612f30ab7d02b.url(options),
    method: 'post',
})

store0d4990ab933a7660111612f30ab7d02b.definition = {
    methods: ["post"],
    url: '/shop/inventory/category',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/shop/inventory/category'
 */
store0d4990ab933a7660111612f30ab7d02b.url = (options?: RouteQueryOptions) => {
    return store0d4990ab933a7660111612f30ab7d02b.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/shop/inventory/category'
 */
store0d4990ab933a7660111612f30ab7d02b.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store0d4990ab933a7660111612f30ab7d02b.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/shop/inventory/category'
 */
    const store0d4990ab933a7660111612f30ab7d02bForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store0d4990ab933a7660111612f30ab7d02b.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/shop/inventory/category'
 */
        store0d4990ab933a7660111612f30ab7d02bForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store0d4990ab933a7660111612f30ab7d02b.url(options),
            method: 'post',
        })
    
    store0d4990ab933a7660111612f30ab7d02b.form = store0d4990ab933a7660111612f30ab7d02bForm
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/staff/inventory/category'
 */
const store8ed479659968e6106cd3ed91513dd65d = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store8ed479659968e6106cd3ed91513dd65d.url(options),
    method: 'post',
})

store8ed479659968e6106cd3ed91513dd65d.definition = {
    methods: ["post"],
    url: '/staff/inventory/category',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/staff/inventory/category'
 */
store8ed479659968e6106cd3ed91513dd65d.url = (options?: RouteQueryOptions) => {
    return store8ed479659968e6106cd3ed91513dd65d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/staff/inventory/category'
 */
store8ed479659968e6106cd3ed91513dd65d.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store8ed479659968e6106cd3ed91513dd65d.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/staff/inventory/category'
 */
    const store8ed479659968e6106cd3ed91513dd65dForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store8ed479659968e6106cd3ed91513dd65d.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::store
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:50
 * @route '/staff/inventory/category'
 */
        store8ed479659968e6106cd3ed91513dd65dForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store8ed479659968e6106cd3ed91513dd65d.url(options),
            method: 'post',
        })
    
    store8ed479659968e6106cd3ed91513dd65d.form = store8ed479659968e6106cd3ed91513dd65dForm

export const store = {
    '/shop/inventory/category': store0d4990ab933a7660111612f30ab7d02b,
    '/staff/inventory/category': store8ed479659968e6106cd3ed91513dd65d,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/shop/inventory/category/{inventoryCategory}/edit'
 */
const edit324d80c462e18f63cf9cdb079563791e = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit324d80c462e18f63cf9cdb079563791e.url(args, options),
    method: 'get',
})

edit324d80c462e18f63cf9cdb079563791e.definition = {
    methods: ["get","head"],
    url: '/shop/inventory/category/{inventoryCategory}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/shop/inventory/category/{inventoryCategory}/edit'
 */
edit324d80c462e18f63cf9cdb079563791e.url = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return edit324d80c462e18f63cf9cdb079563791e.definition.url
            .replace('{inventoryCategory}', parsedArgs.inventoryCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/shop/inventory/category/{inventoryCategory}/edit'
 */
edit324d80c462e18f63cf9cdb079563791e.get = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit324d80c462e18f63cf9cdb079563791e.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/shop/inventory/category/{inventoryCategory}/edit'
 */
edit324d80c462e18f63cf9cdb079563791e.head = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit324d80c462e18f63cf9cdb079563791e.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/shop/inventory/category/{inventoryCategory}/edit'
 */
    const edit324d80c462e18f63cf9cdb079563791eForm = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit324d80c462e18f63cf9cdb079563791e.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/shop/inventory/category/{inventoryCategory}/edit'
 */
        edit324d80c462e18f63cf9cdb079563791eForm.get = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit324d80c462e18f63cf9cdb079563791e.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/shop/inventory/category/{inventoryCategory}/edit'
 */
        edit324d80c462e18f63cf9cdb079563791eForm.head = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit324d80c462e18f63cf9cdb079563791e.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit324d80c462e18f63cf9cdb079563791e.form = edit324d80c462e18f63cf9cdb079563791eForm
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/staff/inventory/category/{inventoryCategory}/edit'
 */
const edit7a4b600ef9faa62f3579c2eb4dbbae6c = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit7a4b600ef9faa62f3579c2eb4dbbae6c.url(args, options),
    method: 'get',
})

edit7a4b600ef9faa62f3579c2eb4dbbae6c.definition = {
    methods: ["get","head"],
    url: '/staff/inventory/category/{inventoryCategory}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/staff/inventory/category/{inventoryCategory}/edit'
 */
edit7a4b600ef9faa62f3579c2eb4dbbae6c.url = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return edit7a4b600ef9faa62f3579c2eb4dbbae6c.definition.url
            .replace('{inventoryCategory}', parsedArgs.inventoryCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/staff/inventory/category/{inventoryCategory}/edit'
 */
edit7a4b600ef9faa62f3579c2eb4dbbae6c.get = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit7a4b600ef9faa62f3579c2eb4dbbae6c.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/staff/inventory/category/{inventoryCategory}/edit'
 */
edit7a4b600ef9faa62f3579c2eb4dbbae6c.head = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit7a4b600ef9faa62f3579c2eb4dbbae6c.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/staff/inventory/category/{inventoryCategory}/edit'
 */
    const edit7a4b600ef9faa62f3579c2eb4dbbae6cForm = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit7a4b600ef9faa62f3579c2eb4dbbae6c.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/staff/inventory/category/{inventoryCategory}/edit'
 */
        edit7a4b600ef9faa62f3579c2eb4dbbae6cForm.get = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit7a4b600ef9faa62f3579c2eb4dbbae6c.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::edit
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:62
 * @route '/staff/inventory/category/{inventoryCategory}/edit'
 */
        edit7a4b600ef9faa62f3579c2eb4dbbae6cForm.head = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit7a4b600ef9faa62f3579c2eb4dbbae6c.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit7a4b600ef9faa62f3579c2eb4dbbae6c.form = edit7a4b600ef9faa62f3579c2eb4dbbae6cForm

export const edit = {
    '/shop/inventory/category/{inventoryCategory}/edit': edit324d80c462e18f63cf9cdb079563791e,
    '/staff/inventory/category/{inventoryCategory}/edit': edit7a4b600ef9faa62f3579c2eb4dbbae6c,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:72
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
const update6b3cc540c8f21bba5e29d8ccf7b47446 = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update6b3cc540c8f21bba5e29d8ccf7b47446.url(args, options),
    method: 'put',
})

update6b3cc540c8f21bba5e29d8ccf7b47446.definition = {
    methods: ["put"],
    url: '/shop/inventory/category/{inventoryCategory}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:72
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
update6b3cc540c8f21bba5e29d8ccf7b47446.url = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return update6b3cc540c8f21bba5e29d8ccf7b47446.definition.url
            .replace('{inventoryCategory}', parsedArgs.inventoryCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:72
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
update6b3cc540c8f21bba5e29d8ccf7b47446.put = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update6b3cc540c8f21bba5e29d8ccf7b47446.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:72
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
    const update6b3cc540c8f21bba5e29d8ccf7b47446Form = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update6b3cc540c8f21bba5e29d8ccf7b47446.url(args, {
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
        update6b3cc540c8f21bba5e29d8ccf7b47446Form.put = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update6b3cc540c8f21bba5e29d8ccf7b47446.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update6b3cc540c8f21bba5e29d8ccf7b47446.form = update6b3cc540c8f21bba5e29d8ccf7b47446Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:72
 * @route '/staff/inventory/category/{inventoryCategory}'
 */
const update14331b5d6fffa767cb2d8427688bd8aa = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update14331b5d6fffa767cb2d8427688bd8aa.url(args, options),
    method: 'put',
})

update14331b5d6fffa767cb2d8427688bd8aa.definition = {
    methods: ["put"],
    url: '/staff/inventory/category/{inventoryCategory}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:72
 * @route '/staff/inventory/category/{inventoryCategory}'
 */
update14331b5d6fffa767cb2d8427688bd8aa.url = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return update14331b5d6fffa767cb2d8427688bd8aa.definition.url
            .replace('{inventoryCategory}', parsedArgs.inventoryCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:72
 * @route '/staff/inventory/category/{inventoryCategory}'
 */
update14331b5d6fffa767cb2d8427688bd8aa.put = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update14331b5d6fffa767cb2d8427688bd8aa.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::update
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:72
 * @route '/staff/inventory/category/{inventoryCategory}'
 */
    const update14331b5d6fffa767cb2d8427688bd8aaForm = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update14331b5d6fffa767cb2d8427688bd8aa.url(args, {
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
 * @route '/staff/inventory/category/{inventoryCategory}'
 */
        update14331b5d6fffa767cb2d8427688bd8aaForm.put = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update14331b5d6fffa767cb2d8427688bd8aa.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update14331b5d6fffa767cb2d8427688bd8aa.form = update14331b5d6fffa767cb2d8427688bd8aaForm

export const update = {
    '/shop/inventory/category/{inventoryCategory}': update6b3cc540c8f21bba5e29d8ccf7b47446,
    '/staff/inventory/category/{inventoryCategory}': update14331b5d6fffa767cb2d8427688bd8aa,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:86
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
const destroy6b3cc540c8f21bba5e29d8ccf7b47446 = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy6b3cc540c8f21bba5e29d8ccf7b47446.url(args, options),
    method: 'delete',
})

destroy6b3cc540c8f21bba5e29d8ccf7b47446.definition = {
    methods: ["delete"],
    url: '/shop/inventory/category/{inventoryCategory}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:86
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
destroy6b3cc540c8f21bba5e29d8ccf7b47446.url = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return destroy6b3cc540c8f21bba5e29d8ccf7b47446.definition.url
            .replace('{inventoryCategory}', parsedArgs.inventoryCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:86
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
destroy6b3cc540c8f21bba5e29d8ccf7b47446.delete = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy6b3cc540c8f21bba5e29d8ccf7b47446.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:86
 * @route '/shop/inventory/category/{inventoryCategory}'
 */
    const destroy6b3cc540c8f21bba5e29d8ccf7b47446Form = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy6b3cc540c8f21bba5e29d8ccf7b47446.url(args, {
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
        destroy6b3cc540c8f21bba5e29d8ccf7b47446Form.delete = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy6b3cc540c8f21bba5e29d8ccf7b47446.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy6b3cc540c8f21bba5e29d8ccf7b47446.form = destroy6b3cc540c8f21bba5e29d8ccf7b47446Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:86
 * @route '/staff/inventory/category/{inventoryCategory}'
 */
const destroy14331b5d6fffa767cb2d8427688bd8aa = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy14331b5d6fffa767cb2d8427688bd8aa.url(args, options),
    method: 'delete',
})

destroy14331b5d6fffa767cb2d8427688bd8aa.definition = {
    methods: ["delete"],
    url: '/staff/inventory/category/{inventoryCategory}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:86
 * @route '/staff/inventory/category/{inventoryCategory}'
 */
destroy14331b5d6fffa767cb2d8427688bd8aa.url = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return destroy14331b5d6fffa767cb2d8427688bd8aa.definition.url
            .replace('{inventoryCategory}', parsedArgs.inventoryCategory.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:86
 * @route '/staff/inventory/category/{inventoryCategory}'
 */
destroy14331b5d6fffa767cb2d8427688bd8aa.delete = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy14331b5d6fffa767cb2d8427688bd8aa.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::destroy
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:86
 * @route '/staff/inventory/category/{inventoryCategory}'
 */
    const destroy14331b5d6fffa767cb2d8427688bd8aaForm = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy14331b5d6fffa767cb2d8427688bd8aa.url(args, {
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
 * @route '/staff/inventory/category/{inventoryCategory}'
 */
        destroy14331b5d6fffa767cb2d8427688bd8aaForm.delete = (args: { inventoryCategory: string | number | { id: string | number } } | [inventoryCategory: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy14331b5d6fffa767cb2d8427688bd8aa.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy14331b5d6fffa767cb2d8427688bd8aa.form = destroy14331b5d6fffa767cb2d8427688bd8aaForm

export const destroy = {
    '/shop/inventory/category/{inventoryCategory}': destroy6b3cc540c8f21bba5e29d8ccf7b47446,
    '/staff/inventory/category/{inventoryCategory}': destroy14331b5d6fffa767cb2d8427688bd8aa,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/shop/inventory/category/archive'
 */
const archive404c773398fe82fdd774ec7026f3c784 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive404c773398fe82fdd774ec7026f3c784.url(options),
    method: 'get',
})

archive404c773398fe82fdd774ec7026f3c784.definition = {
    methods: ["get","head"],
    url: '/shop/inventory/category/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/shop/inventory/category/archive'
 */
archive404c773398fe82fdd774ec7026f3c784.url = (options?: RouteQueryOptions) => {
    return archive404c773398fe82fdd774ec7026f3c784.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/shop/inventory/category/archive'
 */
archive404c773398fe82fdd774ec7026f3c784.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive404c773398fe82fdd774ec7026f3c784.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/shop/inventory/category/archive'
 */
archive404c773398fe82fdd774ec7026f3c784.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archive404c773398fe82fdd774ec7026f3c784.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/shop/inventory/category/archive'
 */
    const archive404c773398fe82fdd774ec7026f3c784Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: archive404c773398fe82fdd774ec7026f3c784.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/shop/inventory/category/archive'
 */
        archive404c773398fe82fdd774ec7026f3c784Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archive404c773398fe82fdd774ec7026f3c784.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/shop/inventory/category/archive'
 */
        archive404c773398fe82fdd774ec7026f3c784Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archive404c773398fe82fdd774ec7026f3c784.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    archive404c773398fe82fdd774ec7026f3c784.form = archive404c773398fe82fdd774ec7026f3c784Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/staff/inventory/category/archive'
 */
const archive7398841bee1be9d2b8b2ee1c12a9d9dd = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive7398841bee1be9d2b8b2ee1c12a9d9dd.url(options),
    method: 'get',
})

archive7398841bee1be9d2b8b2ee1c12a9d9dd.definition = {
    methods: ["get","head"],
    url: '/staff/inventory/category/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/staff/inventory/category/archive'
 */
archive7398841bee1be9d2b8b2ee1c12a9d9dd.url = (options?: RouteQueryOptions) => {
    return archive7398841bee1be9d2b8b2ee1c12a9d9dd.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/staff/inventory/category/archive'
 */
archive7398841bee1be9d2b8b2ee1c12a9d9dd.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive7398841bee1be9d2b8b2ee1c12a9d9dd.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/staff/inventory/category/archive'
 */
archive7398841bee1be9d2b8b2ee1c12a9d9dd.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archive7398841bee1be9d2b8b2ee1c12a9d9dd.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/staff/inventory/category/archive'
 */
    const archive7398841bee1be9d2b8b2ee1c12a9d9ddForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: archive7398841bee1be9d2b8b2ee1c12a9d9dd.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/staff/inventory/category/archive'
 */
        archive7398841bee1be9d2b8b2ee1c12a9d9ddForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archive7398841bee1be9d2b8b2ee1c12a9d9dd.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::archive
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:93
 * @route '/staff/inventory/category/archive'
 */
        archive7398841bee1be9d2b8b2ee1c12a9d9ddForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archive7398841bee1be9d2b8b2ee1c12a9d9dd.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    archive7398841bee1be9d2b8b2ee1c12a9d9dd.form = archive7398841bee1be9d2b8b2ee1c12a9d9ddForm

export const archive = {
    '/shop/inventory/category/archive': archive404c773398fe82fdd774ec7026f3c784,
    '/staff/inventory/category/archive': archive7398841bee1be9d2b8b2ee1c12a9d9dd,
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/shop/inventory/category/{id}/restore'
 */
const restore0a1dd82d80f4561d47f5a940c4337ea8 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore0a1dd82d80f4561d47f5a940c4337ea8.url(args, options),
    method: 'post',
})

restore0a1dd82d80f4561d47f5a940c4337ea8.definition = {
    methods: ["post"],
    url: '/shop/inventory/category/{id}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/shop/inventory/category/{id}/restore'
 */
restore0a1dd82d80f4561d47f5a940c4337ea8.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return restore0a1dd82d80f4561d47f5a940c4337ea8.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/shop/inventory/category/{id}/restore'
 */
restore0a1dd82d80f4561d47f5a940c4337ea8.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore0a1dd82d80f4561d47f5a940c4337ea8.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/shop/inventory/category/{id}/restore'
 */
    const restore0a1dd82d80f4561d47f5a940c4337ea8Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore0a1dd82d80f4561d47f5a940c4337ea8.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/shop/inventory/category/{id}/restore'
 */
        restore0a1dd82d80f4561d47f5a940c4337ea8Form.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore0a1dd82d80f4561d47f5a940c4337ea8.url(args, options),
            method: 'post',
        })
    
    restore0a1dd82d80f4561d47f5a940c4337ea8.form = restore0a1dd82d80f4561d47f5a940c4337ea8Form
    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/staff/inventory/category/{id}/restore'
 */
const restore25301f02e32be36e1eee3446d9bd5a17 = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore25301f02e32be36e1eee3446d9bd5a17.url(args, options),
    method: 'post',
})

restore25301f02e32be36e1eee3446d9bd5a17.definition = {
    methods: ["post"],
    url: '/staff/inventory/category/{id}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/staff/inventory/category/{id}/restore'
 */
restore25301f02e32be36e1eee3446d9bd5a17.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return restore25301f02e32be36e1eee3446d9bd5a17.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/staff/inventory/category/{id}/restore'
 */
restore25301f02e32be36e1eee3446d9bd5a17.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore25301f02e32be36e1eee3446d9bd5a17.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/staff/inventory/category/{id}/restore'
 */
    const restore25301f02e32be36e1eee3446d9bd5a17Form = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore25301f02e32be36e1eee3446d9bd5a17.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Inventory\InventoryCategoryController::restore
 * @see app/Http/Controllers/Shop/Inventory/InventoryCategoryController.php:102
 * @route '/staff/inventory/category/{id}/restore'
 */
        restore25301f02e32be36e1eee3446d9bd5a17Form.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore25301f02e32be36e1eee3446d9bd5a17.url(args, options),
            method: 'post',
        })
    
    restore25301f02e32be36e1eee3446d9bd5a17.form = restore25301f02e32be36e1eee3446d9bd5a17Form

export const restore = {
    '/shop/inventory/category/{id}/restore': restore0a1dd82d80f4561d47f5a940c4337ea8,
    '/staff/inventory/category/{id}/restore': restore25301f02e32be36e1eee3446d9bd5a17,
}

const InventoryCategoryController = { index, store, edit, update, destroy, archive, restore }

export default InventoryCategoryController