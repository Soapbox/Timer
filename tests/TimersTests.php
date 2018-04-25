<?php

namespace Tests;

use Mockery;
use SoapBox\Timer\Timer;
use SoapBox\Timer\Timers;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Collection;
use SoapBox\Timer\DuplicateTimerException;
use SoapBox\Timer\TimerNotInitializedException;

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

        $this->assertInstanceOf(Collection::class, $timers);
        $this->assertCount(1, $timers);
        $this->assertSame($timer, $timers->get($timer->getName()));
    }

    /**
     * @test
     */
    public function flushing_the_timers_returns_an_empty_collection()
    {
        $this->assertEmpty(Timers::flush());
        $this->assertInstanceOf(Collection::class, Timers::flush());
    }

    /**
     * @test
     */
    public function reporting_the_timers_logs_the_inner_collection_of_timers_to_the_provided_log()
    {
        $log = Mockery::spy(LoggerInterface::class);

        Timer::start('name');
        Timers::report($log);

        $log->shouldHaveReceived('log')->withArgs(function ($level, $message, $context) {
            $this->assertSame('info', $level);
            $this->assertSame('SoapBox\Timer\Timers::report', $message);
            $this->assertTrue(isset($context['name']));

            return true;
        });
    }

    /**
     * @test
     */
    public function reporting_will_report_at_the_specified_level_to_the_provided_log()
    {
        $log = Mockery::spy(LoggerInterface::class);

        Timers::report($log, 'error');

        $log->shouldHaveReceived('log')->withArgs(function ($level, $message, $context) {
            $this->assertSame('error', $level);
            $this->assertSame('SoapBox\Timer\Timers::report', $message);
            $this->assertEmpty($context);

            return true;
        });
    }

    /**
     * @test
     */
    public function disabling_the_timers_will_prevent_additional_timers_from_being_stored()
    {
        Timers::disable();

        $timer = Timer::start('timer');
        Timers::register($timer);

        $timers = Timers::flush();

        $this->assertEmpty($timers);

        Timers::enable();

        $timer = Timer::start('timer');

        $timers = Timers::flush();

        $this->assertCount(1, $timers);
        $this->assertTrue($timers->has($timer->getName()));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function report_does_not_log_the_timers_if_the_timers_are_disabled_empty()
    {
        $log = Mockery::spy(LoggerInterface::class);

        Timers::disable();
        Timers::report($log);

        $log->shouldNotHaveReceived('log');
    }

    /**
     * @test
     */
    public function report_logs_the_timers_if_the_timers_are_disabled_and_not_empty()
    {
        $log = Mockery::spy(LoggerInterface::class);

        Timer::start('timer');
        Timers::disable();
        Timers::report($log);

        $log->shouldHaveReceived('log')->withArgs(function ($level, $message, $context) {
            $this->assertSame('info', $level);
            $this->assertSame('SoapBox\Timer\Timers::report', $message);
            $this->assertTrue(isset($context['timer']));

            return true;
        });
    }

    /**
     * @test
     */
    public function report_logs_the_timers_if_the_timers_are_enabled_and_empty()
    {
        $log = Mockery::spy(LoggerInterface::class);

        Timers::report($log);

        $log->shouldHaveReceived('log')->withArgs(function ($level, $message, $context) {
            $this->assertSame('info', $level);
            $this->assertSame('SoapBox\Timer\Timers::report', $message);
            $this->assertTrue(empty($context));

            return true;
        });
    }

    /**
     * @test
     */
    public function report_logs_the_timers_if_the_timers_are_enabled_and_not_empty()
    {
        $log = Mockery::spy(LoggerInterface::class);

        Timer::start('timer');
        Timers::report($log);

        $log->shouldHaveReceived('log')->withArgs(function ($level, $message, $context) {
            $this->assertSame('info', $level);
            $this->assertSame('SoapBox\Timer\Timers::report', $message);
            $this->assertTrue(isset($context['timer']));

            return true;
        });
    }
}
