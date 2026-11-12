import UserDashboardController from './UserDashboardController'
import UserOrderController from './UserOrderController'
import UserProductOrderController from './UserProductOrderController'
import OrderTrackingController from './OrderTrackingController'
import UserOrderComplaintController from './UserOrderComplaintController'
import UserOrderRatingController from './UserOrderRatingController'
import UserOrderWeightController from './UserOrderWeightController'
import UserPaymentController from './UserPaymentController'
import UserNotificationController from './UserNotificationController'
const User = {
    UserDashboardController: Object.assign(UserDashboardController, UserDashboardController),
UserOrderController: Object.assign(UserOrderController, UserOrderController),
UserProductOrderController: Object.assign(UserProductOrderController, UserProductOrderController),
OrderTrackingController: Object.assign(OrderTrackingController, OrderTrackingController),
UserOrderComplaintController: Object.assign(UserOrderComplaintController, UserOrderComplaintController),
UserOrderRatingController: Object.assign(UserOrderRatingController, UserOrderRatingController),
UserOrderWeightController: Object.assign(UserOrderWeightController, UserOrderWeightController),
UserPaymentController: Object.assign(UserPaymentController, UserPaymentController),
UserNotificationController: Object.assign(UserNotificationController, UserNotificationController),
}

export default User