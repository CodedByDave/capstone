<?php

namespace App\Exceptions;

use RuntimeException;

/** The map provider is not configured or could not be reached. */
class MapboxUnavailableException extends RuntimeException {}
