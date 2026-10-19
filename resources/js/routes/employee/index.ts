import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::create
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:90
 * @route '/shop/employee/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/shop/employee/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::create
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:90
 * @route '/shop/employee/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::create
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:90
 * @route '/shop/employee/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::create
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:90
 * @route '/shop/employee/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::create
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:90
 * @route '/shop/employee/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::create
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:90
 * @route '/shop/employee/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::create
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:90
 * @route '/shop/employee/create'
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
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::archive
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:142
 * @route '/shop/employee/archive'
 */
export const archive = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})

archive.definition = {
    methods: ["get","head"],
    url: '/shop/employee/archive',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::archive
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:142
 * @route '/shop/employee/archive'
 */
archive.url = (options?: RouteQueryOptions) => {
    return archive.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::archive
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:142
 * @route '/shop/employee/archive'
 */
archive.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: archive.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::archive
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:142
 * @route '/shop/employee/archive'
 */
archive.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: archive.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::archive
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:142
 * @route '/shop/employee/archive'
 */
    const archiveForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: archive.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::archive
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:142
 * @route '/shop/employee/archive'
 */
        archiveForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: archive.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::archive
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:142
 * @route '/shop/employee/archive'
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
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::importMethod
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:256
 * @route '/shop/employee/import'
 */
export const importMethod = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

importMethod.definition = {
    methods: ["post"],
    url: '/shop/employee/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::importMethod
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:256
 * @route '/shop/employee/import'
 */
importMethod.url = (options?: RouteQueryOptions) => {
    return importMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::importMethod
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:256
 * @route '/shop/employee/import'
 */
importMethod.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::importMethod
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:256
 * @route '/shop/employee/import'
 */
    const importMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: importMethod.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::importMethod
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:256
 * @route '/shop/employee/import'
 */
        importMethodForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: importMethod.url(options),
            method: 'post',
        })
    
    importMethod.form = importMethodForm
/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::bulkRestore
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:206
 * @route '/shop/employee/bulk-restore'
 */
export const bulkRestore = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkRestore.url(options),
    method: 'post',
})

bulkRestore.definition = {
    methods: ["post"],
    url: '/shop/employee/bulk-restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::bulkRestore
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:206
 * @route '/shop/employee/bulk-restore'
 */
bulkRestore.url = (options?: RouteQueryOptions) => {
    return bulkRestore.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::bulkRestore
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:206
 * @route '/shop/employee/bulk-restore'
 */
bulkRestore.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: bulkRestore.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::bulkRestore
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:206
 * @route '/shop/employee/bulk-restore'
 */
    const bulkRestoreForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkRestore.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::bulkRestore
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:206
 * @route '/shop/employee/bulk-restore'
 */
        bulkRestoreForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkRestore.url(options),
            method: 'post',
        })
    
    bulkRestore.form = bulkRestoreForm
/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::index
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:67
 * @route '/shop/employee'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/shop/employee',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::index
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:67
 * @route '/shop/employee'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::index
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:67
 * @route '/shop/employee'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::index
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:67
 * @route '/shop/employee'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::index
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:67
 * @route '/shop/employee'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::index
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:67
 * @route '/shop/employee'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::index
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:67
 * @route '/shop/employee'
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
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::store
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:159
 * @route '/shop/employee'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/shop/employee',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::store
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:159
 * @route '/shop/employee'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::store
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:159
 * @route '/shop/employee'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::store
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:159
 * @route '/shop/employee'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::store
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:159
 * @route '/shop/employee'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::show
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:77
 * @route '/shop/employee/{employee}'
 */
export const show = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/shop/employee/{employee}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::show
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:77
 * @route '/shop/employee/{employee}'
 */
show.url = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { employee: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { employee: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    employee: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        employee: typeof args.employee === 'object'
                ? args.employee.id
                : args.employee,
                }

    return show.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::show
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:77
 * @route '/shop/employee/{employee}'
 */
show.get = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::show
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:77
 * @route '/shop/employee/{employee}'
 */
show.head = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::show
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:77
 * @route '/shop/employee/{employee}'
 */
    const showForm = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::show
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:77
 * @route '/shop/employee/{employee}'
 */
        showForm.get = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::show
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:77
 * @route '/shop/employee/{employee}'
 */
        showForm.head = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::edit
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:103
 * @route '/shop/employee/{employee}/edit'
 */
export const edit = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/shop/employee/{employee}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::edit
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:103
 * @route '/shop/employee/{employee}/edit'
 */
edit.url = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { employee: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { employee: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    employee: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        employee: typeof args.employee === 'object'
                ? args.employee.id
                : args.employee,
                }

    return edit.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::edit
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:103
 * @route '/shop/employee/{employee}/edit'
 */
edit.get = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::edit
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:103
 * @route '/shop/employee/{employee}/edit'
 */
edit.head = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::edit
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:103
 * @route '/shop/employee/{employee}/edit'
 */
    const editForm = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::edit
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:103
 * @route '/shop/employee/{employee}/edit'
 */
        editForm.get = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::edit
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:103
 * @route '/shop/employee/{employee}/edit'
 */
        editForm.head = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::update
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:169
 * @route '/shop/employee/{employee}'
 */
export const update = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put"],
    url: '/shop/employee/{employee}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::update
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:169
 * @route '/shop/employee/{employee}'
 */
update.url = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { employee: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { employee: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    employee: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        employee: typeof args.employee === 'object'
                ? args.employee.id
                : args.employee,
                }

    return update.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::update
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:169
 * @route '/shop/employee/{employee}'
 */
update.put = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::update
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:169
 * @route '/shop/employee/{employee}'
 */
    const updateForm = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::update
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:169
 * @route '/shop/employee/{employee}'
 */
        updateForm.put = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::destroy
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:181
 * @route '/shop/employee/{employee}'
 */
export const destroy = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/shop/employee/{employee}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::destroy
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:181
 * @route '/shop/employee/{employee}'
 */
destroy.url = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { employee: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { employee: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    employee: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        employee: typeof args.employee === 'object'
                ? args.employee.id
                : args.employee,
                }

    return destroy.definition.url
            .replace('{employee}', parsedArgs.employee.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::destroy
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:181
 * @route '/shop/employee/{employee}'
 */
destroy.delete = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::destroy
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:181
 * @route '/shop/employee/{employee}'
 */
    const destroyForm = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::destroy
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:181
 * @route '/shop/employee/{employee}'
 */
        destroyForm.delete = (args: { employee: number | { id: number } } | [employee: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::restore
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:193
 * @route '/shop/employee/{id}/restore'
 */
export const restore = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

restore.definition = {
    methods: ["post"],
    url: '/shop/employee/{id}/restore',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::restore
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:193
 * @route '/shop/employee/{id}/restore'
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
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::restore
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:193
 * @route '/shop/employee/{id}/restore'
 */
restore.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::restore
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:193
 * @route '/shop/employee/{id}/restore'
 */
    const restoreForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\EmployeeController::restore
 * @see app/Http/Controllers/Shop/Employee/EmployeeController.php:193
 * @route '/shop/employee/{id}/restore'
 */
        restoreForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore.url(args, options),
            method: 'post',
        })
    
    restore.form = restoreForm
const employee = {
    create: Object.assign(create, create),
archive: Object.assign(archive, archive),
import: Object.assign(importMethod, importMethod),
bulkRestore: Object.assign(bulkRestore, bulkRestore),
index: Object.assign(index, index),
store: Object.assign(store, store),
show: Object.assign(show, show),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
restore: Object.assign(restore, restore),
}

export default employee