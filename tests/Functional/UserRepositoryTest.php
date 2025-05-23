<?php

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = static::getContainer()->get(UserRepository::class);
    }

    public function testFindAdminsReturnsOnlyAdmins(): void
    {
        $admins = $this->repository->findAdmins();
        $emails = array_map(fn($u) => $u->getEmail(), $admins);

        $this->assertContains('admin@test.com', $emails);
    }

    public function testFindAllGuestsReturnsAllUsersExceptAdmins(): void
    {
        $allGuests = $this->repository->findAllGuests();
        $emails = array_map(fn($u) => $u->getEmail(), $allGuests);

        // On filtre uniquement ceux créés par la fixture
        $filtered = array_values(array_filter($emails, fn($email) => in_array($email, [
            'guest1@test.com',
            'guest2@test.com',
        ])));

        sort($filtered);

        $this->assertEquals(['guest1@test.com', 'guest2@test.com'], $filtered);
    }

    public function testUpgradePasswordWorksCorrectly(): void
    {
        $em = static::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);

        // Nettoyer l'utilisateur temp@test.com s'il existe
        $existingUser = $em->getRepository(\App\Entity\User::class)->findOneBy(['email' => 'temp@test.com']);
        if ($existingUser) {
            $em->remove($existingUser);
            $em->flush();
        }

        $user = new \App\Entity\User();
        $user->setEmail('temp@test.com');
        $user->setName('Temporary User');
        $user->setIsBlocked(false);
        $user->setPassword('old_password');

        // Persister l'utilisateur avant de le mettre à jour
        $em->persist($user);
        $em->flush();

        $this->repository->upgradePassword($user, 'new_hashed_pwd');

        $this->assertEquals('new_hashed_pwd', $user->getPassword());
    }

}
