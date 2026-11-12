<?php

namespace App\Exceptions;

use RuntimeException;

/** The shop's SMS gateway is not set up, could not be reached, or refused the text. */
class SmsGatewayException extends RuntimeException {}
