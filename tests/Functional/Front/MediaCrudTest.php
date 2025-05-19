<?php

namespace App\Tests\Functional\Front;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaCrudTest extends WebTestCase
{
    public function testGuestCanAddAndDeleteMedia(): void
    {
        $client = static::createClient();

        // Récupérer l'utilisateur de test depuis la base de données
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('guest1@test.com');

        // Vérifiez que l'utilisateur existe
        $this->assertNotNull($testUser, 'Test user guest1@test.com not found in fixtures.');

        $client->loginUser($testUser);

        // Aller à la page d'ajout
        $crawler = $client->request('GET', '/guest/media/add');
        $this->assertResponseIsSuccessful();

        $filePath = __DIR__ . '/../../Fixtures/test.jpg';
        if (!file_exists(__DIR__.'/../../Fixtures/dummy.jpeg')) {
            touch(__DIR__.'/../../Fixtures/dummy.jpeg');
        }
        copy(__DIR__.'/../../Fixtures/dummy.jpeg', $filePath);

        $form = $crawler->selectButton('Ajouter')->form([
            'media[title]' => 'Nouveau Média Test',
            'media[file]' => new UploadedFile($filePath, 'test.jpg', 'image/jpeg', null, true)
        ]);

        $client->submit($form);
        $this->assertResponseRedirects('/guest/media/');
        $crawler = $client->followRedirect();

        $this->assertSelectorTextContains('tbody tr:last-child td:nth-child(2)', 'Nouveau Média Test');

        $media = self::getContainer()->get('doctrine')->getRepository(\App\Entity\Media::class)->findOneBy(['title' => 'Nouveau Média Test']);
        $this->assertNotNull($media, "Le média 'Nouveau Média Test' n'a pas été trouvé après l'ajout.");

        $client->request('GET', '/guest/media/delete/' . $media->getId());
        $deleted = self::getContainer()->get('doctrine')->getRepository(\App\Entity\Media::class)->find($media->getId());
        $this->assertNull($deleted, "Le média n'a pas été supprimé de la base de données.");


        $this->assertResponseRedirects('/guest/media/');
  
        // Redémarrer le kernel pour purger Doctrine cache
        $client->restart();

        $client->followRedirect();

        $nodes = $client->getCrawler()->filter('tbody tr td:nth-child(2)');
        foreach ($nodes as $node) {
            $this->assertStringNotContainsString('Nouveau Média Test', $node->textContent);
        }


        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}