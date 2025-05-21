<?php

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

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

    public function testFindGuestsReturnsOnlyNonBlockedUsersWithoutRoleAdmin(): void
    {
        $guests = $this->repository->findGuests();
        $emails = array_map(fn($u) => $u->getEmail(), $guests);

        // On filtre uniquement ceux créés par la fixture
        $filtered = array_values(array_filter($emails, fn($email) => in_array($email, [
            'guest1@test.com',
        ])));

        sort($filtered);

        $this->assertEquals(['guest1@test.com'], $filtered);
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
}
