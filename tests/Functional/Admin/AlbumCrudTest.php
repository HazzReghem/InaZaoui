<?php

namespace App\Tests\Functional\Admin;

use App\Entity\Album;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class AlbumCrudTest extends WebTestCase
{
    public function testAdminCanAddUpdateAndDeleteAlbum(): void
    {
        $client = static::createClient();

        // Connexion d’un utilisateur admin
        $admin = static::getContainer()->get(UserRepository::class)->findOneByEmail('admin@test.com');
        $this->assertNotNull($admin);
        $client->loginUser($admin);

        $em = static::getContainer()->get(EntityManagerInterface::class);

        // Nom unique pour éviter les conflits avec les fixtures
        $initialName = 'Album test ' . uniqid();
        $updatedName = $initialName . ' (modifié)';

        // Ajout
        $crawler = $client->request('GET', '/admin/album/add');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Ajouter')->form([
            'album[name]' => $initialName,
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/admin/album');
        $crawler = $client->followRedirect();
        $this->assertStringContainsString($initialName, $client->getCrawler()->filter('table')->text());


        // Modification
        $album = $em->getRepository(Album::class)->findOneBy(['name' => $initialName]);
        $this->assertNotNull($album);

        $crawler = $client->request('GET', '/admin/album/update/' . $album->getId());
        $form = $crawler->selectButton('Modifier')->form([
            'album[name]' => $updatedName,
        ]);
        $client->submit($form);
        $client->followRedirect();
        $this->assertStringContainsString($updatedName, $client->getCrawler()->filter('table')->text());

        // Suppression
        $client->request('GET', '/admin/album/delete/' . $album->getId());
        $client->followRedirect();
        $this->assertSelectorNotExists('td:contains("' . $updatedName . '")');
    }
}
