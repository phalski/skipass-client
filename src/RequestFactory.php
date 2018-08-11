<?php


namespace Phalski\Skipass;


use GuzzleHttp\Psr7\Request;

abstract class RequestFactory
{
    public static function createSearch()
    {
        $r = new Request('POST', '/golm/de/search.php', [], [

        ]);

    }

    private static function pathFor(array $elements): string
    {
        return '/' . join('/', $elements);
    }
}