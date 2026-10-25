<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A driver tried something a job does not allow, such as moving a run backwards.
 */
class DriverJobException extends RuntimeException {}
