import UserDashboardController from './UserDashboardController'
import UserOrderController from './UserOrderController'
import UserOrderRatingController from './UserOrderRatingController'
import UserPaymentController from './UserPaymentController'
import UserNotificationController from './UserNotificationController'
const User = {
    UserDashboardController: Object.assign(UserDashboardController, UserDashboardController),
UserOrderController: Object.assign(UserOrderController, UserOrderController),
UserOrderRatingController: Object.assign(UserOrderRatingController, UserOrderRatingController),
UserPaymentController: Object.assign(UserPaymentController, UserPaymentController),
UserNotificationController: Object.assign(UserNotificationController, UserNotificationController),
}

export default User