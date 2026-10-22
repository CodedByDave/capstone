<?php

namespace App\Exceptions;

use RuntimeException;

/** A customer rating cannot be saved, with a message safe to show them. */
class OrderRatingException extends RuntimeException {}
