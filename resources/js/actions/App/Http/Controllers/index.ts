import Auth from './Auth'
import Driver from './Driver'
import LandingController from './LandingController'
import Shop from './Shop'
import BusinessAgreementSignatureController from './BusinessAgreementSignatureController'
import Admin from './Admin'
import Staff from './Staff'
import User from './User'
import Settings from './Settings'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
Driver: Object.assign(Driver, Driver),
LandingController: Object.assign(LandingController, LandingController),
Shop: Object.assign(Shop, Shop),
BusinessAgreementSignatureController: Object.assign(BusinessAgreementSignatureController, BusinessAgreementSignatureController),
Admin: Object.assign(Admin, Admin),
Staff: Object.assign(Staff, Staff),
User: Object.assign(User, User),
Settings: Object.assign(Settings, Settings),
}

export default Controllers