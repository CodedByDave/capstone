import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import settings69f00b from './settings'
import users from './users'
import auditLogs from './audit-logs'
import orders from './orders'
import platformRatings from './platform-ratings'
import issueReports from './issue-reports'
/**
* @see \App\Http\Controllers\Admin\AdminDashboardController::dashboard
 * @see app/Http/Controllers/Admin/AdminDashboardController.php:16
 * @route '/admin/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/admin/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\AdminDashboardController::dashboard
 * @see app/Http/Controllers/Admin/AdminDashboardController.php:16
 * @route '/admin/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AdminDashboardController::dashboard
 * @see app/Http/Controllers/Admin/AdminDashboardController.php:16
 * @route '/admin/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\AdminDashboardController::dashboard
 * @see app/Http/Controllers/Admin/AdminDashboardController.php:16
 * @route '/admin/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\AdminDashboardController::dashboard
 * @see app/Http/Controllers/Admin/AdminDashboardController.php:16
 * @route '/admin/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\AdminDashboardController::dashboard
 * @see app/Http/Controllers/Admin/AdminDashboardController.php:16
 * @route '/admin/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\AdminDashboardController::dashboard
 * @see app/Http/Controllers/Admin/AdminDashboardController.php:16
 * @route '/admin/dashboard'
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
 * @see routes/web.php:92
 * @route '/admin/settings/profile'
 */
export const settings = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(options),
    method: 'get',
})

settings.definition = {
    methods: ["get","head"],
    url: '/admin/settings/profile',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:92
 * @route '/admin/settings/profile'
 */
settings.url = (options?: RouteQueryOptions) => {
    return settings.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:92
 * @route '/admin/settings/profile'
 */
settings.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: settings.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:92
 * @route '/admin/settings/profile'
 */
settings.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: settings.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:92
 * @route '/admin/settings/profile'
 */
    const settingsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: settings.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:92
 * @route '/admin/settings/profile'
 */
        settingsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: settings.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:92
 * @route '/admin/settings/profile'
 */
        settingsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: settings.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    settings.form = settingsForm
/**
* @see \App\Http\Controllers\Admin\OrderController::kycFile
 * @see app/Http/Controllers/Admin/OrderController.php:150
 * @route '/admin/kyc-file'
 */
export const kycFile = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: kycFile.url(options),
    method: 'get',
})

kycFile.definition = {
    methods: ["get","head"],
    url: '/admin/kyc-file',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\OrderController::kycFile
 * @see app/Http/Controllers/Admin/OrderController.php:150
 * @route '/admin/kyc-file'
 */
kycFile.url = (options?: RouteQueryOptions) => {
    return kycFile.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\OrderController::kycFile
 * @see app/Http/Controllers/Admin/OrderController.php:150
 * @route '/admin/kyc-file'
 */
kycFile.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: kycFile.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\OrderController::kycFile
 * @see app/Http/Controllers/Admin/OrderController.php:150
 * @route '/admin/kyc-file'
 */
kycFile.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: kycFile.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\OrderController::kycFile
 * @see app/Http/Controllers/Admin/OrderController.php:150
 * @route '/admin/kyc-file'
 */
    const kycFileForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: kycFile.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\OrderController::kycFile
 * @see app/Http/Controllers/Admin/OrderController.php:150
 * @route '/admin/kyc-file'
 */
        kycFileForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: kycFile.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\OrderController::kycFile
 * @see app/Http/Controllers/Admin/OrderController.php:150
 * @route '/admin/kyc-file'
 */
        kycFileForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: kycFile.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    kycFile.form = kycFileForm
/**
* @see \App\Http\Controllers\Admin\AnalyticsController::analytics
 * @see app/Http/Controllers/Admin/AnalyticsController.php:16
 * @route '/admin/analytics'
 */
export const analytics = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: analytics.url(options),
    method: 'get',
})

analytics.definition = {
    methods: ["get","head"],
    url: '/admin/analytics',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\AnalyticsController::analytics
 * @see app/Http/Controllers/Admin/AnalyticsController.php:16
 * @route '/admin/analytics'
 */
analytics.url = (options?: RouteQueryOptions) => {
    return analytics.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AnalyticsController::analytics
 * @see app/Http/Controllers/Admin/AnalyticsController.php:16
 * @route '/admin/analytics'
 */
analytics.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: analytics.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\AnalyticsController::analytics
 * @see app/Http/Controllers/Admin/AnalyticsController.php:16
 * @route '/admin/analytics'
 */
analytics.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: analytics.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\AnalyticsController::analytics
 * @see app/Http/Controllers/Admin/AnalyticsController.php:16
 * @route '/admin/analytics'
 */
    const analyticsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: analytics.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\AnalyticsController::analytics
 * @see app/Http/Controllers/Admin/AnalyticsController.php:16
 * @route '/admin/analytics'
 */
        analyticsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: analytics.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\AnalyticsController::analytics
 * @see app/Http/Controllers/Admin/AnalyticsController.php:16
 * @route '/admin/analytics'
 */
        analyticsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: analytics.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    analytics.form = analyticsForm
const admin = {
    dashboard: Object.assign(dashboard, dashboard),
settings: Object.assign(settings, settings69f00b),
users: Object.assign(users, users),
auditLogs: Object.assign(auditLogs, auditLogs),
orders: Object.assign(orders, orders),
kycFile: Object.assign(kycFile, kycFile),
analytics: Object.assign(analytics, analytics),
platformRatings: Object.assign(platformRatings, platformRatings),
issueReports: Object.assign(issueReports, issueReports),
}

export default admin