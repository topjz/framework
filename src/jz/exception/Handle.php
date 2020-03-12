<?php


namespace jz\exception;

use Exception;
use jz\App;

class Handle
{
    public function __construct(App $app)
    {
        $this->app = $app;
    }
}