<?php

namespace App\Tests\Functional\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testAccessDenied(): void
    {
        $client = static::createClient();
        $client->request('GET', '/access-denied');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('body'); // vérifie que la page s'affiche
    }

    public function testLogoutThrowsLogicException(): void
    {
        $this->expectException(\LogicException::class);

        $controller = new \App\Controller\Admin\SecurityController();
        $controller->logout(); // méthode volontairement vide avec exception
    }
}
