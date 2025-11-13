<?php

declare(strict_types=1);

namespace webignition\HttpHistoryContainer\Tests\Unit\Collection;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use webignition\HttpHistoryContainer\Collection\PeriodCollection;
use webignition\HttpHistoryContainer\Tests\Model\PeriodCollectionTest\PeriodRange;

class PeriodCollectionTest extends TestCase
{
    /**
     * @param int[]         $addCallDelays
     * @param PeriodRange[] $expectedPeriodRanges
     */
    #[DataProvider('populateDataProvider')]
    public function testPopulate(array $addCallDelays, array $expectedPeriodRanges): void
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

            $upper = $expectedPeriodRange->getUpper();
            if (is_int($upper)) {
                self::assertLessThanOrEqual($upper, $period);
            }
        }
    }

    /**
     * @return array<mixed>
     */
    public static function populateDataProvider(): array
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

    public function testAppend(): void
    {
        $collection = new PeriodCollection();
        self::assertSame([], $collection->getPeriodsInMicroseconds());

        $collection->append(1);
        $collection->append(2);
        $collection->append(15);

        self::assertSame([1, 2, 15], $collection->getPeriodsInMicroseconds());
    }
}
