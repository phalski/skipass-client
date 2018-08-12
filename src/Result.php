<?php

namespace Phalski\Skipass;

use JsonSerializable;

class Result implements JsonSerializable
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * @var array
     */
    protected $errors;

    /**
     * Result constructor.
     * @param Data $data
     * @param array $errors
     */
    public function __construct(?Data $data, array $errors = [])
    {
        $this->data = $data;
        $this->errors = $errors;
    }

    /**
     * @return Data
     */
    public function getData(): Data
    {
        return $this->data;
    }

    public function hasErrors(): bool
    {
        return 0 < count($this->errors);
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function jsonSerialize()
    {
        return [
            'data' => $this->data,
            'hasErrors' => $this->hasErrors(),
            'errors' => $this->errors,
        ];
    }

}