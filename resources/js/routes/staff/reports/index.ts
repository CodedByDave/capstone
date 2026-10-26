import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/staff/reports'
 */
export const overview = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: overview.url(options),
    method: 'get',
})

overview.definition = {
    methods: ["get","head"],
    url: '/staff/reports',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/staff/reports'
 */
overview.url = (options?: RouteQueryOptions) => {
    return overview.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/staff/reports'
 */
overview.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: overview.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/staff/reports'
 */
overview.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: overview.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/staff/reports'
 */
    const overviewForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: overview.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/staff/reports'
 */
        overviewForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: overview.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/staff/reports'
 */
        overviewForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: overview.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    overview.form = overviewForm
/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/staff/reports/inventory'
 */
export const inventory = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: inventory.url(options),
    method: 'get',
})

inventory.definition = {
    methods: ["get","head"],
    url: '/staff/reports/inventory',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/staff/reports/inventory'
 */
inventory.url = (options?: RouteQueryOptions) => {
    return inventory.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/staff/reports/inventory'
 */
inventory.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: inventory.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/staff/reports/inventory'
 */
inventory.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: inventory.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/staff/reports/inventory'
 */
    const inventoryForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: inventory.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/staff/reports/inventory'
 */
        inventoryForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: inventory.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/staff/reports/inventory'
 */
        inventoryForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: inventory.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    inventory.form = inventoryForm
/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/staff/reports/employee'
 */
export const employee = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: employee.url(options),
    method: 'get',
})

employee.definition = {
    methods: ["get","head"],
    url: '/staff/reports/employee',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/staff/reports/employee'
 */
employee.url = (options?: RouteQueryOptions) => {
    return employee.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/staff/reports/employee'
 */
employee.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: employee.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/staff/reports/employee'
 */
employee.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: employee.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/staff/reports/employee'
 */
    const employeeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: employee.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/staff/reports/employee'
 */
        employeeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: employee.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/staff/reports/employee'
 */
        employeeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: employee.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    employee.form = employeeForm
const reports = {
    overview: Object.assign(overview, overview),
inventory: Object.assign(inventory, inventory),
employee: Object.assign(employee, employee),
}

export default reports