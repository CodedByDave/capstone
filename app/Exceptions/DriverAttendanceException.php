<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A driver tried to clock in or out when the schedule or today's record does not allow it.
 */
class DriverAttendanceException extends RuntimeException {}
