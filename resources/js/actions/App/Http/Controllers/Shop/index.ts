import CheckoutController from './CheckoutController'
import BusinessAgreementPdfController from './BusinessAgreementPdfController'
import TrialController from './TrialController'
import BusinessAgreementController from './BusinessAgreementController'
import ShopNotificationController from './ShopNotificationController'
import PaymentHistoryController from './PaymentHistoryController'
import ShopSetupController from './ShopSetupController'
import ShopDashboardController from './ShopDashboardController'
import ShopDataController from './ShopDataController'
import Employee from './Employee'
import CustomerQrController from './CustomerQrController'
import Inventory from './Inventory'
import Operations from './Operations'
import PlatformRatingController from './PlatformRatingController'
import ReportsController from './ReportsController'
import ShopDataExportController from './ShopDataExportController'
import Finance from './Finance'
import PaymentSettingsController from './PaymentSettingsController'
import UpgradeController from './UpgradeController'
import ShopSettingsController from './ShopSettingsController'
import LogisticsController from './LogisticsController'
import DeliveryProofController from './DeliveryProofController'
const Shop = {
    CheckoutController: Object.assign(CheckoutController, CheckoutController),
BusinessAgreementPdfController: Object.assign(BusinessAgreementPdfController, BusinessAgreementPdfController),
TrialController: Object.assign(TrialController, TrialController),
BusinessAgreementController: Object.assign(BusinessAgreementController, BusinessAgreementController),
ShopNotificationController: Object.assign(ShopNotificationController, ShopNotificationController),
PaymentHistoryController: Object.assign(PaymentHistoryController, PaymentHistoryController),
ShopSetupController: Object.assign(ShopSetupController, ShopSetupController),
ShopDashboardController: Object.assign(ShopDashboardController, ShopDashboardController),
ShopDataController: Object.assign(ShopDataController, ShopDataController),
Employee: Object.assign(Employee, Employee),
CustomerQrController: Object.assign(CustomerQrController, CustomerQrController),
Inventory: Object.assign(Inventory, Inventory),
Operations: Object.assign(Operations, Operations),
PlatformRatingController: Object.assign(PlatformRatingController, PlatformRatingController),
ReportsController: Object.assign(ReportsController, ReportsController),
ShopDataExportController: Object.assign(ShopDataExportController, ShopDataExportController),
Finance: Object.assign(Finance, Finance),
PaymentSettingsController: Object.assign(PaymentSettingsController, PaymentSettingsController),
UpgradeController: Object.assign(UpgradeController, UpgradeController),
ShopSettingsController: Object.assign(ShopSettingsController, ShopSettingsController),
LogisticsController: Object.assign(LogisticsController, LogisticsController),
DeliveryProofController: Object.assign(DeliveryProofController, DeliveryProofController),
}

export default Shop