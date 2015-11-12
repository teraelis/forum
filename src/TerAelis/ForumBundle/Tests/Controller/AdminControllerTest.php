<?php

namespace TerAelis\ForumBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTest extends WebTestCase
{
    public function testAdmincategories()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/admin/categories');
    }

}
