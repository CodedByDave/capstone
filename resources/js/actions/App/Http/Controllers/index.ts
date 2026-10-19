import Auth from './Auth'
import DriverPageController from './DriverPageController'
import Shop from './Shop'
import BusinessAgreementSignatureController from './BusinessAgreementSignatureController'
import Admin from './Admin'
import Staff from './Staff'
import User from './User'
import Settings from './Settings'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
DriverPageController: Object.assign(DriverPageController, DriverPageController),
Shop: Object.assign(Shop, Shop),
BusinessAgreementSignatureController: Object.assign(BusinessAgreementSignatureController, BusinessAgreementSignatureController),
Admin: Object.assign(Admin, Admin),
Staff: Object.assign(Staff, Staff),
User: Object.assign(User, User),
Settings: Object.assign(Settings, Settings),
}

export default Controllers