<?php

namespace Phalski\Skipass;

use DateInterval;
use JsonSerializable;

/**
 * Class Lift
 * @package Phalski\Skipass
 */
class Lift implements JsonSerializable
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var float
     */
    private $lowerElevationMeters;
    /**
     * @var float
     */
    private $upperElevationMeters;
    /**
     * @var DateInterval
     */
    private $rideDuration;

    /**
     * Lift constructor.
     * @param string $id
     * @param float $lowerElevationMeters
     * @param float $upperElevationMeters
     * @param DateInterval $rideDuration
     */
    public function __construct(string $id, ?float $lowerElevationMeters, ?float $upperElevationMeters, ?DateInterval $rideDuration)
    {
        $this->id = $id;
        $this->lowerElevationMeters = $lowerElevationMeters;
        $this->upperElevationMeters = $upperElevationMeters;
        $this->rideDuration = $rideDuration;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return float
     */
    public function getLowerElevationMeters(): float
    {
        return $this->lowerElevationMeters;
    }

    /**
     * @param float $lowerElevationMeters
     */
    public function setLowerElevationMeters(float $lowerElevationMeters): void
    {
        $this->lowerElevationMeters = $lowerElevationMeters;
    }

    /**
     * @return float
     */
    public function getUpperElevationMeters(): float
    {
        return $this->upperElevationMeters;
    }

    /**
     * @param float $upperElevationMeters
     */
    public function setUpperElevationMeters(float $upperElevationMeters): void
    {
        $this->upperElevationMeters = $upperElevationMeters;
    }

    /**
     * @return DateInterval
     */
    public function getRideDuration(): DateInterval
    {
        return $this->rideDuration;
    }

    /**
     * @param DateInterval $rideDuration
     */
    public function setRideDuration(DateInterval $rideDuration): void
    {
        $this->rideDuration = $rideDuration;
    }

    public function jsonSerialize()
    {
        // TODO get rid of format hack
        $duration = isset($this->rideDuration) ? 'PT'.$this->rideDuration->i.'M' : null;
        return [
            'id' => $this->id,
            'lowerElevationMeters' => $this->lowerElevationMeters,
            'upperElevationMeters' => $this->upperElevationMeters,
            'rideDuration' => $duration
        ];
    }


}