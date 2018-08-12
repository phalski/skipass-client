# Skipass site scraping client

Usage:

```php
<?php

use \Phalski\Skipass\Skipass;
use \Phalski\Skipass\Ticket;


// init for a ticket
try {
    // uses the default skipass endpoint at 'http://kv.skipass.cx' with locale 'en' and 'Europe/Berlin' timezone.
    $skipass = Skipass::for('golm', new Ticket(27, 154, 23715)); 
} catch (\Phalski\Skipass\NotFoundException $e) {
    // ticket does not exists
} catch (\Phalski\Skipass\FetchException $e) {
    // something went wrong with the http request
}


// retrieve the total day count of a ticket
try {
    $count = $skipass->count();
} catch (\Phalski\Skipass\FetchException $e) {
    // something went wrong with the http request
}


// retrieve the usage data of a specific day (day_id is between 0 and count())
try {
    $result = $skipass->findById(2);
    if ($result->hasErrors()) {
        // handle all the errors withing $result->getErrors()
        throw new LogicException();
    }
    $data = $result->getData();
} catch (\Phalski\Skipass\FetchException $e) {
    // something went wrong with the http request for the latest count
} catch (\InvalidArgumentException $e) {
    // day_id is not between 0 and count()
}


// retrieve the usage data of multiple days
$first = 3;
$offset = 5;
try {
    $result = $skipass->findAll($first, $offset);
    if ($result->hasErrors()) {
        // handle all the errors withing $result->getErrors()
        throw new LogicException();
    }
    $data = $result->getData();
} catch (\Phalski\Skipass\FetchException $e) {
    // something went wrong with the http request for the latest count
} catch (\InvalidArgumentException $e) {
    // offset is not between 0 and count()
}


// retrieve the usage data of all days
try {
    $result = $skipass->findAll(-1);
    if ($result->hasErrors()) {
        // handle all the errors withing $result->getErrors()
        throw new LogicException();
    }
    $data = $result->getData();
} catch (\Phalski\Skipass\FetchException $e) {
    // something went wrong with the http request for the latest count
} catch (\InvalidArgumentException $e) {
    // day_id is not between 0 and count()
}

```

