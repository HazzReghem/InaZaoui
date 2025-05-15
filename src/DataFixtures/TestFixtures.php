<?php

namespace App\DataFixtures;

use App\Entity\Media;
use App\Entity\User;
use App\Entity\Album;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;


class TestFixtures extends Fixture implements FixtureGroupInterface
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setName('Admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsBlocked(false);
        $admin->setPassword($this->hasher->hashPassword($admin, 'password'));
        $manager->persist($admin);

        // Invité non bloqué
        $guest1 = new User();
        $guest1->setEmail('guest1@test.com');
        $guest1->setName('Guest One');
        $guest1->setRoles(['ROLE_USER']);
        $guest1->setIsBlocked(false);
        $guest1->setPassword($this->hasher->hashPassword($guest1, 'password'));
        $manager->persist($guest1);

        // Invité bloqué
        $guest2 = new User();
        $guest2->setEmail('guest2@test.com');
        $guest2->setName('Guest Two');
        $guest2->setRoles(['ROLE_USER']);
        $guest2->setIsBlocked(true);
        $guest2->setPassword($this->hasher->hashPassword($guest2, 'password'));
        $manager->persist($guest2);

        // Album
        $album = new Album();
        $album->setName('Album de test');
        $manager->persist($album);

        // Media 1
        $media1 = new Media();
        $media1->setTitle('Media de Guest1');
        $media1->setUser($guest1);
        $media1->setAlbum($album);
        $media1->setPath('uploads/media1.jpg');
        $manager->persist($media1);

        // Media 2
        $media2 = new Media();
        $media2->setTitle('Media de Guest2');
        $media2->setUser($guest2);
        $media2->setPath('uploads/media2.jpg');
        $manager->persist($media2);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['test'];
    }
}
