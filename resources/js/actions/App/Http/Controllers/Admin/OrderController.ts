import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:24
 * @route '/admin/orders'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/orders',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:24
 * @route '/admin/orders'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:24
 * @route '/admin/orders'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:24
 * @route '/admin/orders'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:24
 * @route '/admin/orders'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:24
 * @route '/admin/orders'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\OrderController::index
 * @see app/Http/Controllers/Admin/OrderController.php:24
 * @route '/admin/orders'
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
* @see \App\Http\Controllers\Admin\OrderController::exportCsv
 * @see app/Http/Controllers/Admin/OrderController.php:35
 * @route '/admin/orders/export'
 */
export const exportCsv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportCsv.url(options),
    method: 'get',
})

exportCsv.definition = {
    methods: ["get","head"],
    url: '/admin/orders/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::exportCsv
 * @see app/Http/Controllers/Admin/OrderController.php:35
 * @route '/admin/orders/export'
 */
exportCsv.url = (options?: RouteQueryOptions) => {
    return exportCsv.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::exportCsv
 * @see app/Http/Controllers/Admin/OrderController.php:35
 * @route '/admin/orders/export'
 */
exportCsv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportCsv.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\OrderController::exportCsv
 * @see app/Http/Controllers/Admin/OrderController.php:35
 * @route '/admin/orders/export'
 */
exportCsv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportCsv.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::exportCsv
 * @see app/Http/Controllers/Admin/OrderController.php:35
 * @route '/admin/orders/export'
 */
    const exportCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportCsv.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::exportCsv
 * @see app/Http/Controllers/Admin/OrderController.php:35
 * @route '/admin/orders/export'
 */
        exportCsvForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportCsv.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\OrderController::exportCsv
 * @see app/Http/Controllers/Admin/OrderController.php:35
 * @route '/admin/orders/export'
 */
        exportCsvForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportCsv.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportCsv.form = exportCsvForm
/**
* @see \App\Http\Controllers\Admin\OrderController::importCsv
 * @see app/Http/Controllers/Admin/OrderController.php:81
 * @route '/admin/orders/import'
 */
export const importCsv = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importCsv.url(options),
    method: 'post',
})

importCsv.definition = {
    methods: ["post"],
    url: '/admin/orders/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::importCsv
 * @see app/Http/Controllers/Admin/OrderController.php:81
 * @route '/admin/orders/import'
 */
importCsv.url = (options?: RouteQueryOptions) => {
    return importCsv.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::importCsv
 * @see app/Http/Controllers/Admin/OrderController.php:81
 * @route '/admin/orders/import'
 */
importCsv.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importCsv.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::importCsv
 * @see app/Http/Controllers/Admin/OrderController.php:81
 * @route '/admin/orders/import'
 */
    const importCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: importCsv.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::importCsv
 * @see app/Http/Controllers/Admin/OrderController.php:81
 * @route '/admin/orders/import'
 */
        importCsvForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: importCsv.url(options),
            method: 'post',
        })
    
    importCsv.form = importCsvForm
/**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:95
 * @route '/admin/orders/{order}'
 */
export const show = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/orders/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:95
 * @route '/admin/orders/{order}'
 */
show.url = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { order: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.public_id
                : args.order,
                }

    return show.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:95
 * @route '/admin/orders/{order}'
 */
show.get = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:95
 * @route '/admin/orders/{order}'
 */
show.head = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:95
 * @route '/admin/orders/{order}'
 */
    const showForm = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:95
 * @route '/admin/orders/{order}'
 */
        showForm.get = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\OrderController::show
 * @see app/Http/Controllers/Admin/OrderController.php:95
 * @route '/admin/orders/{order}'
 */
        showForm.head = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Admin\OrderController::approve
 * @see app/Http/Controllers/Admin/OrderController.php:107
 * @route '/admin/orders/{order}/approve'
 */
export const approve = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

approve.definition = {
    methods: ["post"],
    url: '/admin/orders/{order}/approve',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::approve
 * @see app/Http/Controllers/Admin/OrderController.php:107
 * @route '/admin/orders/{order}/approve'
 */
approve.url = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { order: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.public_id
                : args.order,
                }

    return approve.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::approve
 * @see app/Http/Controllers/Admin/OrderController.php:107
 * @route '/admin/orders/{order}/approve'
 */
approve.post = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: approve.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::approve
 * @see app/Http/Controllers/Admin/OrderController.php:107
 * @route '/admin/orders/{order}/approve'
 */
    const approveForm = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: approve.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::approve
 * @see app/Http/Controllers/Admin/OrderController.php:107
 * @route '/admin/orders/{order}/approve'
 */
        approveForm.post = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: approve.url(args, options),
            method: 'post',
        })
    
    approve.form = approveForm
/**
* @see \App\Http\Controllers\Admin\OrderController::reject
 * @see app/Http/Controllers/Admin/OrderController.php:124
 * @route '/admin/orders/{order}/reject'
 */
export const reject = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

reject.definition = {
    methods: ["post"],
    url: '/admin/orders/{order}/reject',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::reject
 * @see app/Http/Controllers/Admin/OrderController.php:124
 * @route '/admin/orders/{order}/reject'
 */
reject.url = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { order: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.public_id
                : args.order,
                }

    return reject.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::reject
 * @see app/Http/Controllers/Admin/OrderController.php:124
 * @route '/admin/orders/{order}/reject'
 */
reject.post = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reject.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::reject
 * @see app/Http/Controllers/Admin/OrderController.php:124
 * @route '/admin/orders/{order}/reject'
 */
    const rejectForm = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: reject.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::reject
 * @see app/Http/Controllers/Admin/OrderController.php:124
 * @route '/admin/orders/{order}/reject'
 */
        rejectForm.post = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: reject.url(args, options),
            method: 'post',
        })
    
    reject.form = rejectForm
/**
* @see \App\Http\Controllers\Admin\OrderController::serveKyc
 * @see app/Http/Controllers/Admin/OrderController.php:150
 * @route '/admin/kyc-file'
 */
export const serveKyc = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: serveKyc.url(options),
    method: 'get',
})

serveKyc.definition = {
    methods: ["get","head"],
    url: '/admin/kyc-file',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::serveKyc
 * @see app/Http/Controllers/Admin/OrderController.php:150
 * @route '/admin/kyc-file'
 */
serveKyc.url = (options?: RouteQueryOptions) => {
    return serveKyc.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::serveKyc
 * @see app/Http/Controllers/Admin/OrderController.php:150
 * @route '/admin/kyc-file'
 */
serveKyc.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: serveKyc.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\OrderController::serveKyc
 * @see app/Http/Controllers/Admin/OrderController.php:150
 * @route '/admin/kyc-file'
 */
serveKyc.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: serveKyc.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::serveKyc
 * @see app/Http/Controllers/Admin/OrderController.php:150
 * @route '/admin/kyc-file'
 */
    const serveKycForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: serveKyc.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::serveKyc
 * @see app/Http/Controllers/Admin/OrderController.php:150
 * @route '/admin/kyc-file'
 */
        serveKycForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: serveKyc.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\OrderController::serveKyc
 * @see app/Http/Controllers/Admin/OrderController.php:150
 * @route '/admin/kyc-file'
 */
        serveKycForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: serveKyc.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    serveKyc.form = serveKycForm
const OrderController = { index, exportCsv, importCsv, show, approve, reject, serveKyc }

export default OrderController