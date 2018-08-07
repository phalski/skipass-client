<?php

namespace Phalski\Skipass\Model;

use JsonSerializable;
use Spatie\Regex\Regex;
use Spatie\Regex\RegexFailed;

use Phalski\Skipass\InvalidIdException;

/**
 * Class PassId
 * @package Phalski\Skipass
 */
class PassId implements JsonSerializable
{
    private const PATTERN = '/^(\d+)-(\d+)-(\d+)\z/';

    /**
     * @var string
     */
    private $project;

    /**
     * @var string
     */
    private $pos;

    /**
     * @var string
     */
    private $serial;

    /**
     * PassId constructor.
     * @param $project
     * @param $pos
     * @param $serial
     */
    public function __construct(int $project, int $pos, int $serial)
    {
        $this->project = $project;
        $this->pos = $pos;
        $this->serial = $serial;
    }


    /**
     * @param $passId
     * @return PassId
     * @throws InvalidIdException
     */
    public static function for($passId): self
    {
        $result = Regex::match(static::PATTERN, $passId);
        try {
            return new static($result->group(1), $result->group(2), $result->group(3));
        } catch (RegexFailed $e) {
            throw new InvalidIdException(sprintf('Failed to parse pass ID \'%s\'', $passId), 0, $e);

        }
    }

    /**
     * @return string
     */
    public function getProject(): string
    {
        return $this->project;
    }

    /**
     * @return string
     */
    public function getPos(): string
    {
        return $this->pos;
    }

    /**
     * @return string
     */
    public function getSerial(): string
    {
        return $this->serial;
    }

    /**
     * @return string
     */
    public function getId(): string {
        return join('-', [$this->getProject(), $this->getPos(), $this->getSerial()]);
    }

    public function __toString()
    {
        return json_encode(get_object_vars($this), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function jsonSerialize()
    {
        return $this->getId();
    }

}