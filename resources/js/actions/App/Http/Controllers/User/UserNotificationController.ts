import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\User\UserNotificationController::index
 * @see app/Http/Controllers/User/UserNotificationController.php:13
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
 * @see app/Http/Controllers/User/UserNotificationController.php:13
 * @route '/user/notifications'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserNotificationController::index
 * @see app/Http/Controllers/User/UserNotificationController.php:13
 * @route '/user/notifications'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserNotificationController::index
 * @see app/Http/Controllers/User/UserNotificationController.php:13
 * @route '/user/notifications'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserNotificationController::index
 * @see app/Http/Controllers/User/UserNotificationController.php:13
 * @route '/user/notifications'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserNotificationController::index
 * @see app/Http/Controllers/User/UserNotificationController.php:13
 * @route '/user/notifications'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserNotificationController::index
 * @see app/Http/Controllers/User/UserNotificationController.php:13
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
 * @see app/Http/Controllers/User/UserNotificationController.php:36
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
 * @see app/Http/Controllers/User/UserNotificationController.php:36
 * @route '/user/notifications/poll'
 */
poll.url = (options?: RouteQueryOptions) => {
    return poll.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserNotificationController::poll
 * @see app/Http/Controllers/User/UserNotificationController.php:36
 * @route '/user/notifications/poll'
 */
poll.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: poll.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserNotificationController::poll
 * @see app/Http/Controllers/User/UserNotificationController.php:36
 * @route '/user/notifications/poll'
 */
poll.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: poll.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserNotificationController::poll
 * @see app/Http/Controllers/User/UserNotificationController.php:36
 * @route '/user/notifications/poll'
 */
    const pollForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: poll.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserNotificationController::poll
 * @see app/Http/Controllers/User/UserNotificationController.php:36
 * @route '/user/notifications/poll'
 */
        pollForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: poll.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserNotificationController::poll
 * @see app/Http/Controllers/User/UserNotificationController.php:36
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
 * @see app/Http/Controllers/User/UserNotificationController.php:66
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
 * @see app/Http/Controllers/User/UserNotificationController.php:66
 * @route '/user/notifications/read-all'
 */
markAllRead.url = (options?: RouteQueryOptions) => {
    return markAllRead.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserNotificationController::markAllRead
 * @see app/Http/Controllers/User/UserNotificationController.php:66
 * @route '/user/notifications/read-all'
 */
markAllRead.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markAllRead.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\User\UserNotificationController::markAllRead
 * @see app/Http/Controllers/User/UserNotificationController.php:66
 * @route '/user/notifications/read-all'
 */
    const markAllReadForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: markAllRead.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\User\UserNotificationController::markAllRead
 * @see app/Http/Controllers/User/UserNotificationController.php:66
 * @route '/user/notifications/read-all'
 */
        markAllReadForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: markAllRead.url(options),
            method: 'post',
        })
    
    markAllRead.form = markAllReadForm
const UserNotificationController = { index, poll, markAllRead }

export default UserNotificationController