import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
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
* @see \App\Http\Controllers\Admin\IssueReportController::exportCsv
 * @see app/Http/Controllers/Admin/IssueReportController.php:32
 * @route '/admin/issue-reports/export'
 */
export const exportCsv = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportCsv.url(options),
    method: 'get',
})

exportCsv.definition = {
    methods: ["get","head"],
    url: '/admin/issue-reports/export',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\IssueReportController::exportCsv
 * @see app/Http/Controllers/Admin/IssueReportController.php:32
 * @route '/admin/issue-reports/export'
 */
exportCsv.url = (options?: RouteQueryOptions) => {
    return exportCsv.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\IssueReportController::exportCsv
 * @see app/Http/Controllers/Admin/IssueReportController.php:32
 * @route '/admin/issue-reports/export'
 */
exportCsv.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: exportCsv.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\IssueReportController::exportCsv
 * @see app/Http/Controllers/Admin/IssueReportController.php:32
 * @route '/admin/issue-reports/export'
 */
exportCsv.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: exportCsv.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\IssueReportController::exportCsv
 * @see app/Http/Controllers/Admin/IssueReportController.php:32
 * @route '/admin/issue-reports/export'
 */
    const exportCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: exportCsv.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\IssueReportController::exportCsv
 * @see app/Http/Controllers/Admin/IssueReportController.php:32
 * @route '/admin/issue-reports/export'
 */
        exportCsvForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportCsv.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\IssueReportController::exportCsv
 * @see app/Http/Controllers/Admin/IssueReportController.php:32
 * @route '/admin/issue-reports/export'
 */
        exportCsvForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: exportCsv.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    exportCsv.form = exportCsvForm
/**
* @see \App\Http\Controllers\Admin\IssueReportController::importCsv
 * @see app/Http/Controllers/Admin/IssueReportController.php:41
 * @route '/admin/issue-reports/import'
 */
export const importCsv = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importCsv.url(options),
    method: 'post',
})

importCsv.definition = {
    methods: ["post"],
    url: '/admin/issue-reports/import',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\IssueReportController::importCsv
 * @see app/Http/Controllers/Admin/IssueReportController.php:41
 * @route '/admin/issue-reports/import'
 */
importCsv.url = (options?: RouteQueryOptions) => {
    return importCsv.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\IssueReportController::importCsv
 * @see app/Http/Controllers/Admin/IssueReportController.php:41
 * @route '/admin/issue-reports/import'
 */
importCsv.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: importCsv.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\IssueReportController::importCsv
 * @see app/Http/Controllers/Admin/IssueReportController.php:41
 * @route '/admin/issue-reports/import'
 */
    const importCsvForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: importCsv.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\IssueReportController::importCsv
 * @see app/Http/Controllers/Admin/IssueReportController.php:41
 * @route '/admin/issue-reports/import'
 */
        importCsvForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: importCsv.url(options),
            method: 'post',
        })
    
    importCsv.form = importCsvForm
/**
* @see \App\Http\Controllers\Admin\IssueReportController::update
 * @see app/Http/Controllers/Admin/IssueReportController.php:55
 * @route '/admin/issue-reports/{issueReport}'
 */
export const update = (args: { issueReport: string | number | { public_id: string | number } } | [issueReport: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
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
update.url = (args: { issueReport: string | number | { public_id: string | number } } | [issueReport: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions) => {
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
update.patch = (args: { issueReport: string | number | { public_id: string | number } } | [issueReport: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\IssueReportController::update
 * @see app/Http/Controllers/Admin/IssueReportController.php:55
 * @route '/admin/issue-reports/{issueReport}'
 */
    const updateForm = (args: { issueReport: string | number | { public_id: string | number } } | [issueReport: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
        updateForm.patch = (args: { issueReport: string | number | { public_id: string | number } } | [issueReport: string | number | { public_id: string | number } ] | string | number | { public_id: string | number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
const IssueReportController = { index, exportCsv, importCsv, update }

export default IssueReportController