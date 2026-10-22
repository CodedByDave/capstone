<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A rider location update was sent for a delivery that is not on the road.
 */
class RiderLocationException extends RuntimeException {}
