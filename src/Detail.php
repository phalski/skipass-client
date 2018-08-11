<?php

namespace Phalski\Skipass;

use DateTime;

/**
 * Class Detail
 * @package Phalski\Skipass
 */
class Detail
{
    /**
     * @var Ticket
     */
    private $ticket;
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
     * @param Ticket $ticket
     * @param DateTime $date
     * @param array $rides
     * @param array $lifts
     */
    public function __construct(Ticket $ticket, DateTime $date, array $rides, array $lifts)
    {
        $this->ticket = $ticket;
        $this->date = $date;
        $this->rides = $rides;
        $this->lifts = $lifts;
    }

    /**
     * @return Ticket
     */
    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    /**
     * @param Ticket $ticket
     */
    public function setTicket(Ticket $ticket): void
    {
        $this->ticket = $ticket;
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