<?php

namespace Phalski\Skipass;

use DateInterval;
use DateTime;
use DateTimeZone;
use DOMDocument;
use DOMNode;
use DOMXPath;
use Exception;
use InvalidArgumentException;
use NumberFormatter;
use Spatie\Regex\Regex;
use Spatie\Regex\RegexFailed;

/**
 * Class Selector
 * @package Phalski\Skipass
 */
class Selector
{
    private $timezone;

    /**
     * Selector constructor.
     * @var string $timezone
     */
    public function __construct(string $timezone = 'Europe/Berlin')
    {
        $this->timezone = new DateTimeZone($timezone);
    }

    public static function xPathFor(string $source, bool $strict = true, $options = 0): DOMXPath
    {
        $doc = new DOMDocument();
        if ($strict) {
            $doc->loadHTML($source, $options);
        } else {
            $internal_errors = libxml_use_internal_errors(true);
            $doc->loadHTML($source);
            libxml_use_internal_errors($internal_errors);
        }
        return new DOMXPath($doc);
    }

    public static function xPathForNode(DOMNode $node): DOMXPath {
        $cloned = $node->cloneNode(true);
        $doc = new DOMDocument();
        $doc->appendChild($doc->importNode($cloned, true));
        return new DOMXPath($doc);
    }

    public function dayCount($html): int
    {
        return self::xPathFor($html)->query('//table[@id="acclist"]/tr[@id]/@id')->count();
    }


    /**
     * @param $html
     * @return Detail
     * @throws UnexpectedContentException
     */
    public function detail(string $html): Detail
    {
        $xpath = self::xPathFor($html, false);

        $ticket = self::captureTicket($xpath->query('//*[@id="ticket"]')->item(0)->textContent);
        if (is_null($ticket)) {
            throw new UnexpectedContentException('Failed to extract ticket from content');
        }

        $date = self::captureDate($xpath->query('//*[@id="container"]/div[2]')->item(0)->textContent);
        if (is_null($date)) {
            throw new UnexpectedContentException('Failed to extract date from content');
        }

        $nodes = $xpath->query('//*[@id="detail"]/tr[@class and not(@class="table_header")]');
        if ($nodes->count() <= 0) {
            throw new UnexpectedContentException('Failed to extract ride list from content');
        }

        $rides = [];
        $lifts = [];

        foreach ($nodes as $node) {
            $xpath = self::xPathForNode($node);

            $liftId = $xpath->query('//td[2]')->item(0)->textContent;

            if (empty($lifts[$liftId])) {
                $lifts[$liftId] = new Lift(
                    $liftId,
                    self::captureElevation($xpath->query('//td[3]')->item(0)->textContent),
                    self::captureElevation($xpath->query('//td[5]')->item(0)->textContent),
                    self::captureDuration($xpath->query('//td[7]')->item(0)->textContent));
            }
            $ride = new Ride(
                self::captureTime($date, $xpath->query('//td[4]')->item(0)->textContent),
                $liftId);
            array_push($rides, $ride);
        }

        return new Detail($ticket, $date, $rides, $lifts);
    }

    public static function hasSearch($html): bool {
        return 0 < self::xPathFor($html)->query('//*[@id="search"]')->count();
    }

    // capture

    private static function captureTicket(string $textContent): ?Ticket
    {
        $match = Regex::match('/(?P<passId>\d+-\d+-\d+)/', $textContent);
        if (!$match->hasMatch()) {
            return null;
        }
        try {
            return Ticket::for($match->group('passId'));
        } catch (RegexFailed | InvalidArgumentException $e) {
            return null;
        }
    }

    private function captureDate(string $textContent): ?DateTime {
        $match = Regex::match('/(?P<date>\d{2}\.\d{2}\.\d{4})/', $textContent);
        if (!$match->hasMatch()) {
            return null;
        }
        try {
            $date = DateTime::createFromFormat('d.m.Y', $match->group('date'), $this->timezone);
            $date->setTime(0,0);
            return $date;
        } catch (RegexFailed $e) {
            return null;
        }
    }

    private static function captureTime(DateTime $date, string $textContent): ?DateTime {
        $match = Regex::match('/(?P<time>\d{2}\:\d{2})/', $textContent);
        if (!$match->hasMatch()) {
            return null;
        }
        try {
            return new DateTime($date->format('Y-m-d').' '.$match->group('time'), $date->getTimezone());
        } catch (RegexFailed $e) {
            return null;
        }
    }

    private static function captureElevation(string $textContent): ?float {
        $match = Regex::match('/(?P<altitude>(?:\d+\.)?\d+)m$/', $textContent);
        if (!$match->hasMatch()) {
            return null;
        }
        try {
            $fmt = numfmt_create( 'de_DE', NumberFormatter::DECIMAL );
            $altitude = numfmt_parse($fmt, $match->group('altitude'));
            return $altitude === 0.0 ? null : $altitude;
        } catch (RegexFailed $e) {
            return null;
        }
    }

    private static function captureDuration(string $textContent): ?DateInterval {
        $match = Regex::match('/(?P<duration>\d+) min$/', $textContent);
        if (!$match->hasMatch()) {
            return null;
        }
        try {
            $duration = intval($match->group('duration'));
            return $duration === 0 ? null : new DateInterval('PT'.$duration.'M');
        } catch (RegexFailed | Exception $e) {
            return null;
        }
    }
}