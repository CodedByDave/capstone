import ShopOrderController from './ShopOrderController'
import ShopServiceController from './ShopServiceController'
import PromotionController from './PromotionController'
const Operations = {
    ShopOrderController: Object.assign(ShopOrderController, ShopOrderController),
ShopServiceController: Object.assign(ShopServiceController, ShopServiceController),
PromotionController: Object.assign(PromotionController, PromotionController),
}

export default Operations