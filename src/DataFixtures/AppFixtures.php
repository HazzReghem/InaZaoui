<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $repository = $manager->getRepository(User::class);
        $users = $repository->findAll();

        foreach ($users as $user) {
            $email = $user->getEmail();

            // Set password
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
            $user->setPassword($hashedPassword);

            // Set role
            if ($email === 'ina@zaoui.com') {
                $user->setRoles(['ROLE_ADMIN']);
            } else {
                $user->setRoles(['ROLE_USER']);
            }

            // Set isBlocked to false if null
            if ($user->isBlocked() === null) {
                $user->setIsBlocked(false);
            }

            $manager->persist($user);
        }

        $manager->flush();
    }
}
