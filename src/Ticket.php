<?php


namespace Phalski\Skipass;

use InvalidArgumentException;
use JsonSerializable;
use Spatie\Regex\Regex;
use Spatie\Regex\RegexFailed;


/**
 * Class PassId
 * @package Phalski\Skipass
 */
class Ticket implements JsonSerializable
{
    private const PATTERN = '/^(\d+)-(\d+)-(\d+)\z/';

    /**
     * @var string
     */
    protected $project;

    /**
     * @var string
     */
    protected $pos;

    /**
     * @var string
     */
    protected $serial;

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
     * @param $ticket
     * @return Ticket
     * @throws InvalidArgumentException
     */
    public static function for($ticket): self
    {
        $result = Regex::match(static::PATTERN, $ticket);
        try {
            return new static($result->group(1), $result->group(2), $result->group(3));
        } catch (RegexFailed $e) {
            throw new InvalidArgumentException(sprintf('Failed to parse ticket \'%s\'', $ticket), 0, $e);
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
    public function getId(): string
    {
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