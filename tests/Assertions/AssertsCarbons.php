<?php

namespace Actengage\NightWatch\Tests\Assertions;

use Carbon\Carbon;
use Carbon\CarbonInterval;

trait AssertsCarbons
{
    public function assertCarbonsEqualWithDelta(Carbon $expected, Carbon $actual, CarbonInterval $delta, string $message = '')
    {
        $diff = $actual->diffAsCarbonInterval($expected, true)->forHumans(['minimumUnit' => 'ms']);
        self::assertEqualsWithDelta(
            $expected->getPreciseTimestamp(),
            $actual->getPreciseTimestamp(),
            $delta->totalMicroseconds,
            $message ?: "Failed asserting that '" . $actual . "' was within '" . $delta->forHumans(['minimumUnit' => 'ms']) . "' of '" . $expected . "'. They are '" . $diff . "' apart."
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
}