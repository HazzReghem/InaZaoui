<?php

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Security\UserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserCheckerTest extends TestCase
{
    public function testCheckPreAuthWithUnblockedUser(): void
    {
        $user = new User();
        $user->setIsBlocked(false);

        $checker = new UserChecker();

        $checker->checkPreAuth($user);
       
        $this->addToAssertionCount(1); 
    }

    public function testCheckPreAuthWithBlockedUser(): void
    {
        $user = new User();
        $user->setIsBlocked(true);

        $checker = new UserChecker();

        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('Votre accès a été révoqué.');

        $checker->checkPreAuth($user);
    }

    public function testCheckPreAuthWithInvalidUserInstance(): void
    {
        $mockUser = $this->createMock(UserInterface::class);
        $checker = new UserChecker();

        $checker->checkPreAuth($mockUser);
        
        $this->addToAssertionCount(1); 
    }
}
