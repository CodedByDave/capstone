import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockIn
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:17
 * @route '/staff/attendance/clock-in'
 */
export const clockIn = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clockIn.url(options),
    method: 'post',
})

clockIn.definition = {
    methods: ["post"],
    url: '/staff/attendance/clock-in',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockIn
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:17
 * @route '/staff/attendance/clock-in'
 */
clockIn.url = (options?: RouteQueryOptions) => {
    return clockIn.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockIn
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:17
 * @route '/staff/attendance/clock-in'
 */
clockIn.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clockIn.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockIn
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:17
 * @route '/staff/attendance/clock-in'
 */
    const clockInForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: clockIn.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockIn
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:17
 * @route '/staff/attendance/clock-in'
 */
        clockInForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: clockIn.url(options),
            method: 'post',
        })
    
    clockIn.form = clockInForm
/**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockOut
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:53
 * @route '/staff/attendance/clock-out'
 */
export const clockOut = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clockOut.url(options),
    method: 'post',
})

clockOut.definition = {
    methods: ["post"],
    url: '/staff/attendance/clock-out',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockOut
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:53
 * @route '/staff/attendance/clock-out'
 */
clockOut.url = (options?: RouteQueryOptions) => {
    return clockOut.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockOut
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:53
 * @route '/staff/attendance/clock-out'
 */
clockOut.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: clockOut.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockOut
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:53
 * @route '/staff/attendance/clock-out'
 */
    const clockOutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: clockOut.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Staff\SelfAttendanceController::clockOut
 * @see app/Http/Controllers/Staff/SelfAttendanceController.php:53
 * @route '/staff/attendance/clock-out'
 */
        clockOutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: clockOut.url(options),
            method: 'post',
        })
    
    clockOut.form = clockOutForm
const SelfAttendanceController = { clockIn, clockOut }

export default SelfAttendanceController