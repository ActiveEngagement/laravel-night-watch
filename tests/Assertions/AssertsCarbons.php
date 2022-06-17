<?php

namespace Actengage\NightWatch\Tests\Assertions;

use Carbon\Carbon;
use Carbon\CarbonInterval;

trait AssertsCarbons
{
    public function assertCarbonsEqualWithDelta(Carbon $expected, Carbon $actual, CarbonInterval $delta, string $message = '')
    {
        $diff = $actual->diffAsCarbonInterval($expected, true);
        self::assertEqualsWithDelta(
            $expected->getPreciseTimestamp(),
            $actual->getPreciseTimestamp(),
            $delta->totalMicroseconds,
            $message ?: "Failed asserting that '" . $actual . "' was within '" . $this->format($delta) . "' of '" . $expected . "'. They are '" . $this->format($diff) . "' apart."
        );
    }

    public function assertCarbonsEqual(Carbon $expected, Carbon $actual, string $message = '')
    {
        self::assertEquals(
            $expected->getPreciseTimestamp(),
            $actual->getPreciseTimestamp(),
            $message ?: "Failed asserting that '" . $actual . "' was equal to '" . $expected . "'."
        );
    }

    public function assertIntervalsEqualWithDelta(CarbonInterval $expected, CarbonInterval $actual, CarbonInterval $delta, string $message = '')
    {
        $diff = CarbonInterval::make($actual)->sub($expected);
        self::assertEqualsWithDelta(
            $expected->totalMicroseconds,
            $actual->totalMicroseconds,
            $delta->totalMicroseconds,
            $message ?: "Failed asserting that '" . $this->format($actual) . "' was within '" . $this->format($delta) . "' of '" . $this->format($expected) . "'. They are '" . $this->format($diff) . "' apart."
        );
    }

    public function assertIntervalsEqual(CarbonInterval $expected, CarbonInterval $actual, string $message = '')
    {
        self::assertEquals(
            $expected->totalMicroseconds,
            $actual->totalMicroseconds,
            $message ?: "Failed asserting that '" . $this->format($actual) . "' was equal to '" . $this->format($expected) . "'."
        );
    }

    private function format(CarbonInterval $interval)
    {
        return $interval->forHumans(['minimumUnit' => 'ms']);
    }
}