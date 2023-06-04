<?php

/*
 * Library to handle Portone billing datetime and date strings as objects
 */

namespace PortaDateTimeTest;

use Psr\Container\ContainerInterface;

/**
 * Test class for PortaDateTime
 */
class PortaDateTimeTest extends \PHPUnit\Framework\TestCase {

    const ZONE = 'Pacific/Palau';
    const DATETIME = '2023-03-20 07:38:17';
    const DATE = '2023-03-20';
    const LOCAL_DATETIME = '2023-03-20 16:38:17';
    const FIRST_MOMENT = '2023-03-19 15:00:00';
    const LAST_MOMENT = '2023-03-20 14:59:59';
    const NEXT_FIRST = '2023-03-20 15:00:00';
    const LAST_MOMENT_THIS_MONTH = '2023-03-31 14:59:59';
    const FIRST_MOMENT_NEXT_MONTH = '2023-03-31 15:00:00';

    public function testCreate() {
        $t1 = new \PortaDateTime(self::LOCAL_DATETIME, self::ZONE);
        $t2 = new \PortaDateTime(self::LOCAL_DATETIME, new \DateTimeZone(self::ZONE));
        $this->assertEquals(static::DATETIME, $t1->formatPorta());
        $this->assertEquals(static::DATETIME, $t2->formatPorta());
    }

    public function testCreateException() {
        $this->expectException(\InvalidArgumentException::class);
        $t1 = new \PortaDateTime(self::LOCAL_DATETIME, []);
    }

    public function testCrateFromPorta() {
        $t = \PortaDateTime::fromPortaString(self::DATETIME, self::ZONE);
        $this->assertEquals(self::LOCAL_DATETIME, $t->format(\PortaDateTime::PORTA_DATETIME));
    }

    public function testMoments() {
        $t = new \PortaDateTime(self::LOCAL_DATETIME, self::ZONE);
        $this->assertEquals(self::FIRST_MOMENT, $t->firstMoment()->formatPorta());
        $this->assertEquals(self::LAST_MOMENT, (string) $t->lastMoment());
        $this->assertEquals(self::NEXT_FIRST, $t->nextDay()->firstMoment()->formatPorta());
        $this->assertEquals(self::FIRST_MOMENT_NEXT_MONTH, $t->firstDayNextMonth()->firstMoment()->formatPorta());
        $this->assertEquals(self::LAST_MOMENT_THIS_MONTH, $t->lastDayThisMonth()->lastMoment()->formatPorta());
    }

    public function testCreateFromPortaString() {
        $this->assertEquals(self::DATETIME,
                \PortaDateTime::fromPortaString(self::DATETIME)
                        ->format(\PortaDateTime::PORTA_DATETIME));
        $this->assertEquals(self::LOCAL_DATETIME,
                \PortaDateTime::fromPortaString(self::DATETIME, self::ZONE)
                        ->format(\PortaDateTime::PORTA_DATETIME));
    }

    public function testCreateFromDateString() {
        $this->assertEquals(self::FIRST_MOMENT,
                \PortaDateTime::fromPortaDateString(self::DATE, self::ZONE)
                        ->formatPorta());
    }

    public function testCreateAndFormat() {
        $t = new \DateTime(self::LOCAL_DATETIME, new \DateTimeZone(self::ZONE));
        $pt = \PortaDateTime::fromInterface($t);
        $this->assertEquals(self::DATETIME, $pt->formatPorta());
        $this->assertEquals(self::DATETIME, \PortaDateTime::formatDateTime($t));
    }

    public function testProrate() {
        $t = new \PortaDateTime(self::LOCAL_DATETIME, self::ZONE);
        $this->assertEquals((130 * 12 / 31), $t->prorateTillEndOfMonth(130));
    }

    public function testInFuture() {
        $t1 = new \PortaDateTime(self::LOCAL_DATETIME, self::ZONE);
        $this->assertFalse($t1->inFuture());
        $t2 = new \PortaDateTime('first day of next month', self::ZONE);
        $this->assertTrue($t2->inFuture());
    }

    public function testInPast() {
        $t1 = new \PortaDateTime(self::LOCAL_DATETIME, self::ZONE);
        $this->assertTrue($t1->inPast());
        $t2 = new \PortaDateTime('first day of next month', self::ZONE);
        $this->assertFalse($t2->inPast());
    }

    public function testBetween() {
        $t = new \PortaDateTime('now');
        $this->assertTrue($t->between(null, null));
        $this->assertTrue($t->between(new \DateTime('yesterday noon'), new \DateTime('tomorrow noon')));
        $this->assertTrue($t->between(null, new \DateTime('tomorrow noon')));
        $this->assertTrue($t->between(new \DateTime('yesterday noon'), null));
        $this->assertFalse($t->between(null, new \DateTime('yesterday noon')));
        $this->assertFalse($t->between(new \DateTime('tomorrow noon'), null));
        $this->assertFalse($t->between(new \DateTime('tomorrow noon'), new \DateTime('yesterday noon')));
    }

    public function testFromContainer() {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
                ->method('has')
                ->withConsecutive(
                        [$this->equalTo('default.timezone')],
                        [$this->equalTo('default.timezone')])
                ->willReturn(true, false);
        $container->expects($this->exactly(1))
                ->method('get')
                ->with($this->equalTo('default.timezone'))
                ->willReturn(self::ZONE);
        $t = new \PortaDateTime(self::LOCAL_DATETIME, $container);
        $this->assertEquals(self::DATETIME, (string) $t);
        $t2 = new \PortaDateTime(self::LOCAL_DATETIME, $container);
        $this->assertEquals(self::LOCAL_DATETIME, (string) $t2);
    }

}
