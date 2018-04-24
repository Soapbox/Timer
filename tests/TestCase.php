<?php

namespace Tests;

use SoapBox\Timer\Timers;
use PHPUnit\Framework\TestCase as Base;

abstract class TestCase extends Base
{
    protected function setUp()
    {
        parent::setUp();
        Timers::flush();
    }
}
