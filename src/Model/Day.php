<?php


namespace Phalski\Skipass\Model;

use DateTime;
use JsonSerializable;

/**
 * Class Day
 * @package Phalski\Skipass\Model
 */
class Day implements JsonSerializable
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var DateTime
     */
    private $date;
    /**
     * @var array
     */
    private $rides;

    /**
     * Day constructor.
     * @param int $id
     * @param DateTime $date
     * @param array $rides
     */
    public function __construct(int $id, DateTime $date, array $rides)
    {
        $this->id = $id;
        $this->date = $date;
        $this->rides = $rides;
    }

    public static function for(Detail $detail, int $dayId): self {
        return new self($dayId, $detail->getDate(), $detail->getRides());
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getDate(): DateTime
    {
        return $this->date;
    }

    /**
     * @return array
     */
    public function getRides(): array
    {
        return $this->rides;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format(DateTime::ISO8601),
            'rides' => $this->rides
        ];
    }


}