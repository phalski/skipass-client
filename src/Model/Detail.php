<?php

namespace Phalski\Skipass\Model;

use DateTime;

/**
 * Class Detail
 * @package Phalski\Skipass
 */
class Detail
{
    /**
     * @var PassId
     */
    private $passId;
    /**
     * @var DateTime
     */
    private $date;
    /**
     * @var array
     */
    private $rides;
    /**
     * @var array
     */
    private $lifts;

    /**
     * Detail constructor.
     * @param PassId $passId
     * @param DateTime $date
     * @param array $rides
     * @param array $lifts
     */
    public function __construct(PassId $passId, DateTime $date, array $rides, array $lifts)
    {
        $this->passId = $passId;
        $this->date = $date;
        $this->rides = $rides;
        $this->lifts = $lifts;
    }

    /**
     * @return PassId
     */
    public function getPassId(): PassId
    {
        return $this->passId;
    }

    /**
     * @param PassId $passId
     */
    public function setPassId(PassId $passId): void
    {
        $this->passId = $passId;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }

    /**
     * @return array
     */
    public function getRides(): array
    {
        return $this->rides;
    }

    /**
     * @param array $rides
     */
    public function setRides(array $rides): void
    {
        $this->rides = $rides;
    }

    /**
     * @return array
     */
    public function getLifts(): array
    {
        return $this->lifts;
    }

    /**
     * @param array $lifts
     */
    public function setLifts(array $lifts): void
    {
        $this->lifts = $lifts;
    }

}