<?php

namespace App\Exceptions;

use RuntimeException;

/** A complaint cannot be filed or answered because of a business rule; the message is user-facing. */
class OrderComplaintException extends RuntimeException {}
