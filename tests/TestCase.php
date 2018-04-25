<?php

namespace Tests;

use Mockery;
use SoapBox\Timer\Timers;
use PHPUnit\Framework\TestCase as Base;

abstract class TestCase extends Base
{
    protected function setUp()
    {
        parent::setUp();
        Timers::enable();
    }

    protected function tearDown()
    {
        Timers::flush();
        Mockery::close();
        parent::tearDown();
    }
}
