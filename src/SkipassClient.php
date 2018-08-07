<?php


namespace Phalski\Skipass;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use OutOfBoundsException;
use Phalski\Skipass\Model\Detail;
use Phalski\Skipass\Model\Result;
use Psr\Http\Message\ResponseInterface;

use Phalski\Skipass\Model\Day;
use Phalski\Skipass\Model\Locale;
use Phalski\Skipass\Model\PassId;
use Phalski\Skipass\Model\WtpId;


/**
 * Class SkipassClient
 * @package Phalski\Skipass
 */
class SkipassClient implements SkipassInterface
{
    private const LOCATION_HEADER = 'Location';

    private const ACCLIST_PHP = 'acclist.php';
    private const DETAIL_PHP = 'detail.php';
    private const SEARCH_PHP = 'search.php';

    /**
     * @var Client
     */
    private $client;


    /**
     * @var string
     */
    private $locale;

    private $selectors;

    /**
     * SkipassClient constructor.
     * @param string $base_uri
     * @param string $locale
     */
    public function __construct(string $base_uri, string $locale = Locale::DE)
    {
        $this->client = new Client(['base_uri' => $base_uri]);
        $this->locale = $locale;
        $this->selectors = new Selectors();
    }

    public function findContextByPassId(string $projectId, PassId $id, string $locale = null): Context
    {
        $locale = $locale ?: $this->locale;
        $jar = new CookieJar();
        $response = self::requestSearch($jar, $projectId, $id, $locale);
        $dayCount = self::findDayCount($jar, $response);

        if (0 === $dayCount) {
            throw new ContextNotFoundException('Failed to create session context for: projectId=' . $projectId . ',passId=' . $id->getId());
        }

        return new Context($jar, $projectId, $locale, $dayCount);
    }

    public function findDayById(Context $context, int $dayId): Result
    {
        if (($dayId < 0) || ($context->getDayCount() <= $dayId)) {
            throw new InvalidArgumentException('Day ID "' . $dayId . '" is not valid for this context');
        }

        $detail = self::findDetail($context, $dayId);

        $lifts = $detail->getLifts();
        usort($lifts, function ($a, $b) {
            return strcmp($a->getId(), $b->getId());
        });

        return new Result($detail->getPassId(), $context->getProjectId(), [Day::for($detail, $dayId)], $detail->getLifts(), [
            'dayCount' => $context->getDayCount(),
            'locale' => $context->getLocale(),
            'dayId' => $dayId
        ]);
    }

    public function findDays(Context $context, int $offset = 0, int $limit = 50)
    {
        if ($offset < 0 || $context->getDayCount() <= $offset) {
            throw new InvalidArgumentException('Offset "' . $offset . '" exceeds day count');
        }

        $upperBound = ((0 <= $limit) && ($offset + $limit < $context->getDayCount())) ? $offset + $limit : $context->getDayCount();

        $passId = null;
        $days = [];
        $lifts = [];
        $errors = [];

        for ($i = $offset; $i < $upperBound; $i++) {
            try {
                $detail = self::findDetail($context, $i);

                if (is_null($passId)) {
                    $passId = $detail->getPassId();
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

        return new Result($passId, $context->getProjectId(), $days, array_values($lifts), [
            'dayCount' => $context->getDayCount(),
            'locale' => $context->getLocale(),
            'offset' => $offset,
            'limit' => $limit,
            'errors' => $errors
        ]);

    }

    private function findDayCount(CookieJarInterface $session, ResponseInterface $response): int
    {
        $location = self::findRedirectUri($response);
        if (is_null($location)) {
            return 0;
        }
        switch (self::findScriptName($location)) {
            case self::DETAIL_PHP:
                return 1;
            case self::ACCLIST_PHP:
                {
                    $listResponse = self::requestRedirect($session, $location);
                    if ($listResponse->getStatusCode() !== 200) {
                        return 0;
                    };
                    $html = $listResponse->getBody()->getContents();
                    return $this->selectors->dayCount($html);
                }
            default:
                return 0;
        }
    }


    /**
     * @param Context $context
     * @param int $dayId
     * @return Detail
     * @throws UnexpectedContentException
     */
    private function findDetail(Context $context, int $dayId): Detail
    {
        $response = self::requestDetail($context->getCookieJar(), $context->getProjectId(), $dayId, $context->getLocale());
        $html = $response->getBody()->getContents();
        return $this->selectors->detail($html);
    }

    private static function findScriptName(string $location): ?string
    {
        try {
            $elements = explode('/', parse_url($location)['path']);
            return end($elements);
        } catch (Exception $e) {
            return null;
        }
    }

    // HTTP stuff

    public function requestSearch(CookieJarInterface $cookieJar, string $projectId, PassId $passId, string $locale): ResponseInterface
    {
        return $this->client->post(self::localizedPathFor($projectId, $locale, [static::SEARCH_PHP]), [
            'allow_redirects' => false,
            'cookies' => $cookieJar,
            'form_params' => [
                'search' => 'ticket',
                'nProjectNO' => $passId->getProject(),
                'nPOSNO' => $passId->getPos(),
                'nSerialNO' => $passId->getSerial(),
            ]
        ]);
    }

    public function requestRedirect(CookieJarInterface $cookieJar, string $location): ResponseInterface
    {
        return $this->client->get($location, [
            'allow_redirects' => false,
            'cookies' => $cookieJar
        ]);
    }

    public function requestDetail(CookieJarInterface $cookieJar, string $projectId, int $dayId, string $locale): ResponseInterface
    {
        return $this->client->get(self::localizedPathFor($projectId, $locale, [static::DETAIL_PHP]) . '?day=' . $dayId, [
            'allow_redirects' => false,
            'cookies' => $cookieJar
        ]);
    }

    private static function findRedirectUri(ResponseInterface $response): ?string
    {
        return static::isRedirect($response) ? self::findLocation($response) : null;
    }

    private static function isRedirect(ResponseInterface $response): bool
    {
        return $response->getStatusCode() === 302;
    }

    private static function findLocation(ResponseInterface $response): ?string
    {
        return $response->hasHeader(self::LOCATION_HEADER) ?
            $response->getHeader(self::LOCATION_HEADER)[0] : null;
    }

    private static function localizedPathFor(string $projectId, string $locale, array $elements)
    {
        return static::pathFor([$projectId, $locale, self::pathFor($elements)]);
    }

    private static function pathFor(array $elements): string
    {
        return '/' . join('/', $elements);
    }
}