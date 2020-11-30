<?php

namespace App\Exceptions;

use Exception;

class NoProductsException extends Exception
{
 

    public function report()
    {
        dump('Have no products from ' . $this->getMessage());
        die();
    }
}
