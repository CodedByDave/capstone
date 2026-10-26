import ActivityLogsController from './ActivityLogsController'
import EmployeeController from './EmployeeController'
import ScheduleController from './ScheduleController'
import AttendanceController from './AttendanceController'
import PayrollController from './PayrollController'
import PermissionController from './PermissionController'
const Employee = {
    ActivityLogsController: Object.assign(ActivityLogsController, ActivityLogsController),
EmployeeController: Object.assign(EmployeeController, EmployeeController),
ScheduleController: Object.assign(ScheduleController, ScheduleController),
AttendanceController: Object.assign(AttendanceController, AttendanceController),
PayrollController: Object.assign(PayrollController, PayrollController),
PermissionController: Object.assign(PermissionController, PermissionController),
}

export default Employee