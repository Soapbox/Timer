<?php

namespace SoapBox\Timer;

use Psr\Log\LoggerInterface;
use Illuminate\Support\Collection;

class Timers
{
    /**
     * A collection of timers that have been started.
     *
     * @var \Illumiante\Support\Collection
     */
    private static $timers;

    /**
     * Determines if the Timers are enabled or not.
     *
     * @var boolean
     */
    private static $enabled = false;

    /**
     * Enables the timers. I.E. Store the timers that are set so we can report
     * them later.
     *
     * @return void
     */
    public static function enable(): void
    {
        Timers::$enabled = true;
    }

    /**
     * Disables the timers. I.E. Preform a no-op when the timer methods are
     * called so we can disable these at will in production.
     *
     * @return void
     */
    public static function disable(): void
    {
        Timers::$enabled = false;
    }

    /**
     * Returns the timers and resets the local set of timers.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function flush(): Collection
    {
        $temp = Timers::$timers;

        if (is_null(Timers::$timers)) {
            return new Collection([]);
        }

        Timers::$timers = null;
        return $temp;
    }

    /**
     * Registers the provided timer into our timer collection.
     *
     * @param \SoapBox\Timer\Timer $timer
     *
     * @return void
     */
    public static function register(Timer $timer): void
    {
        if (!Timers::$enabled) {
            return;
        }

        if (is_null(Timers::$timers)) {
            Timers::$timers = new Collection([]);
        }

        if (Timers::$timers->has($timer->getName())) {
            $message = sprintf(
                "The '%s' timer was previously created.",
                $timer->getName()
            );

            throw new DuplicateTimerException($message);
        }

        Timers::$timers->put($timer->getName(), $timer);
    }

    /**
     * Retrieves the requested timer by name.
     *
     * @param string $name
     *
     * @return \SoapBox\Timer\Timer
     */
    public static function get(string $name): Timer
    {
        if (!Timers::$enabled) {
            return Timer::start('null');
        }

        if (is_null(Timers::$timers) || !Timers::$timers->has($name)) {
            $message = sprintf(
                "The '%s' timer has not been initialized.",
                $name
            );

            throw new TimerNotInitializedException($message);
        }

        return Timers::$timers->get($name);
    }

    /**
     * Reports the contents of our timers to the provider logger.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $level
     *
     * @return void
     */
    public static function report(LoggerInterface $logger, string $level = 'info'): void
    {
        if (!Timers::$enabled && empty(Timers::$timers)) {
            return;
        }

        $logger->log($level, __METHOD__, Timers::flush()->toArray());
    }
}
