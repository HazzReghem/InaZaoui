<?php

namespace App\Tests\Functional\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class GuestCrudTest extends WebTestCase
{
    public function testAdminCanAddBlockAndDeleteGuest(): void
    {
        $client = static::createClient();

        // Connexion d’un admin
        $admin = static::getContainer()->get(UserRepository::class)->findOneByEmail('admin@test.com');
        $this->assertNotNull($admin);
        $client->loginUser($admin);

        // Générer un nom et email uniques
        $name = 'Test Guest ' . uniqid();
        $email = 'guest_' . uniqid() . '@example.com';

        // Nettoyage défensif
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $existing = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $em->remove($existing);
            $em->flush();
        }

        // Ajout
        $crawler = $client->request('GET', '/admin/guests/add');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form([
            'user[name]' => $name,
            'user[email]' => $email,
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/admin/guests/');
        $client->followRedirect();
        $this->assertStringContainsString($name, $client->getCrawler()->filter('table')->text());

        // Blocage
        $guest = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        $this->assertNotNull($guest);

        $client->request('GET', '/admin/guests/toggle-block/' . $guest->getId());
        $client->followRedirect();

        // Suppression
        $client->request('GET', '/admin/guests/delete/' . $guest->getId());
        $client->followRedirect();
        $this->assertSelectorNotExists('td:contains("' . $name . '")');
    }
}
