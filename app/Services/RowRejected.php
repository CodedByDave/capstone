<?php

namespace App\Services;

use RuntimeException;

/** A CSV row that a service refused; the message already names the row. */
class RowRejected extends RuntimeException {}
