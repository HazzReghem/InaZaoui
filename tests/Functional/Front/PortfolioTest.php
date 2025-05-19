<?php

namespace App\Tests\Functional\Front;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PortfolioTest extends WebTestCase
{
    public function testGuestSeesOwnMediaInPortfolio(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('guest1@test.com');
        $this->assertNotNull($testUser, 'Test user guest1@test.com not found in fixtures.');

        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/portfolio');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Portfolio');
        $this->assertSelectorTextContains('.media-title', 'Media de Guest1'); 
        $this->assertSelectorNotExists('.media-title:contains("Media de Guest2")');
    }
}