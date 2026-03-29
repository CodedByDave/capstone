<?php

use App\Http\Controllers\Shop\CheckoutController;

Route::post('/paymongo/checkout', [CheckoutController::class, 'create']);
