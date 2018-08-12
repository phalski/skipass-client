<?php


namespace Phalski\Skipass;

use InvalidArgumentException;
use JsonSerializable;
use Spatie\Regex\Regex;
use Spatie\Regex\RegexFailed;

class Wtp implements JsonSerializable
{
    private const PATTERN = '/^(\d+)-(\d+)-(\d+)\z/';

    /**
     * @var string
     */
    protected $chip_id;

    /**
     * @var string
     */
    protected $chip_id_crc;

    /**
     * @var string
     */
    protected $accept_id;

    /**
     * Wtp constructor.
     * @param string $chip_id
     * @param string $chip_id_crc
     * @param string $accept_id
     */
    public function __construct(string $chip_id, string $chip_id_crc, string $accept_id)
    {
        $this->chip_id = $chip_id;
        $this->chip_id_crc = $chip_id_crc;
        $this->accept_id = $accept_id;
    }


    /**
     * @param $ticket
     * @return Wtp
     */
    public static function for($ticket): self
    {
        $result = Regex::match(static::PATTERN, $ticket);
        try {
            return new static($result->group(1), $result->group(2), $result->group(3));
        } catch (RegexFailed $e) {
            throw new InvalidArgumentException(sprintf('Failed to parse wtp \'%s\'', $ticket), 0, $e);
        }
    }

    /**
     * @return string
     */
    public function getChipId(): string
    {
        return $this->chip_id;
    }

    /**
     * @return string
     */
    public function getChipIdCrc(): string
    {
        return $this->chip_id_crc;
    }

    /**
     * @return string
     */
    public function getAcceptId(): string
    {
        return $this->accept_id;
    }

    /**
     * @return string
     */
    public function getId(): string {
        return join('-', [$this->getChipId(), $this->getChipIdCrc(), $this->getAcceptId()]);
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