<?php

namespace SoapBox\Timer;

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
        if (is_null(Timers::$timers) || !Timers::$timers->has($name)) {
            $message = sprintf(
                "The '%s' timer has not been initialized.",
                $name
            );

            throw new TimerNotInitializedException($message);
        }

        return Timers::$timers->get($name);
    }
}
