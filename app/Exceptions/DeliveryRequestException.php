<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * The order is not eligible for a delivery request (not completed, unpaid, or already requested).
 */
class DeliveryRequestException extends RuntimeException {}
