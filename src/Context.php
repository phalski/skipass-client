<?php

namespace Phalski\Skipass;

use GuzzleHttp\Cookie\CookieJarInterface;
use JsonSerializable;

class Context implements JsonSerializable
{
    /**
     * @var CookieJarInterface
     */
    private $cookieJar;
    /**
     * @var string
     */
    private $projectId;
    /**
     * @var string
     */
    private $locale;
    /**
     * @var int
     */
    private $dayCount;

    /**
     * Context constructor.
     * @param CookieJarInterface $cookieJar
     * @param string $projectId
     * @param $locale
     * @param int $dayCount
     */
    public function __construct(CookieJarInterface $cookieJar, string $projectId, string $locale, int $dayCount)
    {
        $this->cookieJar = $cookieJar;
        $this->projectId = $projectId;
        $this->locale = $locale;
        $this->dayCount = $dayCount;
    }

    /**
     * @return CookieJarInterface
     */
    public function getCookieJar(): CookieJarInterface
    {
        return $this->cookieJar;
    }

    /**
     * @return string
     */
    public function getProjectId(): string
    {
        return $this->projectId;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return int
     */
    public function getDayCount(): int
    {
        return $this->dayCount;
    }

    public function jsonSerialize()
    {
        return [
            'projectId' => $this->projectId,
            'locale' => $this->locale,
            'dayCount' => $this->dayCount
        ];
    }


}