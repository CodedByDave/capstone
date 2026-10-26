import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import shops266a13 from './shops'
import orders from './orders'
import complaints from './complaints'
import notifications1ce82a from './notifications'
/**
* @see \App\Http\Controllers\User\UserDashboardController::dashboard
 * @see app/Http/Controllers/User/UserDashboardController.php:25
 * @route '/user/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/user/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserDashboardController::dashboard
 * @see app/Http/Controllers/User/UserDashboardController.php:25
 * @route '/user/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserDashboardController::dashboard
 * @see app/Http/Controllers/User/UserDashboardController.php:25
 * @route '/user/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserDashboardController::dashboard
 * @see app/Http/Controllers/User/UserDashboardController.php:25
 * @route '/user/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserDashboardController::dashboard
 * @see app/Http/Controllers/User/UserDashboardController.php:25
 * @route '/user/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserDashboardController::dashboard
 * @see app/Http/Controllers/User/UserDashboardController.php:25
 * @route '/user/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserDashboardController::dashboard
 * @see app/Http/Controllers/User/UserDashboardController.php:25
 * @route '/user/dashboard'
 */
        dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dashboard.form = dashboardForm
/**
* @see \App\Http\Controllers\User\UserDashboardController::shops
 * @see app/Http/Controllers/User/UserDashboardController.php:97
 * @route '/user/shops'
 */
export const shops = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: shops.url(options),
    method: 'get',
})

shops.definition = {
    methods: ["get","head"],
    url: '/user/shops',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserDashboardController::shops
 * @see app/Http/Controllers/User/UserDashboardController.php:97
 * @route '/user/shops'
 */
shops.url = (options?: RouteQueryOptions) => {
    return shops.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserDashboardController::shops
 * @see app/Http/Controllers/User/UserDashboardController.php:97
 * @route '/user/shops'
 */
shops.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: shops.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserDashboardController::shops
 * @see app/Http/Controllers/User/UserDashboardController.php:97
 * @route '/user/shops'
 */
shops.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: shops.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserDashboardController::shops
 * @see app/Http/Controllers/User/UserDashboardController.php:97
 * @route '/user/shops'
 */
    const shopsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: shops.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserDashboardController::shops
 * @see app/Http/Controllers/User/UserDashboardController.php:97
 * @route '/user/shops'
 */
        shopsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: shops.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserDashboardController::shops
 * @see app/Http/Controllers/User/UserDashboardController.php:97
 * @route '/user/shops'
 */
        shopsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: shops.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    shops.form = shopsForm
/**
* @see \App\Http\Controllers\User\UserNotificationController::notifications
 * @see app/Http/Controllers/User/UserNotificationController.php:13
 * @route '/user/notifications'
 */
export const notifications = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: notifications.url(options),
    method: 'get',
})

notifications.definition = {
    methods: ["get","head"],
    url: '/user/notifications',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\User\UserNotificationController::notifications
 * @see app/Http/Controllers/User/UserNotificationController.php:13
 * @route '/user/notifications'
 */
notifications.url = (options?: RouteQueryOptions) => {
    return notifications.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\User\UserNotificationController::notifications
 * @see app/Http/Controllers/User/UserNotificationController.php:13
 * @route '/user/notifications'
 */
notifications.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: notifications.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\User\UserNotificationController::notifications
 * @see app/Http/Controllers/User/UserNotificationController.php:13
 * @route '/user/notifications'
 */
notifications.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: notifications.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\User\UserNotificationController::notifications
 * @see app/Http/Controllers/User/UserNotificationController.php:13
 * @route '/user/notifications'
 */
    const notificationsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: notifications.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\User\UserNotificationController::notifications
 * @see app/Http/Controllers/User/UserNotificationController.php:13
 * @route '/user/notifications'
 */
        notificationsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: notifications.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\User\UserNotificationController::notifications
 * @see app/Http/Controllers/User/UserNotificationController.php:13
 * @route '/user/notifications'
 */
        notificationsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: notifications.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    notifications.form = notificationsForm
/**
 * @see routes/web.php:725
 * @route '/user/profile'
 */
export const profile = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: profile.url(options),
    method: 'get',
})

profile.definition = {
    methods: ["get","head"],
    url: '/user/profile',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:725
 * @route '/user/profile'
 */
profile.url = (options?: RouteQueryOptions) => {
    return profile.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:725
 * @route '/user/profile'
 */
profile.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: profile.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:725
 * @route '/user/profile'
 */
profile.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: profile.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:725
 * @route '/user/profile'
 */
    const profileForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: profile.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:725
 * @route '/user/profile'
 */
        profileForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: profile.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:725
 * @route '/user/profile'
 */
        profileForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: profile.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    profile.form = profileForm
const user = {
    dashboard: Object.assign(dashboard, dashboard),
shops: Object.assign(shops, shops266a13),
orders: Object.assign(orders, orders),
complaints: Object.assign(complaints, complaints),
notifications: Object.assign(notifications, notifications1ce82a),
profile: Object.assign(profile, profile),
}

export default user