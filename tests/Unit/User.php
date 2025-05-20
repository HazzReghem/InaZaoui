<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Media;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testEmailAndName(): void
    {
        $user = new User();
        $user->setEmail('john@example.com');
        $user->setName('John');

        $this->assertSame('john@example.com', $user->getEmail());
        $this->assertSame('John', $user->getName());
        $this->assertSame('john@example.com', $user->getUserIdentifier());
    }

    public function testRoles(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles);
    }

    public function testPassword(): void
    {
        $user = new User();
        $user->setPassword('secret');
        $this->assertSame('secret', $user->getPassword());
    }

    public function testDescription(): void
    {
        $user = new User();
        $user->setDescription('A test user');
        $this->assertSame('A test user', $user->getDescription());
    }

    public function testIsBlocked(): void
    {
        $user = new User();
        $user->setIsBlocked(true);
        $this->assertTrue($user->isBlocked());
    }

    public function testMedias(): void
    {
        $user = new User();
        $media = new Media();

        $collection = new ArrayCollection([$media]);
        $user->setMedias($collection);

        $this->assertCount(1, $user->getMedias());
        $this->assertSame($media, $user->getMedias()->first());
    }

    public function testInitialIdIsNull(): void
    {
        $user = new User();
        $this->assertNull($user->getId());
    }
}
