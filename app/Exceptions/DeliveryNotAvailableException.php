<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * The shop cannot deliver to the requested destination (no shop location,
 * delivery disabled, or the destination is outside the delivery radius).
 */
class DeliveryNotAvailableException extends RuntimeException {}
