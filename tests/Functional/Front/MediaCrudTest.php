<?php

namespace App\Tests\Functional\Front;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class MediaCrudTest extends WebTestCase
{
    public function testGuestCanAddAndDeleteMedia(): void
    {
        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'guest1@test.com',
            'PHP_AUTH_PW'   => 'password',
        ]);

        // Aller à la page d'ajout
        $crawler = $client->request('GET', '/guest/media/add');
        $this->assertResponseIsSuccessful();

        // Fichier fictif
        $filePath = __DIR__ . '/../../Fixtures/test.jpg';
        copy(__DIR__.'/../../Fixtures/dummy.jpeg', $filePath); 

        $form = $crawler->selectButton('Ajouter')->form([
            'media[title]' => 'Nouveau Média Test',
            'media[path]' => new UploadedFile($filePath, 'test.jpg')
        ]);

        $client->submit($form);
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertSelectorTextContains('.media-title', 'Nouveau Média Test');

        // Récupérer l'ID du média ajouté pour suppression
        $media = self::getContainer()->get('doctrine')->getRepository(\App\Entity\Media::class)->findOneBy(['title' => 'Nouveau Média Test']);
        $client->request('GET', '/guest/media/delete/' . $media->getId());
        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertSelectorNotExists('.media-title:contains("Nouveau Média Test")');
    }
}
