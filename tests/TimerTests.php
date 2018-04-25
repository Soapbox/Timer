<?php

namespace Tests;

use RuntimeException;
use SoapBox\Timer\Timer;

class TimerTests extends TestCase
{
    /**
     * @test
     */
    public function starting_a_currently_running_timer_throws_a_runtime_exception()
    {
        $this->expectException(RuntimeException::class);

        Timer::start('timer');
        Timer::start('timer');
    }

    /**
     * @test
     */
    public function starting_a_timer_that_was_previously_stopped_throws_a_runtime_exception()
    {
        $this->expectException(RuntimeException::class);

        Timer::start('timer')->stop();
        Timer::start('timer');
    }

    /**
     * @test
     */
    public function starting_a_timer_returns_an_instance_of_a_running_timer()
    {
        $timer = Timer::start('timer');

        $this->assertInstanceOf(Timer::class, $timer);
        $this->assertTrue($timer->isRunning());
    }

    /**
     * @test
     */
    public function a_running_timer_can_be_stopped()
    {
        $timer = Timer::start('timer');

        $timer->stop();
        $this->assertFalse($timer->isRunning());
    }

    /**
     * @test
     */
    public function getting_the_elapsed_time_for_a_timer_returns_a_float()
    {
        $timer = Timer::start('timer');

        $this->assertTrue(is_float($timer->getElapsedTime()));
    }

    /**
     * @test
     */
    public function getting_the_elapsed_time_returns_a_positive_float()
    {
        $timer = Timer::start('timer');

        $this->assertTrue($timer->getElapsedTime() > 0);
    }

    /**
     * @test
     */
    public function calling_stop_multiple_times_does_not_change_the_elapsed_time()
    {
        $timer = Timer::start('timer');

        $timer->stop();
        $time = $timer->getElapsedTime();

        $this->assertSame($time, $timer->getElapsedTime());

        $timer->stop();
        $this->assertSame($time, $timer->getElapsedTime());
    }
}
