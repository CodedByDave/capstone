<?php

namespace App\Exceptions;

use RuntimeException;

/** Mapbox could not find a drivable route between the requested points. */
class NoRouteFoundException extends RuntimeException {}
