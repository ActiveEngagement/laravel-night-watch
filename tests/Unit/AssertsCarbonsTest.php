<?php

namespace Actengage\NightWatch\Tests\Unit;

use Actengage\NightWatch\Tests\TestCase;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use PHPUnit\Framework\AssertionFailedError;

class AssertsCarbonsTest extends TestCase
{
    public function test__assertCarbonsEqual__withEqualCarbons__passes()
    {
        $exp = Carbon::create(2022, 6, 7, 3, 1, 5);
        $act = Carbon::create(2022, 6, 7, 3, 1, 5);

        $this->assertCarbonsEqual($exp, $act);
    }

    public function test__assertCarbonsEqual__withInequalCarbons__fails()
    {
        $exp = Carbon::create(2022, 6, 7, 3, 1, 5);
        $act = Carbon::create(2022, 6, 7, 3, 1, 4);

        $this->assertThrows(function() use ($exp, $act) {
            $this->assertCarbonsEqual($exp, $act, "They've got to be equal!");
        }, AssertionFailedError::class, "They've got to be equal!");
    }

    public function test__assertCarbonsEqualWithDelta__withInBoundsCarbons__passes()
    {
        $exp = Carbon::create(2022, 6, 7, 3, 1, 5);
        $act = Carbon::create(2022, 6, 7, 3, 1, 6);
        $int = CarbonInterval::milliseconds(1000);

        $this->assertCarbonsEqualWithDelta($exp, $act, $int);
    }

    public function test__assertCarbonsEqualWithDelta__withOutOfBoundsCarbons__fails()
    {
        $exp = Carbon::create(2022, 6, 7, 3, 1, 5);
        $act = Carbon::create(2022, 6, 7, 3, 1, 6);
        $int = CarbonInterval::milliseconds(999);

        $this->assertThrows(function() use ($exp, $act, $int) {
            $this->assertCarbonsEqualWithDelta($exp, $act, $int, "They've got to be somewhat equal!");
        }, AssertionFailedError::class, "They've got to be somewhat equal!");
    }

    public function test__assertCarbonsEqualWithDelta__withDifferencePrecisionsWithInBoundsCarbons__passes() {
        $exp = Carbon::createFromTimestampMs(123);
        $act = Carbon::createFromTimestampMs(123.1);
        $int = CarbonInterval::milliseconds(0.1);

        $this->assertCarbonsEqualWithDelta($exp, $act, $int);
    }

    public function test__assertIntervalsEqual__withEqualIntervals__passes()
    {
        $exp = CarbonInterval::milliseconds(2000);
        $act = CarbonInterval::seconds(2);

        $this->assertIntervalsEqual($exp, $act);
    }

    public function test__assertIntervalsEqual__withInequalIntervals__fails()
    {
        $exp = CarbonInterval::milliseconds(2001);
        $act = CarbonInterval::seconds(2);

        $this->assertThrows(function() use ($exp, $act) {
            $this->assertIntervalsEqual($exp, $act, "They've got to be equal!");
        }, AssertionFailedError::class, "They've got to be equal!");
    }

    public function test__assertIntervalsEqualWithDelta__withInBoundsIntervals__passes()
    {
        $exp = CarbonInterval::milliseconds(2001);
        $act = CarbonInterval::seconds(2);
        $d = CarbonInterval::milliseconds(1);

        $this->assertIntervalsEqualWithDelta($exp, $act, $d);
    }

    public function test__assertIntervalsEqualWithDelta__withOutOfBoundsIntervals__fails()
    {
        $exp = CarbonInterval::milliseconds(2002);
        $act = CarbonInterval::seconds(2);
        $d = CarbonInterval::milliseconds(1);

        $this->assertThrows(function() use ($exp, $act, $d) {
            $this->assertIntervalsEqualWithDelta($exp, $act, $d, "They've got to be somewhat equal!");
        }, AssertionFailedError::class, "They've got to be somewhat equal!");
    }

}