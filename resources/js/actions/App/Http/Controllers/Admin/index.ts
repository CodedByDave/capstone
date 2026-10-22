import AdminDashboardController from './AdminDashboardController'
import PlatformRoleController from './PlatformRoleController'
import ShopController from './ShopController'
import UserController from './UserController'
import LoginLogController from './LoginLogController'
import OrderController from './OrderController'
import AnalyticsController from './AnalyticsController'
import PlatformRatingController from './PlatformRatingController'
import IssueReportController from './IssueReportController'
const Admin = {
    AdminDashboardController: Object.assign(AdminDashboardController, AdminDashboardController),
PlatformRoleController: Object.assign(PlatformRoleController, PlatformRoleController),
ShopController: Object.assign(ShopController, ShopController),
UserController: Object.assign(UserController, UserController),
LoginLogController: Object.assign(LoginLogController, LoginLogController),
OrderController: Object.assign(OrderController, OrderController),
AnalyticsController: Object.assign(AnalyticsController, AnalyticsController),
PlatformRatingController: Object.assign(PlatformRatingController, PlatformRatingController),
IssueReportController: Object.assign(IssueReportController, IssueReportController),
}

export default Admin