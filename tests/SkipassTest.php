<?php


namespace Phalski\Skipass;


class SkipassTest extends \PHPUnit\Framework\TestCase
{
    private const VALID_PROJECT_ID = 'golm';
    private const EMPTY_PROJECT_ID = '';
    private const INVALID_PROJECT_ID = 'qwertyuiop';

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

    /**
     * @var Skipass
     */
    private $skipass;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$validMultiTicket = new Ticket(27, 154, 23715);
        self::$validSingleTicket = new Ticket(27, 154, 23708);
        self::$invalidTicket = new Ticket(27, 154, 0);
    }

    protected function setUpFor(Ticket $ticket)
    {
        $client = new Client(
            self::VALID_PROJECT_ID,
            new \GuzzleHttp\Client([
                'allow_redirects' => false,
                'base_uri' => 'http://kv.skipass.cx',
                'cookies' => true
            ]),
            'en');
        $client->setTicket($ticket);
        $this->skipass = new Skipass(
            $client,
            new Selector());
    }


    public function testForEmptyProjectId()
    {
        $this->expectException(\InvalidArgumentException::class);
        Skipass::for(self::EMPTY_PROJECT_ID, self::$validMultiTicket);
    }

    public function testForInvalidProjectId()
    {
        $this->expectException(NotFoundException::class);
        Skipass::for(self::INVALID_PROJECT_ID, self::$validMultiTicket);
    }

    public function testForInvalidTicket()
    {
        $this->expectException(NotFoundException::class);
        Skipass::for(self::VALID_PROJECT_ID, self::$invalidTicket);
    }

    public function testForSingleTicket()
    {
        Skipass::for(self::VALID_PROJECT_ID, self::$validSingleTicket);
        $this->assertTrue(true);
    }

    public function testForMultiTicket()
    {
        Skipass::for(self::VALID_PROJECT_ID, self::$validMultiTicket);
        $this->assertTrue(true);
    }


    public function testCountMulti()
    {
        $this->setUpFor(self::$validMultiTicket);
        $this->assertEquals(35, $this->skipass->count());
    }

    public function testCountSingle()
    {
        $this->setUpFor(self::$validSingleTicket);
        $this->assertEquals(1, $this->skipass->count());
    }

    public function testFindByIdMulti()
    {
        $this->setUpFor(self::$validMultiTicket);
        $result = $this->skipass->findById(0);
        $this->assertFalse($result->hasErrors());
        $this->assertEmpty($result->getErrors());
        $this->assertEquals(self::$validMultiTicket, $result->getData()->getTicket());
        $this->assertEquals(self::VALID_PROJECT_ID, $result->getData()->getProjectId());
        $this->assertCount(1, ($result->getData()->getDays()));
        $this->assertEquals(0, ($result->getData()->getDays()[0]->getId()));
        $this->assertCount(4, ($result->getData()->getDays()[0]->getRides()));
        $this->assertCount(3, ($result->getData()->getLifts()));
    }

    public function testFindByIdInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->setUpFor(self::$validMultiTicket);
        $this->skipass->findById(-1);
    }

    public function testFindByIdInvalid2()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->setUpFor(self::$validMultiTicket);
        $this->skipass->findById(999);
    }

    public function testFindAll()
    {
        $this->setUpFor(self::$validMultiTicket);

        $result = $this->skipass->findAll(-1);
        $this->assertFalse($result->hasErrors());
        $this->assertEmpty($result->getErrors());
        $this->assertEquals(self::$validMultiTicket, $result->getData()->getTicket());
        $this->assertEquals(self::VALID_PROJECT_ID, $result->getData()->getProjectId());
        $this->assertCount(35, ($result->getData()->getDays()));
        $this->assertEquals(0, ($result->getData()->getDays()[0]->getId()));
        $this->assertCount(4, ($result->getData()->getDays()[0]->getRides()));
        $this->assertCount(21, ($result->getData()->getLifts()));
    }

    public function testFindAllFirst()
    {
        $this->setUpFor(self::$validMultiTicket);

        $result = $this->skipass->findAll(2);
        $this->assertFalse($result->hasErrors());
        $this->assertEmpty($result->getErrors());
        $this->assertEquals(self::$validMultiTicket, $result->getData()->getTicket());
        $this->assertEquals(self::VALID_PROJECT_ID, $result->getData()->getProjectId());
        $this->assertCount(2, ($result->getData()->getDays()));
        $this->assertEquals(0, ($result->getData()->getDays()[0]->getId()));
        $this->assertCount(4, ($result->getData()->getDays()[0]->getRides()));
        $this->assertCount(5, ($result->getData()->getLifts()), 'Verify lifts');
    }

    public function testFindAllOffset()
    {
        $this->setUpFor(self::$validMultiTicket);

        $result = $this->skipass->findAll(2,5);
        $this->assertFalse($result->hasErrors());
        $this->assertEmpty($result->getErrors());
        $this->assertEquals(self::$validMultiTicket, $result->getData()->getTicket());
        $this->assertEquals(self::VALID_PROJECT_ID, $result->getData()->getProjectId());
        $this->assertCount(2, ($result->getData()->getDays()));
        $this->assertEquals(5, ($result->getData()->getDays()[0]->getId()));
        $this->assertCount(3, ($result->getData()->getDays()[0]->getRides()));
        $this->assertCount(2, ($result->getData()->getLifts()), 'Verify lifts');
    }

}
