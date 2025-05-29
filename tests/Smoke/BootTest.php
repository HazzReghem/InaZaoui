<?php

namespace App\Tests\Smoke;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BootTest extends KernelTestCase
{
    public function testBoot(): void
    {
        self::bootKernel();
        $this->assertNotNull(self::$kernel);
    }
}
