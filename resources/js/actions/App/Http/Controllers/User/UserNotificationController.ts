import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\User\UserNotificationController::index
 * @see app/Http/Controllers/User/UserNotificationController.php:19
 * @route '/user/notifications'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/user/notifications',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserNotificationController::index
 * @see app/Http/Controllers/User/UserNotificationController.php:19
 * @route '/user/notifications'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserNotificationController::index
 * @see app/Http/Controllers/User/UserNotificationController.php:19
 * @route '/user/notifications'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserNotificationController::index
 * @see app/Http/Controllers/User/UserNotificationController.php:19
 * @route '/user/notifications'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserNotificationController::index
 * @see app/Http/Controllers/User/UserNotificationController.php:19
 * @route '/user/notifications'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserNotificationController::index
 * @see app/Http/Controllers/User/UserNotificationController.php:19
 * @route '/user/notifications'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserNotificationController::index
 * @see app/Http/Controllers/User/UserNotificationController.php:19
 * @route '/user/notifications'
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
* @see \App\Http\Controllers\User\UserNotificationController::poll
 * @see app/Http/Controllers/User/UserNotificationController.php:26
 * @route '/user/notifications/poll'
 */
export const poll = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: poll.url(options),
    method: 'get',
})

poll.definition = {
    methods: ["get","head"],
    url: '/user/notifications/poll',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserNotificationController::poll
 * @see app/Http/Controllers/User/UserNotificationController.php:26
 * @route '/user/notifications/poll'
 */
poll.url = (options?: RouteQueryOptions) => {
    return poll.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserNotificationController::poll
 * @see app/Http/Controllers/User/UserNotificationController.php:26
 * @route '/user/notifications/poll'
 */
poll.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: poll.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserNotificationController::poll
 * @see app/Http/Controllers/User/UserNotificationController.php:26
 * @route '/user/notifications/poll'
 */
poll.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: poll.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserNotificationController::poll
 * @see app/Http/Controllers/User/UserNotificationController.php:26
 * @route '/user/notifications/poll'
 */
    const pollForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: poll.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserNotificationController::poll
 * @see app/Http/Controllers/User/UserNotificationController.php:26
 * @route '/user/notifications/poll'
 */
        pollForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: poll.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserNotificationController::poll
 * @see app/Http/Controllers/User/UserNotificationController.php:26
 * @route '/user/notifications/poll'
 */
        pollForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: poll.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    poll.form = pollForm
/**
* @see \App\Http\Controllers\User\UserNotificationController::markAllRead
 * @see app/Http/Controllers/User/UserNotificationController.php:53
 * @route '/user/notifications/read-all'
 */
export const markAllRead = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllRead.url(options),
    method: 'post',
})

markAllRead.definition = {
    methods: ["post"],
    url: '/user/notifications/read-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\User\UserNotificationController::markAllRead
 * @see app/Http/Controllers/User/UserNotificationController.php:53
 * @route '/user/notifications/read-all'
 */
markAllRead.url = (options?: RouteQueryOptions) => {
    return markAllRead.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserNotificationController::markAllRead
 * @see app/Http/Controllers/User/UserNotificationController.php:53
 * @route '/user/notifications/read-all'
 */
markAllRead.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllRead.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\User\UserNotificationController::markAllRead
 * @see app/Http/Controllers/User/UserNotificationController.php:53
 * @route '/user/notifications/read-all'
 */
    const markAllReadForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markAllRead.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\User\UserNotificationController::markAllRead
 * @see app/Http/Controllers/User/UserNotificationController.php:53
 * @route '/user/notifications/read-all'
 */
        markAllReadForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markAllRead.url(options),
            method: 'post',
        })
    
    markAllRead.form = markAllReadForm
/**
* @see \App\Http\Controllers\User\UserNotificationController::markRead
 * @see app/Http/Controllers/User/UserNotificationController.php:46
 * @route '/user/notifications/{id}/read'
 */
export const markRead = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markRead.url(args, options),
    method: 'post',
})

markRead.definition = {
    methods: ["post"],
    url: '/user/notifications/{id}/read',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\User\UserNotificationController::markRead
 * @see app/Http/Controllers/User/UserNotificationController.php:46
 * @route '/user/notifications/{id}/read'
 */
markRead.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return markRead.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserNotificationController::markRead
 * @see app/Http/Controllers/User/UserNotificationController.php:46
 * @route '/user/notifications/{id}/read'
 */
markRead.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markRead.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\User\UserNotificationController::markRead
 * @see app/Http/Controllers/User/UserNotificationController.php:46
 * @route '/user/notifications/{id}/read'
 */
    const markReadForm = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markRead.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\User\UserNotificationController::markRead
 * @see app/Http/Controllers/User/UserNotificationController.php:46
 * @route '/user/notifications/{id}/read'
 */
        markReadForm.post = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markRead.url(args, options),
            method: 'post',
        })
    
    markRead.form = markReadForm
const UserNotificationController = { index, poll, markAllRead, markRead }

export default UserNotificationController