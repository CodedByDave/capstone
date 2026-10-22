import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import expenses from './expenses'
import categories from './categories'
/**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::dashboard
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:35
 * @route '/shop/finance'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/shop/finance',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::dashboard
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:35
 * @route '/shop/finance'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::dashboard
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:35
 * @route '/shop/finance'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::dashboard
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:35
 * @route '/shop/finance'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::dashboard
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:35
 * @route '/shop/finance'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::dashboard
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:35
 * @route '/shop/finance'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::dashboard
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:35
 * @route '/shop/finance'
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
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::income
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:44
 * @route '/shop/finance/income'
 */
export const income = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: income.url(options),
    method: 'get',
})

income.definition = {
    methods: ["get","head"],
    url: '/shop/finance/income',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::income
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:44
 * @route '/shop/finance/income'
 */
income.url = (options?: RouteQueryOptions) => {
    return income.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::income
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:44
 * @route '/shop/finance/income'
 */
income.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: income.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::income
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:44
 * @route '/shop/finance/income'
 */
income.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: income.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::income
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:44
 * @route '/shop/finance/income'
 */
    const incomeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: income.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::income
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:44
 * @route '/shop/finance/income'
 */
        incomeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: income.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::income
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:44
 * @route '/shop/finance/income'
 */
        incomeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: income.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    income.form = incomeForm
/**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::transactions
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:58
 * @route '/shop/finance/transactions'
 */
export const transactions = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: transactions.url(options),
    method: 'get',
})

transactions.definition = {
    methods: ["get","head"],
    url: '/shop/finance/transactions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::transactions
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:58
 * @route '/shop/finance/transactions'
 */
transactions.url = (options?: RouteQueryOptions) => {
    return transactions.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::transactions
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:58
 * @route '/shop/finance/transactions'
 */
transactions.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: transactions.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::transactions
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:58
 * @route '/shop/finance/transactions'
 */
transactions.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: transactions.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::transactions
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:58
 * @route '/shop/finance/transactions'
 */
    const transactionsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: transactions.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::transactions
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:58
 * @route '/shop/finance/transactions'
 */
        transactionsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: transactions.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Shop\Finance\FinanceDashboardController::transactions
 * @see app/Http/Controllers/Shop/Finance/FinanceDashboardController.php:58
 * @route '/shop/finance/transactions'
 */
        transactionsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: transactions.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    transactions.form = transactionsForm
const finance = {
    dashboard: Object.assign(dashboard, dashboard),
income: Object.assign(income, income),
transactions: Object.assign(transactions, transactions),
expenses: Object.assign(expenses, expenses),
categories: Object.assign(categories, categories),
}

export default finance