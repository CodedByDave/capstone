import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\TrialController::show
 * @see app/Http/Controllers/Shop/TrialController.php:22
 * @route '/trial'
 */
export const show = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/trial',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\TrialController::show
 * @see app/Http/Controllers/Shop/TrialController.php:22
 * @route '/trial'
 */
show.url = (options?: RouteQueryOptions) => {
    return show.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\TrialController::show
 * @see app/Http/Controllers/Shop/TrialController.php:22
 * @route '/trial'
 */
show.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\TrialController::show
 * @see app/Http/Controllers/Shop/TrialController.php:22
 * @route '/trial'
 */
show.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\TrialController::show
 * @see app/Http/Controllers/Shop/TrialController.php:22
 * @route '/trial'
 */
    const showForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\TrialController::show
 * @see app/Http/Controllers/Shop/TrialController.php:22
 * @route '/trial'
 */
        showForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\TrialController::show
 * @see app/Http/Controllers/Shop/TrialController.php:22
 * @route '/trial'
 */
        showForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Shop\TrialController::start
 * @see app/Http/Controllers/Shop/TrialController.php:43
 * @route '/trial'
 */
export const start = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: start.url(options),
    method: 'post',
})

start.definition = {
    methods: ["post"],
    url: '/trial',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\TrialController::start
 * @see app/Http/Controllers/Shop/TrialController.php:43
 * @route '/trial'
 */
start.url = (options?: RouteQueryOptions) => {
    return start.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\TrialController::start
 * @see app/Http/Controllers/Shop/TrialController.php:43
 * @route '/trial'
 */
start.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: start.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\TrialController::start
 * @see app/Http/Controllers/Shop/TrialController.php:43
 * @route '/trial'
 */
    const startForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: start.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\TrialController::start
 * @see app/Http/Controllers/Shop/TrialController.php:43
 * @route '/trial'
 */
        startForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: start.url(options),
            method: 'post',
        })
    
    start.form = startForm
const TrialController = { show, start }

export default TrialController