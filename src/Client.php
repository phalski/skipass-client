<?php


namespace Phalski\Skipass;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use InvalidArgumentException;
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
    protected $client;

    /**
     * @var bool
     */
    protected $is_ready;

    /**
     * @var bool|null
     */
    protected $has_multiple_days;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $project_id;

    /**
     * @var Ticket|null
     */
    protected $ticket;

    /**
     * @var Wtp|null
     */
    protected $wtp;

    /**
     * Client constructor.
     * @param $project_id
     * @param $client
     * @param $locale
     */
    public function __construct(string $project_id, \GuzzleHttp\Client $client, string $locale)
    {
        if (empty($project_id)) {
            throw new InvalidArgumentException('Empty project_id');
        }
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
    public static function for(string $project_id, string $base_uri = 'http://kv.skipass.cx', string $locale = 'en'): self
    {
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
     * @throws FetchException
     * @throws NotFoundException
     */
    public function setTicket(Ticket $ticket): void
    {
        $this->clear();

        try {
            $response = $this->client->post($this->searchUri(), [
                'form_params' => [
                    'search' => 'ticket',
                    'nProjectNO' => $ticket->getProject(),
                    'nPOSNO' => $ticket->getPos(),
                    'nSerialNO' => $ticket->getSerial(),
                ]
            ]);
            $this->has_multiple_days = $this->testDays($response);
        } catch (Exception $e) {
            throw new FetchException('Failed to fetch search result for ticket: '.$ticket,0, $e);
        }

        if (is_null($this->has_multiple_days)) {
            throw new NotFoundException('Ticket not found: ' . $ticket);
        }

        $this->ticket = $ticket;
        $this->ready();
    }


    /**
     * @param Wtp $wtp
     * @throws FetchException
     * @throws NotFoundException
     */
    public function setWtp(Wtp $wtp): void
    {
        $this->clear();

        try {
            $response = $this->client->post($this->searchUri(), [
                'form_params' => [
                    'search' => 'wtp',
                    'szChipID' => $wtp->getChipId(),
                    'szChipIDCRC' => $wtp->getChipIdCrc(),
                    'szAcceptID' => $wtp->getAcceptId(),
                ]
            ]);
            $this->has_multiple_days = $this->testDays($response);
        } catch (Exception $e) {
            throw new FetchException('Failed to fetch search result for wtp: '.$wtp,0, $e);
        }

        if (is_null($this->has_multiple_days)) {
            throw new NotFoundException('WTP not found: ' . $wtp);
        }

        $this->wtp = $wtp;
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
    public function getLocale(): string
    {
        $this->ensureReady();
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getProjectId(): string
    {
        $this->ensureReady();
        return $this->project_id;
    }

    /**
     * @return null|Ticket
     */
    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    /**
     * @return null|Wtp
     */
    public function getWtp(): ?Wtp
    {
        return $this->wtp;
    }


    /**
     * @return string
     * @throws FetchException
     */
    public function getAccessListContents(): string
    {
        $this->ensureReady();
        try {
            return $this->client->get($this->accessListUri())->getBody()->getContents();
        } catch (Exception $e) {
            throw new FetchException('Failed to fetch access list for ticket or wtp: '. $this->ticket ?? $this->wtp,0, $e);
        }
    }

    /**
     * @param int $day_id
     * @return string
     * @throws FetchException
     */
    public function getDetailContents(int $day_id): string
    {
        $this->ensureReady();
        try {
            return $this->client->get($this->detailUri($day_id))->getBody()->getContents();
        } catch (Exception $e) {
            throw new FetchException('Failed to fetch day='.$day_id.' details for ticket or wtp: '. $this->ticket ?? $this->wtp,0, $e);
        }
    }

    /**
     * @param int $day_id
     * @return PromiseInterface
     */
    public function getDetailContentsAsync(int $day_id): PromiseInterface
    {
        $this->ensureReady();
        return $this->client->getAsync($this->detailUri($day_id))->then(function (ResponseInterface $r) {
            return $r->getBody()->getContents();
        }, function (Exception $e) {
            return new FetchException('Failed to fetch detail for ticket or wtp: '. $this->ticket ?? $this->wtp,0, $e);
        });
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
        $this->ticket = null;
        $this->wtp = null;
    }

    /**
     *
     */
    private function ready(): void
    {
        $this->is_ready = true;
    }


    /**
     * @throws NotReadyException
     */
    private function ensureReady(): void
    {
        if (!$this->is_ready) {
            throw new NotReadyException('Client is not ready. Set a ticket or wtp!');
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