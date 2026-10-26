import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/shop/reports'
 */
const overviewf58b9d3d1e3618d266e743fc8f274ed1 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: overviewf58b9d3d1e3618d266e743fc8f274ed1.url(options),
    method: 'get',
})

overviewf58b9d3d1e3618d266e743fc8f274ed1.definition = {
    methods: ["get","head"],
    url: '/shop/reports',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/shop/reports'
 */
overviewf58b9d3d1e3618d266e743fc8f274ed1.url = (options?: RouteQueryOptions) => {
    return overviewf58b9d3d1e3618d266e743fc8f274ed1.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/shop/reports'
 */
overviewf58b9d3d1e3618d266e743fc8f274ed1.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: overviewf58b9d3d1e3618d266e743fc8f274ed1.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/shop/reports'
 */
overviewf58b9d3d1e3618d266e743fc8f274ed1.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: overviewf58b9d3d1e3618d266e743fc8f274ed1.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/shop/reports'
 */
    const overviewf58b9d3d1e3618d266e743fc8f274ed1Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: overviewf58b9d3d1e3618d266e743fc8f274ed1.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/shop/reports'
 */
        overviewf58b9d3d1e3618d266e743fc8f274ed1Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: overviewf58b9d3d1e3618d266e743fc8f274ed1.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/shop/reports'
 */
        overviewf58b9d3d1e3618d266e743fc8f274ed1Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: overviewf58b9d3d1e3618d266e743fc8f274ed1.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    overviewf58b9d3d1e3618d266e743fc8f274ed1.form = overviewf58b9d3d1e3618d266e743fc8f274ed1Form
    /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/staff/reports'
 */
const overviewc104b9a411d6d369d06701bcdf2f77d9 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: overviewc104b9a411d6d369d06701bcdf2f77d9.url(options),
    method: 'get',
})

overviewc104b9a411d6d369d06701bcdf2f77d9.definition = {
    methods: ["get","head"],
    url: '/staff/reports',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/staff/reports'
 */
overviewc104b9a411d6d369d06701bcdf2f77d9.url = (options?: RouteQueryOptions) => {
    return overviewc104b9a411d6d369d06701bcdf2f77d9.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/staff/reports'
 */
overviewc104b9a411d6d369d06701bcdf2f77d9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: overviewc104b9a411d6d369d06701bcdf2f77d9.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/staff/reports'
 */
overviewc104b9a411d6d369d06701bcdf2f77d9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: overviewc104b9a411d6d369d06701bcdf2f77d9.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/staff/reports'
 */
    const overviewc104b9a411d6d369d06701bcdf2f77d9Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: overviewc104b9a411d6d369d06701bcdf2f77d9.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/staff/reports'
 */
        overviewc104b9a411d6d369d06701bcdf2f77d9Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: overviewc104b9a411d6d369d06701bcdf2f77d9.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:66
 * @route '/staff/reports'
 */
        overviewc104b9a411d6d369d06701bcdf2f77d9Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: overviewc104b9a411d6d369d06701bcdf2f77d9.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    overviewc104b9a411d6d369d06701bcdf2f77d9.form = overviewc104b9a411d6d369d06701bcdf2f77d9Form

export const overview = {
    '/shop/reports': overviewf58b9d3d1e3618d266e743fc8f274ed1,
    '/staff/reports': overviewc104b9a411d6d369d06701bcdf2f77d9,
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/shop/reports/inventory'
 */
const inventory0b8cee2e227e86df6bd8c2cd699d33f8 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: inventory0b8cee2e227e86df6bd8c2cd699d33f8.url(options),
    method: 'get',
})

inventory0b8cee2e227e86df6bd8c2cd699d33f8.definition = {
    methods: ["get","head"],
    url: '/shop/reports/inventory',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/shop/reports/inventory'
 */
inventory0b8cee2e227e86df6bd8c2cd699d33f8.url = (options?: RouteQueryOptions) => {
    return inventory0b8cee2e227e86df6bd8c2cd699d33f8.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/shop/reports/inventory'
 */
inventory0b8cee2e227e86df6bd8c2cd699d33f8.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: inventory0b8cee2e227e86df6bd8c2cd699d33f8.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/shop/reports/inventory'
 */
inventory0b8cee2e227e86df6bd8c2cd699d33f8.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: inventory0b8cee2e227e86df6bd8c2cd699d33f8.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/shop/reports/inventory'
 */
    const inventory0b8cee2e227e86df6bd8c2cd699d33f8Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: inventory0b8cee2e227e86df6bd8c2cd699d33f8.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/shop/reports/inventory'
 */
        inventory0b8cee2e227e86df6bd8c2cd699d33f8Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: inventory0b8cee2e227e86df6bd8c2cd699d33f8.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/shop/reports/inventory'
 */
        inventory0b8cee2e227e86df6bd8c2cd699d33f8Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: inventory0b8cee2e227e86df6bd8c2cd699d33f8.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    inventory0b8cee2e227e86df6bd8c2cd699d33f8.form = inventory0b8cee2e227e86df6bd8c2cd699d33f8Form
    /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/staff/reports/inventory'
 */
const inventory8d299b25c5c83abf10e77cfedc02892c = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: inventory8d299b25c5c83abf10e77cfedc02892c.url(options),
    method: 'get',
})

inventory8d299b25c5c83abf10e77cfedc02892c.definition = {
    methods: ["get","head"],
    url: '/staff/reports/inventory',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/staff/reports/inventory'
 */
inventory8d299b25c5c83abf10e77cfedc02892c.url = (options?: RouteQueryOptions) => {
    return inventory8d299b25c5c83abf10e77cfedc02892c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/staff/reports/inventory'
 */
inventory8d299b25c5c83abf10e77cfedc02892c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: inventory8d299b25c5c83abf10e77cfedc02892c.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/staff/reports/inventory'
 */
inventory8d299b25c5c83abf10e77cfedc02892c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: inventory8d299b25c5c83abf10e77cfedc02892c.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/staff/reports/inventory'
 */
    const inventory8d299b25c5c83abf10e77cfedc02892cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: inventory8d299b25c5c83abf10e77cfedc02892c.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/staff/reports/inventory'
 */
        inventory8d299b25c5c83abf10e77cfedc02892cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: inventory8d299b25c5c83abf10e77cfedc02892c.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:118
 * @route '/staff/reports/inventory'
 */
        inventory8d299b25c5c83abf10e77cfedc02892cForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: inventory8d299b25c5c83abf10e77cfedc02892c.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    inventory8d299b25c5c83abf10e77cfedc02892c.form = inventory8d299b25c5c83abf10e77cfedc02892cForm

export const inventory = {
    '/shop/reports/inventory': inventory0b8cee2e227e86df6bd8c2cd699d33f8,
    '/staff/reports/inventory': inventory8d299b25c5c83abf10e77cfedc02892c,
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/shop/reports/employee'
 */
const employee7941040f71f94ad13b5d474f4917db7f = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: employee7941040f71f94ad13b5d474f4917db7f.url(options),
    method: 'get',
})

employee7941040f71f94ad13b5d474f4917db7f.definition = {
    methods: ["get","head"],
    url: '/shop/reports/employee',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/shop/reports/employee'
 */
employee7941040f71f94ad13b5d474f4917db7f.url = (options?: RouteQueryOptions) => {
    return employee7941040f71f94ad13b5d474f4917db7f.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/shop/reports/employee'
 */
employee7941040f71f94ad13b5d474f4917db7f.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: employee7941040f71f94ad13b5d474f4917db7f.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/shop/reports/employee'
 */
employee7941040f71f94ad13b5d474f4917db7f.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: employee7941040f71f94ad13b5d474f4917db7f.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/shop/reports/employee'
 */
    const employee7941040f71f94ad13b5d474f4917db7fForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: employee7941040f71f94ad13b5d474f4917db7f.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/shop/reports/employee'
 */
        employee7941040f71f94ad13b5d474f4917db7fForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: employee7941040f71f94ad13b5d474f4917db7f.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/shop/reports/employee'
 */
        employee7941040f71f94ad13b5d474f4917db7fForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: employee7941040f71f94ad13b5d474f4917db7f.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    employee7941040f71f94ad13b5d474f4917db7f.form = employee7941040f71f94ad13b5d474f4917db7fForm
    /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/staff/reports/employee'
 */
const employee99d20509669d4860b9bc0427a6a6844d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: employee99d20509669d4860b9bc0427a6a6844d.url(options),
    method: 'get',
})

employee99d20509669d4860b9bc0427a6a6844d.definition = {
    methods: ["get","head"],
    url: '/staff/reports/employee',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/staff/reports/employee'
 */
employee99d20509669d4860b9bc0427a6a6844d.url = (options?: RouteQueryOptions) => {
    return employee99d20509669d4860b9bc0427a6a6844d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/staff/reports/employee'
 */
employee99d20509669d4860b9bc0427a6a6844d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: employee99d20509669d4860b9bc0427a6a6844d.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/staff/reports/employee'
 */
employee99d20509669d4860b9bc0427a6a6844d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: employee99d20509669d4860b9bc0427a6a6844d.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/staff/reports/employee'
 */
    const employee99d20509669d4860b9bc0427a6a6844dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: employee99d20509669d4860b9bc0427a6a6844d.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/staff/reports/employee'
 */
        employee99d20509669d4860b9bc0427a6a6844dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: employee99d20509669d4860b9bc0427a6a6844d.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:205
 * @route '/staff/reports/employee'
 */
        employee99d20509669d4860b9bc0427a6a6844dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: employee99d20509669d4860b9bc0427a6a6844d.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    employee99d20509669d4860b9bc0427a6a6844d.form = employee99d20509669d4860b9bc0427a6a6844dForm

export const employee = {
    '/shop/reports/employee': employee7941040f71f94ad13b5d474f4917db7f,
    '/staff/reports/employee': employee99d20509669d4860b9bc0427a6a6844d,
}

const ReportsController = { overview, inventory, employee }

export default ReportsController