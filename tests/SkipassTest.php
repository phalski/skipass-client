<?php


namespace Phalski\Skipass;


class SkipassTest extends \PHPUnit\Framework\TestCase
{

    public function testName()
    {
        $skipass = Skipass::for('golm', new Ticket(27,154, 23715));

        $skipass->updateCount();
        var_dump(json_encode($skipass->findAllAsync(-1), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

}
