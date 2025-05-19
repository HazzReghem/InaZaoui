<?php

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomePageLoadsSuccessfully(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h2', 'Photographe');
    }

    public function testGuestsPageListsGuests(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/guests');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h4');
    }

    public function testGuestPageDisplaysValidGuest(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $guest = $userRepository->findOneByEmail('guest1@test.com');

        $this->assertNotNull($guest, 'Le guest de test est introuvable.');
        
        $client->request('GET', '/guest/' . $guest->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h3');
    }

    public function testAboutPageLoadsSuccessfully(): void
    {
        $client = static::createClient();
        $client->request('GET', '/about');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h2', 'Qui suis-je ?');
    }

    public function testPortfolioRedirectsIfNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/portfolio');

        $this->assertResponseRedirects('/login');
    }

    public function testPortfolioDisplaysUserMedias(): void
    {
        $client = static::createClient();

        $userRepo = static::getContainer()->get(\App\Repository\UserRepository::class);
        $testUser = $userRepo->findOneByEmail('guest1@test.com');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/portfolio');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h3', 'Portfolio');
    }

    public function testGuestPageReturns404OnInvalidGuest(): void
    {
        $client = static::createClient();
        $client->request('GET', '/guest/999999');

        $this->assertResponseStatusCodeSame(404);
    }

}