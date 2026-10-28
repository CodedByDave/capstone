import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:44
 * @route '/shop/operations/services'
 */
const indexc2022b34c03cfcdd3e3ae396048f9985 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexc2022b34c03cfcdd3e3ae396048f9985.url(options),
    method: 'get',
})

indexc2022b34c03cfcdd3e3ae396048f9985.definition = {
    methods: ["get","head"],
    url: '/shop/operations/services',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:44
 * @route '/shop/operations/services'
 */
indexc2022b34c03cfcdd3e3ae396048f9985.url = (options?: RouteQueryOptions) => {
    return indexc2022b34c03cfcdd3e3ae396048f9985.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:44
 * @route '/shop/operations/services'
 */
indexc2022b34c03cfcdd3e3ae396048f9985.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexc2022b34c03cfcdd3e3ae396048f9985.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:44
 * @route '/shop/operations/services'
 */
indexc2022b34c03cfcdd3e3ae396048f9985.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexc2022b34c03cfcdd3e3ae396048f9985.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:44
 * @route '/shop/operations/services'
 */
    const indexc2022b34c03cfcdd3e3ae396048f9985Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexc2022b34c03cfcdd3e3ae396048f9985.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:44
 * @route '/shop/operations/services'
 */
        indexc2022b34c03cfcdd3e3ae396048f9985Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexc2022b34c03cfcdd3e3ae396048f9985.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:44
 * @route '/shop/operations/services'
 */
        indexc2022b34c03cfcdd3e3ae396048f9985Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexc2022b34c03cfcdd3e3ae396048f9985.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexc2022b34c03cfcdd3e3ae396048f9985.form = indexc2022b34c03cfcdd3e3ae396048f9985Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:44
 * @route '/staff/operations/services'
 */
const index803712c8ef05166c8ed1abbc87997ea5 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index803712c8ef05166c8ed1abbc87997ea5.url(options),
    method: 'get',
})

index803712c8ef05166c8ed1abbc87997ea5.definition = {
    methods: ["get","head"],
    url: '/staff/operations/services',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:44
 * @route '/staff/operations/services'
 */
index803712c8ef05166c8ed1abbc87997ea5.url = (options?: RouteQueryOptions) => {
    return index803712c8ef05166c8ed1abbc87997ea5.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:44
 * @route '/staff/operations/services'
 */
index803712c8ef05166c8ed1abbc87997ea5.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index803712c8ef05166c8ed1abbc87997ea5.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:44
 * @route '/staff/operations/services'
 */
index803712c8ef05166c8ed1abbc87997ea5.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index803712c8ef05166c8ed1abbc87997ea5.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:44
 * @route '/staff/operations/services'
 */
    const index803712c8ef05166c8ed1abbc87997ea5Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index803712c8ef05166c8ed1abbc87997ea5.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:44
 * @route '/staff/operations/services'
 */
        index803712c8ef05166c8ed1abbc87997ea5Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index803712c8ef05166c8ed1abbc87997ea5.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:44
 * @route '/staff/operations/services'
 */
        index803712c8ef05166c8ed1abbc87997ea5Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index803712c8ef05166c8ed1abbc87997ea5.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index803712c8ef05166c8ed1abbc87997ea5.form = index803712c8ef05166c8ed1abbc87997ea5Form

export const index = {
    '/shop/operations/services': indexc2022b34c03cfcdd3e3ae396048f9985,
    '/staff/operations/services': index803712c8ef05166c8ed1abbc87997ea5,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:81
 * @route '/shop/operations/services/create'
 */
const createe90efd62acece0aa3b66f235459feb13 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: createe90efd62acece0aa3b66f235459feb13.url(options),
    method: 'get',
})

createe90efd62acece0aa3b66f235459feb13.definition = {
    methods: ["get","head"],
    url: '/shop/operations/services/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:81
 * @route '/shop/operations/services/create'
 */
createe90efd62acece0aa3b66f235459feb13.url = (options?: RouteQueryOptions) => {
    return createe90efd62acece0aa3b66f235459feb13.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:81
 * @route '/shop/operations/services/create'
 */
createe90efd62acece0aa3b66f235459feb13.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: createe90efd62acece0aa3b66f235459feb13.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:81
 * @route '/shop/operations/services/create'
 */
createe90efd62acece0aa3b66f235459feb13.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: createe90efd62acece0aa3b66f235459feb13.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:81
 * @route '/shop/operations/services/create'
 */
    const createe90efd62acece0aa3b66f235459feb13Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: createe90efd62acece0aa3b66f235459feb13.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:81
 * @route '/shop/operations/services/create'
 */
        createe90efd62acece0aa3b66f235459feb13Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: createe90efd62acece0aa3b66f235459feb13.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:81
 * @route '/shop/operations/services/create'
 */
        createe90efd62acece0aa3b66f235459feb13Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: createe90efd62acece0aa3b66f235459feb13.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    createe90efd62acece0aa3b66f235459feb13.form = createe90efd62acece0aa3b66f235459feb13Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:81
 * @route '/staff/operations/services/create'
 */
const create375cb51c967a6371cfdd48e70569819f = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create375cb51c967a6371cfdd48e70569819f.url(options),
    method: 'get',
})

create375cb51c967a6371cfdd48e70569819f.definition = {
    methods: ["get","head"],
    url: '/staff/operations/services/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:81
 * @route '/staff/operations/services/create'
 */
create375cb51c967a6371cfdd48e70569819f.url = (options?: RouteQueryOptions) => {
    return create375cb51c967a6371cfdd48e70569819f.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:81
 * @route '/staff/operations/services/create'
 */
create375cb51c967a6371cfdd48e70569819f.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create375cb51c967a6371cfdd48e70569819f.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:81
 * @route '/staff/operations/services/create'
 */
create375cb51c967a6371cfdd48e70569819f.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create375cb51c967a6371cfdd48e70569819f.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:81
 * @route '/staff/operations/services/create'
 */
    const create375cb51c967a6371cfdd48e70569819fForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create375cb51c967a6371cfdd48e70569819f.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:81
 * @route '/staff/operations/services/create'
 */
        create375cb51c967a6371cfdd48e70569819fForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create375cb51c967a6371cfdd48e70569819f.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:81
 * @route '/staff/operations/services/create'
 */
        create375cb51c967a6371cfdd48e70569819fForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create375cb51c967a6371cfdd48e70569819f.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create375cb51c967a6371cfdd48e70569819f.form = create375cb51c967a6371cfdd48e70569819fForm

export const create = {
    '/shop/operations/services/create': createe90efd62acece0aa3b66f235459feb13,
    '/staff/operations/services/create': create375cb51c967a6371cfdd48e70569819f,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:92
 * @route '/shop/operations/services'
 */
const storec2022b34c03cfcdd3e3ae396048f9985 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storec2022b34c03cfcdd3e3ae396048f9985.url(options),
    method: 'post',
})

storec2022b34c03cfcdd3e3ae396048f9985.definition = {
    methods: ["post"],
    url: '/shop/operations/services',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:92
 * @route '/shop/operations/services'
 */
storec2022b34c03cfcdd3e3ae396048f9985.url = (options?: RouteQueryOptions) => {
    return storec2022b34c03cfcdd3e3ae396048f9985.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:92
 * @route '/shop/operations/services'
 */
storec2022b34c03cfcdd3e3ae396048f9985.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storec2022b34c03cfcdd3e3ae396048f9985.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:92
 * @route '/shop/operations/services'
 */
    const storec2022b34c03cfcdd3e3ae396048f9985Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: storec2022b34c03cfcdd3e3ae396048f9985.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:92
 * @route '/shop/operations/services'
 */
        storec2022b34c03cfcdd3e3ae396048f9985Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: storec2022b34c03cfcdd3e3ae396048f9985.url(options),
            method: 'post',
        })
    
    storec2022b34c03cfcdd3e3ae396048f9985.form = storec2022b34c03cfcdd3e3ae396048f9985Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:92
 * @route '/staff/operations/services'
 */
const store803712c8ef05166c8ed1abbc87997ea5 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store803712c8ef05166c8ed1abbc87997ea5.url(options),
    method: 'post',
})

store803712c8ef05166c8ed1abbc87997ea5.definition = {
    methods: ["post"],
    url: '/staff/operations/services',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:92
 * @route '/staff/operations/services'
 */
store803712c8ef05166c8ed1abbc87997ea5.url = (options?: RouteQueryOptions) => {
    return store803712c8ef05166c8ed1abbc87997ea5.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:92
 * @route '/staff/operations/services'
 */
store803712c8ef05166c8ed1abbc87997ea5.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store803712c8ef05166c8ed1abbc87997ea5.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:92
 * @route '/staff/operations/services'
 */
    const store803712c8ef05166c8ed1abbc87997ea5Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store803712c8ef05166c8ed1abbc87997ea5.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:92
 * @route '/staff/operations/services'
 */
        store803712c8ef05166c8ed1abbc87997ea5Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store803712c8ef05166c8ed1abbc87997ea5.url(options),
            method: 'post',
        })
    
    store803712c8ef05166c8ed1abbc87997ea5.form = store803712c8ef05166c8ed1abbc87997ea5Form

export const store = {
    '/shop/operations/services': storec2022b34c03cfcdd3e3ae396048f9985,
    '/staff/operations/services': store803712c8ef05166c8ed1abbc87997ea5,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:57
 * @route '/shop/operations/services/export'
 */
const exportMethod11b2bec68271b8c2aab4bbb123659c1e = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod11b2bec68271b8c2aab4bbb123659c1e.url(options),
    method: 'get',
})

exportMethod11b2bec68271b8c2aab4bbb123659c1e.definition = {
    methods: ["get","head"],
    url: '/shop/operations/services/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:57
 * @route '/shop/operations/services/export'
 */
exportMethod11b2bec68271b8c2aab4bbb123659c1e.url = (options?: RouteQueryOptions) => {
    return exportMethod11b2bec68271b8c2aab4bbb123659c1e.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:57
 * @route '/shop/operations/services/export'
 */
exportMethod11b2bec68271b8c2aab4bbb123659c1e.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod11b2bec68271b8c2aab4bbb123659c1e.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:57
 * @route '/shop/operations/services/export'
 */
exportMethod11b2bec68271b8c2aab4bbb123659c1e.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod11b2bec68271b8c2aab4bbb123659c1e.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:57
 * @route '/shop/operations/services/export'
 */
    const exportMethod11b2bec68271b8c2aab4bbb123659c1eForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMethod11b2bec68271b8c2aab4bbb123659c1e.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:57
 * @route '/shop/operations/services/export'
 */
        exportMethod11b2bec68271b8c2aab4bbb123659c1eForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod11b2bec68271b8c2aab4bbb123659c1e.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:57
 * @route '/shop/operations/services/export'
 */
        exportMethod11b2bec68271b8c2aab4bbb123659c1eForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod11b2bec68271b8c2aab4bbb123659c1e.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportMethod11b2bec68271b8c2aab4bbb123659c1e.form = exportMethod11b2bec68271b8c2aab4bbb123659c1eForm
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:57
 * @route '/staff/operations/services/export'
 */
const exportMethod7048c9ad286b8ba47c7bf15131776b2e = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod7048c9ad286b8ba47c7bf15131776b2e.url(options),
    method: 'get',
})

exportMethod7048c9ad286b8ba47c7bf15131776b2e.definition = {
    methods: ["get","head"],
    url: '/staff/operations/services/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:57
 * @route '/staff/operations/services/export'
 */
exportMethod7048c9ad286b8ba47c7bf15131776b2e.url = (options?: RouteQueryOptions) => {
    return exportMethod7048c9ad286b8ba47c7bf15131776b2e.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:57
 * @route '/staff/operations/services/export'
 */
exportMethod7048c9ad286b8ba47c7bf15131776b2e.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod7048c9ad286b8ba47c7bf15131776b2e.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:57
 * @route '/staff/operations/services/export'
 */
exportMethod7048c9ad286b8ba47c7bf15131776b2e.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod7048c9ad286b8ba47c7bf15131776b2e.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:57
 * @route '/staff/operations/services/export'
 */
    const exportMethod7048c9ad286b8ba47c7bf15131776b2eForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMethod7048c9ad286b8ba47c7bf15131776b2e.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:57
 * @route '/staff/operations/services/export'
 */
        exportMethod7048c9ad286b8ba47c7bf15131776b2eForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod7048c9ad286b8ba47c7bf15131776b2e.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:57
 * @route '/staff/operations/services/export'
 */
        exportMethod7048c9ad286b8ba47c7bf15131776b2eForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod7048c9ad286b8ba47c7bf15131776b2e.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportMethod7048c9ad286b8ba47c7bf15131776b2e.form = exportMethod7048c9ad286b8ba47c7bf15131776b2eForm

export const exportMethod = {
    '/shop/operations/services/export': exportMethod11b2bec68271b8c2aab4bbb123659c1e,
    '/staff/operations/services/export': exportMethod7048c9ad286b8ba47c7bf15131776b2e,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:65
 * @route '/shop/operations/services/import-template'
 */
const importTemplatee517ad76fbd3d1929c2d72d925a06837 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: importTemplatee517ad76fbd3d1929c2d72d925a06837.url(options),
    method: 'get',
})

importTemplatee517ad76fbd3d1929c2d72d925a06837.definition = {
    methods: ["get","head"],
    url: '/shop/operations/services/import-template',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:65
 * @route '/shop/operations/services/import-template'
 */
importTemplatee517ad76fbd3d1929c2d72d925a06837.url = (options?: RouteQueryOptions) => {
    return importTemplatee517ad76fbd3d1929c2d72d925a06837.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:65
 * @route '/shop/operations/services/import-template'
 */
importTemplatee517ad76fbd3d1929c2d72d925a06837.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: importTemplatee517ad76fbd3d1929c2d72d925a06837.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:65
 * @route '/shop/operations/services/import-template'
 */
importTemplatee517ad76fbd3d1929c2d72d925a06837.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: importTemplatee517ad76fbd3d1929c2d72d925a06837.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:65
 * @route '/shop/operations/services/import-template'
 */
    const importTemplatee517ad76fbd3d1929c2d72d925a06837Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: importTemplatee517ad76fbd3d1929c2d72d925a06837.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:65
 * @route '/shop/operations/services/import-template'
 */
        importTemplatee517ad76fbd3d1929c2d72d925a06837Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: importTemplatee517ad76fbd3d1929c2d72d925a06837.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:65
 * @route '/shop/operations/services/import-template'
 */
        importTemplatee517ad76fbd3d1929c2d72d925a06837Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: importTemplatee517ad76fbd3d1929c2d72d925a06837.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    importTemplatee517ad76fbd3d1929c2d72d925a06837.form = importTemplatee517ad76fbd3d1929c2d72d925a06837Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:65
 * @route '/staff/operations/services/import-template'
 */
const importTemplateddd938f2fd5f0410d4f0a673aa77d2ff = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: importTemplateddd938f2fd5f0410d4f0a673aa77d2ff.url(options),
    method: 'get',
})

importTemplateddd938f2fd5f0410d4f0a673aa77d2ff.definition = {
    methods: ["get","head"],
    url: '/staff/operations/services/import-template',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:65
 * @route '/staff/operations/services/import-template'
 */
importTemplateddd938f2fd5f0410d4f0a673aa77d2ff.url = (options?: RouteQueryOptions) => {
    return importTemplateddd938f2fd5f0410d4f0a673aa77d2ff.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:65
 * @route '/staff/operations/services/import-template'
 */
importTemplateddd938f2fd5f0410d4f0a673aa77d2ff.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: importTemplateddd938f2fd5f0410d4f0a673aa77d2ff.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:65
 * @route '/staff/operations/services/import-template'
 */
importTemplateddd938f2fd5f0410d4f0a673aa77d2ff.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: importTemplateddd938f2fd5f0410d4f0a673aa77d2ff.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:65
 * @route '/staff/operations/services/import-template'
 */
    const importTemplateddd938f2fd5f0410d4f0a673aa77d2ffForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: importTemplateddd938f2fd5f0410d4f0a673aa77d2ff.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:65
 * @route '/staff/operations/services/import-template'
 */
        importTemplateddd938f2fd5f0410d4f0a673aa77d2ffForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: importTemplateddd938f2fd5f0410d4f0a673aa77d2ff.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:65
 * @route '/staff/operations/services/import-template'
 */
        importTemplateddd938f2fd5f0410d4f0a673aa77d2ffForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: importTemplateddd938f2fd5f0410d4f0a673aa77d2ff.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    importTemplateddd938f2fd5f0410d4f0a673aa77d2ff.form = importTemplateddd938f2fd5f0410d4f0a673aa77d2ffForm

export const importTemplate = {
    '/shop/operations/services/import-template': importTemplatee517ad76fbd3d1929c2d72d925a06837,
    '/staff/operations/services/import-template': importTemplateddd938f2fd5f0410d4f0a673aa77d2ff,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:70
 * @route '/shop/operations/services/import'
 */
const importMethodc5bf634f3d8e908370efe6531b26558f = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethodc5bf634f3d8e908370efe6531b26558f.url(options),
    method: 'post',
})

importMethodc5bf634f3d8e908370efe6531b26558f.definition = {
    methods: ["post"],
    url: '/shop/operations/services/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:70
 * @route '/shop/operations/services/import'
 */
importMethodc5bf634f3d8e908370efe6531b26558f.url = (options?: RouteQueryOptions) => {
    return importMethodc5bf634f3d8e908370efe6531b26558f.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:70
 * @route '/shop/operations/services/import'
 */
importMethodc5bf634f3d8e908370efe6531b26558f.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethodc5bf634f3d8e908370efe6531b26558f.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:70
 * @route '/shop/operations/services/import'
 */
    const importMethodc5bf634f3d8e908370efe6531b26558fForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: importMethodc5bf634f3d8e908370efe6531b26558f.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:70
 * @route '/shop/operations/services/import'
 */
        importMethodc5bf634f3d8e908370efe6531b26558fForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: importMethodc5bf634f3d8e908370efe6531b26558f.url(options),
            method: 'post',
        })
    
    importMethodc5bf634f3d8e908370efe6531b26558f.form = importMethodc5bf634f3d8e908370efe6531b26558fForm
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:70
 * @route '/staff/operations/services/import'
 */
const importMethodffda4e931fa27de99f8a9bece6e9220d = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethodffda4e931fa27de99f8a9bece6e9220d.url(options),
    method: 'post',
})

importMethodffda4e931fa27de99f8a9bece6e9220d.definition = {
    methods: ["post"],
    url: '/staff/operations/services/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:70
 * @route '/staff/operations/services/import'
 */
importMethodffda4e931fa27de99f8a9bece6e9220d.url = (options?: RouteQueryOptions) => {
    return importMethodffda4e931fa27de99f8a9bece6e9220d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:70
 * @route '/staff/operations/services/import'
 */
importMethodffda4e931fa27de99f8a9bece6e9220d.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethodffda4e931fa27de99f8a9bece6e9220d.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:70
 * @route '/staff/operations/services/import'
 */
    const importMethodffda4e931fa27de99f8a9bece6e9220dForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: importMethodffda4e931fa27de99f8a9bece6e9220d.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:70
 * @route '/staff/operations/services/import'
 */
        importMethodffda4e931fa27de99f8a9bece6e9220dForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: importMethodffda4e931fa27de99f8a9bece6e9220d.url(options),
            method: 'post',
        })
    
    importMethodffda4e931fa27de99f8a9bece6e9220d.form = importMethodffda4e931fa27de99f8a9bece6e9220dForm

export const importMethod = {
    '/shop/operations/services/import': importMethodc5bf634f3d8e908370efe6531b26558f,
    '/staff/operations/services/import': importMethodffda4e931fa27de99f8a9bece6e9220d,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:169
 * @route '/shop/operations/services/restore-all'
 */
const restoreAll2c8131e5baea3dee26b995f349c1452a = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreAll2c8131e5baea3dee26b995f349c1452a.url(options),
    method: 'post',
})

restoreAll2c8131e5baea3dee26b995f349c1452a.definition = {
    methods: ["post"],
    url: '/shop/operations/services/restore-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:169
 * @route '/shop/operations/services/restore-all'
 */
restoreAll2c8131e5baea3dee26b995f349c1452a.url = (options?: RouteQueryOptions) => {
    return restoreAll2c8131e5baea3dee26b995f349c1452a.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:169
 * @route '/shop/operations/services/restore-all'
 */
restoreAll2c8131e5baea3dee26b995f349c1452a.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreAll2c8131e5baea3dee26b995f349c1452a.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:169
 * @route '/shop/operations/services/restore-all'
 */
    const restoreAll2c8131e5baea3dee26b995f349c1452aForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restoreAll2c8131e5baea3dee26b995f349c1452a.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:169
 * @route '/shop/operations/services/restore-all'
 */
        restoreAll2c8131e5baea3dee26b995f349c1452aForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restoreAll2c8131e5baea3dee26b995f349c1452a.url(options),
            method: 'post',
        })
    
    restoreAll2c8131e5baea3dee26b995f349c1452a.form = restoreAll2c8131e5baea3dee26b995f349c1452aForm
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:169
 * @route '/staff/operations/services/restore-all'
 */
const restoreAll3a9d9c697ccbeb2854f59f7b7be1bb20 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreAll3a9d9c697ccbeb2854f59f7b7be1bb20.url(options),
    method: 'post',
})

restoreAll3a9d9c697ccbeb2854f59f7b7be1bb20.definition = {
    methods: ["post"],
    url: '/staff/operations/services/restore-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:169
 * @route '/staff/operations/services/restore-all'
 */
restoreAll3a9d9c697ccbeb2854f59f7b7be1bb20.url = (options?: RouteQueryOptions) => {
    return restoreAll3a9d9c697ccbeb2854f59f7b7be1bb20.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:169
 * @route '/staff/operations/services/restore-all'
 */
restoreAll3a9d9c697ccbeb2854f59f7b7be1bb20.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreAll3a9d9c697ccbeb2854f59f7b7be1bb20.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:169
 * @route '/staff/operations/services/restore-all'
 */
    const restoreAll3a9d9c697ccbeb2854f59f7b7be1bb20Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restoreAll3a9d9c697ccbeb2854f59f7b7be1bb20.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:169
 * @route '/staff/operations/services/restore-all'
 */
        restoreAll3a9d9c697ccbeb2854f59f7b7be1bb20Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restoreAll3a9d9c697ccbeb2854f59f7b7be1bb20.url(options),
            method: 'post',
        })
    
    restoreAll3a9d9c697ccbeb2854f59f7b7be1bb20.form = restoreAll3a9d9c697ccbeb2854f59f7b7be1bb20Form

export const restoreAll = {
    '/shop/operations/services/restore-all': restoreAll2c8131e5baea3dee26b995f349c1452a,
    '/staff/operations/services/restore-all': restoreAll3a9d9c697ccbeb2854f59f7b7be1bb20,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:106
 * @route '/shop/operations/services/{service}/edit'
 */
const editff96fda3a296604b1e57cdb10db1fd42 = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editff96fda3a296604b1e57cdb10db1fd42.url(args, options),
    method: 'get',
})

editff96fda3a296604b1e57cdb10db1fd42.definition = {
    methods: ["get","head"],
    url: '/shop/operations/services/{service}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:106
 * @route '/shop/operations/services/{service}/edit'
 */
editff96fda3a296604b1e57cdb10db1fd42.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { service: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { service: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    service: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        service: typeof args.service === 'object'
                ? args.service.id
                : args.service,
                }

    return editff96fda3a296604b1e57cdb10db1fd42.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:106
 * @route '/shop/operations/services/{service}/edit'
 */
editff96fda3a296604b1e57cdb10db1fd42.get = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editff96fda3a296604b1e57cdb10db1fd42.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:106
 * @route '/shop/operations/services/{service}/edit'
 */
editff96fda3a296604b1e57cdb10db1fd42.head = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: editff96fda3a296604b1e57cdb10db1fd42.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:106
 * @route '/shop/operations/services/{service}/edit'
 */
    const editff96fda3a296604b1e57cdb10db1fd42Form = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: editff96fda3a296604b1e57cdb10db1fd42.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:106
 * @route '/shop/operations/services/{service}/edit'
 */
        editff96fda3a296604b1e57cdb10db1fd42Form.get = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: editff96fda3a296604b1e57cdb10db1fd42.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:106
 * @route '/shop/operations/services/{service}/edit'
 */
        editff96fda3a296604b1e57cdb10db1fd42Form.head = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: editff96fda3a296604b1e57cdb10db1fd42.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    editff96fda3a296604b1e57cdb10db1fd42.form = editff96fda3a296604b1e57cdb10db1fd42Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:106
 * @route '/staff/operations/services/{service}/edit'
 */
const editae2dfaae269ba2620585b7ae9512af71 = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editae2dfaae269ba2620585b7ae9512af71.url(args, options),
    method: 'get',
})

editae2dfaae269ba2620585b7ae9512af71.definition = {
    methods: ["get","head"],
    url: '/staff/operations/services/{service}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:106
 * @route '/staff/operations/services/{service}/edit'
 */
editae2dfaae269ba2620585b7ae9512af71.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { service: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { service: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    service: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        service: typeof args.service === 'object'
                ? args.service.id
                : args.service,
                }

    return editae2dfaae269ba2620585b7ae9512af71.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:106
 * @route '/staff/operations/services/{service}/edit'
 */
editae2dfaae269ba2620585b7ae9512af71.get = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: editae2dfaae269ba2620585b7ae9512af71.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:106
 * @route '/staff/operations/services/{service}/edit'
 */
editae2dfaae269ba2620585b7ae9512af71.head = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: editae2dfaae269ba2620585b7ae9512af71.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:106
 * @route '/staff/operations/services/{service}/edit'
 */
    const editae2dfaae269ba2620585b7ae9512af71Form = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: editae2dfaae269ba2620585b7ae9512af71.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:106
 * @route '/staff/operations/services/{service}/edit'
 */
        editae2dfaae269ba2620585b7ae9512af71Form.get = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: editae2dfaae269ba2620585b7ae9512af71.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:106
 * @route '/staff/operations/services/{service}/edit'
 */
        editae2dfaae269ba2620585b7ae9512af71Form.head = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: editae2dfaae269ba2620585b7ae9512af71.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    editae2dfaae269ba2620585b7ae9512af71.form = editae2dfaae269ba2620585b7ae9512af71Form

export const edit = {
    '/shop/operations/services/{service}/edit': editff96fda3a296604b1e57cdb10db1fd42,
    '/staff/operations/services/{service}/edit': editae2dfaae269ba2620585b7ae9512af71,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:118
 * @route '/shop/operations/services/{service}'
 */
const updatee9ab3d744718a35303c175ccfb883b75 = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatee9ab3d744718a35303c175ccfb883b75.url(args, options),
    method: 'put',
})

updatee9ab3d744718a35303c175ccfb883b75.definition = {
    methods: ["put"],
    url: '/shop/operations/services/{service}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:118
 * @route '/shop/operations/services/{service}'
 */
updatee9ab3d744718a35303c175ccfb883b75.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { service: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { service: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    service: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        service: typeof args.service === 'object'
                ? args.service.id
                : args.service,
                }

    return updatee9ab3d744718a35303c175ccfb883b75.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:118
 * @route '/shop/operations/services/{service}'
 */
updatee9ab3d744718a35303c175ccfb883b75.put = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatee9ab3d744718a35303c175ccfb883b75.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:118
 * @route '/shop/operations/services/{service}'
 */
    const updatee9ab3d744718a35303c175ccfb883b75Form = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updatee9ab3d744718a35303c175ccfb883b75.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:118
 * @route '/shop/operations/services/{service}'
 */
        updatee9ab3d744718a35303c175ccfb883b75Form.put = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updatee9ab3d744718a35303c175ccfb883b75.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updatee9ab3d744718a35303c175ccfb883b75.form = updatee9ab3d744718a35303c175ccfb883b75Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:118
 * @route '/staff/operations/services/{service}'
 */
const updatebb9db02789b70ee0b379bb45f3d6ad78 = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatebb9db02789b70ee0b379bb45f3d6ad78.url(args, options),
    method: 'put',
})

updatebb9db02789b70ee0b379bb45f3d6ad78.definition = {
    methods: ["put"],
    url: '/staff/operations/services/{service}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:118
 * @route '/staff/operations/services/{service}'
 */
updatebb9db02789b70ee0b379bb45f3d6ad78.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { service: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { service: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    service: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        service: typeof args.service === 'object'
                ? args.service.id
                : args.service,
                }

    return updatebb9db02789b70ee0b379bb45f3d6ad78.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:118
 * @route '/staff/operations/services/{service}'
 */
updatebb9db02789b70ee0b379bb45f3d6ad78.put = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: updatebb9db02789b70ee0b379bb45f3d6ad78.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:118
 * @route '/staff/operations/services/{service}'
 */
    const updatebb9db02789b70ee0b379bb45f3d6ad78Form = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: updatebb9db02789b70ee0b379bb45f3d6ad78.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:118
 * @route '/staff/operations/services/{service}'
 */
        updatebb9db02789b70ee0b379bb45f3d6ad78Form.put = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: updatebb9db02789b70ee0b379bb45f3d6ad78.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    updatebb9db02789b70ee0b379bb45f3d6ad78.form = updatebb9db02789b70ee0b379bb45f3d6ad78Form

export const update = {
    '/shop/operations/services/{service}': updatee9ab3d744718a35303c175ccfb883b75,
    '/staff/operations/services/{service}': updatebb9db02789b70ee0b379bb45f3d6ad78,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:136
 * @route '/shop/operations/services/{service}'
 */
const destroye9ab3d744718a35303c175ccfb883b75 = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroye9ab3d744718a35303c175ccfb883b75.url(args, options),
    method: 'delete',
})

destroye9ab3d744718a35303c175ccfb883b75.definition = {
    methods: ["delete"],
    url: '/shop/operations/services/{service}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:136
 * @route '/shop/operations/services/{service}'
 */
destroye9ab3d744718a35303c175ccfb883b75.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { service: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { service: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    service: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        service: typeof args.service === 'object'
                ? args.service.id
                : args.service,
                }

    return destroye9ab3d744718a35303c175ccfb883b75.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:136
 * @route '/shop/operations/services/{service}'
 */
destroye9ab3d744718a35303c175ccfb883b75.delete = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroye9ab3d744718a35303c175ccfb883b75.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:136
 * @route '/shop/operations/services/{service}'
 */
    const destroye9ab3d744718a35303c175ccfb883b75Form = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroye9ab3d744718a35303c175ccfb883b75.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:136
 * @route '/shop/operations/services/{service}'
 */
        destroye9ab3d744718a35303c175ccfb883b75Form.delete = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroye9ab3d744718a35303c175ccfb883b75.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroye9ab3d744718a35303c175ccfb883b75.form = destroye9ab3d744718a35303c175ccfb883b75Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:136
 * @route '/staff/operations/services/{service}'
 */
const destroybb9db02789b70ee0b379bb45f3d6ad78 = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroybb9db02789b70ee0b379bb45f3d6ad78.url(args, options),
    method: 'delete',
})

destroybb9db02789b70ee0b379bb45f3d6ad78.definition = {
    methods: ["delete"],
    url: '/staff/operations/services/{service}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:136
 * @route '/staff/operations/services/{service}'
 */
destroybb9db02789b70ee0b379bb45f3d6ad78.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { service: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { service: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    service: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        service: typeof args.service === 'object'
                ? args.service.id
                : args.service,
                }

    return destroybb9db02789b70ee0b379bb45f3d6ad78.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:136
 * @route '/staff/operations/services/{service}'
 */
destroybb9db02789b70ee0b379bb45f3d6ad78.delete = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroybb9db02789b70ee0b379bb45f3d6ad78.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:136
 * @route '/staff/operations/services/{service}'
 */
    const destroybb9db02789b70ee0b379bb45f3d6ad78Form = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroybb9db02789b70ee0b379bb45f3d6ad78.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:136
 * @route '/staff/operations/services/{service}'
 */
        destroybb9db02789b70ee0b379bb45f3d6ad78Form.delete = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroybb9db02789b70ee0b379bb45f3d6ad78.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroybb9db02789b70ee0b379bb45f3d6ad78.form = destroybb9db02789b70ee0b379bb45f3d6ad78Form

export const destroy = {
    '/shop/operations/services/{service}': destroye9ab3d744718a35303c175ccfb883b75,
    '/staff/operations/services/{service}': destroybb9db02789b70ee0b379bb45f3d6ad78,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:147
 * @route '/shop/operations/services/{service}/toggle'
 */
const toggleActivea265d049ab0a5a58628af29b925615ee = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActivea265d049ab0a5a58628af29b925615ee.url(args, options),
    method: 'patch',
})

toggleActivea265d049ab0a5a58628af29b925615ee.definition = {
    methods: ["patch"],
    url: '/shop/operations/services/{service}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:147
 * @route '/shop/operations/services/{service}/toggle'
 */
toggleActivea265d049ab0a5a58628af29b925615ee.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { service: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { service: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    service: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        service: typeof args.service === 'object'
                ? args.service.id
                : args.service,
                }

    return toggleActivea265d049ab0a5a58628af29b925615ee.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:147
 * @route '/shop/operations/services/{service}/toggle'
 */
toggleActivea265d049ab0a5a58628af29b925615ee.patch = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActivea265d049ab0a5a58628af29b925615ee.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:147
 * @route '/shop/operations/services/{service}/toggle'
 */
    const toggleActivea265d049ab0a5a58628af29b925615eeForm = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggleActivea265d049ab0a5a58628af29b925615ee.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:147
 * @route '/shop/operations/services/{service}/toggle'
 */
        toggleActivea265d049ab0a5a58628af29b925615eeForm.patch = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggleActivea265d049ab0a5a58628af29b925615ee.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    toggleActivea265d049ab0a5a58628af29b925615ee.form = toggleActivea265d049ab0a5a58628af29b925615eeForm
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:147
 * @route '/staff/operations/services/{service}/toggle'
 */
const toggleActive3ce389933df36d1be9f98e9cc88d9278 = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActive3ce389933df36d1be9f98e9cc88d9278.url(args, options),
    method: 'patch',
})

toggleActive3ce389933df36d1be9f98e9cc88d9278.definition = {
    methods: ["patch"],
    url: '/staff/operations/services/{service}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:147
 * @route '/staff/operations/services/{service}/toggle'
 */
toggleActive3ce389933df36d1be9f98e9cc88d9278.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { service: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { service: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    service: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        service: typeof args.service === 'object'
                ? args.service.id
                : args.service,
                }

    return toggleActive3ce389933df36d1be9f98e9cc88d9278.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:147
 * @route '/staff/operations/services/{service}/toggle'
 */
toggleActive3ce389933df36d1be9f98e9cc88d9278.patch = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActive3ce389933df36d1be9f98e9cc88d9278.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:147
 * @route '/staff/operations/services/{service}/toggle'
 */
    const toggleActive3ce389933df36d1be9f98e9cc88d9278Form = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggleActive3ce389933df36d1be9f98e9cc88d9278.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:147
 * @route '/staff/operations/services/{service}/toggle'
 */
        toggleActive3ce389933df36d1be9f98e9cc88d9278Form.patch = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggleActive3ce389933df36d1be9f98e9cc88d9278.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    toggleActive3ce389933df36d1be9f98e9cc88d9278.form = toggleActive3ce389933df36d1be9f98e9cc88d9278Form

export const toggleActive = {
    '/shop/operations/services/{service}/toggle': toggleActivea265d049ab0a5a58628af29b925615ee,
    '/staff/operations/services/{service}/toggle': toggleActive3ce389933df36d1be9f98e9cc88d9278,
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:158
 * @route '/shop/operations/services/{service}/restore'
 */
const restore702ff9ce9291e339ce7ead51131a5a13 = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore702ff9ce9291e339ce7ead51131a5a13.url(args, options),
    method: 'patch',
})

restore702ff9ce9291e339ce7ead51131a5a13.definition = {
    methods: ["patch"],
    url: '/shop/operations/services/{service}/restore',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:158
 * @route '/shop/operations/services/{service}/restore'
 */
restore702ff9ce9291e339ce7ead51131a5a13.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { service: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { service: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    service: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        service: typeof args.service === 'object'
                ? args.service.id
                : args.service,
                }

    return restore702ff9ce9291e339ce7ead51131a5a13.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:158
 * @route '/shop/operations/services/{service}/restore'
 */
restore702ff9ce9291e339ce7ead51131a5a13.patch = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore702ff9ce9291e339ce7ead51131a5a13.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:158
 * @route '/shop/operations/services/{service}/restore'
 */
    const restore702ff9ce9291e339ce7ead51131a5a13Form = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore702ff9ce9291e339ce7ead51131a5a13.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:158
 * @route '/shop/operations/services/{service}/restore'
 */
        restore702ff9ce9291e339ce7ead51131a5a13Form.patch = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore702ff9ce9291e339ce7ead51131a5a13.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    restore702ff9ce9291e339ce7ead51131a5a13.form = restore702ff9ce9291e339ce7ead51131a5a13Form
    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:158
 * @route '/staff/operations/services/{service}/restore'
 */
const restore223fb1e6f3bda34708848fd57a7eea19 = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore223fb1e6f3bda34708848fd57a7eea19.url(args, options),
    method: 'patch',
})

restore223fb1e6f3bda34708848fd57a7eea19.definition = {
    methods: ["patch"],
    url: '/staff/operations/services/{service}/restore',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:158
 * @route '/staff/operations/services/{service}/restore'
 */
restore223fb1e6f3bda34708848fd57a7eea19.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { service: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { service: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    service: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        service: typeof args.service === 'object'
                ? args.service.id
                : args.service,
                }

    return restore223fb1e6f3bda34708848fd57a7eea19.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:158
 * @route '/staff/operations/services/{service}/restore'
 */
restore223fb1e6f3bda34708848fd57a7eea19.patch = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore223fb1e6f3bda34708848fd57a7eea19.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:158
 * @route '/staff/operations/services/{service}/restore'
 */
    const restore223fb1e6f3bda34708848fd57a7eea19Form = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore223fb1e6f3bda34708848fd57a7eea19.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:158
 * @route '/staff/operations/services/{service}/restore'
 */
        restore223fb1e6f3bda34708848fd57a7eea19Form.patch = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore223fb1e6f3bda34708848fd57a7eea19.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    restore223fb1e6f3bda34708848fd57a7eea19.form = restore223fb1e6f3bda34708848fd57a7eea19Form

export const restore = {
    '/shop/operations/services/{service}/restore': restore702ff9ce9291e339ce7ead51131a5a13,
    '/staff/operations/services/{service}/restore': restore223fb1e6f3bda34708848fd57a7eea19,
}

const ShopServiceController = { index, create, store, exportMethod, importTemplate, importMethod, restoreAll, edit, update, destroy, toggleActive, restore, export: exportMethod, import: importMethod }

export default ShopServiceController