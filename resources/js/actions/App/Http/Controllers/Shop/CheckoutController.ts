import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shop\CheckoutController::show
 * @see app/Http/Controllers/Shop/CheckoutController.php:31
 * @route '/shop/checkout/{plan}'
 */
export const show = (args: { plan: string | number } | [plan: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/shop/checkout/{plan}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\CheckoutController::show
 * @see app/Http/Controllers/Shop/CheckoutController.php:31
 * @route '/shop/checkout/{plan}'
 */
show.url = (args: { plan: string | number } | [plan: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { plan: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    plan: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        plan: args.plan,
                }

    return show.definition.url
            .replace('{plan}', parsedArgs.plan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::show
 * @see app/Http/Controllers/Shop/CheckoutController.php:31
 * @route '/shop/checkout/{plan}'
 */
show.get = (args: { plan: string | number } | [plan: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\CheckoutController::show
 * @see app/Http/Controllers/Shop/CheckoutController.php:31
 * @route '/shop/checkout/{plan}'
 */
show.head = (args: { plan: string | number } | [plan: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\CheckoutController::show
 * @see app/Http/Controllers/Shop/CheckoutController.php:31
 * @route '/shop/checkout/{plan}'
 */
    const showForm = (args: { plan: string | number } | [plan: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\CheckoutController::show
 * @see app/Http/Controllers/Shop/CheckoutController.php:31
 * @route '/shop/checkout/{plan}'
 */
        showForm.get = (args: { plan: string | number } | [plan: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\CheckoutController::show
 * @see app/Http/Controllers/Shop/CheckoutController.php:31
 * @route '/shop/checkout/{plan}'
 */
        showForm.head = (args: { plan: string | number } | [plan: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Shop\CheckoutController::select
 * @see app/Http/Controllers/Shop/CheckoutController.php:57
 * @route '/checkout/select'
 */
export const select = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: select.url(options),
    method: 'post',
})

select.definition = {
    methods: ["post"],
    url: '/checkout/select',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\CheckoutController::select
 * @see app/Http/Controllers/Shop/CheckoutController.php:57
 * @route '/checkout/select'
 */
select.url = (options?: RouteQueryOptions) => {
    return select.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::select
 * @see app/Http/Controllers/Shop/CheckoutController.php:57
 * @route '/checkout/select'
 */
select.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: select.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\CheckoutController::select
 * @see app/Http/Controllers/Shop/CheckoutController.php:57
 * @route '/checkout/select'
 */
    const selectForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: select.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\CheckoutController::select
 * @see app/Http/Controllers/Shop/CheckoutController.php:57
 * @route '/checkout/select'
 */
        selectForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: select.url(options),
            method: 'post',
        })
    
    select.form = selectForm
/**
* @see \App\Http\Controllers\Shop\CheckoutController::plans
 * @see app/Http/Controllers/Shop/CheckoutController.php:43
 * @route '/plans'
 */
export const plans = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: plans.url(options),
    method: 'get',
})

plans.definition = {
    methods: ["get","head"],
    url: '/plans',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\CheckoutController::plans
 * @see app/Http/Controllers/Shop/CheckoutController.php:43
 * @route '/plans'
 */
plans.url = (options?: RouteQueryOptions) => {
    return plans.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::plans
 * @see app/Http/Controllers/Shop/CheckoutController.php:43
 * @route '/plans'
 */
plans.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: plans.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\CheckoutController::plans
 * @see app/Http/Controllers/Shop/CheckoutController.php:43
 * @route '/plans'
 */
plans.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: plans.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\CheckoutController::plans
 * @see app/Http/Controllers/Shop/CheckoutController.php:43
 * @route '/plans'
 */
    const plansForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: plans.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\CheckoutController::plans
 * @see app/Http/Controllers/Shop/CheckoutController.php:43
 * @route '/plans'
 */
        plansForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: plans.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\CheckoutController::plans
 * @see app/Http/Controllers/Shop/CheckoutController.php:43
 * @route '/plans'
 */
        plansForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: plans.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    plans.form = plansForm
/**
* @see \App\Http\Controllers\Shop\CheckoutController::confirm
 * @see app/Http/Controllers/Shop/CheckoutController.php:80
 * @route '/checkout/confirm'
 */
export const confirm = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirm.url(options),
    method: 'get',
})

confirm.definition = {
    methods: ["get","head"],
    url: '/checkout/confirm',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\CheckoutController::confirm
 * @see app/Http/Controllers/Shop/CheckoutController.php:80
 * @route '/checkout/confirm'
 */
confirm.url = (options?: RouteQueryOptions) => {
    return confirm.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::confirm
 * @see app/Http/Controllers/Shop/CheckoutController.php:80
 * @route '/checkout/confirm'
 */
confirm.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: confirm.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\CheckoutController::confirm
 * @see app/Http/Controllers/Shop/CheckoutController.php:80
 * @route '/checkout/confirm'
 */
confirm.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: confirm.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\CheckoutController::confirm
 * @see app/Http/Controllers/Shop/CheckoutController.php:80
 * @route '/checkout/confirm'
 */
    const confirmForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: confirm.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\CheckoutController::confirm
 * @see app/Http/Controllers/Shop/CheckoutController.php:80
 * @route '/checkout/confirm'
 */
        confirmForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: confirm.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\CheckoutController::confirm
 * @see app/Http/Controllers/Shop/CheckoutController.php:80
 * @route '/checkout/confirm'
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
* @see \App\Http\Controllers\Shop\CheckoutController::checkout
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/checkout/process'
 */
const checkout9402fd173d227135a00ac448b707d23a = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkout9402fd173d227135a00ac448b707d23a.url(options),
    method: 'post',
})

checkout9402fd173d227135a00ac448b707d23a.definition = {
    methods: ["post"],
    url: '/checkout/process',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\CheckoutController::checkout
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/checkout/process'
 */
checkout9402fd173d227135a00ac448b707d23a.url = (options?: RouteQueryOptions) => {
    return checkout9402fd173d227135a00ac448b707d23a.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::checkout
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/checkout/process'
 */
checkout9402fd173d227135a00ac448b707d23a.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkout9402fd173d227135a00ac448b707d23a.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\CheckoutController::checkout
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/checkout/process'
 */
    const checkout9402fd173d227135a00ac448b707d23aForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: checkout9402fd173d227135a00ac448b707d23a.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\CheckoutController::checkout
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/checkout/process'
 */
        checkout9402fd173d227135a00ac448b707d23aForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: checkout9402fd173d227135a00ac448b707d23a.url(options),
            method: 'post',
        })
    
    checkout9402fd173d227135a00ac448b707d23a.form = checkout9402fd173d227135a00ac448b707d23aForm
    /**
* @see \App\Http\Controllers\Shop\CheckoutController::checkout
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/shop/checkout'
 */
const checkoutba17b2ef70f9aafdacf7d90188f0227d = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkoutba17b2ef70f9aafdacf7d90188f0227d.url(options),
    method: 'post',
})

checkoutba17b2ef70f9aafdacf7d90188f0227d.definition = {
    methods: ["post"],
    url: '/shop/checkout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\CheckoutController::checkout
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/shop/checkout'
 */
checkoutba17b2ef70f9aafdacf7d90188f0227d.url = (options?: RouteQueryOptions) => {
    return checkoutba17b2ef70f9aafdacf7d90188f0227d.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::checkout
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/shop/checkout'
 */
checkoutba17b2ef70f9aafdacf7d90188f0227d.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkoutba17b2ef70f9aafdacf7d90188f0227d.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\CheckoutController::checkout
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/shop/checkout'
 */
    const checkoutba17b2ef70f9aafdacf7d90188f0227dForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: checkoutba17b2ef70f9aafdacf7d90188f0227d.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\CheckoutController::checkout
 * @see app/Http/Controllers/Shop/CheckoutController.php:99
 * @route '/shop/checkout'
 */
        checkoutba17b2ef70f9aafdacf7d90188f0227dForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: checkoutba17b2ef70f9aafdacf7d90188f0227d.url(options),
            method: 'post',
        })
    
    checkoutba17b2ef70f9aafdacf7d90188f0227d.form = checkoutba17b2ef70f9aafdacf7d90188f0227dForm

export const checkout = {
    '/checkout/process': checkout9402fd173d227135a00ac448b707d23a,
    '/shop/checkout': checkoutba17b2ef70f9aafdacf7d90188f0227d,
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::pay
 * @see app/Http/Controllers/Shop/CheckoutController.php:141
 * @route '/shop/payment/pay'
 */
export const pay = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: pay.url(options),
    method: 'post',
})

pay.definition = {
    methods: ["post"],
    url: '/shop/payment/pay',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Shop\CheckoutController::pay
 * @see app/Http/Controllers/Shop/CheckoutController.php:141
 * @route '/shop/payment/pay'
 */
pay.url = (options?: RouteQueryOptions) => {
    return pay.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::pay
 * @see app/Http/Controllers/Shop/CheckoutController.php:141
 * @route '/shop/payment/pay'
 */
pay.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: pay.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Shop\CheckoutController::pay
 * @see app/Http/Controllers/Shop/CheckoutController.php:141
 * @route '/shop/payment/pay'
 */
    const payForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: pay.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Shop\CheckoutController::pay
 * @see app/Http/Controllers/Shop/CheckoutController.php:141
 * @route '/shop/payment/pay'
 */
        payForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: pay.url(options),
            method: 'post',
        })
    
    pay.form = payForm
/**
* @see \App\Http\Controllers\Shop\CheckoutController::success
 * @see app/Http/Controllers/Shop/CheckoutController.php:165
 * @route '/shop/payment/success/{order}'
 */
export const success = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: success.url(args, options),
    method: 'get',
})

success.definition = {
    methods: ["get","head"],
    url: '/shop/payment/success/{order}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\CheckoutController::success
 * @see app/Http/Controllers/Shop/CheckoutController.php:165
 * @route '/shop/payment/success/{order}'
 */
success.url = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { order: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { order: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    order: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        order: typeof args.order === 'object'
                ? args.order.public_id
                : args.order,
                }

    return success.definition.url
            .replace('{order}', parsedArgs.order.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::success
 * @see app/Http/Controllers/Shop/CheckoutController.php:165
 * @route '/shop/payment/success/{order}'
 */
success.get = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: success.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\CheckoutController::success
 * @see app/Http/Controllers/Shop/CheckoutController.php:165
 * @route '/shop/payment/success/{order}'
 */
success.head = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: success.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\CheckoutController::success
 * @see app/Http/Controllers/Shop/CheckoutController.php:165
 * @route '/shop/payment/success/{order}'
 */
    const successForm = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: success.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\CheckoutController::success
 * @see app/Http/Controllers/Shop/CheckoutController.php:165
 * @route '/shop/payment/success/{order}'
 */
        successForm.get = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: success.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\CheckoutController::success
 * @see app/Http/Controllers/Shop/CheckoutController.php:165
 * @route '/shop/payment/success/{order}'
 */
        successForm.head = (args: { order: string | { public_id: string } } | [order: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: success.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    success.form = successForm
/**
* @see \App\Http\Controllers\Shop\CheckoutController::cancel
 * @see app/Http/Controllers/Shop/CheckoutController.php:176
 * @route '/shop/payment/cancel'
 */
export const cancel = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cancel.url(options),
    method: 'get',
})

cancel.definition = {
    methods: ["get","head"],
    url: '/shop/payment/cancel',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\CheckoutController::cancel
 * @see app/Http/Controllers/Shop/CheckoutController.php:176
 * @route '/shop/payment/cancel'
 */
cancel.url = (options?: RouteQueryOptions) => {
    return cancel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\CheckoutController::cancel
 * @see app/Http/Controllers/Shop/CheckoutController.php:176
 * @route '/shop/payment/cancel'
 */
cancel.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: cancel.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\CheckoutController::cancel
 * @see app/Http/Controllers/Shop/CheckoutController.php:176
 * @route '/shop/payment/cancel'
 */
cancel.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: cancel.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\CheckoutController::cancel
 * @see app/Http/Controllers/Shop/CheckoutController.php:176
 * @route '/shop/payment/cancel'
 */
    const cancelForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: cancel.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\CheckoutController::cancel
 * @see app/Http/Controllers/Shop/CheckoutController.php:176
 * @route '/shop/payment/cancel'
 */
        cancelForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: cancel.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\CheckoutController::cancel
 * @see app/Http/Controllers/Shop/CheckoutController.php:176
 * @route '/shop/payment/cancel'
 */
        cancelForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: cancel.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    cancel.form = cancelForm
const CheckoutController = { show, select, plans, confirm, checkout, pay, success, cancel }

export default CheckoutController