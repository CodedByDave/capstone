import ShopOrderController from './ShopOrderController'
import OrderRatingController from './OrderRatingController'
import ShopServiceController from './ShopServiceController'
import PromotionController from './PromotionController'
const Operations = {
    ShopOrderController: Object.assign(ShopOrderController, ShopOrderController),
OrderRatingController: Object.assign(OrderRatingController, OrderRatingController),
ShopServiceController: Object.assign(ShopServiceController, ShopServiceController),
PromotionController: Object.assign(PromotionController, PromotionController),
}

export default Operations