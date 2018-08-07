<?php


namespace Phalski\Skipass\Model;

use JsonSerializable;

use Phalski\Skipass\Context;


/**
 * Class Result
 * @package Phalski\Skipass\Model
 */
class Result implements JsonSerializable
{
    /**
     * @var PassId
     */
    private $passId;
    /**
     * @var string
     */
    private $projectId;
    /**
     * @var array
     */
    private $days;
    /**
     * @var array
     */
    private $lifts;
    /**
     * @var mixed
     */
    private $meta;

    /**
     * Result constructor.
     * @param PassId $passId
     * @param string $projectId
     * @param array $days
     * @param array $lifts
     * @param mixed $meta
     */
    public function __construct(PassId $passId, string $projectId, array $days = [], array $lifts = [], $meta = null)
    {
        $this->passId = $passId;
        $this->projectId = $projectId;
        $this->days = $days;
        $this->lifts = $lifts;
        $this->meta = $meta;
    }

    /**
     * @return PassId
     */
    public function getPassId(): PassId
    {
        return $this->passId;
    }

    /**
     * @return Context
     */
    public function getProjectId(): Context
    {
        return $this->projectId;
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
        $result = [
            'passId' => $this->passId,
            'projectId' => $this->projectId,
            'days' => $this->days,
            'lifts' => $this->lifts
        ];

        if ($this->meta) {
            $result['_meta'] = $this->meta;
        }

        return $result;
    }


}