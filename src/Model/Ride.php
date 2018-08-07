<?php

namespace Phalski\Skipass\Model;

use DateTime;
use JsonSerializable;

/**
 * Class Ride
 * @package Phalski\Skipass
 */
class Ride implements JsonSerializable
{
    /**
     * @var DateTime
     */
    private $timestamp;
    /**
     * @var string
     */
    private $liftId;

    /**
     * Ride constructor.
     * @param DateTime $timestamp
     * @param string $liftId
     */
    public function __construct(DateTime $timestamp, string $liftId)
    {
        $this->timestamp = $timestamp;
        $this->liftId = $liftId;
    }

    /**
     * @return DateTime
     */
    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    /**
     * @param DateTime $timestamp
     */
    public function setTimestamp(DateTime $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getLiftId(): string
    {
        return $this->liftId;
    }

    /**
     * @param string $liftId
     */
    public function setLiftId(string $liftId): void
    {
        $this->liftId = $liftId;
    }

    public function jsonSerialize()
    {
        return [
            'timestamp' => $this->timestamp->format(DateTime::ISO8601),
            'liftId' => $this->liftId

        ];
    }


}