<?php


namespace Phalski\Skipass;

use function GuzzleHttp\Promise\settle;
use function GuzzleHttp\Promise\unwrap;
use InvalidArgumentException;

class Skipass
{
    /**
     * @var Client $client
     */
    private $client;

    /**
     * @var Selector $selector
     */
    private $selector;


    /**
     * @var int|null $count
     */
    private $count;

    /**
     * Skipass constructor.
     * @param Client $client
     * @param Selector $selector
     */
    public function __construct(Client $client, Selector $selector)
    {
        if (!$client->isReady()) {
            throw new InvalidArgumentException('Client is not ready');
        }
        $this->client = $client;
        $this->selector = $selector;
    }

    public static function for(string $project_id, Ticket $ticket)
    {
        $client = Client::for($project_id);
        $client->setTicket($ticket);
        $selector = new Selector();
        return new Skipass($client, $selector);
    }

    public function updateCount()
    {
        if ($this->client->hasMultipleDays()) {
            $content = $this->client->getAccessListContents();
            $this->count = $this->selector->dayCount($content);
        } else {
            $this->count = 1;
        }
    }

    public function findById(int $day_id)
    {
        $this->updateCount();
        $this->ensureValidDayId($day_id);

        $content = $this->client->getDetailContents($day_id);
        $detail = $this->selector->detail($content);

        $lifts = $detail->getLifts();
        usort($lifts, function ($a, $b) {
            return strcmp($a->getId(), $b->getId());
        });

        return new Data(
            $this->client->getProjectId(),
            $this->client->getLocale(),
            $this->client->getTicket() ?? $detail->getTicket(),
            $this->client->getWtp(),
            [Day::for($detail, $day_id)],
            $lifts
        );
    }

    public function findAll($first = 50, $offset = 0)
    {
        $this->updateCount();
        echo $this->count;
        if ($offset < 0 || $this->count <= $offset) {
            throw new InvalidArgumentException('Offset "' . $offset . '" exceeds day count');
        }

        $upperBound = ((0 <= $first) && ($offset + $first < $this->count)) ? $offset + $first : $this->count;

        $ticket = null;
        $days = [];
        $lifts = [];
        $errors = [];

        for ($i = $offset; $i < $upperBound; $i++) {
            try {
                $content = $this->client->getDetailContents($i);
                $detail = $this->selector->detail($content);

                if (is_null($ticket)) {
                    $ticket = $detail->getTicket();
                }

                array_push($days, Day::for($detail, $i));

                foreach ($detail->getLifts() as $lift) {
                    if (empty($lifts[$lift->getId()])) {
                        $lifts[$lift->getId()] = $lift;
                    }
                }
            } catch (UnexpectedContentException $e) {
                $errors[$i] = $e;
            }
        }

        ksort($lifts);

        return new Result(new Data(
            $this->client->getProjectId(),
            $this->client->getLocale(),
            $this->client->getTicket() ?? $ticket,
            $this->client->getWtp(),
            $days,
            $lifts
        ), $errors);
    }

    public function count(): int
    {
        if (is_null($this->count)) {
            $this->updateCount();
        }
        return $this->count;
    }

    public function findAllAsync($first = 50, $offset = 0)
    {
        $this->updateCount();

        if ($offset < 0 || $this->count <= $offset) {
            throw new InvalidArgumentException('Offset "' . $offset . '" exceeds day count');
        }

        $upperBound = ((0 <= $first) && ($offset + $first < $this->count)) ? $offset + $first : $this->count;

        $ticket = null;
        $days = [];
        $lifts = [];
        $errors = [];

        $promises = [];
        for ($i = $offset; $i < $upperBound; $i++) {
            $promises[$i] = $this->client->getDetailContentsAsync($i);
        }

        $results = settle($promises)->wait();

        foreach ($results as $day_id => $promise) {
            if ($promise['state'] !== 'fulfilled') {
                $errors[$day_id] = new \LogicException('Request failed');
                continue;
            }

            try {
                $detail = $this->selector->detail($promise['value']);

                if (is_null($ticket)) {
                    $ticket = $detail->getTicket();
                }

                array_push($days, Day::for($detail, $day_id));

                foreach ($detail->getLifts() as $lift) {
                    if (empty($lifts[$lift->getId()])) {
                        $lifts[$lift->getId()] = $lift;
                    }
                }
            } catch (UnexpectedContentException $e) {
                $errors[$day_id] = $e;
            }
        }

        ksort($lifts);

        return new Result(new Data(
            $this->client->getProjectId(),
            $this->client->getLocale(),
            $this->client->getTicket() ?? $ticket,
            $this->client->getWtp(),
            $days,
            $lifts
        ), $errors);
    }

    private function ensureValidDayId(int $day_id)
    {
        if (!(0 <= $day_id && $day_id < $this->count)) {
            throw new InvalidArgumentException('Invalid day_id: ' . $day_id);
        }
    }
}