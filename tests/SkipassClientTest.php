<?php


use \Phalski\Skipass\Model\Locale;
use \Phalski\Skipass\Model\PassId;
use \Phalski\Skipass\Model\ProjectId;
use \Phalski\Skipass\SkipassClient;

use \GuzzleHttp\Cookie\CookieJar;

class SkipassClientTest extends PHPUnit\Framework\TestCase
{
    public const BASE_URI = 'http://kv.skipass.cx';
    public const ACCLIST_LOCATION = 'http://kv.skipass.cx/golm/de/acclist.php';

    public const TEST_LOCALE = Locale::DE;

    /**
     * @var PassId
     */
    private $validMultiPassId;
    /**
     * @var PassId
     */
    private $validSinglePassId;
    /**
     * @var PassId
     */
    private $invalidPassId;


    /**
     * @var SkipassClient
     */
    private $client;

    /**
     * @var \GuzzleHttp\Cookie\CookieJarInterface
     */
    private $jar;

    protected function setUp() {
        $this->validMultiPassId = new PassId(27, 154, 23712);
        $this->validSinglePassId = new PassId(27, 154, 23708);
        $this->invalidPassId = new PassId(27, 154, 0);

        $this->client = new SkipassClient(static::BASE_URI, Locale::EN);
        $this->jar = new CookieJar();
    }

    public function testRequestSearchSinglePass()
    {
        $response = $this->client->requestSearch($this->jar, ProjectId::GOLM, $this->validSinglePassId, self::TEST_LOCALE);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $locationHeader = $response->getHeader('Location');
        $this->assertEquals(1, count($locationHeader));
        $this->assertEquals(static::BASE_URI.'/'.ProjectId::GOLM.'/'.Locale::DE.'/detail.php?day=0', $locationHeader[0]);

        $this->assertEquals(2, $this->jar->count());
    }

    public function testRequestSearchMultiPass()
    {
        $response = $this->client->requestSearch($this->jar, ProjectId::GOLM, $this->validMultiPassId, self::TEST_LOCALE);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $locationHeader = $response->getHeader('Location');
        $this->assertEquals(1, count($locationHeader));
        $this->assertEquals(static::BASE_URI.'/'.ProjectId::GOLM.'/'.Locale::DE.'/acclist.php', $locationHeader[0]);

        $this->assertEquals(2, $this->jar->count());
    }

    public function testRequestSearchInvalidPass()
    {
        $response = $this->client->requestSearch($this->jar, ProjectId::GOLM, $this->invalidPassId, self::TEST_LOCALE);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('Location'));

        $this->assertEquals(2, $this->jar->count());
    }

    public function testRequestRedirectSinglePass()
    {
        $location = $this->client->requestSearch($this->jar, ProjectId::GOLM, $this->validSinglePassId, self::TEST_LOCALE)->getHeader('Location')[0];
        $response = $this->client->requestRedirect($this->jar, $location);
        $this->assertEquals(200, $response->getStatusCode());
        var_dump($response->getBody()->getContents());
    }

    public function testRequestRedirectMultiPass()
    {
        $location = $this->client->requestSearch($this->jar, ProjectId::GOLM, $this->validMultiPassId, self::TEST_LOCALE)->getHeader('Location')[0];
        $response = $this->client->requestRedirect($this->jar, $location);
        $this->assertEquals(200, $response->getStatusCode());
        var_dump($response->getBody()->getContents());
    }

    public function test()
    {
        $context = $this->client->findContextByPassId(ProjectId::GOLM, $this->validMultiPassId);
        $this->assertNotNull($context, 'No valid context found');
//        var_dump($context);
//        $result = $this->client->findDayById($context, 20);
//        echo json_encode($result, JSON_PRETTY_PRINT |JSON_UNESCAPED_UNICODE);
        echo json_encode($this->client->findDays($context), JSON_PRETTY_PRINT |JSON_UNESCAPED_UNICODE);
    }
}
