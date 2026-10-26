import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\UpgradeController::select
 * @see app/Http/Controllers/Shop/UpgradeController.php:74
 * @route '/shop/upgrade/select'
 */
export const select = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: select.url(options),
    method: 'post',
})

select.definition = {
    methods: ["post"],
    url: '/shop/upgrade/select',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\UpgradeController::select
 * @see app/Http/Controllers/Shop/UpgradeController.php:74
 * @route '/shop/upgrade/select'
 */
select.url = (options?: RouteQueryOptions) => {
    return select.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\UpgradeController::select
 * @see app/Http/Controllers/Shop/UpgradeController.php:74
 * @route '/shop/upgrade/select'
 */
select.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: select.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\UpgradeController::select
 * @see app/Http/Controllers/Shop/UpgradeController.php:74
 * @route '/shop/upgrade/select'
 */
    const selectForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: select.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\UpgradeController::select
 * @see app/Http/Controllers/Shop/UpgradeController.php:74
 * @route '/shop/upgrade/select'
 */
        selectForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: select.url(options),
            method: 'post',
        })
    
    select.form = selectForm
/**
* @see \App\Http\Controllers\Shop\UpgradeController::confirm
 * @see app/Http/Controllers/Shop/UpgradeController.php:120
 * @route '/shop/upgrade/confirm'
 */
export const confirm = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirm.url(options),
    method: 'get',
})

confirm.definition = {
    methods: ["get","head"],
    url: '/shop/upgrade/confirm',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\UpgradeController::confirm
 * @see app/Http/Controllers/Shop/UpgradeController.php:120
 * @route '/shop/upgrade/confirm'
 */
confirm.url = (options?: RouteQueryOptions) => {
    return confirm.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\UpgradeController::confirm
 * @see app/Http/Controllers/Shop/UpgradeController.php:120
 * @route '/shop/upgrade/confirm'
 */
confirm.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirm.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\UpgradeController::confirm
 * @see app/Http/Controllers/Shop/UpgradeController.php:120
 * @route '/shop/upgrade/confirm'
 */
confirm.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: confirm.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\UpgradeController::confirm
 * @see app/Http/Controllers/Shop/UpgradeController.php:120
 * @route '/shop/upgrade/confirm'
 */
    const confirmForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: confirm.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\UpgradeController::confirm
 * @see app/Http/Controllers/Shop/UpgradeController.php:120
 * @route '/shop/upgrade/confirm'
 */
        confirmForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: confirm.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\UpgradeController::confirm
 * @see app/Http/Controllers/Shop/UpgradeController.php:120
 * @route '/shop/upgrade/confirm'
 */
        confirmForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: confirm.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    confirm.form = confirmForm
/**
* @see \App\Http\Controllers\Shop\UpgradeController::process
 * @see app/Http/Controllers/Shop/UpgradeController.php:173
 * @route '/shop/upgrade/process'
 */
export const process = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: process.url(options),
    method: 'post',
})

process.definition = {
    methods: ["post"],
    url: '/shop/upgrade/process',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\UpgradeController::process
 * @see app/Http/Controllers/Shop/UpgradeController.php:173
 * @route '/shop/upgrade/process'
 */
process.url = (options?: RouteQueryOptions) => {
    return process.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\UpgradeController::process
 * @see app/Http/Controllers/Shop/UpgradeController.php:173
 * @route '/shop/upgrade/process'
 */
process.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: process.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\UpgradeController::process
 * @see app/Http/Controllers/Shop/UpgradeController.php:173
 * @route '/shop/upgrade/process'
 */
    const processForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: process.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\UpgradeController::process
 * @see app/Http/Controllers/Shop/UpgradeController.php:173
 * @route '/shop/upgrade/process'
 */
        processForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: process.url(options),
            method: 'post',
        })
    
    process.form = processForm
const upgrade = {
    select: Object.assign(select, select),
confirm: Object.assign(confirm, confirm),
process: Object.assign(process, process),
}

export default upgrade