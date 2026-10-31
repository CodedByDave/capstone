import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:52
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
 * @see app/Http/Controllers/Shop/ReportsController.php:52
 * @route '/shop/reports'
 */
overviewf58b9d3d1e3618d266e743fc8f274ed1.url = (options?: RouteQueryOptions) => {
    return overviewf58b9d3d1e3618d266e743fc8f274ed1.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:52
 * @route '/shop/reports'
 */
overviewf58b9d3d1e3618d266e743fc8f274ed1.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: overviewf58b9d3d1e3618d266e743fc8f274ed1.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:52
 * @route '/shop/reports'
 */
overviewf58b9d3d1e3618d266e743fc8f274ed1.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: overviewf58b9d3d1e3618d266e743fc8f274ed1.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:52
 * @route '/shop/reports'
 */
    const overviewf58b9d3d1e3618d266e743fc8f274ed1Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: overviewf58b9d3d1e3618d266e743fc8f274ed1.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:52
 * @route '/shop/reports'
 */
        overviewf58b9d3d1e3618d266e743fc8f274ed1Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: overviewf58b9d3d1e3618d266e743fc8f274ed1.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:52
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
 * @see app/Http/Controllers/Shop/ReportsController.php:52
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
 * @see app/Http/Controllers/Shop/ReportsController.php:52
 * @route '/staff/reports'
 */
overviewc104b9a411d6d369d06701bcdf2f77d9.url = (options?: RouteQueryOptions) => {
    return overviewc104b9a411d6d369d06701bcdf2f77d9.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:52
 * @route '/staff/reports'
 */
overviewc104b9a411d6d369d06701bcdf2f77d9.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: overviewc104b9a411d6d369d06701bcdf2f77d9.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:52
 * @route '/staff/reports'
 */
overviewc104b9a411d6d369d06701bcdf2f77d9.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: overviewc104b9a411d6d369d06701bcdf2f77d9.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:52
 * @route '/staff/reports'
 */
    const overviewc104b9a411d6d369d06701bcdf2f77d9Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: overviewc104b9a411d6d369d06701bcdf2f77d9.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:52
 * @route '/staff/reports'
 */
        overviewc104b9a411d6d369d06701bcdf2f77d9Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: overviewc104b9a411d6d369d06701bcdf2f77d9.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::overview
 * @see app/Http/Controllers/Shop/ReportsController.php:52
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
 * @see app/Http/Controllers/Shop/ReportsController.php:103
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
 * @see app/Http/Controllers/Shop/ReportsController.php:103
 * @route '/shop/reports/inventory'
 */
inventory0b8cee2e227e86df6bd8c2cd699d33f8.url = (options?: RouteQueryOptions) => {
    return inventory0b8cee2e227e86df6bd8c2cd699d33f8.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:103
 * @route '/shop/reports/inventory'
 */
inventory0b8cee2e227e86df6bd8c2cd699d33f8.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: inventory0b8cee2e227e86df6bd8c2cd699d33f8.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:103
 * @route '/shop/reports/inventory'
 */
inventory0b8cee2e227e86df6bd8c2cd699d33f8.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: inventory0b8cee2e227e86df6bd8c2cd699d33f8.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:103
 * @route '/shop/reports/inventory'
 */
    const inventory0b8cee2e227e86df6bd8c2cd699d33f8Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: inventory0b8cee2e227e86df6bd8c2cd699d33f8.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:103
 * @route '/shop/reports/inventory'
 */
        inventory0b8cee2e227e86df6bd8c2cd699d33f8Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: inventory0b8cee2e227e86df6bd8c2cd699d33f8.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:103
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
 * @see app/Http/Controllers/Shop/ReportsController.php:103
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
 * @see app/Http/Controllers/Shop/ReportsController.php:103
 * @route '/staff/reports/inventory'
 */
inventory8d299b25c5c83abf10e77cfedc02892c.url = (options?: RouteQueryOptions) => {
    return inventory8d299b25c5c83abf10e77cfedc02892c.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:103
 * @route '/staff/reports/inventory'
 */
inventory8d299b25c5c83abf10e77cfedc02892c.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: inventory8d299b25c5c83abf10e77cfedc02892c.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:103
 * @route '/staff/reports/inventory'
 */
inventory8d299b25c5c83abf10e77cfedc02892c.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: inventory8d299b25c5c83abf10e77cfedc02892c.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:103
 * @route '/staff/reports/inventory'
 */
    const inventory8d299b25c5c83abf10e77cfedc02892cForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: inventory8d299b25c5c83abf10e77cfedc02892c.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:103
 * @route '/staff/reports/inventory'
 */
        inventory8d299b25c5c83abf10e77cfedc02892cForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: inventory8d299b25c5c83abf10e77cfedc02892c.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::inventory
 * @see app/Http/Controllers/Shop/ReportsController.php:103
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
 * @see app/Http/Controllers/Shop/ReportsController.php:190
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
 * @see app/Http/Controllers/Shop/ReportsController.php:190
 * @route '/shop/reports/employee'
 */
employee7941040f71f94ad13b5d474f4917db7f.url = (options?: RouteQueryOptions) => {
    return employee7941040f71f94ad13b5d474f4917db7f.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:190
 * @route '/shop/reports/employee'
 */
employee7941040f71f94ad13b5d474f4917db7f.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: employee7941040f71f94ad13b5d474f4917db7f.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:190
 * @route '/shop/reports/employee'
 */
employee7941040f71f94ad13b5d474f4917db7f.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: employee7941040f71f94ad13b5d474f4917db7f.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:190
 * @route '/shop/reports/employee'
 */
    const employee7941040f71f94ad13b5d474f4917db7fForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: employee7941040f71f94ad13b5d474f4917db7f.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:190
 * @route '/shop/reports/employee'
 */
        employee7941040f71f94ad13b5d474f4917db7fForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: employee7941040f71f94ad13b5d474f4917db7f.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:190
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
 * @see app/Http/Controllers/Shop/ReportsController.php:190
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
 * @see app/Http/Controllers/Shop/ReportsController.php:190
 * @route '/staff/reports/employee'
 */
employee99d20509669d4860b9bc0427a6a6844d.url = (options?: RouteQueryOptions) => {
    return employee99d20509669d4860b9bc0427a6a6844d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:190
 * @route '/staff/reports/employee'
 */
employee99d20509669d4860b9bc0427a6a6844d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: employee99d20509669d4860b9bc0427a6a6844d.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:190
 * @route '/staff/reports/employee'
 */
employee99d20509669d4860b9bc0427a6a6844d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: employee99d20509669d4860b9bc0427a6a6844d.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:190
 * @route '/staff/reports/employee'
 */
    const employee99d20509669d4860b9bc0427a6a6844dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: employee99d20509669d4860b9bc0427a6a6844d.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:190
 * @route '/staff/reports/employee'
 */
        employee99d20509669d4860b9bc0427a6a6844dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: employee99d20509669d4860b9bc0427a6a6844d.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::employee
 * @see app/Http/Controllers/Shop/ReportsController.php:190
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

/**
* @see \App\Http\Controllers\Shop\ReportsController::finance
 * @see app/Http/Controllers/Shop/ReportsController.php:255
 * @route '/shop/reports/finance'
 */
const finance06ff818f83f77689bc5d93b7f03cb008 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: finance06ff818f83f77689bc5d93b7f03cb008.url(options),
    method: 'get',
})

finance06ff818f83f77689bc5d93b7f03cb008.definition = {
    methods: ["get","head"],
    url: '/shop/reports/finance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::finance
 * @see app/Http/Controllers/Shop/ReportsController.php:255
 * @route '/shop/reports/finance'
 */
finance06ff818f83f77689bc5d93b7f03cb008.url = (options?: RouteQueryOptions) => {
    return finance06ff818f83f77689bc5d93b7f03cb008.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::finance
 * @see app/Http/Controllers/Shop/ReportsController.php:255
 * @route '/shop/reports/finance'
 */
finance06ff818f83f77689bc5d93b7f03cb008.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: finance06ff818f83f77689bc5d93b7f03cb008.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::finance
 * @see app/Http/Controllers/Shop/ReportsController.php:255
 * @route '/shop/reports/finance'
 */
finance06ff818f83f77689bc5d93b7f03cb008.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: finance06ff818f83f77689bc5d93b7f03cb008.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::finance
 * @see app/Http/Controllers/Shop/ReportsController.php:255
 * @route '/shop/reports/finance'
 */
    const finance06ff818f83f77689bc5d93b7f03cb008Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: finance06ff818f83f77689bc5d93b7f03cb008.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::finance
 * @see app/Http/Controllers/Shop/ReportsController.php:255
 * @route '/shop/reports/finance'
 */
        finance06ff818f83f77689bc5d93b7f03cb008Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: finance06ff818f83f77689bc5d93b7f03cb008.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::finance
 * @see app/Http/Controllers/Shop/ReportsController.php:255
 * @route '/shop/reports/finance'
 */
        finance06ff818f83f77689bc5d93b7f03cb008Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: finance06ff818f83f77689bc5d93b7f03cb008.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    finance06ff818f83f77689bc5d93b7f03cb008.form = finance06ff818f83f77689bc5d93b7f03cb008Form
    /**
* @see \App\Http\Controllers\Shop\ReportsController::finance
 * @see app/Http/Controllers/Shop/ReportsController.php:255
 * @route '/staff/reports/finance'
 */
const finance7cce0d76a13640fc4d3cd3f6a40a257e = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: finance7cce0d76a13640fc4d3cd3f6a40a257e.url(options),
    method: 'get',
})

finance7cce0d76a13640fc4d3cd3f6a40a257e.definition = {
    methods: ["get","head"],
    url: '/staff/reports/finance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::finance
 * @see app/Http/Controllers/Shop/ReportsController.php:255
 * @route '/staff/reports/finance'
 */
finance7cce0d76a13640fc4d3cd3f6a40a257e.url = (options?: RouteQueryOptions) => {
    return finance7cce0d76a13640fc4d3cd3f6a40a257e.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::finance
 * @see app/Http/Controllers/Shop/ReportsController.php:255
 * @route '/staff/reports/finance'
 */
finance7cce0d76a13640fc4d3cd3f6a40a257e.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: finance7cce0d76a13640fc4d3cd3f6a40a257e.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::finance
 * @see app/Http/Controllers/Shop/ReportsController.php:255
 * @route '/staff/reports/finance'
 */
finance7cce0d76a13640fc4d3cd3f6a40a257e.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: finance7cce0d76a13640fc4d3cd3f6a40a257e.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::finance
 * @see app/Http/Controllers/Shop/ReportsController.php:255
 * @route '/staff/reports/finance'
 */
    const finance7cce0d76a13640fc4d3cd3f6a40a257eForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: finance7cce0d76a13640fc4d3cd3f6a40a257e.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::finance
 * @see app/Http/Controllers/Shop/ReportsController.php:255
 * @route '/staff/reports/finance'
 */
        finance7cce0d76a13640fc4d3cd3f6a40a257eForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: finance7cce0d76a13640fc4d3cd3f6a40a257e.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::finance
 * @see app/Http/Controllers/Shop/ReportsController.php:255
 * @route '/staff/reports/finance'
 */
        finance7cce0d76a13640fc4d3cd3f6a40a257eForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: finance7cce0d76a13640fc4d3cd3f6a40a257e.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    finance7cce0d76a13640fc4d3cd3f6a40a257e.form = finance7cce0d76a13640fc4d3cd3f6a40a257eForm

export const finance = {
    '/shop/reports/finance': finance06ff818f83f77689bc5d93b7f03cb008,
    '/staff/reports/finance': finance7cce0d76a13640fc4d3cd3f6a40a257e,
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::pricing
 * @see app/Http/Controllers/Shop/ReportsController.php:260
 * @route '/shop/reports/pricing'
 */
const pricingbb5937b0509fe047ec3dac2cc11a458d = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pricingbb5937b0509fe047ec3dac2cc11a458d.url(options),
    method: 'get',
})

pricingbb5937b0509fe047ec3dac2cc11a458d.definition = {
    methods: ["get","head"],
    url: '/shop/reports/pricing',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::pricing
 * @see app/Http/Controllers/Shop/ReportsController.php:260
 * @route '/shop/reports/pricing'
 */
pricingbb5937b0509fe047ec3dac2cc11a458d.url = (options?: RouteQueryOptions) => {
    return pricingbb5937b0509fe047ec3dac2cc11a458d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::pricing
 * @see app/Http/Controllers/Shop/ReportsController.php:260
 * @route '/shop/reports/pricing'
 */
pricingbb5937b0509fe047ec3dac2cc11a458d.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pricingbb5937b0509fe047ec3dac2cc11a458d.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::pricing
 * @see app/Http/Controllers/Shop/ReportsController.php:260
 * @route '/shop/reports/pricing'
 */
pricingbb5937b0509fe047ec3dac2cc11a458d.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pricingbb5937b0509fe047ec3dac2cc11a458d.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::pricing
 * @see app/Http/Controllers/Shop/ReportsController.php:260
 * @route '/shop/reports/pricing'
 */
    const pricingbb5937b0509fe047ec3dac2cc11a458dForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: pricingbb5937b0509fe047ec3dac2cc11a458d.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::pricing
 * @see app/Http/Controllers/Shop/ReportsController.php:260
 * @route '/shop/reports/pricing'
 */
        pricingbb5937b0509fe047ec3dac2cc11a458dForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pricingbb5937b0509fe047ec3dac2cc11a458d.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::pricing
 * @see app/Http/Controllers/Shop/ReportsController.php:260
 * @route '/shop/reports/pricing'
 */
        pricingbb5937b0509fe047ec3dac2cc11a458dForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pricingbb5937b0509fe047ec3dac2cc11a458d.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    pricingbb5937b0509fe047ec3dac2cc11a458d.form = pricingbb5937b0509fe047ec3dac2cc11a458dForm
    /**
* @see \App\Http\Controllers\Shop\ReportsController::pricing
 * @see app/Http/Controllers/Shop/ReportsController.php:260
 * @route '/staff/reports/pricing'
 */
const pricing0a69a072ac0d6c62be406814e11f9fb7 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pricing0a69a072ac0d6c62be406814e11f9fb7.url(options),
    method: 'get',
})

pricing0a69a072ac0d6c62be406814e11f9fb7.definition = {
    methods: ["get","head"],
    url: '/staff/reports/pricing',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::pricing
 * @see app/Http/Controllers/Shop/ReportsController.php:260
 * @route '/staff/reports/pricing'
 */
pricing0a69a072ac0d6c62be406814e11f9fb7.url = (options?: RouteQueryOptions) => {
    return pricing0a69a072ac0d6c62be406814e11f9fb7.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::pricing
 * @see app/Http/Controllers/Shop/ReportsController.php:260
 * @route '/staff/reports/pricing'
 */
pricing0a69a072ac0d6c62be406814e11f9fb7.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: pricing0a69a072ac0d6c62be406814e11f9fb7.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::pricing
 * @see app/Http/Controllers/Shop/ReportsController.php:260
 * @route '/staff/reports/pricing'
 */
pricing0a69a072ac0d6c62be406814e11f9fb7.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: pricing0a69a072ac0d6c62be406814e11f9fb7.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::pricing
 * @see app/Http/Controllers/Shop/ReportsController.php:260
 * @route '/staff/reports/pricing'
 */
    const pricing0a69a072ac0d6c62be406814e11f9fb7Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: pricing0a69a072ac0d6c62be406814e11f9fb7.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::pricing
 * @see app/Http/Controllers/Shop/ReportsController.php:260
 * @route '/staff/reports/pricing'
 */
        pricing0a69a072ac0d6c62be406814e11f9fb7Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pricing0a69a072ac0d6c62be406814e11f9fb7.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::pricing
 * @see app/Http/Controllers/Shop/ReportsController.php:260
 * @route '/staff/reports/pricing'
 */
        pricing0a69a072ac0d6c62be406814e11f9fb7Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: pricing0a69a072ac0d6c62be406814e11f9fb7.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    pricing0a69a072ac0d6c62be406814e11f9fb7.form = pricing0a69a072ac0d6c62be406814e11f9fb7Form

export const pricing = {
    '/shop/reports/pricing': pricingbb5937b0509fe047ec3dac2cc11a458d,
    '/staff/reports/pricing': pricing0a69a072ac0d6c62be406814e11f9fb7,
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::operations
 * @see app/Http/Controllers/Shop/ReportsController.php:265
 * @route '/shop/reports/operations'
 */
const operations3dc8db81cb2498ca5539f57a72b06aea = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: operations3dc8db81cb2498ca5539f57a72b06aea.url(options),
    method: 'get',
})

operations3dc8db81cb2498ca5539f57a72b06aea.definition = {
    methods: ["get","head"],
    url: '/shop/reports/operations',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::operations
 * @see app/Http/Controllers/Shop/ReportsController.php:265
 * @route '/shop/reports/operations'
 */
operations3dc8db81cb2498ca5539f57a72b06aea.url = (options?: RouteQueryOptions) => {
    return operations3dc8db81cb2498ca5539f57a72b06aea.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::operations
 * @see app/Http/Controllers/Shop/ReportsController.php:265
 * @route '/shop/reports/operations'
 */
operations3dc8db81cb2498ca5539f57a72b06aea.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: operations3dc8db81cb2498ca5539f57a72b06aea.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::operations
 * @see app/Http/Controllers/Shop/ReportsController.php:265
 * @route '/shop/reports/operations'
 */
operations3dc8db81cb2498ca5539f57a72b06aea.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: operations3dc8db81cb2498ca5539f57a72b06aea.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::operations
 * @see app/Http/Controllers/Shop/ReportsController.php:265
 * @route '/shop/reports/operations'
 */
    const operations3dc8db81cb2498ca5539f57a72b06aeaForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: operations3dc8db81cb2498ca5539f57a72b06aea.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::operations
 * @see app/Http/Controllers/Shop/ReportsController.php:265
 * @route '/shop/reports/operations'
 */
        operations3dc8db81cb2498ca5539f57a72b06aeaForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: operations3dc8db81cb2498ca5539f57a72b06aea.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::operations
 * @see app/Http/Controllers/Shop/ReportsController.php:265
 * @route '/shop/reports/operations'
 */
        operations3dc8db81cb2498ca5539f57a72b06aeaForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: operations3dc8db81cb2498ca5539f57a72b06aea.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    operations3dc8db81cb2498ca5539f57a72b06aea.form = operations3dc8db81cb2498ca5539f57a72b06aeaForm
    /**
* @see \App\Http\Controllers\Shop\ReportsController::operations
 * @see app/Http/Controllers/Shop/ReportsController.php:265
 * @route '/staff/reports/operations'
 */
const operationsda09d62937cb634e7c679ec01b5a4815 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: operationsda09d62937cb634e7c679ec01b5a4815.url(options),
    method: 'get',
})

operationsda09d62937cb634e7c679ec01b5a4815.definition = {
    methods: ["get","head"],
    url: '/staff/reports/operations',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\ReportsController::operations
 * @see app/Http/Controllers/Shop/ReportsController.php:265
 * @route '/staff/reports/operations'
 */
operationsda09d62937cb634e7c679ec01b5a4815.url = (options?: RouteQueryOptions) => {
    return operationsda09d62937cb634e7c679ec01b5a4815.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\ReportsController::operations
 * @see app/Http/Controllers/Shop/ReportsController.php:265
 * @route '/staff/reports/operations'
 */
operationsda09d62937cb634e7c679ec01b5a4815.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: operationsda09d62937cb634e7c679ec01b5a4815.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\ReportsController::operations
 * @see app/Http/Controllers/Shop/ReportsController.php:265
 * @route '/staff/reports/operations'
 */
operationsda09d62937cb634e7c679ec01b5a4815.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: operationsda09d62937cb634e7c679ec01b5a4815.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\ReportsController::operations
 * @see app/Http/Controllers/Shop/ReportsController.php:265
 * @route '/staff/reports/operations'
 */
    const operationsda09d62937cb634e7c679ec01b5a4815Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: operationsda09d62937cb634e7c679ec01b5a4815.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\ReportsController::operations
 * @see app/Http/Controllers/Shop/ReportsController.php:265
 * @route '/staff/reports/operations'
 */
        operationsda09d62937cb634e7c679ec01b5a4815Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: operationsda09d62937cb634e7c679ec01b5a4815.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\ReportsController::operations
 * @see app/Http/Controllers/Shop/ReportsController.php:265
 * @route '/staff/reports/operations'
 */
        operationsda09d62937cb634e7c679ec01b5a4815Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: operationsda09d62937cb634e7c679ec01b5a4815.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    operationsda09d62937cb634e7c679ec01b5a4815.form = operationsda09d62937cb634e7c679ec01b5a4815Form

export const operations = {
    '/shop/reports/operations': operations3dc8db81cb2498ca5539f57a72b06aea,
    '/staff/reports/operations': operationsda09d62937cb634e7c679ec01b5a4815,
}

const ReportsController = { overview, inventory, employee, finance, pricing, operations }

export default ReportsController