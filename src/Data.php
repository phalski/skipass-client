<?php


namespace Phalski\Skipass;

use JsonSerializable;

/**
 * Class Data
 * @package Phalski\Skipass
 */
class Data implements JsonSerializable
{
    /**
     * @var string
     */
    protected $project_id;
    /**
     * @var string
     */
    protected $locale;
    /**
     * @var Ticket
     */
    protected $ticket;
    /**
     * @var Wtp|null
     */
    protected $wtp;
    /**
     * @var array
     */
    protected $days;
    /**
     * @var array
     */
    protected $lifts;

    /**
     * Data constructor.
     * @param string $project_id
     * @param string $locale
     * @param Ticket $ticket
     * @param Wtp $wtp
     * @param array $days
     * @param array $lifts
     */
    public function __construct(string $project_id, string $locale, Ticket $ticket, ?Wtp $wtp, array $days, array $lifts)
    {
        $this->project_id = $project_id;
        $this->locale = $locale;
        $this->ticket = $ticket;
        $this->wtp = $wtp;
        $this->days = $days;
        $this->lifts = $lifts;
    }

    /**
     * @return string
     */
    public function getProjectId(): string
    {
        return $this->project_id;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return Ticket
     */
    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    /**
     * @return Wtp|null
     */
    public function getWtp():?Wtp
    {
        return $this->wtp;
    }

    /**
     * @return array
     */
    public function getDays(): array
    {
        return $this->days;
    }

    /**
     * @return array
     */
    public function getLifts(): array
    {
        return $this->lifts;
    }

    public function jsonSerialize()
    {
        return [
            'projectId' => $this->project_id,
            'locale' => $this->locale,
            'ticketId' => $this->ticket->getId(),
            'wtp' => $this->wtp,
            'days' => $this->days,
            'lifts' => $this->lifts,
        ];
    }


}