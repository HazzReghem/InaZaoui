<?php

namespace App\Tests\Functional\Front;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PortfolioTest extends WebTestCase
{
    public function testGuestSeesOwnMediaInPortfolio(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'guest1@test.com',
            'PHP_AUTH_PW'   => 'password',
        ]);

        $crawler = $client->request('GET', '/portfolio');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Portfolio');
        $this->assertSelectorTextContains('.media-title', 'Media de Guest1');
        $this->assertSelectorNotExists('td:contains("Media de Guest2")');
    }
}
