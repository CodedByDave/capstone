import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/shop/operations/promos'
 */
const index9c39362ed83aa81bc159439b65bbd8bf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index9c39362ed83aa81bc159439b65bbd8bf.url(options),
    method: 'get',
})

index9c39362ed83aa81bc159439b65bbd8bf.definition = {
    methods: ["get","head"],
    url: '/shop/operations/promos',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/shop/operations/promos'
 */
index9c39362ed83aa81bc159439b65bbd8bf.url = (options?: RouteQueryOptions) => {
    return index9c39362ed83aa81bc159439b65bbd8bf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/shop/operations/promos'
 */
index9c39362ed83aa81bc159439b65bbd8bf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index9c39362ed83aa81bc159439b65bbd8bf.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/shop/operations/promos'
 */
index9c39362ed83aa81bc159439b65bbd8bf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index9c39362ed83aa81bc159439b65bbd8bf.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/shop/operations/promos'
 */
    const index9c39362ed83aa81bc159439b65bbd8bfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index9c39362ed83aa81bc159439b65bbd8bf.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/shop/operations/promos'
 */
        index9c39362ed83aa81bc159439b65bbd8bfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index9c39362ed83aa81bc159439b65bbd8bf.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/shop/operations/promos'
 */
        index9c39362ed83aa81bc159439b65bbd8bfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index9c39362ed83aa81bc159439b65bbd8bf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index9c39362ed83aa81bc159439b65bbd8bf.form = index9c39362ed83aa81bc159439b65bbd8bfForm
    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/staff/operations/promos'
 */
const index48f57d45c75e865737acacdda605d06d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index48f57d45c75e865737acacdda605d06d.url(options),
    method: 'get',
})

index48f57d45c75e865737acacdda605d06d.definition = {
    methods: ["get","head"],
    url: '/staff/operations/promos',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/staff/operations/promos'
 */
index48f57d45c75e865737acacdda605d06d.url = (options?: RouteQueryOptions) => {
    return index48f57d45c75e865737acacdda605d06d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/staff/operations/promos'
 */
index48f57d45c75e865737acacdda605d06d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index48f57d45c75e865737acacdda605d06d.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/staff/operations/promos'
 */
index48f57d45c75e865737acacdda605d06d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index48f57d45c75e865737acacdda605d06d.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/staff/operations/promos'
 */
    const index48f57d45c75e865737acacdda605d06dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index48f57d45c75e865737acacdda605d06d.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/staff/operations/promos'
 */
        index48f57d45c75e865737acacdda605d06dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index48f57d45c75e865737acacdda605d06d.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::index
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:37
 * @route '/staff/operations/promos'
 */
        index48f57d45c75e865737acacdda605d06dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index48f57d45c75e865737acacdda605d06d.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index48f57d45c75e865737acacdda605d06d.form = index48f57d45c75e865737acacdda605d06dForm

export const index = {
    '/shop/operations/promos': index9c39362ed83aa81bc159439b65bbd8bf,
    '/staff/operations/promos': index48f57d45c75e865737acacdda605d06d,
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/shop/operations/promos/create'
 */
const create16d5f4209b67b807b3b99dc8b019519b = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create16d5f4209b67b807b3b99dc8b019519b.url(options),
    method: 'get',
})

create16d5f4209b67b807b3b99dc8b019519b.definition = {
    methods: ["get","head"],
    url: '/shop/operations/promos/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/shop/operations/promos/create'
 */
create16d5f4209b67b807b3b99dc8b019519b.url = (options?: RouteQueryOptions) => {
    return create16d5f4209b67b807b3b99dc8b019519b.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/shop/operations/promos/create'
 */
create16d5f4209b67b807b3b99dc8b019519b.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create16d5f4209b67b807b3b99dc8b019519b.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/shop/operations/promos/create'
 */
create16d5f4209b67b807b3b99dc8b019519b.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create16d5f4209b67b807b3b99dc8b019519b.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/shop/operations/promos/create'
 */
    const create16d5f4209b67b807b3b99dc8b019519bForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create16d5f4209b67b807b3b99dc8b019519b.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/shop/operations/promos/create'
 */
        create16d5f4209b67b807b3b99dc8b019519bForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create16d5f4209b67b807b3b99dc8b019519b.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/shop/operations/promos/create'
 */
        create16d5f4209b67b807b3b99dc8b019519bForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create16d5f4209b67b807b3b99dc8b019519b.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create16d5f4209b67b807b3b99dc8b019519b.form = create16d5f4209b67b807b3b99dc8b019519bForm
    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/staff/operations/promos/create'
 */
const create0739cab17c98b53123eb94a0e5198536 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create0739cab17c98b53123eb94a0e5198536.url(options),
    method: 'get',
})

create0739cab17c98b53123eb94a0e5198536.definition = {
    methods: ["get","head"],
    url: '/staff/operations/promos/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/staff/operations/promos/create'
 */
create0739cab17c98b53123eb94a0e5198536.url = (options?: RouteQueryOptions) => {
    return create0739cab17c98b53123eb94a0e5198536.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/staff/operations/promos/create'
 */
create0739cab17c98b53123eb94a0e5198536.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create0739cab17c98b53123eb94a0e5198536.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/staff/operations/promos/create'
 */
create0739cab17c98b53123eb94a0e5198536.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create0739cab17c98b53123eb94a0e5198536.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/staff/operations/promos/create'
 */
    const create0739cab17c98b53123eb94a0e5198536Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create0739cab17c98b53123eb94a0e5198536.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/staff/operations/promos/create'
 */
        create0739cab17c98b53123eb94a0e5198536Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create0739cab17c98b53123eb94a0e5198536.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::create
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:51
 * @route '/staff/operations/promos/create'
 */
        create0739cab17c98b53123eb94a0e5198536Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create0739cab17c98b53123eb94a0e5198536.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create0739cab17c98b53123eb94a0e5198536.form = create0739cab17c98b53123eb94a0e5198536Form

export const create = {
    '/shop/operations/promos/create': create16d5f4209b67b807b3b99dc8b019519b,
    '/staff/operations/promos/create': create0739cab17c98b53123eb94a0e5198536,
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/shop/operations/promos'
 */
const store9c39362ed83aa81bc159439b65bbd8bf = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store9c39362ed83aa81bc159439b65bbd8bf.url(options),
    method: 'post',
})

store9c39362ed83aa81bc159439b65bbd8bf.definition = {
    methods: ["post"],
    url: '/shop/operations/promos',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/shop/operations/promos'
 */
store9c39362ed83aa81bc159439b65bbd8bf.url = (options?: RouteQueryOptions) => {
    return store9c39362ed83aa81bc159439b65bbd8bf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/shop/operations/promos'
 */
store9c39362ed83aa81bc159439b65bbd8bf.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store9c39362ed83aa81bc159439b65bbd8bf.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/shop/operations/promos'
 */
    const store9c39362ed83aa81bc159439b65bbd8bfForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store9c39362ed83aa81bc159439b65bbd8bf.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/shop/operations/promos'
 */
        store9c39362ed83aa81bc159439b65bbd8bfForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store9c39362ed83aa81bc159439b65bbd8bf.url(options),
            method: 'post',
        })
    
    store9c39362ed83aa81bc159439b65bbd8bf.form = store9c39362ed83aa81bc159439b65bbd8bfForm
    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/staff/operations/promos'
 */
const store48f57d45c75e865737acacdda605d06d = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store48f57d45c75e865737acacdda605d06d.url(options),
    method: 'post',
})

store48f57d45c75e865737acacdda605d06d.definition = {
    methods: ["post"],
    url: '/staff/operations/promos',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/staff/operations/promos'
 */
store48f57d45c75e865737acacdda605d06d.url = (options?: RouteQueryOptions) => {
    return store48f57d45c75e865737acacdda605d06d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/staff/operations/promos'
 */
store48f57d45c75e865737acacdda605d06d.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store48f57d45c75e865737acacdda605d06d.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/staff/operations/promos'
 */
    const store48f57d45c75e865737acacdda605d06dForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store48f57d45c75e865737acacdda605d06d.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::store
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:56
 * @route '/staff/operations/promos'
 */
        store48f57d45c75e865737acacdda605d06dForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store48f57d45c75e865737acacdda605d06d.url(options),
            method: 'post',
        })
    
    store48f57d45c75e865737acacdda605d06d.form = store48f57d45c75e865737acacdda605d06dForm

export const store = {
    '/shop/operations/promos': store9c39362ed83aa81bc159439b65bbd8bf,
    '/staff/operations/promos': store48f57d45c75e865737acacdda605d06d,
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/shop/operations/promos/{promotion}/edit'
 */
const edit95f895296bb40647272d019b543a0368 = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit95f895296bb40647272d019b543a0368.url(args, options),
    method: 'get',
})

edit95f895296bb40647272d019b543a0368.definition = {
    methods: ["get","head"],
    url: '/shop/operations/promos/{promotion}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/shop/operations/promos/{promotion}/edit'
 */
edit95f895296bb40647272d019b543a0368.url = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return edit95f895296bb40647272d019b543a0368.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/shop/operations/promos/{promotion}/edit'
 */
edit95f895296bb40647272d019b543a0368.get = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit95f895296bb40647272d019b543a0368.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/shop/operations/promos/{promotion}/edit'
 */
edit95f895296bb40647272d019b543a0368.head = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit95f895296bb40647272d019b543a0368.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/shop/operations/promos/{promotion}/edit'
 */
    const edit95f895296bb40647272d019b543a0368Form = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit95f895296bb40647272d019b543a0368.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/shop/operations/promos/{promotion}/edit'
 */
        edit95f895296bb40647272d019b543a0368Form.get = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit95f895296bb40647272d019b543a0368.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/shop/operations/promos/{promotion}/edit'
 */
        edit95f895296bb40647272d019b543a0368Form.head = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit95f895296bb40647272d019b543a0368.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit95f895296bb40647272d019b543a0368.form = edit95f895296bb40647272d019b543a0368Form
    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/staff/operations/promos/{promotion}/edit'
 */
const edit13aa6292ef14e6d94015ee222b5b9671 = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit13aa6292ef14e6d94015ee222b5b9671.url(args, options),
    method: 'get',
})

edit13aa6292ef14e6d94015ee222b5b9671.definition = {
    methods: ["get","head"],
    url: '/staff/operations/promos/{promotion}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/staff/operations/promos/{promotion}/edit'
 */
edit13aa6292ef14e6d94015ee222b5b9671.url = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return edit13aa6292ef14e6d94015ee222b5b9671.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/staff/operations/promos/{promotion}/edit'
 */
edit13aa6292ef14e6d94015ee222b5b9671.get = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit13aa6292ef14e6d94015ee222b5b9671.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/staff/operations/promos/{promotion}/edit'
 */
edit13aa6292ef14e6d94015ee222b5b9671.head = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit13aa6292ef14e6d94015ee222b5b9671.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/staff/operations/promos/{promotion}/edit'
 */
    const edit13aa6292ef14e6d94015ee222b5b9671Form = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit13aa6292ef14e6d94015ee222b5b9671.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/staff/operations/promos/{promotion}/edit'
 */
        edit13aa6292ef14e6d94015ee222b5b9671Form.get = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit13aa6292ef14e6d94015ee222b5b9671.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::edit
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:77
 * @route '/staff/operations/promos/{promotion}/edit'
 */
        edit13aa6292ef14e6d94015ee222b5b9671Form.head = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit13aa6292ef14e6d94015ee222b5b9671.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit13aa6292ef14e6d94015ee222b5b9671.form = edit13aa6292ef14e6d94015ee222b5b9671Form

export const edit = {
    '/shop/operations/promos/{promotion}/edit': edit95f895296bb40647272d019b543a0368,
    '/staff/operations/promos/{promotion}/edit': edit13aa6292ef14e6d94015ee222b5b9671,
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::update
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:87
 * @route '/shop/operations/promos/{promotion}'
 */
const update24cb406459369ee1dbf9e397062e5e73 = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update24cb406459369ee1dbf9e397062e5e73.url(args, options),
    method: 'put',
})

update24cb406459369ee1dbf9e397062e5e73.definition = {
    methods: ["put"],
    url: '/shop/operations/promos/{promotion}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::update
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:87
 * @route '/shop/operations/promos/{promotion}'
 */
update24cb406459369ee1dbf9e397062e5e73.url = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return update24cb406459369ee1dbf9e397062e5e73.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::update
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:87
 * @route '/shop/operations/promos/{promotion}'
 */
update24cb406459369ee1dbf9e397062e5e73.put = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update24cb406459369ee1dbf9e397062e5e73.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::update
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:87
 * @route '/shop/operations/promos/{promotion}'
 */
    const update24cb406459369ee1dbf9e397062e5e73Form = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update24cb406459369ee1dbf9e397062e5e73.url(args, {
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
        update24cb406459369ee1dbf9e397062e5e73Form.put = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update24cb406459369ee1dbf9e397062e5e73.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update24cb406459369ee1dbf9e397062e5e73.form = update24cb406459369ee1dbf9e397062e5e73Form
    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::update
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:87
 * @route '/staff/operations/promos/{promotion}'
 */
const update0fb4570b2c298095301e289765df7319 = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update0fb4570b2c298095301e289765df7319.url(args, options),
    method: 'put',
})

update0fb4570b2c298095301e289765df7319.definition = {
    methods: ["put"],
    url: '/staff/operations/promos/{promotion}',
} satisfies RouteDefinition<["put"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::update
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:87
 * @route '/staff/operations/promos/{promotion}'
 */
update0fb4570b2c298095301e289765df7319.url = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return update0fb4570b2c298095301e289765df7319.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::update
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:87
 * @route '/staff/operations/promos/{promotion}'
 */
update0fb4570b2c298095301e289765df7319.put = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update0fb4570b2c298095301e289765df7319.url(args, options),
    method: 'put',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::update
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:87
 * @route '/staff/operations/promos/{promotion}'
 */
    const update0fb4570b2c298095301e289765df7319Form = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update0fb4570b2c298095301e289765df7319.url(args, {
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
 * @route '/staff/operations/promos/{promotion}'
 */
        update0fb4570b2c298095301e289765df7319Form.put = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update0fb4570b2c298095301e289765df7319.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update0fb4570b2c298095301e289765df7319.form = update0fb4570b2c298095301e289765df7319Form

export const update = {
    '/shop/operations/promos/{promotion}': update24cb406459369ee1dbf9e397062e5e73,
    '/staff/operations/promos/{promotion}': update0fb4570b2c298095301e289765df7319,
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/shop/operations/promos/{promotion}/toggle'
 */
const toggleActive5730ad0ee9897bc69e40d4361ed01bc9 = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActive5730ad0ee9897bc69e40d4361ed01bc9.url(args, options),
    method: 'patch',
})

toggleActive5730ad0ee9897bc69e40d4361ed01bc9.definition = {
    methods: ["patch"],
    url: '/shop/operations/promos/{promotion}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/shop/operations/promos/{promotion}/toggle'
 */
toggleActive5730ad0ee9897bc69e40d4361ed01bc9.url = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return toggleActive5730ad0ee9897bc69e40d4361ed01bc9.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/shop/operations/promos/{promotion}/toggle'
 */
toggleActive5730ad0ee9897bc69e40d4361ed01bc9.patch = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActive5730ad0ee9897bc69e40d4361ed01bc9.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/shop/operations/promos/{promotion}/toggle'
 */
    const toggleActive5730ad0ee9897bc69e40d4361ed01bc9Form = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggleActive5730ad0ee9897bc69e40d4361ed01bc9.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/shop/operations/promos/{promotion}/toggle'
 */
        toggleActive5730ad0ee9897bc69e40d4361ed01bc9Form.patch = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggleActive5730ad0ee9897bc69e40d4361ed01bc9.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    toggleActive5730ad0ee9897bc69e40d4361ed01bc9.form = toggleActive5730ad0ee9897bc69e40d4361ed01bc9Form
    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/staff/operations/promos/{promotion}/toggle'
 */
const toggleActive8ed1eac8b7193796f68ee24a241e76e0 = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActive8ed1eac8b7193796f68ee24a241e76e0.url(args, options),
    method: 'patch',
})

toggleActive8ed1eac8b7193796f68ee24a241e76e0.definition = {
    methods: ["patch"],
    url: '/staff/operations/promos/{promotion}/toggle',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/staff/operations/promos/{promotion}/toggle'
 */
toggleActive8ed1eac8b7193796f68ee24a241e76e0.url = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return toggleActive8ed1eac8b7193796f68ee24a241e76e0.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/staff/operations/promos/{promotion}/toggle'
 */
toggleActive8ed1eac8b7193796f68ee24a241e76e0.patch = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleActive8ed1eac8b7193796f68ee24a241e76e0.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/staff/operations/promos/{promotion}/toggle'
 */
    const toggleActive8ed1eac8b7193796f68ee24a241e76e0Form = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: toggleActive8ed1eac8b7193796f68ee24a241e76e0.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::toggleActive
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:109
 * @route '/staff/operations/promos/{promotion}/toggle'
 */
        toggleActive8ed1eac8b7193796f68ee24a241e76e0Form.patch = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: toggleActive8ed1eac8b7193796f68ee24a241e76e0.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    toggleActive8ed1eac8b7193796f68ee24a241e76e0.form = toggleActive8ed1eac8b7193796f68ee24a241e76e0Form

export const toggleActive = {
    '/shop/operations/promos/{promotion}/toggle': toggleActive5730ad0ee9897bc69e40d4361ed01bc9,
    '/staff/operations/promos/{promotion}/toggle': toggleActive8ed1eac8b7193796f68ee24a241e76e0,
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::destroy
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:120
 * @route '/shop/operations/promos/{promotion}'
 */
const destroy24cb406459369ee1dbf9e397062e5e73 = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy24cb406459369ee1dbf9e397062e5e73.url(args, options),
    method: 'delete',
})

destroy24cb406459369ee1dbf9e397062e5e73.definition = {
    methods: ["delete"],
    url: '/shop/operations/promos/{promotion}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::destroy
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:120
 * @route '/shop/operations/promos/{promotion}'
 */
destroy24cb406459369ee1dbf9e397062e5e73.url = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return destroy24cb406459369ee1dbf9e397062e5e73.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::destroy
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:120
 * @route '/shop/operations/promos/{promotion}'
 */
destroy24cb406459369ee1dbf9e397062e5e73.delete = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy24cb406459369ee1dbf9e397062e5e73.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::destroy
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:120
 * @route '/shop/operations/promos/{promotion}'
 */
    const destroy24cb406459369ee1dbf9e397062e5e73Form = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy24cb406459369ee1dbf9e397062e5e73.url(args, {
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
        destroy24cb406459369ee1dbf9e397062e5e73Form.delete = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy24cb406459369ee1dbf9e397062e5e73.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy24cb406459369ee1dbf9e397062e5e73.form = destroy24cb406459369ee1dbf9e397062e5e73Form
    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::destroy
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:120
 * @route '/staff/operations/promos/{promotion}'
 */
const destroy0fb4570b2c298095301e289765df7319 = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy0fb4570b2c298095301e289765df7319.url(args, options),
    method: 'delete',
})

destroy0fb4570b2c298095301e289765df7319.definition = {
    methods: ["delete"],
    url: '/staff/operations/promos/{promotion}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::destroy
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:120
 * @route '/staff/operations/promos/{promotion}'
 */
destroy0fb4570b2c298095301e289765df7319.url = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions) => {
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

    return destroy0fb4570b2c298095301e289765df7319.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::destroy
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:120
 * @route '/staff/operations/promos/{promotion}'
 */
destroy0fb4570b2c298095301e289765df7319.delete = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy0fb4570b2c298095301e289765df7319.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::destroy
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:120
 * @route '/staff/operations/promos/{promotion}'
 */
    const destroy0fb4570b2c298095301e289765df7319Form = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy0fb4570b2c298095301e289765df7319.url(args, {
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
 * @route '/staff/operations/promos/{promotion}'
 */
        destroy0fb4570b2c298095301e289765df7319Form.delete = (args: { promotion: string | number | { id: string | number } } | [promotion: string | number | { id: string | number } ] | string | number | { id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy0fb4570b2c298095301e289765df7319.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy0fb4570b2c298095301e289765df7319.form = destroy0fb4570b2c298095301e289765df7319Form

export const destroy = {
    '/shop/operations/promos/{promotion}': destroy24cb406459369ee1dbf9e397062e5e73,
    '/staff/operations/promos/{promotion}': destroy0fb4570b2c298095301e289765df7319,
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::restore
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:130
 * @route '/shop/operations/promos/{promotion}/restore'
 */
const restorec75498aaf04384600601c35a2956d101 = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restorec75498aaf04384600601c35a2956d101.url(args, options),
    method: 'patch',
})

restorec75498aaf04384600601c35a2956d101.definition = {
    methods: ["patch"],
    url: '/shop/operations/promos/{promotion}/restore',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::restore
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:130
 * @route '/shop/operations/promos/{promotion}/restore'
 */
restorec75498aaf04384600601c35a2956d101.url = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return restorec75498aaf04384600601c35a2956d101.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::restore
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:130
 * @route '/shop/operations/promos/{promotion}/restore'
 */
restorec75498aaf04384600601c35a2956d101.patch = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restorec75498aaf04384600601c35a2956d101.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::restore
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:130
 * @route '/shop/operations/promos/{promotion}/restore'
 */
    const restorec75498aaf04384600601c35a2956d101Form = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restorec75498aaf04384600601c35a2956d101.url(args, {
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
        restorec75498aaf04384600601c35a2956d101Form.patch = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restorec75498aaf04384600601c35a2956d101.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    restorec75498aaf04384600601c35a2956d101.form = restorec75498aaf04384600601c35a2956d101Form
    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::restore
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:130
 * @route '/staff/operations/promos/{promotion}/restore'
 */
const restore902d6a8afacdfc605dee41d55dca0cd8 = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore902d6a8afacdfc605dee41d55dca0cd8.url(args, options),
    method: 'patch',
})

restore902d6a8afacdfc605dee41d55dca0cd8.definition = {
    methods: ["patch"],
    url: '/staff/operations/promos/{promotion}/restore',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::restore
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:130
 * @route '/staff/operations/promos/{promotion}/restore'
 */
restore902d6a8afacdfc605dee41d55dca0cd8.url = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return restore902d6a8afacdfc605dee41d55dca0cd8.definition.url
            .replace('{promotion}', parsedArgs.promotion.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::restore
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:130
 * @route '/staff/operations/promos/{promotion}/restore'
 */
restore902d6a8afacdfc605dee41d55dca0cd8.patch = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: restore902d6a8afacdfc605dee41d55dca0cd8.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Shop\Operations\PromotionController::restore
 * @see app/Http/Controllers/Shop/Operations/PromotionController.php:130
 * @route '/staff/operations/promos/{promotion}/restore'
 */
    const restore902d6a8afacdfc605dee41d55dca0cd8Form = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: restore902d6a8afacdfc605dee41d55dca0cd8.url(args, {
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
 * @route '/staff/operations/promos/{promotion}/restore'
 */
        restore902d6a8afacdfc605dee41d55dca0cd8Form.patch = (args: { promotion: string | number } | [promotion: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: restore902d6a8afacdfc605dee41d55dca0cd8.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    restore902d6a8afacdfc605dee41d55dca0cd8.form = restore902d6a8afacdfc605dee41d55dca0cd8Form

export const restore = {
    '/shop/operations/promos/{promotion}/restore': restorec75498aaf04384600601c35a2956d101,
    '/staff/operations/promos/{promotion}/restore': restore902d6a8afacdfc605dee41d55dca0cd8,
}

const PromotionController = { index, create, store, edit, update, toggleActive, destroy, restore }

export default PromotionController