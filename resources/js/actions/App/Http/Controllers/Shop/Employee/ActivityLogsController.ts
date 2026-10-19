import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\Employee\ActivityLogsController::index
 * @see app/Http/Controllers/Shop/Employee/ActivityLogsController.php:34
 * @route '/shop/logs'
 */
const index458b8fb441f569641a33bf5d213e92c4 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index458b8fb441f569641a33bf5d213e92c4.url(options),
    method: 'get',
})

index458b8fb441f569641a33bf5d213e92c4.definition = {
    methods: ["get","head"],
    url: '/shop/logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ActivityLogsController::index
 * @see app/Http/Controllers/Shop/Employee/ActivityLogsController.php:34
 * @route '/shop/logs'
 */
index458b8fb441f569641a33bf5d213e92c4.url = (options?: RouteQueryOptions) => {
    return index458b8fb441f569641a33bf5d213e92c4.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\ActivityLogsController::index
 * @see app/Http/Controllers/Shop/Employee/ActivityLogsController.php:34
 * @route '/shop/logs'
 */
index458b8fb441f569641a33bf5d213e92c4.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index458b8fb441f569641a33bf5d213e92c4.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\ActivityLogsController::index
 * @see app/Http/Controllers/Shop/Employee/ActivityLogsController.php:34
 * @route '/shop/logs'
 */
index458b8fb441f569641a33bf5d213e92c4.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index458b8fb441f569641a33bf5d213e92c4.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ActivityLogsController::index
 * @see app/Http/Controllers/Shop/Employee/ActivityLogsController.php:34
 * @route '/shop/logs'
 */
    const index458b8fb441f569641a33bf5d213e92c4Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index458b8fb441f569641a33bf5d213e92c4.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\ActivityLogsController::index
 * @see app/Http/Controllers/Shop/Employee/ActivityLogsController.php:34
 * @route '/shop/logs'
 */
        index458b8fb441f569641a33bf5d213e92c4Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index458b8fb441f569641a33bf5d213e92c4.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\ActivityLogsController::index
 * @see app/Http/Controllers/Shop/Employee/ActivityLogsController.php:34
 * @route '/shop/logs'
 */
        index458b8fb441f569641a33bf5d213e92c4Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index458b8fb441f569641a33bf5d213e92c4.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index458b8fb441f569641a33bf5d213e92c4.form = index458b8fb441f569641a33bf5d213e92c4Form
    /**
* @see \App\Http\Controllers\Shop\Employee\ActivityLogsController::index
 * @see app/Http/Controllers/Shop/Employee/ActivityLogsController.php:34
 * @route '/staff/logs'
 */
const indexad897ca4c68881def2f1bb2e683e7b11 = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexad897ca4c68881def2f1bb2e683e7b11.url(options),
    method: 'get',
})

indexad897ca4c68881def2f1bb2e683e7b11.definition = {
    methods: ["get","head"],
    url: '/staff/logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Employee\ActivityLogsController::index
 * @see app/Http/Controllers/Shop/Employee/ActivityLogsController.php:34
 * @route '/staff/logs'
 */
indexad897ca4c68881def2f1bb2e683e7b11.url = (options?: RouteQueryOptions) => {
    return indexad897ca4c68881def2f1bb2e683e7b11.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Employee\ActivityLogsController::index
 * @see app/Http/Controllers/Shop/Employee/ActivityLogsController.php:34
 * @route '/staff/logs'
 */
indexad897ca4c68881def2f1bb2e683e7b11.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: indexad897ca4c68881def2f1bb2e683e7b11.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Employee\ActivityLogsController::index
 * @see app/Http/Controllers/Shop/Employee/ActivityLogsController.php:34
 * @route '/staff/logs'
 */
indexad897ca4c68881def2f1bb2e683e7b11.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: indexad897ca4c68881def2f1bb2e683e7b11.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Employee\ActivityLogsController::index
 * @see app/Http/Controllers/Shop/Employee/ActivityLogsController.php:34
 * @route '/staff/logs'
 */
    const indexad897ca4c68881def2f1bb2e683e7b11Form = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: indexad897ca4c68881def2f1bb2e683e7b11.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Employee\ActivityLogsController::index
 * @see app/Http/Controllers/Shop/Employee/ActivityLogsController.php:34
 * @route '/staff/logs'
 */
        indexad897ca4c68881def2f1bb2e683e7b11Form.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexad897ca4c68881def2f1bb2e683e7b11.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Employee\ActivityLogsController::index
 * @see app/Http/Controllers/Shop/Employee/ActivityLogsController.php:34
 * @route '/staff/logs'
 */
        indexad897ca4c68881def2f1bb2e683e7b11Form.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: indexad897ca4c68881def2f1bb2e683e7b11.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    indexad897ca4c68881def2f1bb2e683e7b11.form = indexad897ca4c68881def2f1bb2e683e7b11Form

export const index = {
    '/shop/logs': index458b8fb441f569641a33bf5d213e92c4,
    '/staff/logs': indexad897ca4c68881def2f1bb2e683e7b11,
}

const ActivityLogsController = { index }

export default ActivityLogsController