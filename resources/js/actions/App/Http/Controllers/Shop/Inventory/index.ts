import InventoryCategoryController from './InventoryCategoryController'
import LowStockAlertController from './LowStockAlertController'
import InventoryController from './InventoryController'
import SupplierController from './SupplierController'
const Inventory = {
    InventoryCategoryController: Object.assign(InventoryCategoryController, InventoryCategoryController),
LowStockAlertController: Object.assign(LowStockAlertController, LowStockAlertController),
InventoryController: Object.assign(InventoryController, InventoryController),
SupplierController: Object.assign(SupplierController, SupplierController),
}

export default Inventory