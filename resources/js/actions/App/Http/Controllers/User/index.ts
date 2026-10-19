import UserDashboardController from './UserDashboardController'
import UserOrderController from './UserOrderController'
import UserPaymentController from './UserPaymentController'
import UserNotificationController from './UserNotificationController'
const User = {
    UserDashboardController: Object.assign(UserDashboardController, UserDashboardController),
UserOrderController: Object.assign(UserOrderController, UserOrderController),
UserPaymentController: Object.assign(UserPaymentController, UserPaymentController),
UserNotificationController: Object.assign(UserNotificationController, UserNotificationController),
}

export default User