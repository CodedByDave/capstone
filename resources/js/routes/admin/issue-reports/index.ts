import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\IssueReportController::index
 * @see app/Http/Controllers/Admin/IssueReportController.php:24
 * @route '/admin/issue-reports'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/issue-reports',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\IssueReportController::index
 * @see app/Http/Controllers/Admin/IssueReportController.php:24
 * @route '/admin/issue-reports'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\IssueReportController::index
 * @see app/Http/Controllers/Admin/IssueReportController.php:24
 * @route '/admin/issue-reports'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\IssueReportController::index
 * @see app/Http/Controllers/Admin/IssueReportController.php:24
 * @route '/admin/issue-reports'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\IssueReportController::index
 * @see app/Http/Controllers/Admin/IssueReportController.php:24
 * @route '/admin/issue-reports'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\IssueReportController::index
 * @see app/Http/Controllers/Admin/IssueReportController.php:24
 * @route '/admin/issue-reports'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\IssueReportController::index
 * @see app/Http/Controllers/Admin/IssueReportController.php:24
 * @route '/admin/issue-reports'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\Admin\IssueReportController::exportMethod
 * @see app/Http/Controllers/Admin/IssueReportController.php:32
 * @route '/admin/issue-reports/export'
 */
export const exportMethod = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})

exportMethod.definition = {
    methods: ["get","head"],
    url: '/admin/issue-reports/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\IssueReportController::exportMethod
 * @see app/Http/Controllers/Admin/IssueReportController.php:32
 * @route '/admin/issue-reports/export'
 */
exportMethod.url = (options?: RouteQueryOptions) => {
    return exportMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\IssueReportController::exportMethod
 * @see app/Http/Controllers/Admin/IssueReportController.php:32
 * @route '/admin/issue-reports/export'
 */
exportMethod.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportMethod.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\IssueReportController::exportMethod
 * @see app/Http/Controllers/Admin/IssueReportController.php:32
 * @route '/admin/issue-reports/export'
 */
exportMethod.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportMethod.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\IssueReportController::exportMethod
 * @see app/Http/Controllers/Admin/IssueReportController.php:32
 * @route '/admin/issue-reports/export'
 */
    const exportMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportMethod.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\IssueReportController::exportMethod
 * @see app/Http/Controllers/Admin/IssueReportController.php:32
 * @route '/admin/issue-reports/export'
 */
        exportMethodForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\IssueReportController::exportMethod
 * @see app/Http/Controllers/Admin/IssueReportController.php:32
 * @route '/admin/issue-reports/export'
 */
        exportMethodForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportMethod.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportMethod.form = exportMethodForm
/**
* @see \App\Http\Controllers\Admin\IssueReportController::importMethod
 * @see app/Http/Controllers/Admin/IssueReportController.php:41
 * @route '/admin/issue-reports/import'
 */
export const importMethod = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

importMethod.definition = {
    methods: ["post"],
    url: '/admin/issue-reports/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\IssueReportController::importMethod
 * @see app/Http/Controllers/Admin/IssueReportController.php:41
 * @route '/admin/issue-reports/import'
 */
importMethod.url = (options?: RouteQueryOptions) => {
    return importMethod.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\IssueReportController::importMethod
 * @see app/Http/Controllers/Admin/IssueReportController.php:41
 * @route '/admin/issue-reports/import'
 */
importMethod.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importMethod.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\IssueReportController::importMethod
 * @see app/Http/Controllers/Admin/IssueReportController.php:41
 * @route '/admin/issue-reports/import'
 */
    const importMethodForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: importMethod.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\IssueReportController::importMethod
 * @see app/Http/Controllers/Admin/IssueReportController.php:41
 * @route '/admin/issue-reports/import'
 */
        importMethodForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: importMethod.url(options),
            method: 'post',
        })
    
    importMethod.form = importMethodForm
/**
* @see \App\Http\Controllers\Admin\IssueReportController::update
 * @see app/Http/Controllers/Admin/IssueReportController.php:55
 * @route '/admin/issue-reports/{issueReport}'
 */
export const update = (args: { issueReport: string | { public_id: string } } | [issueReport: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/admin/issue-reports/{issueReport}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\IssueReportController::update
 * @see app/Http/Controllers/Admin/IssueReportController.php:55
 * @route '/admin/issue-reports/{issueReport}'
 */
update.url = (args: { issueReport: string | { public_id: string } } | [issueReport: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { issueReport: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'public_id' in args) {
            args = { issueReport: args.public_id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    issueReport: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        issueReport: typeof args.issueReport === 'object'
                ? args.issueReport.public_id
                : args.issueReport,
                }

    return update.definition.url
            .replace('{issueReport}', parsedArgs.issueReport.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\IssueReportController::update
 * @see app/Http/Controllers/Admin/IssueReportController.php:55
 * @route '/admin/issue-reports/{issueReport}'
 */
update.patch = (args: { issueReport: string | { public_id: string } } | [issueReport: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\IssueReportController::update
 * @see app/Http/Controllers/Admin/IssueReportController.php:55
 * @route '/admin/issue-reports/{issueReport}'
 */
    const updateForm = (args: { issueReport: string | { public_id: string } } | [issueReport: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\IssueReportController::update
 * @see app/Http/Controllers/Admin/IssueReportController.php:55
 * @route '/admin/issue-reports/{issueReport}'
 */
        updateForm.patch = (args: { issueReport: string | { public_id: string } } | [issueReport: string | { public_id: string } ] | string | { public_id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
const issueReports = {
    index: Object.assign(index, index),
export: Object.assign(exportMethod, exportMethod),
import: Object.assign(importMethod, importMethod),
update: Object.assign(update, update),
}

export default issueReports