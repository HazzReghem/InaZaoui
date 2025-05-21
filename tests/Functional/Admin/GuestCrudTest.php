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

    public function testAdminCanUpdateGuest(): void
    {
        $client = static::createClient();

        $admin = static::getContainer()->get(UserRepository::class)->findOneByEmail('admin@test.com');
        $client->loginUser($admin);

        $em = static::getContainer()->get(EntityManagerInterface::class);

        // Crée un guest temporaire
        $guest = new User();
        $guest->setName('Old Name');
        $guest->setEmail('update_guest_' . uniqid() . '@example.com');
        $guest->setRoles(['ROLE_USER']);
        $guest->setIsBlocked(false);
        $guest->setPassword('password');
        $em->persist($guest);
        $em->flush();

        // Récupérer la page d'édition
        $crawler = $client->request('GET', '/admin/guests/update/' . $guest->getId());
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form')->form();
        $form['user[name]'] = 'Updated Name';
        $form['user[email]'] = $guest->getEmail();
        $form['user[password]'] = 'newpassword';


        // Soumettre le formulaire
        $client->submit($form);

        // Suivre la redirection après soumission
        $client->followRedirect();

        // Vérifier que la nouvelle valeur est affichée dans le tableau
        $this->assertStringContainsString('Updated Name', $client->getCrawler()->filter('table')->text());

        $guestToRemove = $em->find(User::class, $guest->getId());
        if ($guestToRemove) {
            $em->remove($guestToRemove);
            $em->flush();
        }
    }

}
