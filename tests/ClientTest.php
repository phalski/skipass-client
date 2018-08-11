<?php


namespace Phalski\Skipass;


class ClientTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Ticket
     */
    private static $validMultiTicket;
    /**
     * @var Ticket
     */
    private static $validSingleTicket;
    /**
     * @var Ticket
     */
    private static $invalidTicket;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$validMultiTicket = new Ticket(27, 154, 23715);
        self::$validSingleTicket = new Ticket(27, 154, 23708);
        self::$invalidTicket = new Ticket(27, 154, 0);
    }


    protected function setUp()
    {
        parent::setUp();
        $this->client = Client::for('golm');
    }

    public function testSetSingleTicket()
    {
        $this->client->setTicket(self::$validSingleTicket);
        $this->assertNotNull($this->client->hasMultipleDays());
        $this->assertFalse($this->client->hasMultipleDays());
        $this->assertTrue($this->client->isReady());
    }

    public function testSetMultiTicket()
    {
        $this->client->setTicket(self::$validMultiTicket);
        $this->assertNotNull($this->client->hasMultipleDays());
        $this->assertTrue($this->client->hasMultipleDays());
        $this->assertTrue($this->client->isReady());
    }

    public function testSetInvalidTicket()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->client->setTicket(self::$invalidTicket);
    }

    public function testAccessListForSingleTicket()
    {
        $this->client->setTicket(self::$validSingleTicket);
        $content = $this->client->getAccessListContents();
        $this->assertNotNull($content);
        $this->assertNotEmpty($content);
    }

    public function testAccessListForMultiTicket()
    {
        $this->client->setTicket(self::$validMultiTicket);
        $content = $this->client->getAccessListContents();
        $this->assertNotNull($content);
        $this->assertNotEmpty($content);
    }

    public function testDetailForValidDay()
    {
        $this->client->setTicket(self::$validSingleTicket);
        $content = $this->client->getDetailContents(0);
        $this->assertNotNull($content);
        $this->assertNotEmpty($content);
    }

    public function testDetailForInvalidDay()
    {
        $this->client->setTicket(self::$validSingleTicket);
        $content = $this->client->getDetailContents(-1);
        $this->assertNotNull($content);
        $this->assertNotEmpty($content);
    }

    public function testNotReadyAccessList()
    {
        $this->expectException(\LogicException::class);
        $this->client->getAccessListContents();
    }

    public function testNotReadyDetail()
    {
        $this->expectException(\LogicException::class);
        $this->client->getDetailContents(0);
    }

    public function testNotReadyHasMultipleDays()
    {
        $this->expectException(\LogicException::class);
        $this->client->hasMultipleDays();
    }
}
