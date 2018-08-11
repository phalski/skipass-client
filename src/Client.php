<?php


namespace Phalski\Skipass;


use InvalidArgumentException;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Spatie\Regex\Regex;

/**
 * Class Client
 *
 * Wraps all http client actions
 *
 * @package Phalski\Skipass
 */
class Client
{

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $project_id;

    /**
     * @var bool|null
     */
    private $has_multiple_days;

    /**
     * @var bool
     */
    private $is_ready;

    /**
     * Client constructor.
     * @param $project_id
     * @param $client
     * @param $locale
     */
    public function __construct(string $project_id, \GuzzleHttp\Client $client, string $locale)
    {
        $this->project_id = $project_id;
        $this->client = $client;
        $this->locale = $locale;
        $this->is_ready = false;
    }


    /**
     * @param string $project_id
     * @param string $base_uri
     * @param string $locale
     * @return Client
     */
    public static function for(string $project_id, string $base_uri = 'http://kv.skipass.cx', string $locale = 'en') {
        $client = new \GuzzleHttp\Client([
            'allow_redirects' => false,
            'base_uri' => $base_uri,
            'cookies' => true
        ]);
        return new self($project_id, $client, $locale);
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->clear();
        $this->locale = $locale;
    }

    /**
     * @param string $project_id
     */
    public function setProjectId(string $project_id): void
    {
        $this->clear();
        $this->project_id = $project_id;
    }



    /**
     * @param Ticket $ticket
     */
    public function setTicket(Ticket $ticket): void
    {
        $this->clear();

        $response = $this->client->post($this->searchUri(), [
            'form_params' => [
                'search' => 'ticket',
                'nProjectNO' => $ticket->getProject(),
                'nPOSNO' => $ticket->getPos(),
                'nSerialNO' => $ticket->getSerial(),
            ]
        ]);

        $this->has_multiple_days = $this->testDays($response);

        if (is_null($this->has_multiple_days)) {
            throw new InvalidArgumentException('Ticket not found: ' . $ticket);
        }

        $this->ready();
    }

    /**
     * @param $wtp
     */
    public function setWtp($wtp): void
    {
        $this->clear();

        $response = $this->client->post($this->searchUri(), [
            'form_params' => [
                'search' => 'wtp',
                'szChipID' => $wtp->getChipId(),
                'szChipIDCRC' => $wtp->getChipIdCrc(),
                'szAcceptID' => $wtp->getAcceptId(),
            ]
        ]);

        $this->has_multiple_days = $this->testDays($response);

        if (is_null($this->has_multiple_days)) {
            throw new InvalidArgumentException('WTP not found: ' . $wtp);
        }

        $this->ready();
    }

    /**
     * @return bool
     */
    public function hasMultipleDays(): bool
    {
        $this->ensureReady();
        return $this->has_multiple_days;
    }

    /**
     * @return string
     */
    public function getAccessListContents(): string
    {
        $this->ensureReady();
        return $this->client->get($this->accessListUri())->getBody()->getContents();
    }

    /**
     * @param int $day_id
     * @return string
     */
    public function getDetailContents(int $day_id): string
    {
        $this->ensureReady();
        return $this->client->get($this->detailUri($day_id))->getBody()->getContents();
    }

    /**
     * @return bool|null
     */
    public function isReady(): bool
    {
        return $this->is_ready;
    }



    /**
     *
     */
    private function clear(): void
    {
        $this->is_ready = false;
        $this->has_multiple_days = null;
    }

    /**
     *
     */
    private function ready(): void
    {
        $this->is_ready = true;
    }

    /**
     *
     */
    private function ensureReady(): void
    {
        if (!$this->is_ready) {
            throw new LogicException('Client is not ready. Set a ticket or wtp!');
        }
    }

    /**
     * @param ResponseInterface $response
     * @return bool|null
     */
    private function testDays(ResponseInterface $response): ?bool
    {
        $location = $response->hasHeader('Location') ? $response->getHeader('Location')[0] : null;

        if (is_null($location)) {
            return null;
        } elseif (Regex::match(self::encodePattern($this->detailUri(0)), $location)->hasMatch()) {
            return false;
        } elseif (Regex::match(self::encodePattern($this->accessListUri()), $location)->hasMatch()) {
            return true;
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    private function accessListUri(): string
    {
        return '/' . $this->project_id . '/' . $this->locale . '/acclist.php';
    }

    /**
     * @return string
     */
    private function searchUri(): string
    {
        return '/' . $this->project_id . '/' . $this->locale . '/search.php';
    }

    /**
     * @param int $day_id
     * @return string
     */
    private function detailUri(int $day_id): string
    {
        return '/' . $this->project_id . '/' . $this->locale . '/detail.php?day=' . $day_id;
    }

    /**
     * @param string $s
     * @return string
     */
    private static function encodePattern(string $s)
    {
        return '`' . preg_quote($s) . '`';
    }
}