<?php

/*
 *  PortaDateTime usage example
 */
require __DIR__ . '/../vendor/autoload.php';

// Let's API returned datetime, string, UTC
$portaDateTime = '2023-03-07 14:52:43';
echo "Biling datetime string: '$portaDateTime'\n";

// Convert to object with local timezone GMT+3
$dt = \PortaDateTime::FromPortaString($portaDateTime, 'GMT+03:00');
echo "Local timezone (+03:00): {$dt->format(DateTimeInterface::RFC3339_EXTENDED)}\n\n";

// Then, do with this everything we can do with Datetime object plus specific functions
// Mind it is imutable, so the object itself won't change
$nextDayLastMoment = $dt->nextDay()->lastMoment();
echo "Last second of the next day:\n";
echo "  Local timezone (+03:00):    {$nextDayLastMoment->format(DateTimeInterface::RFC3339_EXTENDED)}\n";
echo "  String for billing in UTC: '{$nextDayLastMoment}'\n\n";
// mind we use it as string and got billing-prepard timestring ^^^^^
//
//
//
// Let's prepare product change pair of datetime string for coming month boundary
// at Paris timezone
echo "Building coming month break point, midnight local time\n";

$now = new PortaDateTime('now', 'Europe/Paris');

$oldProductEnds = $now->lastDayThisMonth()->lastMoment();
$newProductStarts = $now->firstDayNextMonth()->firstMoment();

echo "  At local timezone (Paris, France):\n";
echo "    Ends:  {$oldProductEnds->format(DateTimeInterface::RFC3339_EXTENDED)}\n";
echo "    Start: {$newProductStarts->format(DateTimeInterface::RFC3339_EXTENDED)}\n\n";
echo "  Billign API strings at UTC:\n";
echo "    Ends:  {$oldProductEnds->formatPorta()}\n";
echo "    Start: {$newProductStarts}\n";

