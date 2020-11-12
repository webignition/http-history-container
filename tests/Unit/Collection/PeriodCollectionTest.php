<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Unit\Collection;

use PHPUnit\Framework\TestCase;
use webignition\HttpHistoryContainer\Collection\PeriodCollection;
use webignition\HttpHistoryContainer\Tests\Model\PeriodCollectionTest\PeriodRange;

class PeriodCollectionTest extends TestCase
{
    /**
     * @dataProvider populateDataProvider
     *
     * @param int[] $addCallDelays
     * @param PeriodRange[] $expectedPeriodRanges
     */
    public function testPopulate(array $addCallDelays, array $expectedPeriodRanges)
    {
        $collection = new PeriodCollection();

        foreach ($addCallDelays as $addCallDelay) {
            $collection->add();
            usleep($addCallDelay);
        }

        $collection->add();

        $periods = $collection->getPeriodsInMicroseconds();

        foreach ($expectedPeriodRanges as $expectedRangeIndex => $expectedPeriodRange) {
            $period = $periods[$expectedRangeIndex] ?? null;

            self::assertGreaterThanOrEqual($expectedPeriodRange->getLower(), $period);
            self::assertLessThanOrEqual($expectedPeriodRange->getUpper(), $period);
        }
    }

    public function populateDataProvider(): array
    {
        return [
            'default' => [
                'addCallDelays' => [
                    0,
                    100,
                    200,
                    300,
                ],
                'expectedPeriodRanges' => [
                    new PeriodRange(0, 0),
                    new PeriodRange(0, 100),
                    new PeriodRange(100, 200),
                    new PeriodRange(200, 300),
                    new PeriodRange(300, 400),
                ],
            ],
        ];
    }
}
