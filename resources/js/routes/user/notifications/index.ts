import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
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
* @see \App\Http\Controllers\User\UserNotificationController::readAll
 * @see app/Http/Controllers/User/UserNotificationController.php:66
 * @route '/user/notifications/read-all'
 */
export const readAll = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: readAll.url(options),
    method: 'post',
})

readAll.definition = {
    methods: ["post"],
    url: '/user/notifications/read-all',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\User\UserNotificationController::readAll
 * @see app/Http/Controllers/User/UserNotificationController.php:66
 * @route '/user/notifications/read-all'
 */
readAll.url = (options?: RouteQueryOptions) => {
    return readAll.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserNotificationController::readAll
 * @see app/Http/Controllers/User/UserNotificationController.php:66
 * @route '/user/notifications/read-all'
 */
readAll.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: readAll.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\User\UserNotificationController::readAll
 * @see app/Http/Controllers/User/UserNotificationController.php:66
 * @route '/user/notifications/read-all'
 */
    const readAllForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: readAll.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\User\UserNotificationController::readAll
 * @see app/Http/Controllers/User/UserNotificationController.php:66
 * @route '/user/notifications/read-all'
 */
        readAllForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: readAll.url(options),
            method: 'post',
        })
    
    readAll.form = readAllForm
const notifications = {
    poll: Object.assign(poll, poll),
readAll: Object.assign(readAll, readAll),
}

export default notifications