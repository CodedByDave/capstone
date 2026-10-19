import FinanceDashboardController from './FinanceDashboardController'
import ExpenseController from './ExpenseController'
const Finance = {
    FinanceDashboardController: Object.assign(FinanceDashboardController, FinanceDashboardController),
ExpenseController: Object.assign(ExpenseController, ExpenseController),
}

export default Finance