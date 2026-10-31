import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:19
 * @route '/shop/notifications'
 */
const indexd38a7121fdfe4709ec16cc281aea4f7d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexd38a7121fdfe4709ec16cc281aea4f7d.url(options),
    method: 'get',
})

indexd38a7121fdfe4709ec16cc281aea4f7d.definition = {
    methods: ["get","head"],
    url: '/shop/notifications',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:19
 * @route '/shop/notifications'
 */
indexd38a7121fdfe4709ec16cc281aea4f7d.url = (options?: RouteQueryOptions) => {
    return indexd38a7121fdfe4709ec16cc281aea4f7d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:19
 * @route '/shop/notifications'
 */
indexd38a7121fdfe4709ec16cc281aea4f7d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexd38a7121fdfe4709ec16cc281aea4f7d.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:19
 * @route '/shop/notifications'
 */
indexd38a7121fdfe4709ec16cc281aea4f7d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexd38a7121fdfe4709ec16cc281aea4f7d.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:19
 * @route '/shop/notifications'
 */
    const indexd38a7121fdfe4709ec16cc281aea4f7dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexd38a7121fdfe4709ec16cc281aea4f7d.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:19
 * @route '/shop/notifications'
 */
        indexd38a7121fdfe4709ec16cc281aea4f7dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexd38a7121fdfe4709ec16cc281aea4f7d.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:19
 * @route '/shop/notifications'
 */
        indexd38a7121fdfe4709ec16cc281aea4f7dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexd38a7121fdfe4709ec16cc281aea4f7d.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexd38a7121fdfe4709ec16cc281aea4f7d.form = indexd38a7121fdfe4709ec16cc281aea4f7dForm
    /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:19
 * @route '/staff/notifications'
 */
const indexe9634c10bf6c5f3d663fc9942c0fca4c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexe9634c10bf6c5f3d663fc9942c0fca4c.url(options),
    method: 'get',
})

indexe9634c10bf6c5f3d663fc9942c0fca4c.definition = {
    methods: ["get","head"],
    url: '/staff/notifications',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:19
 * @route '/staff/notifications'
 */
indexe9634c10bf6c5f3d663fc9942c0fca4c.url = (options?: RouteQueryOptions) => {
    return indexe9634c10bf6c5f3d663fc9942c0fca4c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:19
 * @route '/staff/notifications'
 */
indexe9634c10bf6c5f3d663fc9942c0fca4c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexe9634c10bf6c5f3d663fc9942c0fca4c.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:19
 * @route '/staff/notifications'
 */
indexe9634c10bf6c5f3d663fc9942c0fca4c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexe9634c10bf6c5f3d663fc9942c0fca4c.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:19
 * @route '/staff/notifications'
 */
    const indexe9634c10bf6c5f3d663fc9942c0fca4cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexe9634c10bf6c5f3d663fc9942c0fca4c.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:19
 * @route '/staff/notifications'
 */
        indexe9634c10bf6c5f3d663fc9942c0fca4cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexe9634c10bf6c5f3d663fc9942c0fca4c.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::index
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:19
 * @route '/staff/notifications'
 */
        indexe9634c10bf6c5f3d663fc9942c0fca4cForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexe9634c10bf6c5f3d663fc9942c0fca4c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexe9634c10bf6c5f3d663fc9942c0fca4c.form = indexe9634c10bf6c5f3d663fc9942c0fca4cForm

export const index = {
    '/shop/notifications': indexd38a7121fdfe4709ec16cc281aea4f7d,
    '/staff/notifications': indexe9634c10bf6c5f3d663fc9942c0fca4c,
}

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::poll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:29
 * @route '/shop/notifications/poll'
 */
const pollcede224ab22b8634bd7e5a99018ebebf = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pollcede224ab22b8634bd7e5a99018ebebf.url(options),
    method: 'get',
})

pollcede224ab22b8634bd7e5a99018ebebf.definition = {
    methods: ["get","head"],
    url: '/shop/notifications/poll',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::poll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:29
 * @route '/shop/notifications/poll'
 */
pollcede224ab22b8634bd7e5a99018ebebf.url = (options?: RouteQueryOptions) => {
    return pollcede224ab22b8634bd7e5a99018ebebf.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::poll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:29
 * @route '/shop/notifications/poll'
 */
pollcede224ab22b8634bd7e5a99018ebebf.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pollcede224ab22b8634bd7e5a99018ebebf.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::poll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:29
 * @route '/shop/notifications/poll'
 */
pollcede224ab22b8634bd7e5a99018ebebf.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pollcede224ab22b8634bd7e5a99018ebebf.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::poll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:29
 * @route '/shop/notifications/poll'
 */
    const pollcede224ab22b8634bd7e5a99018ebebfForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: pollcede224ab22b8634bd7e5a99018ebebf.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::poll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:29
 * @route '/shop/notifications/poll'
 */
        pollcede224ab22b8634bd7e5a99018ebebfForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pollcede224ab22b8634bd7e5a99018ebebf.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::poll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:29
 * @route '/shop/notifications/poll'
 */
        pollcede224ab22b8634bd7e5a99018ebebfForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pollcede224ab22b8634bd7e5a99018ebebf.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    pollcede224ab22b8634bd7e5a99018ebebf.form = pollcede224ab22b8634bd7e5a99018ebebfForm
    /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::poll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:29
 * @route '/staff/notifications/poll'
 */
const poll327add20eb2d63265ae153b4b09fe608 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: poll327add20eb2d63265ae153b4b09fe608.url(options),
    method: 'get',
})

poll327add20eb2d63265ae153b4b09fe608.definition = {
    methods: ["get","head"],
    url: '/staff/notifications/poll',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::poll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:29
 * @route '/staff/notifications/poll'
 */
poll327add20eb2d63265ae153b4b09fe608.url = (options?: RouteQueryOptions) => {
    return poll327add20eb2d63265ae153b4b09fe608.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::poll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:29
 * @route '/staff/notifications/poll'
 */
poll327add20eb2d63265ae153b4b09fe608.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: poll327add20eb2d63265ae153b4b09fe608.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::poll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:29
 * @route '/staff/notifications/poll'
 */
poll327add20eb2d63265ae153b4b09fe608.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: poll327add20eb2d63265ae153b4b09fe608.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::poll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:29
 * @route '/staff/notifications/poll'
 */
    const poll327add20eb2d63265ae153b4b09fe608Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: poll327add20eb2d63265ae153b4b09fe608.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::poll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:29
 * @route '/staff/notifications/poll'
 */
        poll327add20eb2d63265ae153b4b09fe608Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: poll327add20eb2d63265ae153b4b09fe608.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::poll
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:29
 * @route '/staff/notifications/poll'
 */
        poll327add20eb2d63265ae153b4b09fe608Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: poll327add20eb2d63265ae153b4b09fe608.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    poll327add20eb2d63265ae153b4b09fe608.form = poll327add20eb2d63265ae153b4b09fe608Form

export const poll = {
    '/shop/notifications/poll': pollcede224ab22b8634bd7e5a99018ebebf,
    '/staff/notifications/poll': poll327add20eb2d63265ae153b4b09fe608,
}

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:37
 * @route '/shop/notifications/read-all'
 */
const markAllRead1cfccfcdb7ca7f35de483688f60b32a6 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllRead1cfccfcdb7ca7f35de483688f60b32a6.url(options),
    method: 'post',
})

markAllRead1cfccfcdb7ca7f35de483688f60b32a6.definition = {
    methods: ["post"],
    url: '/shop/notifications/read-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:37
 * @route '/shop/notifications/read-all'
 */
markAllRead1cfccfcdb7ca7f35de483688f60b32a6.url = (options?: RouteQueryOptions) => {
    return markAllRead1cfccfcdb7ca7f35de483688f60b32a6.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:37
 * @route '/shop/notifications/read-all'
 */
markAllRead1cfccfcdb7ca7f35de483688f60b32a6.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllRead1cfccfcdb7ca7f35de483688f60b32a6.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:37
 * @route '/shop/notifications/read-all'
 */
    const markAllRead1cfccfcdb7ca7f35de483688f60b32a6Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markAllRead1cfccfcdb7ca7f35de483688f60b32a6.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:37
 * @route '/shop/notifications/read-all'
 */
        markAllRead1cfccfcdb7ca7f35de483688f60b32a6Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markAllRead1cfccfcdb7ca7f35de483688f60b32a6.url(options),
            method: 'post',
        })
    
    markAllRead1cfccfcdb7ca7f35de483688f60b32a6.form = markAllRead1cfccfcdb7ca7f35de483688f60b32a6Form
    /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:37
 * @route '/staff/notifications/read-all'
 */
const markAllRead634875c271c96def64f51ed56a6af9a1 = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllRead634875c271c96def64f51ed56a6af9a1.url(options),
    method: 'post',
})

markAllRead634875c271c96def64f51ed56a6af9a1.definition = {
    methods: ["post"],
    url: '/staff/notifications/read-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:37
 * @route '/staff/notifications/read-all'
 */
markAllRead634875c271c96def64f51ed56a6af9a1.url = (options?: RouteQueryOptions) => {
    return markAllRead634875c271c96def64f51ed56a6af9a1.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:37
 * @route '/staff/notifications/read-all'
 */
markAllRead634875c271c96def64f51ed56a6af9a1.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllRead634875c271c96def64f51ed56a6af9a1.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:37
 * @route '/staff/notifications/read-all'
 */
    const markAllRead634875c271c96def64f51ed56a6af9a1Form = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markAllRead634875c271c96def64f51ed56a6af9a1.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\ShopNotificationController::markAllRead
 * @see app/Http/Controllers/Shop/ShopNotificationController.php:37
 * @route '/staff/notifications/read-all'
 */
        markAllRead634875c271c96def64f51ed56a6af9a1Form.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markAllRead634875c271c96def64f51ed56a6af9a1.url(options),
            method: 'post',
        })
    
    markAllRead634875c271c96def64f51ed56a6af9a1.form = markAllRead634875c271c96def64f51ed56a6af9a1Form

export const markAllRead = {
    '/shop/notifications/read-all': markAllRead1cfccfcdb7ca7f35de483688f60b32a6,
    '/staff/notifications/read-all': markAllRead634875c271c96def64f51ed56a6af9a1,
}

const ShopNotificationController = { index, poll, markAllRead }

export default ShopNotificationController