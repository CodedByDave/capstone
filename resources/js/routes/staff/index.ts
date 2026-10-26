import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import attendance from './attendance'
import employee from './employee'
import schedule from './schedule'
import inventory from './inventory'
import supplier from './supplier'
import logs from './logs'
import orders from './orders'
import services from './services'
import promotions from './promotions'
import ratings from './ratings'
import complaints from './complaints'
import logisticsC73990 from './logistics'
import payroll from './payroll'
import reports from './reports'
import finance from './finance'
/**
* @see \App\Http\Controllers\Staff\StaffDashboardController::dashboard
 * @see app/Http/Controllers/Staff/StaffDashboardController.php:20
 * @route '/staff/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/staff/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Staff\StaffDashboardController::dashboard
 * @see app/Http/Controllers/Staff/StaffDashboardController.php:20
 * @route '/staff/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Staff\StaffDashboardController::dashboard
 * @see app/Http/Controllers/Staff/StaffDashboardController.php:20
 * @route '/staff/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Staff\StaffDashboardController::dashboard
 * @see app/Http/Controllers/Staff/StaffDashboardController.php:20
 * @route '/staff/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Staff\StaffDashboardController::dashboard
 * @see app/Http/Controllers/Staff/StaffDashboardController.php:20
 * @route '/staff/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Staff\StaffDashboardController::dashboard
 * @see app/Http/Controllers/Staff/StaffDashboardController.php:20
 * @route '/staff/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Staff\StaffDashboardController::dashboard
 * @see app/Http/Controllers/Staff/StaffDashboardController.php:20
 * @route '/staff/dashboard'
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
* @see \App\Http\Controllers\Shop\LogisticsController::logistics
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/staff/logistics'
 */
export const logistics = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: logistics.url(options),
    method: 'get',
})

logistics.definition = {
    methods: ["get","head"],
    url: '/staff/logistics',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\LogisticsController::logistics
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/staff/logistics'
 */
logistics.url = (options?: RouteQueryOptions) => {
    return logistics.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\LogisticsController::logistics
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/staff/logistics'
 */
logistics.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: logistics.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\LogisticsController::logistics
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/staff/logistics'
 */
logistics.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: logistics.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\LogisticsController::logistics
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/staff/logistics'
 */
    const logisticsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: logistics.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\LogisticsController::logistics
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/staff/logistics'
 */
        logisticsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: logistics.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\LogisticsController::logistics
 * @see app/Http/Controllers/Shop/LogisticsController.php:24
 * @route '/staff/logistics'
 */
        logisticsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: logistics.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    logistics.form = logisticsForm
const staff = {
    dashboard: Object.assign(dashboard, dashboard),
attendance: Object.assign(attendance, attendance),
employee: Object.assign(employee, employee),
schedule: Object.assign(schedule, schedule),
inventory: Object.assign(inventory, inventory),
supplier: Object.assign(supplier, supplier),
logs: Object.assign(logs, logs),
orders: Object.assign(orders, orders),
services: Object.assign(services, services),
promotions: Object.assign(promotions, promotions),
ratings: Object.assign(ratings, ratings),
complaints: Object.assign(complaints, complaints),
logistics: Object.assign(logistics, logisticsC73990),
payroll: Object.assign(payroll, payroll),
reports: Object.assign(reports, reports),
finance: Object.assign(finance, finance),
}

export default staff