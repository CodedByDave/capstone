import ShopOrderController from './ShopOrderController'
import OrderWeightProofController from './OrderWeightProofController'
import OrderRatingController from './OrderRatingController'
import OrderAutomationController from './OrderAutomationController'
import OrderComplaintController from './OrderComplaintController'
import ShopServiceController from './ShopServiceController'
import ServiceCategoryController from './ServiceCategoryController'
import PromotionController from './PromotionController'
const Operations = {
    ShopOrderController: Object.assign(ShopOrderController, ShopOrderController),
OrderWeightProofController: Object.assign(OrderWeightProofController, OrderWeightProofController),
OrderRatingController: Object.assign(OrderRatingController, OrderRatingController),
OrderAutomationController: Object.assign(OrderAutomationController, OrderAutomationController),
OrderComplaintController: Object.assign(OrderComplaintController, OrderComplaintController),
ShopServiceController: Object.assign(ShopServiceController, ShopServiceController),
ServiceCategoryController: Object.assign(ServiceCategoryController, ServiceCategoryController),
PromotionController: Object.assign(PromotionController, PromotionController),
}

export default Operations