import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:42
 * @route '/shop/operations/services'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/operations/services',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:42
 * @route '/shop/operations/services'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:42
 * @route '/shop/operations/services'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:42
 * @route '/shop/operations/services'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:42
 * @route '/shop/operations/services'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:42
 * @route '/shop/operations/services'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::index
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:42
 * @route '/shop/operations/services'
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
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:78
 * @route '/shop/operations/services/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/shop/operations/services/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:78
 * @route '/shop/operations/services/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:78
 * @route '/shop/operations/services/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:78
 * @route '/shop/operations/services/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:78
 * @route '/shop/operations/services/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:78
 * @route '/shop/operations/services/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::create
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:78
 * @route '/shop/operations/services/create'
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
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:85
 * @route '/shop/operations/services'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/shop/operations/services',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:85
 * @route '/shop/operations/services'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:85
 * @route '/shop/operations/services'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:85
 * @route '/shop/operations/services'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::store
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:85
 * @route '/shop/operations/services'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:54
 * @route '/shop/operations/services/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/shop/operations/services/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:54
 * @route '/shop/operations/services/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:54
 * @route '/shop/operations/services/export'
 */
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:54
 * @route '/shop/operations/services/export'
 */
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:54
 * @route '/shop/operations/services/export'
 */
    const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMethod.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:54
 * @route '/shop/operations/services/export'
 */
        exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::exportMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:54
 * @route '/shop/operations/services/export'
 */
        exportMethodForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportMethod.form = exportMethodForm
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:62
 * @route '/shop/operations/services/import-template'
 */
export const importTemplate = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: importTemplate.url(options),
    method: 'get',
})

importTemplate.definition = {
    methods: ["get","head"],
    url: '/shop/operations/services/import-template',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:62
 * @route '/shop/operations/services/import-template'
 */
importTemplate.url = (options?: RouteQueryOptions) => {
    return importTemplate.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:62
 * @route '/shop/operations/services/import-template'
 */
importTemplate.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: importTemplate.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:62
 * @route '/shop/operations/services/import-template'
 */
importTemplate.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: importTemplate.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:62
 * @route '/shop/operations/services/import-template'
 */
    const importTemplateForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: importTemplate.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:62
 * @route '/shop/operations/services/import-template'
 */
        importTemplateForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: importTemplate.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importTemplate
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:62
 * @route '/shop/operations/services/import-template'
 */
        importTemplateForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: importTemplate.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    importTemplate.form = importTemplateForm
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:67
 * @route '/shop/operations/services/import'
 */
export const importMethod = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

importMethod.definition = {
    methods: ["post"],
    url: '/shop/operations/services/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:67
 * @route '/shop/operations/services/import'
 */
importMethod.url = (options?: RouteQueryOptions) => {
    return importMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:67
 * @route '/shop/operations/services/import'
 */
importMethod.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:67
 * @route '/shop/operations/services/import'
 */
    const importMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: importMethod.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::importMethod
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:67
 * @route '/shop/operations/services/import'
 */
        importMethodForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: importMethod.url(options),
            method: 'post',
        })
    
    importMethod.form = importMethodForm
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:160
 * @route '/shop/operations/services/restore-all'
 */
export const restoreAll = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreAll.url(options),
    method: 'post',
})

restoreAll.definition = {
    methods: ["post"],
    url: '/shop/operations/services/restore-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:160
 * @route '/shop/operations/services/restore-all'
 */
restoreAll.url = (options?: RouteQueryOptions) => {
    return restoreAll.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:160
 * @route '/shop/operations/services/restore-all'
 */
restoreAll.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restoreAll.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:160
 * @route '/shop/operations/services/restore-all'
 */
    const restoreAllForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restoreAll.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restoreAll
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:160
 * @route '/shop/operations/services/restore-all'
 */
        restoreAllForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restoreAll.url(options),
            method: 'post',
        })
    
    restoreAll.form = restoreAllForm
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:99
 * @route '/shop/operations/services/{service}/edit'
 */
export const edit = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/shop/operations/services/{service}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:99
 * @route '/shop/operations/services/{service}/edit'
 */
edit.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return edit.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:99
 * @route '/shop/operations/services/{service}/edit'
 */
edit.get = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:99
 * @route '/shop/operations/services/{service}/edit'
 */
edit.head = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:99
 * @route '/shop/operations/services/{service}/edit'
 */
    const editForm = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:99
 * @route '/shop/operations/services/{service}/edit'
 */
        editForm.get = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::edit
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:99
 * @route '/shop/operations/services/{service}/edit'
 */
        editForm.head = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:109
 * @route '/shop/operations/services/{service}'
 */
export const update = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/shop/operations/services/{service}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:109
 * @route '/shop/operations/services/{service}'
 */
update.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:109
 * @route '/shop/operations/services/{service}'
 */
update.put = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:109
 * @route '/shop/operations/services/{service}'
 */
    const updateForm = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::update
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:109
 * @route '/shop/operations/services/{service}'
 */
        updateForm.put = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:127
 * @route '/shop/operations/services/{service}'
 */
export const destroy = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/shop/operations/services/{service}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:127
 * @route '/shop/operations/services/{service}'
 */
destroy.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:127
 * @route '/shop/operations/services/{service}'
 */
destroy.delete = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:127
 * @route '/shop/operations/services/{service}'
 */
    const destroyForm = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::destroy
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:127
 * @route '/shop/operations/services/{service}'
 */
        destroyForm.delete = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggle
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:138
 * @route '/shop/operations/services/{service}/toggle'
 */
export const toggle = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

toggle.definition = {
    methods: ["patch"],
    url: '/shop/operations/services/{service}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggle
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:138
 * @route '/shop/operations/services/{service}/toggle'
 */
toggle.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return toggle.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggle
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:138
 * @route '/shop/operations/services/{service}/toggle'
 */
toggle.patch = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggle.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggle
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:138
 * @route '/shop/operations/services/{service}/toggle'
 */
    const toggleForm = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggle.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::toggle
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:138
 * @route '/shop/operations/services/{service}/toggle'
 */
        toggleForm.patch = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:149
 * @route '/shop/operations/services/{service}/restore'
 */
export const restore = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore.url(args, options),
    method: 'patch',
})

restore.definition = {
    methods: ["patch"],
    url: '/shop/operations/services/{service}/restore',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:149
 * @route '/shop/operations/services/{service}/restore'
 */
restore.url = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
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

    return restore.definition.url
            .replace('{service}', parsedArgs.service.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:149
 * @route '/shop/operations/services/{service}/restore'
 */
restore.patch = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:149
 * @route '/shop/operations/services/{service}/restore'
 */
    const restoreForm = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\ShopServiceController::restore
 * @see app/Http/Controllers/Shop/Operations/ShopServiceController.php:149
 * @route '/shop/operations/services/{service}/restore'
 */
        restoreForm.patch = (args: { service: number | { id: number } } | [service: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    restore.form = restoreForm
const services = {
    index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
export: Object.assign(exportMethod, exportMethod),
importTemplate: Object.assign(importTemplate, importTemplate),
import: Object.assign(importMethod, importMethod),
restoreAll: Object.assign(restoreAll, restoreAll),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
toggle: Object.assign(toggle, toggle),
restore: Object.assign(restore, restore),
}

export default services