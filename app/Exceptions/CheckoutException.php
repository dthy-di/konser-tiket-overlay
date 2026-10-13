<?php

namespace App\Exceptions;

use RuntimeException;

/** Kesalahan yang aman ditampilkan ke pembeli (stok habis, melebihi batas, dll). */
class CheckoutException extends RuntimeException
{
}
