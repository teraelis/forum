<?php

namespace TerAelis\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RangControllerTest extends WebTestCase
{
    public function testAdd()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/add');
    }

    public function testRemove()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/remove');
    }

}
