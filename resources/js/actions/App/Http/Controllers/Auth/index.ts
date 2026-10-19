import ForgotPasswordController from './ForgotPasswordController'
import ResetPasswordController from './ResetPasswordController'
import UserRegisterController from './UserRegisterController'
import AuthViewController from './AuthViewController'
import GoogleAuthController from './GoogleAuthController'
import AuthController from './AuthController'
import ShopRegisterController from './ShopRegisterController'
import OtpVerificationController from './OtpVerificationController'
const Auth = {
    ForgotPasswordController: Object.assign(ForgotPasswordController, ForgotPasswordController),
ResetPasswordController: Object.assign(ResetPasswordController, ResetPasswordController),
UserRegisterController: Object.assign(UserRegisterController, UserRegisterController),
AuthViewController: Object.assign(AuthViewController, AuthViewController),
GoogleAuthController: Object.assign(GoogleAuthController, GoogleAuthController),
AuthController: Object.assign(AuthController, AuthController),
ShopRegisterController: Object.assign(ShopRegisterController, ShopRegisterController),
OtpVerificationController: Object.assign(OtpVerificationController, OtpVerificationController),
}

export default Auth