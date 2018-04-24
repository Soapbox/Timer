<?php

namespace Tests;

use SoapBox\Timer\Timer;
use SoapBox\Timer\Timers;
use SoapBox\Timer\TimerNotInitializedException;
use SoapBox\Timer\DuplicateTimerException;

class TimersTests extends TestCase
{
    /**
     * @test
     */
    public function getting_a_timer_throws_a_timer_not_initialized_exception_if_the_timer_was_not_initialized()
    {
        $this->expectException(TimerNotInitializedException::class);
        Timers::get('timer');
    }

    /**
     * @test
     */
    public function getting_a_timer_that_has_been_initialized_returns_the_timer_instance()
    {
        $timer = Timer::start('timer');

        $this->assertSame($timer, Timers::get('timer'));
        $this->assertSame($timer, Timers::get('timer'));
    }

    /**
     * @test
     */
    public function registering_a_previously_registered_timer_throws_a_duplicate_timer_exception()
    {
        $this->expectException(DuplicateTimerException::class);

        $timer = Timer::start('timer');

        Timers::register($timer);
    }

    /**
     * @test
     */
    public function after_flushing_the_timers_you_can_register_a_previously_registered_timer()
    {
        $timer = Timer::start('timer');

        Timers::flush();

        try {
            Timers::get('timer');
        } catch (TimerNotInitializedException $exception) {
            Timers::register($timer);
            $this->assertSame($timer, Timers::get($timer->getName()));
            return;
        }

        $this->fail();
    }

    /**
     * @test
     */
    public function flushing_the_timers_returns_all_of_the_previously_registered_timers()
    {
        $timer = Timer::start('timer');

        $timers = Timers::flush();

        $this->assertCount(1, $timers);
        $this->assertSame($timer, $timers->get($timer->getName()));
    }
}
