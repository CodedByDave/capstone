import CheckoutController from './CheckoutController'
import TrialController from './TrialController'
import BusinessAgreementController from './BusinessAgreementController'
import ShopNotificationController from './ShopNotificationController'
import PaymentHistoryController from './PaymentHistoryController'
import ShopSetupController from './ShopSetupController'
import ShopDashboardController from './ShopDashboardController'
import ShopDataController from './ShopDataController'
import Employee from './Employee'
import Inventory from './Inventory'
import Operations from './Operations'
import PlatformRatingController from './PlatformRatingController'
import ReportsController from './ReportsController'
import Finance from './Finance'
import PaymentQrController from './PaymentQrController'
import UpgradeController from './UpgradeController'
import ShopSettingsController from './ShopSettingsController'
import LogisticsController from './LogisticsController'
import DeliveryProofController from './DeliveryProofController'
const Shop = {
    CheckoutController: Object.assign(CheckoutController, CheckoutController),
TrialController: Object.assign(TrialController, TrialController),
BusinessAgreementController: Object.assign(BusinessAgreementController, BusinessAgreementController),
ShopNotificationController: Object.assign(ShopNotificationController, ShopNotificationController),
PaymentHistoryController: Object.assign(PaymentHistoryController, PaymentHistoryController),
ShopSetupController: Object.assign(ShopSetupController, ShopSetupController),
ShopDashboardController: Object.assign(ShopDashboardController, ShopDashboardController),
ShopDataController: Object.assign(ShopDataController, ShopDataController),
Employee: Object.assign(Employee, Employee),
Inventory: Object.assign(Inventory, Inventory),
Operations: Object.assign(Operations, Operations),
PlatformRatingController: Object.assign(PlatformRatingController, PlatformRatingController),
ReportsController: Object.assign(ReportsController, ReportsController),
Finance: Object.assign(Finance, Finance),
PaymentQrController: Object.assign(PaymentQrController, PaymentQrController),
UpgradeController: Object.assign(UpgradeController, UpgradeController),
ShopSettingsController: Object.assign(ShopSettingsController, ShopSettingsController),
LogisticsController: Object.assign(LogisticsController, LogisticsController),
DeliveryProofController: Object.assign(DeliveryProofController, DeliveryProofController),
}

export default Shop